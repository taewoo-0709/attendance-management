<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceEditBreak extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_edit_id',
        'after_break_start_time',
        'after_break_end_time',
    ];

    public function attendanceEdit()
    {
        return $this->belongsTo(AttendanceEdit::class);
    }
}
