<?php

namespace App\Services;

class GradingService
{
    /**
     * Get grade based on percentage following Kenyan KCSE system.
     * 
     * A  (12 pts): 80 - 100
     * A- (11 pts): 75 - 79
     * B+ (10 pts): 70 - 74
     * B  (9 pts):  65 - 69
     * B- (8 pts):  60 - 64
     * C+ (7 pts):  55 - 59
     * C  (6 pts):  50 - 54
     * C- (5 pts):  45 - 49
     * D+ (4 pts):  40 - 44
     * D  (3 pts):  35 - 39
     * D- (2 pts):  30 - 34
     * E  (1 pt):   0 - 29
     */
    public static function getGrade(float $percentage): string
    {
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
     * Get points based on grade.
     */
    public static function getPoints(string $grade): int
    {
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
}
