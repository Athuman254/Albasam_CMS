<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StaffAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'clock_in_time',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_out_time',
        'clock_out_latitude',
        'clock_out_longitude',
        'status',
        'is_late',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
        'clock_in_latitude' => 'decimal:8',
        'clock_in_longitude' => 'decimal:8',
        'clock_out_latitude' => 'decimal:8',
        'clock_out_longitude' => 'decimal:8',
        'is_late' => 'boolean',
    ];

    /**
     * Get the employee that owns the attendance
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get total hours worked
     */
    public function getTotalHoursAttribute()
    {
        if (!$this->clock_out_time) {
            return null;
        }

        $clockIn = Carbon::parse($this->clock_in_time);
        $clockOut = Carbon::parse($this->clock_out_time);

        return round($clockOut->diffInMinutes($clockIn) / 60, 2);
    }



    /**
     * Get formatted clock in time
     */
    public function getFormattedClockInAttribute()
    {
        return $this->clock_in_time ? $this->clock_in_time->format('h:i A') : null;
    }

    /**
     * Get formatted clock out time
     */
    public function getFormattedClockOutAttribute()
    {
        return $this->clock_out_time ? $this->clock_out_time->format('h:i A') : null;
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by employee
     */
    public function scopeForEmployee($query, $employeeId)
    {
        return $query->where('employee_id', $employeeId);
    }

    /**
     * Scope to filter by status
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
