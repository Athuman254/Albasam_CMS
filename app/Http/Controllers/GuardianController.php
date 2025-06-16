<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuardianRequest;
use App\Http\Resources\Resource;
use App\Models\Guardian;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GuardianController extends Controller
{
    public function dataTable()
    {
        $guardians = QueryBuilder::for(
            Guardian::with('relationship')
        )->allowedFilters([
            AllowedFilter::exact('student_id'),
        ])->jsonPaginate();

        return Resource::collection($guardians);
    }

    public function index()
    {
        return Inertia::render('Admin/Guardians/Index', []);
    }
    
    public function store(GuardianRequest $request)
    {
        $validated = $request->validated();
        
        Guardian::create([
            'student_id' => $validated['student_id'],
            'relationship_id' => $validated['relationship_id'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'identification_number' => $validated['identification_number'],
            'profession' => $validated['profession'],
        ]);
        
        return back(303);
    }
    
    public function update(GuardianRequest $request, Guardian $guardian)
    {
        $validated = $request->validated();
        
        $guardian->update([
            'student_id' => $validated['student_id'],
            'relationship_id' => $validated['relationship_id'],
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'identification_number' => $validated['identification_number'],
            'profession' => $validated['profession'],
        ]);
        
        return back(303);
    }
    
    public function destroy(Guardian $guardian)
    {
        $guardian->delete();
        
        return back(303);
    }
}
