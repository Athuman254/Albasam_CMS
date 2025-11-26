<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeTransfer extends Model
{
    protected $fillable = [
        'from_student_id', 
        'to_student_id', 
        'amount', 
        'reason_type',
        'reason_notes',
        'initiated_by',
        'status',
        'reference_number',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->reference_number = $model->generateReferenceNumber();
            
            // Validate reason if provided
            if ($model->reason_type && !$model->isValidReason($model->reason_type)) {
                throw new \Exception('Invalid transfer reason type.');
            }
        });
    }

    /**
     * Check if the reason type is valid
     */
    public function isValidReason(string $reasonType): bool
    {
        $validReasons = array_keys(config('fees.valid_transfer_reasons', []));
        return in_array($reasonType, $validReasons);
    }

    /**
     * Get formatted reason with notes
     */
    public function getFormattedReasonAttribute(): string
    {
        $reasonTypes = config('fees.valid_transfer_reasons', []);
        $baseReason = $reasonTypes[$this->reason_type] ?? $this->reason_type;
        
        if ($this->reason_notes) {
            return $baseReason . ' - ' . $this->reason_notes;
        }
        
        return $baseReason;
    }

    /**
     * Check if reason requires additional approval
     */
    public function requiresApproval(): bool
    {
        $requireApproval = config('fees.require_reason_approval', []);
        return isset($requireApproval[$this->reason_type]) && $requireApproval[$this->reason_type];
    }

    public function generateReferenceNumber(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -6));
        
        return "FT{$date}{$random}";
    }

    // Relationships
    public function fromStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'from_student_id');
    }

    public function toStudent(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'to_student_id');
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function feePayments(): HasMany
    {
        return $this->hasMany(FeePayment::class, 'transfer_id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePendingApproval($query)
    {
        $requireApproval = config('fees.require_reason_approval', []);
        $reasons = array_keys(array_filter($requireApproval));
        
        return $query->whereIn('reason_type', $reasons)
                    ->where('status', 'pending_approval');
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where(function($q) use ($studentId) {
            $q->where('from_student_id', $studentId)
              ->orWhere('to_student_id', $studentId);
        });
    }

    public function scopeWithReason($query, $reasonType)
    {
        return $query->where('reason_type', $reasonType);
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return 'KSh ' . number_format($this->amount, 2);
    }

    public function getIsSuccessfulAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsApprovedAttribute(): bool
    {
        return !is_null($this->approved_by);
    }

    /**
     * Approve the transfer
     */
    public function approve(int $approvedBy): bool
    {
        $this->approved_by = $approvedBy;
        $this->approved_at = now();
        $this->status = 'completed';
        return $this->save();
    }
}