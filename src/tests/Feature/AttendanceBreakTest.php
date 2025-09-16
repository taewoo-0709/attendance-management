<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\BreakTime;

class AttendanceBreakTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function break_in_button_is_visible_and_works()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'check_in_time' => now()->setTime(9, 0),
            'check_out_time' => null,
        ]);

        $this->actingAs($user)
            ->get(route('attendance'))
            ->assertSee('休憩入');

        $this->actingAs($user)
            ->post(route('attendance.update'), ['action' => 'break_start']);

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
            'break_end_time' => null,
        ]);
    }

    /** @test */
    public function user_can_take_multiple_breaks_per_day()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'check_in_time' => now()->setTime(9, 0),
            'check_out_time' => null,
        ]);

        $this->actingAs($user)->post(route('attendance.update'), ['action' => 'break_start']);
        $this->actingAs($user)->post(route('attendance.update'), ['action' => 'break_end']);
        $this->actingAs($user)->post(route('attendance.update'), ['action' => 'break_start']);

        $this->actingAs($user)
            ->get(route('attendance'))
            ->assertSee('休憩戻');
    }

    /** @test */
    public function break_out_button_is_visible_and_works()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'check_in_time' => now()->setTime(9, 0),
            'check_out_time' => null,
        ]);

        $this->actingAs($user)
            ->post(route('attendance.update'), ['action' => 'break_start']);

        $this->actingAs($user)
            ->get(route('attendance'))
            ->assertSee('休憩戻');

        $this->actingAs($user)
            ->post(route('attendance.update'), ['action' => 'break_end']);

        $this->assertDatabaseHas('break_times', [
            'attendance_id' => $attendance->id,
        ]);
    }

    /** @test */
    public function user_can_return_from_break_multiple_times_per_day()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'check_in_time' => now()->setTime(9, 0),
            'check_out_time' => null,
        ]);

        $this->actingAs($user)->post(route('attendance.update'), ['action' => 'break_start']);
        $this->actingAs($user)->post(route('attendance.update'), ['action' => 'break_end']);
        $this->actingAs($user)->post(route('attendance.update'), ['action' => 'break_start']);

        $this->actingAs($user)
            ->get(route('attendance'))
            ->assertSee('休憩戻');
    }

    /** @test */
    public function break_times_are_displayed_in_attendance_index()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'check_in_time' => now()->setTime(9, 0),
            'check_out_time' => now()->setTime(18, 0),
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_time' => now()->setTime(12, 0),
            'break_end_time' => now()->setTime(12, 30),
        ]);

        $this->actingAs($user)
            ->get(route('attendance.list'))
            ->assertSee('00:30');
    }
}
