<?php

namespace App\Http\Controllers\Fee;

use App\Http\Controllers\Controller;
use App\Models\FeeStructure;
use App\Models\Rank;
use App\Models\Student;
use App\Models\Fee;
use App\Models\FeeInvoice;
use App\Models\FeeInvoiceItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FeeStructureController extends Controller
{
    // TEMPORARY: Bypass all permission checks for development

    public function index()
    {
        $feeStructures = FeeStructure::with(['rank', 'invoices'])
            ->withCount('invoices')
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Fees/FeeStructures/Index', [
            'fee_structures' => $feeStructures,
            'classes' => Rank::all(),
            'can' => [
                'create' => true,
                'edit' => true,
                'delete' => true,
                'generate_fees' => true,
            ]
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Fees/FeeStructures/Create', [
            'classes' => Rank::all(),
            'academic_years' => $this->getAcademicYears(),
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Fee Structure Store Request:', $request->all());

        try {
            $validated = $request->validate([
                'class_id' => 'required|exists:ranks,id',
                'academic_year' => 'required|string|max:255',
                'term' => 'required|in:1,2,3',
                'amount' => 'required|numeric|min:0',
                'description' => 'nullable|string|max:500',
                'due_date' => 'required|date|after:today',
                'additional_fees' => 'nullable|array',
                'additional_fees.*.name' => 'required|string|max:255',
                'additional_fees.*.amount' => 'required|numeric|min:0',
                'additional_fees.*.description' => 'nullable|string|max:500',
            ]);

            // Check if fee structure already exists
            $existing = FeeStructure::where('rank_id', $validated['class_id'])
                ->where('academic_year', $validated['academic_year'])
                ->where('term', $validated['term'])
                ->first();

            if ($existing) {
                return redirect()->back()
                    ->withErrors([
                        'academic_year' => 'Fee structure already exists for this class, academic year, and term combination.'
                    ])
                    ->withInput();
            }

            DB::beginTransaction();

            // Create the fee structure
            $feeStructure = FeeStructure::create([
                'rank_id' => $validated['class_id'],
                'academic_year' => $validated['academic_year'],
                'term' => $validated['term'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'due_date' => $validated['due_date'],
                'additional_fees' => $validated['additional_fees'] ?? [],
                'is_active' => true,
            ]);

            DB::commit();

            Log::info('Fee Structure Created Successfully:', $feeStructure->toArray());

            return redirect()->route('admin.fee-structures.index')
                ->with('success', 'Fee structure created successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Error: ', $e->errors());
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee Structure Creation Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to create fee structure: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(FeeStructure $fee_structure)
    {
        $fee_structure->load(['rank', 'invoices.student', 'fees']);

        return Inertia::render('Admin/Fees/FeeStructures/Show', [
            'fee_structure' => $fee_structure,
            'generated_invoices_count' => $fee_structure->invoices->count(),
            'total_amount' => $fee_structure->total_amount,
            'can' => [
                'edit' => true,
                'generate_fees' => true,
            ]
        ]);
    }

    public function edit(FeeStructure $fee_structure)
    {
        return Inertia::render('Admin/Fees/FeeStructures/Edit', [
            'fee_structure' => $fee_structure->load('rank'),
            'classes' => Rank::all(),
            'academic_years' => $this->getAcademicYears(),
        ]);
    }

    public function update(Request $request, FeeStructure $fee_structure)
    {
        Log::info('Fee Structure Update Request:', $request->all());

        try {
            $validated = $request->validate([
                'class_id' => 'required|exists:ranks,id',
                'academic_year' => 'required|string|max:255',
                'term' => 'required|in:1,2,3',
                'amount' => 'required|numeric|min:0',
                'description' => 'nullable|string|max:500',
                'due_date' => 'required|date',
                'is_active' => 'required|boolean',
                'additional_fees' => 'nullable|array',
                'additional_fees.*.name' => 'required|string|max:255',
                'additional_fees.*.amount' => 'required|numeric|min:0',
                'additional_fees.*.description' => 'nullable|string|max:500',
            ]);

            // Check if fee structure already exists (excluding current one)
            $existing = FeeStructure::where('rank_id', $validated['class_id'])
                ->where('academic_year', $validated['academic_year'])
                ->where('term', $validated['term'])
                ->where('id', '!=', $fee_structure->id)
                ->first();

            if ($existing) {
                return redirect()->back()
                    ->withErrors([
                        'academic_year' => 'Fee structure already exists for this class, academic year, and term combination.'
                    ])
                    ->withInput();
            }

            DB::beginTransaction();

            // Store old values for comparison
            $oldAmount = $fee_structure->amount;
            $oldAdditionalFees = $fee_structure->additional_fees ?? [];
            $oldDueDate = $fee_structure->due_date;

            // Update the fee structure
            $fee_structure->update([
                'rank_id' => $validated['class_id'],
                'academic_year' => $validated['academic_year'],
                'term' => $validated['term'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'due_date' => $validated['due_date'],
                'additional_fees' => $validated['additional_fees'] ?? [],
                'is_active' => $validated['is_active'],
            ]);

            // NEW: Always sync changes to existing fees when there are invoices
            if ($fee_structure->invoices()->exists()) {
                $this->syncFeeStructureChanges($fee_structure, [
                    'old_amount' => $oldAmount,
                    'new_amount' => $validated['amount'],
                    'old_additional_fees' => $oldAdditionalFees,
                    'new_additional_fees' => $validated['additional_fees'] ?? [],
                    'old_due_date' => $oldDueDate,
                    'new_due_date' => $validated['due_date'],
                ]);
            }

            DB::commit();

            Log::info('Fee Structure Updated Successfully:', $fee_structure->toArray());

            $message = 'Fee structure updated successfully!';
            if ($fee_structure->invoices()->exists()) {
                $message .= ' All related student fees have been synchronized.';
            }

            return redirect()->route('admin.fee-structures.index')
                ->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Error: ', $e->errors());
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Fee Structure Update Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to update fee structure: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Sync fee structure changes to individual fee records
     */
    private function syncFeeStructureChanges(FeeStructure $feeStructure, array $changes)
    {
        try {
            Log::info("Syncing fee changes for structure {$feeStructure->id}", $changes);

            $affectedCount = 0;

            // Sync tuition fees
            if ($changes['old_amount'] != $changes['new_amount']) {
                $affectedCount += $this->syncTuitionFees($feeStructure, $changes['old_amount'], $changes['new_amount']);
            }

            // Sync additional fees
            $affectedCount += $this->syncAdditionalFees($feeStructure, $changes['old_additional_fees'], $changes['new_additional_fees']);

            // Sync due date
            if ($changes['old_due_date'] != $changes['new_due_date']) {
                $affectedCount += $this->syncDueDate($feeStructure, $changes['new_due_date']);
            }

            Log::info("Successfully synced {$affectedCount} fee records for structure {$feeStructure->id}");
        } catch (\Exception $e) {
            Log::error('Error syncing fee structure changes: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Sync tuition fee changes
     */
    private function syncTuitionFees(FeeStructure $feeStructure, $oldAmount, $newAmount)
    {
        $fees = Fee::where('original_fee_structure_id', $feeStructure->id)
            ->where('fee_type', 'tuition')
            ->get();

        foreach ($fees as $fee) {
            // Calculate the difference
            $amountDifference = $newAmount - $oldAmount;
            $newBalance = max(0, $fee->balance + $amountDifference);

            // Update the fee amount and balance
            $fee->update([
                'amount' => $newAmount,
                'balance' => $newBalance,
                'status' => $this->calculateFeeStatus($newBalance, $fee->paid_amount),
            ]);

            // Update related invoice items
            $this->updateInvoiceItem($fee, $newAmount, 'Tuition Fee');
        }

        Log::info("Synced tuition fees for structure {$feeStructure->id}: {$oldAmount} -> {$newAmount}, affected {$fees->count()} records");
        return $fees->count();
    }

    /**
     * Sync additional fee changes
     */
    private function syncAdditionalFees(FeeStructure $feeStructure, $oldAdditionalFees, $newAdditionalFees)
    {
        $oldFees = collect($oldAdditionalFees);
        $newFees = collect($newAdditionalFees);
        $totalAffected = 0;

        // Handle removed fees
        $removedFees = $oldFees->pluck('name')->diff($newFees->pluck('name'));
        foreach ($removedFees as $removedFeeName) {
            $totalAffected += $this->removeAdditionalFee($feeStructure, $removedFeeName);
        }

        // Handle added or modified fees
        foreach ($newFees as $newFee) {
            $oldFee = $oldFees->firstWhere('name', $newFee['name']);
            $feeType = $this->getFeeTypeFromName($newFee['name']);

            if (!$oldFee) {
                // New additional fee - create for all students with invoices
                $totalAffected += $this->addNewAdditionalFee($feeStructure, $newFee, $feeType);
            } else {
                // Existing fee - update if amount changed
                if ($oldFee['amount'] != $newFee['amount']) {
                    $totalAffected += $this->updateAdditionalFee($feeStructure, $newFee, $oldFee['amount'], $feeType);
                }
                // Also update description if changed
                if (($oldFee['description'] ?? '') != ($newFee['description'] ?? '')) {
                    $this->updateAdditionalFeeDescription($feeStructure, $newFee, $feeType);
                }
            }
        }

        return $totalAffected;
    }

    /**
     * Remove an additional fee from all students
     */
    private function removeAdditionalFee(FeeStructure $feeStructure, $feeName)
    {
        $feeType = $this->getFeeTypeFromName($feeName);

        $fees = Fee::where('original_fee_structure_id', $feeStructure->id)
            ->where('fee_type', $feeType)
            ->get();

        foreach ($fees as $fee) {
            // Find and remove related invoice items
            $this->removeInvoiceItem($fee, $feeName);

            // Delete the fee record
            $fee->delete();
        }

        Log::info("Removed additional fee '{$feeName}' from structure {$feeStructure->id}, affected {$fees->count()} records");
        return $fees->count();
    }

    /**
     * Add a new additional fee to all students with invoices
     */
    private function addNewAdditionalFee(FeeStructure $feeStructure, $feeData, $feeType)
    {
        $invoices = FeeInvoice::where('fee_structure_id', $feeStructure->id)->get();
        $createdCount = 0;

        foreach ($invoices as $invoice) {
            // Check if fee already exists for this student
            $existingFee = Fee::where('student_id', $invoice->student_id)
                ->where('original_fee_structure_id', $feeStructure->id)
                ->where('fee_type', $feeType)
                ->first();

            if (!$existingFee) {
                // Create the fee record
                Fee::create([
                    'student_id' => $invoice->student_id,
                    'rank_id' => $invoice->rank_id,
                    'original_fee_structure_id' => $feeStructure->id,
                    'fee_type' => $feeType,
                    'amount' => $feeData['amount'],
                    'paid_amount' => 0,
                    'balance' => $feeData['amount'],
                    'academic_year' => $feeStructure->academic_year,
                    'term' => $feeStructure->term,
                    'due_date' => $feeStructure->due_date,
                    'status' => 'pending',
                    'is_carry_over' => false,
                    'description' => $feeData['description'] ?? $feeData['name'],
                ]);

                // Add to invoice
                $this->addInvoiceItem($invoice, $feeData['name'], $feeData['amount'], $feeData['description'] ?? '');
                $createdCount++;
            }
        }

        Log::info("Added new additional fee '{$feeData['name']}' to structure {$feeStructure->id}, affected {$createdCount} students");
        return $createdCount;
    }

    /**
     * Update an existing additional fee amount
     */
    private function updateAdditionalFee(FeeStructure $feeStructure, $feeData, $oldAmount, $feeType)
    {
        $fees = Fee::where('original_fee_structure_id', $feeStructure->id)
            ->where('fee_type', $feeType)
            ->get();

        foreach ($fees as $fee) {
            // Calculate the difference
            $amountDifference = $feeData['amount'] - $oldAmount;
            $newBalance = max(0, $fee->balance + $amountDifference);

            // Update the fee
            $fee->update([
                'amount' => $feeData['amount'],
                'balance' => $newBalance,
                'status' => $this->calculateFeeStatus($newBalance, $fee->paid_amount),
            ]);

            // Update related invoice items
            $this->updateInvoiceItem($fee, $feeData['amount'], $feeData['name']);
        }

        Log::info("Updated additional fee '{$feeData['name']}' for structure {$feeStructure->id}: {$oldAmount} -> {$feeData['amount']}, affected {$fees->count()} records");
        return $fees->count();
    }

    /**
     * Update additional fee description
     */
    private function updateAdditionalFeeDescription(FeeStructure $feeStructure, $feeData, $feeType)
    {
        $fees = Fee::where('original_fee_structure_id', $feeStructure->id)
            ->where('fee_type', $feeType)
            ->get();

        foreach ($fees as $fee) {
            $fee->update([
                'description' => $feeData['description'] ?? $feeData['name'],
            ]);
        }

        Log::info("Updated description for additional fee '{$feeData['name']}' in structure {$feeStructure->id}, affected {$fees->count()} records");
        return $fees->count();
    }

    /**
     * Sync due date changes
     */
    private function syncDueDate(FeeStructure $feeStructure, $newDueDate)
    {
        $fees = Fee::where('original_fee_structure_id', $feeStructure->id)->get();

        foreach ($fees as $fee) {
            $fee->update(['due_date' => $newDueDate]);
        }

        // Also update invoices due date
        $invoices = FeeInvoice::where('fee_structure_id', $feeStructure->id)->get();
        foreach ($invoices as $invoice) {
            $invoice->update(['due_date' => $newDueDate]);
        }

        Log::info("Updated due date for structure {$feeStructure->id}, affected {$fees->count()} fee records and {$invoices->count()} invoices");
        return $fees->count() + $invoices->count();
    }

    /**
     * Update invoice item for a fee
     */
    private function updateInvoiceItem(Fee $fee, $newAmount, $itemName)
    {
        $invoice = FeeInvoice::where('fee_structure_id', $fee->original_fee_structure_id)
            ->where('student_id', $fee->student_id)
            ->first();

        if ($invoice) {
            $invoiceItem = FeeInvoiceItem::where('fee_invoice_id', $invoice->id)
                ->where('item_name', $itemName)
                ->first();

            if ($invoiceItem) {
                $invoiceItem->update(['amount' => $newAmount]);
                $this->recalculateInvoiceTotals($invoice);
            }
        }
    }

    /**
     * Remove an invoice item
     */
    private function removeInvoiceItem(Fee $fee, $itemName)
    {
        $invoice = FeeInvoice::where('fee_structure_id', $fee->original_fee_structure_id)
            ->where('student_id', $fee->student_id)
            ->first();

        if ($invoice) {
            $invoiceItem = FeeInvoiceItem::where('fee_invoice_id', $invoice->id)
                ->where('item_name', $itemName)
                ->first();

            if ($invoiceItem) {
                $invoiceItem->delete();
                $this->recalculateInvoiceTotals($invoice);
            }
        }
    }

    /**
     * Add an invoice item
     */
    private function addInvoiceItem(FeeInvoice $invoice, $name, $amount, $description = null)
    {
        FeeInvoiceItem::create([
            'fee_invoice_id' => $invoice->id,
            'item_name' => $name,
            'amount' => $amount,
            'description' => $description,
        ]);
        $this->recalculateInvoiceTotals($invoice);
    }

    /**
     * Recalculate invoice totals
     */
    private function recalculateInvoiceTotals(FeeInvoice $invoice)
    {
        $totalAmount = FeeInvoiceItem::where('fee_invoice_id', $invoice->id)->sum('amount');
        $paidAmount = $invoice->paid_amount;
        $balance = $totalAmount - $paidAmount;

        $invoice->update([
            'total_amount' => $totalAmount,
            'balance' => $balance,
            'status' => $balance <= 0 ? 'paid' : ($paidAmount > 0 ? 'partial' : 'pending'),
        ]);
    }

    /**
     * Calculate fee status based on balance and paid amount
     */
    private function calculateFeeStatus($balance, $paidAmount)
    {
        if ($balance <= 0) {
            return 'paid';
        } elseif ($paidAmount > 0) {
            return 'partial';
        } else {
            return 'pending';
        }
    }

    public function destroy(FeeStructure $fee_structure)
    {
        try {
            Log::info('Deleting Fee Structure:', $fee_structure->toArray());

            // Check if there are generated invoices
            if ($fee_structure->invoices()->exists()) {
                return redirect()->route('admin.fee-structures.index')
                    ->with('error', 'Cannot delete fee structure. There are generated invoices associated with it.');
            }

            $fee_structure->delete();

            return redirect()->route('admin.fee-structures.index')
                ->with('success', 'Fee structure deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Fee Structure Deletion Error: ' . $e->getMessage());
            return redirect()->route('admin.fee-structures.index')
                ->with('error', 'Failed to delete fee structure: ' . $e->getMessage());
        }
    }

    public function updateStatus(Request $request, FeeStructure $fee_structure)
    {
        try {
            $request->validate([
                'is_active' => 'required|boolean',
            ]);

            $fee_structure->update([
                'is_active' => $request->is_active,
            ]);

            $status = $request->is_active ? 'activated' : 'deactivated';

            return redirect()->route('admin.fee-structures.index')
                ->with('success', "Fee structure {$status} successfully!");
        } catch (\Exception $e) {
            Log::error('Fee Structure Status Update Error: ' . $e->getMessage());
            return redirect()->route('admin.fee-structures.index')
                ->with('error', 'Failed to update fee structure status: ' . $e->getMessage());
        }
    }

    /**
     * NEW: Apply fee structure to a specific student
     * This can be called when a new student is registered
     */
    public function applyToStudent(FeeStructure $feeStructure, Student $student)
    {
        try {
            // Check if student is in the correct class for this fee structure
            if ($student->rank_id != $feeStructure->rank_id) {
                return [
                    'success' => false,
                    'message' => "Student is not in the required class. Fee structure is for {$feeStructure->rank->name}, student is in {$student->rank->name}"
                ];
            }

            // Check if student already has an invoice for this fee structure
            $existingInvoice = FeeInvoice::where('student_id', $student->id)
                ->where('fee_structure_id', $feeStructure->id)
                ->first();

            if ($existingInvoice) {
                return [
                    'success' => false,
                    'message' => 'Student already has fees generated for this fee structure.'
                ];
            }

            DB::beginTransaction();

            // Generate invoice for the student
            $invoice = $this->generateStudentInvoice($student, $feeStructure);

            DB::commit();

            Log::info("Fee structure applied to new student", [
                'fee_structure_id' => $feeStructure->id,
                'student_id' => $student->id,
                'invoice_id' => $invoice->id
            ]);

            return [
                'success' => true,
                'message' => 'Fees successfully applied to student.',
                'invoice' => $invoice
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error applying fee structure to student: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to apply fees to student: ' . $e->getMessage()
            ];
        }
    }

    /**
     * NEW: Apply fee structure to all students in the class who don't have it yet
     * This can be called manually or automatically
     */
    public function applyToAllEligibleStudents(FeeStructure $feeStructure)
    {
        try {
            $students = Student::where('rank_id', $feeStructure->rank_id)
                ->where('status', 'active')
                ->whereDoesntHave('feeInvoices', function ($query) use ($feeStructure) {
                    $query->where('fee_structure_id', $feeStructure->id);
                })
                ->get();

            if ($students->isEmpty()) {
                return [
                    'success' => true,
                    'message' => 'No eligible students found. All students already have fees or no active students in this class.',
                    'applied_count' => 0
                ];
            }

            $appliedCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($students as $student) {
                try {
                    $invoice = $this->generateStudentInvoice($student, $feeStructure);
                    if ($invoice) {
                        $appliedCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed for {$student->full_name}: " . $e->getMessage();
                    Log::error("Error applying fees to student {$student->id}: " . $e->getMessage());
                }
            }

            DB::commit();

            $result = [
                'success' => true,
                'message' => "Successfully applied fees to {$appliedCount} new student(s).",
                'applied_count' => $appliedCount
            ];

            if (!empty($errors)) {
                $result['errors'] = $errors;
                $result['message'] .= " " . count($errors) . " errors occurred.";
            }

            Log::info("Fee structure applied to eligible students", [
                'fee_structure_id' => $feeStructure->id,
                'applied_count' => $appliedCount,
                'error_count' => count($errors)
            ]);

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error applying fee structure to eligible students: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to apply fees to eligible students: ' . $e->getMessage()
            ];
        }
    }

    /**
     * NEW: Check for and apply fees to new students automatically
     * This can be called from a job, command, or when a student is registered
     */
    public function syncAllFeeStructures()
    {
        try {
            $feeStructures = FeeStructure::where('is_active', true)->get();
            $totalApplied = 0;
            $results = [];

            foreach ($feeStructures as $feeStructure) {
                $result = $this->applyToAllEligibleStudents($feeStructure);
                $results[$feeStructure->id] = $result;

                if ($result['success']) {
                    $totalApplied += $result['applied_count'] ?? 0;
                }
            }

            Log::info("Fee structure sync completed", [
                'total_structures' => $feeStructures->count(),
                'total_applied' => $totalApplied
            ]);

            return [
                'success' => true,
                'message' => "Sync completed. Applied fees to {$totalApplied} student(s) across {$feeStructures->count()} fee structure(s).",
                'results' => $results
            ];
        } catch (\Exception $e) {
            Log::error('Error syncing all fee structures: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to sync fee structures: ' . $e->getMessage()
            ];
        }
    }

    /**
     * NEW: API endpoint to apply fees to a specific student for all relevant fee structures
     * Call this when a new student is registered
     */
    public function applyRelevantFeesToStudent(Student $student)
    {
        try {
            // Get all active fee structures for the student's class
            $feeStructures = FeeStructure::where('rank_id', $student->rank_id)
                ->where('is_active', true)
                ->get();

            if ($feeStructures->isEmpty()) {
                return [
                    'success' => true,
                    'message' => 'No active fee structures found for this class.',
                    'applied_count' => 0
                ];
            }

            $appliedCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($feeStructures as $feeStructure) {
                try {
                    // Check if student already has this fee structure
                    $existingInvoice = FeeInvoice::where('student_id', $student->id)
                        ->where('fee_structure_id', $feeStructure->id)
                        ->first();

                    if (!$existingInvoice) {
                        $invoice = $this->generateStudentInvoice($student, $feeStructure);
                        if ($invoice) {
                            $appliedCount++;
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = "Fee structure {$feeStructure->academic_year} Term {$feeStructure->term}: " . $e->getMessage();
                    Log::error("Error applying fee structure {$feeStructure->id} to student {$student->id}: " . $e->getMessage());
                }
            }

            DB::commit();

            $result = [
                'success' => true,
                'message' => "Successfully applied {$appliedCount} fee structure(s) to student.",
                'applied_count' => $appliedCount
            ];

            if (!empty($errors)) {
                $result['errors'] = $errors;
                $result['message'] .= " " . count($errors) . " errors occurred.";
            }

            Log::info("Relevant fees applied to new student", [
                'student_id' => $student->id,
                'applied_count' => $appliedCount,
                'class_id' => $student->rank_id
            ]);

            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error applying relevant fees to student: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Failed to apply fees to student: ' . $e->getMessage()
            ];
        }
    }

    /**
     * UPDATED: Generate fees for students who don't have it yet
     * Now only generates for students who don't have the fee structure
     */
    public function generateFees(Request $request, FeeStructure $fee_structure)
    {
        try {
            Log::info('=== FEE GENERATION STARTED ===');
            Log::info('Fee Structure:', $fee_structure->toArray());

            if (!$fee_structure->is_active) {
                return redirect()->route('admin.fee-structures.index')
                    ->with('error', 'Cannot generate fees for an inactive fee structure.');
            }

            // Get students who don't have this fee structure yet
            $students = Student::where('rank_id', $fee_structure->rank_id)
                ->where('status', 'active')
                ->whereDoesntHave('feeInvoices', function ($query) use ($fee_structure) {
                    $query->where('fee_structure_id', $fee_structure->id);
                })
                ->get();

            Log::info('Eligible students found:', ['count' => $students->count()]);

            if ($students->isEmpty()) {
                return redirect()->route('admin.fee-structures.index')
                    ->with('info', 'All active students in ' . $fee_structure->rank->name . ' already have fees generated for this structure.');
            }

            $generatedCount = 0;
            $errors = [];

            DB::beginTransaction();

            try {
                foreach ($students as $student) {
                    try {
                        // Generate invoice for student
                        $invoice = $this->generateStudentInvoice($student, $fee_structure);

                        if ($invoice) {
                            $generatedCount++;
                            Log::info('Invoice generated successfully:', [
                                'invoice_id' => $invoice->id,
                                'invoice_number' => $invoice->invoice_number
                            ]);
                        } else {
                            $errors[] = "Failed to generate invoice for {$student->full_name}";
                        }
                    } catch (\Exception $e) {
                        $errorMsg = "Error for {$student->full_name}: " . $e->getMessage();
                        $errors[] = $errorMsg;
                        Log::error($errorMsg);
                    }
                }

                DB::commit();
                Log::info('=== FEE GENERATION COMPLETED ===');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('=== FEE GENERATION FAILED - TRANSACTION ROLLED BACK ===');
                Log::error('Transaction error: ' . $e->getMessage());
                throw $e;
            }

            $message = "Generated {$generatedCount} student invoices for " . $fee_structure->rank->name;

            if (!empty($errors)) {
                $message .= ". " . count($errors) . " errors occurred.";
                session()->flash('generation_errors', $errors);
            }

            return redirect()->route('admin.fee-structures.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Fee Generation Error: ' . $e->getMessage());
            return redirect()->route('admin.fee-structures.index')
                ->with('error', 'Failed to generate fees: ' . $e->getMessage());
        }
    }

    private function generateStudentInvoice(Student $student, FeeStructure $fee_structure)
    {
        try {
            // Calculate previous balance
            $previousFees = $this->getPreviousFees($student->id, $fee_structure->academic_year, $fee_structure->term);
            $previousBalance = $previousFees->sum('balance');

            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber();

            // Calculate total amount
            $totalAmount = $fee_structure->amount;
            $additionalFees = $fee_structure->additional_fees ?? [];

            foreach ($additionalFees as $fee) {
                $totalAmount += $fee['amount'];
            }

            if ($previousBalance != 0) {
                $totalAmount += $previousBalance;
            }

            // Create the main fee invoice
            $invoice = FeeInvoice::create([
                'student_id' => $student->id,
                'rank_id' => $student->rank_id,
                'fee_structure_id' => $fee_structure->id,
                'invoice_number' => $invoiceNumber,
                'academic_year' => $fee_structure->academic_year,
                'term' => $fee_structure->term,
                'due_date' => $fee_structure->due_date,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'balance' => $totalAmount,
                'status' => $totalAmount > 0 ? 'pending' : 'paid',
                'is_carry_over' => $previousBalance != 0,
                'notes' => $this->generateFeeDescription($fee_structure, $previousBalance),
            ]);

            // Add main tuition fee item
            $this->addInvoiceItem($invoice, 'Tuition Fee', $fee_structure->amount, $fee_structure->description);

            // Add additional fee items
            foreach ($additionalFees as $fee) {
                $this->addInvoiceItem($invoice, $fee['name'], $fee['amount'], $fee['description'] ?? '');
            }

            // Add previous balance item if exists
            if ($previousBalance != 0) {
                $description = $previousBalance > 0 ? 'Previous Balance Carry-over' : 'Credit Balance Carry-over';
                $this->addInvoiceItem($invoice, 'Balance B/F', $previousBalance, $description);

                // Mark old fees as carried over
                foreach ($previousFees as $fee) {
                    $fee->update(['status' => 'carried_over']);
                }
            }

            // Create individual fee records
            $this->createIndividualFeeRecords($student, $fee_structure, $invoice, $previousBalance);

            return $invoice;
        } catch (\Exception $e) {
            Log::error('Invoice generation error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createIndividualFeeRecords(Student $student, FeeStructure $fee_structure, FeeInvoice $invoice, $previousBalance)
    {
        try {
            // Create main tuition fee record
            Fee::create([
                'student_id' => $student->id,
                'rank_id' => $student->rank_id,
                'original_fee_structure_id' => $fee_structure->id,
                'fee_type' => 'tuition',
                'amount' => $fee_structure->amount,
                'paid_amount' => 0,
                'balance' => $fee_structure->amount,
                'academic_year' => $fee_structure->academic_year,
                'term' => $fee_structure->term,
                'due_date' => $fee_structure->due_date,
                'status' => 'pending',
                'is_carry_over' => false,
                'description' => $fee_structure->description ?: "Term {$fee_structure->term} Tuition Fee",
            ]);

            // Create additional fee records
            $additionalFees = $fee_structure->additional_fees ?? [];
            foreach ($additionalFees as $additionalFee) {
                $feeType = $this->getFeeTypeFromName($additionalFee['name']);
                Fee::create([
                    'student_id' => $student->id,
                    'rank_id' => $student->rank_id,
                    'original_fee_structure_id' => $fee_structure->id,
                    'fee_type' => $feeType,
                    'amount' => $additionalFee['amount'],
                    'paid_amount' => 0,
                    'balance' => $additionalFee['amount'],
                    'academic_year' => $fee_structure->academic_year,
                    'term' => $fee_structure->term,
                    'due_date' => $fee_structure->due_date,
                    'status' => 'pending',
                    'is_carry_over' => false,
                    'description' => $additionalFee['description'] ?? $additionalFee['name'],
                ]);
            }

            // Create carry-over balance fee record if exists
            if ($previousBalance != 0) {
                $description = $previousBalance > 0 ? 'Previous Balance Carry-over' : 'Credit Balance Carry-over';
                Fee::create([
                    'student_id' => $student->id,
                    'rank_id' => $student->rank_id,
                    'original_fee_structure_id' => $fee_structure->id,
                    'fee_type' => 'carry_over',
                    'amount' => abs($previousBalance),
                    'paid_amount' => 0,
                    'balance' => abs($previousBalance),
                    'academic_year' => $fee_structure->academic_year,
                    'term' => $fee_structure->term,
                    'due_date' => $fee_structure->due_date,
                    'status' => 'pending',
                    'is_carry_over' => true,
                    'description' => $description,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to create individual fee records: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getFeeTypeFromName($name)
    {
        $mapping = [
            'activity' => 'activity',
            'examination' => 'exam',
            'library' => 'library',
            'sports' => 'sports',
            'transport' => 'transport',
            'hostel' => 'hostel',
        ];

        $lowerName = strtolower($name);

        foreach ($mapping as $key => $value) {
            if (str_contains($lowerName, $key)) {
                return $value;
            }
        }

        return 'other';
    }

    private function getPreviousFees($studentId, $academicYear, $term)
    {
        try {
            return Fee::where('student_id', $studentId)
                ->where(function ($query) use ($academicYear, $term) {
                    $query->where('academic_year', $academicYear)
                        ->where('term', '<', $term)
                        ->orWhere('academic_year', '<', $academicYear);
                })
                ->where('balance', '!=', 0)
                ->where('status', '!=', 'carried_over')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error getting previous fees: ' . $e->getMessage());
            return collect([]);
        }
    }

    private function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        $lastInvoice = FeeInvoice::where('invoice_number', 'like', "INV-{$year}{$month}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "INV-{$year}{$month}-{$newNumber}";
    }

    private function generateFeeDescription($fee_structure, $previousBalance)
    {
        $description = $fee_structure->description ?: "Term {$fee_structure->term} Fees";

        if ($previousBalance > 0) {
            $description .= " (Includes KSh " . number_format($previousBalance, 2) . " previous balance)";
        } elseif ($previousBalance < 0) {
            $description .= " (Includes KSh " . number_format(abs($previousBalance), 2) . " credit from previous terms)";
        }

        return $description;
    }

    public function bulkDeleteFees(Request $request, FeeStructure $fee_structure)
    {
        try {
            DB::beginTransaction();

            $invoiceIds = FeeInvoice::where('fee_structure_id', $fee_structure->id)
                ->pluck('id');

            // Delete related records
            FeeInvoiceItem::whereIn('fee_invoice_id', $invoiceIds)->delete();
            Fee::whereIn('fee_invoice_id', $invoiceIds)->delete();
            $deletedCount = FeeInvoice::where('fee_structure_id', $fee_structure->id)->delete();

            DB::commit();

            return redirect()->back()
                ->with('success', "Successfully deleted {$deletedCount} generated fees for this fee structure.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk delete fees error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to delete generated fees: ' . $e->getMessage());
        }
    }

    private function getAcademicYears()
    {
        $currentYear = now()->year;
        $years = [];

        for ($i = -2; $i <= 2; $i++) {
            $year = $currentYear + $i;
            $years[] = [
                'id' => $year,
                'name' => $year . '/' . ($year + 1),
            ];
        }

        return $years;
    }

    public function dataTable(Request $request)
    {
        return FeeStructure::with(['rank'])
            ->when($request->has('class_id') && $request->class_id, function ($query) use ($request) {
                $query->where('rank_id', $request->class_id);
            })
            ->when($request->has('academic_year') && $request->academic_year, function ($query) use ($request) {
                $query->where('academic_year', $request->academic_year);
            })
            ->when($request->has('is_active') && $request->is_active !== '', function ($query) use ($request) {
                $query->where('is_active', $request->is_active);
            })
            ->orderBy('academic_year', 'desc')
            ->orderBy('rank_id')
            ->orderBy('term')
            ->paginate($request->get('per_page', 20));
    }
}
