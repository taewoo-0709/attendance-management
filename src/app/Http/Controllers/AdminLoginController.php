<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomLoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AdminLoginController extends Controller
{
    public function login(CustomLoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || $user->is_admin !== 1) {
            return back()->withErrors([
                'email' => '管理者権限がありません。',
            ]);
        }

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            return redirect()->route('admin.attendance.list');
        }
    }
}
