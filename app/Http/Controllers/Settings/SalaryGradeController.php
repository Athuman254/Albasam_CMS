<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\SalaryGrade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SalaryGradeController extends Controller
{
    public function dataTable()
    {
        $salaryGrades = QueryBuilder::for(
            SalaryGrade::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($salaryGrades);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('salary_grades', 'name')],
            'activated' => ['required','boolean'],
        ]);
        
        SalaryGrade::create([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Salary grade created.');
    }
    
    public function update(SalaryGrade $salaryGrade, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('salary_grades', 'name')->ignore($salaryGrade->id)],
            'activated' => ['required','boolean'],
        ]);
        
        $salaryGrade->update([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Salary grade details updated.');
    }
}
