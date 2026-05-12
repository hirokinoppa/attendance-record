<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    private function createGeneralUser(): User
    {
        return User::create([
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'general',
            'email_verified_at' => now(),
        ]);
    }

    private function createWorkingAttendance(User $user): Attendance
    {
        return Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in_at' => Carbon::today()->setTime(9, 0),
            'clock_out_at' => null,
            'note' => null,
        ]);
    }

    public function test_勤務中の場合退勤ボタンが表示される()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        Carbon::setTestNow(Carbon::create(2026, 4, 13, 18, 0, 0));

        $user = $this->createGeneralUser();

        $this->createWorkingAttendance($user);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤');

        Carbon::setTestNow();
    }

    public function test_退勤ボタンを押すとステータスが退勤済になる()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        Carbon::setTestNow(Carbon::create(2026, 4, 13, 18, 0, 0));

        $user = $this->createGeneralUser();

        $this->createWorkingAttendance($user);

        $this->actingAs($user)->post('/attendance/clock-out');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');

        Carbon::setTestNow();
    }

    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        Carbon::setTestNow(Carbon::create(2026, 4, 13, 18, 0, 0));

        $user = $this->createGeneralUser();

        $this->createWorkingAttendance($user);

        $this->actingAs($user)->post('/attendance/clock-out');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => '2026-04-13',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('18:00');

        Carbon::setTestNow();
    }
}