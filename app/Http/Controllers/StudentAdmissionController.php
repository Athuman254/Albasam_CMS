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
use App\Http\Controllers\Fee\FeeStructureController;

class StudentAdmissionController extends Controller
{
   public function dataTable(Request $request)
{
    Log::info('=== STUDENT ADMISSIONS DATATABLE ===');

    try {
        // Build query with relationships
        $query = StudentAdmission::with([
            'student',
            'division',
            'student.rank',
            'student.gender'
        ])->orderBy('created_at', 'desc');

        // Apply search filter
        if ($request->has('filter.search') && !empty($request->filter['search'])) {
            $search = $request->filter['search'];
            Log::info("Applying search filter: {$search}");

            $query->where(function ($q) use ($search) {
                $q->whereHas('student', function ($studentQuery) use ($search) {
                    $studentQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%");
                })
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Get pagination parameters
        Log::info('Request parameters:', $request->all());
        $perPage = $request->input('page.size', 20); // Default to 20 if not specified
        $currentPage = $request->input('page.number', 1);

        Log::info("Pagination params - perPage: {$perPage}, currentPage: {$currentPage}");

        // Paginate with error handling
        $admissions = $query->paginate($perPage, ['*'], 'page', $currentPage);

        Log::info('Pagination successful', [
            'total' => $admissions->total(),
            'current_page' => $admissions->currentPage(),
            'last_page' => $admissions->lastPage(),
            'per_page' => $admissions->perPage()
        ]);

        $transformedData = $admissions->through(function ($admission) {
            return [
                'id' => $admission->id,
                'hashid' => $admission->hashid,
                'date' => $admission->created_at ? $admission->created_at->toISOString() : null,
                'formatted_date' => $admission->formatted_date,
                'student_name' => $admission->student_name,
                'admission_number' => $admission->admission_number,
                'student_class' => $admission->student_class,
                'division_name' => $admission->division_name,
                'is_active' => $admission->is_active,
                'has_student' => $admission->has_student,
                'student' => $admission->student ? [
                    'id' => $admission->student->id,
                    'first_name' => $admission->student->first_name,
                    'last_name' => $admission->student->last_name,
                    'admission_number' => $admission->student->admission_number,
                ] : null,
                'division' => $admission->division ? [
                    'id' => $admission->division->id,
                    'name' => $admission->division->name,
                ] : null,
            ];
        });

        return Resource::collection($transformedData);
    } catch (\Exception $e) {
        Log::error('DATATABLE ERROR: ' . $e->getMessage());
        Log::error('Stack trace: ' . $e->getTraceAsString());

        return response()->json([
            'error' => 'Failed to fetch admissions data',
            'message' => $e->getMessage(),
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => $perPage ?? 20,
                'total' => 0
            ]
        ], 500);
    }
}

    public function index()
    {
        Log::info('🎯 STUDENT ADMISSION CONTROLLER INDEX METHOD CALLED');
        Log::info('📁 Component path: Admin/StudentAdmissions/Index');

        try {
            Log::info('🔄 Attempting Inertia render...');
            $response = Inertia::render('Admin/StudentAdmissions/Index');
            Log::info('✅ INERTIA RENDER SUCCESS - Student admissions index page should load');
            return $response;
        } catch (\Exception $e) {
            Log::error('❌ INERTIA RENDER FAILED: ' . $e->getMessage());
            Log::error('📝 Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    public function create()
    {
        Log::info('🎯 STUDENT ADMISSION CONTROLLER CREATE METHOD CALLED');
        Log::info('📁 Component path: Admin/StudentAdmissions/Create');

        try {
            $response = Inertia::render('Admin/StudentAdmissions/Create');
            Log::info('✅ INERTIA RENDER SUCCESS - Student admission create page should load');
            return $response;
        } catch (\Exception $e) {
            Log::error('❌ INERTIA RENDER FAILED: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate the next admission number in format ADM01, ADM02, etc.
     */
    private function generateAdmissionNumber(): string
    {
        try {
            // Get the latest admission number
            $latestStudent = Student::orderBy('id', 'desc')->first();

            if ($latestStudent && !empty($latestStudent->admission_number)) {
                // Extract the numeric part from the latest admission number
                $latestNumber = preg_replace('/[^0-9]/', '', $latestStudent->admission_number);

                if ($latestNumber !== '') {
                    $nextNumber = (int)$latestNumber + 1;
                } else {
                    // If no numeric part found, start from 1
                    $nextNumber = 1;
                }
            } else {
                // If no students exist yet, start from 1
                $nextNumber = 1;
            }

            // Format the number with leading zeros (at least 2 digits)
            $formattedNumber = str_pad($nextNumber, 2, '0', STR_PAD_LEFT);

            return "ADM{$formattedNumber}";
        } catch (\Exception $e) {
            Log::error('Error generating admission number: ' . $e->getMessage());

            // Fallback: use timestamp-based number
            $fallbackNumber = date('YmdHis');
            return "ADM{$fallbackNumber}";
        }
    }

    /**
     * API endpoint to generate admission number for frontend
     */
    public function generateAdmissionNumberApi()
    {
        try {
            $admissionNumber = $this->generateAdmissionNumber();

            return response()->json([
                'success' => true,
                'admission_number' => $admissionNumber,
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating admission number: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate admission number',
            ], 500);
        }
    }

    public function store(StudentAdmissionRequest $request)
    {
        $validated = $request->validated();
        $defaultDivision = Division::first();

        DB::beginTransaction();
        try {
            // Generate admission number if not provided (for backward compatibility)
            $admissionNumber = $validated['student']['admission_number'] ?? $this->generateAdmissionNumber();

            // Create admission - REMOVE 'date' field
            $admission = StudentAdmission::create([
                'division_id' => $validated['registration_details']['division_id'],
            ]);

            $student = Student::create([
                'student_admission_id' => $admission->id,
                'admission_number' => $admissionNumber,
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

                if (!empty($siblingRecords)) {
                    Sibling::insert($siblingRecords);
                }
            }

            DB::commit();

            // Auto-apply relevant fees after student is created successfully
            if ($student) {
                $this->applyRelevantFeesToNewStudent($student);
            }

            Log::info('🎉 STUDENT ADMISSION CREATION SUCCESSFUL - Redirecting to index');
            return to_route('admin.admissions.index');
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('❌ STUDENT ADMISSION CREATION FAILED: ' . $exception->getMessage());
            Log::error('📝 Stack trace: ' . $exception->getTraceAsString());
            report($exception);
            return to_route('admin.admissions.form');
        }
    }

    /**
     * Apply relevant fees to newly created student
     */
    private function applyRelevantFeesToNewStudent(Student $student)
    {
        try {
            Log::info("Auto-applying fees to new student", [
                'student_id' => $student->id,
                'student_name' => $student->full_name,
                'class_id' => $student->rank_id,
                'admission_number' => $student->admission_number
            ]);

            $feeController = app(FeeStructureController::class);
            $result = $feeController->applyRelevantFeesToStudent($student);

            if ($result['success']) {
                Log::info("Auto-applied fees to new student successfully", [
                    'student_id' => $student->id,
                    'applied_count' => $result['applied_count'],
                    'message' => $result['message']
                ]);
            } else {
                Log::error("Failed to auto-apply fees to new student", [
                    'student_id' => $student->id,
                    'message' => $result['message']
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in applyRelevantFeesToNewStudent: ' . $e->getMessage(), [
                'student_id' => $student->id,
                'exception' => $e
            ]);

            return [
                'success' => false,
                'message' => 'Failed to apply fees automatically: ' . $e->getMessage()
            ];
        }
    }

    public function show($id)
    {
        Log::info('🎯 STUDENT ADMISSION CONTROLLER SHOW METHOD CALLED');
        Log::info('📁 Student Admission ID: ' . $id);

        try {
            // Find by ID but don't load the student relationship with computed properties
            $admission = StudentAdmission::with([
                'division',
                'student.rank.stream',
                'student.gender',
                'student.religion',
                'student.guardians.relationship',
                'student.siblings.gender',
            ])->find($id);

            if (!$admission) {
                Log::error('❌ Student admission not found with ID: ' . $id);
                abort(404, 'Student admission not found');
            }

            Log::info('✅ Student admission found: ' . $admission->id);

            // Create a simple array representation without triggering computed properties
            $responseData = [
                'studentAdmission' => [
                    'id' => $admission->id,
                    'hashid' => $admission->hashid,
                    'division_id' => $admission->division_id,
                    'has_exit_school' => $admission->has_exit_school,
                    'created_at' => $admission->created_at,
                    'updated_at' => $admission->updated_at,
                    'formatted_date' => $admission->formatted_date,
                    'is_active' => $admission->is_active,
                    'student_name' => $admission->student_name,
                    'admission_number' => $admission->admission_number,
                    'student_class' => $admission->student_class,
                    'division_name' => $admission->division_name,
                    'has_student' => $admission->has_student,
                ],
                'student' => null,
            ];

            // Manually build student data without computed properties
            if ($admission->student) {
                $student = $admission->student;
                $responseData['student'] = [
                    'id' => $student->id,
                    'first_name' => $student->first_name,
                    'middle_name' => $student->middle_name,
                    'last_name' => $student->last_name,
                    'admission_number' => $student->admission_number,
                    'date_of_birth' => $student->date_of_birth,
                    'birth_certificate_number' => $student->birth_certificate_number,
                    'citizenship' => $student->citizenship,
                    'county' => $student->county,
                    'ward' => $student->ward,
                    'permanent_address' => $student->permanent_address,
                    'kcpe_score' => $student->kcpe_score,
                    'previous_school' => $student->previous_school,
                    'physical_disability' => $student->physical_disability,
                    'hobby' => $student->hobby,
                    'medical_details' => $student->medical_details,
                    'character_book' => $student->character_book,
                    'full_name' => $student->full_name,
                    'age' => $student->age,
                    'formatted_dob' => $student->formatted_dob,
                    'rank' => $student->rank ? ['id' => $student->rank->id, 'name' => $student->rank->name] : null,
                    'gender' => $student->gender ? ['id' => $student->gender->id, 'name' => $student->gender->name] : null,
                    'religion' => $student->religion ? ['id' => $student->religion->id, 'name' => $student->religion->name] : null,
                ];

                // Add guardians and siblings
                $responseData['student']['guardians'] = $student->guardians->map(function ($guardian) {
                    return [
                        'id' => $guardian->id,
                        'first_name' => $guardian->first_name,
                        'middle_name' => $guardian->middle_name,
                        'last_name' => $guardian->last_name,
                        'email' => $guardian->email,
                        'phone' => $guardian->phone,
                        'identification_number' => $guardian->identification_number,
                        'profession' => $guardian->profession,
                        'relationship' => $guardian->relationship ? [
                            'id' => $guardian->relationship->id,
                            'name' => $guardian->relationship->name,
                        ] : null,
                    ];
                });

                $responseData['student']['siblings'] = $student->siblings->map(function ($sibling) {
                    return [
                        'id' => $sibling->id,
                        'name' => $sibling->name,
                        'age' => $sibling->age,
                        'gender_id' => $sibling->gender_id,
                        'current_school' => $sibling->current_school,
                        'current_class' => $sibling->current_class,
                        'gender' => $sibling->gender ? [
                            'id' => $sibling->gender->id,
                            'name' => $sibling->gender->name,
                        ] : null,
                    ];
                });
            }

            return Inertia::render('Admin/StudentAdmissions/Show', $responseData);
        } catch (\Exception $e) {
            Log::error('❌ Error in show method: ' . $e->getMessage());
            Log::error('📝 Stack trace: ' . $e->getTraceAsString());

            abort(500, 'Failed to load student admission details: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        Log::info('🎯 STUDENT ADMISSION CONTROLLER EDIT METHOD CALLED');
        Log::info('📁 Student Admission ID: ' . $id);

        try {
            // Find by ID (not hashid)
            $admission = StudentAdmission::with([
                'division',
                'student.rank',
                'student.gender',
                'student.religion',
                'student.guardians.relationship',
                'student.siblings.gender',
            ])->find($id);

            if (!$admission) {
                Log::error('❌ Student admission not found with ID: ' . $id);
                abort(404, 'Student admission not found');
            }

            Log::info('✅ Student admission found: ' . $admission->id);

            return Inertia::render('Admin/StudentAdmissions/Edit', [
                'studentAdmission' => $admission,
                'student' => $admission->student,
                'guardians' => $admission->student ? $admission->student->guardians : [],
                'siblings' => $admission->student ? $admission->student->siblings : [],
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error in edit method: ' . $e->getMessage());
            Log::error('📝 Stack trace: ' . $e->getTraceAsString());

            abort(500, 'Failed to load student admission for editing');
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('🎯 STUDENT ADMISSION CONTROLLER UPDATE METHOD CALLED');
        Log::info('📁 Student Admission ID: ' . $id);

        $studentAdmission = StudentAdmission::find($id);

        if (!$studentAdmission) {
            Log::error('❌ Student admission not found with ID: ' . $id);
            return redirect()->back()->withErrors(['message' => 'Student admission not found.']);
        }

        Log::info('📁 Student Admission Hashid: ' . $studentAdmission->hashid);
        Log::info('📦 Full Request Data:', $request->all());

        DB::beginTransaction();
        try {
            // Get the student first to check if it exists
            $student = $studentAdmission->student;

            if (!$student) {
                Log::error('❌ No student found for admission ID: ' . $studentAdmission->id);
                throw new \Exception('No student found for this admission. Student Admission ID: ' . $studentAdmission->id);
            }

            Log::info('✅ Student found: ' . $student->id);

            // Basic validation for required fields
            $request->validate([
                'registration_details.division_id' => ['required', 'exists:divisions,id'],
                'student.first_name' => ['required', 'string', 'max:255'],
                'student.last_name' => ['required', 'string', 'max:255'],
                'student.rank_id' => ['required', 'exists:ranks,id'],
                'student.gender_id' => ['required', 'exists:genders,id'],
                'student.religion_id' => ['required', 'exists:religions,id'],
                'student.date_of_birth' => ['nullable', 'date'],
            ]);

            Log::info('✅ Validation passed');

            // Update admission
            $studentAdmission->update([
                'division_id' => $request->input('registration_details.division_id'),
            ]);

            Log::info('✅ Admission updated');

            // Update student
            $studentData = [
                'first_name' => $request->input('student.first_name'),
                'middle_name' => $request->input('student.middle_name', ''),
                'last_name' => $request->input('student.last_name'),
                'rank_id' => $request->input('student.rank_id'),
                'date_of_birth' => $request->input('student.date_of_birth'),
                'birth_certificate_number' => $request->input('student.birth_certificate_number', ''),
                'gender_id' => $request->input('student.gender_id'),
                'religion_id' => $request->input('student.religion_id'),
                'citizenship' => $request->input('student.citizenship', ''),
                'county' => $request->input('student.county', ''),
                'ward' => $request->input('student.ward', ''),
                'permanent_address' => $request->input('student.permanent_address', ''),
                'kcpe_score' => $request->input('student.kcpe_score', ''),
                'previous_school' => $request->input('student.previous_school', ''),
                'physical_disability' => $request->input('other_details.physical_disability', ''),
                'hobby' => $request->input('other_details.hobby', ''),
                'medical_details' => $request->input('other_details.medical_details', ''),
                'character_book' => $request->input('other_details.character_book', ''),
            ];

            Log::info('📦 Student Update Data:', $studentData);

            $student->update($studentData);
            Log::info('✅ Student updated');

            // Handle guardians
            if ($request->has('guardians') && is_array($request->input('guardians'))) {
                Log::info('🔄 Processing guardians...');
                $student->guardians()->delete();

                $guardianRecords = collect($request->input('guardians'))
                    ->filter(function ($guardian) {
                        return !empty($guardian['first_name']) && !empty($guardian['last_name']);
                    })
                    ->map(function ($guardian) use ($student) {
                        return [
                            'student_id' => $student->id,
                            'relationship_id' => $guardian['relationship_id'] ?? null,
                            'first_name' => $guardian['first_name'],
                            'middle_name' => $guardian['middle_name'] ?? null,
                            'last_name' => $guardian['last_name'],
                            'email' => $guardian['email'] ?? null,
                            'phone' => $guardian['phone'] ?? null,
                            'profession' => $guardian['profession'] ?? null,
                            'identification_number' => $guardian['identification_number'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    })->toArray();

                if (!empty($guardianRecords)) {
                    Guardian::insert($guardianRecords);
                    Log::info('✅ Guardians updated: ' . count($guardianRecords) . ' records');
                } else {
                    Log::info('ℹ️ No guardians to update');
                }
            }

            // Handle siblings
            if ($request->has('other_details.siblings') && is_array($request->input('other_details.siblings'))) {
                Log::info('🔄 Processing siblings...');
                $student->siblings()->delete();

                $siblingRecords = collect($request->input('other_details.siblings'))
                    ->filter(function ($sibling) {
                        return !empty($sibling['name']);
                    })
                    ->map(function ($sibling) use ($student) {
                        return [
                            'student_id' => $student->id,
                            'name' => $sibling['name'],
                            'age' => $sibling['age'] ?? null,
                            'gender_id' => $sibling['gender_id'] ?? null,
                            'current_school' => $sibling['current_school'] ?? null,
                            'current_class' => $sibling['current_class'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    })->toArray();

                if (!empty($siblingRecords)) {
                    Sibling::insert($siblingRecords);
                    Log::info('✅ Siblings updated: ' . count($siblingRecords) . ' records');
                } else {
                    Log::info('ℹ️ No siblings to update');
                }
            }

            DB::commit();
            Log::info('🎉 STUDENT ADMISSION UPDATE SUCCESSFUL - Redirecting to index');

            // If class was changed, re-apply relevant fees
            if ($student->wasChanged('rank_id')) {
                $this->applyRelevantFeesToStudentAfterClassChange($student);
            }

            return redirect()->route('admin.admissions.index')->with('success', 'Student admission updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            Log::error('❌ VALIDATION ERROR: ' . $e->getMessage());
            Log::error('📝 Validation errors:', $e->errors());
            return redirect()->back()->withInput()->withErrors($e->errors());
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('❌ STUDENT ADMISSION UPDATE FAILED: ' . $exception->getMessage());
            Log::error('📝 Stack trace: ' . $exception->getTraceAsString());
            Log::error('📝 File: ' . $exception->getFile());
            Log::error('📝 Line: ' . $exception->getLine());

            return redirect()->back()->withInput()->withErrors(['message' => 'Failed to update student details: ' . $exception->getMessage()]);
        }
    }
    /**
     * Apply relevant fees when student class is changed
     */
    private function applyRelevantFeesToStudentAfterClassChange(Student $student)
    {
        try {
            Log::info("Student class changed, applying relevant fees", [
                'student_id' => $student->id,
                'student_name' => $student->full_name,
                'new_class_id' => $student->rank_id,
                'admission_number' => $student->admission_number
            ]);

            $feeController = app(FeeStructureController::class);
            $result = $feeController->applyRelevantFeesToStudent($student);

            if ($result['success']) {
                Log::info("Applied fees after class change successfully", [
                    'student_id' => $student->id,
                    'applied_count' => $result['applied_count'],
                    'message' => $result['message']
                ]);
            } else {
                Log::warning("Failed to apply fees after class change", [
                    'student_id' => $student->id,
                    'message' => $result['message']
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Error applying fees after class change: ' . $e->getMessage(), [
                'student_id' => $student->id
            ]);

            return [
                'success' => false,
                'message' => 'Failed to apply fees after class change: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Updated firstStep method to handle both create and edit scenarios
     */
    public function firstStep(Request $request)
    {
        Log::info('🎯 FIRST STEP METHOD CALLED');
        Log::info('📦 Request data:', $request->all());

        $errorMessages = [
            'registration_details.division_id' => 'Please select a division.',
        ];

        $request->validate([
            'registration_details.division_id' => ['required', Rule::exists('divisions', 'id')],
        ], $errorMessages);

        // Handle edit scenario
        if ($request->has('student_admission_id')) {
            $studentAdmission = StudentAdmission::find($request->student_admission_id);
            if ($studentAdmission) {
                // Update the division
                $studentAdmission->update([
                    'division_id' => $request->input('registration_details.division_id')
                ]);

                Log::info('✅ Division updated for admission ID: ' . $studentAdmission->id);

                // Return success response for Inertia
                if ($request->header('X-Inertia')) {
                    return back()->with('success', 'Division updated successfully');
                }

                return response()->json(['success' => true, 'message' => 'Division updated']);
            } else {
                Log::error('❌ Student admission not found with ID: ' . $request->student_admission_id);

                if ($request->header('X-Inertia')) {
                    return back()->withErrors(['error' => 'Student admission not found']);
                }

                return response()->json(['success' => false, 'message' => 'Student admission not found'], 404);
            }
        }

        // For create scenario, continue with session or other logic
        Log::info('✅ First step validation passed for create scenario');

        if ($request->header('X-Inertia')) {
            return back()->with('success', 'First step completed');
        }

        return response()->json(['success' => true]);
    }

    /**
     * Updated secondStep method to handle both create and edit scenarios
     */
    public function secondStep(Request $request)
    {
        Log::info('🎯 SECOND STEP METHOD CALLED');
        Log::info('📦 Request data:', $request->all());

        $errorMessages = [
            'student.first_name.required' => 'The first name is required.',
            'student.first_name.string' => 'The first name must be a valid string.',
            'student.first_name.max' => 'The first name may not be greater than 255 characters.',
            'student.middle_name.string' => 'The middle name must be a valid string.',
            'student.middle_name.max' => 'The middle name may not be greater than 255 characters.',
            'student.last_name.required' => 'The last name is required.',
            'student.last_name.string' => 'The last name must be a valid string.',
            'student.last_name.max' => 'The last name may not be greater than 255 characters.',
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

        // Handle edit scenario
        if ($request->has('student_admission_id')) {
            $studentAdmission = StudentAdmission::with('student')->find($request->student_admission_id);
            if ($studentAdmission && $studentAdmission->student) {
                // Update student details
                $studentAdmission->student->update([
                    'first_name' => $request->input('student.first_name'),
                    'middle_name' => $request->input('student.middle_name'),
                    'last_name' => $request->input('student.last_name'),
                    'rank_id' => $request->input('student.rank_id'),
                    'gender_id' => $request->input('student.gender_id'),
                    'religion_id' => $request->input('student.religion_id'),
                    'date_of_birth' => $request->input('student.date_of_birth'),
                    'birth_certificate_number' => $request->input('student.birth_certificate_number'),
                    'citizenship' => $request->input('student.citizenship'),
                    'county' => $request->input('student.county'),
                    'ward' => $request->input('student.ward'),
                    'permanent_address' => $request->input('student.permanent_address'),
                    'kcpe_score' => $request->input('student.kcpe_score'),
                    'previous_school' => $request->input('student.previous_school'),
                ]);

                Log::info('✅ Student details updated for admission ID: ' . $studentAdmission->id);

                // Return success response for Inertia
                if ($request->header('X-Inertia')) {
                    return back()->with('success', 'Student details updated successfully');
                }

                return response()->json(['success' => true, 'message' => 'Student details updated']);
            } else {
                Log::error('❌ Student admission or student not found with ID: ' . $request->student_admission_id);

                if ($request->header('X-Inertia')) {
                    return back()->withErrors(['error' => 'Student admission or student not found']);
                }

                return response()->json(['success' => false, 'message' => 'Student admission or student not found'], 404);
            }
        }

        Log::info('✅ Second step validation passed for create scenario');

        if ($request->header('X-Inertia')) {
            return back()->with('success', 'Second step completed');
        }

        return response()->json(['success' => true]);
    }

    /**
     * Updated thirdStep method to handle both create and edit scenarios
     */
    public function thirdStep(Request $request)
    {
        Log::info('🎯 THIRD STEP METHOD CALLED');
        Log::info('📦 Request data:', $request->all());

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
            'guardians' => ['nullable', 'array'],
            'guardians.*.relationship_id' => ['nullable', Rule::exists('relationships', 'id')],
            'guardians.*.first_name' => ['nullable', 'string', 'max:255'],
            'guardians.*.middle_name' => ['nullable', 'string', 'max:255'],
            'guardians.*.last_name' => ['nullable', 'string', 'max:255'],
            'guardians.*.email' => ['nullable', 'email'],
            'guardians.*.phone' => ['nullable', 'string'],
            'guardians.*.identification_number' => ['nullable', 'string'],
            'guardians.*.profession' => ['nullable', 'string'],
        ], $errorMessages);

        // Handle edit scenario - guardians are handled separately in the main update
        if ($request->has('student_admission_id')) {
            Log::info('✅ Third step validation passed for edit scenario - guardians will be handled in main update');

            if ($request->header('X-Inertia')) {
                return back()->with('success', 'Third step completed');
            }

            return response()->json(['success' => true, 'message' => 'Step validated']);
        }

        Log::info('✅ Third step validation passed for create scenario');

        if ($request->header('X-Inertia')) {
            return back()->with('success', 'Third step completed');
        }

        return response()->json(['success' => true]);
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

    public function requestForAdmission(Request $request)
    {
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
            function () {},
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
