<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\EmploymentType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmploymentTypeController extends Controller
{
    public function dataTable()
    {
        $types = QueryBuilder::for(
            EmploymentType::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($types);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('employment_types', 'name')],
            'activated' => ['required','boolean'],
        ]);
        
        EmploymentType::create([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Employment type created.');
    }
    
    public function update(EmploymentType $employmentType, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('employment_types', 'name')->ignore($employmentType->id)],
            'activated' => ['required','boolean'],
        ]);
        
        $employmentType->update([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Employment type details updated.');
    }
}
