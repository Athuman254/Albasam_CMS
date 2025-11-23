<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Resources\Resource;
use App\Models\Subject;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Inertia\Inertia; // Add this import

class SubjectController extends Controller
{
    public function dataTable()
    {
        $subjects = QueryBuilder::for(
            Subject::withCount('skills')->orderBy('name')
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

    /**
     * Get skills for a specific subject
     */
    public function getSkills(Subject $subject)
    {
        $skills = $subject->skills()
            ->when(request()->has('is_active'), function($query) {
                $query->where('is_active', request('is_active'));
            })
            ->orderBy('name')
            ->get();

        return response()->json($skills); // This is fine for API calls
    }

    /**
     * Store a new skill for a subject
     */
    public function storeSkill(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255',
                Rule::unique('skills')->where(function ($query) use ($subject) {
                    return $query->where('subject_id', $subject->id);
                })
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $skill = $subject->skills()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Return a redirect instead of JSON for Inertia
        return back()->with([
            'success' => 'Skill created successfully',
            'skill' => $skill // Optional: if you need to access it in the frontend
        ]);
    }

    /**
     * Update a specific skill
     */
    public function updateSkill(Request $request, Subject $subject, Skill $skill)
    {
        // Verify the skill belongs to the subject
        if ($skill->subject_id !== $subject->id) {
            return back()->with('error', 'Skill does not belong to this subject');
        }

        $validated = $request->validate([
            'name' => [
                'required', 
                'string', 
                'max:255',
                Rule::unique('skills')->where(function ($query) use ($subject, $skill) {
                    return $query->where('subject_id', $subject->id);
                })->ignore($skill->id)
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $skill->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'],
        ]);

        return back()->with('success', 'Skill updated successfully');
    }

    /**
     * Delete a specific skill
     */
    public function destroySkill(Subject $subject, Skill $skill)
    {
        // Verify the skill belongs to the subject
        if ($skill->subject_id !== $subject->id) {
            return back()->with('error', 'Skill does not belong to this subject');
        }

        // Check if skill is used in any exams before deleting
        if ($skill->exams()->count() > 0) {
            return back()->with('error', 'Cannot delete skill. It is being used in exams.');
        }

        $skill->delete();

        return back()->with('success', 'Skill deleted successfully');
    }

    /**
     * Get all skills across all subjects (for exam management)
     */
    public function getAllSkills(Request $request)
    {
        $skills = QueryBuilder::for(Skill::with('subject'))
            ->allowedFilters([
                AllowedFilter::exact('subject_id'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::partial('name'),
            ])
            ->allowedSorts(['name', 'created_at'])
            ->when($request->has('subject_id'), function($query) use ($request) {
                $query->where('subject_id', $request->subject_id);
            })
            ->jsonPaginate();

        return Resource::collection($skills);
    }

    /**
     * Bulk update skills status
     */
    public function bulkUpdateSkills(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'skill_ids' => 'required|array',
            'skill_ids.*' => 'exists:skills,id',
            'is_active' => 'required|boolean',
        ]);

        // Verify all skills belong to the subject
        $invalidSkills = Skill::whereIn('id', $validated['skill_ids'])
            ->where('subject_id', '!=', $subject->id)
            ->exists();

        if ($invalidSkills) {
            return back()->with('error', 'Some skills do not belong to this subject');
        }

        Skill::whereIn('id', $validated['skill_ids'])->update([
            'is_active' => $validated['is_active']
        ]);

        return back()->with('success', 'Skills updated successfully');
    }
}