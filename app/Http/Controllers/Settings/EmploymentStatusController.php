<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\EmploymentStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmploymentStatusController extends Controller
{
    public function dataTable()
    {
        $statuses = QueryBuilder::for(
            EmploymentStatus::orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($statuses);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('employment_statuses', 'name')],
            'activated' => ['required','boolean'],
        ]);
        
        EmploymentStatus::create([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Employment status created.');
    }
    
    public function update(EmploymentStatus $employmentStatus, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('employment_statuses', 'name')->ignore($employmentStatus->id)],
            'activated' => ['required','boolean'],
        ]);
        
        $employmentStatus->update([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Employment status details updated.');
    }
}
