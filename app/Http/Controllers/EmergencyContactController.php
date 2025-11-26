<?php

namespace App\Http\Controllers;

use App\Http\Resources\Resource;
use App\Models\EmergencyContact;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class EmergencyContactController extends Controller
{
    public function dataTable()
    {
        $emergencyContacts = QueryBuilder::for(
            EmergencyContact::with('relationship')->orderBy('id')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('employee_id'),
        ])->jsonPaginate();

        return Resource::collection($emergencyContacts);
    }
    
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'employee_id' => ['required', Rule::exists('employees', 'id')],
            'relationship_id' => ['required', Rule::exists('relationships', 'id')],
            'name' => ['required'],
            'email' => ['nullable', 'email'],
            'phone' => ['required', 'string'],
        ]);
        
        $emergencyContact = EmergencyContact::create($validatedData);
        
        return back(303)->with('success', 'Emergency Contact created.');
    }
    
    public function update(Request $request, EmergencyContact $emergencyContact)
    {
        $validatedData = $request->validate([
            'employee_id' => ['required', Rule::exists('employees', 'id')],
            'relationship_id' => ['required', Rule::exists('relationships', 'id')],
            'name' => ['required'],
            'email' => ['nullable', 'email'],
            'phone' => ['required', 'string'],
        ]);
        
        $emergencyContact->update($validatedData);
        
        return back(303)->with('success', 'Emergency Contact updated.');
    }
    
    public function destroy(EmergencyContact $emergencyContact)
    {
        $emergencyContact->delete();
        
        return back(303)->with('success', 'Emergency Contact deleted.');
    }
}
