<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class Fee extends Model
{
    protected $fillable = [
        'student_id',
        'rank_id', 
        'fee_type',
        'amount', 
        'paid_amount', 
        'balance',
        'academic_year', 
        'term', 
        'due_date', 
        'status', 
        'description',
        'is_carry_over', 
        'original_fee_structure_id',
        'fee_invoice_id' 
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'due_date' => 'date',
        'is_carry_over' => 'boolean',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class, 'original_fee_structure_id');
    }

    // ADDED: Relationship to FeeInvoice
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }

    // Scopes
    public function scopeOutstanding($query)
    {
        return $query->where('balance', '>', 0);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())->where('balance', '>', 0);
    }

    public function scopeCurrentTerm($query, $academicYear = null, $term = null)
    {
        $academicYear = $academicYear ?? now()->year;
        $term = $term ?? self::getCurrentTerm();
        
        return $query->where('academic_year', $academicYear)
                    ->where('term', $term);
    }

    public function scopeCarryOver($query)
    {
        return $query->where('is_carry_over', true);
    }

    public function scopeRegular($query)
    {
        return $query->where('is_carry_over', false);
    }

    public function scopeByFeeType($query, $feeType)
    {
        return $query->where('fee_type', $feeType);
    }

    public function scopeByAcademicYear($query, $academicYear)
    {
        return $query->where('academic_year', $academicYear);
    }

    public function scopeByTerm($query, $term)
    {
        return $query->where('term', $term);
    }

    public function scopeByRank($query, $rankId)
    {
        return $query->where('rank_id', $rankId);
    }

    // ADDED: Scope to get fees with invoices
    public function scopeWithInvoice($query)
    {
        return $query->whereNotNull('fee_invoice_id');
    }

    // ADDED: Scope to get fees without invoices
    public function scopeWithoutInvoice($query)
    {
        return $query->whereNull('fee_invoice_id');
    }

    // Fee Generation Methods
    public static function generateFeesForClass($rankId, $academicYear, $term, $includeCarryOver = false)
    {
        $feeStructure = FeeStructure::getFee($rankId, $academicYear, $term);
        
        if (!$feeStructure) {
            throw new \Exception("No active fee structure found for this class and term");
        }

        $students = Student::where('rank_id', $rankId)->get();
        
        $generatedFees = [];
        
        foreach ($students as $student) {
            try {
                if ($includeCarryOver) {
                    $fee = self::generateStudentFee($student->id, $academicYear, $term, true);
                } else {
                    $fee = self::generateStudentFeeWithoutCarryOver($student->id, $academicYear, $term);
                }
                $generatedFees[] = $fee;
            } catch (\Exception $e) {
                Log::error("Error generating fee for student {$student->id}: " . $e->getMessage());
                continue;
            }
        }

        return $generatedFees;
    }

    public static function generateStudentFeeWithoutCarryOver($studentId, $academicYear, $term)
    {
        $student = Student::find($studentId);
        
        if (!$student) {
            throw new \Exception("Student not found");
        }

        // Check if fee already exists
        $existingFee = Fee::where('student_id', $studentId)
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->where('is_carry_over', false)
            ->first();

        if ($existingFee) {
            return $existingFee;
        }

        // Get fee structure for student's class
        $feeStructure = FeeStructure::getFee($student->rank_id, $academicYear, $term);
        
        if (!$feeStructure) {
            throw new \Exception("No fee structure found for this student's class and term");
        }

        // Create the fee record
        return Fee::create([
            'student_id' => $studentId,
            'rank_id' => $student->rank_id,
            'amount' => $feeStructure->amount,
            'paid_amount' => 0,
            'balance' => $feeStructure->amount,
            'academic_year' => $academicYear,
            'term' => $term,
            'due_date' => $feeStructure->due_date,
            'status' => 'pending',
            'description' => $feeStructure->description,
            'is_carry_over' => false,
            'original_fee_structure_id' => $feeStructure->id,
        ]);
    }

    public static function generateStudentFee($studentId, $academicYear, $term, $includeCarryOver = true)
    {
        $student = Student::find($studentId);
        
        if (!$student) {
            throw new \Exception("Student not found");
        }

        // Check if regular fee already exists
        $existingFee = Fee::where('student_id', $studentId)
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->where('is_carry_over', false)
            ->first();

        if ($existingFee) {
            return $existingFee;
        }

        // Get fee structure for student's class
        $feeStructure = FeeStructure::getFee($student->rank_id, $academicYear, $term);
        
        if (!$feeStructure) {
            throw new \Exception("No fee structure found for this student's class and term");
        }

        // Calculate carry-over amount if needed
        $carryOverAmount = 0;
        $carryOverDescription = '';
        
        if ($includeCarryOver) {
            $previousFees = Fee::where('student_id', $studentId)
                ->where(function($query) use ($academicYear, $term) {
                    // Previous terms in same academic year
                    $query->where('academic_year', $academicYear)
                          ->where('term', '<', $term)
                          ->orWhere('academic_year', '<', $academicYear); // Previous years
                })
                ->where('balance', '>', 0)
                ->where('is_carry_over', false)
                ->get();
                
            $carryOverAmount = $previousFees->sum('balance');
            $carryOverDescription = self::buildCarryOverDescription($previousFees);
        }

        $totalAmount = $feeStructure->amount + $carryOverAmount;

        // Create the main fee record
        $fee = Fee::create([
            'student_id' => $studentId,
            'rank_id' => $student->rank_id,
            'amount' => $totalAmount,
            'paid_amount' => 0,
            'balance' => $totalAmount,
            'academic_year' => $academicYear,
            'term' => $term,
            'due_date' => $feeStructure->due_date,
            'status' => 'pending',
            'description' => self::buildFeeDescription($feeStructure->description, $carryOverAmount, $carryOverDescription),
            'is_carry_over' => false,
            'original_fee_structure_id' => $feeStructure->id,
        ]);

        // If there's carry-over, create a separate record for tracking and mark previous fees
        if ($carryOverAmount > 0) {
            // Create carry-over tracking record
            Fee::create([
                'student_id' => $studentId,
                'rank_id' => $student->rank_id,
                'amount' => $carryOverAmount,
                'paid_amount' => 0,
                'balance' => $carryOverAmount,
                'academic_year' => $academicYear,
                'term' => $term,
                'due_date' => $feeStructure->due_date,
                'status' => 'pending',
                'description' => $carryOverDescription,
                'is_carry_over' => true,
            ]);

            // Mark previous fees as carried over
            foreach ($previousFees as $previousFee) {
                $previousFee->update([
                    'status' => 'carried_over',
                    'description' => $previousFee->description . ' [Balance carried forward]',
                ]);
            }
        }

        return $fee;
    }

    // NEW: Method to generate fee with invoice (for the new invoice system)
    public static function generateStudentFeeWithInvoice($studentId, $feeStructure, $previousBalance = 0)
    {
        $student = Student::find($studentId);
        
        if (!$student) {
            throw new \Exception("Student not found");
        }

        // Check if fee already exists for this term
        $existingFee = Fee::where('student_id', $studentId)
            ->where('academic_year', $feeStructure->academic_year)
            ->where('term', $feeStructure->term)
            ->where('is_carry_over', false)
            ->first();

        if ($existingFee) {
            return $existingFee;
        }

        $totalAmount = $feeStructure->amount + $previousBalance;

        // Create the fee record (invoice_id will be set later when invoice is created)
        return Fee::create([
            'student_id' => $studentId,
            'rank_id' => $student->rank_id,
            'amount' => $totalAmount,
            'paid_amount' => 0,
            'balance' => $totalAmount,
            'academic_year' => $feeStructure->academic_year,
            'term' => $feeStructure->term,
            'due_date' => $feeStructure->due_date,
            'status' => 'pending',
            'description' => self::buildInvoiceFeeDescription($feeStructure, $previousBalance),
            'is_carry_over' => $previousBalance != 0,
            'original_fee_structure_id' => $feeStructure->id,
        ]);
    }

    // NEW: Helper method for invoice fee description
    private static function buildInvoiceFeeDescription($feeStructure, $previousBalance)
    {
        $description = $feeStructure->description ?: "Term {$feeStructure->term} Fees";
        
        if ($previousBalance > 0) {
            $description .= " (Includes KSh " . number_format($previousBalance, 2) . " previous balance)";
        } elseif ($previousBalance < 0) {
            $description .= " (Includes KSh " . number_format(abs($previousBalance), 2) . " credit from previous terms)";
        }
        
        return $description;
    }

    // Carry-over Management
    public static function carryOverBalances($studentId, $fromAcademicYear, $fromTerm, $toAcademicYear, $toTerm)
    {
        // Get outstanding fees from previous term
        $outstandingFees = Fee::where('student_id', $studentId)
            ->where('academic_year', $fromAcademicYear)
            ->where('term', $fromTerm)
            ->where('balance', '>', 0)
            ->where('is_carry_over', false)
            ->get();

        if ($outstandingFees->isEmpty()) {
            return null;
        }

        $totalCarryOver = $outstandingFees->sum('balance');
        
        // Get student's current class
        $student = Student::find($studentId);
        if (!$student) {
            throw new \Exception("Student not found");
        }

        // Get the fee structure for the new term
        $newFeeStructure = FeeStructure::getFee($student->rank_id, $toAcademicYear, $toTerm);
        
        if (!$newFeeStructure) {
            throw new \Exception("No fee structure found for the new term");
        }

        // Create carry-over fee record
        $carryOverFee = Fee::create([
            'student_id' => $studentId,
            'rank_id' => $student->rank_id,
            'amount' => $totalCarryOver,
            'paid_amount' => 0,
            'balance' => $totalCarryOver,
            'academic_year' => $toAcademicYear,
            'term' => $toTerm,
            'due_date' => $newFeeStructure->due_date,
            'status' => 'pending',
            'description' => 'Carry-over balance from ' . $fromAcademicYear . ' Term ' . $fromTerm,
            'is_carry_over' => true,
        ]);

        // Mark original fees as carried over
        foreach ($outstandingFees as $fee) {
            $fee->update([
                'status' => 'carried_over',
                'description' => $fee->description . ' [Balance carried over to ' . $toAcademicYear . ' Term ' . $toTerm . ']',
            ]);
        }

        return $carryOverFee;
    }

    public static function bulkCarryOver($fromAcademicYear, $fromTerm, $toAcademicYear, $toTerm)
    {
        $studentsWithBalances = Fee::where('academic_year', $fromAcademicYear)
            ->where('term', $fromTerm)
            ->where('balance', '>', 0)
            ->where('is_carry_over', false)
            ->pluck('student_id')
            ->unique();

        $carriedOverCount = 0;
        
        foreach ($studentsWithBalances as $studentId) {
            try {
                self::carryOverBalances($studentId, $fromAcademicYear, $fromTerm, $toAcademicYear, $toTerm);
                $carriedOverCount++;
            } catch (\Exception $e) {
                Log::error("Error carrying over balance for student {$studentId}: " . $e->getMessage());
                continue;
            }
        }

        return $carriedOverCount;
    }

    // Payment Management
    public function recordPayment($amount, $paymentMethod, $referenceNumber, $notes = null)
    {
        if ($amount > $this->balance) {
            throw new \Exception("Payment amount cannot exceed outstanding balance.");
        }

        return DB::transaction(function () use ($amount, $paymentMethod, $referenceNumber, $notes) {
            // Create payment record
            $payment = FeePayment::create([
                'fee_id' => $this->id,
                'student_id' => $this->student_id,
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'reference_number' => $referenceNumber,
                'payment_date' => now(),
                'status' => 'completed',
                'notes' => $notes,
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

            // Update fee balance
            $this->paid_amount += $amount;
            $this->balance = $this->amount - $this->paid_amount;
            
            $this->updateStatus();
            $this->save();

            return $payment;
        });
    }

    public function applyPayment($payment)
    {
        if ($payment->amount > $this->balance) {
            throw new \Exception("Payment amount cannot exceed outstanding balance.");
        }

        $this->paid_amount += $payment->amount;
        $this->balance = $this->amount - $this->paid_amount;
        
        $this->updateStatus();
        $this->save();

        return $this;
    }

    // Status Management
    public function isOverdue()
    {
        return $this->due_date < now() && $this->balance > 0;
    }

    public function updateStatus()
    {
        if ($this->balance <= 0) {
            $this->status = 'paid';
        } elseif ($this->isOverdue()) {
            $this->status = 'overdue';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'pending';
        }
        
        return $this;
    }

    public static function bulkUpdateStatus()
    {
        $fees = Fee::where('balance', '>', 0)->get();
        
        $updatedCount = 0;
        foreach ($fees as $fee) {
            $fee->updateStatus();
            $fee->save();
            $updatedCount++;
        }
        
        return $updatedCount;
    }

    // Student Fee Information
    public static function getStudentTotalBalance($studentId)
    {
        return Fee::where('student_id', $studentId)
            ->where('balance', '>', 0)
            ->sum('balance');
    }

    public static function getStudentCurrentTermBalance($studentId, $academicYear = null, $term = null)
    {
        $academicYear = $academicYear ?? now()->year;
        $term = $term ?? self::getCurrentTerm();
        
        return Fee::where('student_id', $studentId)
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->where('balance', '>', 0)
            ->sum('balance');
    }

    public static function getStudentFeeSummary($studentId, $academicYear = null)
    {
        $query = Fee::where('student_id', $studentId);
        
        if ($academicYear) {
            $query->where('academic_year', $academicYear);
        }

        $fees = $query->get();

        $regularFees = $fees->where('is_carry_over', false);
        $carryOverFees = $fees->where('is_carry_over', true);

        return [
            'total_expected' => $regularFees->sum('amount'),
            'total_paid' => $fees->sum('paid_amount'),
            'total_balance' => $fees->sum('balance'),
            'current_term_balance' => self::getStudentCurrentTermBalance($studentId, $academicYear),
            'carry_over_balance' => $carryOverFees->sum('balance'),
            'regular_fees_count' => $regularFees->count(),
            'carry_over_fees_count' => $carryOverFees->count(),
            'fees' => $fees,
        ];
    }

    public static function getStudentCurrentYearFees($studentId)
    {
        $currentYear = now()->year;
        
        return Fee::where('student_id', $studentId)
            ->where('academic_year', $currentYear)
            ->orderBy('term')
            ->get()
            ->groupBy('term');
    }

    public static function getStudentYearlyTotal($studentId, $academicYear = null)
    {
        $academicYear = $academicYear ?? now()->year;
        
        $fees = Fee::where('student_id', $studentId)
            ->where('academic_year', $academicYear)
            ->where('is_carry_over', false)
            ->get();

        return [
            'total_expected' => $fees->sum('amount'),
            'total_paid' => $fees->sum('paid_amount'),
            'total_balance' => $fees->sum('balance'),
            'terms' => $fees->groupBy('term')->map(function($termFees) {
                return [
                    'expected' => $termFees->sum('amount'),
                    'paid' => $termFees->sum('paid_amount'),
                    'balance' => $termFees->sum('balance'),
                ];
            })
        ];
    }

    // NEW: Get student fees with invoice information
    public static function getStudentFeesWithInvoices($studentId, $academicYear = null)
    {
        $query = Fee::where('student_id', $studentId)
            ->with(['invoice', 'invoice.items']);
        
        if ($academicYear) {
            $query->where('academic_year', $academicYear);
        }

        return $query->get();
    }

    // Utility Methods
    private static function buildCarryOverDescription($previousFees)
    {
        $descriptions = [];
        foreach ($previousFees as $fee) {
            $descriptions[] = $fee->academic_year . ' Term ' . $fee->term . ': KSh ' . number_format($fee->balance, 2);
        }
        
        return 'Carry-over from: ' . implode('; ', $descriptions);
    }

    private static function buildFeeDescription($baseDescription, $carryOverAmount, $carryOverDescription)
    {
        if ($carryOverAmount > 0) {
            return $baseDescription . " (Includes KSh " . number_format($carryOverAmount, 2) . " carry-over)";
        }
        
        return $baseDescription;
    }

    public static function getCurrentTerm()
    {
        $month = now()->month;
        
        if ($month >= 1 && $month <= 4) {
            return '1'; // First term
        } elseif ($month >= 5 && $month <= 8) {
            return '2'; // Second term
        } else {
            return '3'; // Third term
        }
    }

    public static function getAcademicYears($yearsBack = 2, $yearsForward = 2)
    {
        $currentYear = now()->year;
        $years = [];
        
        for ($i = -$yearsBack; $i <= $yearsForward; $i++) {
            $year = $currentYear + $i;
            $years[] = (string)$year;
        }
        
        return $years;
    }

    public static function getTerms()
    {
        return [
            '1' => 'Term 1',
            '2' => 'Term 2', 
            '3' => 'Term 3',
        ];
    }

    public static function getStatusOptions()
    {
        return [
            'pending' => 'Pending',
            'partial' => 'Partial Payment',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
            'carried_over' => 'Carried Over',
        ];
    }

    public static function getFeeTypeOptions()
    {
        return [
            'tuition' => 'Tuition Fee',
            'activity' => 'Activity Fee',
            'exam' => 'Examination Fee',
            'library' => 'Library Fee',
            'sports' => 'Sports Fee',
            'transport' => 'Transport Fee',
            'hostel' => 'Hostel Fee',
            'other' => 'Other Fee',
        ];
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return 'KSh ' . number_format($this->amount, 2);
    }

    public function getFormattedPaidAmountAttribute()
    {
        return 'KSh ' . number_format($this->paid_amount, 2);
    }

    public function getFormattedBalanceAttribute()
    {
        return 'KSh ' . number_format($this->balance, 2);
    }

    public function getPaymentProgressAttribute()
    {
        if ($this->amount <= 0) {
            return 100;
        }
        
        return ($this->paid_amount / $this->amount) * 100;
    }

    public function getDaysUntilDueAttribute()
    {
        return now()->diffInDays($this->due_date, false);
    }

    public function getFeeTypeFormattedAttribute()
    {
        $types = self::getFeeTypeOptions();
        return $types[$this->fee_type] ?? ucfirst($this->fee_type);
    }

    // NEW: Accessor to check if fee has an invoice
    public function getHasInvoiceAttribute()
    {
        return !is_null($this->fee_invoice_id);
    }

    // NEW: Accessor to get invoice number if exists
    public function getInvoiceNumberAttribute()
    {
        return $this->invoice ? $this->invoice->invoice_number : null;
    }

    // Business Logic
    public function canAcceptPayment()
    {
        return $this->balance > 0 && !in_array($this->status, ['paid', 'carried_over']);
    }

    public function getCarryOverFeeAttribute()
    {
        if ($this->is_carry_over) {
            return null;
        }
        
        return Fee::where('student_id', $this->student_id)
            ->where('academic_year', $this->academic_year)
            ->where('term', $this->term)
            ->where('is_carry_over', true)
            ->first();
    }

    // NEW: Link fee to an invoice
    public function linkToInvoice($invoiceId)
    {
        $this->fee_invoice_id = $invoiceId;
        return $this->save();
    }

    // Event Handlers
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($fee) {
            // Set default balance if not set
            if (is_null($fee->balance)) {
                $fee->balance = $fee->amount - $fee->paid_amount;
            }

            // Set default status if not set
            if (is_null($fee->status)) {
                $fee->status = 'pending';
            }
        });

        static::updating(function ($fee) {
            // Recalculate balance if amount or paid_amount changes
            if ($fee->isDirty(['amount', 'paid_amount'])) {
                $fee->balance = $fee->amount - $fee->paid_amount;
                $fee->updateStatus();
            }
        });
    }
}