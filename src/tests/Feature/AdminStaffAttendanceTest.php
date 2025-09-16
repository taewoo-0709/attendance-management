<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminStaffAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'is_admin' => true,
        ]);
    }

    /** @test */
    public function admin_can_view_staff_list_with_name_and_email()
    {
        $staffA = User::factory()->create([
            'is_admin' => false,
            'name' => 'Staff A',
            'email' => 'staffA@example.com',
        ]);
        $staffB = User::factory()->create([
            'is_admin' => false,
            'name' => 'Staff B',
            'email' => 'staffB@example.com',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.staff.list'))
            ->assertStatus(200)
            ->assertSee('Staff A')
            ->assertSee('staffA@example.com')
            ->assertSee('Staff B')
            ->assertSee('staffB@example.com');
    }

    /** @test */
    public function admin_can_view_selected_staff_attendances()
    {
        $staff = User::factory()->create(['is_admin' => false]);

        $attendance = Attendance::factory()->create([
            'user_id' => $staff->id,
            'work_date' => '2025-09-09',
            'check_in_time' => '2025-09-09 09:00:00',
            'check_out_time' => '2025-09-09 18:00:00',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.attendance.staff', ['id' => $staff->id, 'date' => '2025-09']))
            ->assertStatus(200)
            ->assertSee($staff->name)
            ->assertSee('09:00')
            ->assertSee('18:00')
            ->assertSee('2025/09/09');
    }

    /** @test */
    public function prev_month_button_shows_previous_month_attendances()
    {
        $staff = User::factory()->create(['is_admin' => false]);

        $attendanceAug = Attendance::factory()->create([
            'user_id' => $staff->id,
            'work_date' => '2025-08-15',
            'check_in_time' => '2025-08-15 09:00:00',
            'check_out_time' => '2025-08-15 17:00:00',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.attendance.staff', ['id' => $staff->id, 'date' => '2025-08']))
            ->assertStatus(200)
            ->assertSee('2025/08/15')
            ->assertSee('09:00')
            ->assertSee('17:00');
    }

    /** @test */
    public function next_month_button_shows_next_month_attendances()
    {
        $staff = User::factory()->create(['is_admin' => false]);

        $attendanceOct = Attendance::factory()->create([
            'user_id' => $staff->id,
            'work_date' => '2025-10-20',
            'check_in_time' => '2025-10-20 10:00:00',
            'check_out_time' => '2025-10-20 19:00:00',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.attendance.staff', ['id' => $staff->id, 'date' => '2025-10']))
            ->assertStatus(200)
            ->assertSee('2025/10/20')
            ->assertSee('10:00')
            ->assertSee('19:00');
    }

    /** @test */
    public function clicking_detail_opens_attendance_detail_for_admin()
    {
        $staff = User::factory()->create(['is_admin' => false]);

        $attendance = Attendance::factory()->create([
            'user_id' => $staff->id,
            'work_date' => '2025-09-11',
            'check_in_time' => '2025-09-11 09:30:00',
            'check_out_time' => '2025-09-11 18:30:00',
        ]);

        $this->actingAs($this->admin)
        ->get(route('admin.attendance.edit', ['id' => $attendance->id, 'date' => $attendance->work_date]))
        ->assertStatus(200)
        ->assertSee($staff->name)
        ->assertSee('09:30')
        ->assertSee('18:30')
        ->assertSee('2025年')
        ->assertSee('09月11日');
    }
}