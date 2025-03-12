<?php

namespace App\Services;

use Carbon\Carbon;

class TimeService
{
    public function generateTimeRange($from, $to): array
    {
        $time = Carbon::parse($from);
        $timeRange = [];
        
        do {
            $timeRange[] = [
                'start' => $time->format("H:i"),
                'end' => $time->addMinutes(30)->format("H:i")
            ];
        } while ($time->format("H:i") !== $to);
        
        return $timeRange;
    }
}
