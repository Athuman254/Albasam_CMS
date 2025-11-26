<?php

namespace App\Http\Controllers;

use App\Models\Rank;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Attendance;
use App\Http\Resources\Resource;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Requests\StoreAttendanceRequest;
use App\Http\Requests\UpdateAttendanceRequest;

class AttendanceController extends Controller
{
    public function dataTable()
    {
        $attendance =  QueryBuilder::for(
            Attendance::with(['student','teacher.employee', 'rank'])->orderBy('id', 'desc')
        )->allowedFilters([
            AllowedFilter::scope('search'),
            AllowedFilter::partial('date'),
            AllowedFilter::partial('rank_id'),
        ])->jsonPaginate();

        // dd($students);
        return Resource::collection($attendance);
    }
    
    public function index()
    {
        return Inertia::render("Admin/Attendance/Index");
    }

    public function records()
    {
        return Inertia::render("Admin/Attendance/Report");
    }
    
    public function create()
    {
        //
    }
    
    public function store(StoreAttendanceRequest $request)
    {
        $validated = $request->validated();
        $rank = Rank::findOrFail($validated['rank_id']);
        $teacher = Teacher::find($rank->teacher_id) ?? null;
        
        DB::beginTransaction();
        
        try {
            foreach ($validated['attendances'] as $attendance)  {
                Attendance::updateOrCreate(
                    [
                        'student_id' => $attendance['student_id'],
                        'rank_id' => $validated['rank_id'],
                        'date' => $validated['date'],
                    ],
                    [
                        'teacher_id' => $teacher ? $teacher->id : 1,
                        'status' => $attendance['status'],
                        'remarks' => $attendance['remarks'] ?? null,
                    ]
                );
            }
            DB::commit();
            return back(303)->with('Attendance record created.');
            
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Error: ' . $exception->getMessage());
            report($exception);
            return redirect()->back()->withInput()->withErrors(['message' => $exception->getMessage()]);
        }
    }
    
    public function fetchForDate(Request $request)
    {
        $data = $request->validate([
            'rank_id' => 'required|exists:ranks,id',
            'date' => 'required|date',
        ]);
        
        $attendances = Attendance::where('rank_id', $data['rank_id'])
            ->where('date', $data['date'])
            ->get();
        
        return response()->json([
            'attendances' => $attendances,
        ]);
    }
}
