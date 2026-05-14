<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceCorrectionRequestController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\AdminLoginController;

/*
|--------------------------------------------------------------------------
| Home
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.attendance.list');
    }

    return redirect()->route('attendance.index');
})->name('home');

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | General User Login
    |--------------------------------------------------------------------------
    */

    Route::get('/login', [LoginController::class, 'show'])
        ->name('login');

    Route::post('/login', [LoginController::class, 'store'])
        ->name('login.store');

    /*
    |--------------------------------------------------------------------------
    | Admin Login
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/login', [AdminLoginController::class, 'show'])
        ->name('admin.login');

    Route::post('/admin/login', [AdminLoginController::class, 'store'])
        ->name('admin.login.store');
});

/*
|--------------------------------------------------------------------------
| Logout
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'destroy'])
        ->name('logout');

    Route::post('/admin/logout', [AdminLoginController::class, 'destroy'])
        ->name('admin.logout');
});

/*
|--------------------------------------------------------------------------
| General User Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Attendance
    |--------------------------------------------------------------------------
    */

    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
        ->name('attendance.clock_in');

    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
        ->name('attendance.clock_out');

    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])
        ->name('attendance.break_start');

    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])
        ->name('attendance.break_end');

    /*
    |--------------------------------------------------------------------------
    | Attendance List
    |--------------------------------------------------------------------------
    */

    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');

    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])
        ->name('attendance.show');

    Route::get('/attendance/detail/date/{date}', [AttendanceController::class, 'showByDate'])
        ->name('attendance.show_by_date');

    Route::post('/attendance/detail/{id}/update', [AttendanceController::class, 'update'])
        ->name('attendance.update');

    /*
    |--------------------------------------------------------------------------
    | Correction Requests
    |--------------------------------------------------------------------------
    */

    Route::get('/stamp_correction_request/list', [AttendanceCorrectionRequestController::class, 'list'])
        ->name('stamp_correction_request.list');

    Route::get('/stamp_correction_request/detail/{id}', [AttendanceCorrectionRequestController::class, 'show'])
        ->name('stamp_correction_request.show');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', function () {
            return redirect()->route('admin.attendance.list');
        })->name('index');

        /*
        |--------------------------------------------------------------------------
        | Attendance Management
        |--------------------------------------------------------------------------
        */

        Route::get('/attendance/list', [AdminController::class, 'attendanceList'])
            ->name('attendance.list');

        Route::get('/attendance/{id}', [AdminController::class, 'attendanceDetail'])
            ->name('attendance.detail');

        Route::post('/attendance/{id}/update', [AdminController::class, 'attendanceUpdate'])
            ->name('attendance.update');

        /*
        |--------------------------------------------------------------------------
        | Staff Management
        |--------------------------------------------------------------------------
        */

        Route::get('/staff/list', [AdminController::class, 'staffList'])
            ->name('staff.list');

        Route::get('/attendance/staff/{id}', [AdminController::class, 'staffAttendance'])
            ->name('attendance.staff');

        Route::get('/attendance/staff/{id}/date/{date}', [AdminController::class, 'staffAttendanceDetailByDate'])
            ->name('attendance.staff.detail_by_date');

        Route::get('/attendance/staff/{id}/csv', [AdminController::class, 'staffAttendanceCsv'])
            ->name('attendance.staff.csv');

        /*
        |--------------------------------------------------------------------------
        | Correction Request Management
        |--------------------------------------------------------------------------
        */

        Route::get('/stamp_correction_request/list', [AttendanceCorrectionRequestController::class, 'adminList'])
            ->name('stamp_correction_request.list');

        Route::get('/stamp_correction_request/approve/{id}', [AttendanceCorrectionRequestController::class, 'approve'])
            ->name('stamp_correction_request.approve');

        Route::post('/stamp_correction_request/approve/{id}', [AttendanceCorrectionRequestController::class, 'approveUpdate'])
            ->name('stamp_correction_request.approve.update');

        Route::get('/stamp_correction_request/detail/{id}', [AttendanceCorrectionRequestController::class, 'adminShow'])
            ->name('stamp_correction_request.show');
    });