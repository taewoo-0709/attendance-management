<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    /** @test */
    public function it_displays_selected_attendance_detail()
    {
        $attendance = Attendance::factory()->create([
            'check_in_time'  => now()->setTime(9, 0),
            'check_out_time' => now()->setTime(18, 0),
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.attendance.edit', ['id' => $attendance->id, 'date' => $attendance->work_date]))
            ->assertStatus(200)
            ->assertSee($attendance->user->name)
            ->assertSee('09:00')
            ->assertSee('18:00');
    }

    /** @test */
    public function it_shows_error_if_check_in_is_after_check_out()
    {
        $attendance = Attendance::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.attendance.update', $attendance->id), [
                'work_date'       => $attendance->work_date->format('Y-m-d'),
                'check_in_time'   => '20:00',
                'check_out_time'  => '09:00',
                'reason'          => 'テスト理由',
            ])
            ->assertSessionHasErrors(['check_in_time']);
    }

    /** @test */
    public function it_shows_error_if_break_start_is_after_check_out()
    {
        $attendance = Attendance::factory()->create([
            'check_out_time' => now()->setTime(18, 0),
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.attendance.update', $attendance->id), [
                'work_date' => $attendance->work_date->format('Y-m-d'),
                'check_in_time' => '09:00',
                'check_out_time' => '18:00',
                'breaks' => [
                ['start' => '19:00', 'end' => null],
                ],
                'reason' => 'テスト理由',
            ])
            ->assertSessionHasErrors(['breaks.0']);
    }

    /** @test */
    public function it_shows_error_if_break_end_is_after_check_out()
    {
        $attendance = Attendance::factory()->create([
            'check_out_time' => now()->setTime(18, 0),
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.attendance.update', $attendance->id), [
                'work_date' => $attendance->work_date->format('Y-m-d'),
                'check_in_time' => '09:00',
                'check_out_time' => '18:00',
                'breaks' => [
                ['start' => '10:00', 'end' => '19:00'],
                ],
                'reason' => 'テスト理由',
            ])
            ->assertSessionHasErrors(['breaks.0']);
    }

    /** @test */
    public function it_shows_error_if_reason_is_empty()
    {
        $attendance = Attendance::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.attendance.update', $attendance->id), [
                'work_date'      => $attendance->work_date->format('Y-m-d'),
                'check_in_time'  => '09:00',
                'check_out_time' => '18:00',
                'reason'         => '',
            ])
            ->assertSessionHasErrors(['reason']);
    }
}