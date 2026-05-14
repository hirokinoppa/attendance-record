<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminAttendanceUpdateRequest;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Models\BreakTime;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function attendanceList(Request $request)
    {
        $currentDate = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();

        $users = User::query()
            ->where('role', 'general')
            ->orderBy('id')
            ->get();

        $attendances = Attendance::query()
            ->with(['breakTimes' => function ($query) {
                $query->orderBy('break_start_at');
            }])
            ->whereDate('work_date', $currentDate->toDateString())
            ->get()
            ->keyBy('user_id');

        $attendanceRows = $users->map(function ($user) use ($attendances) {
            $attendance = $attendances->get($user->id);

            $breakMinutes = 0;

            if ($attendance) {
                foreach ($attendance->breakTimes as $breakTime) {
                    if ($breakTime->break_start_at && $breakTime->break_end_at) {
                        $breakMinutes += Carbon::parse($breakTime->break_start_at)
                            ->diffInMinutes(Carbon::parse($breakTime->break_end_at));
                    }
                }
            }

            $workMinutes = null;

            if ($attendance && $attendance->clock_in_at && $attendance->clock_out_at) {
                $totalMinutes = Carbon::parse($attendance->clock_in_at)
                    ->diffInMinutes(Carbon::parse($attendance->clock_out_at));

                $workMinutes = $totalMinutes - $breakMinutes;
            }

            return [
                'user_name' => $user->name,
                'attendance_id' => $attendance?->id,
                'clock_in_at' => $attendance && $attendance->clock_in_at
                    ? Carbon::parse($attendance->clock_in_at)->format('H:i')
                    : '',
                'clock_out_at' => $attendance && $attendance->clock_out_at
                    ? Carbon::parse($attendance->clock_out_at)->format('H:i')
                    : '',
                'break_time' => $breakMinutes > 0
                    ? sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60)
                    : '',
                'work_time' => !is_null($workMinutes)
                    ? sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60)
                    : '',
            ];
        });

        return view('admin.attendance.list', [
            'currentDate' => $currentDate,
            'previousDate' => $currentDate->copy()->subDay()->toDateString(),
            'nextDate' => $currentDate->copy()->addDay()->toDateString(),
            'attendanceRows' => $attendanceRows,
        ]);
    }

    public function attendanceDetail($id)
    {
        $attendance = Attendance::query()
            ->with(['user', 'breakTimes' => function ($query) {
                $query->orderBy('break_start_at');
            }])
            ->findOrFail($id);

        $pendingRequest = AttendanceCorrectionRequest::query()
            ->with(['breakTimes' => function ($query) {
                $query->orderBy('break_start_at');
            }])
            ->where('attendance_id', $attendance->id)
            ->where('status', AttendanceCorrectionRequest::STATUS_PENDING)
            ->latest()
            ->first();

        if ($pendingRequest) {
            $breakTimes = $pendingRequest->breakTimes->map(function ($breakTime) {
                return [
                    'start' => $breakTime->break_start_at
                        ? Carbon::parse($breakTime->break_start_at)->format('H:i')
                        : '',
                    'end' => $breakTime->break_end_at
                        ? Carbon::parse($breakTime->break_end_at)->format('H:i')
                        : '',
                ];
            })->values()->toArray();

            return view('admin.attendance.detail', [
                'attendanceId' => $attendance->id,
                'userName' => $attendance->user->name,
                'workYear' => Carbon::parse($attendance->work_date)->format('Y年'),
                'workDate' => Carbon::parse($attendance->work_date)->isoFormat('M月D日'),
                'clockInAt' => $pendingRequest->requested_clock_in_at
                    ? Carbon::parse($pendingRequest->requested_clock_in_at)->format('H:i')
                    : '',
                'clockOutAt' => $pendingRequest->requested_clock_out_at
                    ? Carbon::parse($pendingRequest->requested_clock_out_at)->format('H:i')
                    : '',
                'breakTimes' => $breakTimes,
                'note' => $pendingRequest->request_note ?? '',
                'isPending' => true,
            ]);
        }

        $breakTimes = $attendance->breakTimes->map(function ($breakTime) {
            return [
                'start' => $breakTime->break_start_at
                    ? Carbon::parse($breakTime->break_start_at)->format('H:i')
                    : '',
                'end' => $breakTime->break_end_at
                    ? Carbon::parse($breakTime->break_end_at)->format('H:i')
                    : '',
            ];
        })->values()->toArray();

        $breakTimes[] = [
            'start' => '',
            'end' => '',
        ];

        return view('admin.attendance.detail', [
            'attendanceId' => $attendance->id,
            'userName' => $attendance->user->name,
            'workYear' => Carbon::parse($attendance->work_date)->format('Y年'),
            'workDate' => Carbon::parse($attendance->work_date)->isoFormat('M月D日'),
            'clockInAt' => $attendance->clock_in_at
                ? Carbon::parse($attendance->clock_in_at)->format('H:i')
                : '',
            'clockOutAt' => $attendance->clock_out_at
                ? Carbon::parse($attendance->clock_out_at)->format('H:i')
                : '',
            'breakTimes' => $breakTimes,
            'note' => $attendance->note ?? '',
            'isPending' => false,
        ]);
    }

    public function attendanceUpdate(AdminAttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::query()
            ->with('breakTimes')
            ->findOrFail($id);

        $pendingRequestExists = AttendanceCorrectionRequest::query()
            ->where('attendance_id', $attendance->id)
            ->where('status', AttendanceCorrectionRequest::STATUS_PENDING)
            ->exists();

        if ($pendingRequestExists) {
            return redirect()
                ->route('admin.attendance.detail', ['id' => $attendance->id])
                ->with('error', '承認待ちのため修正はできません。');
        }

        DB::transaction(function () use ($request, $attendance) {
            $workDate = Carbon::parse($attendance->work_date)->toDateString();

            $attendance->update([
                'clock_in_at' => $request->clock_in_at
                    ? Carbon::parse($workDate . ' ' . $request->clock_in_at)
                    : null,
                'clock_out_at' => $request->clock_out_at
                    ? Carbon::parse($workDate . ' ' . $request->clock_out_at)
                    : null,
                'note' => $request->note,
            ]);

            $attendance->breakTimes()->delete();

            foreach ($request->input('break_times', []) as $breakTime) {
                $breakStart = $breakTime['start'] ?? null;
                $breakEnd = $breakTime['end'] ?? null;

                if (!$breakStart && !$breakEnd) {
                    continue;
                }

                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start_at' => $breakStart
                        ? Carbon::parse($workDate . ' ' . $breakStart)
                        : null,
                    'break_end_at' => $breakEnd
                        ? Carbon::parse($workDate . ' ' . $breakEnd)
                        : null,
                ]);
            }
        });

        return redirect()
            ->route('admin.attendance.detail', ['id' => $attendance->id])
            ->with('success', '勤怠情報を修正しました。');
    }

    public function staffList()
    {
        $staffs = User::query()
            ->where('role', 'general')
            ->orderBy('id')
            ->get(['id', 'name', 'email']);

        return view('admin.staff.list', [
            'staffs' => $staffs,
        ]);
    }

    public function staffAttendance(Request $request, $id)
    {
        $staff = User::query()
            ->where('role', 'general')
            ->findOrFail($id);

        $currentMonth = $request->input('month')
            ? Carbon::createFromFormat('Y-m', $request->input('month'))->startOfMonth()
            : Carbon::now()->startOfMonth();

        $days = $this->makeStaffAttendanceDays($staff->id, $currentMonth);

        return view('admin.staff.attendance', [
            'staff' => $staff,
            'days' => $days,
            'currentMonth' => $currentMonth,
            'previousMonth' => $currentMonth->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $currentMonth->copy()->addMonth()->format('Y-m'),
        ]);
    }

    public function staffAttendanceCsv(Request $request, $id): StreamedResponse
    {
        $staff = User::query()
            ->where('role', 'general')
            ->findOrFail($id);

        $currentMonth = $request->input('month')
            ? Carbon::createFromFormat('Y-m', $request->input('month'))->startOfMonth()
            : Carbon::now()->startOfMonth();

        $days = $this->makeStaffAttendanceDays($staff->id, $currentMonth);

        $fileName = 'attendance_' . $staff->id . '_' . $currentMonth->format('Y_m') . '.csv';

        return response()->streamDownload(function () use ($days) {
            $handle = fopen('php://output', 'w');

            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                '日付',
                '出勤',
                '退勤',
                '休憩',
                '合計',
            ]);

            foreach ($days as $day) {
                fputcsv($handle, [
                    $day['date'],
                    $day['clock_in_at'],
                    $day['clock_out_at'],
                    $day['break_time'],
                    $day['work_time'],
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function staffAttendanceDetailByDate($id, $date)
    {
        $staff = User::query()
            ->where('role', 'general')
            ->findOrFail($id);

        $workDate = Carbon::parse($date)->toDateString();

        $attendance = Attendance::firstOrCreate(
            [
                'user_id' => $staff->id,
                'work_date' => $workDate,
            ],
            [
                'clock_in_at' => null,
                'clock_out_at' => null,
                'note' => '',
            ]
        );

        return redirect()->route('admin.attendance.detail', [
            'id' => $attendance->id,
        ]);
    }

    private function makeStaffAttendanceDays(int $staffId, Carbon $currentMonth): array
    {
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::query()
            ->with(['breakTimes' => function ($query) {
                $query->orderBy('break_start_at');
            }])
            ->where('user_id', $staffId)
            ->whereBetween('work_date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString(),
            ])
            ->orderBy('work_date')
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->work_date)->toDateString();
            });

        $days = [];
        $period = CarbonPeriod::create($startOfMonth, $endOfMonth);

        foreach ($period as $date) {
            $dateKey = $date->toDateString();
            $attendance = $attendances->get($dateKey);

            $breakMinutes = 0;

            if ($attendance) {
                foreach ($attendance->breakTimes as $breakTime) {
                    if ($breakTime->break_start_at && $breakTime->break_end_at) {
                        $breakMinutes += Carbon::parse($breakTime->break_start_at)
                            ->diffInMinutes(Carbon::parse($breakTime->break_end_at));
                    }
                }
            }

            $workMinutes = null;

            if ($attendance && $attendance->clock_in_at && $attendance->clock_out_at) {
                $totalMinutes = Carbon::parse($attendance->clock_in_at)
                    ->diffInMinutes(Carbon::parse($attendance->clock_out_at));

                $workMinutes = $totalMinutes - $breakMinutes;
            }

            $days[] = [
                'date' => $date->isoFormat('MM/DD(ddd)'),
                'date_key' => $dateKey,
                'attendance_id' => $attendance?->id,
                'clock_in_at' => $attendance && $attendance->clock_in_at
                    ? Carbon::parse($attendance->clock_in_at)->format('H:i')
                    : '',
                'clock_out_at' => $attendance && $attendance->clock_out_at
                    ? Carbon::parse($attendance->clock_out_at)->format('H:i')
                    : '',
                'break_time' => $breakMinutes > 0
                    ? sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60)
                    : '',
                'work_time' => !is_null($workMinutes)
                    ? sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60)
                    : '',
            ];
        }

        return $days;
    }
}