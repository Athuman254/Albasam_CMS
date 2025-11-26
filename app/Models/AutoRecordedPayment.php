<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AutoRecordedPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'transaction_id',
        'amount',
        'payment_method',
        'account_number',
        'payer_name',
        'payer_phone',
        'payer_account',
        'payment_date',
        'narration',
        'status',
        'matched_student_id',
        'matched_admission_number',
        'verification_notes',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'verified_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'matched_student_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function feePayments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

  
    public function scopeRecorded($query)
    {
        return $query->where('status', 'recorded');
    }

    public function scopeUnmatched($query)
    {
        return $query->where('status', 'unmatched');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopePendingVerification($query)
    {
        return $query->whereIn('status', ['recorded', 'unmatched']);
    }

    public function scopeMpesa($query)
    {
        return $query->where('payment_method', 'mpesa');
    }

    public function scopeBank($query)
    {
        return $query->where('payment_method', 'bank');
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'recorded' => 'bg-primary',
            'verified' => 'bg-success',
            'rejected' => 'bg-danger',
            'unmatched' => 'bg-warning',
        ];

        return '<span class="badge ' . ($badges[$this->status] ?? 'bg-secondary') . '">' . ucfirst($this->status) . '</span>';
    }

    public function getCanBeVerifiedAttribute(): bool
    {
        return in_array($this->status, ['recorded', 'unmatched']) && !empty($this->matched_student_id);
    }

    public function getFormattedPaymentMethodAttribute(): string
    {
        return match($this->payment_method) {
            'mpesa' => 'M-Pesa',
            'bank' => 'Bank Transfer',
            default => ucfirst($this->payment_method)
        };
    }

    public function getTotalAllocatedAmountAttribute(): float
    {
        return $this->feePayments()->sum('amount');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, $this->amount - $this->total_allocated_amount);
    }

    public function getIsFullyAllocatedAttribute(): bool
    {
        return $this->remaining_amount <= 0;
    }

    public function getIsPartiallyAllocatedAttribute(): bool
    {
        return $this->total_allocated_amount > 0 && $this->remaining_amount > 0;
    }

    public function canBeAllocatedTo(Student $student): bool
    {
        return $this->status === 'recorded' && 
               $this->matched_student_id === $student->id &&
               $this->remaining_amount > 0;
    }
}