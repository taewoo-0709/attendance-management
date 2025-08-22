<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'after_break_start',
        'after_break_end',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}