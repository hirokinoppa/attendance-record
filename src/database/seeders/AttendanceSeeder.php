<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceCorrectionRequestBreakTime;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        $users = User::where('role', 'general')->get();

        foreach ($users as $user) {
            for ($day = 1; $day <= 30; $day++) {
                $workDate = Carbon::create(2026, 4, $day);

                if ($workDate->isWeekend()) {
                    continue;
                }

                $clockIn = Carbon::create(2026, 4, $day, rand(8, 10), rand(0, 1) ? 0 : 30);
                $clockOut = (clone $clockIn)->addHours(rand(8, 10));

                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $workDate->toDateString(),
                    'clock_in_at' => $clockIn,
                    'clock_out_at' => $clockOut,
                    'note' => '通常勤務',
                ]);

                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start_at' => Carbon::create(2026, 4, $day, 12, 0),
                    'break_end_at' => Carbon::create(2026, 4, $day, 13, 0),
                ]);

                if (rand(1, 100) <= 40) {
                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'break_start_at' => Carbon::create(2026, 4, $day, 15, 0),
                        'break_end_at' => Carbon::create(2026, 4, $day, 15, 15),
                    ]);
                }

                if (rand(1, 100) <= 20) {
                    $correctionRequest = AttendanceCorrectionRequest::create([
                        'attendance_id' => $attendance->id,
                        'user_id' => $user->id,
                        'requested_clock_in_at' => (clone $clockIn)->addMinutes(15),
                        'requested_clock_out_at' => (clone $clockOut),
                        'request_note' => '電車遅延のため',
                        'status' => rand(0, 1)
                            ? AttendanceCorrectionRequest::STATUS_PENDING
                            : AttendanceCorrectionRequest::STATUS_APPROVED,
                    ]);

                    AttendanceCorrectionRequestBreakTime::create([
                        'attendance_correction_request_id' => $correctionRequest->id,
                        'break_start_at' => Carbon::create(2026, 4, $day, 12, 0),
                        'break_end_at' => Carbon::create(2026, 4, $day, 13, 0),
                    ]);
                }
            }
        }
    }
}