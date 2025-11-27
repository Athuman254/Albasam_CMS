<?php

use App\Http\Controllers\Timetable\ConstraintController;
use App\Http\Controllers\Timetable\SubjectAllocationController;
use App\Http\Controllers\Timetable\TimetableGenerationController;
use App\Http\Controllers\Timetable\TimetablePeriodController;
use App\Http\Controllers\Timetable\TimetableViewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Timetable Routes
|--------------------------------------------------------------------------
|
| Routes for the timetable management system.
|
*/

Route::middleware(['auth', 'verified'])->prefix('timetable')->name('timetable.')->group(function () {

    // Setup Routes (Admin/Academic)
    Route::middleware(['role:admin|Academic Teacher'])->group(function () {
        // Periods
        Route::get('/setup/periods', [TimetablePeriodController::class, 'index'])->name('periods.index');
        Route::post('/setup/periods', [TimetablePeriodController::class, 'store'])->name('periods.store');
        Route::put('/setup/periods/{period}', [TimetablePeriodController::class, 'update'])->name('periods.update');
        Route::delete('/setup/periods/{period}', [TimetablePeriodController::class, 'destroy'])->name('periods.destroy');
        Route::post('/setup/periods/bulk-delete', [TimetablePeriodController::class, 'bulkDestroy'])->name('periods.bulk-destroy');

        // Subject Allocations
        Route::get('/setup/allocations', [SubjectAllocationController::class, 'index'])->name('allocations.index');
        Route::post('/setup/allocations', [SubjectAllocationController::class, 'store'])->name('allocations.store');
        Route::put('/setup/allocations/{allocation}', [SubjectAllocationController::class, 'update'])->name('allocations.update');
        Route::delete('/setup/allocations/{allocation}', [SubjectAllocationController::class, 'destroy'])->name('allocations.destroy');
        Route::post('/setup/allocations/bulk-delete', [SubjectAllocationController::class, 'bulkDestroy'])->name('allocations.bulk-destroy');

        // Allocation API Routes
        Route::get('/api/teacher-workload/{teacher}', [SubjectAllocationController::class, 'getTeacherWorkload'])->name('api.teacher-workload');
        Route::get('/api/teacher-subjects/{teacher}', [SubjectAllocationController::class, 'getTeacherSubjects'])->name('api.teacher-subjects');
        Route::get('/api/teacher-workload-status/{teacher}', [SubjectAllocationController::class, 'getTeacherWorkloadStatus'])->name('api.teacher-workload-status');
        Route::get('/api/suggest-teachers', [SubjectAllocationController::class, 'suggestAlternativeTeachers'])->name('api.suggest-teachers');
        Route::get('/api/class-coverage', [SubjectAllocationController::class, 'analyzeClassCoverage'])->name('api.class-coverage');

        // Constraints
        Route::get('/setup/constraints', [ConstraintController::class, 'index'])->name('constraints.index');
        Route::post('/setup/constraints', [ConstraintController::class, 'store'])->name('constraints.store');
        Route::delete('/setup/constraints/{constraint}', [ConstraintController::class, 'destroy'])->name('constraints.destroy');

        // Generation
        Route::get('/generate', [TimetableGenerationController::class, 'index'])->name('generate.index');
        Route::post('/generate', [TimetableGenerationController::class, 'store'])->name('generate.store');
        Route::post('/versions/{version}/publish', [TimetableGenerationController::class, 'publish'])->name('versions.publish');
        Route::delete('/versions/{version}', [TimetableGenerationController::class, 'destroy'])->name('versions.destroy');
    });

    // Viewing Routes (Accessible to Teachers/Students too, but we'll restrict editing)
    Route::get('/view/class/{classId?}', [TimetableViewController::class, 'classTimetable'])->name('view.class');
    Route::get('/view/teacher/{teacherId?}', [TimetableViewController::class, 'teacherTimetable'])->name('view.teacher');
});
