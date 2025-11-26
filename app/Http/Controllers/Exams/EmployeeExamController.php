<?php

namespace App\Http\Controllers\Exams;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EmployeeExamController extends Controller
{
    public function enterMarks()
    {
        return Inertia::render('Employee/Exams/EnterMarks');
    }
    
    public function submittedMarks()
    {
        return Inertia::render('Employee/Exams/SubmittedMarks');
    }
}