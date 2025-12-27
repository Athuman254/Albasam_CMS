<?php

namespace App\Services;

use App\Models\GradingScale;
use App\Models\GradingEntry;

class GradingService
{
    /**
     * Get grade based on percentage using dynamic scales or default KCSE system.
     */
    public static function getGrade(float $percentage, ?int $gradingScaleId = null): string
    {
        $entry = self::getMatchingEntry($percentage, $gradingScaleId);

        if ($entry) {
            return $entry->grade;
        }

        // Hardcoded Kenyan KCSE system fallback if no scale/entry found
        return match (true) {
            $percentage >= 80 => 'A',
            $percentage >= 75 => 'A-',
            $percentage >= 70 => 'B+',
            $percentage >= 65 => 'B',
            $percentage >= 60 => 'B-',
            $percentage >= 55 => 'C+',
            $percentage >= 50 => 'C',
            $percentage >= 45 => 'C-',
            $percentage >= 40 => 'D+',
            $percentage >= 35 => 'D',
            $percentage >= 30 => 'D-',
            default => 'E',
        };
    }

    /**
     * Get points based on grade and scale.
     */
    public static function getPoints(string $grade, ?int $gradingScaleId = null): int
    {
        if ($gradingScaleId) {
            $entry = GradingEntry::where('grading_scale_id', $gradingScaleId)
                ->where('grade', $grade)
                ->first();
            if ($entry) {
                return $entry->points;
            }
        }

        $defaultScale = GradingScale::where('is_default', true)->first();
        if ($defaultScale) {
            $entry = GradingEntry::where('grading_scale_id', $defaultScale->id)
                ->where('grade', $grade)
                ->first();
            if ($entry) {
                return $entry->points;
            }
        }

        // Hardcoded KCSE points fallback
        return match (strtoupper($grade)) {
            'A' => 12,
            'A-' => 11,
            'B+' => 10,
            'B' => 9,
            'B-' => 8,
            'C+' => 7,
            'C' => 6,
            'C-' => 5,
            'D+' => 4,
            'D' => 3,
            'D-' => 2,
            'E' => 1,
            default => 0,
        };
    }

    /**
     * Get automated remarks based on percentage and scale.
     */
    public static function getRemarks(float $percentage, ?int $gradingScaleId = null): string
    {
        $entry = self::getMatchingEntry($percentage, $gradingScaleId);

        if ($entry && $entry->remarks) {
            return $entry->remarks;
        }

        // Default remarks fallback
        return match (true) {
            $percentage >= 80 => 'Exceeded Expectations',
            $percentage >= 70 => 'Met Expectations',
            $percentage >= 60 => 'Approached Expectations',
            $percentage >= 40 => 'Satisfactory',
            default => 'Below Expectations',
        };
    }

    /**
     * Helper to find matching entry.
     */
    private static function getMatchingEntry(float $percentage, ?int $gradingScaleId = null): ?GradingEntry
    {
        if ($gradingScaleId) {
            $entry = GradingEntry::where('grading_scale_id', $gradingScaleId)
                ->where('min_score', '<=', $percentage)
                ->where('max_score', '>=', $percentage)
                ->first();
            if ($entry) return $entry;
        }

        $defaultScale = GradingScale::where('is_default', true)->active()->first();
        if ($defaultScale) {
            $entry = GradingEntry::where('grading_scale_id', $defaultScale->id)
                ->where('min_score', '<=', $percentage)
                ->where('max_score', '>=', $percentage)
                ->first();
            if ($entry) return $entry;
        }

        return null;
    }
}
