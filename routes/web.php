<?php

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/login', [\App\Http\Controllers\LoginController::class, 'index'])->name('login.index');
Route::post('/login', [\App\Http\Controllers\LoginController::class, 'store'])->name('login');

// SYSTEM DASHBOARD ROUTES
Route::group([
    'middleware' => 'auth'
], function () {
    
    Route::post('/logout', [\App\Http\Controllers\LoginController::class, 'destroy'])->name('logout');
    
    Route::group([
        'prefix' => 'datatable',
        'as' => 'datatable.'
    ], function () {
        Route::get('/student-admissions', [\App\Http\Controllers\StudentAdmissionController::class, 'dataTable']);
        Route::get('/students', [\App\Http\Controllers\StudentController::class, 'dataTable']);
        Route::get('/attendance', [\App\Http\Controllers\AttendanceController::class, 'dataTable']);
        Route::get('/guardians', [\App\Http\Controllers\GuardianController::class, 'dataTable']);
        Route::get('/siblings', [\App\Http\Controllers\SiblingController::class, 'dataTable']);
        Route::get('/institution', [\App\Http\Controllers\InstitutionController::class, 'dataTable']);
        Route::get('/users', [\App\Http\Controllers\UserController::class, 'dataTable']);
        Route::get('/roles', [\App\Http\Controllers\RoleController::class, 'dataTable']);
        Route::get('/permissions', [\App\Http\Controllers\PermissionController::class, 'dataTable']);
        Route::get('/divisions', [\App\Http\Controllers\DivisionController::class, 'dataTable']);
        Route::get('/streams', [\App\Http\Controllers\StreamController::class, 'dataTable']);
        Route::get('/ranks', [\App\Http\Controllers\RankController::class, 'dataTable']);
        Route::get('/subjects', [\App\Http\Controllers\SubjectController::class, 'dataTable']);
        Route::get('/rank-subjects', [\App\Http\Controllers\RankSubjectController::class, 'dataTable']);
        Route::get('/genders', [\App\Http\Controllers\GenderController::class, 'dataTable']);
        Route::get('/religions', [\App\Http\Controllers\ReligionController::class, 'dataTable']);
        Route::get('/relationships', [\App\Http\Controllers\RelationshipController::class, 'dataTable']);
        Route::get('/departments', [\App\Http\Controllers\DepartmentController::class, 'dataTable']);
        Route::get('/employment-types', [\App\Http\Controllers\EmploymentTypeController::class, 'dataTable']);
        Route::get('/employment-statuses', [\App\Http\Controllers\EmploymentStatusController::class, 'dataTable']);
        Route::get('/marital-statuses', [\App\Http\Controllers\MaritalStatusController::class, 'dataTable']);
        Route::get('/honorifics', [\App\Http\Controllers\HonorificController::class, 'dataTable']);
        Route::get('/job-titles', [\App\Http\Controllers\JobTitleController::class, 'dataTable']);
        Route::get('/specializations', [\App\Http\Controllers\SpecializationController::class, 'dataTable']);
        Route::get('/qualification-types', [\App\Http\Controllers\QualificationTypeController::class, 'dataTable']);
        Route::get('/salary-grades', [\App\Http\Controllers\SalaryGradeController::class, 'dataTable']);
        Route::get('/salary-scales', [\App\Http\Controllers\SalaryScaleController::class, 'dataTable']);
        Route::get('/teacher-titles', [\App\Http\Controllers\TeacherTitleController::class, 'dataTable']);
        
        // EMPLOYEES' DATATABLE ROUTES
        Route::get('/employees', [\App\Http\Controllers\EmployeeController::class, 'dataTable']);
        Route::get('/teachers', [\App\Http\Controllers\TeacherController::class, 'dataTable']);
        Route::get('/emergency-contacts', [\App\Http\Controllers\EmergencyContactController::class, 'dataTable']);
        Route::get('/employee-qualifications', [\App\Http\Controllers\QualificationController::class, 'dataTable']);
        Route::get('/work-histories', [\App\Http\Controllers\WorkHistoryController::class, 'dataTable']);
        
        // WEBSITE MANAGEMENT DATATABLES
        Route::get('/website/pages', [\App\Http\Controllers\Website\PageController::class, 'dataTable']);
        Route::get('/website/page-sections', [\App\Http\Controllers\Website\SectionController::class, 'dataTable']);
        Route::get('/website/page-sub-sections', [\App\Http\Controllers\Website\SubSectionController::class, 'dataTable']);
    });
    
    Route::resource('/attendance', \App\Http\Controllers\AttendanceController::class)->names('attendace');
    Route::get('/attendance-record', [\App\Http\Controllers\AttendanceController::class, 'records'])->name('attendence-records');
    
    Route::group([
        'prefix' => 'profile',
        'as' => 'profile.'
    ], function () {
        Route::get('/', [\App\Http\Controllers\ProfileController::class, 'index'])->name('index');
        Route::post('/update', [\App\Http\Controllers\ProfileController::class, 'update'])->name('update-account');
    });
    
    Route::group([
        'prefix' => 'admin',
    ], function () {
        
        Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
        
        Route::get('student-admissions', [\App\Http\Controllers\StudentAdmissionController::class, 'index'])->name('admissions.index');
        Route::get('student-admissions/admission-form', [\App\Http\Controllers\StudentAdmissionController::class, 'create'])->name('admissions.form');
        Route::post('student-admissions', [\App\Http\Controllers\StudentAdmissionController::class, 'store'])->name('admissions.store');
        Route::get('student-admissions/{student_admission}/edit', [\App\Http\Controllers\StudentAdmissionController::class, 'edit'])->name('admissions.edit');
        Route::patch('student-admissions/{student_admission}', [\App\Http\Controllers\StudentAdmissionController::class, 'update'])->name('admissions.update');
        Route::post('student-admissions/first-step', [\App\Http\Controllers\StudentAdmissionController::class, 'firstStep'])->name('admissions.first.step');
        Route::post('student-admissions/{student_admission}/first-step', [\App\Http\Controllers\StudentAdmissionController::class, 'firstStep'])->name('admissions.edit.first.step');
        Route::post('student-admissions/second-step', [\App\Http\Controllers\StudentAdmissionController::class, 'secondStep'])->name('admissions.second.step');
        Route::post('student-admissions/{student_admission}/second-step', [\App\Http\Controllers\StudentAdmissionController::class, 'secondStep'])->name('admissions.edit.second.step');
        Route::post('student-admissions/third-step', [\App\Http\Controllers\StudentAdmissionController::class, 'thirdStep'])->name('admissions.third.step');
        Route::post('student-admissions/{student_admission}/third-step', [\App\Http\Controllers\StudentAdmissionController::class, 'thirdStep'])->name('admissions.edit.third.step');
        //    Route::post('student-admissions/fourth-step', [\App\Http\Controllers\StudentAdmissionController::class, 'fourthStep'])->name('admissions.fourth.step');
        
        Route::post('/users/{user}/permissions', [\App\Http\Controllers\UserController::class, 'updatePermission']);
        Route::post('/institution-media', [\App\Http\Controllers\InstitutionController::class, 'uploadMedia']);
        
        Route::post('/rank_subjects', [\App\Http\Controllers\RankSubjectController::class, 'store']);
        Route::patch('/rank-subjects/{rank_subject}', [\App\Http\Controllers\RankSubjectController::class, 'update']);
        Route::delete('/rank-subjects/{rank_subject}', [\App\Http\Controllers\RankSubjectController::class, 'destroy']);
      
        Route::group([
            'prefix' => 'employees',
        ], function () {
            Route::group([
                'prefix' => '/teacher-registration',
            ], function () {
                Route::post('/first-step', [\App\Http\Controllers\TeacherController::class, 'firstStep'])->name('teacher.registration.first.step');
                Route::post('/first-step/{employee}', [\App\Http\Controllers\TeacherController::class, 'firstStep'])->name('teacher.registration.edit.first.step');
                Route::post('/second-step', [\App\Http\Controllers\TeacherController::class, 'secondStep'])->name('teacher.registration.second.step');
                Route::post('/second-step/{employee}', [\App\Http\Controllers\TeacherController::class, 'secondStep'])->name('teacher.registration.edit.second.step');
                Route::post('/third-step', [\App\Http\Controllers\TeacherController::class, 'thirdStep'])->name('teacher.registration.third.step');
                Route::post('/third-step/{employee}', [\App\Http\Controllers\TeacherController::class, 'thirdStep'])->name('teacher.registration.edit.third.step');
                Route::post('/fourth-step', [\App\Http\Controllers\TeacherController::class, 'fourthStep'])->name('teacher.registration.fourth.step');
            });
            Route::get('/teachers', [\App\Http\Controllers\TeacherController::class, 'index'])->name('teachers.index');
            Route::get('/teachers/create', [\App\Http\Controllers\TeacherController::class, 'create'])->name('teachers.create');
            Route::post('/teachers', [\App\Http\Controllers\TeacherController::class, 'store'])->name('teachers.store');
            Route::get('/teachers/{teacher}', [\App\Http\Controllers\TeacherController::class, 'show'])->name('teachers.show');
            Route::get('/teachers/{teacher}/edit', [\App\Http\Controllers\TeacherController::class, 'edit'])->name('teachers.edit');
            Route::patch('/teachers/{teacher}', [\App\Http\Controllers\TeacherController::class, 'update'])->name('teachers.update');
            Route::resource('/qualifications', \App\Http\Controllers\QualificationController::class)->names('qualifications');
            Route::resource('/work-histories', \App\Http\Controllers\WorkHistoryController::class)->names('work.histories');
            Route::resource('/emergency-contacts', \App\Http\Controllers\EmergencyContactController::class)->names('emergency.contacts');
        });
        
        Route::group(['prefix' => 'sms'], function () {
            Route::get('compose', [\App\Http\Controllers\SmsController::class, 'create']);
            Route::post('send', [\App\Http\Controllers\SmsController::class, 'store'])->name('sms.send');
            Route::get('outbox', [\App\Http\Controllers\SmsController::class, 'index'])->name('sms.outbox');
        });
        
        Route::group([
            'prefix' => 'settings',
        ], function () {
            //        Route::get('/',  [\App\Http\Controllers\ConfigurationController::class, 'index'])->name('settings.index');
            Route::resource('/guardians', \App\Http\Controllers\GuardianController::class)->names('guardians');
            Route::resource('/divisions', \App\Http\Controllers\DivisionController::class)->names('divisions');
            Route::resource('/streams', \App\Http\Controllers\StreamController::class)->names('streams');
            Route::resource('/subjects', \App\Http\Controllers\SubjectController::class)->names('subjects');
            Route::resource('/departments', \App\Http\Controllers\DepartmentController::class)->names('departments');
            Route::resource('/marital-statuses', \App\Http\Controllers\MaritalStatusController::class)->names('marital.statuses');
            Route::resource('/honorifics', \App\Http\Controllers\HonorificController::class)->names('honorifics');
            Route::resource('/employment-types', \App\Http\Controllers\EmploymentTypeController::class)->names('employment.types');
            Route::resource('/employment-statuses', \App\Http\Controllers\EmploymentStatusController::class)->names('employment.statuses');
            Route::resource('/job-titles', \App\Http\Controllers\JobTitleController::class)->names('job.titles');
            Route::resource('/specializations', \App\Http\Controllers\SpecializationController::class)->names('specializations');
            Route::resource('/qualification-types', \App\Http\Controllers\QualificationTypeController::class)->names('qualification.types');
            Route::resource('/salary-grades', \App\Http\Controllers\SalaryGradeController::class)->names('salary-grades');
            Route::resource('/salary-scales', \App\Http\Controllers\SalaryScaleController::class)->names('salary-scales');
            Route::resource('/teacher-titles', \App\Http\Controllers\TeacherTitleController::class)->names('teacher.titles');
        });
        
        Route::resource('/employees', \App\Http\Controllers\EmployeeController::class)->names('employees');
        Route::resource('/institutions', \App\Http\Controllers\InstitutionController::class)->names('institutions');
        Route::resource('/users', \App\Http\Controllers\UserController::class)->names('users');
        Route::resource('/roles', \App\Http\Controllers\RoleController::class)->names('roles');
        Route::resource('/ranks', \App\Http\Controllers\RankController::class)->names('ranks');
        //        Route::resource('/permissions', \App\Http\Controllers\PermissionController::class)->names('permissions');
        
        /**
        * WEBSITE MANAGEMENT ROUTES
        */
        Route::group([
            'prefix' => 'website'
        ], function () {
            Route::get('/pages', [\App\Http\Controllers\Website\PageController::class, 'index'])->name('pages.index');
            Route::post('/pages', [\App\Http\Controllers\Website\PageController::class, 'store'])->name('pages.store');
            Route::patch('/pages/{page}', [\App\Http\Controllers\Website\PageController::class, 'update'])->name('pages.update');
            Route::delete('/pages/{page}', [\App\Http\Controllers\Website\PageController::class, 'destroy'])->name('pages.destroy');
            // PAGE SECTION ROUTES
            Route::get('/pages/{page}/create-sections', [\App\Http\Controllers\Website\SectionController::class, 'create'])->name('pages.sections.create');
            Route::post('/sections', [\App\Http\Controllers\Website\SectionController::class, 'store'])->name('sections.store');
            Route::get('/pages/{page}/edit-sections', [\App\Http\Controllers\Website\SectionController::class, 'edit'])->name('pages.sections.edit');
            Route::patch('/sections/{page}', [\App\Http\Controllers\Website\SectionController::class, 'update'])->name('pages.sections.update');
        });
   });
});

/**
 *  WEBSITE ROUTES
 */
Route::get('/', [\App\Http\Controllers\Website\WebsiteController::class, 'index'])->name('homepage');
Route::get('/{slug}', [\App\Http\Controllers\Website\WebsiteController::class, 'page'])->name('page.show');
