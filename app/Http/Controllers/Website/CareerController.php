<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\Website\CareerRequest;
use App\Http\Resources\Resource;
use App\Models\Website\Career;
use App\Services\HtmlPurifierService;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CareerController extends Controller
{
    public function datatable()
    {
        $careers = QueryBuilder::for(
            Career::with('user', 'contract_type')->orderByDesc('created_at')
        )->allowedFilters([
            AllowedFilter::exact('id'),
            AllowedFilter::exact('active'),
            AllowedFilter::partial('title'),
        ])->jsonPaginate();
        
        return Resource::collection($careers);
    }
    
    public function store(CareerRequest $request, HtmlPurifierService $purifier)
    {
        $user = auth()->user();
        $validated = $request->validated();
        
        $validated['slug'] = strtolower(str_replace(' ', '-', $validated['title']));
        $validated['job_description'] = $purifier->purify($validated['job_description']);
        
        if($validated['employment_type_id'] === '') {
            unset($validated['employment_type_id']);
        }
        
        Career::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'location' => $validated['location'] ?? null,
            'employment_type_id' => $validated['employment_type_id'] ?? null,
            'deadline_date' => $validated['deadline_date'] ?? null,
            'job_description' => $validated['job_description'],
            'active' => $validated['active'],
        ]);
        
        return back(303);
    }
    
    public function show($career)
    {
        $career = Career::where('slug', '=', $career)->firstOrFail();
        $career->load('user', 'contract_type');
        $otherCareers = Career::where('id', '!=', $career->id)->orderByDesc('created_at')->get();
        $previousCareer = Career::where('created_at', '<', $career->created_at)
            ->orderBy('created_at', 'desc')
            ->first();
        $nextCareer = Career::where('created_at', '>', $career->created_at)
            ->orderBy('created_at', 'asc')
            ->first();
        
        
        return view('website.template-1.career-details', [
            'career' => $career,
            'otherCareers' => $otherCareers,
            'previousCareer' => $previousCareer,
            'nextCareer' => $nextCareer,
        ]);
    }
    
    public function update(CareerRequest $request, Career $career)
    {
        $validated = $request->validated();
        $validated['slug'] = strtolower(str_replace(' ', '-', $validated['slug']));
        
        $career->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'location' => $validated['location'] ?? null,
            'employment_type_id' => $validated['employment_type_id'] ?? null,
            'deadline_date' => $validated['deadline_date'] ?? null,
            'job_description' => $validated['job_description'],
            'active' => $validated['active'],
        ]);
        
        return back(303);
    }
    
    public function destroy($careerId)
    {
        $career = Career::findOrFail($careerId);
        $career->delete();
        
        return redirect()->route('admin.components.index');
    }
}
