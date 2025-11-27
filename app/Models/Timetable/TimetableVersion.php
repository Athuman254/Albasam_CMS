<?php

namespace App\Models\Timetable;

use App\Models\Settings\AcademicYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimetableVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'version_name',
        'description',
        'is_active',
        'is_published',
        'generation_stats',
        'created_by',
        'published_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_published' => 'boolean',
        'generation_stats' => 'array',
        'published_at' => 'datetime',
    ];

    /**
     * Get the academic year.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Get the user who created this version.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the allocations for this version.
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(TimetableAllocation::class, 'version_id');
    }

    /**
     * Scope to get only active versions.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only published versions.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Activate this version (deactivates others for the same academic year).
     */
    public function activate(): bool
    {
        // Deactivate all other versions for this academic year
        static::where('academic_year_id', $this->academic_year_id)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);

        // Activate this version
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Publish this version.
     */
    public function publish(): bool
    {
        $this->is_published = true;
        $this->published_at = now();
        return $this->save();
    }
}
