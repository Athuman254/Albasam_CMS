<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\JobTitle;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class JobTitleController extends Controller
{
    public function dataTable()
    {
        $titles = QueryBuilder::for(
            JobTitle::orderBy('name')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($titles);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('job_titles', 'name')],
            'activated' => ['required','boolean'],
        ]);
        
        JobTitle::create([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Job Title created.');
    }
    
    public function update(JobTitle $jobTitle, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('job_titles', 'name')->ignore($jobTitle->id)],
            'activated' => ['required','boolean'],
        ]);
        
        $jobTitle->update([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Job Title details updated.');
    }
}
