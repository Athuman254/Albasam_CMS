<?php

namespace App\Models\Timetable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimetableRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_name',
        'room_type',
        'capacity',
        'facilities',
        'building',
        'floor',
        'status',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'facilities' => 'array',
    ];

    /**
     * Get the allocations for this room.
     */
    public function allocations()
    {
        return $this->hasMany(TimetableAllocation::class, 'room_id');
    }

    /**
     * Get the constraints for this room.
     */
    public function constraints()
    {
        return $this->hasMany(TimetableConstraint::class, 'room_id');
    }

    /**
     * Scope to get only available rooms.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope to get rooms by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('room_type', $type);
    }

    /**
     * Check if room has a specific facility.
     */
    public function hasFacility(string $facility): bool
    {
        return in_array($facility, $this->facilities ?? []);
    }
}
