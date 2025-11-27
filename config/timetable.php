<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Timetable Workload Limits
    |--------------------------------------------------------------------------
    |
    | These limits define the maximum workload for teachers to prevent
    | overloading and ensure fair distribution of teaching responsibilities.
    |
    */
    'limits' => [
        'max_classes_per_teacher' => env('TIMETABLE_MAX_CLASSES', 5),
        'max_subjects_per_teacher' => env('TIMETABLE_MAX_SUBJECTS', 3),
        'max_hours_per_week' => env('TIMETABLE_MAX_HOURS', 30),
        'max_consecutive_periods' => env('TIMETABLE_MAX_CONSECUTIVE', 3),
        'min_break_between_classes' => env('TIMETABLE_MIN_BREAK', 0), // periods
    ],

    /*
    |--------------------------------------------------------------------------
    | Timetable Generation Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the automated timetable generation algorithm.
    |
    */
    'generation' => [
        'max_attempts' => env('TIMETABLE_MAX_ATTEMPTS', 5),
        'conflict_tolerance' => env('TIMETABLE_CONFLICT_TOLERANCE', 2), // acceptable conflicts
        'optimization_level' => env('TIMETABLE_OPTIMIZATION', 'balanced'), // fast, balanced, thorough
        'enable_room_allocation' => env('TIMETABLE_ENABLE_ROOMS', true),
        'enable_constraint_checking' => env('TIMETABLE_ENABLE_CONSTRAINTS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Period Configuration
    |--------------------------------------------------------------------------
    |
    | Default period durations in minutes.
    |
    */
    'periods' => [
        'default_lesson_duration' => 45, // minutes
        'short_break_duration' => 15, // minutes
        'lunch_duration' => 90, // minutes
        'assembly_duration' => 30, // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Days of Week
    |--------------------------------------------------------------------------
    |
    | Active school days for timetable generation.
    |
    */
    'days_of_week' => [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
    ],

    /*
    |--------------------------------------------------------------------------
    | Constraint Types
    |--------------------------------------------------------------------------
    |
    | Available constraint types for timetable scheduling.
    |
    */
    'constraint_types' => [
        'teacher_availability' => 'Teacher Availability',
        'room_availability' => 'Room Availability',
        'subject_preference' => 'Subject Preference',
        'class_capacity' => 'Class Capacity',
        'consecutive_periods' => 'Consecutive Periods',
        'no_double_booking' => 'No Double Booking',
    ],

    /*
    |--------------------------------------------------------------------------
    | Room Types
    |--------------------------------------------------------------------------
    |
    | Available room/facility types.
    |
    */
    'room_types' => [
        'classroom' => 'Classroom',
        'laboratory' => 'Laboratory',
        'library' => 'Library',
        'hall' => 'Hall',
        'sports' => 'Sports Facility',
        'other' => 'Other',
    ],

    /*
    |--------------------------------------------------------------------------
    | Export Formats
    |--------------------------------------------------------------------------
    |
    | Supported export formats for timetables.
    |
    */
    'export_formats' => [
        'pdf' => 'PDF Document',
        'excel' => 'Excel Spreadsheet',
        'csv' => 'CSV File',
        'ical' => 'iCalendar',
    ],
];
