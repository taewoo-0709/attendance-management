<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\EmailCodeController;
use App\Notifications\CodeVerifyNotification;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\StaffLoginController;
use App\Http\Controllers\ApplicationController;

Route::get('/login', function () {
    return view('auth.login', ['role' => 'staff']);
    })->name('login');
Route::post('/login', [StaffLoginController::class, 'login'])
    ->name('login');
Route::get('/admin/login', function () {
    return view('auth.login', ['role' => 'admin']);
    })->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])
    ->name('admin.login.submit');

Route::get('/email/verify', [EmailVerificationController::class, 'showEmailVerificationNotice'])
    ->name('verification.notice');

Route::post('/email/verify', [EmailCodeController::class, 'resend'])
    ->name('verification.send');

Route::get('/verify-code', [EmailCodeController::class, 'showForm'])
    ->name('verification.code.form');

Route::post('/verify-code', [EmailCodeController::class, 'verifyCode'])
    ->name('verification.code.submit');

Route::post('/logout', function (Request $request) {
    $user = Auth::user();

    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    if ($user && $user->is_admin === 1) {
        return redirect('/admin/login');
    }

    return redirect('/login');
})->name('logout.submit');

Route::middleware(['auth', 'can:isStaff', 'verified'])->group(function () {
    Route::get('/attendance', [StaffController::class, 'show'])->name('attendance');
    Route::post('/attendance', [StaffController::class, 'update'])->name('attendance.update');
    Route::get('/attendance/list', [StaffController::class, 'index'])->name('attendance.list');
    Route::get('/attendance/detail/{id}', [StaffController::class, 'edit'])
    ->name('attendance.detail');
    Route::post('/attendance/detail/{id}', [StaffController::class, 'requestEdit'])
        ->name('attendance.requestEdit');
});

Route::middleware(['auth', 'can:isAdmin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminController::class, 'index'])
        ->name('admin.attendance.list');
    Route::get('/admin/attendance/{id}', [AdminController::class, 'edit'])
        ->name('admin.attendance.edit');
    Route::put('/admin/attendance/{id}', [AdminController::class, 'update'])
        ->name('admin.attendance.update');
    Route::post('/admin/attendance', [AdminController::class, 'store'])
        ->name('admin.attendance.store');
    Route::get('/admin/staff/list', [AdminController::class, 'staffIndex'])
        ->name('admin.staff.list');
    Route::get('/admin/attendance/staff/{id}', [AdminController::class, 'attendanceIndex'])
        ->name('admin.attendance.staff');
    Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AdminController::class, 'approveView'])
        ->name('admin.attendance.approveview');
    Route::post('/stamp_correction_request/approve/{attendance_correct_request}', [AdminController::class, 'approve'])
        ->name('admin.attendance.approve');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/stamp_correction_request/list', [ApplicationController::class, 'index'])
        ->name('attendance.request.list');
});