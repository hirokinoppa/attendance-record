@extends('layouts.app2')

@section('title', '勤怠一覧')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
    <div class="attendance-list">
        <div class="attendance-list__inner">
            <h1 class="attendance-list__heading">勤怠一覧</h1>

            <div class="attendance-list__month-nav">
                <a
                    href="{{ route('attendance.list', ['month' => $previousMonth]) }}"
                    class="attendance-list__month-link"
                >
                    ← 前月
                </a>

                <form
                    action="{{ route('attendance.list') }}"
                    method="GET"
                    class="attendance-list__month-form"
                >
                    <label
                        class="attendance-list__month-picker"
                        onclick="document.getElementById('attendance-month-input').showPicker()"
                    >
                        <img
                            src="{{ asset('images/calendar-icon.jpeg') }}"
                            alt="calendar"
                            class="attendance-list__calendar-image"
                        >

                        <input
                            id="attendance-month-input"
                            type="month"
                            name="month"
                            value="{{ $currentMonth->format('Y-m') }}"
                            class="attendance-list__month-input"
                            onchange="this.form.submit()"
                        >
                    </label>
                </form>

                <a
                    href="{{ route('attendance.list', ['month' => $nextMonth]) }}"
                    class="attendance-list__month-link"
                >
                    翌月 →
                </a>
            </div>

            <div class="attendance-list__table-wrapper">
                <table class="attendance-list__table">
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
                                            href="{{ route('attendance.show', ['id' => $day['attendance_id']]) }}"
                                            class="attendance-list__detail-link"
                                        >
                                            詳細
                                        </a>
                                    @else
                                        <a
                                            href="{{ route('attendance.show_by_date', ['date' => $day['date_key']]) }}"
                                            class="attendance-list__detail-link"
                                        >
                                            詳細
                                        </a>
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