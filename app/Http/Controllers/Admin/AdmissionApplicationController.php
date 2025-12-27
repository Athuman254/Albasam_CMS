<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\AdmissionApplication;
use App\Models\Student;
use App\Models\StudentAdmission;
use App\Models\Guardian;
use App\Models\Rank;
use App\Models\Division;
use App\Models\Gender;
use App\Models\Religion;
use App\Models\Relationship;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Mail\AdmissionStatusMail;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class AdmissionApplicationController extends Controller
{
    /**
     * Display a listing of applications.
     */
    public function index()
    {
        $applications = QueryBuilder::for(AdmissionApplication::class)
            ->with(['gender', 'rank', 'division'])
            ->allowedFilters([
                'status',
                AllowedFilter::callback('global', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('first_name', 'LIKE', "%{$value}%")
                            ->orWhere('last_name', 'LIKE', "%{$value}%")
                            ->orWhere('application_number', 'LIKE', "%{$value}%")
                            ->orWhere('guardian_name', 'LIKE', "%{$value}%");
                    });
                }),
            ])
            ->defaultSort('-created_at')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Admissions/OnlineApplications', [
            'applications' => $applications,
            'filters' => request('filter', []),
        ]);
    }

    /**
     * Update application status (Review/Reject).
     */
    public function update(Request $request, AdmissionApplication $application)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,approved,rejected',
            'admin_notes' => 'nullable|string',
        ]);

        $application->update([
            ...$validated,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        // Send Email Notification
        try {
            Mail::to($application->guardian_email)->send(new AdmissionStatusMail($application, $validated['status']));
        } catch (\Exception $e) {
            Log::error('Failed to send admission status email: ' . $e->getMessage());
        }

        return back()->with('success', 'Application status updated and notification sent.');
    }

    /**
     * Approve and convert to student.
     */
    public function approve(Request $request, AdmissionApplication $application)
    {
        if ($application->status === 'approved' && $application->student_id) {
            return back()->with('error', 'This application has already been converted to a student.');
        }

        try {
            DB::beginTransaction();

            // 1. Generate unique admission number for the student
            $admissionNumber = Student::generateAdmissionNumber();
            $defaultPassword = Hash::make($admissionNumber . date('Y'));

            // 2. Create StudentAdmission record first (since student belongs to it)
            Log::info('Creating admission record for student application: ' . $application->application_number);
            $admission = StudentAdmission::create([
                'division_id' => $application->division_id,
                'registered_at' => now(),
                'has_exit_school' => false,
            ]);

            // 3. Create Student record
            Log::info('Creating student record for application: ' . $application->application_number);
            $student = Student::create([
                'student_admission_id' => $admission->id,
                'first_name' => $application->first_name,
                'middle_name' => $application->middle_name,
                'last_name' => $application->last_name,
                'admission_number' => $admissionNumber,
                'assessment_number' => $application->assessment_number,
                'username' => $admissionNumber,
                'password' => $defaultPassword,
                'user_type' => 'student',
                'gender_id' => $application->gender_id,
                'religion_id' => $application->religion_id,
                'date_of_birth' => $application->date_of_birth,
                'rank_id' => $application->rank_id,
                'birth_certificate_number' => $application->birth_certificate_number,
                'previous_school' => $application->previous_school,
                'force_password_change' => true,
            ]);

            // 4. Create Guardian record
            Log::info('Creating guardian record for student ID: ' . $student->id);

            // Split guardian name into first and last name
            $guardianNames = explode(' ', trim($application->guardian_name));
            $gFirstName = $guardianNames[0] ?? 'N/A';
            $gLastName = count($guardianNames) > 1 ? implode(' ', array_slice($guardianNames, 1)) : $gFirstName;

            Guardian::create([
                'student_id' => $student->id,
                'first_name' => $gFirstName,
                'last_name' => $gLastName,
                'email' => $application->guardian_email,
                'phone' => $application->guardian_phone,
                'relationship_id' => $application->relationship_id,
            ]);

            // 5. Update application link
            Log::info('Updating application status to approved');
            $application->update([
                'status' => 'approved',
                'student_id' => $student->id,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'admin_notes' => $request->admin_notes ?? 'Application approved and student record created.',
            ]);

            DB::commit();
            Log::info('Enrollment successful for ' . $student->full_name);

            // Send Email Notification
            try {
                Log::info('Attempting to send approval email to: ' . $application->guardian_email);
                Mail::to($application->guardian_email)->send(new AdmissionStatusMail($application, 'approved'));
                Log::info('Approval email sent/logged successfully');
            } catch (\Exception $e) {
                Log::error('Failed to send admission approval email: ' . $e->getMessage());
                Log::error($e->getTraceAsString());
            }

            return back()->with('success', 'Application approved and notification sent! Student record created: ' . $student->full_name);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Enrollment failed: ' . $e->getMessage());
        }
    }
}
