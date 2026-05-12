<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceCorrectionRequestBreakTime;
use App\Models\BreakTime;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * 勤怠打刻画面表示
     */
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::query()
            ->with('breakTimes')
            ->where('user_id', $user->id)
            ->where('work_date', $today->toDateString())
            ->first();

        $status = '勤務外';

        if ($attendance) {
            $latestBreakTime = $attendance->breakTimes->last();

            $isClockedIn = !is_null($attendance->clock_in_at);
            $isClockedOut = !is_null($attendance->clock_out_at);
            $isBreaking = $latestBreakTime && is_null($latestBreakTime->break_end_at);

            if ($isBreaking) {
                $status = '休憩中';
            } elseif ($isClockedIn && !$isClockedOut) {
                $status = '出勤中';
            } elseif ($isClockedOut) {
                $status = '退勤済';
            }
        }

        return view('attendance.index', [
            'currentDate' => $today->isoFormat('YYYY年M月D日(ddd)'),
            'currentTime' => now()->format('H:i'),
            'status' => $status,
        ]);
    }

    /**
     * 出勤処理
     */
    public function clockIn()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::query()
            ->where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        if ($attendance && !is_null($attendance->clock_in_at)) {
            return redirect()->route('attendance.index');
        }

        if (!$attendance) {
            Attendance::create([
                'user_id' => $user->id,
                'work_date' => $today,
                'clock_in_at' => now(),
            ]);
        } else {
            $attendance->update([
                'clock_in_at' => now(),
            ]);
        }

        return redirect()->route('attendance.index');
    }

    /**
     * 退勤処理
     */
    public function clockOut()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::query()
            ->with('breakTimes')
            ->where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        if (!$attendance || is_null($attendance->clock_in_at)) {
            return redirect()->route('attendance.index');
        }

        if (!is_null($attendance->clock_out_at)) {
            return redirect()->route('attendance.index');
        }

        $latestBreakTime = $attendance->breakTimes->last();

        if ($latestBreakTime && is_null($latestBreakTime->break_end_at)) {
            return redirect()->route('attendance.index');
        }

        $attendance->update([
            'clock_out_at' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    /**
     * 休憩開始処理
     */
    public function breakStart()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::query()
            ->with('breakTimes')
            ->where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        if (!$attendance || is_null($attendance->clock_in_at) || !is_null($attendance->clock_out_at)) {
            return redirect()->route('attendance.index');
        }

        $latestBreakTime = $attendance->breakTimes->last();

        if ($latestBreakTime && is_null($latestBreakTime->break_end_at)) {
            return redirect()->route('attendance.index');
        }

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    /**
     * 休憩終了処理
     */
    public function breakEnd()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::query()
            ->with('breakTimes')
            ->where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        if (!$attendance) {
            return redirect()->route('attendance.index');
        }

        $latestBreakTime = $attendance->breakTimes->last();

        if (!$latestBreakTime || !is_null($latestBreakTime->break_end_at)) {
            return redirect()->route('attendance.index');
        }

        $latestBreakTime->update([
            'break_end_at' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    /**
     * 勤怠一覧画面表示
     */
    public function list(Request $request)
    {
        $user = Auth::user();

        $currentMonth = $request->input('month')
            ? Carbon::createFromFormat('Y-m', $request->input('month'))->startOfMonth()
            : Carbon::now()->startOfMonth();

        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        $attendances = Attendance::query()
            ->with('breakTimes')
            ->where('user_id', $user->id)
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

        return view('attendance.list', [
            'days' => $days,
            'currentMonth' => $currentMonth,
            'previousMonth' => $currentMonth->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $currentMonth->copy()->addMonth()->format('Y-m'),
        ]);
    }

    /**
     * 勤怠詳細画面表示
     */
    public function show($id)
    {
        $user = Auth::user();

        $attendance = Attendance::query()
            ->with(['breakTimes' => function ($query) {
                $query->orderBy('break_start_at');
            }])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $pendingCorrectionRequest = AttendanceCorrectionRequest::query()
            ->where('attendance_id', $attendance->id)
            ->where('user_id', $user->id)
            ->where('status', AttendanceCorrectionRequest::STATUS_PENDING)
            ->latest()
            ->first();

        if ($pendingCorrectionRequest) {
            return redirect()->route('stamp_correction_request.show', [
                'id' => $pendingCorrectionRequest->id,
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

        return view('attendance.detail', [
            'attendanceId' => $attendance->id,
            'userName' => $user->name,
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
        ]);
    }

    /**
     * 修正申請処理
     */
    public function update(AttendanceUpdateRequest $request, $id)
    {
        $user = Auth::user();

        $attendance = Attendance::query()
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        DB::transaction(function () use ($request, $user, $attendance) {
            $workDate = Carbon::parse($attendance->work_date)->toDateString();

            $requestedClockInAt = $request->clock_in_at
                ? Carbon::parse($workDate . ' ' . $request->clock_in_at)
                : null;

            $requestedClockOutAt = $request->clock_out_at
                ? Carbon::parse($workDate . ' ' . $request->clock_out_at)
                : null;

            $correctionRequest = AttendanceCorrectionRequest::create([
                'user_id' => $user->id,
                'attendance_id' => $attendance->id,
                'requested_clock_in_at' => $requestedClockInAt,
                'requested_clock_out_at' => $requestedClockOutAt,
                'request_note' => $request->note,
                'status' => AttendanceCorrectionRequest::STATUS_PENDING,
            ]);

            foreach ($request->break_times ?? [] as $breakTime) {
                $breakStartAt = !empty($breakTime['start'])
                    ? Carbon::parse($workDate . ' ' . $breakTime['start'])
                    : null;

                $breakEndAt = !empty($breakTime['end'])
                    ? Carbon::parse($workDate . ' ' . $breakTime['end'])
                    : null;

                if (is_null($breakStartAt) && is_null($breakEndAt)) {
                    continue;
                }

                AttendanceCorrectionRequestBreakTime::create([
                    'attendance_correction_request_id' => $correctionRequest->id,
                    'break_start_at' => $breakStartAt,
                    'break_end_at' => $breakEndAt,
                ]);
            }
        });

        return redirect()
            ->route('stamp_correction_request.list')
            ->with('success', '修正申請を送信しました。');
    }
}