<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomLoginRequest;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function login(CustomLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        $credentials['is_admin'] = 1;

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('admin.attendances');
        }

        return back()->withErrors([
            'email' => 'メールアドレスまたはパスワードが正しくありません。',
        ]);
    }
}
