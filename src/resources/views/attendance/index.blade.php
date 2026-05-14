@extends('layouts.app2')

@section('title', '勤怠')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
    <div class="attendance">
        <div class="attendance__inner">
            <div class="attendance__status">
                {{ $status }}
            </div>

            <p class="attendance__date">
                {{ $currentDate }}
            </p>

            <p class="attendance__time">
                {{ $currentTime }}
            </p>

            <div class="attendance__button-wrapper">
                @if ($status === '勤務外')
                    <form action="{{ route('attendance.clock_in') }}" method="POST" class="attendance__form">
                        @csrf
                        <button type="submit" class="attendance__button attendance__button--primary">
                            出勤
                        </button>
                    </form>
                @elseif ($status === '出勤中')
                    <form action="{{ route('attendance.clock_out') }}" method="POST" class="attendance__form">
                        @csrf
                        <button type="submit" class="attendance__button attendance__button--primary">
                            退勤
                        </button>
                    </form>

                    <form action="{{ route('attendance.break_start') }}" method="POST" class="attendance__form">
                        @csrf
                        <button type="submit" class="attendance__button attendance__button--secondary">
                            休憩入
                        </button>
                    </form>
                @elseif ($status === '休憩中')
                    <form action="{{ route('attendance.break_end') }}" method="POST" class="attendance__form">
                        @csrf
                        <button type="submit" class="attendance__button attendance__button--secondary">
                            休憩戻
                        </button>
                    </form>
                @elseif ($status === '退勤済')
                    <p class="attendance__finished-message">お疲れ様でした。</p>
                @endif
            </div>
        </div>
    </div>
@endsection