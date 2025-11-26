<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'rank_id',
        'academic_year',
        'term',
        'amount',
        'description',
        'due_date',
        'additional_fees',
        'is_active',
    ];

    protected $casts = [
        'additional_fees' => 'array',
        'is_active' => 'boolean',
        'amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(FeeInvoice::class, 'fee_structure_id');
    }

    public function fees(): HasMany
    {
        return $this->hasMany(Fee::class, 'original_fee_structure_id');
    }

    // Scope for active fee structures
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Get fee structure for specific class, year, and term
    public static function getFee($rankId, $academicYear, $term)
    {
        return static::where('rank_id', $rankId)
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->active()
            ->first();
    }

    // Get all fee structures for a class
    public static function getClassFees($rankId, $academicYear = null)
    {
        $query = static::where('rank_id', $rankId)->active();
        
        if ($academicYear) {
            $query->where('academic_year', $academicYear);
        }
        
        return $query->get()->groupBy('term');
    }

    // Check if fee structure exists
    public static function exists($rankId, $academicYear, $term)
    {
        return static::where('rank_id', $rankId)
            ->where('academic_year', $academicYear)
            ->where('term', $term)
            ->exists();
    }

    // Get formatted amount
    public function getFormattedAmountAttribute()
    {
        return 'KSh ' . number_format($this->amount, 2);
    }

    // Get academic years with fee structures
    public static function getAcademicYearsWithStructures()
    {
        return static::select('academic_year')
            ->distinct()
            ->orderBy('academic_year', 'desc')
            ->pluck('academic_year');
    }

    // Calculate total amount including additional fees
    public function getTotalAmountAttribute()
    {
        $total = $this->amount;
        
        if (!empty($this->additional_fees)) {
            foreach ($this->additional_fees as $fee) {
                $total += $fee['amount'] ?? 0;
            }
        }
        
        return $total;
    }

    // Get formatted total amount
    public function getFormattedTotalAmountAttribute()
    {
        return 'KSh ' . number_format($this->total_amount, 2);
    }

    // Get additional fees total
    public function getAdditionalFeesTotalAttribute()
    {
        $total = 0;
        
        if (!empty($this->additional_fees)) {
            foreach ($this->additional_fees as $fee) {
                $total += $fee['amount'] ?? 0;
            }
        }
        
        return $total;
    }

    // Get formatted additional fees total
    public function getFormattedAdditionalFeesTotalAttribute()
    {
        return 'KSh ' . number_format($this->additional_fees_total, 2);
    }

    // Check if has additional fees
    public function getHasAdditionalFeesAttribute()
    {
        return !empty($this->additional_fees) && count($this->additional_fees) > 0;
    }

    // Get additional fees count
    public function getAdditionalFeesCountAttribute()
    {
        return !empty($this->additional_fees) ? count($this->additional_fees) : 0;
    }

    // Get all fee items (tuition + additional fees) for display
    public function getFeeItemsAttribute()
    {
        $items = [
            [
                'name' => 'Tuition Fee',
                'amount' => $this->amount,
                'description' => $this->description,
                'type' => 'tuition'
            ]
        ];

        if (!empty($this->additional_fees)) {
            foreach ($this->additional_fees as $additionalFee) {
                $items[] = [
                    'name' => $additionalFee['name'] ?? 'Additional Fee',
                    'amount' => $additionalFee['amount'] ?? 0,
                    'description' => $additionalFee['description'] ?? '',
                    'type' => 'additional'
                ];
            }
        }

        return $items;
    }

    // Validate additional fees structure
    public function validateAdditionalFees()
    {
        if (empty($this->additional_fees)) {
            return true;
        }

        foreach ($this->additional_fees as $fee) {
            if (empty($fee['name']) || !isset($fee['amount']) || $fee['amount'] < 0) {
                return false;
            }
        }

        return true;
    }

    // Add an additional fee
    public function addAdditionalFee($name, $amount, $description = null)
    {
        $additionalFees = $this->additional_fees ?? [];
        
        $additionalFees[] = [
            'name' => $name,
            'amount' => $amount,
            'description' => $description
        ];

        $this->additional_fees = $additionalFees;
        return $this;
    }

    // Remove an additional fee by index
    public function removeAdditionalFee($index)
    {
        if (!empty($this->additional_fees) && isset($this->additional_fees[$index])) {
            $additionalFees = $this->additional_fees;
            array_splice($additionalFees, $index, 1);
            $this->additional_fees = $additionalFees;
        }
        
        return $this;
    }

    // Clear all additional fees
    public function clearAdditionalFees()
    {
        $this->additional_fees = [];
        return $this;
    }

    // Get fee structure summary for display
    public function getSummaryAttribute()
    {
        return [
            'class' => $this->rank->name ?? 'N/A',
            'academic_year' => $this->academic_year,
            'term' => 'Term ' . $this->term,
            'tuition_fee' => $this->formatted_amount,
            'additional_fees_count' => $this->additional_fees_count,
            'additional_fees_total' => $this->formatted_additional_fees_total,
            'total_amount' => $this->formatted_total_amount,
            'due_date' => $this->due_date->format('M j, Y'),
            'status' => $this->is_active ? 'Active' : 'Inactive'
        ];
    }
}