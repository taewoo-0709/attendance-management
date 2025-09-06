<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Support\Facades\Auth;

class CustomLogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        Auth::logout();

        return redirect('/login');
    }
}