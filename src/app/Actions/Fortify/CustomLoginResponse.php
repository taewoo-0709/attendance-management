<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Notifications\CodeVerifyNotification;

class CustomLoginResponse implements LoginResponseContract
{
    /**
     * Handle login response based on user role.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toResponse($request)
    {
        $user = auth()->user();

        if ($user->is_admin) {
            return redirect()->route('admin.attendance.list');
        }

        if (!$user->hasVerifiedEmail()) {
            $code = random_int(1000, 9999);
            session(['verification_code' => $code]);
            $user->notify(new CodeVerifyNotification($code));

            return redirect()->route('verification.notice');
        }

        return redirect()->route('attendance');
    }
}