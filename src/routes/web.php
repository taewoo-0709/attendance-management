<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\EmailCodeController;
use App\Notifications\CodeVerifyNotification;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AdminController;

Route::get('/login', function () {
    return view('auth.login', ['role' => 'staff']);
    })->name('login');
Route::get('/admin/login', function () {
    return view('auth.login', ['role' => 'admin']);
    })->name('admin.login');

Route::get('/email/verify', [EmailVerificationController::class, 'showEmailVerificationNotice'])
    ->name('verification.notice');

Route::post('/email/verify', [EmailCodeController::class, 'resend'])
    ->name('verification.send');

Route::get('/verify-code', [EmailCodeController::class, 'showForm'])
    ->name('verification.code.form');

Route::post('/verify-code', [EmailCodeController::class, 'verifyCode'])
    ->name('verification.code.submit');

Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [StaffController::class, 'index'])
        ->name('attendance')
        ->middleware(['can:isStaff', 'verified']);
    Route::get('/admin/attendances', [AdminController::class, 'index'])
        ->name('admin.attendances')
        ->middleware(['can:isAdmin']);
});