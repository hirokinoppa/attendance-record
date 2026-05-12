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

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        return User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'role' => 'general',
            'email_verified_at' => now(),
        ]);
    }

    private function createAttendance(User $user): Attendance
    {
        return Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-15',
            'clock_in_at' => '2026-04-15 09:00:00',
            'clock_out_at' => '2026-04-15 18:00:00',
            'note' => '通常勤務',
        ]);
    }

    public function test_勤怠詳細画面の名前がログインユーザーの氏名になっている()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)
            ->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
    }

    public function test_勤怠詳細画面の日付が選択した日付になっている()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)
            ->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('2026年');
        $response->assertSee('4月15日');
    }

    public function test_出勤退勤に記されている時間がログインユーザーの打刻と一致している()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)
            ->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_休憩に記されている時間がログインユーザーの打刻と一致している()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => '2026-04-15 12:00:00',
            'break_end_at' => '2026-04-15 13:00:00',
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}