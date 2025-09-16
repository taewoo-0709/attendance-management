<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApproveController extends Controller
{
    public function update(){
    DB::transaction(function() use ($editId) {
    $edit = AttendanceEdit::with('editBreaks')->findOrFail($editId);
    $attendance = $edit->attendance;

    // Attendance を更新
    $attendance->update([
        'check_in_time' => $edit->after_check_in,
        'check_out_time' => $edit->after_check_out,
        'reason' => $edit->reason,
    ]);

    // 既存の break を削除して更新
    $attendance->breaks()->delete();
    foreach ($edit->editBreaks as $break) {
        $attendance->breaks()->create([
            'break_start_time' => $break->after_break_start_time,
            'break_end_time' => $break->after_break_end_time,
        ]);
    }

    $myEdits = AttendanceEdit::where('requested_id', auth()->id())
    ->with('attendance')
    ->orderBy('created_at', 'desc')
    ->get();


    $edit->update([
        'status' => AttendanceEdit::STATUS_APPROVED,
        'approved_id' => auth()->id(),
    ]);
});

}
}
