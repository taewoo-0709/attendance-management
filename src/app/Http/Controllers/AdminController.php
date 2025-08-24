<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));

        $attendances = Attendance::with('user', 'breaks')
            ->whereDate('work_date', $date)
            ->get()
            ->map(function($attendance) {

                $totalBreakSeconds = $attendance->breaks->sum(function($b){
                    return $b->break_start_time && $b->break_end_time
                        ? $b->break_end_time->diffInSeconds($b->break_start_time)
                        : 0;
                });

                $workSeconds = $attendance->check_in_time && $attendance->check_out_time
                    ? $attendance->check_in_time->diffInSeconds($attendance->check_out_time) - $totalBreakSeconds
                    : null;

                $attendance->break_time = gmdate('H:i', $totalBreakSeconds);
                $attendance->total_time = $workSeconds !== null ? gmdate('H:i', $workSeconds) : '-';

                return $attendance;
        });

        return view('admin_index', [
            'attendances' => $attendances,
            'date' => $date,
            'prevDate' => date('Y-m-d', strtotime($date .' -1 day')),
            'nextDate' => date('Y-m-d', strtotime($date .' +1 day')),
        ]);
    }
}
