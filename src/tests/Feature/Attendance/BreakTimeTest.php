<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BreakTimeTest extends TestCase
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

    public function test_出勤中の場合休憩入ボタンが表示される()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        Carbon::setTestNow(Carbon::create(2026, 4, 13, 10, 0, 0));

        $user = $this->createGeneralUser();

        $this->createWorkingAttendance($user);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩入');

        Carbon::setTestNow();
    }

    public function test_休憩入ボタンを押すとステータスが休憩中になる()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        Carbon::setTestNow(Carbon::create(2026, 4, 13, 10, 0, 0));

        $user = $this->createGeneralUser();

        $this->createWorkingAttendance($user);

        $this->actingAs($user)->post('/attendance/break-start');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');

        Carbon::setTestNow();
    }

    public function test_休憩は一日に何回でもできる()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        Carbon::setTestNow(Carbon::create(2026, 4, 13, 10, 0, 0));

        $user = $this->createGeneralUser();

        $attendance = $this->createWorkingAttendance($user);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => Carbon::today()->setTime(10, 0),
            'break_end_at' => Carbon::today()->setTime(10, 15),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩入');

        Carbon::setTestNow();
    }

    public function test_休憩戻ボタンを押すとステータスが出勤中になる()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        Carbon::setTestNow(Carbon::create(2026, 4, 13, 10, 15, 0));

        $user = $this->createGeneralUser();

        $attendance = $this->createWorkingAttendance($user);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => Carbon::today()->setTime(10, 0),
            'break_end_at' => null,
        ]);

        $this->actingAs($user)->post('/attendance/break-end');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');

        Carbon::setTestNow();
    }

    public function test_休憩戻は一日に何回でもできる()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        Carbon::setTestNow(Carbon::create(2026, 4, 13, 10, 30, 0));

        $user = $this->createGeneralUser();

        $attendance = $this->createWorkingAttendance($user);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => Carbon::today()->setTime(10, 0),
            'break_end_at' => Carbon::today()->setTime(10, 15),
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => Carbon::today()->setTime(10, 30),
            'break_end_at' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩戻');

        Carbon::setTestNow();
    }

    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        Carbon::setTestNow(Carbon::create(2026, 4, 13, 10, 15, 0));

        $user = $this->createGeneralUser();

        $attendance = $this->createWorkingAttendance($user);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => Carbon::today()->setTime(10, 0),
            'break_end_at' => null,
        ]);

        $this->actingAs($user)->post('/attendance/break-end');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('0:15');

        Carbon::setTestNow();
    }
}