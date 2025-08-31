<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceEdit extends Model
{
    use HasFactory;

    const STATUS_PENDING = 0;
    const STATUS_APPROVED = 1;

    protected $fillable = [
        'attendance_id',
        'requested_id',
        'after_check_in',
        'after_check_out',
        'approved_id',
        'reason',
        'status',
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

    public function editBreaks()
    {
    return $this->hasMany(AttendanceEditBreak::class);
    }
}
