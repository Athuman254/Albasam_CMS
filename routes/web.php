<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Artisan;
use Inertia\Inertia;

Route::get('/dashboard', function () {
   return redirect()->route('admin.dashboard');
});

/********************************
 *  SYSTEM ROUTES
 *******************************/
Route::middleware('auth')->group(function () {
   /****
    * EMPLOYEES
    */
   Route::get('/employees-payroll', [\App\Http\Controllers\Payroll\EmployeePayrollController::class, 'getEmployees']);

   /********************************
    * DATATABLE ROUTES
    *******************************/
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
      Route::get('/roles', [\App\Http\Controllers\Settings\RoleController::class, 'dataTable']);
      Route::get('/permissions', [\App\Http\Controllers\Settings\PermissionController::class, 'dataTable']);
      Route::get('/blog-categories', [\App\Http\Controllers\Website\BlogCategoryController::class, 'dataTable']);
      Route::get('/divisions', [\App\Http\Controllers\Settings\DivisionController::class, 'dataTable']);
      Route::get('/streams', [\App\Http\Controllers\Settings\StreamController::class, 'dataTable']);
      Route::get('/ranks', [\App\Http\Controllers\Settings\RankController::class, 'dataTable']);
      Route::get('/subjects', [\App\Http\Controllers\Settings\SubjectController::class, 'dataTable']);
      Route::get('/lessons', [\App\Http\Controllers\LessonController::class, 'dataTable']);
      Route::get('/time-table', [\App\Http\Controllers\TimetableController::class, 'timeTableData']);
      Route::get('/languages', [\App\Http\Controllers\Settings\LanguageController::class, 'dataTable']);
      Route::get('/genders', [\App\Http\Controllers\Settings\GenderController::class, 'dataTable']);
      Route::get('/religions', [\App\Http\Controllers\Settings\ReligionController::class, 'dataTable']);
      Route::get('/relationships', [\App\Http\Controllers\Settings\RelationshipController::class, 'dataTable']);
      Route::get('/departments', [\App\Http\Controllers\Settings\DepartmentController::class, 'dataTable']);
      Route::get('/employment-types', [\App\Http\Controllers\Settings\EmploymentTypeController::class, 'dataTable']);
      Route::get('/employment-statuses', [\App\Http\Controllers\Settings\EmploymentStatusController::class, 'dataTable']);
      Route::get('/marital-statuses', [\App\Http\Controllers\Settings\MaritalStatusController::class, 'dataTable']);
      Route::get('/honorifics', [\App\Http\Controllers\Settings\HonorificController::class, 'dataTable']);
      Route::get('/job-titles', [\App\Http\Controllers\Settings\JobTitleController::class, 'dataTable']);
      Route::get('/specializations', [\App\Http\Controllers\Settings\SpecializationController::class, 'dataTable']);
      Route::get('/qualification-types', [\App\Http\Controllers\Settings\QualificationTypeController::class, 'dataTable']);
      Route::get('/salary-grades', [\App\Http\Controllers\Settings\SalaryGradeController::class, 'dataTable']);
      Route::get('/salary-scales', [\App\Http\Controllers\Settings\SalaryScaleController::class, 'dataTable']);
      Route::get('/settings/allowances', [\App\Http\Controllers\Settings\AllowanceController::class, 'dataTable']);
      Route::get('/settings/deductions', [\App\Http\Controllers\Settings\DeductionController::class, 'dataTable']);
      Route::get('/settings/incomes', [\App\Http\Controllers\Settings\IncomeController::class, 'dataTable']);

      // EMPLOYEES' DATATABLE ROUTES
      Route::get('/employees', [\App\Http\Controllers\EmployeeController::class, 'dataTable']);
      Route::get('/teachers', [\App\Http\Controllers\TeacherController::class, 'dataTable']);
      Route::get('/emergency-contacts', [\App\Http\Controllers\EmergencyContactController::class, 'dataTable']);
      Route::get('/employee-qualifications', [\App\Http\Controllers\QualificationController::class, 'dataTable']);
      Route::get('/work-histories', [\App\Http\Controllers\WorkHistoryController::class, 'dataTable']);

      // WEBSITE MANAGEMENT DATATABLES
      Route::get('/website/customisations', [\App\Http\Controllers\Website\CustomisationController::class, 'dataTable'])->name('website.customisations');
      Route::get('/website/blogs', [\App\Http\Controllers\Website\BlogController::class, 'dataTable'])->name('website.blogs');
      Route::get('/website/careers', [\App\Http\Controllers\Website\CareerController::class, 'dataTable'])->name('website.careers');
      Route::get('/website/menus', [\App\Http\Controllers\Website\MenuController::class, 'dataTable'])->name('website.menus');
      Route::get('/website/pages', [\App\Http\Controllers\Website\PageController::class, 'dataTable'])->name('website.pages');
      Route::get('/website/page-sections', [\App\Http\Controllers\Website\SectionController::class, 'dataTable'])->name('website.sections');
      Route::get('/website/page-sub-sections', [\App\Http\Controllers\Website\SubSectionController::class, 'dataTable']);
      Route::get('/website/seo-metas', [\App\Http\Controllers\Website\SeoMetaController::class, 'dataTable']);

      // payroll routes
      Route::get('allowance/adjustments', [\App\Http\Controllers\Payroll\EmployeePayrollController::class, 'allowanceAdjustmentDataTable']);
      Route::get('deduction/adjustments', [\App\Http\Controllers\Payroll\EmployeePayrollController::class, 'deductionAdjustmentDataTable']);
      Route::get('employee/incomes', [\App\Http\Controllers\Payroll\EmployeePayrollController::class, 'employeeIncomeDataTable']);
      Route::get('employee/deductions', [\App\Http\Controllers\Payroll\EmployeePayrollController::class, 'employeeDeductionDataTable']);
      Route::get('payroll/summary',[\App\Http\Controllers\Payroll\PayrollController::class, 'datatableSummary']);
   });

   /********************************
    *  ADMIN ROUTES
    *******************************/
   Route::group([
      'prefix' => 'admin',
      'as' => 'admin.'
   ], function () {

      Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

      /********************************
       * USER PROFILE ROUTES
       *******************************/
      Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
      Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
      Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

      Route::resource('/attendances', \App\Http\Controllers\AttendanceController::class)->names('attendances');
      Route::get('/reports/attendance', [\App\Http\Controllers\AttendanceController::class, 'records'])->name('reports.attendance');
      Route::get('/attendance/fetch', [\App\Http\Controllers\AttendanceController::class, 'fetchForDate']);

      /********************************
       * FORM WIZARD ROUTES
       *******************************/
      Route::group([
         'prefix' => '/student-admissions',
      ], function () {
         Route::get('/', [\App\Http\Controllers\StudentAdmissionController::class, 'index'])->name('admissions.index');
         Route::get('/admission-form', [\App\Http\Controllers\StudentAdmissionController::class, 'create'])->name('admissions.form');
         Route::post('/', [\App\Http\Controllers\StudentAdmissionController::class, 'store'])->name('admissions.store');
         Route::get('/{student_admission}/edit', [\App\Http\Controllers\StudentAdmissionController::class, 'edit'])->name('admissions.edit');
         Route::patch('/{student_admission}', [\App\Http\Controllers\StudentAdmissionController::class, 'update'])->name('admissions.update');
         Route::get('/{student_admission}', [\App\Http\Controllers\StudentAdmissionController::class, 'show'])->name('admissions.show');
         Route::post('/first-step', [\App\Http\Controllers\StudentAdmissionController::class, 'firstStep'])->name('admissions.first.step');
         Route::post('/{student_admission}/first-step', [\App\Http\Controllers\StudentAdmissionController::class, 'firstStep'])->name('admissions.edit.first.step');
         Route::post('/second-step', [\App\Http\Controllers\StudentAdmissionController::class, 'secondStep'])->name('admissions.second.step');
         Route::post('/{student_admission}/second-step', [\App\Http\Controllers\StudentAdmissionController::class, 'secondStep'])->name('admissions.edit.second.step');
         Route::post('/third-step', [\App\Http\Controllers\StudentAdmissionController::class, 'thirdStep'])->name('admissions.third.step');
         Route::post('/{student_admission}/third-step', [\App\Http\Controllers\StudentAdmissionController::class, 'thirdStep'])->name('admissions.edit.third.step');
         //    Route::post('student-admissions/fourth-step', [\App\Http\Controllers\StudentAdmissionController::class, 'fourthStep'])->name('admissions.fourth.step');
      });

      Route::post('/users/{user}/permissions', [\App\Http\Controllers\UserController::class, 'updatePermission']);
      Route::post('/medias/institution', [\App\Http\Controllers\InstitutionController::class, 'uploadMedia'])->name('institution.media-upload');
      Route::delete('/medias/{institution}/logo', [\App\Http\Controllers\MediaController::class, 'deleteLogo'])->name('medias.delete.logo');
      Route::delete('/medias/{institutions}/favicon', [\App\Http\Controllers\MediaController::class, 'deleteFavicon'])->name('medias.delete.favicon');

      Route::get('/time-table', [\App\Http\Controllers\TimetableController::class, 'index'])->name('timetable.index');
      Route::post('/lessons', [\App\Http\Controllers\LessonController::class, 'store']);
      Route::patch('/lessons/{lesson}', [\App\Http\Controllers\LessonController::class, 'update']);
      Route::delete('/lessons/{lesson}', [\App\Http\Controllers\LessonController::class, 'destroy']);

      /********************************
       * EMPLOYEE MANAGEMENT ROUTES
       *******************************/
      Route::group([
         'prefix' => 'employees',
      ], function () {
         /********************************
          * FORM WIZARD ROUTES
          *******************************/
         Route::group([
            'prefix' => '/teacher-registration',
         ], function () {
            Route::post('/first-step', [\App\Http\Controllers\FormWizard\TeacherController::class, 'firstStep'])->name('teacher.registration.first.step');
            Route::post('/first-step/{employee}', [\App\Http\Controllers\FormWizard\TeacherController::class, 'firstStep'])->name('teacher.registration.edit.first.step');
            Route::post('/second-step', [\App\Http\Controllers\FormWizard\TeacherController::class, 'secondStep'])->name('teacher.registration.second.step');
            Route::post('/second-step/{employee}', [\App\Http\Controllers\FormWizard\TeacherController::class, 'secondStep'])->name('teacher.registration.edit.second.step');
            Route::post('/third-step', [\App\Http\Controllers\FormWizard\TeacherController::class, 'thirdStep'])->name('teacher.registration.third.step');
            Route::post('/third-step/{employee}', [\App\Http\Controllers\FormWizard\TeacherController::class, 'thirdStep'])->name('teacher.registration.edit.third.step');
            Route::post('/fourth-step', [\App\Http\Controllers\FormWizard\TeacherController::class, 'fourthStep'])->name('teacher.registration.fourth.step');
         });

         Route::post('/system-access/{employee}', [\App\Http\Controllers\EmployeeController::class, 'systemAccess'])->name('employees.system-access');
         Route::patch('/system-access/{employee}', [\App\Http\Controllers\EmployeeController::class, 'revokeSystemAccess'])->name('employees.revoke-system-access');

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

      /********************************
       * SMS MANAGEMENT ROUTES
       *******************************/
      Route::group([
         'prefix' => 'sms'
      ], function () {
         Route::get('compose', [\App\Http\Controllers\SmsController::class, 'create'])->name('sms.compose');
         Route::post('send', [\App\Http\Controllers\SmsController::class, 'store'])->name('sms.send');
         Route::get('outbox', [\App\Http\Controllers\SmsController::class, 'index'])->name('sms.outbox');
      });

      /********************************
       * PAYROLL
       *******************************/
      Route::group([
         'prefix' => 'payroll'
      ], function () {
         // Route::get('adjustments', [\App\Http\Controller
         Route::get('adjustment', [\App\Http\Controllers\Payroll\EmployeePayrollController::class, 'payrollAdjustment'])->name('adjustment.index');
         Route::get('run', [\App\Http\Controllers\Payroll\PayrollController::class, 'run'])->name('payroll.run');
         Route::post('run',[\App\Http\Controllers\Payroll\PayrollController::class, 'store'])->name('payroll.store');
         Route::get('summary',[\App\Http\Controllers\Payroll\PayrollController::class, 'index'])->name('payroll.index');
         Route::post("dedution/adjustments", [\App\Http\Controllers\Payroll\EmployeePayrollController::class, 'storeDeductionAdjustment'])->name("deduction.adjustment");
         Route::patch('deduction/adjustments/{payrollDeduction}', [\App\Http\Controllers\Payroll\EmployeePayrollController::class, 'updateDeductionAdjustment'])->name('deduction.adjustment.update');
         Route::post("allowance/adjustments", [\App\Http\Controllers\Payroll\EmployeePayrollController::class, 'storeAllowanceAdjustment'])->name("allowance.adjustment");
         Route::patch('allowance/adjustments/{payrollAllowance}', [\App\Http\Controllers\Payroll\EmployeePayrollController::class, 'updateAllowanceAdjustment'])->name('allowance.adjustment.update');
         Route::post("employee/income", [\App\Http\Controllers\Payroll\EmployeePayrollController::class, 'storeEmployeeIncome'])->name("employee.income");
         Route::patch("employee/income/{employeeIncome}update", [\App\Http\Controllers\Payroll\EmployeePayrollController::class, 'updateEmployeeIncome'])->name("employee.income.update");
         Route::delete("employee/income/{employeeIncome}delete", [\App\Http\Controllers\Payroll\EmployeePayrollController::class, 'deleteEmployeeIncome'])->name("employee.income.delete");
         Route::post("employee/deductions", [\App\Http\Controllers\Payroll\EmployeePayrollController::class, 'storeEmployeeDeduction'])->name("employee.deduction");
         Route::patch("employee/deduction/{employeeDeduction}update", [\App\Http\Controllers\Payroll\EmployeePayrollController::class, 'updateEmployeeDeduction'])->name("employee.deductions.update");
         Route::delete("employee/deduction/{employeeDeduction}delete", [\App\Http\Controllers\Payroll\EmployeePayrollController::class, 'deleteEmployeeDedection'])->name("employee.deductions.delete");


      });

      /********************************
       * SYSTEM SETTINGS ROUTES
       *******************************/
      Route::group([
         'prefix' => 'settings',
      ], function () {
         Route::get('/',  [\App\Http\Controllers\Settings\SettingController::class, 'index'])->name('settings.index');
         Route::resource('/guardians', \App\Http\Controllers\GuardianController::class)->names('guardians')->only('store', 'update', 'destroy');
         Route::resource('/blog_categories', \App\Http\Controllers\Website\BlogCategoryController::class)->names('blog_categories')->only('store', 'update', 'destroy');
         Route::resource('/divisions', \App\Http\Controllers\Settings\DivisionController::class)->names('divisions')->only('store', 'update', 'destroy');
         Route::resource('/streams', \App\Http\Controllers\Settings\StreamController::class)->names('streams')->only('store', 'update', 'destroy');
         Route::resource('/subjects', \App\Http\Controllers\Settings\SubjectController::class)->names('subjects');
         Route::resource('/departments', \App\Http\Controllers\Settings\DepartmentController::class)->names('departments')->only('store', 'update', 'destroy');
         Route::resource('/genders', \App\Http\Controllers\Settings\GenderController::class)->names('genders')->only('store', 'update', 'destroy');
         Route::resource('/religions', \App\Http\Controllers\Settings\ReligionController::class)->names('religions')->only('store', 'update', 'destroy');
         Route::resource('/relationships', \App\Http\Controllers\Settings\RelationshipController::class)->names('relationships')->only('store', 'update', 'destroy');
         Route::resource('/languages', \App\Http\Controllers\Settings\LanguageController::class)->names('languages')->only('store', 'update', 'destroy');
         Route::resource('/marital-statuses', \App\Http\Controllers\Settings\MaritalStatusController::class)->names('marital.statuses')->only('store', 'update', 'destroy');
         Route::resource('/honorifics', \App\Http\Controllers\Settings\HonorificController::class)->names('honorifics')->only('store', 'update', 'destroy');
         Route::resource('/employment-types', \App\Http\Controllers\Settings\EmploymentTypeController::class)->names('employment-types')->only('store', 'update', 'destroy');
         Route::resource('/employment-statuses', \App\Http\Controllers\Settings\EmploymentStatusController::class)->names('employment-statuses')->only('store', 'update', 'destroy');
         Route::resource('/job-titles', \App\Http\Controllers\Settings\JobTitleController::class)->names('job-titles')->only('store', 'update', 'destroy');
         Route::resource('/specializations', \App\Http\Controllers\Settings\SpecializationController::class)->names('specializations')->only('store', 'update', 'destroy');
         Route::resource('/qualification-types', \App\Http\Controllers\Settings\QualificationTypeController::class)->names('qualification-types')->only('store', 'update', 'destroy');
         Route::resource('/salary-grades', \App\Http\Controllers\Settings\SalaryGradeController::class)->names('salary-grades')->only('store', 'update', 'destroy');
         Route::resource('/salary-scales', \App\Http\Controllers\Settings\SalaryScaleController::class)->names('salary-scales')->only('store', 'update', 'destroy');

         Route::resource('allowances', \App\Http\Controllers\Settings\AllowanceController::class)->names('settings.allowances')->only('store', 'update', 'destroy');
         Route::resource('deductions', \App\Http\Controllers\Settings\DeductionController::class)->names('settings.deductions')->only('store', 'update', 'destroy');
         Route::resource('income', \App\Http\Controllers\Settings\IncomeController::class)->names('settings.income')->only('store', 'update', 'destroy');
      });

      Route::resource('/employees', \App\Http\Controllers\EmployeeController::class)->names('employees');
      Route::resource('/institutions', \App\Http\Controllers\InstitutionController::class)->names('institutions');
      Route::resource('/users', \App\Http\Controllers\UserController::class)->names('users');
      Route::resource('/roles', \App\Http\Controllers\Settings\RoleController::class)->names('roles');
      Route::resource('/ranks', \App\Http\Controllers\Settings\RankController::class)->names('ranks');
      //        Route::resource('/permissions', \App\Http\Controllers\PermissionController::class)->names('permissions');

      /********************************
       * WEBSITE MANAGEMENT ROUTES
       *******************************/
      Route::group([
         'prefix' => 'website'
      ], function () {
         Route::group([
            'prefix' => 'components',
         ], function () {
            Route::get('/', [\App\Http\Controllers\Website\ComponentController::class, 'index'])->name('components.index');
            Route::resource('/blogs', \App\Http\Controllers\Website\BlogController::class)->names('components.blogs')->only('store', 'update', 'destroy');
            Route::resource('/careers', \App\Http\Controllers\Website\CareerController::class)->names('components.careers')->only('store', 'update', 'destroy');
         });

         Route::get('/pages', [\App\Http\Controllers\Website\PageController::class, 'index'])->name('pages.index');
         Route::post('/pages', [\App\Http\Controllers\Website\PageController::class, 'store'])->name('pages.store');
         Route::patch('/pages/{page}', [\App\Http\Controllers\Website\PageController::class, 'update'])->name('pages.update');
         Route::delete('/pages/{page}', [\App\Http\Controllers\Website\PageController::class, 'destroy'])->name('pages.destroy');
         // PAGE_SECTION ROUTES
         Route::get('/pages/{page}/manage-sections', [\App\Http\Controllers\Website\PageController::class, 'manageSections'])->name('pages.manage-sections');
         Route::post('/sections', [\App\Http\Controllers\Website\SectionController::class, 'store'])->name('pages.sections.store');
         Route::patch('/pages/{page}/sections', [\App\Http\Controllers\Website\SectionController::class, 'update'])->name('pages.sections.update');
         Route::delete('/sections/{section}', [\App\Http\Controllers\Website\SectionController::class, 'destroy'])->name('pages.sections.delete');
         Route::post('/medias/section', [MediaController::class, 'uploadSectionMedia'])->name('pages.sections.media');
         Route::delete('/medias/{section}', [MediaController::class, 'deleteSectionMedia'])->name('pages.sections.delete-media');

         Route::post('/sitemap/generate', function () {
            Artisan::call('app:generate-sitemap');
            return back()->with('success', 'Sitemap generated successfully.');
         })->name('sitemap.generate');

         Route::resource('/menus', \App\Http\Controllers\Website\MenuController::class)->names('menus');
         Route::resource('/customisations', \App\Http\Controllers\Website\CustomisationController::class)->names('customisations');
         Route::resource('/sections-cta-buttons', \App\Http\Controllers\Website\SectionCtaButtonController::class)->names('cta-buttons')->only('store', 'update', 'destroy');
         Route::resource('/seo-metas', \App\Http\Controllers\Website\SeoMetaController::class)->names('seo-metas')->except('create', 'edit', 'show');
      });
   });
});

require __DIR__ . '/auth.php';
require __DIR__ . '/employee.php';

/********************************
 *  WEBSITE ROUTES
 *******************************/
Route::get('/', [\App\Http\Controllers\Website\HomePageController::class, 'index'])->name('homepage');
Route::get('/blogs/{blog}', [\App\Http\Controllers\Website\BlogController::class, 'show'])->name('blogs.show');
Route::get('/vacancies/{career}', [\App\Http\Controllers\Website\CareerController::class, 'show'])->name('careers.show');
Route::get('/{slug}', [\App\Http\Controllers\Website\PageController::class, 'show'])->name('page.show');


/********************************
 *  SITEMAP
 *******************************/
Route::get('/sitemap.xml', function () {
   return response()->file(public_path('sitemap.xml'));
});
