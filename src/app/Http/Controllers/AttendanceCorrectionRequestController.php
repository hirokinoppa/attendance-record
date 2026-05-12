<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrectionRequest;
use App\Models\BreakTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceCorrectionRequestController extends Controller
{
    public function list(Request $request)
    {
        $status = $request->input('status', AttendanceCorrectionRequest::STATUS_PENDING);

        $requests = AttendanceCorrectionRequest::query()
            ->with(['attendance', 'user'])
            ->where('user_id', Auth::id())
            ->where('status', $status)
            ->latest()
            ->get()
            ->map(function ($correctionRequest) {
                return [
                    'id' => $correctionRequest->id,
                    'status' => $correctionRequest->status === AttendanceCorrectionRequest::STATUS_PENDING
                        ? '承認待ち'
                        : '承認済み',
                    'user_name' => $correctionRequest->user->name,
                    'target_date' => $correctionRequest->attendance
                        ? Carbon::parse($correctionRequest->attendance->work_date)->format('Y/m/d')
                        : '',
                    'reason' => $correctionRequest->request_note,
                    'requested_at' => $correctionRequest->created_at
                        ? Carbon::parse($correctionRequest->created_at)->format('Y/m/d')
                        : '',
                ];
            });

        return view('stamp_correction_request.list', [
            'requests' => $requests,
            'currentStatus' => $status,
        ]);
    }

    public function show($id)
    {
        $correctionRequest = AttendanceCorrectionRequest::query()
            ->with(['attendance', 'user', 'breakTimes'])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $breakTimes = $correctionRequest->breakTimes->map(function ($breakTime) {
            return [
                'start' => $breakTime->break_start_at
                    ? Carbon::parse($breakTime->break_start_at)->format('H:i')
                    : '',
                'end' => $breakTime->break_end_at
                    ? Carbon::parse($breakTime->break_end_at)->format('H:i')
                    : '',
            ];
        });

        return view('stamp_correction_request.show', [
            'status' => $correctionRequest->status,
            'statusLabel' => $correctionRequest->status === AttendanceCorrectionRequest::STATUS_PENDING
                ? '承認待ち'
                : '承認済み',
            'userName' => $correctionRequest->user->name,
            'workYear' => $correctionRequest->attendance
                ? Carbon::parse($correctionRequest->attendance->work_date)->format('Y年')
                : '',
            'workDate' => $correctionRequest->attendance
                ? Carbon::parse($correctionRequest->attendance->work_date)->isoFormat('M月D日')
                : '',
            'clockInAt' => $correctionRequest->requested_clock_in_at
                ? Carbon::parse($correctionRequest->requested_clock_in_at)->format('H:i')
                : '',
            'clockOutAt' => $correctionRequest->requested_clock_out_at
                ? Carbon::parse($correctionRequest->requested_clock_out_at)->format('H:i')
                : '',
            'breakTimes' => $breakTimes,
            'note' => $correctionRequest->request_note ?? '',
        ]);
    }

    public function adminList(Request $request)
    {
        $status = $request->input('status', AttendanceCorrectionRequest::STATUS_PENDING);

        $requests = AttendanceCorrectionRequest::query()
            ->with(['attendance', 'user'])
            ->where('status', $status)
            ->latest()
            ->get()
            ->map(function ($correctionRequest) {
                return [
                    'id' => $correctionRequest->id,
                    'status' => $correctionRequest->status === AttendanceCorrectionRequest::STATUS_PENDING
                        ? '承認待ち'
                        : '承認済み',
                    'user_name' => $correctionRequest->user->name,
                    'target_date' => $correctionRequest->attendance
                        ? Carbon::parse($correctionRequest->attendance->work_date)->format('Y/m/d')
                        : '',
                    'reason' => $correctionRequest->request_note,
                    'requested_at' => $correctionRequest->created_at
                        ? Carbon::parse($correctionRequest->created_at)->format('Y/m/d')
                        : '',
                ];
            });

        return view('admin.stamp_correction_request.list', [
            'requests' => $requests,
            'currentStatus' => $status,
        ]);
    }

    public function adminShow($id)
    {
        $correctionRequest = AttendanceCorrectionRequest::query()
            ->with(['attendance', 'user', 'breakTimes'])
            ->findOrFail($id);

        $breakTimes = $correctionRequest->breakTimes->map(function ($breakTime) {
            return [
                'start' => $breakTime->break_start_at
                    ? Carbon::parse($breakTime->break_start_at)->format('H:i')
                    : '',
                'end' => $breakTime->break_end_at
                    ? Carbon::parse($breakTime->break_end_at)->format('H:i')
                    : '',
            ];
        });

        return view('admin.stamp_correction_request.show', [
            'requestId' => $correctionRequest->id,
            'status' => $correctionRequest->status,
            'statusLabel' => $correctionRequest->status === AttendanceCorrectionRequest::STATUS_PENDING
                ? '承認待ち'
                : '承認済み',
            'userName' => $correctionRequest->user->name,
            'workYear' => $correctionRequest->attendance
                ? Carbon::parse($correctionRequest->attendance->work_date)->format('Y年')
                : '',
            'workDate' => $correctionRequest->attendance
                ? Carbon::parse($correctionRequest->attendance->work_date)->isoFormat('M月D日')
                : '',
            'clockInAt' => $correctionRequest->requested_clock_in_at
                ? Carbon::parse($correctionRequest->requested_clock_in_at)->format('H:i')
                : '',
            'clockOutAt' => $correctionRequest->requested_clock_out_at
                ? Carbon::parse($correctionRequest->requested_clock_out_at)->format('H:i')
                : '',
            'breakTimes' => $breakTimes,
            'note' => $correctionRequest->request_note ?? '',
        ]);
    }

    public function approve($id)
    {
        $correctionRequest = AttendanceCorrectionRequest::query()
            ->with(['attendance.user', 'breakTimes'])
            ->findOrFail($id);

        $breakTimes = $correctionRequest->breakTimes->map(function ($breakTime) {
            return [
                'start' => $breakTime->break_start_at
                    ? Carbon::parse($breakTime->break_start_at)->format('H:i')
                    : '',
                'end' => $breakTime->break_end_at
                    ? Carbon::parse($breakTime->break_end_at)->format('H:i')
                    : '',
            ];
        });

        return view('admin.stamp_correction_request.approve', [
            'requestId' => $correctionRequest->id,
            'status' => $correctionRequest->status,
            'statusLabel' => $correctionRequest->status === AttendanceCorrectionRequest::STATUS_PENDING
                ? '承認待ち'
                : '承認済み',
            'userName' => $correctionRequest->attendance->user->name,
            'workYear' => Carbon::parse($correctionRequest->attendance->work_date)->format('Y年'),
            'workDate' => Carbon::parse($correctionRequest->attendance->work_date)->isoFormat('M月D日'),
            'clockInAt' => $correctionRequest->requested_clock_in_at
                ? Carbon::parse($correctionRequest->requested_clock_in_at)->format('H:i')
                : '',
            'clockOutAt' => $correctionRequest->requested_clock_out_at
                ? Carbon::parse($correctionRequest->requested_clock_out_at)->format('H:i')
                : '',
            'breakTimes' => $breakTimes,
            'note' => $correctionRequest->request_note ?? '',
        ]);
    }

    public function approveUpdate($id)
    {
        $correctionRequest = AttendanceCorrectionRequest::query()
            ->with(['attendance.breakTimes', 'breakTimes'])
            ->findOrFail($id);

        if ($correctionRequest->status === AttendanceCorrectionRequest::STATUS_APPROVED) {
            return redirect()
                ->route('admin.stamp_correction_request.approve', ['id' => $correctionRequest->id]);
        }

        DB::transaction(function () use ($correctionRequest) {
            $attendance = $correctionRequest->attendance;

            $attendance->update([
                'clock_in_at' => $correctionRequest->requested_clock_in_at,
                'clock_out_at' => $correctionRequest->requested_clock_out_at,
                'note' => $correctionRequest->request_note,
            ]);

            $attendance->breakTimes()->delete();

            foreach ($correctionRequest->breakTimes as $breakTime) {
                if (!$breakTime->break_start_at && !$breakTime->break_end_at) {
                    continue;
                }

                BreakTime::create([
                    'attendance_id' => $attendance->id,
                    'break_start_at' => $breakTime->break_start_at,
                    'break_end_at' => $breakTime->break_end_at,
                ]);
            }

            $correctionRequest->update([
                'status' => AttendanceCorrectionRequest::STATUS_APPROVED,
            ]);
        });

        return redirect()
            ->route('admin.stamp_correction_request.approve', ['id' => $correctionRequest->id]);
    }
}