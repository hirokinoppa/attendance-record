@extends('layouts.app2')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
    <div class="attendance-detail">
        <div class="attendance-detail__inner">
            <h1 class="attendance-detail__heading">勤怠詳細</h1>

            <form
                action="{{ route('attendance.update', ['id' => $attendanceId]) }}"
                method="POST"
                class="js-attendance-detail-form"
            >
                @csrf

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
                            <div class="attendance-detail__value-grid">
                                <span>{{ $workYear }}</span>
                                <span></span>
                                <span>{{ $workDate }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="attendance-detail__row attendance-detail__row--with-error">
                        <div class="attendance-detail__label">出勤・退勤</div>

                        <div class="attendance-detail__value attendance-detail__value--column">
                            <div class="attendance-detail__value-grid">
                                <input
                                    type="text"
                                    name="clock_in_at"
                                    value="{{ old('clock_in_at', $clockInAt) }}"
                                    class="attendance-detail__input js-time-input"
                                    placeholder="09:00"
                                    inputmode="numeric"
                                    autocomplete="off"
                                >

                                <span class="attendance-detail__separator">〜</span>

                                <input
                                    type="text"
                                    name="clock_out_at"
                                    value="{{ old('clock_out_at', $clockOutAt) }}"
                                    class="attendance-detail__input js-time-input"
                                    placeholder="18:00"
                                    inputmode="numeric"
                                    autocomplete="off"
                                >
                            </div>

                            @error('clock_in_at')
                                <p class="attendance-detail__error">{{ $message }}</p>
                            @enderror

                            @error('clock_out_at')
                                <p class="attendance-detail__error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    @php
                        $displayBreakTimes = old('break_times', $breakTimes);

                        if (!is_array($displayBreakTimes)) {
                            $displayBreakTimes = [];
                        }

                        $displayBreakTimes[] = [
                            'start' => '',
                            'end' => '',
                        ];
                    @endphp

                    @foreach ($displayBreakTimes as $index => $breakTime)
                        <div class="attendance-detail__row attendance-detail__row--with-error">
                            <div class="attendance-detail__label">
                                {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                            </div>

                            <div class="attendance-detail__value attendance-detail__value--column">
                                <div class="attendance-detail__value-grid">
                                    <input
                                        type="text"
                                        name="break_times[{{ $index }}][start]"
                                        value="{{ $breakTime['start'] ?? '' }}"
                                        class="attendance-detail__input js-time-input"
                                        placeholder="12:00"
                                        inputmode="numeric"
                                        autocomplete="off"
                                    >

                                    <span class="attendance-detail__separator">〜</span>

                                    <input
                                        type="text"
                                        name="break_times[{{ $index }}][end]"
                                        value="{{ $breakTime['end'] ?? '' }}"
                                        class="attendance-detail__input js-time-input"
                                        placeholder="13:00"
                                        inputmode="numeric"
                                        autocomplete="off"
                                    >
                                </div>

                                @error("break_times.$index.start")
                                    <p class="attendance-detail__error">{{ $message }}</p>
                                @enderror

                                @error("break_times.$index.end")
                                    <p class="attendance-detail__error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endforeach

                    <div class="attendance-detail__row attendance-detail__row--with-error">
                        <div class="attendance-detail__label">備考</div>

                        <div class="attendance-detail__value attendance-detail__value--column">
                            <div class="attendance-detail__value-grid attendance-detail__value-grid--single">
                                <textarea
                                    name="note"
                                    class="attendance-detail__textarea"
                                >{{ old('note', $note) }}</textarea>
                            </div>

                            @error('note')
                                <p class="attendance-detail__error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="attendance-detail__button-wrapper">
                    <button type="submit" class="attendance-detail__button">
                        修正
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const timeInputs = document.querySelectorAll('.js-time-input');
            const form = document.querySelector('.js-attendance-detail-form');

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