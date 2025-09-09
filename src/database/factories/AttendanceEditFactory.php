<?php

namespace Database\Factories;

use App\Models\AttendanceEdit;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceEditFactory extends Factory
{
    protected $model = AttendanceEdit::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'requested_id' => User::factory(),
            'after_check_in' => $this->faker->dateTimeBetween('today 09:00', 'today 10:00'),
            'after_check_out' => $this->faker->dateTimeBetween('today 18:00', 'today 19:00'),
            'reason' => $this->faker->sentence(),
            'status' => 0,
        ];
    }

    public function approved()
    {
        return $this->state(fn () => ['status' => 1]);
    }
}
