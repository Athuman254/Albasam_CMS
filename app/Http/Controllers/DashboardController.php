<?php

namespace App\Http\Controllers;

use App\Models\Rank;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
   public function index()
   {
      $studentsCount = Student::count();
      $teachersCount = Teacher::count();
      $classesCount = Rank::count();
      $studentDistribution = Rank::withStudentCount()
         ->activated()
         ->with('stream')
         ->get()
         ->map(function ($rank) {
            return [
               'class_name' => $rank->full_name,
               'student_count' => $rank->students_count ?? 0,
            ];
         });

      return Inertia::render('Admin/Dashboard', [
         'studentsCount' => $studentsCount,
         'teachersCount' => $teachersCount,
         'classesCount' => $classesCount,
         'studentDistribution' => $studentDistribution
      ]);
   }
}
