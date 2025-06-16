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
            StudentAdmission::with(['division', 'student'])->orderBy('date')
        )->allowedFilters([
            AllowedFilter::partial('id'),
        ])->jsonPaginate();

        return Resource::collection($admissions);
    }

    public function index()
    {
        return Inertia::render('Admin/StudentAdmissions/Index', [
            'divisions' => Division::activated()->orderBy('name')->get(),
            'classes' => Rank::activated()->orderBy('name')->get(),
            'genders' => Gender::activated()->orderBy('name')->get(),
            'religions' => Religion::activated()->orderBy('name')->get(),
            'relationships' => Relationship::activated()->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/StudentAdmissions/Create', []);
    }

    public function store(StudentAdmissionRequest $request)
    {
        $validated = $request->validated();
        $defaultDivision = Division::where('name', 'like', 'High School')->first();

        DB::beginTransaction();
        try {
            $admission = StudentAdmission::create([
                'date' => $validated['registration_details']['date'],
                'division_id' => $validated['registration_details']['division_id'],
            ]);

            $student = Student::create([
                'student_admission_id' => $admission->id,
                'admission_number' => $validated['student']['admission_number'],
                'rank_id' => $validated['student']['rank_id'] ?? $defaultDivision->id,
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
                'kcpe_score' => $validated['student']['kcpe_score'] ?? null,
                'previous_school' => $validated['student']['previous_school'] ?? null,
                'physical_disability' => $validated['other_details']['physical_disability'],
                'hobby' => $validated['other_details']['hobby'],
                'medical_details' => $validated['other_details']['medical_details'] ?? null,
                'character_book' => $validated['other_details']['character_book'] ?? null,
            ]);

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
            return to_route('admin.admissions.index');

        } catch (\Throwable $exception) {

            DB::rollBack();
            Log::error('Error: ' . $exception->getMessage());
            report($exception);
            return to_route('admin.admissions.form');
        }
    }

    public function edit(StudentAdmission $studentAdmission)
    {
        $studentAdmission->load(['student']);

        return Inertia::render('Admin/StudentAdmissions/Edit', [
            'studentAdmission' => $studentAdmission,
        ]);
    }

    public function update(StudentAdmission $studentAdmission, Request $request)
    {
        $validated = $this->otherDetailsValidation($request);
        $defaultDivision = Division::where('name', '=', 'High School')->first();
        
//        dd($request->input());

        DB::beginTransaction();
        try {
            $studentAdmission->update([
                'date' => $request->input('registration_details.date'),
                'division_id' => $request->input('registration_details.division_id') ?? $defaultDivision->id,
            ]);

            $student = Student::where('student_admission_id', '=', $studentAdmission->id)->first();

            $student->update([
                'first_name' => $request->input('student.first_name'),
                'middle_name' => $request->input('student.middle_name'),
                'last_name' => $request->input('student.last_name'),
                'admission_number' => $request->input('student.admission_number'),
                'rank_id' => $request->input('student.rank_id'),
                'date_of_birth' => $request->input('student.date_of_birth'),
                'birth_certificate_number' => $request->input('student.birth_certificate_number'),
                'gender_id' => $request->input('student.gender_id'),
                'religion_id' => $request->input('student.religion_id'),
                'citizenship' => $request->input('student.citizenship'),
                'county' => $request->input('student.county'),
                'ward' => $request->input('student.ward'),
                'permanent_address' => $request->input('student.permanent_address'),
                'kcpe_score' => $request->input('student.kcpe_score'),
                'previous_school' => $request->input('student.previous_school'),
                'physical_disability' => $request->input('other_details.physical_disability') ?? null,
                'hobby' => $request->input('other_details.hobby'),
                'medical_details' => $request->input('other_details.medical_details') ?? null,
                'character_book' => $request->input('other_details.character_book') ?? null,
            ]);

//            if (isset($request->guardians) && is_array($request->guardians)) {
//
//                $oldGuardianDetails = $student->guardians();
//
//                $student->guardians()->delete();
//
//                $guardianDetails = collect($request->guardians)
//                    ->filter(function ($guardian) {
//                        return isset($guardian['relationship_id'], $guardian['first_name']);
//                    })
//                    ->map(function ($guardian) use ($student) {
//                        return [
//                            'student_id' => $student->id,
//                            'relationship_id' => $guardian['relationship_id'],
//                            'first_name' => $guardian['first_name'],
//                            'middle_name' => $guardian['middle_name'],
//                            'last_name' => $guardian['last_name'],
//                            'email' => $guardian['email'],
//                            'phone' => $guardian['phone'],
//                            'profession' => $guardian['profession'],
//                            'identification_number' => $guardian['identification_number']
//                        ];
//                    })->toArray();
//
//                Guardian::insert($guardianDetails);
//            }

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
            return to_route('admin.admissions.index');

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
            'registration_details.division_id' => 'Please select a division.',
        ];

        $request->validate([
            'registration_details.date' => ['required', 'date', 'max:255'],
            'registration_details.division_id' => ['required', Rule::exists('divisions', 'id')],
        ], $errorMessages);

        if($studentAdmission)
        {
            return to_route('admin.admissions.edit', $studentAdmission);
        }

        return to_route('admin.admissions.form');
    }

    public function secondStep(Request $request, StudentAdmission $studentAdmission = null)
    {
        $errorMessages = [
            'student.first_name.required' => 'The first name is required.',
            'student.first_name.string' => 'The first name must be a valid string.',
            'student.first_name.max' => 'The first name may not be greater than 255 characters.',
            'student.middle_name.string' => 'The middle name must be a valid string.',
            'student.middle_name.max' => 'The middle name may not be greater than 255 characters.',
            'student.last_name.required' => 'The last name is required.',
            'student.last_name.string' => 'The last name must be a valid string.',
            'student.last_name.max' => 'The last name may not be greater than 255 characters.',
            'student.admission_number.required' => 'The admission number is required.',
            'student.admission_number.string' => 'The admission number must be a valid string.',
            'student.admission_number.max' => 'The admission number may not be greater than 255 characters.',
            'student.rank_id.required' => 'Please select a class first.',
            'student.rank_id.exists' => 'The selected class does not exist.',
            'student.gender_id.required' => 'The gender is required.',
            'student.gender_id.exists' => 'The selected gender is invalid.',
            'student.religion_id.required' => 'The religion is required.',
            'student.religion_id.exists' => 'The selected religion is invalid.',
            'student.date_of_birth.required' => 'The date of birth is required.',
            'student.date_of_birth.string' => 'The date of birth must be a valid string.',
            'student.date_of_birth.max' => 'The date of birth may not be greater than 255 characters.',
            'student.birth_certificate_number.string' => 'The birth certificate number must be a valid string.',
            'student.birth_certificate_number.max' => 'The birth certificate number may not be greater than 255 characters.',
            'student.citizenship.required' => 'The citizenship is required.',
            'student.citizenship.string' => 'The citizenship must be a valid string.',
            'student.citizenship.max' => 'The citizenship may not be greater than 255 characters.',
            'student.county.string' => 'The county must be a valid string.',
            'student.county.max' => 'The county may not be greater than 255 characters.',
            'student.ward.string' => 'The ward must be a valid string.',
            'student.ward.max' => 'The ward may not be greater than 255 characters.',
            'student.permanent_address.required' => 'The permanent address is required.',
            'student.permanent_address.string' => 'The permanent address must be a valid string.',
            'student.permanent_address.max' => 'The permanent address may not be greater than 255 characters.',
            'student.kcpe_score.string' => 'The KCPE score must be a valid string.',
            'student.kcpe_score.max' => 'The KCPE score may not be greater than 255 characters.',
            'student.previous_school.string' => 'The previous school must be a valid string.',
            'student.previous_school.max' => 'The previous school may not be greater than 255 characters.',
        ];
        
        $request->validate([
            'student.first_name' => ['required', 'string', 'max:255'],
            'student.middle_name' => ['nullable', 'string', 'max:255'],
            'student.last_name' => ['required', 'string', 'max:255'],
            'student.admission_number' => ['required', 'string', 'max:255'],
            'student.rank_id' => ['required', Rule::exists('ranks', 'id')],
            'student.gender_id' => ['required', Rule::exists('genders', 'id')],
            'student.religion_id' => ['required', Rule::exists('religions', 'id')],
            'student.date_of_birth' => ['nullable', 'string', 'max:255'],
            'student.birth_certificate_number' => ['nullable', 'string', 'max:255'],
            'student.citizenship' => ['nullable', 'string', 'max:255'],
            'student.county' => ['nullable', 'string', 'max:255'],
            'student.ward' => ['nullable', 'string', 'max:255'],
            'student.permanent_address' => ['nullable', 'string', 'max:255'],
            'student.kcpe_score' => ['nullable', 'string', 'max:255'],
            'student.previous_school' => ['nullable', 'string', 'max:255'],
        ], $errorMessages);

        if($studentAdmission)
        {
            return to_route('admin.admissions.edit', $studentAdmission);
        }

        return to_route('admin.admissions.form');
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
            return to_route('admin.admissions.edit', $studentAdmission);
        }

        return to_route('admin.admissions.form');
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
