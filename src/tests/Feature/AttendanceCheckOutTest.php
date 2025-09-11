<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceCheckOutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function check_out_button_is_displayed_for_working_user()
    {
        $user = User::factory()->create();

        $checkInTime = Carbon::parse('2025-09-09 09:00');
        Carbon::setTestNow($checkInTime);
        $this->actingAs($user)->post(route('attendance.update'), ['action' => 'check_in']);

        $this->actingAs($user)
            ->get(route('attendance.list'))
            ->assertSee('退勤');

        $checkOutTime = Carbon::parse('2025-09-09 18:00');
        Carbon::setTestNow($checkOutTime);
        $this->actingAs($user)
            ->post(route('attendance.update'), ['action' => 'check_out'])
            ->assertStatus(302);
    }

    /** @test */
    public function check_out_time_is_displayed_in_attendance_index()
    {
        $user = User::factory()->create();

        $checkInTime = Carbon::parse('2025-09-09 09:00');
        Carbon::setTestNow($checkInTime);
        $this->actingAs($user)->post(route('attendance.update'), ['action' => 'check_in']);

        $checkOutTime = Carbon::parse('2025-09-09 18:00');
        Carbon::setTestNow($checkOutTime);
        $this->actingAs($user)->post(route('attendance.update'), ['action' => 'check_out']);

        $this->actingAs($user)
            ->get(route('attendance.list'))
            ->assertSee($checkOutTime->format('H:i'));
    }
}