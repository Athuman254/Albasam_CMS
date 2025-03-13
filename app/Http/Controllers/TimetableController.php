<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Services\TimeTableService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TimetableController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/TimeTable/Index', []);
    }
    
    public function timeTableData(Request $request, TimeTableService $timeTableService)
    {
        $weekDays = Lesson::WEEK_DAYS;
        
        return $timeTableService->generateCalendarData($weekDays, $request);
    }
}
