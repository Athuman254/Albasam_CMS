<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AdmissionApplication;
use App\Models\Gender;
use App\Models\Religion;
use App\Models\Rank;
use App\Models\Division;
use App\Models\Relationship;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PublicAdmissionController extends Controller
{
    /**
     * Show the public admission form.
     */
    public function index()
    {
        return Inertia::render('Public/Admission/Index', [
            'genders' => Gender::all(),
            'religions' => Religion::all(),
            'classes' => Rank::with('stream')->get(),
            'divisions' => Division::all(),
            'relationships' => Relationship::all(),
            'is_modal' => request()->has('modal'),
        ]);
    }

    /**
     * Store a new admission application.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            // Student Information
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'gender_id' => 'required|exists:genders,id',
            'birth_certificate_number' => 'nullable|string|max:255',
            'religion_id' => 'nullable|exists:religions,id',

            // Class Information
            'rank_id' => 'required|exists:ranks,id',
            'division_id' => 'nullable|exists:divisions,id',
            'academic_year' => 'required|string',

            // Guardian Information
            'guardian_name' => 'required|string|max:255',
            'guardian_email' => 'required|email|max:255',
            'guardian_phone' => 'required|string|max:20',
            'relationship_id' => 'required|exists:relationships,id',
            'guardian_address' => 'nullable|string',

            // Previous School
            'previous_school' => 'nullable|string|max:255',
            'previous_class' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Generate application number
            $applicationNumber = $this->generateApplicationNumber();

            // Create admission application
            $application = AdmissionApplication::create([
                ...$validated,
                'religion_id' => $request->religion_id ?: null,
                'division_id' => $request->division_id ?: null,
                'application_number' => $applicationNumber,
                'status' => 'pending',
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            Log::info('New admission application submitted', [
                'application_number' => $applicationNumber,
                'student_name' => $application->full_name,
                'guardian_email' => $application->guardian_email,
            ]);

            return back()->with([
                'success' => 'Application submitted successfully!',
                'application' => [
                    'application_number' => $application->application_number,
                    'full_name' => $application->full_name,
                    'rank' => $application->rank?->name,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Admission application submission failed: ' . $e->getMessage());

            return back()->withInput()->with('error', 'Failed to submit application. Please try again.');
        }
    }

    /**
     * Show application success page.
     */
    public function success($applicationNumber)
    {
        $application = AdmissionApplication::where('application_number', $applicationNumber)
            ->with(['gender', 'rank', 'relationship'])
            ->firstOrFail();

        return Inertia::render('Public/Admission/Success', [
            'application' => $application,
            'is_modal' => request()->has('modal'),
        ]);
    }

    /**
     * Track application status (public).
     */
    public function track(Request $request)
    {
        $request->validate([
            'application_number' => 'required|string',
            'guardian_email' => 'required|email',
        ]);

        $application = AdmissionApplication::where('application_number', $request->application_number)
            ->where('guardian_email', $request->guardian_email)
            ->with(['gender', 'rank', 'division', 'relationship', 'reviewedBy'])
            ->first();

        if (!$application) {
            return back()->with('error', 'Application not found. Please check your application number and email.');
        }

        return Inertia::render('Public/Admission/Track', [
            'application' => $application,
            'is_modal' => request()->has('modal'),
        ]);
    }

    /**
     * Show tracking form.
     */
    public function showTrackingForm()
    {
        return Inertia::render('Public/Admission/TrackingForm', [
            'is_modal' => request()->has('modal'),
        ]);
    }

    /**
     * Generate unique application number.
     */
    private function generateApplicationNumber(): string
    {
        $year = date('Y');
        $month = date('m');

        $lastApplication = AdmissionApplication::where('application_number', 'like', "APP-{$year}{$month}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastApplication) {
            $lastNumber = intval(substr($lastApplication->application_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "APP-{$year}{$month}-{$newNumber}";
    }
}
