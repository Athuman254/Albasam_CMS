<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradingEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'grading_scale_id',
        'grade',
        'min_score',
        'max_score',
        'points',
        'remarks',
    ];

    protected $casts = [
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
    ];

    /**
     * Get the grading scale that owns the entry.
     */
    public function gradingScale(): BelongsTo
    {
        return $this->belongsTo(GradingScale::class);
    }
}
