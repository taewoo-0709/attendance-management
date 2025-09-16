<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\AttendanceEdit;
use App\Http\Requests\AttendanceTimeRequest;

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
        $onBreak = $attendance
        ? $attendance->breaks()->whereNull('break_end_time')->exists()
        : false;

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
        $layout = auth()->user()->is_admin ? 'layouts.admin_nav' : 'layouts.staff_nav';
        $monthParam = $request->input('date');
        if (empty($monthParam)) {
            $monthParam = now()->format('Y-m');
        }

        $carbonDate = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();

        $month    = $carbonDate->format('Y-m');
        $prevDate = $carbonDate->copy()->subMonth()->format('Y-m');
        $nextDate = $carbonDate->copy()->addMonth()->format('Y-m');

        $targetUser = Auth::user();

        $attendances = $targetUser->attendances()
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
            'targetUser', 'days', 'month', 'prevDate', 'nextDate', 'layout'
        ));
    }

    public function edit(Request $request, $id)
    {
        $layout = auth()->user()->is_admin ? 'layouts.admin_nav' : 'layouts.staff_nav';
        $isDetailPage = true;

        $date = $request->query('date', now()->format('Y-m-d'));

        $attendance = Attendance::with(['user', 'breaks'])->find($id);

        if (!$attendance) {
            $attendance = new Attendance([
                'user_id' => Auth::id(),
                'work_date' => $date,
                'check_in_time' => null,
                'check_out_time' => null,
                'reason' => null,
            ]);
            $attendance->user = auth()->user();
            $attendance->breaks = collect();
            $attendanceId = null;
        } else {
            $attendanceId = $attendance->id;
        }

        $pendingEditQuery = AttendanceEdit::with('editBreaks')
            ->where('status', AttendanceEdit::STATUS_PENDING)
            ->where('attendance_id', $attendanceId);

        if (!auth()->user()->is_admin) {
            $pendingEditQuery->where('requested_id', Auth::id());
        }

        $pendingEdit = $pendingEditQuery->latest('created_at')->first();

        return view('detail', compact('attendance', 'layout', 'date', 'isDetailPage', 'pendingEdit'));
    }

    public function requestEdit(AttendanceTimeRequest $request, $id = null)
    {
        $attendance = Attendance::find($id);

        if (!$attendance) {
            $attendance = Attendance::create([
                'user_id' => Auth::id(),
                'work_date' => $request->input('work_date'),
                'check_in_time' => null,
                'check_out_time' => null,
                'reason' => null,
            ]);
        }

        $checkIn = $request->input('check_in_time')
            ? $attendance->work_date->format('Y-m-d') . ' ' . $request->input('check_in_time') . ':00'
            : null;

        $checkOut = $request->input('check_out_time')
            ? $attendance->work_date->format('Y-m-d') . ' ' . $request->input('check_out_time') . ':00'
            : null;

        $attendanceEdit = AttendanceEdit::create([
            'attendance_id'   => $attendance->id ?? null,
            'requested_id'    => Auth::id(),
            'after_check_in'  => $checkIn,
            'after_check_out' => $checkOut,
            'reason'          => $request->input('reason'),
            'status'          => AttendanceEdit::STATUS_PENDING,
        ]);

        if ($request->has('breaks')) {
            foreach ($request->input('breaks') as $break) {
                if (!empty($break['start']) || !empty($break['end'])) {
                    $breakStart = !empty($break['start'])
                        ? $attendance->work_date->format('Y-m-d') . ' ' . $break['start'] . ':00'
                        : null;
                    $breakEnd = !empty($break['end'])
                        ? $attendance->work_date->format('Y-m-d') . ' ' . $break['end'] . ':00'
                        : null;

                    $attendanceEdit->editBreaks()->create([
                        'after_break_start_time' => $breakStart,
                        'after_break_end_time'   => $breakEnd,
                    ]);
                }
            }
        }
        $pendingEdit = AttendanceEdit::with('editBreaks')
            ->where('attendance_id', $attendance->id)
            ->where('status', AttendanceEdit::STATUS_PENDING)
            ->where('requested_id', Auth::id())
            ->latest('created_at')
            ->first();

        $layout = auth()->user()->is_admin ? 'layouts.admin_nav' : 'layouts.staff_nav';
        $date = $attendance->work_date->format('Y-m-d');
        $isDetailPage = true;

        return view('detail', compact('attendance', 'layout', 'date', 'isDetailPage', 'pendingEdit'))
            ->with('success', '修正を申請しました。');
    }
}