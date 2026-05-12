<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceCorrectionRequestBreakTime;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCorrectionRequestTest extends TestCase
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

    private function createStaff(): User
    {
        return User::create([
            'name' => 'スタッフ1',
            'email' => 'staff1@example.com',
            'password' => Hash::make('password'),
            'role' => 'general',
            'email_verified_at' => now(),
        ]);
    }

    private function createAttendance(User $staff): Attendance
    {
        return Attendance::create([
            'user_id' => $staff->id,
            'work_date' => '2026-04-15',
            'clock_in_at' => '2026-04-15 09:00:00',
            'clock_out_at' => '2026-04-15 18:00:00',
            'note' => '通常勤務',
        ]);
    }

    public function test_承認待ちの修正申請が全て表示されている()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();
        $attendance = $this->createAttendance($staff);

        AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $staff->id,
            'requested_clock_in_at' => '2026-04-15 09:30:00',
            'requested_clock_out_at' => '2026-04-15 18:30:00',
            'request_note' => '電車遅延のため',
            'status' => AttendanceCorrectionRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/list?status=' . AttendanceCorrectionRequest::STATUS_PENDING);

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('スタッフ1');
        $response->assertSee('電車遅延のため');
        $response->assertSee('2026/04/15');
    }

    public function test_承認済みの修正申請が全て表示されている()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();
        $attendance = $this->createAttendance($staff);

        AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $staff->id,
            'requested_clock_in_at' => '2026-04-15 09:30:00',
            'requested_clock_out_at' => '2026-04-15 18:30:00',
            'request_note' => '電車遅延のため',
            'status' => AttendanceCorrectionRequest::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/list?status=' . AttendanceCorrectionRequest::STATUS_APPROVED);

        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee('スタッフ1');
        $response->assertSee('電車遅延のため');
        $response->assertSee('2026/04/15');
    }

    public function test_修正申請の詳細内容が正しく表示されている()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();
        $attendance = $this->createAttendance($staff);

        $correctionRequest = AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $staff->id,
            'requested_clock_in_at' => '2026-04-15 09:30:00',
            'requested_clock_out_at' => '2026-04-15 18:30:00',
            'request_note' => '電車遅延のため',
            'status' => AttendanceCorrectionRequest::STATUS_PENDING,
        ]);

        AttendanceCorrectionRequestBreakTime::create([
            'attendance_correction_request_id' => $correctionRequest->id,
            'break_start_at' => '2026-04-15 12:00:00',
            'break_end_at' => '2026-04-15 13:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/stamp_correction_request/approve/' . $correctionRequest->id);

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('スタッフ1');
        $response->assertSee('2026年');
        $response->assertSee('4月15日');
        $response->assertSee('09:30');
        $response->assertSee('18:30');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('電車遅延のため');
    }

    public function test_修正申請の承認処理が正しく行われる()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff();
        $attendance = $this->createAttendance($staff);

        $correctionRequest = AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $staff->id,
            'requested_clock_in_at' => '2026-04-15 09:30:00',
            'requested_clock_out_at' => '2026-04-15 18:30:00',
            'request_note' => '電車遅延のため',
            'status' => AttendanceCorrectionRequest::STATUS_PENDING,
        ]);

        AttendanceCorrectionRequestBreakTime::create([
            'attendance_correction_request_id' => $correctionRequest->id,
            'break_start_at' => '2026-04-15 12:00:00',
            'break_end_at' => '2026-04-15 13:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->post('/admin/stamp_correction_request/approve/' . $correctionRequest->id);

        $response->assertRedirect();

        $this->assertDatabaseHas('attendance_correction_requests', [
            'id' => $correctionRequest->id,
            'status' => AttendanceCorrectionRequest::STATUS_APPROVED,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in_at' => '2026-04-15 09:30:00',
            'clock_out_at' => '2026-04-15 18:30:00',
            'note' => '電車遅延のため',
        ]);

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_start_at' => '2026-04-15 12:00:00',
            'break_end_at' => '2026-04-15 13:00:00',
        ]);
    }
}