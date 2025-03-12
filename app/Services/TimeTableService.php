<?php

namespace App\Services;

use App\Models\Lesson;

class TimeTableService
{
    public function generateCalendarData($weekDays): array
    {
        $calendarData = [];
        $timeRange = (new TimeService)->generateTimeRange(config('app.calendar.start_time'), config('app.calendar.end_time'));
        $lessons = Lesson::with('rank', 'teacher.honorific')
            ->calendarByRoleOrClassId()
            ->get();
        
        foreach ($timeRange as $time)
        {
            $timeText = $time['start'] . ' - ' . $time['end'];
            $calendarData[$timeText] = [];
            
            foreach ($weekDays as $index => $day)
            {
                $lesson = $lessons->where('weekday', $index)->where('rank_id', '=', 1)->where('start_time', $time['start'])->first();
                
//                dd($lesson);
                
                if ($lesson)
                {
                    $calendarData[$timeText][] = [
                        'class_name' => $lesson->rank->name,
                        'teacher_name' => $lesson->teacher->honorific->name . ' ' . $lesson->teacher->first_name . ' ' . $lesson->teacher->last_name,
                        'rowspan' => $lesson->difference / 30 ?? ''
                    ];
                }
                else if (!$lessons->where('weekday', $index)->where('start_time', '<', $time['start'])->where('end_time', '>=', $time['end'])->count())
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
