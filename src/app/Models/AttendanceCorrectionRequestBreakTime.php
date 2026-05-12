<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectionRequestBreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_correction_request_id',
        'break_start_at',
        'break_end_at',
    ];

    protected $casts = [
        'break_start_at' => 'datetime',
        'break_end_at' => 'datetime',
    ];

    public function correctionRequest()
    {
        return $this->belongsTo(
            AttendanceCorrectionRequest::class,
            'attendance_correction_request_id'
        );
    }
}