<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class EmailVerificationController extends Controller
{
    public function showEmailVerificationNotice(Request $request)
    {
        $userId = session('registered_user');
        $user = $userId ? User::find($userId) : null;

        return view('auth.verify', ['user' => $user]);
    }
}