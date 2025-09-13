<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AttendanceEdit;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\AttendanceTimeRequest;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;


class AdminController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));

        $users = User::where('is_admin', 0)
            ->with(['attendances' => function ($q) use ($date) {
                $q->whereDate('work_date', $date)
                ->with('breaks');
            }])
            ->get();

        $attendances = $users->map(function ($user) {
            $attendance = $user->attendances->first();

            if (!$attendance) {
                $attendance = new Attendance([
                    'work_date'      => null,
                    'check_in_time'  => null,
                    'check_out_time' => null,
                ]);
                $attendance->setRelation('breaks', collect());
            }

            $attendance->user = $user;
            return $attendance;
        });

        return view('admin_index', [
            'attendances' => $attendances,
            'date'        => $date,
            'prevDate'    => date('Y-m-d', strtotime($date . ' -1 day')),
            'nextDate'    => date('Y-m-d', strtotime($date . ' +1 day')),
        ]);
    }

    public function edit(Request $request, $id)
    {
        $layout = auth()->user()->is_admin ? 'layouts.admin_nav' : 'layouts.staff_nav';

        $attendance = Attendance::find($id);

        $date = $request->query('date');

        if (!$attendance) {
            $date = request()->query('date');
            $attendance = new Attendance([
                'work_date' => $date ?? now()->format('Y-m-d'),
                'check_in_time' => null,
                'check_out_time' => null,
            ]);

            $attendance->user = User::find(request()->query('user_id'));
            $attendance->breaks = collect();
        } else {
            $attendance->load('user', 'breaks');
        }

        return view('detail', [
            'attendance' => $attendance,
            'layout' => $layout,
            'pendingEdit' => null,
            'date' => $date,
            'isApprovalMode' => false,
        ]);
    }

    public function update(AttendanceTimeRequest $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $attendance = Attendance::with('breaks')->findOrFail($id);

            $attendance->update([
                'check_in_time'  => $request->input('check_in_time'),
                'check_out_time' => $request->input('check_out_time'),
                'reason'         => $request->input('reason'),
            ]);

            $attendance->breaks()->delete();

            if ($request->has('breaks')) {
                foreach ($request->input('breaks') as $break) {
                    if (!empty($break['start']) && !empty($break['end'])) {
                        $attendance->breaks()->create([
                            'break_start_time' => $break['start'],
                            'break_end_time'   => $break['end'],
                        ]);
                    }
                }
            }
        });
        return redirect()->route('admin.attendance.update', $id)
            ->with('success', '勤怠データを更新しました。');
    }

    public function store(AttendanceTimeRequest $request)
    {
        $attendance = null;
        DB::transaction(function () use ($request, &$attendance) {
            $attendance = Attendance::create([
                'user_id'       => $request->input('user_id'),
                'work_date'     => $request->input('work_date'),
                'check_in_time' => $request->input('check_in_time'),
                'check_out_time'=> $request->input('check_out_time'),
                'reason'        => $request->input('reason'),
            ]);

            if ($request->has('breaks')) {
                foreach ($request->input('breaks') as $break) {
                    if (!empty($break['start']) && !empty($break['end'])) {
                        $attendance->breaks()->create([
                            'break_start_time' => $break['start'],
                            'break_end_time'   => $break['end'],
                        ]);
                    }
                }
            }
        });

        return redirect()->route('admin.attendance.edit', $attendance->id)
        ->with('success', '勤怠データを登録しました。');
    }

    public function staffIndex()
    {
        $staffs = User::where('is_admin', 0)->get();
        $isAdmin = auth()->user()->is_admin;

        return view('user_index', compact('staffs', 'isAdmin'));
    }

    public function attendanceIndex(Request $request, $id)
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

        $targetUser = User::findOrFail($id);

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
            'layout', 'targetUser', 'days', 'month', 'prevDate', 'nextDate'
        ));
    }

    public function approveView(AttendanceEdit $attendance_correct_request)
    {
        $attendance_correct_request->load('attendance.user', 'editBreaks');

        return view('detail', [
            'attendance'     => $attendance_correct_request->attendance,
            'pendingEdit'    => $attendance_correct_request,
            'layout'         => 'layouts.admin_nav',
            'date'           => $attendance_correct_request->attendance->work_date,
            'isApprovalMode' => true,
        ]);
    }

    public function approve(Request $request, AttendanceEdit $attendance_correct_request)
    {
        $attendanceEdit = $attendance_correct_request;
        DB::transaction(function () use ($attendanceEdit) {
            $attendance = $attendanceEdit->attendance;

            if (!$attendance) {
                return redirect()->back()->with('error', '紐づく勤怠データが存在しません');
            }

            $attendance->update([
                'check_in_time'  => $attendanceEdit->after_check_in,
                'check_out_time' => $attendanceEdit->after_check_out,
                'reason'        => $attendanceEdit->reason,
            ]);

            $attendance->breaks()->delete();
            foreach ($attendanceEdit->editBreaks as $editBreak) {
                $attendance->breaks()->create([
                    'break_start_time' => $editBreak->after_break_start_time,
                    'break_end_time'   => $editBreak->after_break_end_time,
                ]);
            }

            $attendanceEdit->update(['status' => 1]);
        });

        return redirect()->route('admin.attendance.approveview', [
            'attendance_correct_request' => $attendanceEdit->id
        ])->with('success', '勤怠修正申請を承認しました。');

    }

    public function exportCsv(Request $request, $id)
    {
        $month = $request->input('date', now()->format('Y-m'));
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate   = Carbon::parse($month)->endOfMonth();

        $user = User::findOrFail($id);

        $attendances = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->with('breaks')
            ->get()
            ->keyBy(function ($item) {
                return $item->work_date->toDateString();
            });

        $response = new StreamedResponse(function () use ($attendances, $user, $month, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            $current = $startDate->copy();
            while ($current->lte($endDate)) {
                $att = $attendances->get($current->toDateString());

                fputcsv($handle, [
                    $current->format('Y/m/d'),
                    $att?->check_in_time?->format('H:i') ?? '',
                    $att?->check_out_time?->format('H:i') ?? '',
                    $att?->total_break_time ?? '',
                    $att?->actual_work_time ?? '',
                ]);

                $current->addDay();
            }

            fclose($handle);
        });

        $fileName = $user->name . '_' . $month . '月' . '_勤怠.csv';

        $response->headers->set('Content-Type', 'text/csv; charset=SJIS-win');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }
}