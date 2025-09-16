<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceEdit;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminAttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'is_admin' => true,
        ]);
    }

    /** @test */
    public function it_displays_pending_requests()
    {
        $staff = User::factory()->create(['is_admin' => false]);
        $edit1 = AttendanceEdit::factory()->create([
            'requested_id' => $staff->id,
            'status' => 0,
        ]);
        $edit2 = AttendanceEdit::factory()->create([
            'requested_id' => $staff->id,
            'status' => 0,
        ]);

        $this->actingAs($this->admin)
            ->get(route('attendance.request.list', ['status' => 0]))
            ->assertStatus(200)
            ->assertSee($edit1->reason)
            ->assertSee($edit2->reason);
    }

    /** @test */
    public function it_displays_approved_requests()
    {
        $staff = User::factory()->create(['is_admin' => false]);
        $edit1 = AttendanceEdit::factory()->approved()->create([
            'requested_id' => $staff->id,
        ]);
        $edit2 = AttendanceEdit::factory()->approved()->create([
            'requested_id' => $staff->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('attendance.request.list', ['status' => 1]))
            ->assertStatus(200)
            ->assertSee($edit1->reason)
            ->assertSee($edit2->reason);
    }

    /** @test */
    public function it_displays_request_detail()
    {
        $staff = User::factory()->create(['is_admin' => false]);

        $workDate = now()->format('Y-m-d');

        $attendance = Attendance::factory()->create([
            'user_id'       => $staff->id,
            'work_date'     => $workDate,
            'check_in_time' => "$workDate 09:00:00",
            'check_out_time'=> "$workDate 18:00:00",
        ]);

        $edit = AttendanceEdit::factory()->create([
            'attendance_id'   => $attendance->id,
            'requested_id'    => $staff->id,
            'after_check_in'  => "$workDate 09:30:00",
            'after_check_out' => "$workDate 18:30:00",
            'reason'          => '体調不良',
            'status'          => 0,
        ]);

        $this->actingAs($this->admin)
        ->get(route('admin.attendance.approveview', $edit->id))
        ->assertStatus(200)
        ->assertSee('09:30')
        ->assertSee('18:30')
        ->assertSee('体調不良');
    }

    /** @test */
    public function it_approves_request_correctly()
    {
        $staff = User::factory()->create(['is_admin' => false]);

        $workDate = now()->format('Y-m-d');

        $attendance = Attendance::factory()->create([
            'user_id'       => $staff->id,
            'work_date'     => $workDate,
            'check_in_time' => "$workDate 09:00:00",
            'check_out_time'=> "$workDate 18:00:00",
        ]);

        $edit = AttendanceEdit::factory()->create([
            'attendance_id'   => $attendance->id,
            'requested_id'    => $staff->id,
            'after_check_in'  => "$workDate 09:30:00",
            'after_check_out' => "$workDate 18:30:00",
            'reason'          => 'テスト承認',
            'status'          => 0,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.attendance.approve', $edit->id))
            ->assertRedirect(route('admin.attendance.approveview', $edit->id));

        $this->assertDatabaseHas('attendance_edits', [
            'id'     => $edit->id,
            'status' => 1,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id'             => $attendance->id,
            'check_in_time'  => "$workDate 09:30:00",
            'check_out_time' => "$workDate 18:30:00",
            'reason'         => 'テスト承認',
        ]);
    }
}