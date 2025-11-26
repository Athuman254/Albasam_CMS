<?php

namespace App\Http\Controllers\Fee;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\Student;
use App\Models\FeePayment;
use App\Models\Rank;
use App\Models\Settings\AcademicYear;
use App\Models\FeeStructure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FeeController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Fees/Index', [
            'fees' => Fee::with(['student', 'rank'])
                ->latest()
                ->paginate(20),
            'academic_years' => AcademicYear::all(),
            'ranks' => Rank::all(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Fees/Create', [
            'students' => Student::with('rank')->get(),
            'ranks' => Rank::all(),
            'academic_years' => AcademicYear::all(),
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Fee creation started', $request->all());

        $request->validate([
            'rank_id' => 'required|exists:ranks,id',
            'fee_type' => 'required|string|in:tuition,activity,exam,library,sports,transport,hostel,other',
            'amount' => 'required|numeric|min:0',
            'academic_year' => 'required|string',
            'term' => 'required|string|in:1,2,3',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Get all students in the selected class
            $students = Student::where('rank_id', $request->rank_id)->get();
            
            Log::info("Students found in class {$request->rank_id}: " . $students->count());

            $createdCount = 0;
            $updatedCount = 0;
            $errors = [];

            // If there are students, create/update fees for each student
            if ($students->isNotEmpty()) {
                foreach ($students as $student) {
                    try {
                        // Check if fee already exists for this student, academic year, term, and fee type
                        $existingFee = Fee::where('student_id', $student->id)
                            ->where('rank_id', $request->rank_id)
                            ->where('fee_type', $request->fee_type)
                            ->where('academic_year', $request->academic_year)
                            ->where('term', $request->term)
                            ->first();

                        if (!$existingFee) {
                            // Create new fee
                            Fee::create([
                                'student_id' => $student->id,
                                'rank_id' => $request->rank_id,
                                'original_fee_structure_id' => null,
                                'fee_type' => $request->fee_type,
                                'amount' => $request->amount,
                                'paid_amount' => 0,
                                'balance' => $request->amount,
                                'academic_year' => $request->academic_year,
                                'term' => $request->term,
                                'due_date' => $request->due_date,
                                'status' => 'pending',
                                'is_carry_over' => false,
                                'description' => $request->description,
                            ]);
                            $createdCount++;
                            Log::info("Created fee for student: {$student->first_name} {$student->last_name}");
                        } else {
                            // Update existing fee regardless of payments
                            $newBalance = $request->amount - $existingFee->paid_amount;
                            
                            $existingFee->update([
                                'amount' => $request->amount,
                                'balance' => max(0, $newBalance),
                                'due_date' => $request->due_date,
                                'description' => $request->description,
                                'status' => $newBalance <= 0 ? 'paid' : ($existingFee->paid_amount > 0 ? 'partial' : 'pending'),
                            ]);
                            $updatedCount++;
                            Log::info("Updated fee for student: {$student->first_name} {$student->last_name} (Amount: {$request->amount}, Paid: {$existingFee->paid_amount}, New Balance: {$newBalance})");
                        }
                    } catch (\Exception $e) {
                        $errorMsg = "Error processing fee for {$student->first_name} {$student->last_name}: " . $e->getMessage();
                        $errors[] = $errorMsg;
                        Log::error($errorMsg);
                    }
                }
            } else {
                // No students in class - create fee structure template
                try {
                    $feeStructure = FeeStructure::create([
                        'rank_id' => $request->rank_id,
                        'academic_year' => $request->academic_year,
                        'term' => $request->term,
                        'amount' => $request->amount,
                        'description' => $request->description . ' [CLASS TEMPLATE - Created: ' . now()->format('Y-m-d H:i:s') . ']',
                        'due_date' => $request->due_date,
                        'is_active' => true,
                    ]);
                    $createdCount = 1; // Count as one template created
                    Log::info("Created fee structure template for class {$request->rank_id}");
                } catch (\Exception $e) {
                    $errorMsg = "Error creating fee template: " . $e->getMessage();
                    $errors[] = $errorMsg;
                    Log::error($errorMsg);
                }
            }

            if ($createdCount === 0 && $updatedCount === 0) {
                DB::rollBack();
                $errorMessage = 'No fees were created or updated. ';
                if (!empty($errors)) {
                    $errorMessage .= implode(', ', array_slice($errors, 0, 3));
                }
                Log::error('Fee creation failed: ' . $errorMessage);
                return back()->with('error', $errorMessage);
            }

            DB::commit();

            // Success message based on what was created/updated
            if ($students->isEmpty()) {
                $successMessage = "Class fee template created successfully! This fee structure will apply to future students in this class.";
            } else {
                $successMessage = "Fees processed successfully! Created: {$createdCount}, Updated: {$updatedCount}";
                if (!empty($errors)) {
                    $successMessage .= " Note: " . implode(', ', array_slice($errors, 0, 3));
                    if (count($errors) > 3) {
                        $successMessage .= " and " . (count($errors) - 3) . " more";
                    }
                }
            }

            Log::info('Fee creation completed successfully: ' . $successMessage);
            return redirect()->route('admin.fees.index')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee creation error: ' . $e->getMessage());
            Log::error('Request data: ', $request->all());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Error creating fees: ' . $e->getMessage());
        }
    }

    public function show(Fee $fee)
    {
        $fee->load(['student', 'rank', 'payments' => function($query) {
            $query->latest();
        }]);

        return Inertia::render('Admin/Fees/Show', [
            'fee' => $fee,
        ]);
    }

    public function edit(Fee $fee)
    {
        $fee->load(['student', 'rank']);

        return Inertia::render('Admin/Fees/Edit', [
            'fee' => $fee,
            'students' => Student::with('rank')->get(),
            'ranks' => Rank::all(),
            'academic_years' => AcademicYear::all(),
        ]);
    }

    public function update(Request $request, Fee $fee)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'rank_id' => 'required|exists:ranks,id',
            'fee_type' => 'required|string|in:tuition,activity,exam,library,sports,transport,hostel,other',
            'amount' => 'required|numeric|min:0',
            'academic_year' => 'required|string',
            'term' => 'required|string|in:1,2,3',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Recalculate balance if amount changes
            $newBalance = $request->amount - $fee->paid_amount;
            
            $fee->update([
                'student_id' => $request->student_id,
                'rank_id' => $request->rank_id,
                'fee_type' => $request->fee_type,
                'amount' => $request->amount,
                'balance' => max(0, $newBalance),
                'academic_year' => $request->academic_year,
                'term' => $request->term,
                'due_date' => $request->due_date,
                'description' => $request->description,
                'status' => $newBalance <= 0 ? 'paid' : ($fee->paid_amount > 0 ? 'partial' : 'pending'),
            ]);

            DB::commit();

            Log::info("Fee updated successfully: {$fee->id}");
            return redirect()->route('admin.fees.index')
                ->with('success', 'Fee updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating fee: ' . $e->getMessage());
            return back()->with('error', 'Error updating fee: ' . $e->getMessage());
        }
    }

    public function destroy(Fee $fee)
    {
        try {
            $fee->delete();
            Log::info("Fee deleted successfully: {$fee->id}");
            return redirect()->route('admin.fees.index')
                ->with('success', 'Fee deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting fee: ' . $e->getMessage());
            return back()->with('error', 'Error deleting fee: ' . $e->getMessage());
        }
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'fee_ids' => 'required|array',
            'fee_ids.*' => 'exists:fees,id',
        ]);

        try {
            DB::beginTransaction();
            
            $deletedCount = Fee::whereIn('id', $request->fee_ids)->delete();
            
            DB::commit();

            Log::info("Bulk deleted {$deletedCount} fees");
            return redirect()->route('admin.fees.index')
                ->with('success', 'Selected fees deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting fees in bulk: ' . $e->getMessage());
            return back()->with('error', 'Error deleting fees: ' . $e->getMessage());
        }
    }

    public function studentFees(Student $student)
    {
        $fees = Fee::with(['rank'])
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        return Inertia::render('Admin/Fees/StudentFees', [
            'student' => $student->load('rank'),
            'fees' => $fees,
            'total_balance' => $fees->sum('balance'),
        ]);
    }

    public function manualPayment(Request $request, Student $student)
    {
        $request->validate([
            'fee_id' => 'required|exists:fees,id',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:mpesa,bank,cash',
            'reference_number' => 'required|string',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $fee = Fee::findOrFail($request->fee_id);
            
            if ($request->amount > $fee->balance) {
                throw new \Exception('Payment amount cannot exceed outstanding balance.');
            }

            // Create payment record
            FeePayment::create([
                'fee_id' => $fee->id,
                'student_id' => $student->id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'transaction_id' => null,
                'payment_date' => $request->payment_date,
                'status' => 'completed',
                'notes' => $request->notes,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            // Update fee balance using the model's method for consistency
            $fee->applyPayment([
                'amount' => $request->amount
            ]);

            DB::commit();

            Log::info("Manual payment recorded for student {$student->id}, fee {$fee->id}, amount: {$request->amount}");
            return redirect()->route('admin.fees.students.show', $student->id)
                ->with('success', 'Manual payment recorded successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recording payment: ' . $e->getMessage());
            return back()->with('error', 'Error recording payment: ' . $e->getMessage());
        }
    }

    public function searchStudent($admissionNumber)
    {
        $student = Student::where('admission_number', $admissionNumber)
            ->with(['rank', 'fees' => function($query) {
                $query->where('balance', '>', 0);
            }])
            ->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found.'
            ]);
        }

        return response()->json([
            'success' => true,
            'student' => $student,
            'outstanding_fees' => $student->fees
        ]);
    }

    public function getStudentBalance(Student $student)
    {
        $balance = Fee::where('student_id', $student->id)
            ->where('balance', '>', 0)
            ->sum('balance');

        return response()->json([
            'success' => true,
            'student' => $student,
            'balance' => $balance
        ]);
    }

    public function getClassStudents($rankId)
    {
        $students = Student::where('rank_id', $rankId)
            ->with('rank')
            ->get();

        return response()->json([
            'success' => true,
            'students' => $students,
            'count' => $students->count()
        ]);
    }

    public function dataTable(Request $request)
    {
        $fees = Fee::with(['student', 'rank'])
            ->when($request->has('academic_year') && $request->academic_year, function($query) use ($request) {
                $query->where('academic_year', $request->academic_year);
            })
            ->when($request->has('term') && $request->term, function($query) use ($request) {
                $query->where('term', $request->term);
            })
            ->when($request->has('status') && $request->status, function($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->has('fee_type') && $request->fee_type, function($query) use ($request) {
                $query->where('fee_type', $request->fee_type);
            })
            ->when($request->has('rank_id') && $request->rank_id, function($query) use ($request) {
                $query->where('rank_id', $request->rank_id);
            })
            ->latest();

        return datatables()->eloquent($fees)
            ->addColumn('student_name', function($fee) {
                $name = $fee->student ? $fee->student->first_name . ' ' . $fee->student->last_name : 'N/A';
                
                // Check if this is a template fee
                if (strpos($fee->description, '[CLASS TEMPLATE]') !== false) {
                    $name .= ' (Class Template)';
                }
                
                return $name;
            })
            ->addColumn('admission_number', function($fee) {
                return $fee->student ? $fee->student->admission_number : 'N/A';
            })
            ->addColumn('class', function($fee) {
                return $fee->rank->name;
            })
            ->addColumn('fee_type_formatted', function($fee) {
                return $fee->fee_type_formatted;
            })
            ->addColumn('status_badge', function($fee) {
                $badgeClass = [
                    'paid' => 'bg-success',
                    'partial' => 'bg-warning',
                    'pending' => 'bg-secondary',
                    'overdue' => 'bg-danger'
                ][$fee->status] ?? 'bg-secondary';

                // Special styling for template fees
                if (strpos($fee->description, '[CLASS TEMPLATE]') !== false) {
                    $badgeClass = 'bg-info';
                }

                return '<span class="badge '.$badgeClass.'">'.ucfirst($fee->status).'</span>';
            })
            ->addColumn('amount_formatted', function($fee) {
                return $fee->formatted_amount;
            })
            ->addColumn('balance_formatted', function($fee) {
                return $fee->formatted_balance;
            })
            ->addColumn('due_date_formatted', function($fee) {
                return $fee->due_date->format('M j, Y');
            })
            ->addColumn('actions', function($fee) {
                return '
                    <a href="'.route('admin.fees.show', $fee->id).'" class="btn btn-sm btn-primary">View</a>
                    <a href="'.route('admin.fees.edit', $fee->id).'" class="btn btn-sm btn-warning">Edit</a>
                ';
            })
            ->rawColumns(['status_badge', 'actions'])
            ->toJson();
    }

    public function studentFeesDataTable(Student $student)
    {
        $fees = Fee::with(['rank'])
            ->where('student_id', $student->id)
            ->latest();

        return datatables()->eloquent($fees)
            ->addColumn('class', function($fee) {
                return $fee->rank->name;
            })
            ->addColumn('fee_type_formatted', function($fee) {
                return $fee->fee_type_formatted;
            })
            ->addColumn('status_badge', function($fee) {
                $badgeClass = [
                    'paid' => 'bg-success',
                    'partial' => 'bg-warning',
                    'pending' => 'bg-secondary',
                    'overdue' => 'bg-danger'
                ][$fee->status] ?? 'bg-secondary';

                return '<span class="badge '.$badgeClass.'">'.ucfirst($fee->status).'</span>';
            })
            ->addColumn('amount_formatted', function($fee) {
                return $fee->formatted_amount;
            })
            ->addColumn('balance_formatted', function($fee) {
                return $fee->formatted_balance;
            })
            ->addColumn('due_date_formatted', function($fee) {
                return $fee->due_date->format('M j, Y');
            })
            ->addColumn('actions', function($fee) {
                return '
                    <a href="'.route('admin.fees.show', $fee->id).'" class="btn btn-sm btn-primary">View</a>
                ';
            })
            ->rawColumns(['status_badge', 'actions'])
            ->toJson();
    }

    // Method to copy template fees to all students in a class
    public function copyTemplateToClass($feeId)
    {
        try {
            DB::beginTransaction();

            $templateFee = Fee::findOrFail($feeId);
            
            // Check if this is actually a template fee
            if (strpos($templateFee->description, '[CLASS TEMPLATE]') === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'This is not a template fee.'
                ]);
            }

            $students = Student::where('rank_id', $templateFee->rank_id)->get();
            $copiedCount = 0;

            foreach ($students as $student) {
                // Check if student already has this fee
                $existingFee = Fee::where('student_id', $student->id)
                    ->where('rank_id', $templateFee->rank_id)
                    ->where('fee_type', $templateFee->fee_type)
                    ->where('academic_year', $templateFee->academic_year)
                    ->where('term', $templateFee->term)
                    ->first();

                if (!$existingFee) {
                    Fee::create([
                        'student_id' => $student->id,
                        'rank_id' => $templateFee->rank_id,
                        'original_fee_structure_id' => null,
                        'fee_type' => $templateFee->fee_type,
                        'amount' => $templateFee->amount,
                        'paid_amount' => 0,
                        'balance' => $templateFee->amount,
                        'academic_year' => $templateFee->academic_year,
                        'term' => $templateFee->term,
                        'due_date' => $templateFee->due_date,
                        'status' => 'pending',
                        'is_carry_over' => false,
                        'description' => str_replace(' [CLASS TEMPLATE - APPLIES TO ALL STUDENTS IN THIS CLASS]', '', $templateFee->description),
                    ]);
                    $copiedCount++;
                }
            }

            DB::commit();

            Log::info("Template fee {$feeId} copied to {$copiedCount} students");
            return response()->json([
                'success' => true,
                'message' => "Template fee copied to {$copiedCount} students.",
                'copied_count' => $copiedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error copying template fee: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error copying template fee: ' . $e->getMessage()
            ]);
        }
    }

    // New method to generate fees from fee structure templates
    public function generateFeesFromTemplates(Request $request)
    {
        $request->validate([
            'academic_year' => 'required|string',
            'term' => 'required|string|in:1,2,3',
        ]);

        try {
            DB::beginTransaction();

            $feeStructures = FeeStructure::where('academic_year', $request->academic_year)
                ->where('term', $request->term)
                ->where('is_active', true)
                ->get();

            $generatedCount = 0;
            $errors = [];

            foreach ($feeStructures as $feeStructure) {
                try {
                    $students = Student::where('rank_id', $feeStructure->rank_id)->get();
                    
                    foreach ($students as $student) {
                        // Check if fee already exists
                        $existingFee = Fee::where('student_id', $student->id)
                            ->where('rank_id', $feeStructure->rank_id)
                            ->where('academic_year', $feeStructure->academic_year)
                            ->where('term', $feeStructure->term)
                            ->first();

                        if (!$existingFee) {
                            Fee::create([
                                'student_id' => $student->id,
                                'rank_id' => $feeStructure->rank_id,
                                'original_fee_structure_id' => $feeStructure->id,
                                'fee_type' => 'tuition', // Default fee type
                                'amount' => $feeStructure->amount,
                                'paid_amount' => 0,
                                'balance' => $feeStructure->amount,
                                'academic_year' => $feeStructure->academic_year,
                                'term' => $feeStructure->term,
                                'due_date' => $feeStructure->due_date,
                                'status' => 'pending',
                                'is_carry_over' => false,
                                'description' => $feeStructure->description,
                            ]);
                            $generatedCount++;
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error generating fees for class {$feeStructure->rank_id}: " . $e->getMessage();
                    Log::error("Error generating fees for class {$feeStructure->rank_id}: " . $e->getMessage());
                }
            }

            DB::commit();

            $message = "Generated {$generatedCount} fees from templates.";
            if (!empty($errors)) {
                $message .= " Some errors: " . implode(', ', array_slice($errors, 0, 3));
            }

            Log::info($message);
            return redirect()->route('admin.fees.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating fees from templates: ' . $e->getMessage());
            return back()->with('error', 'Error generating fees: ' . $e->getMessage());
        }
    }

    /**
     * Generate fees from a specific fee structure - CORRECTED VERSION
     * This method now correctly identifies and updates the right fee types
     */
    public function generateFeesFromStructure(Request $request, FeeStructure $feeStructure)
    {
        try {
            DB::beginTransaction();

            // Get all students in the class
            $students = Student::where('rank_id', $feeStructure->rank_id)->get();
            
            Log::info("Generating/Updating fees for fee structure {$feeStructure->id}, students found: " . $students->count());

            $createdCount = 0;
            $updatedCount = 0;
            $errors = [];

            foreach ($students as $student) {
                try {
                    // FIRST: Check if main tuition fee exists for this student from this structure
                    $existingTuitionFee = Fee::where('student_id', $student->id)
                        ->where('rank_id', $feeStructure->rank_id)
                        ->where('academic_year', $feeStructure->academic_year)
                        ->where('term', $feeStructure->term)
                        ->where('original_fee_structure_id', $feeStructure->id)
                        ->where('fee_type', 'tuition') // SPECIFICALLY look for tuition fee
                        ->first();

                    if (!$existingTuitionFee) {
                        // Create new tuition fee record
                        Fee::create([
                            'student_id' => $student->id,
                            'rank_id' => $feeStructure->rank_id,
                            'original_fee_structure_id' => $feeStructure->id,
                            'fee_type' => 'tuition',
                            'amount' => $feeStructure->amount,
                            'paid_amount' => 0,
                            'balance' => $feeStructure->amount,
                            'academic_year' => $feeStructure->academic_year,
                            'term' => $feeStructure->term,
                            'due_date' => $feeStructure->due_date,
                            'status' => 'pending',
                            'is_carry_over' => false,
                            'description' => $feeStructure->description,
                        ]);
                        $createdCount++;
                        Log::info("Created new tuition fee for student {$student->id}");
                    } else {
                        // Update existing tuition fee regardless of payments
                        if ($existingTuitionFee->amount != $feeStructure->amount) {
                            $newBalance = $feeStructure->amount - $existingTuitionFee->paid_amount;
                            
                            $existingTuitionFee->update([
                                'amount' => $feeStructure->amount,
                                'balance' => max(0, $newBalance),
                                'due_date' => $feeStructure->due_date,
                                'description' => $feeStructure->description,
                                'status' => $newBalance <= 0 ? 'paid' : ($existingTuitionFee->paid_amount > 0 ? 'partial' : 'pending'),
                            ]);
                            $updatedCount++;
                            Log::info("Updated TUITION fee for student {$student->id} (Old: {$existingTuitionFee->getOriginal('amount')}, New: {$feeStructure->amount}, Paid: {$existingTuitionFee->paid_amount}, New Balance: {$newBalance})");
                        } else {
                            Log::info("Tuition fee already up to date for student {$student->id}");
                        }
                    }

                    // SECOND: Handle additional fees if they exist in the fee structure
                    if (!empty($feeStructure->additional_fees)) {
                        foreach ($feeStructure->additional_fees as $additionalFee) {
                            $mappedFeeType = $this->mapFeeType($additionalFee['name']);
                            
                            // Check if this specific additional fee already exists
                            $existingAdditionalFee = Fee::where('student_id', $student->id)
                                ->where('rank_id', $feeStructure->rank_id)
                                ->where('academic_year', $feeStructure->academic_year)
                                ->where('term', $feeStructure->term)
                                ->where('original_fee_structure_id', $feeStructure->id)
                                ->where('fee_type', $mappedFeeType) // SPECIFICALLY look for this fee type
                                ->first();

                            if (!$existingAdditionalFee) {
                                // Create new additional fee
                                Fee::create([
                                    'student_id' => $student->id,
                                    'rank_id' => $feeStructure->rank_id,
                                    'original_fee_structure_id' => $feeStructure->id,
                                    'fee_type' => $mappedFeeType,
                                    'amount' => $additionalFee['amount'],
                                    'paid_amount' => 0,
                                    'balance' => $additionalFee['amount'],
                                    'academic_year' => $feeStructure->academic_year,
                                    'term' => $feeStructure->term,
                                    'due_date' => $feeStructure->due_date,
                                    'status' => 'pending',
                                    'is_carry_over' => false,
                                    'description' => $additionalFee['description'] ?? $additionalFee['name'],
                                ]);
                                $createdCount++;
                                Log::info("Created new {$mappedFeeType} fee for student {$student->id}");
                            } else {
                                // Update existing additional fee
                                if ($existingAdditionalFee->amount != $additionalFee['amount']) {
                                    $newAdditionalBalance = $additionalFee['amount'] - $existingAdditionalFee->paid_amount;
                                    
                                    $existingAdditionalFee->update([
                                        'amount' => $additionalFee['amount'],
                                        'balance' => max(0, $newAdditionalBalance),
                                        'due_date' => $feeStructure->due_date,
                                        'description' => $additionalFee['description'] ?? $additionalFee['name'],
                                        'status' => $newAdditionalBalance <= 0 ? 'paid' : ($existingAdditionalFee->paid_amount > 0 ? 'partial' : 'pending'),
                                    ]);
                                    $updatedCount++;
                                    Log::info("Updated {$mappedFeeType} fee for student {$student->id} (Old: {$existingAdditionalFee->getOriginal('amount')}, New: {$additionalFee['amount']}, Paid: {$existingAdditionalFee->paid_amount}, New Balance: {$newAdditionalBalance})");
                                }
                            }
                        }
                    }

                } catch (\Exception $e) {
                    $errorMsg = "Error processing fee for student {$student->id}: " . $e->getMessage();
                    $errors[] = $errorMsg;
                    Log::error($errorMsg);
                }
            }

            DB::commit();

            $message = "Fee generation completed! Created: {$createdCount}, Updated: {$updatedCount}";
            if (!empty($errors)) {
                $message .= " Some errors occurred: " . implode(', ', array_slice($errors, 0, 3));
            }

            Log::info($message);
            return redirect()->route('admin.fee-structures.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error generating fees from fee structure: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->route('admin.fee-structures.index')
                ->with('error', 'Error generating fees: ' . $e->getMessage());
        }
    }

    /**
     * NEW METHOD: Update existing fees when fee structure is modified - CORRECTED VERSION
     * This method correctly identifies and updates the right fee types
     */
    public function updateFeesFromStructure(FeeStructure $feeStructure)
    {
        try {
            DB::beginTransaction();

            Log::info("Updating ALL existing fees for fee structure {$feeStructure->id}");

            $updatedCount = 0;
            $errors = [];

            // Find all TUITION fees that were generated from this fee structure
            $existingTuitionFees = Fee::where('original_fee_structure_id', $feeStructure->id)
                ->where('academic_year', $feeStructure->academic_year)
                ->where('term', $feeStructure->term)
                ->where('fee_type', 'tuition') // SPECIFICALLY target tuition fees
                ->get();

            foreach ($existingTuitionFees as $existingFee) {
                try {
                    // Update TUITION fee regardless of payments
                    if ($existingFee->amount != $feeStructure->amount) {
                        $newBalance = $feeStructure->amount - $existingFee->paid_amount;
                        
                        $existingFee->update([
                            'amount' => $feeStructure->amount,
                            'balance' => max(0, $newBalance),
                            'due_date' => $feeStructure->due_date,
                            'description' => $feeStructure->description,
                            'status' => $newBalance <= 0 ? 'paid' : ($existingFee->paid_amount > 0 ? 'partial' : 'pending'),
                        ]);
                        $updatedCount++;
                        Log::info("Updated TUITION fee {$existingFee->id} for student {$existingFee->student_id} (Amount: {$feeStructure->amount}, Paid: {$existingFee->paid_amount}, New Balance: {$newBalance})");
                    } else {
                        Log::info("Tuition fee {$existingFee->id} amount unchanged - skipping update");
                    }
                } catch (\Exception $e) {
                    $errorMsg = "Error updating tuition fee {$existingFee->id}: " . $e->getMessage();
                    $errors[] = $errorMsg;
                    Log::error($errorMsg);
                }
            }

            // Also update additional fees if they exist in the fee structure
            if (!empty($feeStructure->additional_fees)) {
                foreach ($feeStructure->additional_fees as $additionalFee) {
                    $mappedFeeType = $this->mapFeeType($additionalFee['name']);
                    
                    $existingAdditionalFees = Fee::where('original_fee_structure_id', $feeStructure->id)
                        ->where('academic_year', $feeStructure->academic_year)
                        ->where('term', $feeStructure->term)
                        ->where('fee_type', $mappedFeeType) // SPECIFICALLY target this fee type
                        ->get();

                    foreach ($existingAdditionalFees as $existingAdditionalFee) {
                        try {
                            if ($existingAdditionalFee->amount != $additionalFee['amount']) {
                                $newAdditionalBalance = $additionalFee['amount'] - $existingAdditionalFee->paid_amount;
                                
                                $existingAdditionalFee->update([
                                    'amount' => $additionalFee['amount'],
                                    'balance' => max(0, $newAdditionalBalance),
                                    'due_date' => $feeStructure->due_date,
                                    'description' => $additionalFee['description'] ?? $additionalFee['name'],
                                    'status' => $newAdditionalBalance <= 0 ? 'paid' : ($existingAdditionalFee->paid_amount > 0 ? 'partial' : 'pending'),
                                ]);
                                $updatedCount++;
                                Log::info("Updated {$mappedFeeType} fee {$existingAdditionalFee->id} for student {$existingAdditionalFee->student_id}");
                            }
                        } catch (\Exception $e) {
                            $errorMsg = "Error updating {$mappedFeeType} fee {$existingAdditionalFee->id}: " . $e->getMessage();
                            $errors[] = $errorMsg;
                            Log::error($errorMsg);
                        }
                    }
                }
            }

            DB::commit();

            $message = "Updated {$updatedCount} existing fees from fee structure!";
            if (!empty($errors)) {
                $message .= " Some errors: " . implode(', ', array_slice($errors, 0, 3));
            }

            Log::info($message);
            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating fees from fee structure: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating fees: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * NEW METHOD: Smart fee generation that correctly identifies fee types
     */
    public function smartGenerateFees(Request $request, FeeStructure $feeStructure)
    {
        try {
            DB::beginTransaction();

            // Get all students in the class
            $students = Student::where('rank_id', $feeStructure->rank_id)->get();
            
            Log::info("Smart fee generation for structure {$feeStructure->id}, students: " . $students->count());

            $createdCount = 0;
            $updatedCount = 0;
            $errors = [];

            foreach ($students as $student) {
                try {
                    // Check for existing TUITION fee from this structure
                    $existingTuitionFee = Fee::where('student_id', $student->id)
                        ->where('rank_id', $feeStructure->rank_id)
                        ->where('academic_year', $feeStructure->academic_year)
                        ->where('term', $feeStructure->term)
                        ->where('original_fee_structure_id', $feeStructure->id)
                        ->where('fee_type', 'tuition') // SPECIFICALLY look for tuition
                        ->first();

                    if ($existingTuitionFee) {
                        // Update existing TUITION fee regardless of payments
                        if ($existingTuitionFee->amount != $feeStructure->amount) {
                            $newBalance = $feeStructure->amount - $existingTuitionFee->paid_amount;
                            
                            $existingTuitionFee->update([
                                'amount' => $feeStructure->amount,
                                'balance' => max(0, $newBalance),
                                'due_date' => $feeStructure->due_date,
                                'description' => $feeStructure->description,
                                'status' => $newBalance <= 0 ? 'paid' : ($existingTuitionFee->paid_amount > 0 ? 'partial' : 'pending'),
                            ]);
                            $updatedCount++;
                            Log::info("Updated TUITION fee for student {$student->id} (Amount: {$feeStructure->amount}, Paid: {$existingTuitionFee->paid_amount}, New Balance: {$newBalance})");
                        }
                    } else {
                        // Create new TUITION fee for this student
                        Fee::create([
                            'student_id' => $student->id,
                            'rank_id' => $feeStructure->rank_id,
                            'original_fee_structure_id' => $feeStructure->id,
                            'fee_type' => 'tuition',
                            'amount' => $feeStructure->amount,
                            'paid_amount' => 0,
                            'balance' => $feeStructure->amount,
                            'academic_year' => $feeStructure->academic_year,
                            'term' => $feeStructure->term,
                            'due_date' => $feeStructure->due_date,
                            'status' => 'pending',
                            'is_carry_over' => false,
                            'description' => $feeStructure->description,
                        ]);
                        $createdCount++;
                        Log::info("Created new TUITION fee for student {$student->id}");
                    }

                    // Handle additional fees
                    if (!empty($feeStructure->additional_fees)) {
                        foreach ($feeStructure->additional_fees as $additionalFee) {
                            $mappedFeeType = $this->mapFeeType($additionalFee['name']);
                            
                            $existingAdditionalFee = Fee::where('student_id', $student->id)
                                ->where('rank_id', $feeStructure->rank_id)
                                ->where('academic_year', $feeStructure->academic_year)
                                ->where('term', $feeStructure->term)
                                ->where('original_fee_structure_id', $feeStructure->id)
                                ->where('fee_type', $mappedFeeType) // SPECIFICALLY look for this fee type
                                ->first();

                            if ($existingAdditionalFee) {
                                // Update existing additional fee
                                if ($existingAdditionalFee->amount != $additionalFee['amount']) {
                                    $newAdditionalBalance = $additionalFee['amount'] - $existingAdditionalFee->paid_amount;
                                    
                                    $existingAdditionalFee->update([
                                        'amount' => $additionalFee['amount'],
                                        'balance' => max(0, $newAdditionalBalance),
                                        'due_date' => $feeStructure->due_date,
                                        'description' => $additionalFee['description'] ?? $additionalFee['name'],
                                        'status' => $newAdditionalBalance <= 0 ? 'paid' : ($existingAdditionalFee->paid_amount > 0 ? 'partial' : 'pending'),
                                    ]);
                                    $updatedCount++;
                                }
                            } else {
                                // Create new additional fee
                                Fee::create([
                                    'student_id' => $student->id,
                                    'rank_id' => $feeStructure->rank_id,
                                    'original_fee_structure_id' => $feeStructure->id,
                                    'fee_type' => $mappedFeeType,
                                    'amount' => $additionalFee['amount'],
                                    'paid_amount' => 0,
                                    'balance' => $additionalFee['amount'],
                                    'academic_year' => $feeStructure->academic_year,
                                    'term' => $feeStructure->term,
                                    'due_date' => $feeStructure->due_date,
                                    'status' => 'pending',
                                    'is_carry_over' => false,
                                    'description' => $additionalFee['description'] ?? $additionalFee['name'],
                                ]);
                                $createdCount++;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $errorMsg = "Error processing student {$student->id}: " . $e->getMessage();
                    $errors[] = $errorMsg;
                    Log::error($errorMsg);
                }
            }

            DB::commit();

            $message = "Smart fee generation completed! Created: {$createdCount}, Updated: {$updatedCount}";
            if (!empty($errors)) {
                $message .= " Some errors: " . implode(', ', array_slice($errors, 0, 3));
            }

            Log::info($message);
            return redirect()->route('admin.fee-structures.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in smart fee generation: ' . $e->getMessage());
            return redirect()->route('admin.fee-structures.index')
                ->with('error', 'Error generating fees: ' . $e->getMessage());
        }
    }

    /**
     * Improved mapFeeType method with better mapping
     */
    private function mapFeeType($feeName)
    {
        $feeName = strtolower(trim($feeName));
        
        $mapping = [
            'activity' => 'activity',
            'activity fee' => 'activity',
            'exam' => 'exam', 
            'examination' => 'exam',
            'examination fee' => 'exam',
            'transport' => 'transport',
            'transport fee' => 'transport',
            'library' => 'library',
            'library fee' => 'library',
            'sports' => 'sports',
            'sports fee' => 'sports',
            'hostel' => 'hostel',
            'hostel fee' => 'hostel',
            'other' => 'other',
            'miscellaneous' => 'other',
            'misc' => 'other',
        ];

        return $mapping[$feeName] ?? 'other';
    }

    
        /**
     * Display fee balance checking interface
     */
    public function balances()
    {
        $ranks = Rank::all();
        $academicYears = Fee::distinct()->pluck('academic_year');
        
        // Get initial stats
        $stats = [
            'total_students' => Student::count(),
            'total_fees_due' => Fee::sum('amount'),
            'total_paid' => Fee::sum('paid_amount'),
            'total_balance' => Fee::sum('balance'),
        ];
        
        return Inertia::render('Admin/Fees/FeeBalance', [
            'ranks' => $ranks,
            'academic_years' => $academicYears,
            'initial_stats' => $stats,
        ]);
    }

    /**
     * Search student for fee balance - FIXED VERSION
     */
    public function searchStudentBalance(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
            'academic_year' => 'nullable|string',
            'term' => 'nullable|string',
        ]);
        
        // Extract parameters properly to avoid the InputBag conversion error
        $searchQuery = $request->input('query');
        $academicYear = $request->input('academic_year');
        $term = $request->input('term');
        
        \Log::info('Searching student balance', [
            'query' => $searchQuery,
            'academic_year' => $academicYear,
            'term' => $term
        ]);
        
        // Search by admission number first (exact match)
        $student = Student::with(['currentRank'])
            ->where('admission_number', $searchQuery)
            ->first();
        
        // If not found by admission number, search by name
        if (!$student) {
            $student = Student::with(['currentRank'])
                ->where(function($query) use ($searchQuery) {
                    $query->where('first_name', 'like', "%{$searchQuery}%")
                          ->orWhere('last_name', 'like', "%{$searchQuery}%")
                          ->orWhere('admission_number', 'like', "%{$searchQuery}%");
                })
                ->first();
        }
        
        if (!$student) {
            \Log::warning('Student not found for query: ' . $searchQuery);
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }
        
        \Log::info('Student found:', [
            'id' => $student->id, 
            'name' => $student->first_name . ' ' . $student->last_name,
            'admission_number' => $student->admission_number
        ]);
        
        // Calculate fee totals
        $feeQuery = Fee::where('student_id', $student->id);
        
        if ($academicYear) {
            $feeQuery->where('academic_year', $academicYear);
        }
        
        if ($term) {
            $feeQuery->where('term', $term);
        }
        
        $fees = $feeQuery->get();
        
        // Add calculated properties to student object
        $student->total_fees = $fees->sum('amount');
        $student->total_paid = $fees->sum('paid_amount');
        $student->balance = $fees->sum('balance');
        $student->full_name = $student->first_name . ' ' . $student->last_name;
        
        \Log::info('Fee totals calculated:', [
            'total_fees' => $student->total_fees,
            'total_paid' => $student->total_paid,
            'balance' => $student->balance,
            'fee_count' => $fees->count()
        ]);
        
        return response()->json([
            'success' => true,
            'student' => $student
        ]);
    }

    /**
     * Get class fee balances - FIXED VERSION
     */
    public function getClassBalances(Request $request)
    {
        $request->validate([
            'rank_id' => 'required|exists:ranks,id',
            'academic_year' => 'nullable|string',
            'term' => 'nullable|string',
        ]);
        
        // Extract parameters properly
        $rankId = $request->input('rank_id');
        $academicYear = $request->input('academic_year');
        $term = $request->input('term');
        
        $students = Student::with(['currentRank'])
            ->where('rank_id', $rankId)
            ->get()
            ->map(function($student) use ($academicYear, $term) {
                $feeQuery = Fee::where('student_id', $student->id);
                
                if ($academicYear) {
                    $feeQuery->where('academic_year', $academicYear);
                }
                
                if ($term) {
                    $feeQuery->where('term', $term);
                }
                
                $fees = $feeQuery->get();
                
                $student->total_fees = $fees->sum('amount');
                $student->total_paid = $fees->sum('paid_amount');
                $student->balance = $fees->sum('balance');
                $student->full_name = $student->first_name . ' ' . $student->last_name;
                
                return $student;
            });
        
        // Calculate stats
        $stats = [
            'total_students' => $students->count(),
            'total_fees_due' => $students->sum('total_fees'),
            'total_paid' => $students->sum('total_paid'),
            'total_balance' => $students->sum('balance'),
        ];
        
        return response()->json([
            'success' => true,
            'students' => $students,
            'stats' => $stats,
        ]);
    }

    /**
     * Get detailed student fee information - FIXED VERSION
     */
    public function getStudentDetails(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'academic_year' => 'nullable|string',
            'term' => 'nullable|string',
        ]);
        
        // Extract parameters properly
        $studentId = $request->input('student_id');
        $academicYear = $request->input('academic_year');
        $term = $request->input('term');
        
        $student = Student::with(['currentRank'])->find($studentId);
        
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found'
            ], 404);
        }
        
        $feeQuery = Fee::where('student_id', $student->id);
        $paymentQuery = FeePayment::where('student_id', $student->id);
        
        if ($academicYear) {
            $feeQuery->where('academic_year', $academicYear);
            $paymentQuery->whereHas('fee', function($q) use ($academicYear) {
                $q->where('academic_year', $academicYear);
            });
        }
        
        if ($term) {
            $feeQuery->where('term', $term);
            $paymentQuery->whereHas('fee', function($q) use ($term) {
                $q->where('term', $term);
            });
        }
        
        $student->fees = $feeQuery->get();
        $student->payments = $paymentQuery->latest()->take(20)->get();
        
        $student->total_fees = $student->fees->sum('amount');
        $student->total_paid = $student->fees->sum('paid_amount');
        $student->balance = $student->fees->sum('balance');
        $student->full_name = $student->first_name . ' ' . $student->last_name;
        
        return response()->json([
            'success' => true,
            'student' => $student
        ]);
    }

   /**
 * Print student fee statement with detailed breakdown using Blade template
 */
public function printStudentStatement(Request $request, $studentId)
{
    $student = Student::with(['currentRank'])->findOrFail($studentId);
    
    // Remove academic year and term filters to show all records
    $fees = Fee::where('student_id', $student->id)->get();
    $payments = FeePayment::where('student_id', $student->id)
        ->with(['fee'])
        ->latest()
        ->get();
    
    // Extract parameters for display purposes only (not for filtering)
    $academicYear = $request->input('academic_year');
    $term = $request->input('term');
    
    $totalFees = $fees->sum('amount');
    $totalPaid = $fees->sum('paid_amount');
    $balance = $fees->sum('balance');
    
    // Fee types mapping
    $feeTypes = [
        'tuition' => 'Tuition Fee',
        'activity' => 'Activity Fee', 
        'exam' => 'Examination Fee',
        'library' => 'Library Fee',
        'sports' => 'Sports Fee',
        'transport' => 'Transport Fee',
        'hostel' => 'Hostel Fee',
        'other' => 'Other Fees'
    ];
    
    // Group fees by type for breakdown
    $feeBreakdown = [];
    foreach ($fees as $fee) {
        $type = $fee->fee_type;
        if (!isset($feeBreakdown[$type])) {
            $feeBreakdown[$type] = [
                'name' => $feeTypes[$type] ?? ucfirst($type),
                'amount' => 0,
                'paid' => 0,
                'balance' => 0
            ];
        }
        $feeBreakdown[$type]['amount'] += $fee->amount;
        $feeBreakdown[$type]['paid'] += $fee->paid_amount;
        $feeBreakdown[$type]['balance'] += $fee->balance;
    }
    
    // Get payment allocation details (how payments were applied to fees)
    $paymentAllocations = [];
    foreach ($payments as $payment) {
        if ($payment->fee) {
            $paymentAllocations[] = [
                'payment_date' => $payment->payment_date,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'reference_number' => $payment->reference_number,
                'fee_type' => $payment->fee->fee_type,
                'fee_type_name' => $feeTypes[$payment->fee->fee_type] ?? ucfirst($payment->fee->fee_type),
                'academic_year' => $payment->fee->academic_year,
                'term' => $payment->fee->term,
                'verified_by' => $payment->verified_by ? \App\Models\User::find($payment->verified_by)->name ?? 'System' : 'N/A'
            ];
        } else {
            // Handle payments without associated fees (orphaned payments)
            $paymentAllocations[] = [
                'payment_date' => $payment->payment_date,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'reference_number' => $payment->reference_number,
                'fee_type' => 'general',
                'fee_type_name' => 'General Payment',
                'academic_year' => 'N/A',
                'term' => 'N/A',
                'verified_by' => $payment->verified_by ? \App\Models\User::find($payment->verified_by)->name ?? 'System' : 'N/A'
            ];
        }
    }
    
    // Pass academicYear and term to the view (for display only)
    return view('admin.fees.student-statement', compact(
        'student',
        'fees',
        'payments',
        'totalFees',
        'totalPaid',
        'balance',
        'feeTypes',
        'feeBreakdown',
        'academicYear',
        'term',
        'paymentAllocations'
    ));
}

    /**
     * Print class fee statements - FIXED VERSION
     */
    public function printClassStatements(Request $request)
    {
        $request->validate([
            'rank_id' => 'required|exists:ranks,id',
            'academic_year' => 'nullable|string',
            'term' => 'nullable|string',
        ]);
        
        // Extract parameters properly
        $rankId = $request->input('rank_id');
        $academicYear = $request->input('academic_year');
        $term = $request->input('term');
        
        $rank = Rank::findOrFail($rankId);
        $students = Student::with(['currentRank'])
            ->where('rank_id', $rankId)
            ->get()
            ->map(function($student) use ($academicYear, $term) {
                $feeQuery = Fee::where('student_id', $student->id);
                
                if ($academicYear) {
                    $feeQuery->where('academic_year', $academicYear);
                }
                
                if ($term) {
                    $feeQuery->where('term', $term);
                }
                
                $fees = $feeQuery->get();
                
                $student->total_fees = $fees->sum('amount');
                $student->total_paid = $fees->sum('paid_amount');
                $student->balance = $fees->sum('balance');
                $student->full_name = $student->first_name . ' ' . $student->last_name;
                $student->fees = $fees;
                
                return $student;
            });
        
        if ($request->boolean('print')) {
            // Check if students exist
            if ($students->isEmpty()) {
                return response("
                    <html>
                    <head><title>No Data</title></head>
                    <body>
                        <h1>No Students Found</h1>
                        <p>No students found in {$rank->name}</p>
                        <button onclick='window.close()'>Close</button>
                    </body>
                    </html>
                ");
            }
            
            // Create HTML response directly instead of using view file
            return $this->generateClassStatementsHtml($students, $rank, $academicYear, $term);
        }
        
        return response()->json([
            'success' => true,
            'students' => $students,
            'rank' => $rank,
        ]);
    }

    /**
     * Generate HTML for class statements - FIXED VERSION
     */
    private function generateClassStatementsHtml($students, $rank, $academicYear = null, $term = null)
    {
        $totalFees = $students->sum('total_fees');
        $totalPaid = $students->sum('total_paid');
        $totalBalance = $students->sum('balance');
        $studentsWithBalance = $students->where('balance', '>', 0)->count();
        
        $html = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Class Fee Statements - {$rank->name}</title>
            <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
            <style>
                @media print {
                    .no-print { display: none !important; }
                    .container { max-width: 100% !important; }
                    .card { border: none !important; box-shadow: none !important; }
                }
                body { font-family: Arial, sans-serif; }
                .header { border-bottom: 2px solid #333; margin-bottom: 20px; }
                .student-table { font-size: 12px; }
                .summary-box { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
                .text-success { color: #198754 !important; }
                .text-danger { color: #dc3545 !important; }
                .text-warning { color: #ffc107 !important; }
                .badge { font-size: 0.75em; }
            </style>
        </head>
        <body>
            <div class='container-fluid'>
                <!-- Header -->
                <div class='header text-center py-4'>
                    <h2 class='mb-1'>Class Fee Statements</h2>
                    <h4 class='text-muted mb-1'>{$rank->name}</h4>
                    <p class='text-muted mb-0'>
                        Generated on: " . now()->format('F j, Y g:i A') . "
        ";
        
        if ($academicYear) {
            $html .= " | Academic Year: {$academicYear}";
        }
        
        if ($term) {
            $html .= " | Term: {$term}";
        }
        
        $html .= "
                    </p>
                </div>

                <!-- Print Button -->
                <div class='no-print mb-3'>
                    <button onclick='window.print()' class='btn btn-primary btn-sm'>
                        Print
                    </button>
                    <button onclick='window.close()' class='btn btn-secondary btn-sm'>
                        Close
                    </button>
                </div>

                <!-- Class Summary -->
                <div class='summary-box'>
                    <div class='row'>
                        <div class='col-md-3'>
                            <strong>Total Students:</strong> {$students->count()}
                        </div>
                        <div class='col-md-3'>
                            <strong>Total Fees:</strong> Ksh " . number_format($totalFees, 2) . "
                        </div>
                        <div class='col-md-3'>
                            <strong>Total Paid:</strong> Ksh " . number_format($totalPaid, 2) . "
                        </div>
                        <div class='col-md-3'>
                            <strong>Total Balance:</strong> 
                            <span class='" . ($totalBalance > 0 ? 'text-danger fw-bold' : 'text-success') . "'>
                                Ksh " . number_format($totalBalance, 2) . "
                            </span>
                        </div>
                    </div>
                    <div class='row mt-2'>
                        <div class='col-md-12'>
                            <strong>Students with Balance:</strong> 
                            <span class='badge bg-warning'>{$studentsWithBalance}</span>
                        </div>
                    </div>
                </div>

                <!-- Students Table -->
                <div class='table-responsive'>
                    <table class='table table-sm table-bordered student-table'>
                        <thead class='table-light'>
                            <tr>
                                <th>#</th>
                                <th>Admission No.</th>
                                <th>Student Name</th>
                                <th>Total Fees</th>
                                <th>Paid Amount</th>
                                <th>Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
        ";
        
        foreach ($students as $index => $student) {
            $status = '';
            $statusClass = '';
            if ($student->balance == 0) {
                $status = 'Paid';
                $statusClass = 'bg-success';
            } elseif ($student->balance == $student->total_fees) {
                $status = 'Pending';
                $statusClass = 'bg-danger';
            } elseif ($student->balance > 0) {
                $status = 'Partial';
                $statusClass = 'bg-warning';
            } else {
                $status = 'Overpaid';
                $statusClass = 'bg-info';
            }
            
            $html .= "
                            <tr>
                                <td>" . ($index + 1) . "</td>
                                <td class='fw-bold'>{$student->admission_number}</td>
                                <td>{$student->full_name}</td>
                                <td class='text-primary'>Ksh " . number_format($student->total_fees, 2) . "</td>
                                <td class='text-success'>Ksh " . number_format($student->total_paid, 2) . "</td>
                                <td class='" . ($student->balance > 0 ? 'text-danger fw-bold' : 'text-success') . "'>
                                    Ksh " . number_format($student->balance, 2) . "
                                </td>
                                <td>
                                    <span class='badge {$statusClass}'>{$status}</span>
                                </td>
                            </tr>
            ";
        }
        
        $html .= "
                        </tbody>
                        <tfoot class='table-light'>
                            <tr>
                                <th colspan='3' class='text-end'>TOTALS:</th>
                                <th class='text-primary'>Ksh " . number_format($totalFees, 2) . "</th>
                                <th class='text-success'>Ksh " . number_format($totalPaid, 2) . "</th>
                                <th class='" . ($totalBalance > 0 ? 'text-danger fw-bold' : 'text-success') . "'>
                                    Ksh " . number_format($totalBalance, 2) . "
                                </th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Footer -->
                <div class='mt-4 text-center text-muted'>
                    <small>
                        This is a computer-generated report. No signature is required.<br>
                        Generated by: " . (auth()->user()->name ?? 'System') . "
                    </small>
                </div>
            </div>

            <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
            <script>
                // Auto-print when the page loads
                window.onload = function() {
                    setTimeout(function() {
                        window.print();
                    }, 500);
                };
            </script>
        </body>
        </html>
        ";
        
        return response($html);
    }

    /**
     * Get comprehensive fee dashboard statistics
     */
    public function getDashboardStatistics()
    {
        $totalStudents = Student::count();
        $totalFeesDue = Fee::sum('amount');
        $totalPaid = Fee::sum('paid_amount');
        $totalBalance = Fee::sum('balance');
        
        // Monthly revenue for current year
        $monthlyRevenue = FeePayment::whereYear('payment_date', date('Y'))
            ->selectRaw('MONTH(payment_date) as month, SUM(amount) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month');
        
        // Fee status distribution
        $feeStatusDistribution = Fee::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');
        
        // Class-wise outstanding balances
        $classBalances = Rank::withCount(['students'])
            ->with(['fees' => function($query) {
                $query->select('rank_id', DB::raw('SUM(balance) as total_balance'));
            }])
            ->get()
            ->map(function($rank) {
                return [
                    'class_name' => $rank->name,
                    'student_count' => $rank->students_count,
                    'total_balance' => $rank->fees->sum('total_balance') ?? 0,
                ];
            });
        
        return response()->json([
            'success' => true,
            'statistics' => [
                'total_students' => $totalStudents,
                'total_fees_due' => $totalFeesDue,
                'total_paid' => $totalPaid,
                'total_balance' => $totalBalance,
                'collection_rate' => $totalFeesDue > 0 ? round(($totalPaid / $totalFeesDue) * 100, 2) : 0,
                'monthly_revenue' => $monthlyRevenue,
                'fee_status_distribution' => $feeStatusDistribution,
                'class_balances' => $classBalances,
            ]
        ]);
    }

    /**
     * Export fee balances to Excel
     */
    public function exportFeeBalances(Request $request)
    {
        $request->validate([
            'rank_id' => 'nullable|exists:ranks,id',
            'academic_year' => 'nullable|string',
            'term' => 'nullable|string',
        ]);
        
        // Extract parameters properly
        $rankId = $request->input('rank_id');
        $academicYear = $request->input('academic_year');
        $term = $request->input('term');
        
        $students = Student::with(['currentRank'])
            ->when($rankId, function($query) use ($rankId) {
                $query->where('rank_id', $rankId);
            })
            ->get()
            ->map(function($student) use ($academicYear, $term) {
                $feeQuery = Fee::where('student_id', $student->id);
                
                if ($academicYear) {
                    $feeQuery->where('academic_year', $academicYear);
                }
                
                if ($term) {
                    $feeQuery->where('term', $term);
                }
                
                $fees = $feeQuery->get();
                
                return [
                    'admission_number' => $student->admission_number,
                    'student_name' => $student->first_name . ' ' . $student->last_name,
                    'class' => $student->currentRank->name ?? 'N/A',
                    'total_fees' => $fees->sum('amount'),
                    'total_paid' => $fees->sum('paid_amount'),
                    'balance' => $fees->sum('balance'),
                    'status' => $fees->sum('balance') <= 0 ? 'Paid' : ($fees->sum('paid_amount') > 0 ? 'Partial' : 'Pending'),
                ];
            });
        
        return response()->json([
            'success' => true,
            'data' => $students,
            'filename' => 'fee-balances-' . date('Y-m-d') . '.xlsx',
        ]);
    }
    
    public function sendFeeReminder(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'reminder_type' => 'required|in:sms,email,both',
            'message' => 'nullable|string',
        ]);
        
        $student = Student::with(['currentRank'])->find($request->student_id);
        
        // Calculate outstanding balance
        $outstandingBalance = Fee::where('student_id', $student->id)
            ->where('balance', '>', 0)
            ->sum('balance');
        
        Log::info("Fee reminder sent to student {$student->id}", [
            'student' => $student->admission_number,
            'reminder_type' => $request->reminder_type,
            'outstanding_balance' => $outstandingBalance,
            'custom_message' => $request->message,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Fee reminder sent successfully!',
            'outstanding_balance' => $outstandingBalance,
        ]);
    }
    
    public function bulkSendReminders(Request $request)
    {
        $request->validate([
            'rank_id' => 'required|exists:ranks,id',
            'reminder_type' => 'required|in:sms,email,both',
            'send_to' => 'required|in:all,with_balance,overdue',
            'message' => 'nullable|string',
        ]);
        
        $students = Student::where('rank_id', $request->rank_id)->get();
        $sentCount = 0;
        
        foreach ($students as $student) {
            $outstandingBalance = Fee::where('student_id', $student->id)
                ->where('balance', '>', 0)
                ->sum('balance');
            
            // Apply filters
            if ($request->send_to === 'with_balance' && $outstandingBalance <= 0) {
                continue;
            }
            
            if ($request->send_to === 'overdue') {
                $overdueFees = Fee::where('student_id', $student->id)
                    ->where('balance', '>', 0)
                    ->where('due_date', '<', now())
                    ->exists();
                
                if (!$overdueFees) {
                    continue;
                }
            }
            
            Log::info("Bulk fee reminder sent to student {$student->id}", [
                'student' => $student->admission_number,
                'reminder_type' => $request->reminder_type,
                'outstanding_balance' => $outstandingBalance,
            ]);
            
            $sentCount++;
        }
        
        return response()->json([
            'success' => true,
            'message' => "Fee reminders sent to {$sentCount} students!",
            'sent_count' => $sentCount,
        ]);
    }
}