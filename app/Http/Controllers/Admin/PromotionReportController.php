<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudentPromotion;
use App\Models\Rank;
use App\Models\Settings\AcademicYear;
use App\Models\Institution;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Omaralalwi\Gpdf\Facade\Gpdf as GpdfFacade;
use Illuminate\Support\Facades\Log;

class PromotionReportController extends Controller
{
    /**
     * Display the promotion report page.
     */
    public function index()
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $classes = Rank::orderBy('name')->get();

        return Inertia::render('Admin/Reports/Promotions/Index', [
            'academicYears' => $academicYears,
            'classes' => $classes,
        ]);
    }

    /**
     * Get filtered promotion data for the datatable.
     */
    public function data(Request $request)
    {
        $query = StudentPromotion::with([
            'student',
            'fromClass',
            'toClass',
            'academicYear',
            'promotedBy'
        ]);

        if ($request->academic_year_id) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->from_class_id) {
            $query->where('from_class_id', $request->from_class_id);
        }

        if ($request->to_class_id) {
            $query->where('to_class_id', $request->to_class_id);
        }

        if ($request->has('special_promotion') && $request->special_promotion !== null) {
            $query->where('special_promotion', $request->special_promotion);
        }

        if ($request->start_date) {
            $query->whereDate('promoted_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('promoted_at', '<=', $request->end_date);
        }

        $promotions = $query->latest('promoted_at')->paginate($request->per_page ?? 15);

        return response()->json($promotions);
    }

    /**
     * Generate PDF report.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'academic_year_id' => 'nullable|exists:academic_years,id',
            'from_class_id' => 'nullable|exists:ranks,id',
            'to_class_id' => 'nullable|exists:ranks,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $institution = Institution::with('media')->first();

        $query = StudentPromotion::with([
            'student',
            'fromClass',
            'toClass',
            'academicYear',
            'promotedBy'
        ]);

        // Apply filters
        if ($request->academic_year_id) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->from_class_id) {
            $query->where('from_class_id', $request->from_class_id);
        }

        if ($request->to_class_id) {
            $query->where('to_class_id', $request->to_class_id);
        }

        if ($request->has('special_promotion') && $request->special_promotion !== null) {
            $query->where('special_promotion', $request->boolean('special_promotion'));
        }

        if ($request->start_date) {
            $query->whereDate('promoted_at', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('promoted_at', '<=', $request->end_date);
        }

        $promotions = $query->orderBy('promoted_at', 'desc')->get();

        // Group by class if no specific class selected, or just list
        $groupedPromotions = $promotions->groupBy(function ($item) {
            return $item->fromClass->name . ' -> ' . $item->toClass->name;
        });

        $logoBase64 = $this->getLogoBase64($institution);

        $html = view('admin.reports.promotions.pdf', [
            'institution' => $institution,
            'logoBase64' => $logoBase64,
            'promotions' => $promotions,
            'groupedPromotions' => $groupedPromotions,
            'filters' => [
                'academic_year' => $request->academic_year_id ? AcademicYear::find($request->academic_year_id)->name : 'All Years',
                'from_class' => $request->from_class_id ? Rank::find($request->from_class_id)->name : 'All Classes',
                'date_range' => ($request->start_date && $request->end_date) ? $request->start_date . ' to ' . $request->end_date : 'All Dates',
            ],
            'generatedAt' => now()->format('Y-m-d H:i:s'),
            'user' => auth()->user()->name,
        ])->render();

        $filename = 'promotion-report-' . now()->format('Y-m-d-His') . '.pdf';

        $pdfContent = GpdfFacade::generate($html);
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    /**
     * Get logo as base64 encoded string
     */
    private function getLogoBase64($institution)
    {
        if (!$institution) {
            return null;
        }

        $logoMedia = $institution->getFirstMedia('logo');
        if (!$logoMedia) {
            return null;
        }

        try {
            $logoPath = $logoMedia->getPath();
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoType = pathinfo($logoPath, PATHINFO_EXTENSION);
                return 'data:image/' . $logoType . ';base64,' . base64_encode($logoData);
            }
        } catch (\Exception $e) {
            Log::error('Error getting logo: ' . $e->getMessage());
        }

        return null;
    }
}
