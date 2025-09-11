<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomLoginRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class StaffLoginController extends Controller
{
    public function login(CustomLoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || $user->is_admin !== 0) {
            return back()->withErrors([
                'email' => '管理者ログインが必要です。',
            ]);
        }

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            return redirect()->route('attendance');
        }
    }
}