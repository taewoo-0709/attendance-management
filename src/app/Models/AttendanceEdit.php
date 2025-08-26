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

    protected $casts = [
        'after_check_in'   => 'datetime',
        'after_break_start'=> 'datetime',
        'after_break_end'  => 'datetime',
        'after_check_out'  => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_id');
    }
}
