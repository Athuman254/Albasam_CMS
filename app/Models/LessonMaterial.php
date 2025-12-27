<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'rank_id',
        'teacher_id',
        'title',
        'description',
        'file_path',
        'file_type',
        'material_type',
        'download_count',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'download_count' => 'integer',
    ];

    /**
     * Get the subject that owns the lesson material.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the rank/class that owns the lesson material.
     */
    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }

    /**
     * Get the teacher that created the lesson material.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Increment download count.
     */
    public function incrementDownloads(): void
    {
        $this->increment('download_count');
    }

    /**
     * Scope for published materials.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope for specific material type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('material_type', $type);
    }
}
