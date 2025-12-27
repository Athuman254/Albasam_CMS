<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradingScale extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the entries for the grading scale.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(GradingEntry::class)->orderBy('min_score', 'desc');
    }

    /**
     * Scope a query to only include active scales.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include the default scale.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
