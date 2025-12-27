<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Application;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\Exams\ExamResultController;
use App\Http\Controllers\Settings\AcademicYearController;
use App\Http\Controllers\Fee\FeeReportController;

/********************************
 * MPESA WEBHOOK ROUTE (PUBLIC - MUST BE OUTSIDE AUTH)
 *******************************/
Route::post('/webhook/mpesa-payment', [\App\Http\Controllers\Webhooks\MpesaAutoRecordController::class, 'handlePayment']);

Route::get('/dashboard', function () {
   return redirect()->route('admin.dashboard');
});

/********************************
 *  SYSTEM ROUTES
 *******************************/
Route::get('/generate-pdf', [PdfController::class, 'generateArabicPdf']);

// Add this route for CSRF cookie - MUST be outside auth middleware
Route::get('/sanctum/csrf-cookie', function () {
   return response()->json(['message' => 'CSRF cookie set']);
});

/********************************
 * PUBLIC ADMISSION PORTAL
 *******************************/
Route::prefix('admission')->name('public.admission.')->group(function () {
   Route::get('/', [\App\Http\Controllers\Public\PublicAdmissionController::class, 'index'])->name('index');
   Route::post('/submit', [\App\Http\Controllers\Public\PublicAdmissionController::class, 'store'])->name('submit');
   Route::get('/success/{applicationNumber}', [\App\Http\Controllers\Public\PublicAdmissionController::class, 'success'])->name('success');
   Route::get('/track', [\App\Http\Controllers\Public\PublicAdmissionController::class, 'showTrackingForm'])->name('track.form');
   Route::post('/track', [\App\Http\Controllers\Public\PublicAdmissionController::class, 'track'])->name('track');
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
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
      Route::get('/employees', [EmployeeController::class, 'dataTable']);
      Route::get('/teachers', [TeacherController::class, 'dataTable']);
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
      Route::get('payroll/summary', [\App\Http\Controllers\Payroll\PayrollController::class, 'datatableSummary']);

      // EXAM MODULE DATATABLES
      Route::get('academic-years', [\App\Http\Controllers\Settings\AcademicYearController::class, 'dataTable']);
      Route::get('exams', [\App\Http\Controllers\Exams\ExamManageController::class, 'dataTable']);
      Route::get('exam-subjects', [\App\Http\Controllers\Exams\ExamManageController::class, 'examSubject']);
      Route::get('exam-marks', [\App\Http\Controllers\Exams\UploadExamResultController::class, 'examMarks']);
      Route::get('enrolled-students', [\App\Http\Controllers\Exams\ExamStudentController::class, 'dataTableEnrollStudents']);

      // FEE MANAGEMENT DATATABLES - UPDATED WITH TRANSFER IMPROVEMENTS
      Route::get('fees', [\App\Http\Controllers\Fee\FeeController::class, 'dataTable'])->name('fees.datatable');
      Route::get('fee-payments', [\App\Http\Controllers\Fee\FeePaymentController::class, 'dataTable'])->name('fee-payments.datatable');
      Route::get('fee-transfers', [\App\Http\Controllers\Fee\FeeTransferController::class, 'dataTable'])->name('fee-transfers.datatable');
      Route::get('/fees/transfers/overdue-details/{student}', [\App\Http\Controllers\Fee\FeeTransferController::class, 'getStudentOverdueDetails'])->name('admin.fees.transfers.overdue-details');
      Route::get('student-fees/{student}', [\App\Http\Controllers\Fee\FeeController::class, 'studentFeesDataTable'])->name('student-fees.datatable');
      Route::get('fee-structures', [\App\Http\Controllers\Fee\FeeStructureController::class, 'dataTable'])->name('fee-structures.datatable');
      Route::get('approval-queue', [\App\Http\Controllers\Exams\ApprovalQueueController::class, 'dataTable'])->name('approval-queue.datatable');
      Route::get('employee-class-assignments', [EmployeeController::class, 'dataTableEmployeeClasses'])->name('employee-class.assignments');
      Route::get('employee-subject-assignments', [EmployeeController::class, 'dataTableEmployeeSubjects'])->name('employee-subject.assignments');
      Route::get('skills', [\App\Http\Controllers\Settings\SubjectController::class, 'getAllSkills'])->name('skills.datatable');

      // SMS DATATABLES
      Route::get('sms/outbox', [\App\Http\Controllers\SmsController::class, 'dataTable'])->name('sms.outbox.datatable');

      // GRADING SCALES DATATABLE
      Route::get('/grading-scales', [\App\Http\Controllers\Admin\GradingScaleController::class, 'dataTable']);
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
       * CALENDAR ROUTES
       *******************************/
      Route::prefix('calendar')->name('calendar.')->group(function () {
         Route::get('/', [\App\Http\Controllers\Admin\CalendarController::class, 'index'])->name('index');
         Route::post('/events', [\App\Http\Controllers\Admin\CalendarController::class, 'store'])->name('events.store');
         Route::put('/events/{event}', [\App\Http\Controllers\Admin\CalendarController::class, 'update'])->name('events.update');
         Route::delete('/events/{event}', [\App\Http\Controllers\Admin\CalendarController::class, 'destroy'])->name('events.destroy');
         Route::get('/events/fetch', [\App\Http\Controllers\Admin\CalendarController::class, 'getEvents'])->name('events.fetch');
      });

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
       * PROMOTION REPORT ROUTES
       *******************************/
      Route::get('/reports/promotions', [\App\Http\Controllers\Admin\PromotionReportController::class, 'index'])->name('reports.promotions');
      Route::get('/reports/promotions/data', [\App\Http\Controllers\Admin\PromotionReportController::class, 'data'])->name('reports.promotions.data');
      Route::post('/reports/promotions/generate', [\App\Http\Controllers\Admin\PromotionReportController::class, 'generate'])->name('reports.promotions.generate');

      /********************************
       * STAFF ATTENDANCE REPORT ROUTES
       *******************************/
      Route::prefix('reports')->name('reports.')->group(function () {
         Route::get('/staff-attendance', [\App\Http\Controllers\Admin\StaffAttendanceReportController::class, 'index'])->name('staff-attendance');
         Route::get('/staff-attendance/data', [\App\Http\Controllers\Admin\StaffAttendanceReportController::class, 'getData'])->name('staff-attendance.data');
         Route::get('/staff-attendance/summary', [\App\Http\Controllers\Admin\StaffAttendanceReportController::class, 'getSummary'])->name('staff-attendance.summary');
         Route::post('/staff-attendance/export', [\App\Http\Controllers\Admin\StaffAttendanceReportController::class, 'export'])->name('staff-attendance.export');

         // All Students Report
         Route::get('/all-students', [\App\Http\Controllers\Admin\StudentReportController::class, 'index'])->name('all-students');
         Route::post('/all-students/export', [\App\Http\Controllers\Admin\StudentReportController::class, 'export'])->name('all-students.export');

         // All Staff Report
         Route::get('/all-staff', [\App\Http\Controllers\Admin\EmployeeReportController::class, 'index'])->name('all-staff');
         Route::post('/all-staff/export', [\App\Http\Controllers\Admin\EmployeeReportController::class, 'export'])->name('all-staff.export');
      });

      /********************************
       * STAFF ATTENDANCE MARKING ROUTES (for admin staff)
       *******************************/
      Route::prefix('attendance')->name('attendance.')->group(function () {
         Route::get('/mark', [\App\Http\Controllers\Employee\StaffAttendanceController::class, 'index'])->name('mark');
         Route::post('/clock-in', [\App\Http\Controllers\Employee\StaffAttendanceController::class, 'clockIn'])->name('clock-in');
         Route::post('/clock-out', [\App\Http\Controllers\Employee\StaffAttendanceController::class, 'clockOut'])->name('clock-out');
         Route::get('/status', [\App\Http\Controllers\Employee\StaffAttendanceController::class, 'getTodayStatus'])->name('status');
         Route::get('/history', [\App\Http\Controllers\Employee\StaffAttendanceController::class, 'getHistory'])->name('history');
      });


      /********************************
       * FEE MANAGEMENT ROUTES - COMPLETE FIXED STRUCTURE WITH IMPROVEMENTS
       *******************************/
      Route::prefix('fees')->name('fees.')->group(function () {
         // Basic CRUD routes
         Route::get('/', [\App\Http\Controllers\Fee\FeeController::class, 'index'])->name('index');
         Route::get('/create', [\App\Http\Controllers\Fee\FeeController::class, 'create'])->name('create');
         Route::post('/', [\App\Http\Controllers\Fee\FeeController::class, 'store'])->name('store');

         // FEE BALANCE ROUTES - MUST COME BEFORE PARAMETERIZED ROUTES
         Route::get('/balances', [\App\Http\Controllers\Fee\FeeController::class, 'balances'])->name('balances');

         // ADD GET VERSION OF CLASS-BALANCES FOR DEBUGGING
         Route::get('/class-balances', [\App\Http\Controllers\Fee\FeeController::class, 'getClassBalances'])->name('class-balances.get');
         Route::post('/class-balances', [\App\Http\Controllers\Fee\FeeController::class, 'getClassBalances'])->name('class-balances');

         // ADD FEE REPORT ROUTES HERE
         Route::get('/reports/fee-report', [\App\Http\Controllers\Fee\FeeReportController::class, 'index'])->name('reports.fee-report');
         Route::post('/reports/generate-fee-report', [\App\Http\Controllers\Fee\FeeReportController::class, 'generateReport'])->name('reports.generate-fee-report');

         Route::post('/search-student-balance', [\App\Http\Controllers\Fee\FeeController::class, 'searchStudentBalance'])->name('search-student-balance');
         Route::post('/student-details', [\App\Http\Controllers\Fee\FeeController::class, 'getStudentDetails'])->name('student-details');
         Route::get('/print-statement/{studentId}', [\App\Http\Controllers\Fee\FeeController::class, 'printStudentStatement'])->name('print-statement');
         Route::post('/print-class-statements', [\App\Http\Controllers\Fee\FeeController::class, 'printClassStatements'])->name('print-class-statements');
         Route::post('/export-balances', [\App\Http\Controllers\Fee\FeeController::class, 'exportFeeBalances'])->name('export-balances');
         Route::post('/send-reminder', [\App\Http\Controllers\Fee\FeeController::class, 'sendFeeReminder'])->name('send-reminder');
         Route::post('/bulk-reminders', [\App\Http\Controllers\Fee\FeeController::class, 'bulkSendReminders'])->name('bulk-reminders');
         Route::get('/dashboard-statistics', [\App\Http\Controllers\Fee\FeeController::class, 'getDashboardStatistics'])->name('dashboard-statistics');

         // Template and generation routes
         Route::post('/copy-template/{feeId}', [\App\Http\Controllers\Fee\FeeController::class, 'copyTemplateToClass'])->name('copy-template');
         Route::post('/generate-from-templates', [\App\Http\Controllers\Fee\FeeController::class, 'generateFeesFromTemplates'])->name('generate-from-templates');
         Route::get('/statistics', [\App\Http\Controllers\Fee\FeeController::class, 'getFeeStatistics'])->name('statistics');

         // Class-based operations
         Route::get('/class-students/{rankId}', [\App\Http\Controllers\Fee\FeeController::class, 'getClassStudents'])->name('class-students');

         // Student lookup routes
         Route::get('/students/search/{admissionNumber}', [\App\Http\Controllers\Fee\FeeController::class, 'searchStudent'])->name('students.search');
         Route::get('/students/{student}/balance', [\App\Http\Controllers\Fee\FeeController::class, 'getStudentBalance'])->name('students.balance');

         // Student fee routes
         Route::get('/students/{student}', [\App\Http\Controllers\Fee\FeeController::class, 'studentFees'])->name('students.show');
         Route::post('/students/{student}/pay', [\App\Http\Controllers\Fee\FeeController::class, 'manualPayment'])->name('students.manual-payment');

         // PAYMENT ROUTES - COMPLETE IMPROVED STRUCTURE
         Route::prefix('payments')->name('payments.')->group(function () {
            // Main payment routes
            Route::get('/', [\App\Http\Controllers\Fee\FeePaymentController::class, 'index'])->name('index');
            Route::get('/verify', [\App\Http\Controllers\Fee\FeePaymentController::class, 'verify'])->name('verify');
            Route::post('/check', [\App\Http\Controllers\Fee\FeePaymentController::class, 'checkPayment'])->name('check');

            // NEW: Multiple fee confirmation route
            Route::post('/confirm', [\App\Http\Controllers\Fee\FeePaymentController::class, 'confirmPayment'])->name('confirm');

            // LEGACY: Single fee confirmation route (backward compatibility)
            Route::post('/confirm-single', [\App\Http\Controllers\Fee\FeePaymentController::class, 'confirmSinglePayment'])->name('confirm.single');

            // Reallocation Route
            Route::post('/reallocate', [\App\Http\Controllers\Fee\FeePaymentController::class, 'reallocatePayment'])->name('reallocate');

            // Payment verification queue
            Route::get('/recorded-payments', [\App\Http\Controllers\Fee\FeePaymentController::class, 'recordedPayments'])->name('recorded.payments');
            Route::post('/auto-match', [\App\Http\Controllers\Fee\FeePaymentController::class, 'autoMatchPayments'])->name('auto-match');
            Route::post('/{payment}/reject', [\App\Http\Controllers\Fee\FeePaymentController::class, 'rejectPayment'])->name('reject');
            Route::post('/{payment}/match', [\App\Http\Controllers\Fee\FeePaymentController::class, 'matchPayment'])->name('match');

            // Receipt management
            Route::get('/{payment}/receipt-data', [\App\Http\Controllers\Fee\FeePaymentController::class, 'getReceiptData'])->name('receipt.data');
            Route::get('/{payment}/receipt', [\App\Http\Controllers\Fee\FeePaymentController::class, 'viewReceipt'])->name('receipt');
            Route::get('/{payment}/receipt/download', [\App\Http\Controllers\Fee\FeePaymentController::class, 'generateReceipt'])->name('receipt.download');
            Route::post('/{payment}/reverse', [\App\Http\Controllers\Fee\FeePaymentController::class, 'reversePayment'])->name('reverse');

            // Analytics and reports
            Route::get('/stats', [\App\Http\Controllers\Fee\FeePaymentController::class, 'getPaymentStats'])->name('stats');
            Route::get('/analytics', [\App\Http\Controllers\Fee\FeePaymentController::class, 'getPaymentAnalytics'])->name('analytics');
            Route::get('/verification-stats', [\App\Http\Controllers\Fee\FeePaymentController::class, 'getVerificationStats'])->name('verification-stats');
            Route::get('/students/{student}/payments', [\App\Http\Controllers\Fee\FeePaymentController::class, 'getStudentPayments'])->name('students.payments');
            Route::get('/students/{student}/statement', [\App\Http\Controllers\Fee\FeePaymentController::class, 'getStudentFeeStatement'])->name('students.statement');
            Route::get('/students/{student}/all-outstanding-fees', [\App\Http\Controllers\Fee\FeePaymentController::class, 'getAllOutstandingFees'])
               ->name('students.all-outstanding-fees');
            Route::get('/students/{student}/outstanding-fees', [\App\Http\Controllers\Fee\FeePaymentController::class, 'getOutstandingFees'])
               ->name('students.outstanding-fees');

            // M-Pesa STK Push
            Route::post('/mpesa/initiate', [\App\Http\Controllers\Finance\MpesaController::class, 'initiateStkPush'])->name('mpesa.initiate');
            Route::post('/mpesa/initiate-push', [\App\Http\Controllers\Finance\MpesaController::class, 'initiateStkPush'])->name('mpesa.initiate-push'); // Alias for the name expected by Frontend

            // Cash Payment Recording
            Route::post('/record-cash', [\App\Http\Controllers\Fee\FeePaymentController::class, 'recordCashPayment'])->name('record-cash');
         });

         // Bulk operations
         Route::delete('/bulk-destroy', [\App\Http\Controllers\Fee\FeeController::class, 'bulkDestroy'])->name('bulk-destroy');

         // PARAMETERIZED ROUTES - MUST COME AFTER ALL SPECIFIC ROUTES
         Route::get('/{fee}', [\App\Http\Controllers\Fee\FeeController::class, 'show'])->name('show');
         Route::get('/{fee}/edit', [\App\Http\Controllers\Fee\FeeController::class, 'edit'])->name('edit');
         Route::put('/{fee}', [\App\Http\Controllers\Fee\FeeController::class, 'update'])->name('update');
         Route::delete('/{fee}', [\App\Http\Controllers\Fee\FeeController::class, 'destroy'])->name('destroy');

         // TRANSFER ROUTES - UPDATED WITH REASON VALIDATION AND APPROVAL WORKFLOW
         Route::prefix('transfers')->name('transfers.')->group(function () {
            Route::get('/stats', [\App\Http\Controllers\Fee\FeeTransferController::class, 'stats'])->name('stats');
            Route::get('/reasons', [\App\Http\Controllers\Fee\FeeTransferController::class, 'reasons'])->name('reasons');
            Route::get('/pending-approvals', [\App\Http\Controllers\Fee\FeeTransferController::class, 'pendingApprovals'])->name('pending-approvals');
            Route::get('/', [\App\Http\Controllers\Fee\FeeTransferController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Fee\FeeTransferController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Fee\FeeTransferController::class, 'store'])->name('store');
            Route::post('/{transfer}/approve', [\App\Http\Controllers\Fee\FeeTransferController::class, 'approve'])->name('approve');
            Route::post('/{transfer}/reject', [\App\Http\Controllers\Fee\FeeTransferController::class, 'reject'])->name('reject');
            Route::get('/{transfer}', [\App\Http\Controllers\Fee\FeeTransferController::class, 'show'])->name('show');
            Route::post('/credit-transfer', [\App\Http\Controllers\Fee\FeePaymentController::class, 'transferCredit'])->name('credit-transfer');
         });
         Route::get('/reports/overview', [\App\Http\Controllers\Fee\FeeReportController::class, 'overview'])->name('reports.overview');
         Route::get('/reports/student/{student}', [\App\Http\Controllers\Fee\FeeReportController::class, 'student'])->name('reports.student');
         Route::get('/reports/collection', [\App\Http\Controllers\Fee\FeeReportController::class, 'collectionReport'])->name('reports.collection');
         Route::get('/reports/outstanding', [\App\Http\Controllers\Fee\FeeReportController::class, 'outstandingReport'])->name('reports.outstanding');
         Route::get('/reports/export-collection', [\App\Http\Controllers\Fee\FeeReportController::class, 'exportCollectionReport'])->name('reports.export-collection');
         Route::get('/reports/export-collection-pdf', [\App\Http\Controllers\Fee\FeeReportController::class, 'exportCollectionPdf'])->name('reports.export-collection-pdf');
         Route::get('/reports/export-outstanding', [\App\Http\Controllers\Fee\FeeReportController::class, 'exportOutstandingReport'])->name('reports.export-outstanding');
         Route::get('/reports/export-student/{student}', [\App\Http\Controllers\Fee\FeeReportController::class, 'exportStudentReport'])->name('reports.export-student');
         Route::get('/reports/export-overview', [\App\Http\Controllers\Fee\FeeReportController::class, 'exportOverviewReport'])->name('reports.export-overview');
      });

      /********************************
       * FEE STRUCTURE ROUTES - FIXED VERSION WITH AUTO-FEE APPLICATION
       *******************************/
      Route::prefix('fee-structures')->name('fee-structures.')->group(function () {
         Route::get('/', [\App\Http\Controllers\Fee\FeeStructureController::class, 'index'])->name('index');
         Route::get('/create', [\App\Http\Controllers\Fee\FeeStructureController::class, 'create'])->name('create');
         Route::post('/', [\App\Http\Controllers\Fee\FeeStructureController::class, 'store'])->name('store');
         Route::get('/{fee_structure}/edit', [\App\Http\Controllers\Fee\FeeStructureController::class, 'edit'])->name('edit');
         Route::put('/{fee_structure}', [\App\Http\Controllers\Fee\FeeStructureController::class, 'update'])->name('update');
         Route::delete('/{fee_structure}', [\App\Http\Controllers\Fee\FeeStructureController::class, 'destroy'])->name('destroy');
         Route::post('/{fee_structure}/update-status', [\App\Http\Controllers\Fee\FeeStructureController::class, 'updateStatus'])->name('update-status');
         Route::post('/{fee_structure}/generate-fees', [\App\Http\Controllers\Fee\FeeController::class, 'generateFeesFromStructure'])->name('generate-fees');

         Route::post('/{fee_structure}/bulk-delete-fees', [\App\Http\Controllers\Fee\FeeStructureController::class, 'bulkDeleteFees'])->name('bulk-delete-fees');
         Route::get('/{fee_structure}', [\App\Http\Controllers\Fee\FeeStructureController::class, 'show'])->name('show');

         // NEW: Auto-fee application routes
         Route::post('/{fee_structure}/apply-to-student/{student}', [\App\Http\Controllers\Fee\FeeStructureController::class, 'applyToStudent'])->name('apply-to-student');
         Route::post('/{fee_structure}/apply-to-eligible', [\App\Http\Controllers\Fee\FeeStructureController::class, 'applyToAllEligibleStudents'])->name('apply-to-eligible');
      });

      /********************************
       * AUTO-FEE APPLICATION ROUTES - ADDED HERE
       *******************************/
      Route::post('/fee-structures/sync-all', [\App\Http\Controllers\Fee\FeeStructureController::class, 'syncAllFeeStructures'])->name('admin.fee-structures.sync-all');
      Route::post('/students/{student}/apply-fees', [\App\Http\Controllers\Fee\FeeStructureController::class, 'applyRelevantFeesToStudent'])->name('admin.students.apply-fees');

      Route::get('/students/search', [\App\Http\Controllers\Fee\FeePaymentController::class, 'searchStudents'])->name('students.search');
      /********************************
       * STUDENT ADMISSIONS ROUTES - CLEANED VERSION
       *******************************/
      Route::group([
         'prefix' => '/student-admissions',
         'as' => 'admissions.'
      ], function () {
         Route::get('/', [\App\Http\Controllers\StudentAdmissionController::class, 'index'])->name('index');
         Route::get('/admission-form', [\App\Http\Controllers\StudentAdmissionController::class, 'create'])->name('form');
         Route::post('/', [\App\Http\Controllers\StudentAdmissionController::class, 'store'])->name('store');
         Route::get('/generate-admission-number', [\App\Http\Controllers\StudentAdmissionController::class, 'generateAdmissionNumberApi'])->name('generate.number');
         Route::post('/first-step', [\App\Http\Controllers\StudentAdmissionController::class, 'firstStep'])->name('first.step');
         Route::post('/second-step', [\App\Http\Controllers\StudentAdmissionController::class, 'secondStep'])->name('second.step');
         Route::post('/third-step', [\App\Http\Controllers\StudentAdmissionController::class, 'thirdStep'])->name('third.step');
         Route::post('/{id}/edit-first-step', [\App\Http\Controllers\StudentAdmissionController::class, 'firstStep'])->name('edit.first.step');
         Route::post('/{id}/edit-second-step', [\App\Http\Controllers\StudentAdmissionController::class, 'secondStep'])->name('edit.second.step');
         Route::post('/{id}/edit-third-step', [\App\Http\Controllers\StudentAdmissionController::class, 'thirdStep'])->name('edit.third.step');
         Route::get('/{id}/edit', [\App\Http\Controllers\StudentAdmissionController::class, 'edit'])->name('edit');
         Route::patch('/{id}', [\App\Http\Controllers\StudentAdmissionController::class, 'update'])->name('update');
         Route::get('/{id}', [\App\Http\Controllers\StudentAdmissionController::class, 'show'])->name('show');
      });

      Route::post('/users/{user}/permissions', [\App\Http\Controllers\UserController::class, 'updatePermission']);
      Route::post('/medias/institution', [\App\Http\Controllers\InstitutionController::class, 'uploadMedia'])->name('institution.media-upload');
      Route::delete('/medias/{institution}/logo', [MediaController::class, 'deleteLogo'])->name('medias.delete.logo');
      Route::delete('/medias/{institutions}/favicon', [MediaController::class, 'deleteFavicon'])->name('medias.delete.favicon');

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

         // TEACHER ROUTES
         Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
         Route::get('/teachers/create', [TeacherController::class, 'create'])->name('teachers.create');
         Route::post('/teachers', [TeacherController::class, 'store'])->name('teachers.store');
         Route::get('/teachers/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');
         Route::get('/teachers/{teacher}/edit', [TeacherController::class, 'edit'])->name('teachers.edit');
         Route::patch('/teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');

         // MAIN EMPLOYEE ROUTES
         Route::get('/', [EmployeeController::class, 'index'])->name('employees.index');
         Route::get('/create', [EmployeeController::class, 'create'])->name('employees.create');
         Route::post('/', [EmployeeController::class, 'store'])->name('employees.store');
         Route::get('/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
         Route::get('/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
         Route::patch('/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
         Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
         Route::get('/{employee}/export-pdf', [EmployeeController::class, 'exportPdf'])->name('employees.export-pdf');

         // SYSTEM ACCESS ROUTES
         Route::post('/system-access/{employee}', [EmployeeController::class, 'systemAccess'])->name('employees.system-access');
         Route::patch('/system-access/{employee}', [EmployeeController::class, 'revokeSystemAccess'])->name('employees.revoke-system-access');

         // CLASS ASSIGNMENT ROUTES
         Route::get('/{employee}/assign-classes', [EmployeeController::class, 'assignClasses'])->name('employees.assign-classes');
         Route::post('/{employee}/class-assignments', [EmployeeController::class, 'storeClassAssignments'])->name('employees.class-assignments.store');
         Route::put('/{employee}/class-assignments/{assignment}', [EmployeeController::class, 'updateClassAssignment'])->name('employees.class-assignments.update');
         Route::delete('/{employee}/class-assignments', [EmployeeController::class, 'removeClassAssignment'])->name('employees.class-assignments.remove');

         // SUBJECT ASSIGNMENT ROUTES
         Route::get('/{employee}/assign-subjects', [EmployeeController::class, 'assignSubjects'])->name('employees.assign-subjects');
         Route::post('/{employee}/subject-assignments', [EmployeeController::class, 'storeSubjectAssignments'])->name('employees.subject-assignments.store');
         Route::delete('/{employee}/subject-assignments', [EmployeeController::class, 'removeSubjectAssignment'])->name('employees.subject-assignments.remove');

         // API ROUTES FOR CLASS ASSIGNMENTS
         Route::get('/class/{class}/teachers', [EmployeeController::class, 'getTeachersForClass'])->name('employees.class-teachers');
         Route::get('/{employee}/classes', [EmployeeController::class, 'getEmployeeClasses'])->name('employees.classes');
         Route::get('/available-teachers', [EmployeeController::class, 'getAvailableTeachers'])->name('employees.available-teachers');
         Route::get('/teacher-statistics', [EmployeeController::class, 'getTeacherStatistics'])->name('employees.teacher-statistics');
         Route::get('/{employee}/get-employee-assignments', [EmployeeController::class, 'getEmployeeAssignments'])->name('employees.get-employee-assignments');

         // API ROUTES FOR SUBJECT ASSIGNMENTS
         Route::get('/{employee}/subjects', [EmployeeController::class, 'getEmployeeSubjects'])->name('employees.subjects');
         Route::get('/subject/{subject}/teachers', [EmployeeController::class, 'getTeachersForSubject'])->name('employees.subject-teachers');
         Route::get('/available-subjects', [EmployeeController::class, 'getAvailableSubjects'])->name('employees.available-subjects');

         // EMPLOYEE RELATED RESOURCES
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
         Route::get('group-contacts', [\App\Http\Controllers\SmsController::class, 'getGroupContacts'])->name('sms.group-contacts');
      });

      // LMS Management
      Route::prefix('lms')->name('lms.')->group(function () {
         Route::get('/materials', [\App\Http\Controllers\Admin\LmsController::class, 'materials'])->name('materials.index');
         Route::post('/materials', [\App\Http\Controllers\Admin\LmsController::class, 'storeMaterial'])->name('materials.store');
         Route::delete('/materials/{material}', [\App\Http\Controllers\Admin\LmsController::class, 'destroyMaterial'])->name('materials.destroy');

         Route::get('/assignments', [\App\Http\Controllers\Admin\LmsController::class, 'assignments'])->name('assignments.index');
         Route::post('/assignments', [\App\Http\Controllers\Admin\LmsController::class, 'storeAssignment'])->name('assignments.store');
         Route::delete('/assignments/{assignment}', [\App\Http\Controllers\Admin\LmsController::class, 'destroyAssignment'])->name('assignments.destroy');
         Route::get('/classes', [\App\Http\Controllers\Admin\LmsController::class, 'classes'])->name('classes.index');
         Route::post('/classes', [\App\Http\Controllers\Admin\LmsController::class, 'storeClass'])->name('classes.store');
         Route::delete('/classes/{onlineClass}', [\App\Http\Controllers\Admin\LmsController::class, 'destroyClass'])->name('classes.destroy');
      });

      // Online Admissions
      Route::prefix('admission-applications')->name('admission-applications.')->group(function () {
         Route::get('/', [\App\Http\Controllers\Admin\AdmissionApplicationController::class, 'index'])->name('index');
         Route::put('/{application}', [\App\Http\Controllers\Admin\AdmissionApplicationController::class, 'update'])->name('update');
         Route::post('/{application}/approve', [\App\Http\Controllers\Admin\AdmissionApplicationController::class, 'approve'])->name('approve');
      });

      Route::prefix('student-ids')->group(function () {
         Route::get('/', [\App\Http\Controllers\Admin\StudentIdCardController::class, 'index'])->name('student-ids.index');
         Route::post('/generate', [\App\Http\Controllers\Admin\StudentIdCardController::class, 'generate'])->name('student-ids.generate');
      });

      /********************************
       * PAYROLL
       *******************************/
      Route::group([
         'prefix' => 'payroll'
      ], function () {
         Route::get('adjustment', [\App\Http\Controllers\Payroll\EmployeePayrollController::class, 'payrollAdjustment'])->name('adjustment.index');
         Route::get('run', [\App\Http\Controllers\Payroll\PayrollController::class, 'run'])->name('payroll.run');
         Route::post('run', [\App\Http\Controllers\Payroll\PayrollController::class, 'store'])->name('payroll.store');
         Route::get('summary', [\App\Http\Controllers\Payroll\PayrollController::class, 'index'])->name('payroll.index');
         Route::get('/lists/{pay_date}', [\App\Http\Controllers\Payroll\PayrollController::class, 'show'])->name('payroll-details');
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
         Route::get('/subjects/{subject}/skills', [\App\Http\Controllers\Settings\SubjectController::class, 'getSkills'])->name('subjects.skills');
         Route::post('/subjects/{subject}/skills', [\App\Http\Controllers\Settings\SubjectController::class, 'storeSkill'])->name('subjects.skills.store');
         Route::put('/subjects/{subject}/skills/{skill}', [\App\Http\Controllers\Settings\SubjectController::class, 'updateSkill'])->name('subjects.skills.update');
         Route::delete('/subjects/{subject}/skills/{skill}', [\App\Http\Controllers\Settings\SubjectController::class, 'destroySkill'])->name('subjects.skills.destroy');
         Route::post('/subjects/{subject}/skills/bulk-update', [\App\Http\Controllers\Settings\SubjectController::class, 'bulkUpdateSkills'])->name('subjects.skills.bulk-update');
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

         Route::resource('academic-years', AcademicYearController::class);
         Route::post('academic-years/{id}/activate', [AcademicYearController::class, 'activate'])->name('academic-years.activate');
         Route::resource('allowances', \App\Http\Controllers\Settings\AllowanceController::class)->names('settings.allowances')->only('store', 'update', 'destroy');
         Route::resource('deductions', \App\Http\Controllers\Settings\DeductionController::class)->names('settings.deductions')->only('store', 'update', 'destroy');
         Route::resource('income', \App\Http\Controllers\Settings\IncomeController::class)->names('settings.income')->only('store', 'update', 'destroy');
      });
      Route::resource('/institutions', \App\Http\Controllers\InstitutionController::class)->names('institutions');
      Route::resource('/users', \App\Http\Controllers\UserController::class)->names('users');
      Route::resource('/roles', \App\Http\Controllers\Settings\RoleController::class)->names('roles');
      Route::resource('/ranks', \App\Http\Controllers\Settings\RankController::class)->names('ranks');

      /********************************
       * EXAM MANAGEMENT ROUTES
       *******************************/
      Route::group([
         'prefix' => 'exams',
         'as' => 'exams.'
      ], function () {
         Route::resource('manage', \App\Http\Controllers\Exams\ExamManageController::class);
         Route::resource('exam-students', \App\Http\Controllers\Exams\ExamStudentController::class);
         Route::resource('upload-results', \App\Http\Controllers\Exams\UploadExamResultController::class);
         Route::resource('results', ExamResultController::class);
         Route::get('get-results', [ExamResultController::class, 'getResults'])->name('get-results');

         /********************************
          * APPROVAL QUEUE ROUTES
          *******************************/
         Route::prefix('approval-queue')->name('approval-queue.')->group(function () {
            Route::get('/', function () {
               return Inertia::render('Exam/UploadExamResult/ApprovalQueue');
            })->name('index');

            // API routes for Vue component
            Route::get('/pending-submissions', [\App\Http\Controllers\Exams\ApprovalQueueController::class, 'getPendingSubmissions'])->name('pending-submissions');
            Route::get('/approved-submissions', [\App\Http\Controllers\Exams\ApprovalQueueController::class, 'getApprovedSubmissions'])->name('approved-submissions');
            Route::get('/stats', [\App\Http\Controllers\Exams\ApprovalQueueController::class, 'getStats'])->name('stats');
            Route::get('/{submissionId}/details', [\App\Http\Controllers\Exams\ApprovalQueueController::class, 'getSubmissionDetails'])->name('details');
            Route::post('/approve-marks', [\App\Http\Controllers\Exams\ApprovalQueueController::class, 'approveMarks'])->name('approve');
            Route::post('/reject-marks', [\App\Http\Controllers\Exams\ApprovalQueueController::class, 'rejectMarks'])->name('reject');

            // Individual student approval
            Route::post('/approve-student-marks', [\App\Http\Controllers\Exams\ApprovalQueueController::class, 'approveStudentMarks'])->name('approve-student-marks');
            Route::post('/reject-student-marks', [\App\Http\Controllers\Exams\ApprovalQueueController::class, 'rejectStudentMarks'])->name('reject-student-marks');
            Route::post('/bulk-approve-exam-class', [\App\Http\Controllers\Exams\ApprovalQueueController::class, 'bulkApproveExamClass'])->name('bulk-approve-exam-class');
            Route::post('/fix-missing-grades', [\App\Http\Controllers\Exams\ApprovalQueueController::class, 'fixMissingGrades'])->name('fix-missing-grades');

            // Admin edit approved marks
            Route::post('/update-approved-mark', [\App\Http\Controllers\Exams\ApprovalQueueController::class, 'updateApprovedMark'])->name('update-approved-mark');

            // Additional API routes
            Route::get('/submission-history', [\App\Http\Controllers\Exams\ApprovalQueueController::class, 'getSubmissionHistory'])->name('submission-history');
            Route::get('/analytics', [\App\Http\Controllers\Exams\ApprovalQueueController::class, 'getAnalytics'])->name('analytics');
         });

         // Legacy approval workflow routes (backward compatibility)
         Route::get('/approval-queue-old', [\App\Http\Controllers\Exams\UploadExamResultController::class, 'approvalQueue'])->name('approval-queue-old');
         Route::post('/submit-approval', [\App\Http\Controllers\Exams\UploadExamResultController::class, 'submitForApproval'])->name('submit-approval');
         Route::post('/approve-marks-old', [\App\Http\Controllers\Exams\UploadExamResultController::class, 'approveMarks'])->name('approve-marks-old');
         Route::post('/reject-marks-old', [\App\Http\Controllers\Exams\UploadExamResultController::class, 'rejectMarks'])->name('reject-marks-old');
         Route::post('/publish-marks', [\App\Http\Controllers\Exams\UploadExamResultController::class, 'publishMarks'])->name('publish-marks');
         Route::post('/generate-bulk-report', [ExamResultController::class, 'generateBulkReport'])->name('generate-bulk-report');
         Route::post('/generate-term-analysis', [ExamResultController::class, 'generateTermAnalysisReport'])->name('generate-term-analysis');
         Route::get('/marks-statistics', [\App\Http\Controllers\Exams\UploadExamResultController::class, 'getStatistics'])->name('marks-statistics');
         Route::get('/available-students', [ExamResultController::class, 'getReportStudents'])->name('available-students');
         Route::get('/reports/student/{student}', [ExamResultController::class, 'generateStudentReport'])->name('reports.student');

         // Admin mark editing route
         Route::put('/marks/{mark}/admin-update', [\App\Http\Controllers\Exams\UploadExamResultController::class, 'adminUpdateMark'])->name('marks.admin-update');
      });

      /********************************
       * LEARNING MANAGEMENT SYSTEM (LMS)
       *******************************/
      Route::prefix('lms')->name('lms.')->group(function () {
         // Materials
         Route::get('/materials', [\App\Http\Controllers\Admin\LmsController::class, 'materials'])->name('materials.index');
         Route::post('/materials', [\App\Http\Controllers\Admin\LmsController::class, 'storeMaterial'])->name('materials.store');
         Route::delete('/materials/{material}', [\App\Http\Controllers\Admin\LmsController::class, 'destroyMaterial'])->name('materials.destroy');

         // Assignments
         Route::get('/assignments', [\App\Http\Controllers\Admin\LmsController::class, 'assignments'])->name('assignments.index');
         Route::post('/assignments', [\App\Http\Controllers\Admin\LmsController::class, 'storeAssignment'])->name('assignments.store');
         Route::delete('/assignments/{assignment}', [\App\Http\Controllers\Admin\LmsController::class, 'destroyAssignment'])->name('assignments.destroy');
         Route::get('/assignments/{assignment}/submissions', [\App\Http\Controllers\Admin\LmsController::class, 'submissions'])->name('assignments.submissions');
         Route::post('/submissions/{submission}/grade', [\App\Http\Controllers\Admin\LmsController::class, 'gradeSubmission'])->name('submissions.grade');

         // Online Classes
         Route::get('/classes', [\App\Http\Controllers\Admin\LmsController::class, 'classes'])->name('classes.index');
         Route::post('/classes', [\App\Http\Controllers\Admin\LmsController::class, 'storeClass'])->name('classes.store');
         Route::delete('/classes/{onlineClass}', [\App\Http\Controllers\Admin\LmsController::class, 'destroyClass'])->name('classes.destroy');
      });


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

         // GRADING SCALES
         Route::resource('/grading-scales', \App\Http\Controllers\Admin\GradingScaleController::class)->names('grading-scales');
         Route::get('/grading-scales/{grading_scale}/entries', [\App\Http\Controllers\Admin\GradingScaleController::class, 'getEntries']);
         Route::post('/grading-scales/{grading_scale}/sync-entries', [\App\Http\Controllers\Admin\GradingScaleController::class, 'syncEntries']);
      });
   });

   /********************************
    * TEACHER ROUTES
    *******************************/
   Route::group([
      'prefix' => 'teacher',
      'as' => 'teacher.'
   ], function () {
      Route::get('/my-timetable', [\App\Http\Controllers\Teacher\TeacherTimetableController::class, 'index'])->name('timetable');
      Route::resource('/exam-papers', \App\Http\Controllers\Exams\ExamPaperController::class)->names('exam-papers');
   });
});

require __DIR__ . '/auth.php';
require __DIR__ . '/employee.php';

Route::get('/', [\App\Http\Controllers\Website\HomePageController::class, 'index'])->name('homepage');
Route::get('/blogs/{blog}', [\App\Http\Controllers\Website\BlogController::class, 'show'])->name('blogs.show');
Route::get('/vacancies/{career}', [\App\Http\Controllers\Website\CareerController::class, 'show'])->name('careers.show');
Route::get('/{slug}', [\App\Http\Controllers\Website\PageController::class, 'show'])->name('page.show');

Route::get('/sitemap.xml', function () {
   return response()->file(public_path('sitemap.xml'));
});

require __DIR__ . '/timetable.php';

// Student Routes
Route::group([
   'prefix' => 'student',
   'as' => 'student.',
   'middleware' => \App\Http\Middleware\EnsureStudentIsAuthenticated::class
], function () {
   Route::get('/dashboard', [\App\Http\Controllers\Student\StudentDashboardController::class, 'index'])->name('dashboard');

   // Exam Results
   Route::get('/results', [\App\Http\Controllers\Student\StudentResultController::class, 'index'])->name('results.index');
   Route::get('/results/{exam}', [\App\Http\Controllers\Student\StudentResultController::class, 'show'])->name('results.show');
   Route::get('/results/{exam}/download', [\App\Http\Controllers\Student\StudentResultController::class, 'download'])->name('results.download');

   // Fee Routes
   Route::get('/fees', [\App\Http\Controllers\Student\StudentFeeController::class, 'index'])->name('fees.index');
   Route::get('/fees/download', [\App\Http\Controllers\Student\StudentFeeController::class, 'downloadStatement'])->name('fees.download');

   // Attendance
   Route::get('/attendance', [\App\Http\Controllers\Student\StudentAttendanceController::class, 'index'])->name('attendance.index');

   // Calendar
   Route::prefix('calendar')->name('calendar.')->group(function () {
      Route::get('/', [\App\Http\Controllers\Student\StudentCalendarController::class, 'index'])->name('index');
      Route::get('/events/fetch', [\App\Http\Controllers\Student\StudentCalendarController::class, 'getEvents'])->name('events.fetch');
   });

   // Notices
   Route::get('/notices', [\App\Http\Controllers\Student\StudentNoticeController::class, 'index'])->name('notices.index');

   // Profile
   Route::get('/profile', [\App\Http\Controllers\Student\StudentProfileController::class, 'edit'])->name('profile.edit');
   Route::put('/profile', [\App\Http\Controllers\Student\StudentProfileController::class, 'update'])->name('profile.update');
   Route::put('/profile/password', [\App\Http\Controllers\Student\StudentProfileController::class, 'updatePassword'])->name('profile.password.update');

   // Password Change Routes
   Route::get('/change-password', [\App\Http\Controllers\Student\Auth\StudentPasswordChangeController::class, 'show'])
      ->name('password.change.form');

   Route::put('/change-password', [\App\Http\Controllers\Student\Auth\StudentPasswordChangeController::class, 'update'])
      ->name('password.update');

   // Student M-Pesa Payment
   Route::post('/mpesa/initiate', [\App\Http\Controllers\Finance\MpesaController::class, 'initiateStkPush'])->name('mpesa.initiate');

   // Student LMS Portal Routes
   Route::prefix('lms')->name('lms.')->group(function () {
      Route::get('/dashboard', [\App\Http\Controllers\Student\StudentLmsController::class, 'index'])->name('dashboard');
      Route::get('/materials', [\App\Http\Controllers\Student\StudentLmsController::class, 'materials'])->name('materials.index');
      Route::get('/assignments', [\App\Http\Controllers\Student\StudentLmsController::class, 'assignments'])->name('assignments.index');
      Route::post('/assignments/{assignment}/submit', [\App\Http\Controllers\Student\StudentLmsController::class, 'submitAssignment'])->name('assignments.submit');
      Route::get('/classes', [\App\Http\Controllers\Student\StudentLmsController::class, 'classes'])->name('classes.index');
   });
});

// Student Logout (outside middleware to allow logout)
Route::post('/student/logout', function (\Illuminate\Http\Request $request) {
   Auth::guard('student')->logout();
   $request->session()->invalidate();
   $request->session()->regenerateToken();
   return redirect()->route('homepage');
})->name('student.logout');
