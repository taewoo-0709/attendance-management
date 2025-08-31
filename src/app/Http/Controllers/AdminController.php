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

        return view('detail', compact('attendance', 'layout'));
    }

    public function approve(AttendanceTimeRequest $request, $id)
    {
        DB::transaction(function () use ($id) {
            $edit = AttendanceEdit::with('attendance')->findOrFail($id);

            $edit->approved_id = auth()->id();
            $edit->status = AttendanceEdit::STATUS_APPROVED;
            $edit->save();

            $attendance = $edit->attendance;
            if ($edit->after_check_in) {
                $attendance->check_in_time = $edit->after_check_in;
            }
            if ($edit->after_check_out) {
                $attendance->check_out_time = $edit->after_check_out;
            }
            $attendance->save();

            if (!empty($edit->breaks)) {

                $attendance->breaks()->delete();

                foreach ($edit->breaks as $break) {
                    $attendance->breaks()->create([
                        'start_time' => $break['start_time'],
                        'end_time'   => $break['end_time'],
                    ]);
                }
            }
        });
        return back()->with('success', '承認が完了しました。');
    }
}