<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\QualificationType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class QualificationTypeController extends Controller
{
    public function dataTable()
    {
        $types = QueryBuilder::for(
            QualificationType::orderBy('id')
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
            'name' => ['required', 'max:255', Rule::unique('qualification_types', 'name')],
            'activated' => ['required','boolean'],
        ]);
        
        QualificationType::create([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Qualification type created.');
    }
    
    public function update(QualificationType $qualificationType, Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('qualification_types', 'name')->ignore($qualificationType->id)],
            'activated' => ['required','boolean'],
        ]);
        
        $qualificationType->update([
            'name' => $validated['name'],
            'activated' => $validated['activated'],
        ]);
        
        return back(303)->with('success', 'Qualification type details updated.');
    }
}
