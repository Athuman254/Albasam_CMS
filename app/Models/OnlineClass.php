<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'rank_id',
        'teacher_id',
        'title',
        'description',
        'meeting_link',
        'meeting_platform',
        'meeting_id',
        'meeting_password',
        'scheduled_at',
        'duration_minutes',
        'recording_link',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    /**
     * Get the subject that owns the online class.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the rank/class that owns the online class.
     */
    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class);
    }

    /**
     * Get the teacher that created the online class.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Scope for scheduled classes.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope for ongoing classes.
     */
    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing');
    }

    /**
     * Scope for completed classes.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for upcoming classes.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now())
            ->where('status', 'scheduled');
    }

    /**
     * Scope for past classes.
     */
    public function scopePast($query)
    {
        return $query->where('scheduled_at', '<', now());
    }

    /**
     * Check if class is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->scheduled_at > now() && $this->status === 'scheduled';
    }

    /**
     * Check if class has recording.
     */
    public function hasRecording(): bool
    {
        return !empty($this->recording_link);
    }

    /**
     * Get end time.
     */
    public function getEndTimeAttribute()
    {
        return $this->scheduled_at->addMinutes($this->duration_minutes);
    }
}
