<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeCalendarController extends Controller
{
    public function index()
    {
        return Inertia::render('Employee/Calendar/Index', [
            'initialEvents' => [], // Fetched via AJAX
        ]);
    }

    public function getEvents(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');

        $employee = auth()->guard('employee')->user();
        if (!$employee) {
            return response()->json([]);
        }

        // 1. Get classes via employee_class relationship
        $classIdsFromEmpClass = $employee->classes()->pluck('ranks.id')->toArray();

        // 2. Get classes where they are class teachers (via ranks table)
        // Note: employee->teacher is a HasOne relationship
        $teacherRecord = $employee->teacher;
        $classIdsFromRanks = [];
        if ($teacherRecord) {
            $classIdsFromRanks = \App\Models\Rank::where('teacher_id', $teacherRecord->id)->pluck('id')->toArray();
        }

        // Combine all assigned class IDs
        $assignedClassIds = array_unique(array_merge($classIdsFromEmpClass, $classIdsFromRanks));

        $query = Event::query()
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($sub) use ($start, $end) {
                        $sub->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->where(function ($q) use ($assignedClassIds) {
                // Teachers should see:
                // - General events (all, teachers)
                // - Student events (all teachers need to stay informed)
                // - Specific class events for classes they are assigned to
                $q->whereIn('target_audience', ['all', 'teachers', 'students'])
                    ->orWhere(function ($subQ) use ($assignedClassIds) {
                        $subQ->where('target_audience', 'specific_class');
                        if (!empty($assignedClassIds)) {
                            $subQ->whereIn('rank_id', $assignedClassIds);
                        } else {
                            // If no assigned classes found, show all class events to teachers for now
                            // to ensure they don't miss important school meetings.
                        }
                    });
            });

        $events = $query->get()->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_date->toIso8601String(),
                'end' => $event->end_date->toIso8601String(),
                'allDay' => $event->all_day,
                'backgroundColor' => $event->color,
                'borderColor' => $event->color,
                'extendedProps' => [
                    'description' => $event->description,
                    'event_type' => $event->event_type,
                    'location' => $event->location,
                    'target_audience' => $event->target_audience,
                    'rank_id' => $event->rank_id,
                ]
            ];
        });

        return response()->json($events);
    }
}
