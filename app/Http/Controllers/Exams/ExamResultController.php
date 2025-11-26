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
use App\Models\Subject;
use App\Models\Skill;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    /**
     * Generate skill breakdown for subjects using actual skills from database
     */
    private function generateSkillBreakdown($subjectName, $totalMarks, $maxMarks, $studentId = null, $examId = null, $classId = null)
    {
        // Get the subject with its skills from the database
        $subject = Subject::with(['skills' => function($query) {
            $query->where('is_active', true)->orderBy('name');
        }])->where('name', $subjectName)->first();
        
        if (!$subject || $subject->skills->isEmpty()) {
            return null;
        }
        
        $breakdown = [];
        $numberOfSkills = $subject->skills->count();
        
        // If we have specific student, exam, and class, try to get detailed skill marks
        $hasDetailedSkillMarks = false;
        $detailedSkillMarks = [];
        
        if ($studentId && $examId && $classId) {
            $detailedSkillMarks = $this->getDetailedSkillMarks($studentId, $examId, $classId, $subject->id);
            $hasDetailedSkillMarks = !empty($detailedSkillMarks);
        }
        
        if ($hasDetailedSkillMarks) {
            // Use actual skill marks from database
            foreach ($subject->skills as $skill) {
                $skillMark = $detailedSkillMarks[$skill->id] ?? [
                    'marks_obtained' => 0,
                    'maximum_marks' => 0
                ];
                
                $breakdown[] = [
                    'skill_name' => $skill->name,
                    'marks_obtained' => $skillMark['marks_obtained'],
                    'maximum_marks' => $skillMark['maximum_marks'],
                    'remarks' => $this->getSkillRemarks($skillMark['marks_obtained'], $skillMark['maximum_marks'])
                ];
            }
        } else {
            // Distribute marks evenly among skills (fallback)
            $marksPerSkill = $maxMarks / $numberOfSkills;
            $obtainedPerSkill = $totalMarks / $numberOfSkills;
            
            foreach ($subject->skills as $skill) {
                $breakdown[] = [
                    'skill_name' => $skill->name,
                    'marks_obtained' => round($obtainedPerSkill, 1),
                    'maximum_marks' => round($marksPerSkill, 1),
                    'remarks' => $this->getSkillRemarks($obtainedPerSkill, $marksPerSkill)
                ];
            }
        }
        
        return $breakdown;
    }

    /**
     * Get detailed skill marks for a student in a specific exam and class
     */
    private function getDetailedSkillMarks($studentId, $examId, $classId, $subjectId)
    {
        // This is where you would fetch actual skill-based marks from your database
        // You need to implement this based on your skill marks storage structure
        
        // For now, return empty array as placeholder
        // You'll need to create a table like 'exam_skill_marks' that stores:
        // student_id, exam_id, class_id, subject_id, skill_id, marks_obtained, maximum_marks
        
        return [];
    }

    /**
     * Get remarks for individual skills
     */
    private function getSkillRemarks($marks, $maxMarks)
    {
        if ($maxMarks == 0) return 'Not assessed';
        
        $percentage = ($marks / $maxMarks) * 100;
        
        return match(true) {
            $percentage >= 80 => 'You exceeded expectations',
            $percentage >= 70 => 'You met expectations',
            $percentage >= 60 => 'You approached expectations',
            $percentage >= 50 => 'Satisfactory',
            default => 'You are below expectations'
        };
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
            \Log::error('Error getting logo: ' . $e->getMessage());
        }
        
        return null;
    }

    /**
     * Generate bulk reports for multiple students, class, or stream
     */
    public function generateBulkReport(Request $request)
    {
        \Log::info('=== GENERATE BULK REPORT CALLED ===');
        \Log::info('Request Data: ', $request->all());

        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:ranks,id',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:students,id',
            'report_type' => 'required|in:student,class,stream',
            'include_analysis' => 'sometimes|boolean',
            'include_rankings' => 'sometimes|boolean',
            'only_published' => 'sometimes|boolean',
            'closing_date' => 'nullable|date',
            'opening_date' => 'nullable|date|after:closing_date',
        ]);

        $institution = Institution::with('media')->first();
        $examId = $request->exam_id;
        $classId = $request->class_id;
        $reportType = $request->report_type;
        $studentIds = $request->student_ids;
        $includeAnalysis = $request->boolean('include_analysis', true);
        $includeRankings = $request->boolean('include_rankings', true);
        $onlyPublished = $request->boolean('only_published', false);
        $closingDate = $request->closing_date;
        $openingDate = $request->opening_date;

        $class = Rank::with('stream')->findOrFail($classId);
        $exam = Exam::with('academicYear')->findOrFail($examId);

        // Get all students for the class
        $studentsQuery = Student::where('rank_id', $classId)
            ->with(['rank', 'gender', 'media']);

        // FIXED: Check if student_ids is provided and not empty for student report type
        if ($reportType === 'student') {
            if (!empty($studentIds)) {
                $studentsQuery->whereIn('id', $studentIds);
            } else {
                // If no specific students selected for individual reports, return error
                return response()->json([
                    'error' => 'Please select at least one student for individual reports.'
                ], 422);
            }
        }

        $students = $studentsQuery->get();

        if ($students->isEmpty()) {
            return response()->json([
                'error' => 'No students found for the selected criteria.'
            ], 404);
        }

        // For CLASS report type: Generate individual student reports concatenated
        if ($reportType === 'class') {
            return $this->generateClassIndividualReports(
                $institution, 
                $exam, 
                $class, 
                $students, 
                $includeAnalysis, 
                $includeRankings,
                $onlyPublished,
                $examId,
                $classId,
                $closingDate,
                $openingDate
            );
        }

        // For STUDENT report type: Generate individual reports concatenated
        if ($reportType === 'student') {
            return $this->generateMultipleStudentReports(
                $institution,
                $exam,
                $class,
                $students,
                $includeAnalysis,
                $includeRankings,
                $onlyPublished,
                $examId,
                $classId,
                $closingDate,
                $openingDate
            );
        }

        // For STREAM report type
        // Get all exam subjects for this exam and class
        $examSubjects = \App\Models\ExamSubject::with('subject')
            ->where('exam_id', $examId)
            ->where('class_id', $classId)
            ->get();

        // Calculate marks and rankings for all students
        $studentResults = [];
        $classTotalMarks = 0;
        $classTotalStudents = 0;

        foreach ($students as $student) {
            $marksQuery = ExamMark::with('examSubject.subject')
                ->where('student_id', $student->id)
                ->whereHas('examSubject', function($q) use ($examId, $classId) {
                    $q->where('exam_id', $examId)->where('class_id', $classId);
                });

            // Include approved marks for reports (since approved marks should be reportable)
            if ($onlyPublished) {
                $marksQuery->where('status', ExamMark::PUBLISHED);
            } else {
                // Include both approved and published marks
                $marksQuery->whereIn('status', ['approved', ExamMark::PUBLISHED]);
            }

            $marks = $marksQuery->get();

            if ($marks->isEmpty()) {
                continue;
            }

            $totalMarks = $marks->sum('marks_obtained');
            $average = $marks->count() > 0 ? $totalMarks / $marks->count() : 0;
            $totalMaxMarks = $marks->sum(function($mark) {
                return $mark->examSubject->max_marks;
            });
            $overallPercentage = $totalMaxMarks > 0 ? ($totalMarks / $totalMaxMarks) * 100 : 0;

            $studentResults[] = [
                'student' => $student,
                'marks' => $marks,
                'total_marks' => $totalMarks,
                'total_max_marks' => $totalMaxMarks,
                'average' => $average,
                'overall_percentage' => $overallPercentage,
                'grade' => $this->calculateGrade($overallPercentage),
            ];

            $classTotalMarks += $totalMarks;
            $classTotalStudents++;
        }

        if (empty($studentResults)) {
            $statusMessage = $onlyPublished ? 
                'No published marks found. Try unchecking "Only Published Results".' : 
                'No marks found for the selected criteria.';
                
            return response()->json([
                'error' => $statusMessage
            ], 404);
        }

        // Sort by total marks descending
        usort($studentResults, function($a, $b) {
            return $b['total_marks'] <=> $a['total_marks'];
        });

        // Calculate ranks and subject ranks
        $studentResults = $this->calculateRanks($studentResults, $examId, $classId, $includeRankings, $onlyPublished);

        // Calculate class statistics
        $classStatistics = $this->calculateClassStatistics($studentResults);

        $html = view('exams.reports.bulk_report', [
            'institution' => $institution,
            'exam' => $exam,
            'class' => $class,
            'studentResults' => $studentResults,
            'reportType' => $reportType,
            'examSubjects' => $examSubjects,
            'includeAnalysis' => $includeAnalysis,
            'includeRankings' => $includeRankings,
            'classStatistics' => $classStatistics,
            'closing_date' => $closingDate,
            'opening_date' => $openingDate,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
        ])->render();

        $filename = $this->generateFilename($exam, $class, $reportType, $studentIds);

        $pdfContent = GpdfFacade::generate($html);
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    /**
     * Generate individual student reports for entire class
     */
    private function generateClassIndividualReports($institution, $exam, $class, $students, $includeAnalysis, $includeRankings, $onlyPublished, $examId, $classId, $closingDate = null, $openingDate = null)
    {
        $allStudentHtml = '';
        $logoBase64 = $this->getLogoBase64($institution);

        foreach ($students as $index => $student) {
            try {
                // Get marks for this student
                $marksQuery = ExamMark::with('examSubject.subject')
                    ->where('student_id', $student->id)
                    ->whereHas('examSubject', function($q) use ($examId, $classId) {
                        $q->where('exam_id', $examId)->where('class_id', $classId);
                    });

                if ($onlyPublished) {
                    $marksQuery->where('status', ExamMark::PUBLISHED);
                } else {
                    $marksQuery->whereIn('status', ['approved', ExamMark::PUBLISHED]);
                }

                $marks = $marksQuery->get();

                if ($marks->isEmpty()) {
                    continue;
                }

                $totalMarks = $marks->sum('marks_obtained');
                $totalMaxMarks = $marks->sum(function($mark) {
                    return $mark->examSubject->max_marks;
                });
                $average = $marks->count() > 0 ? $totalMarks / $marks->count() : 0;
                $overallPercentage = $totalMaxMarks > 0 ? ($totalMarks / $totalMaxMarks) * 100 : 0;

                // Calculate class rank
                $classRank = $this->calculateClassRank($student->id, $examId, $classId, $onlyPublished);
                $classSize = $this->getClassSize($examId, $classId, $onlyPublished);

                // Calculate stream rank (if stream exists)
                $streamRank = null;
                $streamSize = null;
                if ($class->stream) {
                    $streamRank = $this->calculateStreamRank($student->id, $examId, $class->stream_id, $onlyPublished);
                    $streamSize = $this->getStreamSize($examId, $class->stream_id, $onlyPublished);
                }

                // Calculate subject ranks for both class and stream
                $subjectRanks = [];
                foreach ($marks as $mark) {
                    $subjectId = $mark->examSubject->subject_id;

                    // Class subject rank
                    $allSubjectMarksQuery = ExamMark::whereHas('examSubject', function($q) use ($examId, $classId, $subjectId) {
                        $q->where('exam_id', $examId)
                          ->where('class_id', $classId)
                          ->where('subject_id', $subjectId);
                    });

                    if ($onlyPublished) {
                        $allSubjectMarksQuery->where('status', ExamMark::PUBLISHED);
                    } else {
                        $allSubjectMarksQuery->whereIn('status', ['approved', ExamMark::PUBLISHED]);
                    }

                    $allSubjectMarks = $allSubjectMarksQuery->get()->sortByDesc('marks_obtained');
                    $classSubjectRank = $allSubjectMarks->search(function($item) use ($mark) {
                        return $item->id === $mark->id;
                    }) + 1;

                    // Stream subject rank
                    $streamSubjectRank = null;
                    $totalStudentsStream = null;
                    if ($class->stream) {
                        $streamId = $class->stream_id;
                        $allStreamSubjectMarksQuery = ExamMark::whereHas('examSubject', function($q) use ($examId, $streamId, $subjectId) {
                            $q->where('exam_id', $examId)
                              ->whereHas('class', function($classQuery) use ($streamId) {
                                  $classQuery->where('stream_id', $streamId);
                              })
                              ->where('subject_id', $subjectId);
                        });

                        if ($onlyPublished) {
                            $allStreamSubjectMarksQuery->where('status', ExamMark::PUBLISHED);
                        } else {
                            $allStreamSubjectMarksQuery->whereIn('status', ['approved', ExamMark::PUBLISHED]);
                        }

                        $allStreamSubjectMarks = $allStreamSubjectMarksQuery->get()->sortByDesc('marks_obtained');
                        $streamSubjectRank = $allStreamSubjectMarks->search(function($item) use ($mark) {
                            return $item->id === $mark->id;
                        }) + 1;
                        $totalStudentsStream = $allStreamSubjectMarks->count();
                    }

                    $subjectRanks[$subjectId] = [
                        'class_rank' => $classSubjectRank,
                        'stream_rank' => $streamSubjectRank,
                        'total_students_class' => $allSubjectMarks->count(),
                        'total_students_stream' => $totalStudentsStream,
                    ];
                }

                // Calculate performance analysis
                $performanceAnalysis = $includeAnalysis ? $this->calculatePerformanceAnalysis($marks, $classRank, $classSize) : null;

                // Prepare marks data with grades and ranks
                $marksWithGrades = $marks->map(function($mark) use ($subjectRanks, $includeRankings, $student, $examId, $classId) {
                    $percentage = $mark->examSubject->max_marks > 0 ? 
                        ($mark->marks_obtained / $mark->examSubject->max_marks) * 100 : 0;
                    
                    $subjectRankInfo = $subjectRanks[$mark->examSubject->subject_id] ?? [];
                    
                    // Generate skill breakdown for this subject - pass student, exam, and class info
                    $breakdown = $this->generateSkillBreakdown(
                        $mark->examSubject->subject->name,
                        $mark->marks_obtained,
                        $mark->examSubject->max_marks,
                        $student->id,
                        $examId,
                        $classId
                    );
                    
                    return [
                        'subject_name' => $mark->examSubject->subject->name,
                        'subject_code' => $mark->examSubject->subject->code ?? '',
                        'marks_obtained' => $mark->marks_obtained,
                        'maximum_marks' => $mark->examSubject->max_marks,
                        'percentage' => round($percentage, 2),
                        'grade' => $this->calculateGrade($percentage),
                        'remarks' => $this->getRemarks($percentage),
                        'class_rank' => $includeRankings ? ($subjectRankInfo['class_rank'] ?? null) : null,
                        'stream_rank' => $includeRankings ? ($subjectRankInfo['stream_rank'] ?? null) : null,
                        'total_students_class' => $includeRankings ? ($subjectRankInfo['total_students_class'] ?? null) : null,
                        'total_students_stream' => $includeRankings ? ($subjectRankInfo['total_students_stream'] ?? null) : null,
                        'breakdown' => $breakdown, // Add skill breakdown
                    ];
                });

                // Generate HTML for this student
                $studentHtml = view('exams.reports.student_report_improved', [
                    'institution' => $institution,
                    'student' => $student,
                    'exam' => $exam,
                    'class' => $class,
                    'marksWithGrades' => $marksWithGrades,
                    'total_marks' => $totalMarks,
                    'total_max_marks' => $totalMaxMarks,
                    'average' => round($average, 2),
                    'overall_percentage' => round($overallPercentage, 2),
                    'class_rank' => $classRank,
                    'stream_rank' => $streamRank,
                    'class_size' => $classSize,
                    'stream_size' => $streamSize,
                    'subjectRanks' => $subjectRanks,
                    'performanceAnalysis' => $performanceAnalysis,
                    'includeAnalysis' => $includeAnalysis,
                    'includeRankings' => $includeRankings,
                    'overall_grade' => $this->calculateGrade($overallPercentage),
                    'closing_date' => $closingDate,
                    'opening_date' => $openingDate,
                    'generatedAt' => now()->format('Y-m-d H:i:s'),
                    'logoBase64' => $logoBase64, // Add base64 logo
                ])->render();

                $allStudentHtml .= $studentHtml;

                // Add page break after each student except the last one
                if ($index < count($students) - 1) {
                    $allStudentHtml .= '<div style="page-break-after: always;"></div>';
                }

            } catch (\Exception $e) {
                \Log::error("Error generating report for student {$student->id}: " . $e->getMessage());
                continue;
            }
        }

        if (empty($allStudentHtml)) {
            return response()->json([
                'error' => 'No student reports could be generated. Please check if marks are available.'
            ], 404);
        }

        $filename = "exam-report-{$exam->name}-{$class->name}-class-individuals-" . now()->format('Y-m-d') . ".pdf";

        $pdfContent = GpdfFacade::generate($allStudentHtml);
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    /**
     * Generate multiple individual student reports
     */
    private function generateMultipleStudentReports($institution, $exam, $class, $students, $includeAnalysis, $includeRankings, $onlyPublished, $examId, $classId, $closingDate = null, $openingDate = null)
    {
        // Use the same method as class individual reports but for selected students only
        return $this->generateClassIndividualReports($institution, $exam, $class, $students, $includeAnalysis, $includeRankings, $onlyPublished, $examId, $classId, $closingDate, $openingDate);
    }

    /**
     * Generate individual student report with all requested elements
     */
    public function generateStudentReport(Request $request, $studentId)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:ranks,id',
            'include_analysis' => 'sometimes|boolean',
            'include_rankings' => 'sometimes|boolean',
            'only_published' => 'sometimes|boolean',
            'closing_date' => 'nullable|date',
            'opening_date' => 'nullable|date|after:closing_date',
        ]);

        $institution = Institution::with('media')->first();
        $examId = $request->get('exam_id');
        $classId = $request->get('class_id');
        $includeAnalysis = $request->boolean('include_analysis', true);
        $includeRankings = $request->boolean('include_rankings', true);
        $onlyPublished = $request->boolean('only_published', false);
        $closingDate = $request->get('closing_date');
        $openingDate = $request->get('opening_date');

        $class = Rank::with('stream')->findOrFail($classId);
        $student = Student::with(['rank', 'gender', 'media'])->findOrFail($studentId);
        $exam = Exam::with('academicYear')->findOrFail($examId);

        $marksQuery = ExamMark::with('examSubject.subject')
            ->where('student_id', $studentId)
            ->whereHas('examSubject', function($q) use ($examId, $classId) {
                $q->where('exam_id', $examId)->where('class_id', $classId);
            });

        // Include approved marks for reports
        if ($onlyPublished) {
            $marksQuery->where('status', ExamMark::PUBLISHED);
        } else {
            $marksQuery->whereIn('status', ['approved', ExamMark::PUBLISHED]);
        }

        $marks = $marksQuery->get();

        if ($marks->isEmpty()) {
            $statusMessage = $onlyPublished ? 
                'No published marks found for this student. Try unchecking "Only Published Results".' : 
                'No marks found for this student.';
                
            return response()->json([
                'error' => $statusMessage
            ], 404);
        }

        $totalMarks = $marks->sum('marks_obtained');
        $totalMaxMarks = $marks->sum(function($mark) {
            return $mark->examSubject->max_marks;
        });
        $average = $marks->count() > 0 ? $totalMarks / $marks->count() : 0;
        $overallPercentage = $totalMaxMarks > 0 ? ($totalMarks / $totalMaxMarks) * 100 : 0;

        // Calculate class rank
        $classRank = $this->calculateClassRank($studentId, $examId, $classId, $onlyPublished);

        // Calculate stream rank (if stream exists)
        $streamRank = null;
        if ($class->stream) {
            $streamRank = $this->calculateStreamRank($studentId, $examId, $class->stream_id, $onlyPublished);
        }

        // Calculate subject ranks for both class and stream
        $subjectRanks = [];
        foreach ($marks as $mark) {
            $subjectId = $mark->examSubject->subject_id;

            // Class subject rank
            $allSubjectMarksQuery = ExamMark::whereHas('examSubject', function($q) use ($examId, $classId, $subjectId) {
                $q->where('exam_id', $examId)
                  ->where('class_id', $classId)
                  ->where('subject_id', $subjectId);
            });

            if ($onlyPublished) {
                $allSubjectMarksQuery->where('status', ExamMark::PUBLISHED);
            } else {
                $allSubjectMarksQuery->whereIn('status', ['approved', ExamMark::PUBLISHED]);
            }

            $allSubjectMarks = $allSubjectMarksQuery->get()->sortByDesc('marks_obtained');
            $classSubjectRank = $allSubjectMarks->search(function($item) use ($mark) {
                return $item->id === $mark->id;
            }) + 1;

            // Stream subject rank - FIXED: Extract stream_id to variable first
            $streamSubjectRank = null;
            $totalStudentsStream = null;
            if ($class->stream) {
                $streamId = $class->stream_id; // Extract to variable for use in closure
                $allStreamSubjectMarksQuery = ExamMark::whereHas('examSubject', function($q) use ($examId, $streamId, $subjectId) {
                    $q->where('exam_id', $examId)
                      ->whereHas('class', function($classQuery) use ($streamId) {
                          $classQuery->where('stream_id', $streamId);
                      })
                      ->where('subject_id', $subjectId);
                });

                if ($onlyPublished) {
                    $allStreamSubjectMarksQuery->where('status', ExamMark::PUBLISHED);
                } else {
                    $allStreamSubjectMarksQuery->whereIn('status', ['approved', ExamMark::PUBLISHED]);
                }

                $allStreamSubjectMarks = $allStreamSubjectMarksQuery->get()->sortByDesc('marks_obtained');
                $streamSubjectRank = $allStreamSubjectMarks->search(function($item) use ($mark) {
                    return $item->id === $mark->id;
                }) + 1;
                $totalStudentsStream = $allStreamSubjectMarks->count();
            }

            $subjectRanks[$subjectId] = [
                'class_rank' => $classSubjectRank,
                'stream_rank' => $streamSubjectRank,
                'total_students_class' => $allSubjectMarks->count(),
                'total_students_stream' => $totalStudentsStream,
            ];
        }

        // Calculate performance analysis
        $performanceAnalysis = $includeAnalysis ? $this->calculatePerformanceAnalysis($marks, $classRank, $this->getClassSize($examId, $classId, $onlyPublished)) : null;

        // Prepare marks data with grades and ranks
        $marksWithGrades = $marks->map(function($mark) use ($subjectRanks, $includeRankings, $studentId, $examId, $classId) {
            $percentage = $mark->examSubject->max_marks > 0 ? 
                ($mark->marks_obtained / $mark->examSubject->max_marks) * 100 : 0;
            
            $subjectRankInfo = $subjectRanks[$mark->examSubject->subject_id] ?? [];
            
            // Generate skill breakdown for this subject - pass student, exam, and class info
            $breakdown = $this->generateSkillBreakdown(
                $mark->examSubject->subject->name,
                $mark->marks_obtained,
                $mark->examSubject->max_marks,
                $studentId,
                $examId,
                $classId
            );
            
            return [
                'subject_name' => $mark->examSubject->subject->name,
                'subject_code' => $mark->examSubject->subject->code ?? '',
                'marks_obtained' => $mark->marks_obtained,
                'maximum_marks' => $mark->examSubject->max_marks,
                'percentage' => round($percentage, 2),
                'grade' => $this->calculateGrade($percentage),
                'remarks' => $this->getRemarks($percentage),
                'class_rank' => $includeRankings ? ($subjectRankInfo['class_rank'] ?? null) : null,
                'stream_rank' => $includeRankings ? ($subjectRankInfo['stream_rank'] ?? null) : null,
                'total_students_class' => $includeRankings ? ($subjectRankInfo['total_students_class'] ?? null) : null,
                'total_students_stream' => $includeRankings ? ($subjectRankInfo['total_students_stream'] ?? null) : null,
                'breakdown' => $breakdown, // Add skill breakdown
            ];
        });

        // Get base64 logo
        $logoBase64 = $this->getLogoBase64($institution);

        $html = view('exams.reports.student_report_improved', [
            'institution' => $institution,
            'student' => $student,
            'exam' => $exam,
            'class' => $class,
            'marksWithGrades' => $marksWithGrades,
            'total_marks' => $totalMarks,
            'total_max_marks' => $totalMaxMarks,
            'average' => round($average, 2),
            'overall_percentage' => round($overallPercentage, 2),
            'class_rank' => $classRank,
            'stream_rank' => $streamRank,
            'class_size' => $this->getClassSize($examId, $classId, $onlyPublished),
            'stream_size' => $streamRank ? $this->getStreamSize($examId, $class->stream_id, $onlyPublished) : null,
            'subjectRanks' => $subjectRanks,
            'performanceAnalysis' => $performanceAnalysis,
            'includeAnalysis' => $includeAnalysis,
            'includeRankings' => $includeRankings,
            'overall_grade' => $this->calculateGrade($overallPercentage),
            'closing_date' => $closingDate,
            'opening_date' => $openingDate,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
            'logoBase64' => $logoBase64, // Add base64 logo
        ])->render();

        $filename = "exam-report-{$student->admission_number}-{$exam->name}.pdf";

        $pdfContent = GpdfFacade::generate($html);
        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    /**
     * Calculate class rank for a student
     */
    private function calculateClassRank($studentId, $examId, $classId, $onlyPublished = false)
    {
        $allStudentsMarksQuery = ExamMark::whereHas('examSubject', function($q) use ($examId, $classId) {
            $q->where('exam_id', $examId)->where('class_id', $classId);
        });

        if ($onlyPublished) {
            $allStudentsMarksQuery->where('status', ExamMark::PUBLISHED);
        } else {
            $allStudentsMarksQuery->whereIn('status', ['approved', ExamMark::PUBLISHED]);
        }

        $allStudentsMarks = $allStudentsMarksQuery->get()
            ->groupBy('student_id')
            ->map(function($marks) {
                return $marks->sum('marks_obtained');
            })
            ->sortDesc();

        $rank = $allStudentsMarks->keys()->search($studentId);
        return $rank !== false ? $rank + 1 : null;
    }

    /**
     * Calculate stream rank for a student
     */
    private function calculateStreamRank($studentId, $examId, $streamId, $onlyPublished = false)
    {
        $allStreamMarksQuery = ExamMark::whereHas('examSubject', function($q) use ($examId, $streamId) {
            $q->where('exam_id', $examId)
              ->whereHas('class', function($classQuery) use ($streamId) {
                  $classQuery->where('stream_id', $streamId);
              });
        });

        if ($onlyPublished) {
            $allStreamMarksQuery->where('status', ExamMark::PUBLISHED);
        } else {
            $allStreamMarksQuery->whereIn('status', ['approved', ExamMark::PUBLISHED]);
        }

        $allStreamMarks = $allStreamMarksQuery->get()
            ->groupBy('student_id')
            ->map(function($marks) {
                return $marks->sum('marks_obtained');
            })
            ->sortDesc();

        $rank = $allStreamMarks->keys()->search($studentId);
        return $rank !== false ? $rank + 1 : null;
    }

    /**
     * Get class size
     */
    private function getClassSize($examId, $classId, $onlyPublished = false)
    {
        $query = ExamMark::whereHas('examSubject', function($q) use ($examId, $classId) {
            $q->where('exam_id', $examId)->where('class_id', $classId);
        });

        if ($onlyPublished) {
            $query->where('status', ExamMark::PUBLISHED);
        } else {
            $query->whereIn('status', ['approved', ExamMark::PUBLISHED]);
        }

        return $query->distinct('student_id')->count('student_id');
    }

    /**
     * Get stream size
     */
    private function getStreamSize($examId, $streamId, $onlyPublished = false)
    {
        $query = ExamMark::whereHas('examSubject', function($q) use ($examId, $streamId) {
            $q->where('exam_id', $examId)
              ->whereHas('class', function($classQuery) use ($streamId) {
                  $classQuery->where('stream_id', $streamId);
              });
        });

        if ($onlyPublished) {
            $query->where('status', ExamMark::PUBLISHED);
        } else {
            $query->whereIn('status', ['approved', ExamMark::PUBLISHED]);
        }

        return $query->distinct('student_id')->count('student_id');
    }

    /**
     * Calculate ranks for students
     */
    private function calculateRanks($studentResults, $examId, $classId, $includeSubjectRanks = true, $onlyPublished = false)
    {
        foreach ($studentResults as $index => &$result) {
            $result['rank'] = $index + 1;

            if ($includeSubjectRanks) {
                $subjectRanks = [];
                foreach ($result['marks'] as $mark) {
                    $subjectId = $mark->examSubject->subject_id;

                    $allSubjectMarksQuery = ExamMark::whereHas('examSubject', function($q) use ($examId, $classId, $subjectId) {
                        $q->where('exam_id', $examId)
                          ->where('class_id', $classId)
                          ->where('subject_id', $subjectId);
                    });

                    if ($onlyPublished) {
                        $allSubjectMarksQuery->where('status', ExamMark::PUBLISHED);
                    } else {
                        $allSubjectMarksQuery->whereIn('status', ['approved', ExamMark::PUBLISHED]);
                    }

                    $allSubjectMarks = $allSubjectMarksQuery->get()->sortByDesc('marks_obtained');

                    $subjectRank = $allSubjectMarks->search(function($item) use ($mark) {
                        return $item->id === $mark->id;
                    }) + 1;

                    $subjectRanks[$subjectId] = $subjectRank;
                }
                $result['subject_ranks'] = $subjectRanks;
            }
        }

        return $studentResults;
    }

    /**
     * Calculate class statistics
     */
    private function calculateClassStatistics($studentResults)
    {
        if (empty($studentResults)) {
            return [];
        }

        $totalStudents = count($studentResults);
        $totalMarks = array_sum(array_column($studentResults, 'total_marks'));
        $averageMarks = $totalMarks / $totalStudents;
        $maxMarks = max(array_column($studentResults, 'total_marks'));
        $minMarks = min(array_column($studentResults, 'total_marks'));

        // FIXED: Include all possible grades from calculateGrade method
        $gradeDistribution = [
            'A' => 0, 
            'B+' => 0, 'B' => 0, 'B-' => 0,
            'C+' => 0, 'C' => 0, 'C-' => 0,
            'D+' => 0, 'D-' => 0,
            'E' => 0
        ];

        foreach ($studentResults as $result) {
            $grade = $result['grade'];
            // Safely increment the grade count, initialize if not exists
            if (array_key_exists($grade, $gradeDistribution)) {
                $gradeDistribution[$grade]++;
            } else {
                // If grade doesn't exist in our distribution, add it
                $gradeDistribution[$grade] = 1;
            }
        }

        return [
            'total_students' => $totalStudents,
            'average_marks' => round($averageMarks, 2),
            'highest_marks' => $maxMarks,
            'lowest_marks' => $minMarks,
            'grade_distribution' => $gradeDistribution,
            'pass_rate' => round((($totalStudents - ($gradeDistribution['E'] ?? 0)) / $totalStudents) * 100, 2),
        ];
    }

    /**
     * Calculate performance analysis for a student
     */
    private function calculatePerformanceAnalysis($marks, $rank, $classSize)
    {
        $bestSubject = $marks->sortByDesc('marks_obtained')->first();
        $weakSubject = $marks->sortBy('marks_obtained')->first();

        $analysis = [
            'overall_performance' => '',
            'strong_subject' => [
                'name' => $bestSubject->examSubject->subject->name,
                'marks' => $bestSubject->marks_obtained,
                'max_marks' => $bestSubject->examSubject->max_marks,
                'percentage' => $bestSubject->examSubject->max_marks > 0 ? 
                    ($bestSubject->marks_obtained / $bestSubject->examSubject->max_marks) * 100 : 0,
            ],
            'weak_subject' => [
                'name' => $weakSubject->examSubject->subject->name,
                'marks' => $weakSubject->marks_obtained,
                'max_marks' => $weakSubject->examSubject->max_marks,
                'percentage' => $weakSubject->examSubject->max_marks > 0 ? 
                    ($weakSubject->marks_obtained / $weakSubject->examSubject->max_marks) * 100 : 0,
            ],
            'recommendations' => []
        ];

        $totalMarks = $marks->sum('marks_obtained');
        $totalMaxMarks = $marks->sum(function($mark) {
            return $mark->examSubject->max_marks;
        });
        $percentage = $totalMaxMarks > 0 ? ($totalMarks / $totalMaxMarks) * 100 : 0;

        if ($percentage >= 80) {
            $analysis['overall_performance'] = "Excellent - Ranked {$rank} out of {$classSize} students";
        } elseif ($percentage >= 70) {
            $analysis['overall_performance'] = "Very Good - Ranked {$rank} out of {$classSize} students";
        } elseif ($percentage >= 60) {
            $analysis['overall_performance'] = "Good - Ranked {$rank} out of {$classSize} students";
        } elseif ($percentage >= 50) {
            $analysis['overall_performance'] = "Average - Ranked {$rank} out of {$classSize} students";
        } else {
            $analysis['overall_performance'] = "Needs Improvement - Ranked {$rank} out of {$classSize} students";
        }

        if ($percentage < 50) {
            $analysis['recommendations'][] = "Focus on improving performance in {$weakSubject->examSubject->subject->name}";
            $analysis['recommendations'][] = "Seek extra help from teachers in weak subjects";
        }

        if ($rank <= 3) {
            $analysis['recommendations'][] = "Maintain the excellent performance";
        }

        return $analysis;
    }

    /**
     * Calculate grade based on percentage
     */
    private function calculateGrade($percentage)
    {
        return match (true) {
            $percentage >= 80 => 'A',
            $percentage >= 70 => 'B+',
            $percentage >= 65 => 'B',
            $percentage >= 60 => 'B-',
            $percentage >= 55 => 'C+',
            $percentage >= 50 => 'C',
            $percentage >= 45 => 'C-',
            $percentage >= 40 => 'D+',
            $percentage >= 35 => 'D-',
            default => 'E',
        };
    }

    /**
     * Get remarks based on percentage
     */
    private function getRemarks($percentage)
    {
        return match (true) {
            $percentage >= 80 => 'Excellent',
            $percentage >= 70 => 'Very Good',
            $percentage >= 60 => 'Good',
            $percentage >= 50 => 'Average',
            $percentage >= 40 => 'Below Average',
            default => 'Poor',
        };
    }

    /**
     * Generate filename for PDF
     */
    private function generateFilename($exam, $class, $reportType, $studentIds = null)
    {
        $baseName = "exam-report-{$exam->name}-{$class->name}";

        if ($reportType === 'student' && $studentIds && count($studentIds) === 1) {
            $student = Student::find($studentIds[0]);
            return "{$baseName}-{$student->admission_number}.pdf";
        }

        return "{$baseName}-{$reportType}-" . now()->format('Y-m-d') . ".pdf";
    }

    /**
     * Get available students for report generation
     */
    public function getReportStudents(Request $request)
    {
        $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'class_id' => 'required|exists:ranks,id',
        ]);

        $examId = $request->exam_id;
        $classId = $request->class_id;

        $students = Student::where('rank_id', $classId)
            ->whereHas('examMarks', function($query) use ($examId, $classId) {
                $query->whereHas('examSubject', function($q) use ($examId, $classId) {
                    $q->where('exam_id', $examId)->where('class_id', $classId);
                })->whereIn('status', ['approved', ExamMark::PUBLISHED]);
            })
            ->with(['examMarks' => function($query) use ($examId, $classId) {
                $query->whereHas('examSubject', function($q) use ($examId, $classId) {
                    $q->where('exam_id', $examId)->where('class_id', $classId);
                })->whereIn('status', ['approved', ExamMark::PUBLISHED]);
            }])
            ->get()
            ->map(function($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'admission_number' => $student->admission_number,
                    'has_marks' => $student->examMarks->isNotEmpty(),
                ];
            });

        return response()->json([
            'students' => $students
        ]);
    }
}