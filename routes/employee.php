<?php

use App\Http\Controllers\Employee\Auth\LoginController;
use App\Http\Controllers\Employee\DashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('employee')->name('employee.')->group(function () {
    Route::middleware('guest:employee')->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    });

    Route::middleware('auth:employee')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('profile',[App\Http\Controllers\Employee\ProfileController::class, 'index'])->name('profile.index');
        Route::get('emergence-info',[App\Http\Controllers\EmergencyContactController::class, 'dataTable'])->name('emergence.info');
        Route::post('password/update', [App\Http\Controllers\Employee\ProfileController::class, 'updatePassword'])->name('password.update');
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    });
});

