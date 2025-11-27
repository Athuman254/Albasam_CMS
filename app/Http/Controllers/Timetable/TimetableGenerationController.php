<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Models\Settings\AcademicYear;
use App\Models\Timetable\TimetableVersion;
use App\Services\Timetable\TimetableGeneratorService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TimetableGenerationController extends Controller
{
    protected $generatorService;

    public function __construct(TimetableGeneratorService $generatorService)
    {
        $this->generatorService = $generatorService;
    }

    /**
     * Display the generation page.
     */
    public function index(Request $request)
    {
        $academicYearId = $request->input('academic_year_id', AcademicYear::where('is_active', true)->first()?->id);

        $versions = TimetableVersion::where('academic_year_id', $academicYearId)
            ->with('creator')
            ->orderBy('created_at', 'desc')
            ->get();

        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return Inertia::render('Timetable/Generation/Generate', [
            'versions' => $versions,
            'academicYears' => $academicYears,
            'currentAcademicYearId' => $academicYearId,
        ]);
    }

    /**
     * Trigger timetable generation.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'version_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $version = $this->generatorService->generate(
                $validated['academic_year_id'],
                $validated['version_name'],
                $validated['description']
            );

            return back()->with('success', "Timetable '{$version->version_name}' generated successfully. Scheduled: {$version->generation_stats['scheduled']}/{$version->generation_stats['total_allocations']}");
        } catch (\Exception $e) {
            return back()->with('error', 'Generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Publish a version.
     */
    public function publish(TimetableVersion $version)
    {
        $version->publish();
        $version->activate();

        return back()->with('success', 'Timetable version published and activated.');
    }

    /**
     * Delete a version.
     */
    public function destroy(TimetableVersion $version)
    {
        $version->delete();
        return back()->with('success', 'Timetable version deleted.');
    }
}
