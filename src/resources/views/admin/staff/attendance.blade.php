@extends('layouts.app3')

@section('title', 'スタッフ別勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin-staff-attendance.css') }}">
@endsection

@section('content')
    <div class="admin-staff-attendance">
        <div class="admin-staff-attendance__inner">
            <h1 class="admin-staff-attendance__heading">
                {{ $staff->name }}さんの勤怠
            </h1>

            <div class="admin-staff-attendance__month-nav">
                <a
                    href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $previousMonth]) }}"
                    class="admin-staff-attendance__month-link"
                >
                    ← 前月
                </a>

                <form
                    action="{{ route('admin.attendance.staff', ['id' => $staff->id]) }}"
                    method="GET"
                    class="admin-staff-attendance__month-form"
                >
                    <label
                        class="admin-staff-attendance__month-picker"
                        onclick="document.getElementById('staff-month-input').showPicker()"
                    >
                        <img
                            src="{{ asset('images/calendar-icon.jpeg') }}"
                            alt="calendar"
                            class="admin-staff-attendance__calendar-image"
                        >

                        <input
                            id="staff-month-input"
                            type="month"
                            name="month"
                            value="{{ $currentMonth->format('Y-m') }}"
                            class="admin-staff-attendance__month-input"
                            onchange="this.form.submit()"
                        >
                    </label>
                </form>

                <a
                    href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $nextMonth]) }}"
                    class="admin-staff-attendance__month-link"
                >
                    翌月 →
                </a>
            </div>

            <div class="admin-staff-attendance__table-wrapper">
                <table class="admin-staff-attendance__table">
                    <thead>
                        <tr>
                            <th>日付</th>
                            <th>出勤</th>
                            <th>退勤</th>
                            <th>休憩</th>
                            <th>合計</th>
                            <th>詳細</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($days as $day)
                            <tr>
                                <td>{{ $day['date'] }}</td>
                                <td>{{ $day['clock_in_at'] }}</td>
                                <td>{{ $day['clock_out_at'] }}</td>
                                <td>{{ $day['break_time'] }}</td>
                                <td>{{ $day['work_time'] }}</td>
                                <td>
                                    @if ($day['attendance_id'])
                                        <a
                                            href="{{ route('admin.attendance.detail', ['id' => $day['attendance_id']]) }}"
                                            class="admin-staff-attendance__detail-link"
                                        >
                                            詳細
                                        </a>
                                    @else
                                        <span class="admin-staff-attendance__detail-empty">詳細</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="admin-staff-attendance__button-wrapper">
                <a
                    href="{{ route('admin.attendance.staff.csv', [
                        'id' => $staff->id,
                        'month' => $currentMonth->format('Y-m'),
                    ]) }}"
                    class="admin-staff-attendance__csv-button"
                >
                    CSV出力
                </a>
            </div>
        </div>
    </div>
@endsection