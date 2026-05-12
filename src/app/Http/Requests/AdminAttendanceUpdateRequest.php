<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in_at' => ['nullable', 'date_format:H:i'],
            'clock_out_at' => ['nullable', 'date_format:H:i'],
            'break_times.*.start' => ['nullable', 'date_format:H:i'],
            'break_times.*.end' => ['nullable', 'date_format:H:i'],
            'note' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in_at.date_format' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out_at.date_format' => '出勤時間もしくは退勤時間が不適切な値です',

            'break_times.*.start.date_format' => '休憩時間が不適切な値です',
            'break_times.*.end.date_format' => '休憩時間もしくは退勤時間が不適切な値です',

            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockInAt = $this->input('clock_in_at');
            $clockOutAt = $this->input('clock_out_at');
            $breakTimes = $this->input('break_times', []);

            if ($clockInAt && $clockOutAt && $clockInAt >= $clockOutAt) {
                $validator->errors()->add(
                    'clock_in_at',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            foreach ($breakTimes as $index => $breakTime) {
                $breakStart = $breakTime['start'] ?? null;
                $breakEnd = $breakTime['end'] ?? null;

                if ($breakStart && $clockInAt && $breakStart < $clockInAt) {
                    $validator->errors()->add(
                        "break_times.$index.start",
                        '休憩時間が不適切な値です'
                    );
                }

                if ($breakStart && $clockOutAt && $breakStart > $clockOutAt) {
                    $validator->errors()->add(
                        "break_times.$index.start",
                        '休憩時間が不適切な値です'
                    );
                }

                if ($breakEnd && $clockOutAt && $breakEnd > $clockOutAt) {
                    $validator->errors()->add(
                        "break_times.$index.end",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }

                if ($breakStart && $breakEnd && $breakStart > $breakEnd) {
                    $validator->errors()->add(
                        "break_times.$index.end",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }
            }
        });
    }
}