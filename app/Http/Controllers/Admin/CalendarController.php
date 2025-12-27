<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Rank;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class CalendarController extends Controller
{
    /**
     * Display the calendar view.
     */
    public function index()
    {
        $classes = Rank::where('activated', true)->get(['id', 'name']);

        return Inertia::render('Admin/Calendar/Index', [
            'initialEvents' => [], // Events will be fetched via API
            'classes' => $classes,
        ]);
    }

    /**
     * Fetch events for the calendar (API).
     */
    public function getEvents(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $query = Event::whereBetween('start_date', [$request->start, $request->end]);

        // Filter by permissions/role if needed (e.g., teachers only see their own or public events)
        // For now, admins see everything.

        $events = $query->get()->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_date->toIso8601String(),
                'end' => $event->end_date->toIso8601String(),
                'allDay' => $event->all_day,
                'backgroundColor' => $event->color ?? $this->getColorForType($event->event_type),
                'borderColor' => $event->color ?? $this->getColorForType($event->event_type),
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

    /**
     * Store a newly created event.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'required|in:holiday,exam,meeting,sports,academic,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'all_day' => 'boolean',
            'location' => 'nullable|string',
            'color' => 'nullable|string',
            'target_audience' => 'required|in:all,students,teachers,parents,specific_class',
            'rank_id' => 'nullable|required_if:target_audience,specific_class|exists:ranks,id',
        ]);

        $validated['created_by'] = Auth::id();

        Event::create($validated);

        return redirect()->back()->with('success', 'Event created successfully.');
    }

    /**
     * Update the specified event.
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'event_type' => 'required|in:holiday,exam,meeting,sports,academic,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'all_day' => 'boolean',
            'location' => 'nullable|string',
            'color' => 'nullable|string',
            'target_audience' => 'required|in:all,students,teachers,parents,specific_class',
            'rank_id' => 'nullable|required_if:target_audience,specific_class|exists:ranks,id',
        ]);

        $event->update($validated);

        return redirect()->back()->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified event.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->back()->with('success', 'Event deleted successfully.');
    }

    /**
     * Get default color for event type.
     */
    private function getColorForType($type)
    {
        $colors = [
            'holiday' => '#FF5B5C', // Red
            'exam' => '#5A8DEE', // Blue
            'meeting' => '#39DA8A', // Green
            'sports' => '#FDAC41', // Yellow
            'academic' => '#696CFF', // Purple
            'other' => '#82868B', // Gray
        ];

        return $colors[$type] ?? '#82868B';
    }
}
