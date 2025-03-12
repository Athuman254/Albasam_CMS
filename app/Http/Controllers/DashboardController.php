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
       
       return Inertia::render('admin/Dashboard', [
          'studentsCount' => $studentsCount,
          'teachersCount' => $teachersCount,
          'classesCount' => $classesCount
       ]);
   }
}
