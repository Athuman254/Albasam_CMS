<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SubjectController extends Controller
{
    public function dataTable()
    {
        $subjects = QueryBuilder::for(
            Subject::orderBy('name')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();
        
        return Resource::collection($subjects);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','max:255', Rule::unique('subjects', 'name')],
            'code' => ['nullable', 'max:255', Rule::unique('subjects', 'code')],
            'group' => ['nullable'],
            'activated' => ['required','boolean'],
        ]);
        
        Subject::create([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'group' => $validated['group'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Subject created.');
    }
    
    public function update(Subject $subject, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required','max:255', Rule::unique('subjects', 'name')->ignore($subject->id)],
            'code' => ['nullable', 'max:255', Rule::unique('subjects', 'code')->ignore($subject->id)],
            'group' => ['nullable'],
            'activated' => ['required', 'boolean'],
        ]);
        
        $subject->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'group' => $validated['group'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Subject updated.');
    }
}
