<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentCalendarController extends Controller
{
    public function index()
    {
        return Inertia::render('Student/Calendar/Index', [
            'initialEvents' => [], // Fetched via AJAX
        ]);
    }

    public function getEvents(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');

        // Get logged in student
        $student = auth()->guard('student')->user();

        if (!$student) {
            return response()->json([]);
        }

        $query = Event::query()
            ->whereBetween('start_date', [$start, $end])
            ->where(function ($q) use ($student) {
                // 1. All audience
                $q->where('target_audience', 'all')
                    // 2. Students audience
                    ->orWhere('target_audience', 'students')
                    // 3. Specific class
                    ->orWhere(function ($subQ) use ($student) {
                        $subQ->where('target_audience', 'specific_class')
                            ->where('rank_id', $student->rank_id);
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
