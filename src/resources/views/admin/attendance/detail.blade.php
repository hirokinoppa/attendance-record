@extends('layouts.app3')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin-attendance-detail.css') }}">
@endsection

@section('content')
    <div class="admin-detail">
        <div class="admin-detail__inner">
            <h1 class="admin-detail__heading">勤怠詳細</h1>

            <form
                action="{{ route('admin.attendance.update', ['id' => $attendanceId]) }}"
                method="POST"
                class="js-admin-detail-form"
            >
                @csrf

                <div class="admin-detail__card">
                    <div class="admin-detail__row">
                        <div class="admin-detail__label">名前</div>

                        <div class="admin-detail__value">
                            <div class="admin-detail__value-grid admin-detail__value-grid--single">
                                <span>{{ $userName }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="admin-detail__row">
                        <div class="admin-detail__label">日付</div>

                        <div class="admin-detail__value">
                            <div class="admin-detail__value-grid">
                                <span>{{ $workYear }}</span>
                                <span></span>
                                <span>{{ $workDate }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="admin-detail__row">
                        <div class="admin-detail__label">出勤・退勤</div>

                        <div class="admin-detail__value admin-detail__value--column">
                            <div class="admin-detail__value-grid">
                                @if ($isPending)
                                    <span class="admin-detail__plain-time">{{ $clockInAt }}</span>
                                    <span class="admin-detail__separator">〜</span>
                                    <span class="admin-detail__plain-time">{{ $clockOutAt }}</span>
                                @else
                                    <input
                                        type="text"
                                        name="clock_in_at"
                                        value="{{ old('clock_in_at', $clockInAt) }}"
                                        placeholder="09:00"
                                        class="admin-detail__input js-time-input"
                                        inputmode="numeric"
                                        autocomplete="off"
                                    >

                                    <span class="admin-detail__separator">〜</span>

                                    <input
                                        type="text"
                                        name="clock_out_at"
                                        value="{{ old('clock_out_at', $clockOutAt) }}"
                                        placeholder="18:00"
                                        class="admin-detail__input js-time-input"
                                        inputmode="numeric"
                                        autocomplete="off"
                                    >
                                @endif
                            </div>

                            @error('clock_in_at')
                                <p class="admin-detail__error">{{ $message }}</p>
                            @enderror

                            @error('clock_out_at')
                                <p class="admin-detail__error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @foreach ($isPending ? $breakTimes : old('break_times', $breakTimes) as $index => $breakTime)
                        <div class="admin-detail__row">
                            <div class="admin-detail__label">
                                {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                            </div>

                            <div class="admin-detail__value admin-detail__value--column">
                                <div class="admin-detail__value-grid">
                                    @if ($isPending)
                                        <span class="admin-detail__plain-time">
                                            {{ $breakTime['start'] ?? '' }}
                                        </span>

                                        <span class="admin-detail__separator">〜</span>

                                        <span class="admin-detail__plain-time">
                                            {{ $breakTime['end'] ?? '' }}
                                        </span>
                                    @else
                                        <input
                                            type="text"
                                            name="break_times[{{ $index }}][start]"
                                            value="{{ $breakTime['start'] ?? '' }}"
                                            placeholder="12:00"
                                            class="admin-detail__input js-time-input"
                                            inputmode="numeric"
                                            autocomplete="off"
                                        >

                                        <span class="admin-detail__separator">〜</span>

                                        <input
                                            type="text"
                                            name="break_times[{{ $index }}][end]"
                                            value="{{ $breakTime['end'] ?? '' }}"
                                            placeholder="13:00"
                                            class="admin-detail__input js-time-input"
                                            inputmode="numeric"
                                            autocomplete="off"
                                        >
                                    @endif
                                </div>

                                @error("break_times.$index.start")
                                    <p class="admin-detail__error">{{ $message }}</p>
                                @enderror

                                @error("break_times.$index.end")
                                    <p class="admin-detail__error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endforeach

                    <div class="admin-detail__row">
                        <div class="admin-detail__label">備考</div>

                        <div class="admin-detail__value admin-detail__value--column">
                            <div class="admin-detail__value-grid admin-detail__value-grid--single">
                                @if ($isPending)
                                    <div class="admin-detail__plain-note">
                                        {{ $note }}
                                    </div>
                                @else
                                    <textarea
                                        name="note"
                                        class="admin-detail__textarea"
                                    >{{ old('note', $note) }}</textarea>
                                @endif
                            </div>

                            @error('note')
                                <p class="admin-detail__error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                @if ($isPending)
                    <p class="admin-detail__message">
                        ＊承認待ちのため修正はできません。
                    </p>
                @else
                    <div class="admin-detail__button-wrapper">
                        <button type="submit" class="admin-detail__button">
                            修正
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const timeInputs = document.querySelectorAll('.js-time-input');
            const form = document.querySelector('.js-admin-detail-form');

            function convertToHalfWidth(value) {
                return value.replace(/[０-９]/g, function (character) {
                    return String.fromCharCode(character.charCodeAt(0) - 0xFEE0);
                });
            }

            function formatTime(input) {
                const rawValue = convertToHalfWidth(input.value.trim());

                if (rawValue === '') {
                    return;
                }

                if (/^\d{1,2}:\d{2}$/.test(rawValue)) {
                    input.value = rawValue;
                    return;
                }

                const numberOnly = rawValue.replace(/[^0-9]/g, '');

                if (numberOnly.length === 3) {
                    const hour = numberOnly.slice(0, 1).padStart(2, '0');
                    const minute = numberOnly.slice(1, 3);

                    input.value = `${hour}:${minute}`;
                    return;
                }

                if (numberOnly.length === 4) {
                    const hour = numberOnly.slice(0, 2);
                    const minute = numberOnly.slice(2, 4);

                    input.value = `${hour}:${minute}`;
                }
            }

            timeInputs.forEach(function (input) {
                input.addEventListener('blur', function () {
                    formatTime(input);
                });
            });

            if (form) {
                form.addEventListener('submit', function () {
                    timeInputs.forEach(function (input) {
                        formatTime(input);
                    });
                });
            }
        });
    </script>
@endsection