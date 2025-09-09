<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class StaffAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_logged_in_users_name_on_detail_page()
    {
        $user = User::factory()->create(['name' => '山田 太郎']);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-09-09',
            'check_in_time' => '2025-09-09 09:00:00',
            'check_out_time' => '2025-09-09 18:00:00',
        ]);

        $this->actingAs($user)
            ->get(route('attendance.detail', ['id' => $attendance->id, 'user_id' => $user->id, 'date' => '2025-09-09']))
            ->assertSee('山田 太郎');
    }

    /** @test */
    public function it_displays_selected_date_on_detail_page()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-09-09',
        ]);

        $this->actingAs($user)
            ->get(route('attendance.detail', ['id' => $attendance->id, 'user_id' => $user->id, 'date' => '2025-09-09']))
            ->assertSee('2025-09-09');
    }

    /** @test */
    public function it_displays_break_start_and_end_times_correctly()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-09-09',
        ]);

        $attendance->breaks()->create([
            'break_start_time' => Carbon::parse('2025-09-09 12:00:00'),
            'break_end_time'   => Carbon::parse('2025-09-09 12:45:00'),
        ]);

        $attendance->breaks()->create([
            'break_start_time' => Carbon::parse('2025-09-09 15:00:00'),
            'break_end_time'   => Carbon::parse('2025-09-09 15:15:00'),
        ]);

        $this->actingAs($user)
            ->get(route('attendance.detail', [
                'id' => $attendance->id,
                'user_id' => $user->id,
                'date' => '2025-09-09'
            ]))
            ->assertSee('12:00')
            ->assertSee('12:45')
            ->assertSee('15:00')
            ->assertSee('15:15');
    }

    /** @test */
    public function it_displays_break_times_correctly()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-09-09',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_time' => Carbon::parse('2025-09-09 12:00:00'),
            'break_end_time'   => Carbon::parse('2025-09-09 12:45:00'),
        ]);

        $this->actingAs($user)
            ->get(route('attendance.detail', ['id' => $attendance->id, 'user_id' => $user->id, 'date' => '2025-09-09']))
            ->assertSee('12:00')
            ->assertSee('12:45');
    }
}
