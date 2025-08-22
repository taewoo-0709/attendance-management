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
    ];

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }
}
