<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\SalaryScale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SalaryScaleController extends Controller
{
    public function dataTable()
    {
        $scales = QueryBuilder::for(
            SalaryScale::with('grade')->orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($scales);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('salary_scales', 'name')],
            'salary_grade_id' => ['nullable', Rule::exists('salary_grades', 'id')],
            'activated' => ['required','boolean'],
        ]);
        
        SalaryScale::create([
            'name' => $validated['name'],
            'salary_grade_id' => $validated['salary_grade_id'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Scale created.');
    }
    
    public function update(SalaryScale $salaryScale, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('salary_scales', 'name')->ignore($salaryScale->id)],
            'salary_grade_id' => ['nullable', Rule::exists('salary_grades', 'id')],
            'activated' => ['required','boolean'],
        ]);
        
        $salaryScale->update([
            'name' => $validated['name'],
            'salary_grade_id' => $validated['salary_grade_id'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Salary scale details updated.');
    }
}
