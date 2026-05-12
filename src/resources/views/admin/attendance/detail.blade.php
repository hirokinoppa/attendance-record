@extends('layouts.app3')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin-attendance-detail.css') }}">
@endsection

@section('content')
    <div class="admin-detail">
        <div class="admin-detail__inner">
            <h1 class="admin-detail__heading">勤怠詳細</h1>

            <form action="{{ route('admin.attendance.update', ['id' => $attendanceId]) }}" method="POST">
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
                                    <input type="text" name="clock_in_at" value="{{ old('clock_in_at', $clockInAt) }}" class="admin-detail__input">
                                    <span class="admin-detail__separator">〜</span>
                                    <input type="text" name="clock_out_at" value="{{ old('clock_out_at', $clockOutAt) }}" class="admin-detail__input">
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
                                        <span class="admin-detail__plain-time">{{ $breakTime['start'] ?? '' }}</span>
                                        <span class="admin-detail__separator">〜</span>
                                        <span class="admin-detail__plain-time">{{ $breakTime['end'] ?? '' }}</span>
                                    @else
                                        <input type="text" name="break_times[{{ $index }}][start]" value="{{ $breakTime['start'] ?? '' }}" class="admin-detail__input">
                                        <span class="admin-detail__separator">〜</span>
                                        <input type="text" name="break_times[{{ $index }}][end]" value="{{ $breakTime['end'] ?? '' }}" class="admin-detail__input">
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
                                    <div class="admin-detail__plain-note">{{ $note }}</div>
                                @else
                                    <textarea name="note" class="admin-detail__textarea">{{ old('note', $note) }}</textarea>
                                @endif
                            </div>

                            @error('note')
                                <p class="admin-detail__error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                @if ($isPending)
                    <p class="admin-detail__message">＊承認待ちのため修正はできません。</p>
                @else
                    <div class="admin-detail__button-wrapper">
                        <button type="submit" class="admin-detail__button">修正</button>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection