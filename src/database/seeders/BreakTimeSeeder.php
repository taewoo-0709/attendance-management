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

            $allBreaks = [];

            $firstBreakStart = $checkIn->copy()->addHours(4)->setMinutes(0);
            $firstBreakEnd   = $firstBreakStart->copy()->addMinutes(60);
            $allBreaks[] = ['start' => $firstBreakStart, 'end' => $firstBreakEnd];

            $extraBreaks = rand(0, 3);

            for ($i = 0; $i < $extraBreaks; $i++) {
                $tries = 0;

                do {
                    $tries++;

                    $extraStart = $checkIn->copy()->addHours(rand(1, $workDuration - 1))
                        ->setMinutes(rand(0, 59));
                    $extraEnd   = $extraStart->copy()->addMinutes(rand(10, 30));

                    if ($extraEnd->gt($checkOut)) continue;

                    $overlap = false;
                    foreach ($allBreaks as $b) {
                        if ($extraStart->lt($b['end']) && $extraEnd->gt($b['start'])) {
                            $overlap = true;
                            break;
                        }
                    }

                    if (!$overlap) {
                        $allBreaks[] = ['start' => $extraStart, 'end' => $extraEnd];
                        break;
                    }

                } while ($tries < 10);
            }

            usort($allBreaks, fn($a, $b) => $a['start']->timestamp <=> $b['start']->timestamp);

            foreach ($allBreaks as $b) {
                BreakTime::create([
                    'attendance_id'   => $attendance->id,
                    'break_start_time'=> $b['start'],
                    'break_end_time'  => $b['end'],
                ]);
            }
        }
    }
}