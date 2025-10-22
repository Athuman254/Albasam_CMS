<?php

namespace App\Http\Controllers\Exams;

use Inertia\Inertia;
use App\Models\ExamMark;
use App\Models\ExamSubject;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\AllowedFilter;

class UploadExamResultController extends Controller
{

   public function examMarks()
   {
      $marks = \Spatie\QueryBuilder\QueryBuilder::for(
         ExamMark::orderBy('id')
      )
         ->allowedFilters([
            AllowedFilter::exact('student_id'),
            AllowedFilter::partial('name'),

            AllowedFilter::callback('exam_id', function ($query, $value) {
               $query->whereHas('examSubject', function ($q) use ($value) {
                  $q->where('exam_id', $value);
               });
            }),

            AllowedFilter::callback('class_id', function ($query, $value) {
               $query->whereHas('examSubject', function ($q) use ($value) {
                  $q->where('class_id', $value);
               });
            }),
         ])
         ->jsonPaginate();


      return Resource::collection($marks);
   }
   /**
    * Display a listing of the resource.
    */
   public function index()
   {
      return Inertia::render('Exam/UploadExamResult/Index');
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
      $validated = $request->validate([
         'exam_id'  => 'required|exists:exams,id',
         'class_id' => 'required|integer',
         'marks'    => 'required|array',
         'marks.*'  => 'array',
      ]);

      $examId  = $validated['exam_id'];
      $classId = $validated['class_id'];
      $marks   = $validated['marks'];

      // Load exam_subjects for this exam + class
      $examSubjects = ExamSubject::where('exam_id', $examId)
         ->where('class_id', $classId)
         ->get()
         ->keyBy('id');

      $errors = [];
      $toSave = [];

      foreach ($marks as $studentId => $subjectMarks) {
         foreach ($subjectMarks as $examSubjectId => $scoreRaw) {
            if (! isset($examSubjects[$examSubjectId])) {
               $errors[] = [
                  'student_id' => (int)$studentId,
                  'subject_id' => (int)$examSubjectId,
                  'message'    => "Invalid subject for this exam/class.",
               ];
               continue;
            }

            if ($scoreRaw === '' || $scoreRaw === null) {
               // Skip empty marks
               continue;
            }

            if (! is_numeric($scoreRaw)) {
               $errors[] = [
                  'student_id' => (int)$studentId,
                  'subject_id' => (int)$examSubjectId,
                  'message'    => "Mark must be a number.",
               ];
               continue;
            }

            $score = (float) $scoreRaw;
            $max   = $examSubjects[$examSubjectId]->max_marks;

            if ($score < 0 || $score > $max) {
               $errors[] = [
                  'student_id' => (int)$studentId,
                  'subject_id' => (int)$examSubjectId,
                  'message'    => "Mark must be between 0 and {$max}.",
               ];
               continue;
            }

            $toSave[] = [
               'subject_id' => $examSubjectId,
               'student_id'      => $studentId,
               'marks_obtained'  => $score,
            ];
         }
      }

      if (! empty($errors)) {
         return response()->json([
            'status'  => 'error',
            'message' => 'Some marks were invalid',
            'errors'  => $errors,
         ], 422);
      }

      DB::transaction(function () use ($toSave) {
         foreach ($toSave as $row) {
            ExamMark::updateOrCreate(
               [
                  'exam_subject_id' => $row['subject_id'],
                  'student_id'      => $row['student_id'],
               ],
               ['marks_obtained' => $row['marks_obtained']]
            );
         }
      });

      return response()->json([
         'status'  => 'success',
         'message' => 'Marks saved successfully!',
      ]);
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
   public function edit(string $id)
   {
      //
   }

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
}
