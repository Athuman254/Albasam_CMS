<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SubjectController extends Controller
{
    public function dataTable()
    {
        $subjects = QueryBuilder::for(
            Subject::orderBy('name')
        )->allowedFilters([
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();
        
        return Resource::collection($subjects);
    }
    
    public function index()
    {
        return Inertia::render('Admin/Configurations/Subject/Index', []);
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
        
        return to_route('subjects.index')->with('success', 'Subject created.');
    }
    
    public function update(Subject $subject, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255'],
            'code' => ['nullable', 'max:255'],
            'group' => ['nullable'],
            'activated' => ['required', 'boolean'],
        ]);
        
        $subject->update([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'group' => $validated['group'],
            'activated' => $validated['activated'],
        ]);
        
        return to_route('subjects.index')->with('success', 'Subject updated.');
    }
}
