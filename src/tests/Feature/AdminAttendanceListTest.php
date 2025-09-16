<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    /** @test */
    public function it_shows_all_attendances_of_the_day_for_admin()
    {
        $today = Carbon::today()->format('Y-m-d');
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user1->id,
            'work_date' => $today,
            'check_in_time' => '09:00',
            'check_out_time' => '18:00',
        ]);

        Attendance::factory()->create([
            'user_id' => $user2->id,
            'work_date' => $today,
            'check_in_time' => '10:00',
            'check_out_time' => '19:00',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.attendance.list', ['date' => $today]))
            ->assertSee($user1->name)
            ->assertSee($user2->name)
            ->assertSee('09:00')
            ->assertSee('19:00');
    }

    /** @test */
    public function it_shows_today_date_on_initial_load()
    {
        $today = Carbon::today()->format('Y-m-d');

        $this->actingAs($this->admin)
            ->get(route('admin.attendance.list'))
            ->assertSee("{$today}の勤怠");
    }

    /** @test */
    public function it_shows_previous_day_attendances_when_previous_button_clicked()
    {
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $yesterday,
            'check_in_time' => '08:30',
            'check_out_time' => '17:30',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.attendance.list', ['date' => $yesterday]))
            ->assertSee('08:30')
            ->assertSee("{$yesterday}の勤怠");
    }

    /** @test */
    public function it_shows_next_day_attendances_when_next_button_clicked()
    {
        $tomorrow = Carbon::tomorrow()->format('Y-m-d');
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => $tomorrow,
            'check_in_time' => '11:00',
            'check_out_time' => '20:00',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.attendance.list', ['date' => $tomorrow]))
            ->assertSee('11:00')
            ->assertSee("{$tomorrow}の勤怠");
    }
}