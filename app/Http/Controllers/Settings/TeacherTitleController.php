<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\SalaryGrade;
use App\Models\SalaryScale;
use App\Models\TeacherTitle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TeacherTitleController extends Controller
{
    public function dataTable()
    {
        $titles = QueryBuilder::for(
            TeacherTitle::with('scale', 'grade')->orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('title'),
        ])->jsonPaginate();

        return Resource::collection($titles);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'max:255', Rule::unique('teacher_titles', 'title')],
            'salary_scale_id' => ['nullable', Rule::exists('salary_scales', 'id')],
            'activated' => ['required','boolean'],
        ]);
        
        $scale = SalaryScale::find($validated['salary_scale_id']);
        $grade = SalaryGrade::where('id', '=', $scale->salary_grade_id)->first() ?? null;
        
        TeacherTitle::create([
            'title' => $validated['title'],
            'salary_scale_id' => $validated['salary_scale_id'],
            'salary_grade_id' => $grade->id ?? null,
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Teacher title created.');
    }
    
    public function update(TeacherTitle $teacherTitle, Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'max:255', Rule::unique('teacher_titles', 'title')->ignore($teacherTitle->id)],
            'salary_scale_id' => ['nullable', Rule::exists('salary_scales', 'id')],
            'activated' => ['required','boolean'],
        ]);
        
        $scale = SalaryScale::find($validated['salary_scale_id']);
        $grade = SalaryGrade::where('id', '=', $scale->salary_grade_id)->first() ?? null;
        
        $teacherTitle->update([
            'title' => $validated['title'],
            'salary_scale_id' => $validated['salary_scale_id'],
            'salary_grade_id' => $grade->id ?? null,
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Teacher title details updated.');
    }
}
