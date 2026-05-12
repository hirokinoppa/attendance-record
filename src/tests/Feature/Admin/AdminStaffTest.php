<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminStaffTest extends TestCase
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

    private function createStaff(string $name, string $email): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'general',
            'email_verified_at' => now(),
        ]);
    }

    public function test_全ての一般ユーザーの氏名とメールアドレスが表示されている()
    {
        $admin = $this->createAdmin();

        $this->createStaff('スタッフ1', 'staff1@example.com');
        $this->createStaff('スタッフ2', 'staff2@example.com');

        $response = $this->actingAs($admin)->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertSee('スタッフ1');
        $response->assertSee('staff1@example.com');
        $response->assertSee('スタッフ2');
        $response->assertSee('staff2@example.com');
    }

    public function test_選択したユーザーの勤怠情報が正しく表示される()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff('スタッフ1', 'staff1@example.com');

        Attendance::create([
            'user_id' => $staff->id,
            'work_date' => '2026-04-15',
            'clock_in_at' => '2026-04-15 09:00:00',
            'clock_out_at' => '2026-04-15 18:00:00',
            'note' => '通常勤務',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/' . $staff->id . '?month=2026-04');

        $response->assertStatus(200);
        $response->assertSee('スタッフ1');
        $response->assertSee('04/15');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_前月を押下した時に表示月の前月の情報が表示される()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff('スタッフ1', 'staff1@example.com');

        Attendance::create([
            'user_id' => $staff->id,
            'work_date' => '2026-03-15',
            'clock_in_at' => '2026-03-15 09:30:00',
            'clock_out_at' => '2026-03-15 18:30:00',
            'note' => '通常勤務',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/' . $staff->id . '?month=2026-03');

        $response->assertStatus(200);
        $response->assertSee('2026-03');
        $response->assertSee('03/15');
        $response->assertSee('09:30');
        $response->assertSee('18:30');
    }

    public function test_翌月を押下した時に表示月の翌月の情報が表示される()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff('スタッフ1', 'staff1@example.com');

        Attendance::create([
            'user_id' => $staff->id,
            'work_date' => '2026-05-15',
            'clock_in_at' => '2026-05-15 10:00:00',
            'clock_out_at' => '2026-05-15 19:00:00',
            'note' => '通常勤務',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/staff/' . $staff->id . '?month=2026-05');

        $response->assertStatus(200);
        $response->assertSee('2026-05');
        $response->assertSee('05/15');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    public function test_詳細を押下するとその日の勤怠詳細画面に遷移する()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff('スタッフ1', 'staff1@example.com');

        $attendance = Attendance::create([
            'user_id' => $staff->id,
            'work_date' => '2026-04-15',
            'clock_in_at' => '2026-04-15 09:00:00',
            'clock_out_at' => '2026-04-15 18:00:00',
            'note' => '通常勤務',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('スタッフ1');
        $response->assertSee('2026年');
        $response->assertSee('4月15日');
    }
}