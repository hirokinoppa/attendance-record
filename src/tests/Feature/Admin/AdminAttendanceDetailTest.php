<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    private function createUser(): User
    {
        return User::create([
            'name' => 'スタッフ1',
            'email' => 'staff1@example.com',
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

    public function test_勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => '2026-04-15 12:00:00',
            'break_end_at' => '2026-04-15 13:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('スタッフ1');
        $response->assertSee('2026年');
        $response->assertSee('4月15日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('通常勤務');
    }

    public function test_出勤時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($admin)
            ->post('/admin/attendance/detail/' . $attendance->id . '/update', [
                'clock_in_at' => '19:00',
                'clock_out_at' => '18:00',
                'break_times' => [
                    [
                        'start' => '12:00',
                        'end' => '13:00',
                    ],
                ],
                'note' => '修正します',
            ]);

        $response->assertSessionHasErrors(['clock_in_at']);
    }

    public function test_休憩開始時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($admin)
            ->post('/admin/attendance/detail/' . $attendance->id . '/update', [
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
                'break_times' => [
                    [
                        'start' => '19:00',
                        'end' => '19:30',
                    ],
                ],
                'note' => '修正します',
            ]);

        $response->assertSessionHasErrors(['break_times.0.start']);
    }

    public function test_休憩終了時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($admin)
            ->post('/admin/attendance/detail/' . $attendance->id . '/update', [
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
                'break_times' => [
                    [
                        'start' => '17:30',
                        'end' => '19:00',
                    ],
                ],
                'note' => '修正します',
            ]);

        $response->assertSessionHasErrors(['break_times.0.end']);
    }

    public function test_備考欄が未入力の場合エラーメッセージが表示される()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($admin)
            ->post('/admin/attendance/detail/' . $attendance->id . '/update', [
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
                'break_times' => [
                    [
                        'start' => '12:00',
                        'end' => '13:00',
                    ],
                ],
                'note' => '',
            ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }
}