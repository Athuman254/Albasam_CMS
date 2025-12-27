<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Rank;
use App\Models\Institution;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentIdCardController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query()
            ->with(['currentRank', 'gender'])
            ->when($request->class_id, function ($q) use ($request) {
                $q->where('rank_id', $request->class_id);
            })
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sq) use ($request) {
                    $sq->where('first_name', 'like', "%{$request->search}%")
                        ->orWhere('last_name', 'like', "%{$request->search}%")
                        ->orWhere('admission_number', 'like', "%{$request->search}%");
                });
            });

        $students = $query->paginate(20)->withQueryString();
        $classes = Rank::all();

        return Inertia::render('Admin/StudentId/Index', [
            'students' => $students,
            'classes' => $classes,
            'filters' => $request->only(['class_id', 'search']),
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id'
        ]);

        $students = Student::whereIn('id', $request->student_ids)
            ->with(['currentRank', 'gender'])
            ->get();

        $institution = Institution::first();
        $currentYear = now()->year;

        // Process students to add expiration year
        $students->transform(function ($student) use ($currentYear) {
            $grade = strtoupper($student->currentRank?->name ?? '');
            $expiryYear = $currentYear + 1; // Default fallback

            if (str_contains($grade, 'PP')) {
                // Pre-primary usually 2 years
                $expiryYear = $currentYear + (str_contains($grade, 'PP1') ? 2 : 1);
            } elseif (str_contains($grade, 'GRADE')) {
                preg_match('/GRADE\s*(\d+)/i', $grade, $matches);
                if (isset($matches[1])) {
                    $level = (int)$matches[1];
                    if ($level <= 6) {
                        $expiryYear = $currentYear + (6 - $level);
                    } elseif ($level <= 9) {
                        $expiryYear = $currentYear + (9 - $level);
                    } else {
                        $expiryYear = $currentYear + (12 - $level);
                    }
                }
            }

            // Ensure expiry is at least current year
            $student->expiry_year = max($currentYear, $expiryYear);
            return $student;
        });

        $pdf = PDF::loadView('admin.id-cards.student', [
            'students' => $students,
            'institution' => $institution,
            'generated_at' => now()->format('d/m/Y'),
        ]);

        // ID card size - CR80 is 85.60 x 53.98 mm
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('student_id_cards.pdf');
    }
}
