<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
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

    private function createGeneralUser(string $name, string $email): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'general',
            'email_verified_at' => now(),
        ]);
    }

    public function test_その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 15));

        $admin = $this->createAdmin();

        $user1 = $this->createGeneralUser('スタッフ1', 'staff1@example.com');
        $user2 = $this->createGeneralUser('スタッフ2', 'staff2@example.com');

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'work_date' => '2026-04-15',
            'clock_in_at' => '2026-04-15 09:00:00',
            'clock_out_at' => '2026-04-15 18:00:00',
            'note' => '通常勤務',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance1->id,
            'break_start_at' => '2026-04-15 12:00:00',
            'break_end_at' => '2026-04-15 13:00:00',
        ]);

        Attendance::create([
            'user_id' => $user2->id,
            'work_date' => '2026-04-15',
            'clock_in_at' => '2026-04-15 10:00:00',
            'clock_out_at' => '2026-04-15 19:00:00',
            'note' => '通常勤務',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=2026-04-15');

        $response->assertStatus(200);

        $response->assertSee('スタッフ1');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('1:00');
        $response->assertSee('8:00');

        $response->assertSee('スタッフ2');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        Carbon::setTestNow();
    }

    public function test_遷移した際に現在の日付が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 15));

        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('2026-04-15');

        Carbon::setTestNow();
    }

    public function test_前日を押下した時に前日の日付の勤怠情報が表示される()
    {
        $admin = $this->createAdmin();

        $user = $this->createGeneralUser('スタッフ1', 'staff1@example.com');

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-14',
            'clock_in_at' => '2026-04-14 09:30:00',
            'clock_out_at' => '2026-04-14 18:30:00',
            'note' => '通常勤務',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=2026-04-14');

        $response->assertStatus(200);
        $response->assertSee('2026-04-14');
        $response->assertSee('スタッフ1');
        $response->assertSee('09:30');
        $response->assertSee('18:30');
    }

    public function test_翌日を押下した時に翌日の日付の勤怠情報が表示される()
    {
        $admin = $this->createAdmin();

        $user = $this->createGeneralUser('スタッフ1', 'staff1@example.com');

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-04-16',
            'clock_in_at' => '2026-04-16 10:00:00',
            'clock_out_at' => '2026-04-16 19:00:00',
            'note' => '通常勤務',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=2026-04-16');

        $response->assertStatus(200);
        $response->assertSee('2026-04-16');
        $response->assertSee('スタッフ1');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }
}