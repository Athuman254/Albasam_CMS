<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'rank_id',
        'invoice_number',
        'academic_year',
        'term',
        'due_date',
        'total_amount',
        'paid_amount',
        'balance',
        'status',
        'is_carry_over',
        'notes'
    ];

    protected $casts = [
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_carry_over' => 'boolean'
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fee_structure()
{
    return $this->belongsTo(FeeStructure::class);
}
    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(FeeInvoiceItem::class);
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class, 'fee_invoice_id'); // CORRECTED: Added foreign key
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeForAcademicYear($query, $academicYear)
    {
        return $query->where('academic_year', $academicYear);
    }

    public function scopeForTerm($query, $term)
    {
        return $query->where('term', $term);
    }

    public function scopeWithCarryOver($query)
    {
        return $query->where('is_carry_over', true);
    }

    public function scopeWithoutCarryOver($query)
    {
        return $query->where('is_carry_over', false);
    }

    // Accessors
    public function getFormattedTotalAmountAttribute()
    {
        return 'KSh ' . number_format($this->total_amount, 2);
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
        if ($this->total_amount <= 0) {
            return 100;
        }
        
        return ($this->paid_amount / $this->total_amount) * 100;
    }

    public function getDaysUntilDueAttribute()
    {
        return now()->diffInDays($this->due_date, false);
    }

    public function getIsOverdueAttribute()
    {
        return $this->due_date < now() && $this->balance > 0;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'bg-warning',
            'partial' => 'bg-info',
            'paid' => 'bg-success',
            'overdue' => 'bg-danger',
            'cancelled' => 'bg-secondary',
        ];

        return $badges[$this->status] ?? 'bg-secondary';
    }

    // Business Logic Methods
    public function updateTotals()
    {
        $totalAmount = $this->items->sum('amount');
        $this->total_amount = $totalAmount;
        $this->balance = $totalAmount - $this->paid_amount;
        $this->updateStatus();
        return $this->save();
    }

    public function updateStatus()
    {
        if ($this->balance <= 0) {
            $this->status = 'paid';
        } elseif ($this->is_overdue) {
            $this->status = 'overdue';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partial';
        } else {
            $this->status = 'pending';
        }
        
        return $this->save();
    }

    public function recordPayment($amount, $paymentMethod, $referenceNumber, $notes = null)
    {
        if ($amount > $this->balance) {
            throw new \Exception("Payment amount cannot exceed outstanding balance.");
        }

        $this->paid_amount += $amount;
        $this->balance = $this->total_amount - $this->paid_amount;
        
        $this->updateStatus();
        $this->save();

        // If there are linked fees, apply payment proportionally
        if ($this->fees->count() > 0) {
            $this->applyPaymentToFees($amount);
        }

        return $this;
    }

    private function applyPaymentToFees($totalAmount)
    {
        $fees = $this->fees()->where('balance', '>', 0)->get();
        
        if ($fees->isEmpty()) {
            return;
        }

        $totalFeeBalance = $fees->sum('balance');
        
        foreach ($fees as $fee) {
            // Calculate proportional payment for each fee
            $proportionalAmount = ($fee->balance / $totalFeeBalance) * $totalAmount;
            
            if ($proportionalAmount > 0) {
                $fee->paid_amount += $proportionalAmount;
                $fee->balance = $fee->amount - $fee->paid_amount;
                $fee->updateStatus();
                $fee->save();
            }
        }
    }

    public function addItem($name, $amount, $description = null)
    {
        return $this->items()->create([
            'item_name' => $name,
            'amount' => $amount,
            'description' => $description,
        ]);
    }

    public function getItemizedBreakdown()
    {
        return $this->items->map(function($item) {
            return [
                'name' => $item->item_name,
                'amount' => $item->amount,
                'description' => $item->description,
                'formatted_amount' => 'KSh ' . number_format($item->amount, 2),
            ];
        });
    }

    public function getSummary()
    {
        return [
            'invoice_number' => $this->invoice_number,
            'student_name' => $this->student->full_name ?? 'N/A',
            'class' => $this->rank->name ?? 'N/A',
            'academic_year' => $this->academic_year,
            'term' => 'Term ' . $this->term,
            'due_date' => $this->due_date->format('M j, Y'),
            'total_amount' => $this->formatted_total_amount,
            'paid_amount' => $this->formatted_paid_amount,
            'balance' => $this->formatted_balance,
            'status' => ucfirst($this->status),
            'payment_progress' => round($this->payment_progress, 2),
            'is_overdue' => $this->is_overdue,
            'days_until_due' => $this->days_until_due,
            'has_carry_over' => $this->is_carry_over,
            'items_count' => $this->items->count(),
            'fees_count' => $this->fees->count(),
        ];
    }

    // Static Methods
    public static function generateInvoiceNumber()
    {
        $year = date('Y');
        $lastInvoice = self::where('invoice_number', 'like', "INV-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "INV-{$year}-{$newNumber}";
    }

    public static function getStatusOptions()
    {
        return [
            'pending' => 'Pending',
            'partial' => 'Partial Payment',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
            'cancelled' => 'Cancelled',
        ];
    }

    // Event Handlers
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
        });

        static::created(function ($invoice) {
            // Update totals after items might be added
            $invoice->updateTotals();
        });
    }
}