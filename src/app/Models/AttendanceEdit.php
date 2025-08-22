<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceEdit extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'requested_id',
        'after_check_id',
        'after_break_start',
        'after_break_end',
        'after_check_out',
        'approved_id',
        'status',
    ];
}
