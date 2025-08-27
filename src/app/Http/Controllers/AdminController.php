<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));

        $users = User::where('is_admin', 0)
            ->with(['attendances' => function ($q) use ($date) {
                $q->whereDate('work_date', $date)->with('breaks');
            }])->get();

        $attendances = $users->map(function ($user) {
            $attendance = $user->attendances->first();

        if (!$attendance) {
            $attendance = new Attendance([
                'work_date' => null,
                'check_in_time' => null,
                'check_out_time' => null,
            ]);
            $attendance->setRelation('breaks', collect());
        }

        if ($attendance->breaks->isNotEmpty()) {
            $totalBreakSeconds = $attendance->breaks->sum(function ($b) {
                return $b->break_start_time && $b->break_end_time
                    ? $b->break_end_time->diffInSeconds($b->break_start_time)
                    : 0;
            });
            $attendance->break_time = $totalBreakSeconds ? gmdate('H:i', $totalBreakSeconds) : '0:00';
        } else {
            $attendance->break_time = '';
        }

        if ($attendance->check_in_time && $attendance->check_out_time) {
            $workSeconds = $attendance->check_out_time->diffInSeconds($attendance->check_in_time) - ($attendance->breaks->sum(fn($b) => ($b->break_start_time && $b->break_end_time) ? $b->break_end_time->diffInSeconds($b->break_start_time) : 0));
            $attendance->total_time = gmdate('H:i', $workSeconds);
        } else {
            $attendance->total_time = '';
        }

        $attendance->user = $user;

        return $attendance;
    });


        return view('admin_index', [
            'attendances' => $attendances,
            'date' => $date,
            'prevDate' => date('Y-m-d', strtotime($date .' -1 day')),
            'nextDate' => date('Y-m-d', strtotime($date .' +1 day')),
        ]);
    }

    public function show($id)
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            $attendance = new Attendance([
                'work_date' => null,
                'check_in_time' => null,
                'check_out_time' => null,
            ]);

            $attendance->user = User::find(request()->query('user_id'));
            $attendance->breaks = collect();
        } else {
            $attendance->load('user', 'breaks');
        }

        return view('admin_attendance_show', compact('attendance'));
    }
}