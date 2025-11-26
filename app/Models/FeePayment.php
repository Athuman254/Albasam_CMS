<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePayment extends Model
{
    protected $fillable = [
        'fee_id', 'student_id', 'amount', 'payment_method', 'reference_number',
        'transaction_id', 'payment_date', 'status', 'notes', 'verified_by', 'verified_at',
        'auto_recorded_payment_id' 
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'verified_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function autoRecordedPayment(): BelongsTo
    {
        return $this->belongsTo(AutoRecordedPayment::class);
    }

    /**
     * Scopes
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeMpesa($query)
    {
        return $query->where('payment_method', 'mpesa');
    }

    public function scopeBank($query)
    {
        return $query->where('payment_method', 'bank');
    }

    public function scopeCash($query)
    {
        return $query->where('payment_method', 'cash');
    }
    
    public function getIsAutoAllocatedAttribute(): bool
    {
        return !is_null($this->auto_recorded_payment_id);
    }

    public function getFormattedPaymentMethodAttribute(): string
    {
        return match($this->payment_method) {
            'mpesa' => 'M-Pesa',
            'bank' => 'Bank Transfer',
            'cash' => 'Cash',
            default => ucfirst($this->payment_method)
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'completed' => 'bg-success',
            'pending' => 'bg-warning',
            'failed' => 'bg-danger',
            'reversed' => 'bg-secondary',
        ];

        return '<span class="badge ' . ($badges[$this->status] ?? 'bg-secondary') . '">' . ucfirst($this->status) . '</span>';
    }
}