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
            ->latest()
            ->first();

        return view('attendance', compact('attendance'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $action = $request->input('action');

        $attendance = Attendance::where('user_id', $user->id)->latest()->first();

        switch ($action) {
            case 'check_in':
                Attendance::create([
                    'user_id' => $user->id,
                    'check_in_time' => now(),
                ]);
                break;

            case 'check_out':
                if ($attendance) {
                    $attendance->update(['check_out_time' => now()]);
                }
                break;

            case 'break_start':
                if ($attendance) {
                    $attendance->breaks()->create(['break_start_time' => now()]);
                }
                break;

            case 'break_end':
                if ($attendance) {
                    $latestBreak = $attendance->breaks()->latest()->first();
                    if ($latestBreak) {
                        $latestBreak->update(['break_end_time' => now()]);
                    }
                }
                break;
        }

        return redirect()->route('attendance');
    }
}