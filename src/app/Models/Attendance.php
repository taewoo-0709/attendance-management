<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'work_date',
        'check_in_time',
        'check_out_time',
        'reason',
    ];

    protected $casts = [
        'work_date'      => 'date',
        'check_in_time'  => 'datetime',
        'check_out_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function isOnBreak()
    {
        $latestBreak = $this->breaks()->latest()->first();
        return $latestBreak && is_null($latestBreak->break_end_time);
    }

    public function edits()
    {
        return $this->hasMany(AttendanceEdit::class);
    }

    public function hasPendingEdit()
    {
        return $this->edits()->where('status', AttendanceEdit::STATUS_PENDING)->exists();
    }

    public function getTotalWorkSeconds(): ?int
    {
        if ($this->check_in_time && $this->check_out_time) {
            return $this->check_out_time->diffInSeconds($this->check_in_time);
        }
        return null;
    }

    public function getTotalBreakSeconds(): int
    {
        return $this->breaks->sum(function ($break) {
            if ($break->break_start_time && $break->break_end_time) {
                return $break->break_end_time->diffInSeconds($break->break_start_time);
            }
            return 0;
        });
    }

    public function getActualWorkSeconds(): ?int
    {
        $total = $this->getTotalWorkSeconds();
        return $total !== null ? $total - $this->getTotalBreakSeconds() : null;
    }

    private function formatSeconds(?int $seconds): ?string
    {
        if ($seconds === null) return null;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        return sprintf('%02d:%02d', $hours, $minutes);
    }

    public function getTotalWorkTimeAttribute()
    {
        return $this->formatSeconds($this->getTotalWorkSeconds());
    }

    public function getTotalBreakTimeAttribute(): string
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return '';
        }

        $validBreaks = $this->breaks->filter(function ($b) {
            return $b->break_start_time && $b->break_end_time;
        });

        if ($validBreaks->isEmpty()) {
            return '0:00';
        }

        $totalBreakSeconds = $validBreaks->sum(function ($b) {
            return $b->break_end_time->diffInSeconds($b->break_start_time);
        });

        return $totalBreakSeconds > 0 ? $this->formatSeconds($totalBreakSeconds) : '0:00';
    }

    public function getActualWorkTimeAttribute()
    {
        return $this->formatSeconds($this->getActualWorkSeconds());
    }
}
