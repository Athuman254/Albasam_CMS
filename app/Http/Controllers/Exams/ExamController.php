<?php

namespace App\Http\Controllers\Exams;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExamController extends Controller
{
    public function index(){
   }

   public function manageExam(){
      return Inertia::render('Exam/ManageExam/Index');

    }
}
