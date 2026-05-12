@extends('layouts.app3')

@section('title', '管理者勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-attendance-list.css') }}">
@endsection

@section('content')
<div class="admin-attendance">
    <div class="admin-attendance__inner">

        <h1 class="admin-attendance__heading">
            {{ $currentDate->format('Y年n月j日') }}の勤怠
        </h1>

        <!-- 日付ナビ -->
        <div class="admin-attendance__date-nav">

            <!-- 前日 -->
            <a href="{{ route('admin.attendance.list', ['date' => $previousDate]) }}"
               class="admin-attendance__date-link">
                ← 前日
            </a>

            <!-- カレンダー（ここが今回のメイン） -->
            <form method="GET" action="{{ route('admin.attendance.list') }}">
                <label class="admin-attendance__date-picker"
                       onclick="document.getElementById('admin-date-input').showPicker()">

                    <img src="{{ asset('images/calendar-icon.jpeg') }}"
                         class="admin-attendance__calendar-image"
                         alt="calendar">

                    <input
                        id="admin-date-input"
                        type="date"
                        name="date"
                        value="{{ $currentDate->toDateString() }}"
                        class="admin-attendance__date-input"
                        onchange="this.form.submit()"
                    >
                </label>
            </form>

            <!-- 翌日 -->
            <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}"
               class="admin-attendance__date-link">
                翌日 →
            </a>
        </div>

        <!-- テーブル -->
        <div class="admin-attendance__table-wrapper">
            <table class="admin-attendance__table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($attendanceRows as $row)
                        <tr>
                            <td>{{ $row['user_name'] }}</td>
                            <td>{{ $row['clock_in_at'] }}</td>
                            <td>{{ $row['clock_out_at'] }}</td>
                            <td>{{ $row['break_time'] }}</td>
                            <td>{{ $row['work_time'] }}</td>
                            <td>
                                @if ($row['attendance_id'])
                                    <a href="{{ route('admin.attendance.detail', $row['attendance_id']) }}">
                                        詳細
                                    </a>
                                @else
                                    <span class="admin-attendance__detail-empty">詳細</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection