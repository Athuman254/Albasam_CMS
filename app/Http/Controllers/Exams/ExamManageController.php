<?php

namespace App\Http\Controllers\Exams;

use App\Models\Exam;
use Inertia\Inertia;
use App\Models\ExamSubject;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Spatie\QueryBuilder\AllowedFilter;

class ExamManageController extends Controller
{
   public function examSubject()
   {
      $subjects = \Spatie\QueryBuilder\QueryBuilder::for(
         ExamSubject::with('subject')->orderBy('id')
      )->allowedFilters([
         AllowedFilter::exact('class_id'),
         AllowedFilter::exact('exam_id')
      ])->jsonPaginate();
      return Resource::collection($subjects);
   }
   public function dataTable()
   {
      $exams = \Spatie\QueryBuilder\QueryBuilder::for(
         Exam::with(['academicYear', 'subjects'])->orderBy('id')
      )->allowedFilters([
         // AllowedFilter::exact('id'),
         // AllowedFilter::exact('is_active'),
         // AllowedFilter::partial('name'),
      ])->jsonPaginate();

      return Resource::collection($exams);
   }
   /**
    * Display a listing of the resource.
    */
   public function index()
   {
      return Inertia::render('Exam/ManageExam/Index');
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
         'name' => 'required|string|max:255',
         'term' => 'nullable|string',
         'exam_type' => 'required|in:opening,mid,end,general',
         'publisher' => 'nullable|string',
         'exam_date' => 'nullable|date',
         'academic_year_id' => 'required|exists:academic_years,id',
         'description' => 'nullable|string',
         'classes' => 'required|array|min:1',
         'classes.*' => 'exists:ranks,id',
         'classSubjects' => 'required|array',
      ]);

      DB::transaction(function () use ($validated, $request) {

         $exam = Exam::create([
            'name' => $validated['name'],
            'term' => $request->term,
            'exam_type' => $validated['exam_type'],
            'publisher' => $request->publisher,
            'exam_date' => $request->exam_date,
            'academic_year_id' => $validated['academic_year_id'],
            'description' => $request->description,
            'status' => 'draft',
         ]);

         foreach ($validated['classes'] as $classId) {
            $classSubjectsData = $validated['classSubjects'][$classId] ?? [];

            foreach ($classSubjectsData as $subjectData) {
               $subjectId = is_array($subjectData) ? $subjectData['id'] : $subjectData;
               $maxMarks = is_array($subjectData) ? ($subjectData['max_marks'] ?? 100) : 100;
               // Publisher and Date are now on Exam model

               ExamSubject::create([
                  'exam_id' => $exam->id,
                  'class_id' => $classId,
                  'subject_id' => $subjectId,
                  'max_marks' =>  $maxMarks,
               ]);
            }
         }
      });
      return back(303);
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
   public function update(Request $request, Exam $manage)
   {
      $validated = $request->validate([
         'name' => 'required|string|max:255',
         'term' => 'nullable|string',
         'exam_type' => 'required|in:opening,mid,end,general',
         'publisher' => 'nullable|string',
         'exam_date' => 'nullable|date',
         'academic_year_id' => 'required|exists:academic_years,id',
         'description' => 'nullable|string',
         'classes' => 'required|array|min:1',
         'classes.*' => 'exists:ranks,id',
         'classSubjects' => 'required|array',
      ]);

      DB::transaction(function () use ($validated, $request, $manage) {
         $manage->update([
            'name' => $validated['name'],
            'term' => $request->term,
            'exam_type' => $validated['exam_type'],
            'publisher' => $request->publisher,
            'exam_date' => $request->exam_date,
            'academic_year_id' => $validated['academic_year_id'],
            'description' => $request->description,
         ]);

         // Remove existing subjects and re-add
         $manage->subjects()->delete();

         foreach ($validated['classes'] as $classId) {
            $classSubjectsData = $validated['classSubjects'][$classId] ?? [];
            foreach ($classSubjectsData as $subjectData) {
               $subjectId = is_array($subjectData) ? $subjectData['id'] : $subjectData;
               $maxMarks = is_array($subjectData) ? ($subjectData['max_marks'] ?? 100) : 100;
               ExamSubject::create([
                  'exam_id' => $manage->id,
                  'class_id' => $classId,
                  'subject_id' => $subjectId,
                  'max_marks' =>  $maxMarks,
               ]);
            }
         }
      });
      return back(303);
   }

   /**
    * Remove the specified resource from storage.
    */
   public function destroy(Exam $manage)
   {
      try {

         $manage->subjects()->delete();
         $manage->delete();
         return response()->json([
            'message' => 'Exam deleted successfully.'
         ], 200);
      } catch (\Exception $e) {
         return response()->json([
            'message' => 'Failed to delete exam.',
            'error' => $e->getMessage()
         ], 500);
      }
   }
}
