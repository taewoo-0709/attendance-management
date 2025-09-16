<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceEdit;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $layout = auth()->user()->is_admin ? 'layouts.admin_nav' : 'layouts.staff_nav';
        $user   = Auth::user();
        $status = $request->query('status', 0);

        $requests = AttendanceEdit::with(['attendance.user'])
            ->when(!$user->is_admin, function ($query) use ($user) {
                $query->where('requested_id', $user->id);
            })
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        $isAdmin = $user->is_admin;

        return view('application', compact('requests', 'status', 'isAdmin', 'layout'));
    }
}