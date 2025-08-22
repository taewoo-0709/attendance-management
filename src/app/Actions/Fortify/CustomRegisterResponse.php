<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use App\Notifications\CodeVerifyNotification;
use Illuminate\Support\Facades\Auth;

class CustomRegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        $code = random_int(1000, 9999);
        session([
            'verification_code' => $code,
            'registered_user' => $user->id
        ]);

        $user->notify(new CodeVerifyNotification($code));

        Auth::logout();

        return redirect()->route('verification.notice');
    }
}
