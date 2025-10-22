<?php

namespace App\Http\Controllers\Exams;

use App\Models\Exam;
use App\Models\Rank;
use Inertia\Inertia;
use App\Models\Student;
use App\Models\ExamMark;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use App\Models\Institution;
use Omaralalwi\Gpdf\Facade\Gpdf as GpdfFacade;

class ExamResultController extends Controller
{
   /**
    * Display a listing of the resource.
    */
   public function index()
   {
      return Inertia::render('Exam/ExamResult/Index');
   }

   /**
    * Show the form for creating a new resource.
    */
   public function create()
   {
      //
   }

   /**
    * Store a newly created resource in storage.
    */
   public function store(Request $request)
   {
      //
   }

   /**
    * Display the specified resource.
    */
   public function show(string $id)
   {
      //
   }

   /**
    * Show the form for editing the specified resource.
    */
   public function edit(string $id) {}

   /**
    * Update the specified resource in storage.
    */
   public function update(Request $request, string $id)
   {
      //
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy(string $id)
   {
      //
   }


   public function generateStudentReport(Request $request, $studentId)
   {
      $institution = Institution::with('media')->first();

      $examId  = $request->get('exam_id');
      $classId = $request->get('class_id');
      $class   = Rank::with('teacher')->findOrFail($classId);
      $student = Student::findOrFail($studentId);
      $exam    = Exam::findOrFail($examId);

      $marks = ExamMark::with('examSubject.subject')
         ->where('student_id', $studentId)
         ->whereHas(
            'examSubject',
            fn($q) =>
            $q->where('exam_id', $examId)
               ->where('class_id', $classId)
         )
         ->get();

      $total = $marks->sum('marks_obtained');

      $allStudentsMarks = ExamMark::with('examSubject')
         ->whereHas(
            'examSubject',
            fn($q) =>
            $q->where('exam_id', $examId)
               ->where('class_id', $classId)
         )
         ->get()
         ->groupBy('student_id')
         ->map(fn($rows) => $rows->sum('marks_obtained'))
         ->sortDesc();

      $rank = $allStudentsMarks->keys()->search($studentId) + 1;

      $html = view('exams.reports.madrasa',
       [
         'institution' => $institution,
         'student' => $student,
         'exam' => $exam,
         'marks' => $marks,
         'total' => $total,
         'rank' => $rank,
         'class' => $class,
         'classSize' => $allStudentsMarks->count(),
      ]
      )->render();
      $pdfContent = GpdfFacade::generate($html);
      return response($pdfContent, 200, ['Content-Type' => 'application/pdf']);
      // $pdf = Pdf::setOptions([
      //    'isHtml5ParserEnabled' => true,
      //    'isRemoteEnabled' => true,
      //    'defaultFont' => 'amiri',
      // ])->loadView('exams.reports.madrasa', [
      //    'institution' => $institution,
      //    'student' => $student,
      //    'exam' => $exam,
      //    'marks' => $marks,
      //    'total' => $total,
      //    'rank' => $rank,
      //    'class' => $class,
      //    'classSize' => $allStudentsMarks->count(),
      // ]);
      // // dd($pdf);
      // return $pdf->download("exam-report-{$student->adm_no}.pdf");
   }
}
