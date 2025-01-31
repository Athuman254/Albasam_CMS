<?php

namespace App\Http\Controllers;

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
            AllowedFilter::partial('class_id'),
        ])->jsonPaginate();

        // dd($students);
        return Resource::collection($attendance);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        // return Inertia::render("Website/SiteSettings");
        return Inertia::render("Attendance/Index");
    }

    public function records(){
        return Inertia::render("Attendance/Records");
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttendanceRequest $request)
    {
        // $validated = $request->validate([
        //     '*.teacher_id' => 'required|exists:users,id',
        //     '*.student_id' => 'required|exists:students,id',
        //     '*.class_id' => 'required|exists:classes,id',
        //     '*.date' => 'required|date',
        //     '*.status' => 'required|in:Present,Absent,Late,Excused',
        //     '*.remarks' => 'nullable|string|max:255',
        // ]);

        $validated = $request->validated();
        // dd($validated);
        foreach ($validated as $record) {
            Attendance::updateOrCreate(
                [
                    'student_id' => $record['id'],
                    'class_id' => $record['class_id'],
                    'date' => $record['date'],
                ],
                [
                    'teacher_id' => $record['teacher_id'],
                    'status' => $record['status'],
                    'remarks' => $record['remarks'],
                ]
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Attendance $attendance)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Attendance $attendance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttendanceRequest $request, Attendance $attendance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Attendance $attendance)
    {
        //
    }
}
