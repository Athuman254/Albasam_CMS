<?php

namespace App\Http\Controllers\Exams;

use Inertia\Inertia;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ExamStudentController extends Controller
{

   public function dataTableEnrollStudents(Request $request)
   {
      $request->validate([
         'exam_id'  => 'required|exists:exams,id',
         'class_id' => 'required|exists:ranks,id',
      ]);

      $enrolledIds = DB::table('exam_students')
         ->where('exam_id', $request->exam_id)
         ->where('class_id', $request->class_id)
         ->pluck('student_id')
         ->toArray();

      $enrolledStudents = Student::whereIn('id', $enrolledIds)
         ->get()
         ->map(function ($s) {
            $admNo = $s->admission_number;
            $name = $s->name ?? trim(($s->first_name ?? '') . ' ' . ($s->last_name ?? ''));
            return [
               'id' => $s->id,
               'adm_no' => $admNo,
               'name' => $name,
            ];
         });

      return response()->json([
         'enrolled_student_ids' => $enrolledIds,
         'enrolled_students'    => $enrolledStudents,
      ]);
   }
   public function index()
   {
      return Inertia::render('Exam/ExamStudent/Index');
   }

   public function store(Request $request)
   {
      $validated = $request->validate([
         'exam_id'  => 'required|exists:exams,id',
         'class_id' => 'required|integer',
         'student_ids' => 'required|array',
         'student_ids.*' => 'integer|exists:students,id',
      ]);

      DB::table('exam_students')
         ->where('exam_id', $validated['exam_id'])
         ->where('class_id', $validated['class_id'])
         ->delete();

      if (!empty($validated['student_ids'])) {
         $insert = collect($validated['student_ids'])->map(fn($id) => [
            'exam_id'    => $validated['exam_id'],
            'class_id'   => $validated['class_id'],
            'student_id' => $id,
            'created_at' => now(),
            'updated_at' => now(),
         ])->toArray();

         DB::table('exam_students')->insert($insert);
      }

      return response()->json(['message' => 'Enrollment updated successfully']);
   }
}
