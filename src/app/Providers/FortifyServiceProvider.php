<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\CustomLoginResponse;
use App\Actions\Fortify\CustomRegisterResponse;
use App\Http\Requests\CustomLoginRequest;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse;
use App\Actions\Fortify\CustomLogoutResponse;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LogoutResponse::class, CustomLogoutResponse::class);
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::authenticateUsing(function (CustomLoginRequest $request) {
            $isAdmin = (int) $request->input('is_admin', 0);
            $user = User::where('email', $request->email)
                ->where('is_admin', $isAdmin)
                ->first();

            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }
            return null;
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });

        $this->app->bind(FortifyLoginRequest::class, CustomLoginRequest::class);

        $this->app->singleton(LoginResponse::class, CustomLoginResponse::class);

        $this->app->singleton(RegisterResponseContract::class, CustomRegisterResponse::class);
    }
}