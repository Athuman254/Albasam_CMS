<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\MaritalStatus;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class MaritalStatusController extends Controller
{
    public function dataTable()
    {
        $maritalStatuses = QueryBuilder::for(
            MaritalStatus::orderBy('name')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('activated'),
            AllowedFilter::partial('name'),
        ])->jsonPaginate();

        return Resource::collection($maritalStatuses);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('marital_statuses', 'name')],
            'activated' => ['required','boolean'],
        ]);
        
        MaritalStatus::create([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Marital Status created.');
    }
    
    public function update(MaritalStatus $maritalStatus, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('marital_statuses', 'name')->ignore($maritalStatus->id)],
            'activated' => ['required','boolean'],
        ]);
        
        $maritalStatus->update([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Marital status details updated.');
    }
}
