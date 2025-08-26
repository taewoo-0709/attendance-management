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
            ->map(function ($attendance) {

                // 休憩時間合計
                $totalBreakSeconds = $attendance->breaks->sum(function ($b) {
                    return $b->break_start_time && $b->break_end_time
                        ? $b->break_end_time->diffInSeconds($b->break_start_time)
                        : 0;
                });

                // 出退勤時間差（秒）
                $workSeconds = null;
                if ($attendance->check_in_time && $attendance->check_out_time) {
                    $workSeconds = $attendance->check_out_time->diffInSeconds($attendance->check_in_time) - $totalBreakSeconds;
                }

                // 表示用フォーマット
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

    public function show(Attendance $attendance)
    {
        $attendance->load('user', 'breaks');

        return view('admin.attendances.show', compact('attendance'));
    }
}