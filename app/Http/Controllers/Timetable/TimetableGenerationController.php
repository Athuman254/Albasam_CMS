<?php

namespace App\Http\Controllers\Timetable;

use App\Http\Controllers\Controller;
use App\Models\Settings\AcademicYear;
use App\Models\Timetable\TimetableVersion;
use App\Services\Timetable\TimetableGeneratorService;
use App\Services\Timetable\TimetableValidatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        // Fetch classes and teachers for the view modals
        $classes = \App\Models\Rank::with('stream')
            ->orderBy('name')
            ->get()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->full_name,
                ];
            });

        // Get teachers who have subject allocations (get distinct teacher IDs from allocations)
        $teacherIds = \App\Models\Timetable\TimetableSubjectAllocation::where('academic_year_id', $academicYearId)
            ->whereNotNull('teacher_id')
            ->distinct()
            ->pluck('teacher_id');

        $teachers = \App\Models\User::whereIn('id', $teacherIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Timetable/Generation/Generate', [
            'versions' => $versions,
            'academicYears' => $academicYears,
            'currentAcademicYearId' => $academicYearId,
            'classes' => $classes,
            'teachers' => $teachers,
        ]);
    }

    public function validateData(Request $request, TimetableValidatorService $validator)
    {
        $academicYearId = $request->input('academic_year_id');
        $issues = $validator->validate($academicYearId);

        return response()->json(['issues' => $issues]);
    }

    /**
     * Trigger timetable generation.
     */
    public function store(Request $request, TimetableGeneratorService $generator)
    {
        Log::info('Timetable Generation Started', $request->all());

        $validated = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'version_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            $version = $generator->generate(
                $validated['academic_year_id'],
                $validated['version_name'],
                $validated['description']
            );

            Log::info('Timetable Generation Successful', ['version_id' => $version->id]);

            $stats = $version->generation_stats;
            $message = 'Timetable generated successfully.';

            if (!empty($stats['conflicts'])) {
                $message = 'Timetable generated with conflicts. Please review the report.';
            }

            return back()->with([
                'success' => $message,
                'generation_result' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Timetable Generation Failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['error' => 'Generation failed: ' . $e->getMessage()]);
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

    /**
     * Export class timetable as PDF
     */
    public function exportClassPdf(TimetableVersion $version, $classId)
    {
        $pdfService = new \App\Services\Timetable\TimetablePdfService();
        return $pdfService->generateClassTimetable($version->id, $classId);
    }

    /**
     * Export teacher timetable as PDF
     */
    public function exportTeacherPdf(TimetableVersion $version, $teacherId)
    {
        $pdfService = new \App\Services\Timetable\TimetablePdfService();
        return $pdfService->generateTeacherTimetable($version->id, $teacherId);
    }

    /**
     * Export all classes timetable as PDF
     */
    public function exportAllClassesPdf(TimetableVersion $version)
    {
        $pdfService = new \App\Services\Timetable\TimetablePdfService();
        return $pdfService->generateAllClassesTimetable($version->id);
    }
}
