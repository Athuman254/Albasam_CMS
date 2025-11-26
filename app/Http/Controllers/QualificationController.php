<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\Qualification;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class QualificationController extends Controller
{
    public function dataTable()
    {
        $qualifications = QueryBuilder::for(
            Qualification::with('qualification_type')->orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('employee_id'),
        ])->jsonPaginate();

        return Resource::collection($qualifications);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', Rule::exists('employees', 'id')],
            'qualification_type_id' => ['required', Rule::exists('qualification_types', 'id')],
            'institution_name' => ['required'],
            'course_name' => ['nullable'],
            'year_of_completion' => ['required', 'string'],
        ]);
        
        Qualification::create($validated);
        
        return back(303)->with('success', 'Emergency Contact created.');
    }
    
    public function update(Request $request, Qualification $qualification)
    {
        $validatedData = $request->validate([
            'employee_id' => ['required', Rule::exists('employees', 'id')],
            'qualification_type_id' => ['required', Rule::exists('qualification_types', 'id')],
            'institution_name' => ['required'],
            'course_name' => ['nullable'],
            'year_of_completion' => ['required', 'string'],
        ]);
        
        $qualification->update($validatedData);
        
        return back(303)->with('success', 'Qualification updated.');
    }
    
    public function destroy(Qualification $qualification)
    {
        $qualification->delete();
        
        return back(303)->with('success', 'Qualification deleted.');
    }
}
