<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'event_type',
        'start_date',
        'end_date',
        'all_day',
        'location',
        'color',
        'created_by',
        'target_audience',
        'rank_id',
        'is_recurring',
        'recurrence_pattern',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'all_day' => 'boolean',
        'is_recurring' => 'boolean',
        'recurrence_pattern' => 'array',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }

    public function attendees()
    {
        return $this->hasMany(EventAttendee::class);
    }

    public function notifications()
    {
        return $this->hasMany(EventNotification::class);
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeForClass($query, $rankId)
    {
        return $query->where(function ($q) use ($rankId) {
            $q->whereNull('rank_id')
                ->orWhere('rank_id', $rankId);
        });
    }

    public function scopeInDateRange($query, $start, $end)
    {
        return $query->whereBetween('start_date', [$start, $end])
            ->orWhereBetween('end_date', [$start, $end])
            ->orWhere(function ($q) use ($start, $end) {
                $q->where('start_date', '<', $start)
                    ->where('end_date', '>', $end);
            });
    }
}
