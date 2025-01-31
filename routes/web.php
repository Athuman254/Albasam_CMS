<?php

use Inertia\Inertia;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Middleware\ThrottleAdmissionRequests;
use App\Http\Controllers\StudentAdmissionController;


Route::get('/', [WebsiteController::class, 'index'])->name('homepage.index');
Route::get('about', [WebsiteController::class,'about'])->name('about-page');
Route::get('services', [ServiceController::class,'services'])->name('services');
Route::get('services/{slug}', [ServiceController::class,'single_service'])->name('services.single');
Route::get('blog/{post}', [BlogController::class,'single_blog']);
Route::get('blog', [BlogController::class,'blog_post']);
Route::post('request-admission', [StudentAdmissionController::class, 'requestForAdmission'])->middleware(ThrottleAdmissionRequests::class);


Route::get('data', function(Request $request){
    $data = collect([
        ['id'=>1,'name' => 'Alice Johnson', 'email' => 'alice@example.com', 'role' => 'Admin'],
        ['id'=>2,'name' => 'Bob Smith', 'email' => 'bob@example.com', 'role' => 'User'],
        ['id'=>3,'name' => 'Carol Lee', 'email' => 'carol@example.com', 'role' => 'Moderator'],
        ['id'=>4,'name' => 'Carol3 Lee', 'email' => 'carol@example.com', 'role' => 'Moderator'],
        ['id'=>5,'name' => 'Carol4 Lee', 'email' => 'carol@example.com', 'role' => 'Moderator'],
        ['id'=>6,'name' => 'Carol6 Lee', 'email' => 'carol@example.com', 'role' => 'Moderator'],
        ['id'=>7,'name' => 'Carol5 Lee', 'email' => 'carol@example.com', 'role' => 'Moderator'],
    ]);

    // Simulate search
    if ($search = $request->query('search')) {
        $data = $data->filter(function ($row) use ($search) {
            return stripos($row['name'], $search) !== false ||
                stripos($row['email'], $search) !== false ||
                stripos($row['role'], $search) !== false;
        });
    }

    // Simulate pagination
    $perPage = $request->query('per_page', 5);
    $currentPage = $request->query('pageN', 1);
    $paginated = $data->slice(($currentPage - 1) * $perPage, $perPage)->values();
    $last_page = ceil($data->count() / $perPage);

    return response()->json([
        'data' => $paginated,
        'total' => $data->count(),
        'last_page' => $last_page,
        'current_page' => (int)$currentPage,
        'per_page' => (int)$perPage,
    ]);
});

Route::group([
    'middleware' => 'guest'
], function () {
    Route::get('login', [\App\Http\Controllers\LoginController::class,'index'])->name('login-index');
    Route::post('login', [\App\Http\Controllers\LoginController::class,'store'])->name('login');
});

// SYSTEM DASHBOARD ROUTES
Route::group([
    'middleware' => 'auth'
], function() {

    Route::post('/logout', [\App\Http\Controllers\LoginController::class, 'destroy'])->name('logout');

    Route::group([
        'prefix' => 'datatable',
        'as'     => 'datatable.'
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

        // WEBSITE DATATABLE
        Route::get('/blogs', [\App\Http\Controllers\BlogController::class,'datatable']);
        Route::get('/blog-categories', [\App\Http\Controllers\BlogController::class,'categories_datatable']);
        Route::get('services', [\App\Http\Controllers\ServiceController::class,'datatable']);

    });

    Route::resource('/attendance', \App\Http\Controllers\AttendanceController::class)->names('attendace');
    Route::get('/attendance-record', [\App\Http\Controllers\AttendanceController::class, 'records'])->name('attendence-records');

    Route::group([
        'prefix' => 'profile',
        'as'     => 'profile.'
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

        Route::group([
            'prefix' => 'employees',
        ], function () {
            Route::post('teacher-registration/first-step', [\App\Http\Controllers\TeacherController::class, 'firstStep'])->name('teacher.registration.first.step');
            Route::post('teacher-registration/first-step/{employee}', [\App\Http\Controllers\TeacherController::class, 'firstStep'])->name('teacher.registration.edit.first.step');
            Route::post('teacher-registration/second-step', [\App\Http\Controllers\TeacherController::class, 'secondStep'])->name('teacher.registration.second.step');
            Route::post('teacher-registration/second-step/{employee}', [\App\Http\Controllers\TeacherController::class, 'secondStep'])->name('teacher.registration.edit.second.step');
            Route::post('teacher-registration/third-step', [\App\Http\Controllers\TeacherController::class, 'thirdStep'])->name('teacher.registration.third.step');
            Route::post('teacher-registration/third-step/{employee}', [\App\Http\Controllers\TeacherController::class, 'thirdStep'])->name('teacher.registration.edit.third.step');
            Route::post('teacher-registration/fourth-step', [\App\Http\Controllers\TeacherController::class, 'fourthStep'])->name('teacher.registration.fourth.step');
            Route::get('/teachers', [\App\Http\Controllers\TeacherController::class, 'index'])->name('teachers.index');
            Route::get('/teachers/create', [\App\Http\Controllers\TeacherController::class, 'create'])->name('teachers.create');
            Route::post('/teachers', [\App\Http\Controllers\TeacherController::class, 'store'])->name('teachers.store');
            Route::get('/teachers/{employee}/edit', [\App\Http\Controllers\TeacherController::class, 'edit'])->name('teachers.edit');
            Route::patch('/teachers/{employee}', [\App\Http\Controllers\TeacherController::class, 'update'])->name('teachers.update');
            Route::resource('/qualifications', \App\Http\Controllers\QualificationController::class)->names('qualifications');
            Route::resource('/work-histories', \App\Http\Controllers\WorkHistoryController::class)->names('work.histories');
            Route::resource('/emergency-contacts', \App\Http\Controllers\EmergencyContactController::class)->names('emergency.contacts');
        });

        Route::group(['prefix' => 'sms'], function (){
            Route::get('compose', [SmsController::class, 'create']);
            Route::post('send', [SmsController::class,  'store'])->name('sms.send');
            Route::get('outbox', [SmsController::class,  'index'])->name('sms.outbox');
        });

        Route::group([
            'prefix' => 'settings',
        ], function () {
            //        Route::get('/',  [\App\Http\Controllers\ConfigurationController::class, 'index'])->name('settings.index');
            Route::resource('/guardians', \App\Http\Controllers\GuardianController::class)->names('guardians');
            Route::resource('/divisions', \App\Http\Controllers\DivisionController::class)->names('divisions');
            Route::resource('/streams', \App\Http\Controllers\StreamController::class)->names('streams');
            Route::resource('/ranks', \App\Http\Controllers\RankController::class)->names('ranks');
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
//            ->middleware([
//                'index' => 'permission:access-users-workspace', // View users list
//                'create' => 'permission:create-user',          // Show create form
//                'store' => 'permission:create-user',           // Handle user creation
//                'edit' => 'permission:edit-user',              // Show edit form
//                'update' => 'permission:edit-user',            // Handle user update
//                'destroy' => 'permission:delete-user',         // Delete user
//            ]);
        Route::resource('/roles', \App\Http\Controllers\RoleController::class)->names('roles');
        Route::resource('/permissions', \App\Http\Controllers\PermissionController::class)->names('permissions');
    });

    /**
     * WEBSITE MANAGEMENT ROUTES
     */
    Route::group([
      'prefix' => 'website'
    ], function () {
        Route::get('pages', [WebsiteController::class, 'pages']);
        Route::get('pages/homepage', [WebsiteController::class, 'homepage']);

        Route::post('pages/homepage/slide', [WebsiteController::class, 'store_slide'])->name('homepage.slide');
        Route::delete('pages/homepage/slide/{slide}', [WebsiteController::class, 'delete_slide'])->name('homepage.slide_delete');

        Route::post('pages/homepage/quotes', [WebsiteController::class, 'store_quotes'])->name('homepage.quotes');
        Route::delete('pages/homepage/quotes/{quote}', [WebsiteController::class, 'delete_quotes'])->name('homepage.quotes_delete');

        Route::post('pages/homepage/about', [WebsiteController::class, 'store_about'])->name('homepage.about');
        Route::patch('pages/homepage/about/{about}', [WebsiteController::class, 'update_about'])->name('homepage.about_update');

        Route::post('pages/homepage/whyus', [WebsiteController::class, 'store_whyus'])->name('homepage.whyus');
        Route::delete('pages/homepage/whyus/{reason}', [WebsiteController::class, 'delete_whyus'])->name('homepage.whyus_delete');

        Route::post('pages/homepage/testimonials', [WebsiteController::class, 'store_testimonials'])->name('homepage.testimonials');
        Route::delete('pages/homepage/testimonials/{testimonial}', [WebsiteController::class, 'delete_testimonial'])->name('homepage.testimonial_delete');

        Route::post('pages/homepage/events', [WebsiteController::class, 'store_events'])->name('homepage.events');
        Route::delete('pages/homepage/events/{event}', [WebsiteController::class, 'delete_events'])->name('homepage.event_delete');

        Route::post('pages/homepage/quick-links', [WebsiteController::class, 'store_quicklinks'])->name('homepage.quick-links');
        Route::delete('pages/homepage/quick-links/{link}', [WebsiteController::class, 'delete_quicklinks'])->name('homepage.quick-links_delete');


        Route::get('pages/blogs', [BlogController::class,'index'])->name('website.blog.index');

        Route::post('pages/blogs', [BlogController::class,'store'])->name('website.blog.store');
        Route::put('pages/blogs/{post}', [BlogController::class,'update'])->name('website.blog.update');
        Route::delete('pages/blogs/{post}', [BlogController::class,'destroy'])->name('website.blog.delete');
        Route::post('pages/blogs/category', [BlogController::class,'store_category'])->name('website.blog.category');

        Route::get('settings', [SiteSettingController::class,'index'])->name('site-settings');
        Route::post('settings', [SiteSettingController::class,'store'])->name('site-settings.store');

        Route::get('pages/services', [ServiceController::class,'index'])->name('website.service.index');
        Route::post('pages/services', [ServiceController::class,'store'])->name('website.service.index.store');
        Route::put('pages/services/{service}', [ServiceController::class,'update'])->name('website.service.update');
        Route::delete('pages/services/{service}', [ServiceController::class,'destroy'])->name('website.service.delete');

        Route::post('pages/blogs', [BlogController::class,'store'])->name('website.blog.store');
    });
});
