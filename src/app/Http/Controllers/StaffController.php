<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', now()->toDateString())
            ->first();

        $now = now()->locale('ja');
        $dateStr = $now->isoFormat('YYYY年M月D日 (ddd)');
        $timeStr = $now->format('H:i');

        $latestBreak = $attendance ? $attendance->breaks()->latest()->first() : null;
        $onBreak = $latestBreak && !$latestBreak->break_end_time;

        return view('attendance', compact('attendance', 'dateStr', 'timeStr', 'onBreak'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $action = $request->input('action');

        $todayAttendance = Attendance::where('user_id', $user->id)
        ->where('work_date', now()->toDateString())
        ->first();

        $attendance = Attendance::where('user_id', $user->id)->latest()->first();

        switch ($action) {
            case 'check_in':
                if (!$todayAttendance) {
                    Attendance::create([
                        'user_id'       => $user->id,
                        'work_date'     => now()->toDateString(),
                        'check_in_time' => now(),
                    ]);
                }
                break;

            case 'check_out':
                if ($todayAttendance) {
                    $todayAttendance->update(['check_out_time' => now()]);
                }
                break;

            case 'break_start':
                if ($todayAttendance) {
                    $todayAttendance->breaks()->create(['break_start_time' => now()]);
                }
                break;

            case 'break_end':
                if ($todayAttendance) {
                    $latestBreak = $todayAttendance->breaks()->latest()->first();
                    if ($latestBreak && is_null($latestBreak->break_end_time)) {
                        $latestBreak->update(['break_end_time' => now()]);
                    }
                }
                break;
        }

        return redirect()->route('attendance');
    }
}