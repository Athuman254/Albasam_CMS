<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevisionTool extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'rank_id',
        'teacher_id',
        'title',
        'description',
        'tool_type',
        'file_path',
        'topic',
        'year',
        'download_count',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'download_count' => 'integer',
        'year' => 'integer',
    ];

    /**
     * Get the subject that owns the revision tool.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the rank/class that owns the revision tool.
     */
    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }

    /**
     * Get the teacher that created the revision tool.
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
     * Scope for published tools.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope for specific tool type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('tool_type', $type);
    }

    /**
     * Scope for past papers.
     */
    public function scopePastPapers($query)
    {
        return $query->where('tool_type', 'past_paper');
    }

    /**
     * Scope for topical notes.
     */
    public function scopeTopicalNotes($query)
    {
        return $query->where('tool_type', 'topical_notes');
    }

    /**
     * Scope for specific year.
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }
}
