@extends('layouts.app2')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
    <div class="attendance-detail">
        <div class="attendance-detail__inner">
            <h1 class="attendance-detail__heading">勤怠詳細</h1>

            <div class="attendance-detail__card">

                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">名前</div>

                    <div class="attendance-detail__value">
                        <div class="attendance-detail__value-grid attendance-detail__value-grid--single">
                            <span>{{ $userName }}</span>
                        </div>
                    </div>
                </div>

                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">日付</div>

                    <div class="attendance-detail__value">
                        <div class="attendance-detail__value-grid attendance-detail__value-grid--date">
                            <span class="attendance-detail__date-year">
                                {{ $workYear }}
                            </span>

                            <span class="attendance-detail__date-day">
                                {{ $workDate }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">出勤・退勤</div>

                    <div class="attendance-detail__value">
                        <div class="attendance-detail__value-grid">
                            <span class="attendance-detail__plain-time">
                                {{ $clockInAt }}
                            </span>

                            <span class="attendance-detail__separator">〜</span>

                            <span class="attendance-detail__plain-time">
                                {{ $clockOutAt }}
                            </span>
                        </div>
                    </div>
                </div>

                @foreach ($breakTimes as $index => $breakTime)
                    <div class="attendance-detail__row">

                        <div class="attendance-detail__label">
                            {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                        </div>

                        <div class="attendance-detail__value">
                            <div class="attendance-detail__value-grid">
                                <span class="attendance-detail__plain-time">
                                    {{ $breakTime['start'] ?? '' }}
                                </span>

                                <span class="attendance-detail__separator">〜</span>

                                <span class="attendance-detail__plain-time">
                                    {{ $breakTime['end'] ?? '' }}
                                </span>
                            </div>
                        </div>

                    </div>
                @endforeach

                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">備考</div>

                    <div class="attendance-detail__value">
                        <div class="attendance-detail__value-grid attendance-detail__value-grid--single">

                            <span class="attendance-detail__note-text">
                                {{ $note }}
                            </span>

                        </div>
                    </div>
                </div>

            </div>

            @if ($status === \App\Models\AttendanceCorrectionRequest::STATUS_PENDING)
                <p class="attendance-detail__message">
                    ＊承認待ちのため修正はできません。
                </p>
            @else
                <p class="attendance-detail__message attendance-detail__message--approved">
                    ＊承認済みです。
                </p>
            @endif

        </div>
    </div>
@endsection