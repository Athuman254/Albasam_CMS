<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\WorkHistory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class WorkHistoryController extends Controller
{
    public function dataTable()
    {
        $histories = QueryBuilder::for(
            WorkHistory::orderBy('id')
        )->allowedFilters([
            AllowedFilter::partial('institution_name'),
            AllowedFilter::exact('employee_id'),
        ])->jsonPaginate();

        return Resource::collection($histories);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => ['required', Rule::exists('employees', 'id')],
            'institution_name' => ['required'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);
        
        WorkHistory::create($validated);
        
        return back(303)->with('success', 'Work History created.');
    }
    
    public function update(Request $request, WorkHistory $workHistory)
    {
        $validatedData = $request->validate([
            'employee_id' => ['required', Rule::exists('employees', 'id')],
            'institution_name' => ['required'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);
        
        $workHistory->update($validatedData);
        
        return back(303)->with('success', 'Work History updated.');
    }
    
    public function destroy(WorkHistory $workHistory)
    {
        $workHistory->delete();
        
        return back(303)->with('success', 'Work History deleted.');
    }
}
