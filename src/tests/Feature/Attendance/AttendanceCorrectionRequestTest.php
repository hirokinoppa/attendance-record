<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Models\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AttendanceCorrectionRequestTest extends TestCase
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

    public function test_出勤時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id . '/update', [
            'clock_in_at' => '19:00',
            'clock_out_at' => '18:00',
            'break_times' => [
                [
                    'start' => '12:00',
                    'end' => '13:00',
                ],
            ],
            'note' => '修正申請します',
        ]);

        $response->assertSessionHasErrors(['clock_in_at']);
    }

    public function test_休憩開始時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id . '/update', [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'break_times' => [
                [
                    'start' => '19:00',
                    'end' => '19:30',
                ],
            ],
            'note' => '修正申請します',
        ]);

        $response->assertSessionHasErrors(['break_times.0.start']);
    }

    public function test_休憩終了時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id . '/update', [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'break_times' => [
                [
                    'start' => '17:30',
                    'end' => '19:00',
                ],
            ],
            'note' => '修正申請します',
        ]);

        $response->assertSessionHasErrors(['break_times.0.end']);
    }

    public function test_備考欄が未入力の場合エラーメッセージが表示される()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id . '/update', [
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

    public function test_修正申請処理が実行される()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $response = $this->actingAs($user)->post('/attendance/detail/' . $attendance->id . '/update', [
            'clock_in_at' => '09:30',
            'clock_out_at' => '18:30',
            'break_times' => [
                [
                    'start' => '12:00',
                    'end' => '13:00',
                ],
            ],
            'note' => '電車遅延のため',
        ]);

        $this->assertDatabaseHas('attendance_correction_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'request_note' => '電車遅延のため',
            'status' => AttendanceCorrectionRequest::STATUS_PENDING,
        ]);
    }

    public function test_承認待ちにログインユーザーが行った申請が全て表示されている()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_at' => '2026-04-15 09:30:00',
            'requested_clock_out_at' => '2026-04-15 18:30:00',
            'request_note' => '電車遅延のため',
            'status' => AttendanceCorrectionRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list?status=' . AttendanceCorrectionRequest::STATUS_PENDING);

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('電車遅延のため');
        $response->assertSee('2026/04/15');
    }

    public function test_承認済みに管理者が承認した修正申請が全て表示されている()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_at' => '2026-04-15 09:30:00',
            'requested_clock_out_at' => '2026-04-15 18:30:00',
            'request_note' => '電車遅延のため',
            'status' => AttendanceCorrectionRequest::STATUS_APPROVED,
        ]);

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/list?status=' . AttendanceCorrectionRequest::STATUS_APPROVED);

        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee('電車遅延のため');
        $response->assertSee('2026/04/15');
    }

    public function test_各申請の詳細を押下すると勤怠詳細画面に遷移する()
    {
        $this->withoutMiddleware(EnsureEmailIsVerified::class);

        $user = $this->createUser();
        $attendance = $this->createAttendance($user);

        $correctionRequest = AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_at' => '2026-04-15 09:30:00',
            'requested_clock_out_at' => '2026-04-15 18:30:00',
            'request_note' => '電車遅延のため',
            'status' => AttendanceCorrectionRequest::STATUS_PENDING,
        ]);

        $response = $this->actingAs($user)
            ->get('/stamp_correction_request/' . $correctionRequest->id);

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee('テストユーザー');
        $response->assertSee('電車遅延のため');
    }
}