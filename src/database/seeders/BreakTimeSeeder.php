<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BreakTimeSeeder extends Seeder
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
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {
            $checkIn  = Carbon::parse($attendance->check_in_time);
            $checkOut = Carbon::parse($attendance->check_out_time);

            if (!$checkIn || !$checkOut) {
                continue;
            }

            $workDuration = $checkIn->diffInHours($checkOut);

            if ($workDuration < 6) {
                continue;
            }

            BreakTime::create([
                'attendance_id'   => $attendance->id,
                'break_start_time'=> $attendance->work_date->copy()->setTime(12, 0),
                'break_end_time'  => $attendance->work_date->copy()->setTime(13, 0),
            ]);

            $extraBreaks = rand(0, 2);

            for ($i = 0; $i < $extraBreaks; $i++) {
                $extraStart = $checkIn->copy()->addHours(rand(2, $workDuration - 2))->setMinutes(rand(0, 59));
                $extraEnd   = $extraStart->copy()->addMinutes(rand(10, 20));

                if ($extraEnd->lt($checkOut)) {
                    BreakTime::create([
                        'attendance_id'   => $attendance->id,
                        'break_start_time'=> $extraStart,
                        'break_end_time'  => $extraEnd,
                    ]);
                }
            }
        }
    }
}
