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
        /********************************
         * DATATABLE ROUTES
         *******************************/
        Route::group([
            'prefix' => 'datatable',
            'as' => 'datatable.'
        ], function () {
            Route::get('/relationships', [\App\Http\Controllers\Settings\RelationshipController::class, 'dataTable'])->name('relationships');
            Route::get('/qualification-types', [\App\Http\Controllers\Settings\QualificationTypeController::class, 'dataTable'])->name('qualification-types');
            
            Route::get('/emergency-contacts', [\App\Http\Controllers\EmergencyContactController::class, 'dataTable'])->name('contacts');
            Route::get('/employee-qualifications', [\App\Http\Controllers\QualificationController::class, 'dataTable'])->name('qualifications');
            Route::get('/work-histories', [\App\Http\Controllers\WorkHistoryController::class, 'dataTable'])->name('work-histories');
        });
        
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('profile',[App\Http\Controllers\Employee\ProfileController::class, 'index'])->name('profile.index');
        Route::post('password/update', [App\Http\Controllers\Employee\ProfileController::class, 'updatePassword'])->name('password.update');
        Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
        
        
        Route::resource('/emergency-contacts', \App\Http\Controllers\EmergencyContactController::class)
            ->names('emergency-contacts')->only(['store', 'update', 'destroy']);
        Route::resource('/qualifications', \App\Http\Controllers\QualificationController::class)
            ->names('qualifications')->only(['store', 'update', 'destroy']);
        Route::resource('/work-histories', \App\Http\Controllers\WorkHistoryController::class)
            ->names('work-histories')->only(['store', 'update', 'destroy']);
    });
});

