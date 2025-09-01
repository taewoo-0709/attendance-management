<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AttendanceEdit;
use App\Models\BreakTime;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\AttendanceTimeRequest;

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

        $date = $request->query('date', $attendance->work_date ?? now()->format('Y-m-d'));

        if (!$attendance) {
            $date = request()->query('date');
            $attendance = new Attendance([
                'work_date' => date('Y-m-d'),
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
        ]);
    }

    public function update(AttendanceTimeRequest $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $attendance = Attendance::with('breaks')->findOrFail($id);

        $attendance->update([
            'check_in_time'  => $request->input('check_in_time'),
            'check_out_time' => $request->input('check_out_time'),
            'reason'         => $request->input('remarks'),
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
                'reason'        => $request->input('remarks'),
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
}