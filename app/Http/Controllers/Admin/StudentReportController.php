<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PDF;

class StudentReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query()
            ->with(['currentRank', 'gender', 'admission'])
            ->when($request->class_id, function ($q) use ($request) {
                $q->where('rank_id', $request->class_id);
            })
            ->when($request->gender_id, function ($q) use ($request) {
                $q->where('gender_id', $request->gender_id);
            });

        $perPage = $request->input('per_page', 10);
        $students = $query->paginate($perPage)->withQueryString();
        $classes = \App\Models\Rank::all();
        $genders = \App\Models\Gender::all();

        return inertia('Admin/Reports/AllStudents', [
            'students' => $students,
            'classes' => $classes,
            'genders' => $genders,
            'filters' => $request->only(['class_id', 'gender_id', 'per_page']),
        ]);
    }

    public function export(Request $request)
    {
        $query = Student::query()
            ->with(['currentRank', 'gender', 'admission'])
            ->when($request->class_id, function ($q) use ($request) {
                $q->where('rank_id', $request->class_id);
            })
            ->when($request->gender_id, function ($q) use ($request) {
                $q->where('gender_id', $request->gender_id);
            });

        $students = $query->get();
        $className = $request->class_id ? \App\Models\Rank::find($request->class_id)->name : 'All Classes';
        $genderName = $request->gender_id ? \App\Models\Gender::find($request->gender_id)->name : 'All Genders';

        $pdf = PDF::loadView('reports.all-students', [
            'students' => $students,
            'className' => $className,
            'genderName' => $genderName,
            'generated_at' => now()->format('d/m/Y H:i'),
        ]);

        return $pdf->stream('all_students_report.pdf');
    }
}
