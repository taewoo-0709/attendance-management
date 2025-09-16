<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use App\Models\BreakTime;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        BreakTime::truncate();
        Attendance::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $users = User::whereIn('email', [
            'staff1@example.com',
            'staff2@example.com',
            'staff3@example.com',
            'staff4@example.com',
        ])->get();

        $today = Carbon::today();
        $startDate = Carbon::create(2025, 1, 1);

        foreach ($users as $user) {
            $date = $startDate->copy();
            for ($date = $startDate->copy(); $date->lte($today); $date->addDay()) {

                if ($date->toDateString() == $today->toDateString()) {
                    continue;
                }

                if (rand(1, 100) <= 25) {
                    continue;
                }

                $rand = rand(1, 100);

                if ($rand <= 60) {
                    $pattern = 1;
                } elseif ($rand <= 80) {
                    $pattern = 2;
                } else {
                    $pattern = 3;
                }

                switch ($pattern) {
                    case 1:
                        $checkIn  = $date->copy()->setTime(rand(8, 9), rand(0, 59));
                        $checkOut = $date->copy()->setTime(rand(17, 19), rand(0, 59));
                        break;

                    case 2:
                        $checkIn  = $date->copy()->setTime(rand(8, 9), rand(0, 59));
                        $checkOut = $checkIn->copy()->addHours(rand(4, 5));
                        break;

                    case 3:
                        $checkIn  = $date->copy()->setTime(rand(12, 13), rand(0, 59));
                        $checkOut = $checkIn->copy()->addHours(rand(4, 5));
                        break;
                }

                Attendance::updateOrCreate(
                    [
                        'user_id'   => $user->id,
                        'work_date' => $date->toDateString(),
                    ],
                    [
                        'check_in_time'  => $checkIn,
                        'check_out_time' => $checkOut,
                    ]
                );
            }
        }
    }
}