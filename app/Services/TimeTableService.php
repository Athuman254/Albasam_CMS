<?php

namespace App\Services;

use App\Models\Lesson;

class TimeTableService
{
    public function generateCalendarData($weekDays, $request): array
    {
        $rankId = $request->query('rank_id');
        $calendarData = [];
        $startTime = config('app.calendar.start_time');
        $endTime = config('app.calendar.end_time');
        $timeRange = (new TimeService)->generateTimeRange($startTime, $endTime);
        $lessons = Lesson::with('rank', 'teacher.honorific')->calendarByRoleOrClassId()->get();
        
        foreach ($timeRange as $time)
        {
            $timeText = $time['start'] . ' - ' . $time['end'];
            $calendarData[$timeText] = [];
            
            foreach ($weekDays as $index => $day)
            {
                $lesson = Lesson::with('rank', 'subject', 'teacher.honorific')->calendarByRoleOrClassId()->where('rank_id', '=', $rankId)->where('weekday', '=', $index)->where('start_time', '=', $time['start'])->first();
                
                if ($lesson)
                {
                    $calendarData[$timeText][] = [
                        'class_name' => $lesson->rank->name,
                        'subject_name' => $lesson->subject->name,
                        'teacher_name' => $lesson->teacher->honorific->name . ' ' . $lesson->teacher->first_name . ' ' . $lesson->teacher->last_name,
                        'rowspan' => abs($lesson->difference) / 40 ?? ''
                    ];
                }
                else if (!Lesson::calendarByRoleOrClassId()->where('rank_id', '=', $rankId)->where('weekday', $index)->where('start_time', '<', $time['start'])->where('end_time', '>=', $time['end'])->count())
                {
                    $calendarData[$timeText][] = 1;
                }
                else
                {
                    $calendarData[$timeText][] = 0;
                }
            }
        }
        
        return $calendarData;
    }
}
