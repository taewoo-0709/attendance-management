<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceEdit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailUpdateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_shows_error_if_check_in_is_after_check_out()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'check_in_time' => '09:00',
            'check_out_time' => '18:00',
        ]);

        $this->actingAs($user)
            ->post(route('attendance.requestEdit', $attendance->id), [
                'check_in_time' => '20:00',
                'check_out_time' => '18:00',
                'reason' => 'テスト修正',
            ])
            ->assertSessionHasErrors('check_in_time');
    }

    /** @test */
    public function it_shows_error_if_break_start_is_after_check_out()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'check_in_time' => '09:00',
            'check_out_time' => '18:00',
        ]);

        $this->actingAs($user)
            ->post(route('attendance.requestEdit', $attendance->id), [
                'check_in_time' => '09:00',
                'check_out_time' => '18:00',
                'breaks' => [
                    ['start' => '19:00', 'end' => '19:30'],
                ],
                'reason' => 'テスト修正',
            ])
            ->assertSessionHasErrors('breaks.0');
    }

    /** @test */
    public function it_shows_error_if_break_end_is_after_check_out()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'check_in_time' => '09:00',
            'check_out_time' => '18:00',
        ]);

        $this->actingAs($user)
            ->post(route('attendance.requestEdit', $attendance->id), [
                'check_in_time' => '09:00',
                'check_out_time' => '18:00',
                'breaks' => [
                    ['start' => '17:00', 'end' => '19:00'],
                ],
                'reason' => 'テスト修正',
            ])
            ->assertSessionHasErrors('breaks.0');
    }

    /** @test */
    public function it_requires_reason_when_submitting_edit()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('attendance.requestEdit', $attendance->id), [
                'check_in_time' => '09:00',
                'check_out_time' => '18:00',
                'reason' => '',
            ])
            ->assertSessionHasErrors('reason');
    }

    /** @test */
    public function it_creates_edit_request_when_staff_submits_update()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('attendance.requestEdit', $attendance->id), [
                'check_in_time' => '09:30',
                'check_out_time' => '18:30',
                'reason' => '修正申請テスト',
            ]);

        $this->assertDatabaseHas('attendance_edits', [
            'attendance_id' => $attendance->id,
            'status' => 0,
            'reason' => '修正申請テスト',
        ]);
    }

    /** @test */
    public function it_shows_pending_edits_in_request_list()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        AttendanceEdit::factory()->create([
            'attendance_id' => $attendance->id,
            'requested_id'  => $user->id,
            'status'        => 0,
            'reason'        => '承認待ちテスト',
        ]);

        $this->actingAs($user)
            ->get(route('attendance.request.list'))
            ->assertSee('承認待ちテスト');
    }

    /** @test */
    public function it_shows_approved_edits_in_request_list()
    {
        $user = User::factory()->create();
        AttendanceEdit::factory()->create([
            'attendance_id' => Attendance::factory()->create(['user_id' => $user->id])->id,
            'requested_id' => $user->id,
            'status' => 1,
            'reason' => '承認済みテスト',
        ]);

        $this->actingAs($user)
            ->get(route('attendance.request.list', ['status' => 1]))
            ->assertSee('承認済みテスト');
    }

    /** @test */
    public function clicking_request_detail_redirects_to_attendance_detail()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        AttendanceEdit::factory()->create([
            'attendance_id' => $attendance->id,
            'requested_id' => $user->id,
            'status' => 0,
        ]);

        $this->actingAs($user)
            ->get(route('attendance.detail', $attendance->id))
            ->assertStatus(200)
            ->assertSee($attendance->work_date->format('Y年'))
            ->assertSee($attendance->work_date->format('m月d日'));
    }
}
