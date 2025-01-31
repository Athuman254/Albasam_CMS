<?php

namespace App\Http\Controllers;

use App\Models\Rank;
use Inertia\Inertia;
use App\Models\Gender;
use App\Models\Sibling;
use App\Models\Student;
use App\Models\Division;
use App\Models\Guardian;
use App\Models\Religion;
use App\Models\Relationship;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Resources\Resource;
use App\Models\StudentAdmission;
use App\Mail\AdmissionRequestMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Requests\StudentAdmissionRequest;

class StudentAdmissionController extends Controller
{
    public function dataTable()
    {
        $admissions =  QueryBuilder::for(
            StudentAdmission::with(['division', 'rank', 'student'])->orderBy('id', 'desc')
        )->allowedFilters([
            AllowedFilter::partial('admission_number'),
        ])->jsonPaginate();

        return Resource::collection($admissions);
    }

    public function index()
    {
        return Inertia::render('admin/StudentAdmissions/Index', [
            'divisions' => Division::activated()->orderBy('name')->get(),
            'classes' => Rank::activated()->orderBy('name')->get(),
            'genders' => Gender::activated()->orderBy('name')->get(),
            'religions' => Religion::activated()->orderBy('name')->get(),
            'relationships' => Relationship::activated()->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
//        return 'okay';
        return Inertia::render('admin/StudentAdmissions/Create', []);
    }

    public function store(StudentAdmissionRequest $request)
    {
        $validated = $request->validated();

        DB::beginTransaction();
        try {
            $admission = StudentAdmission::create([
                'date' => $validated['registration_details']['date'],
                'admission_number' => $validated['registration_details']['admission_number'],
                'division_id' => $validated['registration_details']['division_id'],
                'rank_id' => $validated['registration_details']['rank_id'],
                'physical_disability' => $validated['other_details']['physical_disability'],
                'hobby' => $validated['other_details']['hobby'],
            ]);
//            dd($admission);

            $student = Student::create([
                'student_admission_id' => $admission->id,
                'first_name' => $validated['student']['first_name'],
                'middle_name' => $validated['student']['middle_name'],
                'last_name' => $validated['student']['last_name'],
                'date_of_birth' => $validated['student']['date_of_birth'],
                'birth_certificate_number' => $validated['student']['birth_certificate_number'],
                'gender_id' => $validated['student']['gender_id'],
                'religion_id' => $validated['student']['religion_id'],
                'citizenship' => $validated['student']['citizenship'],
                'county' => $validated['student']['county'],
                'ward' => $validated['student']['ward'],
                'permanent_address' => $validated['student']['permanent_address'] ?? null,
                'kpsea_score' => $validated['student']['kpsea_score'] ?? null,
                'kjsea_score' => $validated['student']['kjsea_score'] ?? null,
                'kcpe_score' => $validated['student']['kcpe_score'] ?? null,
                'upi_number' => $validated['student']['upi_number'] ?? null,
                'nemis' => $validated['student']['nemis'] ?? null,
                'assessment_number' => $validated['student']['assessment_number'] ?? null,
                'previous_school' => $validated['student']['previous_school'] ?? null,
                'specialization' => $validated['student']['specialization'] ?? null
            ]);

//            dd($validated['guardians']);

            if (isset($validated['guardians']) && is_array($validated['guardians'])) {
                $guardianRecords = collect($validated['guardians'])
                    ->filter(function ($guardian) {
                        return isset($guardian['relationship_id'], $guardian['first_name']);
                    })
                    ->map(function ($guardian) use ($student) {
                        return [
                            'student_id' => $student->id,
                            'relationship_id' => $guardian['relationship_id'],
                            'first_name' => $guardian['first_name'],
                            'middle_name' => $guardian['middle_name'],
                            'last_name' => $guardian['last_name'],
                            'email' => $guardian['email'],
                            'phone' => $guardian['phone'],
                            'profession' => $guardian['profession'],
                            'identification_number' => $guardian['identification_number']
                        ];
                    })->toArray();

//                dd($guardianRecords);

                if (!empty($guardianRecords)) {
                    Guardian::insert($guardianRecords);
                }
            }

            if (isset($validated['other_details']['siblings']) && is_array($validated['other_details']['siblings'])) {
                $siblingRecords = collect($validated['other_details']['siblings'])
                    ->filter(function ($sibling) {
                        return !empty($sibling['name']) && !empty($sibling['age']);
                    })
                    ->map(function ($sibling) use ($student) {
                        return [
                            'student_id' => $student->id,
                            'name' => $sibling['name'],
                            'age' => $sibling['age'],
                            'gender_id' => $sibling['gender_id'],
                            'current_school' => $sibling['current_school'],
                            'current_class' => $sibling['current_class'],
                        ];
                    })->toArray();

                if(!empty($siblingRecords)) {
                    Sibling::insert($siblingRecords);
                }
            }

            DB::commit();
            // dd("fis");
            return to_route('admissions.index');

        } catch (\Throwable $exception) {

            DB::rollBack();
            Log::error('Error: ' . $exception->getMessage());
            // dd($exception);
            report($exception);
            return to_route('admissions.form');
        }
    }

    public function edit(StudentAdmission $studentAdmission)
    {
        $studentAdmission->load(['student']);

        return Inertia::render('admin/StudentAdmissions/Edit', [
            'studentAdmission' => $studentAdmission,
        ]);
    }

    public function update(StudentAdmission $studentAdmission, Request $request)
    {
        $validated = $this->otherDetailsValidation($request);

        DB::beginTransaction();
        try {
            $studentAdmission->update([
                'date' => $request->input('registration_details.date'),
                'admission_number' => $request->input('registration_details.admission_number'),
                'division_id' => $request->input('registration_details.division_id'),
                'rank_id' => $request->input('registration_details.rank_id'),
                'physical_disability' => $validated['other_details']['physical_disability'],
                'hobby' => $validated['other_details']['hobby'],
            ]);

            $student = Student::where('student_admission_id', '=', $studentAdmission->id)->first();

            $student->update([
                'first_name' => $request->input('student.first_name'),
                'middle_name' => $request->input('student.middle_name'),
                'last_name' => $request->input('student.last_name'),
                'date_of_birth' => $request->input('student.date_of_birth'),
                'birth_certificate_number' => $request->input('student.birth_certificate_number'),
                'gender_id' => $request->input('student.gender_id'),
                'religion_id' => $request->input('student.religion_id'),
                'citizenship' => $request->input('student.citizenship'),
                'county' => $request->input('student.county'),
                'ward' => $request->input('student.ward'),
                'permanent_address' => $request->input('student.permanent_address'),
                'kpsea_score' => $request->input('student.kpsea_score'),
                'kjsea_score' => $request->input('student.kjsea_score'),
                'kcpe_score' => $request->input('student.kcpe_score'),
                'upi_number' => $request->input('student.upi_number'),
                'nemis' => $request->input('student.nemis'),
                'assessment_number' => $request->input('student.assessment_number'),
                'previous_school' => $request->input('student.previous_school'),
                'specialization' => $request->input('student.specialization'),
            ]);

            if (isset($request->guardians) && is_array($request->guardians)) {

                $student->guardians()->delete();

                $guardianDetails = collect($request->guardians)
                    ->filter(function ($guardian) {
                        return isset($guardian['relationship_id'], $guardian['first_name']);
                    })
                    ->map(function ($guardian) use ($student) {
                        return [
                            'student_id' => $student->id,
                            'relationship_id' => $guardian['relationship_id'],
                            'first_name' => $guardian['first_name'],
                            'middle_name' => $guardian['middle_name'],
                            'last_name' => $guardian['last_name'],
                            'email' => $guardian['email'],
                            'phone' => $guardian['phone'],
                            'profession' => $guardian['profession'],
                            'identification_number' => $guardian['identification_number']
                        ];
                    })->toArray();

                Guardian::insert($guardianDetails);
            }

            if (isset($validated['other_details']['siblings']) && is_array($validated['other_details']['siblings'])) {

                $student->siblings()->delete();

                $siblingRecords = collect($validated['other_details']['siblings'])
                    ->filter(function ($sibling) {
                        return !empty($sibling['name']) && !empty($sibling['gender_id']);
                    })
                    ->map(function ($sibling) use ($student) {
                        return [
                            'student_id' => $student->id,
                            'name' => $sibling['name'],
                            'age' => $sibling['age'],
                            'gender_id' => $sibling['gender_id'],
                            'current_school' => $sibling['current_school'],
                            'current_class' => $sibling['current_class'],
                        ];
                    })->toArray();

                Sibling::insert($siblingRecords);
            }

            DB::commit();
            return to_route('admissions.index');

        }  catch (\Throwable $exception) {

            DB::rollBack();
            Log::error('Error: ' . $exception->getMessage());
            report($exception);
            return redirect()->back()->withInput()->withErrors(['message' => 'Failed to update student details. Please try again.']);
        }
    }

    public function firstStep(Request $request, StudentAdmission $studentAdmission = null)
    {
        $errorMessages = [
            'registration_details.date' => 'Please select the registration date.',
            'registration_details.admission_number' => 'You must provide an admission number.',
            'registration_details.division_id' => 'Please select a division.',
            'registration_details.rank_id' => 'Please select a class.',
        ];

        $request->validate([
            'registration_details.date' => ['required', 'date', 'max:255'],
            'registration_details.admission_number' => ['required', 'string', 'max:255'],
            'registration_details.division_id' => ['required'],
            'registration_details.rank_id' => ['required'],
        ], $errorMessages);

        if($studentAdmission)
        {
            return to_route('admissions.edit', $studentAdmission);
        }

        return to_route('admissions.form');
    }

    public function secondStep(Request $request, StudentAdmission $studentAdmission = null)
    {
        $request->validate([
            'student.first_name' => ['required', 'string', 'max:255'],
            'student.middle_name' => ['nullable', 'string', 'max:255'],
            'student.last_name' => ['required', 'string', 'max:255'],
            'student.gender_id' => ['required', Rule::exists('genders', 'id')],
            'student.religion_id' => ['required', Rule::exists('religions', 'id')],
            'student.date_of_birth' => ['required', 'string', 'max:255'],
            'student.birth_certificate_number' => ['nullable', 'string', 'max:255'],
            'student.citizenship' => ['required', 'string', 'max:255'],
            'student.county' => ['nullable', 'string', 'max:255'],
            'student.ward' => ['nullable', 'string', 'max:255'],
            'student.permanent_address' => ['required', 'string', 'max:255'],
            'student.kpsea_score' => ['nullable', 'string', 'max:255'],
            'student.kjsea_score' => ['nullable', 'string', 'max:255'],
            'student.kcpe_score' => ['nullable', 'string', 'max:255'],
            'student.upi_number' => ['nullable', 'string', 'max:255'],
            'student.nemis' => ['nullable', 'string', 'max:255'],
            'student.assessment_number' => ['nullable', 'string', 'max:255'],
            'student.previous_school' => ['nullable', 'string', 'max:255'],
            'student.specialization' => ['nullable', 'string', 'max:255'],
        ]);

        if($studentAdmission)
        {
            return to_route('admissions.edit', $studentAdmission);
        }

        return to_route('admissions.form');
    }

    public function thirdStep(Request $request, StudentAdmission $studentAdmission = null)
    {
        $errorMessages = [
            'guardians.required' => 'Please provide at least one guardian.',
            'guardians.min' => 'You must add at least one guardian.',
            'guardians.*.first_name.required' => 'The first name of each guardian is required.',
            'guardians.*.first_name.string' => 'The first name must be a valid string.',
            'guardians.*.first_name.max' => 'The first name cannot be longer than 255 characters.',
            'guardians.*.last_name.required' => 'The last name of each guardian is required.',
            'guardians.*.last_name.string' => 'The last name must be a valid string.',
            'guardians.*.last_name.max' => 'The last name cannot be longer than 255 characters.',
            'guardians.*.email.required' => 'The guardian\'s email is required.',
            'guardians.*.email.email' => 'The guardian\'s email must be a valid email address.',
            'guardians.*.phone.required' => 'The guardian\'s phone number is required.',
            'guardians.*.phone.string' => 'The phone number must be a valid string.',
            'guardians.*.identification_number.string' => 'The identification number must be a valid string.',
            'guardians.*.profession.string' => 'The guardian\'s profession must be a valid string.',
        ];

        $request->validate([
            'guardians' => ['nullable', 'array'], // Ensure at least one guardian is provided
            'guardians.*.relationship_id' => ['nullable', Rule::exists('relationships', 'id')],
            'guardians.*.first_name' => ['nullable', 'string', 'max:255'],
            'guardians.*.middle_name' => ['nullable', 'string', 'max:255'],
            'guardians.*.last_name' => ['nullable', 'string', 'max:255'],
            'guardians.*.email' => ['nullable', 'email'],
            'guardians.*.phone' => ['nullable', 'string'],
            'guardians.*.identification_number' => ['nullable', 'string'],
            'guardians.*.profession' => ['nullable', 'string'],
        ], $errorMessages);

        if($studentAdmission)
        {
            return to_route('admissions.edit', $studentAdmission);
        }

        return to_route('admissions.form');
    }

    public function otherDetailsValidation(Request $request)
    {
        return $request->validate([
            'other_details.physical_disability' => ['nullable', 'string'],
            'other_details.hobby' => ['nullable', 'string'],
            'other_details.siblings' => ['nullable', 'array'],
            'other_details.siblings.*.name' => ['nullable', 'string', 'max:255'],
            'other_details.siblings.*.age' => ['nullable', 'numeric'],
            'other_details.siblings.*.gender_id' => ['nullable', Rule::exists('genders', 'id')],
            'other_details.siblings.*.current_school' => ['nullable', 'string', 'max:255'],
            'other_details.siblings.*.current_class' => ['nullable', 'string', 'max:255'],
        ]);
    }

    public function requestForAdmission(Request $request){
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $email = $validated['email'];

        if (Cache::has('admission_request_' . md5($email))) {
            return back()->with('error', 'You have already submitted a request. Please wait 24 hours before trying again.');
        }

        $rateLimiter = RateLimiter::attempt(
            'admission_requests:' . $request->ip(),
            1,
            function() {},
            60 * 60 * 5
        );

        if (false) {
            return back()->with('error', 'Too many requests. Please try again later.');
        }

        try {
            Mail::to('admin@yourdomain.com')
                ->send(new AdmissionRequestMail($email));


            Mail::to($email)
                ->send(new AdmissionRequestMail($email));

            Cache::put('admission_request_' . md5($email), true, now()->addHours(24));

            return back()->with('success', 'Your admission request has been sent successfully! Please wait 24 hours before submitting another request.');

        } catch (\Exception $e) {
            return back()->with('error', 'Something went wrong. Please try again.')
                        ->withInput();
        }
    }
}
