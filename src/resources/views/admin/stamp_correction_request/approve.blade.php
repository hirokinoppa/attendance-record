@extends('layouts.app3')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin-request-approve.css') }}">
@endsection

@section('content')
    <div class="admin-request-approve">
        <div class="admin-request-approve__inner">
            <h1 class="admin-request-approve__heading">勤怠詳細</h1>

            <div class="admin-request-approve__card">
                <div class="admin-request-approve__row">
                    <div class="admin-request-approve__label">名前</div>
                    <div class="admin-request-approve__value">
                        <div class="admin-request-approve__content">
                            <span class="admin-request-approve__text">{{ $userName }}</span>
                        </div>
                    </div>
                </div>

                <div class="admin-request-approve__row">
                    <div class="admin-request-approve__label">日付</div>
                    <div class="admin-request-approve__value">
                        <div class="admin-request-approve__content admin-request-approve__content--time">
                            <span class="admin-request-approve__plain-time">{{ $workYear }}</span>
                            <span class="admin-request-approve__separator"></span>
                            <span class="admin-request-approve__plain-time">{{ $workDate }}</span>
                        </div>
                    </div>
                </div>

                <div class="admin-request-approve__row">
                    <div class="admin-request-approve__label">出勤・退勤</div>
                    <div class="admin-request-approve__value">
                        <div class="admin-request-approve__content admin-request-approve__content--time">
                            <span class="admin-request-approve__plain-time">{{ $clockInAt }}</span>
                            <span class="admin-request-approve__separator">〜</span>
                            <span class="admin-request-approve__plain-time">{{ $clockOutAt }}</span>
                        </div>
                    </div>
                </div>

                @foreach ($breakTimes as $index => $breakTime)
                    <div class="admin-request-approve__row">
                        <div class="admin-request-approve__label">
                            {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                        </div>

                        <div class="admin-request-approve__value">
                            <div class="admin-request-approve__content admin-request-approve__content--time">
                                <span class="admin-request-approve__plain-time">{{ $breakTime['start'] ?? '' }}</span>
                                <span class="admin-request-approve__separator">〜</span>
                                <span class="admin-request-approve__plain-time">{{ $breakTime['end'] ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach

                <div class="admin-request-approve__row">
                    <div class="admin-request-approve__label">備考</div>
                    <div class="admin-request-approve__value">
                        <div class="admin-request-approve__content">
                            <span class="admin-request-approve__note">{{ $note }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="admin-request-approve__button-wrapper">
                @if ($status === \App\Models\AttendanceCorrectionRequest::STATUS_PENDING)
                    <form
                        action="{{ route('admin.stamp_correction_request.approve.update', ['id' => $requestId]) }}"
                        method="POST"
                    >
                        @csrf

                        <button type="submit" class="admin-request-approve__button">
                            承認
                        </button>
                    </form>
                @else
                    <button
                        type="button"
                        class="admin-request-approve__button admin-request-approve__button--approved"
                        disabled
                    >
                        承認済み
                    </button>
                @endif
            </div>
        </div>
    </div>
@endsection