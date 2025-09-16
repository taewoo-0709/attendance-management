<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceCheckInTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_see_check_in_button_and_check_in_successfully()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('attendance'))
            ->assertSee('出勤');

        $this->actingAs($user)
            ->post(route('attendance.update'), [
                'action' => 'check_in',
            ])
            ->assertRedirect(route('attendance'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);
    }

    /** @test */
    public function user_cannot_check_in_more_than_once_per_day()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today()->toDateString(),
            'check_in_time' => now()->setTime(9, 0),
            'check_out_time' => now()->setTime(18, 0),
        ]);

        $this->actingAs($user)
            ->get(route('attendance'))
            ->assertDontSee('attendance-btn">出勤<');
    }

    /** @test */
    public function check_in_time_is_displayed_in_attendance_index()
    {
        $user = User::factory()->create();

        $fixedDate = Carbon::parse('2025-09-09 13:07');
        Carbon::setTestNow($fixedDate);

        $this->actingAs($user)
            ->post(route('attendance.update'), [
                'action' => 'check_in',
            ]);

        $expectedDate = now()->isoFormat('Y/m/d');

        $this->actingAs($user)
            ->get(route('attendance.list'))
            ->assertSee($fixedDate->format('H:i'));
    }
}
