<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\AttendanceEdit;
use App\Models\AttendanceEditBreak;

class StaffController extends Controller
{
    public function show()
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

    public function index(Request $request)
    {
        $monthParam = $request->input('date', now()->format('Y-m'));

        $carbonDate = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();

        $month    = $carbonDate->format('Y-m');
        $prevDate = $carbonDate->copy()->subMonth()->format('Y-m');
        $nextDate = $carbonDate->copy()->addMonth()->format('Y-m');

        $user = Auth::user();

        $attendances = $user->attendances()
            ->whereBetween('work_date', [
                $carbonDate->copy()->startOfMonth(),
                $carbonDate->copy()->endOfMonth(),
            ])
            ->with('breaks')
            ->get()
            ->keyBy(fn ($att) => $att->work_date->format('Y-m-d'));

        $days = [];
        $cursor = $carbonDate->copy();
        while ($cursor->lte($carbonDate->copy()->endOfMonth())) {
            $dateStr = $cursor->format('Y-m-d');
            $days[] = [
                'date'       => $dateStr,
                'attendance' => $attendances->get($dateStr),
            ];
            $cursor->addDay();
        }

        return view('user_attendance_index', compact(
            'user', 'days', 'month', 'prevDate', 'nextDate'
        ));
    }

    public function edit($id)
    {
        $attendance = Attendance::with(['user', 'breaks'])->findOrFail($id);
        return view('detail', compact('attendance'));
    }

    public function requestEdit(Request $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        AttendanceEdit::create([
            'attendance_id' => $attendance->id,
            'requested_id' => Auth::id(),
            'check_in_time' => $request->input('check_in_time'),
            'check_out_time'=> $request->input('check_out_time'),
            'status'        => AttendanceEdit::STATUS_PENDING,
        ]);

        if ($request->has('breaks')) {
        foreach ($request->input('breaks') as $break) {
            if (!empty($break['start']) || !empty($break['end'])) {
                AttendanceEditBreak::create([
                    'attendance_edit_id' => $attendanceEdit->id,
                    'break_start_time'   => $break['start'],
                    'break_end_time'     => $break['end'],
                    'status'             => AttendanceEdit::STATUS_PENDING,
                ]);
            }
        }
    }
    return back()->with('success', '修正を申請しました。');
    }
}