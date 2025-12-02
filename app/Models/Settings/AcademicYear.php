<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the display name (start year only) for the academic year.
     * Converts "2025-2026" to "2025"
     */
    public function getDisplayNameAttribute(): string
    {
        // Extract start year from "2025-2026" format
        $parts = explode('-', $this->name);
        return $parts[0] ?? $this->name;
    }

    /**
     * Get the currently active academic year
     */
    public static function current()
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Get all academic years
     */
    public static function getAll()
    {
        return static::all();
    }

    /**
     * Auto-generate academic years starting from current year
     * Creates years in format: 2025-2026, 2026-2027, etc.
     * 
     * @param int $yearsToGenerate Number of years to generate ahead
     * @return void
     */
    public static function autoGenerate(int $yearsToGenerate = 5)
    {
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');

        // If we're past July, start from next year (new academic year typically starts in August/September)
        $startYear = $currentMonth >= 7 ? $currentYear : $currentYear - 1;

        for ($i = 0; $i < $yearsToGenerate; $i++) {
            $year = $startYear + $i;
            $nextYear = $year + 1;
            $name = "{$year}-{$nextYear}";

            // Check if this academic year already exists
            $exists = static::where('name', $name)->exists();

            if (!$exists) {
                static::create([
                    'name' => $name,
                    'start_date' => "{$year}-08-01", // Typically starts in August
                    'end_date' => "{$nextYear}-07-31", // Ends in July next year
                    'is_active' => $i === 0 && !static::where('is_active', true)->exists(), // First one is active if no active year exists
                ]);
            }
        }
    }

    /**
     * Ensure academic years exist, auto-generate if needed
     * This should be called when the system needs academic years
     */
    public static function ensureYearsExist()
    {
        $count = static::count();

        // If no academic years exist, generate them
        if ($count === 0) {
            static::autoGenerate(5);
        }

        // If we have less than 3 years, generate more
        if ($count < 3) {
            static::autoGenerate(5);
        }
    }
}
