<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_shows_status_as_off_duty_when_no_attendance_exists()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('attendance'))
            ->assertSee('勤務外');
    }

    /** @test */
    public function it_shows_status_as_working_when_checked_in()
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'check_in_time' => now()->subHours(2),
            'check_out_time' => null,
        ]);

        $this->actingAs($user)
            ->get(route('attendance'))
            ->assertSee('出勤中');
    }

    /** @test */
    public function it_shows_status_as_on_break_when_break_started_and_not_ended()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'check_in_time' => now()->subHours(4),
            'check_out_time' => null,
        ]);

        $attendance->breaks()->create([
            'break_start_time' => now()->subHour(),
            'break_end_time' => null,
        ]);

        $this->actingAs($user)
            ->get(route('attendance'))
            ->assertSee('休憩中');
    }

    /** @test */
    public function it_shows_status_as_checked_out_when_check_out_time_exists()
    {
        $user = User::factory()->create();
        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'check_in_time' => now()->subHours(8),
            'check_out_time' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('attendance'))
            ->assertSee('退勤済');
    }
}
