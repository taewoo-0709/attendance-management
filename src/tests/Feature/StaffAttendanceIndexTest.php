<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class StaffAttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_see_all_their_attendances_in_index()
    {
        $user = User::factory()->create();

        $dates = ['2025-09-01', '2025-09-02', '2025-09-03'];
        foreach ($dates as $date) {
            Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $date,
                'check_in_time' => '09:00',
                'check_out_time' => '18:00',
            ]);
        }

        $response = $this->actingAs($user)->get(route('attendance.list'));

        foreach ($dates as $date) {
            $response->assertSee($date);
            $response->assertSee('09:00');
            $response->assertSee('18:00');
        }
    }

    /** @test */
    public function attendance_index_shows_current_month_by_default()
    {
        $user = User::factory()->create();

        Carbon::setTestNow('2025-09-09');
            $response = $this->actingAs($user)->get(route('attendance.list'));

        $currentMonth = now()->format('Y-m');
        $response->assertSee($currentMonth);
    }

    /** @test */
    public function clicking_previous_month_shows_previous_month_attendance()
    {
        $user = User::factory()->create();
        $prevMonth = '2025-08';

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-08-15',
            'check_in_time' => '09:00',
            'check_out_time' => '18:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list', ['date' => $prevMonth]));
        $response->assertSee('2025-08-15');
    }

    /** @test */
    public function clicking_next_month_shows_next_month_attendance()
    {
        $user = User::factory()->create();
        $nextMonth = '2025-10';

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-10-05',
            'check_in_time' => '09:00',
            'check_out_time' => '18:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list', ['date' => $nextMonth]));
        $response->assertSee('2025-10-05');
    }

    /** @test */
    public function clicking_detail_button_redirects_to_attendance_detail()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => '2025-09-09',
            'check_in_time' => '09:00',
            'check_out_time' => '18:00',
        ]);

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $detailUrl = route('attendance.detail', [
            'id' => $attendance->id,
            'user_id' => $user->id,
            'date' => '2025-09-09',
        ]);

        $response->assertSee($detailUrl);
    }
}
