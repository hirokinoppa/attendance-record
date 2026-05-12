<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ClockInTest extends TestCase
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

    public function test_勤務外の場合出勤ボタンが表示される()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        Carbon::setTestNow(Carbon::create(2026, 4, 13, 9, 0, 0));

        $user = $this->createGeneralUser();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤');

        Carbon::setTestNow();
    }

    public function test_出勤ボタンを押すとステータスが出勤中になる()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        Carbon::setTestNow(Carbon::create(2026, 4, 13, 9, 0, 0));

        $user = $this->createGeneralUser();

        $this->actingAs($user)->post('/attendance/clock-in');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');

        Carbon::setTestNow();
    }

    public function test_出勤は一日一回のみできる()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        Carbon::setTestNow(Carbon::create(2026, 4, 13, 9, 0, 0));

        $user = $this->createGeneralUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->toDateString(),
            'clock_in_at' => Carbon::today()->setTime(9, 0),
            'clock_out_at' => Carbon::today()->setTime(18, 0),
            'note' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertDontSee('出勤');

        Carbon::setTestNow();
    }

    public function test_出勤時刻が勤怠一覧画面で確認できる()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        Carbon::setTestNow(Carbon::create(2026, 4, 13, 9, 0, 0));

        $user = $this->createGeneralUser();

        $this->actingAs($user)->post('/attendance/clock-in');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => '2026-04-13',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('09:00');

        Carbon::setTestNow();
    }
}