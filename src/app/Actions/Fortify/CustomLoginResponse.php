<?php

namespace App\Actions\Fortify;

use Illuminate\Http\Request;

class CustomLoginResponse implements LoginResponse
{
    /**
     * Handle login response based on user role.
     *
     * @param
     */
    public function toResponse(Request $request)
    {
        $user = auth()->user();

        if ($user->is_admin) {
            return redirect()->route('admin.attendances');
        }

        return redirect()->route('attendance');
    }
}