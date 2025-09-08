<?php

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_formats_total_and_actual_work_time_correctly()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::parse('2025-09-08'),
            'check_in_time' => Carbon::parse('2025-09-08 09:00:00'),
            'check_out_time' => Carbon::parse('2025-09-08 18:00:00'),
        ]);

        $attendance->breaks()->create([
            'break_start_time' => Carbon::parse('2025-09-08 12:00:00'),
            'break_end_time' => Carbon::parse('2025-09-08 14:00:00'),
        ]);

        $this->assertEquals('09:00', $attendance->total_work_time);
        $this->assertEquals('02:00', $attendance->total_break_time);
        $this->assertEquals('07:00', $attendance->actual_work_time);
    }

    /** @test */
    public function it_returns_null_or_empty_when_check_in_or_out_is_missing()
    {
        $attendance = Attendance::factory()->create([
            'check_in_time' => Carbon::parse('2025-09-08 09:00:00'),
            'check_out_time' => null,
        ]);

        $this->assertNull($attendance->total_work_time);
        $this->assertEquals('', $attendance->total_break_time);
        $this->assertNull($attendance->actual_work_time);
    }
}