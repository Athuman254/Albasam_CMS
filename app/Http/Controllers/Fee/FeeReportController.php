<?php
namespace App\Http\Controllers\Fee;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\Rank;
use App\Models\Stream;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PDF;

class FeeReportController extends Controller
{
    /**
     * Display the fee report form
     */
    public function index()
    {
        // Get classes with stream information using join
        $classes = Rank::with(['stream'])
            ->select('ranks.id', 'ranks.name', 'ranks.stream_id')
            ->where('ranks.activated', 1)
            ->get()
            ->map(function ($rank) {
                return [
                    'id' => $rank->id,
                    'name' => $rank->name,
                    'stream_id' => $rank->stream_id,
                    'stream_name' => $rank->stream ? $rank->stream->name : null,
                    'full_name' => $rank->stream ? $rank->name . ' ' . $rank->stream->name : $rank->name
                ];
            });

        $terms = $this->getAvailableTerms();
        
        return Inertia::render('Admin/Fees/FeeReport', [
            'classes' => $classes,
            'terms' => $terms
        ]);
    }

    /**
     * Generate fee report based on filters
     */
    public function generateReport(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:ranks,id',
            'term' => 'nullable|string',
            'include_zero_balance' => 'boolean'
        ]);

        try {
            $classId = $request->class_id;
            $term = $request->term;
            $includeZeroBalance = $request->boolean('include_zero_balance');

            // Get the class with stream information
            $class = Rank::with(['stream'])->find($classId);
            $className = $class->stream ? $class->name . ' ' . $class->stream->name : $class->name;

            // Build query for fees
            $feeQuery = Fee::where('rank_id', $classId)
                ->with(['student' => function($query) {
                    $query->select('id', 'first_name', 'middle_name', 'last_name', 'admission_number');
                }, 'rank.stream']);

            // Fix term filtering - handle both numeric and formatted terms
            if ($term) {
                // Extract numeric value from term (e.g., "Term 1" becomes "1", "1" stays "1")
                $termValue = $this->extractTermValue($term);
                $feeQuery->where('term', $termValue);
            }

            $fees = $feeQuery->get();

            // Group fees by student and calculate totals
            $studentFees = [];
            $totalAmount = 0;
            $totalPaid = 0;
            $totalBalance = 0;

            foreach ($fees as $fee) {
                $studentId = $fee->student_id;
                
                if (!isset($studentFees[$studentId])) {
                    // Get student full name by combining first, middle, and last names
                    $studentFullName = $this->getStudentFullName($fee->student);
                    
                    $studentFees[$studentId] = [
                        'admission_number' => $fee->student->admission_number,
                        'student_name' => $studentFullName,
                        'class_name' => $className,
                        'total_amount' => 0,
                        'paid_amount' => 0,
                        'balance' => 0,
                    ];
                }

                $studentFees[$studentId]['total_amount'] += $fee->amount;
                $studentFees[$studentId]['paid_amount'] += $fee->paid_amount;
                $studentFees[$studentId]['balance'] += $fee->balance;
            }

            // Convert to array and filter, calculate collection rates
            $reportData = [];
            foreach ($studentFees as $studentFee) {
                // Skip students with zero or negative balance if not included
                if (!$includeZeroBalance && $studentFee['balance'] <= 0) {
                    continue;
                }

                // Calculate individual student collection rate
                $studentFee['collection_rate'] = $studentFee['total_amount'] > 0 
                    ? round(($studentFee['paid_amount'] / $studentFee['total_amount']) * 100, 2) 
                    : 0;

                // Add collection rate class for styling
                $studentFee['collection_rate_class'] = $this->getCollectionRateClass($studentFee['collection_rate']);

                $reportData[] = $studentFee;

                $totalAmount += $studentFee['total_amount'];
                $totalPaid += $studentFee['paid_amount'];
                $totalBalance += $studentFee['balance'];
            }

            // Calculate overall collection rate - FIXED LOGIC
            // Use the actual totals instead of summing individual student rates
            $collectionRate = $totalAmount > 0 ? round(($totalPaid / $totalAmount) * 100, 2) : 0;

            $summary = [
                'total_amount' => $totalAmount,
                'total_paid' => $totalPaid,
                'total_balance' => $totalBalance,
                'collection_rate' => $collectionRate,
                'collection_rate_class' => $this->getCollectionRateClass($collectionRate)
            ];

            if ($request->has('download')) {
                return $this->generatePDF($reportData, $summary, $className, $term, $includeZeroBalance);
            }

            if ($request->has('print')) {
                return $this->generatePrintView($reportData, $summary, $className, $term, $includeZeroBalance);
            }

            return response()->json([
                'success' => true,
                'reportData' => $reportData,
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            \Log::error('Fee Report Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating report: ' . $e->getMessage(),
                'reportData' => [],
                'summary' => [
                    'total_amount' => 0,
                    'total_paid' => 0,
                    'total_balance' => 0,
                    'collection_rate' => 0,
                    'collection_rate_class' => 'text-danger'
                ]
            ], 500);
        }
    }

    /**
     * Extract numeric term value from formatted term string
     */
    private function extractTermValue($term)
    {
        if (is_numeric($term)) {
            return $term;
        }
        
        // Handle "Term 1", "Term 2", etc.
        if (preg_match('/Term\s+(\d+)/i', $term, $matches)) {
            return $matches[1];
        }
        
        // Handle "1", "2", etc.
        if (is_numeric(trim($term))) {
            return trim($term);
        }
        
        return $term;
    }

    /**
     * Get student full name by combining first, middle, and last names
     */
    private function getStudentFullName($student)
    {
        $names = [];
        
        if (!empty($student->first_name)) {
            $names[] = $student->first_name;
        }
        
        if (!empty($student->middle_name)) {
            $names[] = $student->middle_name;
        }
        
        if (!empty($student->last_name)) {
            $names[] = $student->last_name;
        }
        
        return implode(' ', $names) ?: 'N/A';
    }

    /**
     * Get collection rate class for styling
     */
    private function getCollectionRateClass($rate)
    {
        if ($rate >= 100) return 'text-success';
        if ($rate >= 50) return 'text-warning';
        return 'text-danger';
    }

    /**
     * Generate PDF report using external blade template
     */
    private function generatePDF($reportData, $summary, $className, $term, $includeZeroBalance)
    {
        // Format term for display
        $displayTerm = $term ? $this->formatTermForDisplay($term) : 'All Terms';

        $pdf = PDF::loadView('admin.fees.fee-report-pdf', [
            'reportData' => $reportData,
            'summary' => $summary,
            'className' => $className,
            'term' => $displayTerm,
            'includeZeroBalance' => $includeZeroBalance,
            'generatedDate' => now()->format('Y-m-d H:i:s')
        ]);

        return $pdf->download('fee-report-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Generate print view using external blade template
     */
    private function generatePrintView($reportData, $summary, $className, $term, $includeZeroBalance)
    {
        // Format term for display
        $displayTerm = $term ? $this->formatTermForDisplay($term) : 'All Terms';

        return view('admin.fees.fee-report-pdf', [
            'reportData' => $reportData,
            'summary' => $summary,
            'className' => $className,
            'term' => $displayTerm,
            'includeZeroBalance' => $includeZeroBalance,
            'generatedDate' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Format term for display in PDF
     */
    private function formatTermForDisplay($term)
    {
        $termValue = $this->extractTermValue($term);
        return 'Term ' . $termValue;
    }

    /**
     * Get all available terms for filtering with full names
     */
    private function getAvailableTerms()
    {
        $terms = Fee::distinct()
            ->whereNotNull('term')
            ->where('term', '!=', '')
            ->pluck('term')
            ->toArray();

        // Format terms to have full names and ensure uniqueness
        $formattedTerms = [];
        foreach ($terms as $term) {
            $numericTerm = $this->extractTermValue($term);
            $formattedTerm = 'Term ' . $numericTerm;
            
            // Avoid duplicates
            if (!in_array($formattedTerm, $formattedTerms)) {
                $formattedTerms[] = $formattedTerm;
            }
        }

        // Sort terms numerically
        usort($formattedTerms, function($a, $b) {
            $aNum = $this->extractTermValue($a);
            $bNum = $this->extractTermValue($b);
            return $aNum - $bNum;
        });

        return $formattedTerms;
    }

    /**
     * Display fee overview dashboard
     */
    public function overview()
    {
        $currentYear = now()->year;
        
        // Basic statistics
        $totalExpected = Fee::sum('amount');
        $totalCollected = FeePayment::where('status', 'completed')->sum('amount');
        $totalBalance = Fee::sum('balance');
        $collectionRate = $totalExpected > 0 ? ($totalCollected / $totalExpected) * 100 : 0;

        // Payment methods breakdown
        $paymentMethods = FeePayment::where('status', 'completed')
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->pluck('total', 'payment_method');

        // Monthly collection for current year
        $monthlyCollection = FeePayment::where('status', 'completed')
            ->whereYear('payment_date', $currentYear)
            ->select(
                DB::raw('MONTH(payment_date) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->get()
            ->pluck('total', 'month');

        // Fill missing months with zero
        $monthlyData = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyData[Carbon::create()->month($month)->format('F')] = $monthlyCollection->get($month, 0);
        }

        // Recent payments with proper student names
        $recentPayments = FeePayment::with(['student' => function($query) {
                $query->select('id', 'first_name', 'middle_name', 'last_name', 'admission_number');
            }, 'verifiedBy'])
            ->where('status', 'completed')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function($payment) {
                $studentFullName = $this->getStudentFullName($payment->student);
                
                return [
                    'id' => $payment->id,
                    'student_name' => $studentFullName,
                    'admission_number' => $payment->student->admission_number,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'payment_date' => $payment->payment_date,
                    'verified_by' => $payment->verifiedBy->name ?? 'N/A',
                    'reference_number' => $payment->reference_number
                ];
            });

        return Inertia::render('Admin/Fees/Reports/Overview', [
            'overview' => [
                'total_expected' => $totalExpected,
                'total_collected' => $totalCollected,
                'total_balance' => $totalBalance,
                'collection_rate' => round($collectionRate, 2),
            ],
            'payment_methods' => $paymentMethods,
            'monthly_data' => $monthlyData,
            'recent_payments' => $recentPayments,
        ]);
    }

    /**
     * Display individual student fee report
     */
    public function student(Student $student)
    {
        $student->load(['currentRank']);

        $fees = Fee::with(['rank'])
            ->where('student_id', $student->id)
            ->get();

        $payments = FeePayment::with(['fee.rank'])
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        $totalPaid = $payments->where('status', 'completed')->sum('amount');
        $totalBalance = $fees->sum('balance');

        return Inertia::render('Admin/Fees/Reports/StudentReport', [
            'student' => $student,
            'fees' => $fees,
            'payments' => $payments,
            'total_paid' => $totalPaid,
            'total_balance' => $totalBalance,
        ]);
    }

    /**
     * Generate collection report with filters
     */
    public function collectionReport(Request $request)
    {
        $query = FeePayment::with(['student' => function($query) {
                $query->select('id', 'first_name', 'middle_name', 'last_name', 'admission_number');
            }, 'fee.rank', 'verifiedBy'])
            ->where('status', 'completed');

        // Apply filters
        if ($request->has('date_from') && $request->date_from) {
            $query->where('payment_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        $collections = $query->latest()->paginate(20);

        $summary = $query->clone()->select(
            DB::raw('SUM(amount) as total_amount'),
            DB::raw('COUNT(*) as total_transactions')
        )->first();

        return Inertia::render('Admin/Fees/Reports/CollectionReport', [
            'collections' => $collections,
            'summary' => $summary,
            'filters' => $request->only(['date_from', 'date_to', 'payment_method']),
        ]);
    }

    /**
     * Generate outstanding fees report
     */
    public function outstandingReport(Request $request)
    {
        // Get classes with stream information
        $classes = Rank::with(['stream'])
            ->select('ranks.id', 'ranks.name', 'ranks.stream_id')
            ->where('ranks.activated', 1)
            ->get()
            ->map(function ($rank) {
                return [
                    'id' => $rank->id,
                    'name' => $rank->name,
                    'stream_id' => $rank->stream_id,
                    'stream_name' => $rank->stream ? $rank->stream->name : null,
                    'full_name' => $rank->stream ? $rank->name . ' ' . $rank->stream->name : $rank->name
                ];
            });

        $query = Fee::with(['student' => function($query) {
                $query->select('id', 'first_name', 'middle_name', 'last_name', 'admission_number');
            }, 'rank.stream'])
            ->where('balance', '>', 0);

        // Apply filters
        if ($request->has('class_id') && $request->class_id) {
            $query->where('rank_id', $request->class_id);
        }

        if ($request->has('academic_year') && $request->academic_year) {
            $query->where('academic_year', $request->academic_year);
        }

        $outstandingFees = $query->get()->groupBy('student_id');

        $studentReports = [];
        foreach ($outstandingFees as $studentId => $fees) {
            $student = $fees->first()->student;
            $totalBalance = $fees->sum('balance');
            $totalExpected = $fees->sum('amount');
            $studentFullName = $this->getStudentFullName($student);

            $studentReports[] = [
                'student' => [
                    'id' => $student->id,
                    'full_name' => $studentFullName,
                    'admission_number' => $student->admission_number,
                ],
                'admission_number' => $student->admission_number,
                'class' => $fees->first()->rank->stream ? 
                    $fees->first()->rank->name . ' ' . $fees->first()->rank->stream->name : 
                    $fees->first()->rank->name,
                'total_expected' => $totalExpected,
                'total_balance' => $totalBalance,
                'amount_paid' => $totalExpected - $totalBalance,
                'fees' => $fees,
            ];
        }

        // Sort by balance descending
        usort($studentReports, function($a, $b) {
            return $b['total_balance'] <=> $a['total_balance'];
        });

        return Inertia::render('Admin/Fees/Reports/OutstandingReport', [
            'student_reports' => $studentReports,
            'classes' => $classes,
            'filters' => $request->only(['class_id', 'academic_year']),
            'total_outstanding' => collect($studentReports)->sum('total_balance'),
        ]);
    }

    /**
     * Export collection report to CSV
     */
    public function exportCollectionReport(Request $request)
    {
        $query = FeePayment::with(['student' => function($query) {
                $query->select('id', 'first_name', 'middle_name', 'last_name', 'admission_number');
            }, 'fee.rank.stream', 'verifiedBy'])
            ->where('status', 'completed');

        // Apply filters
        if ($request->has('date_from') && $request->date_from) {
            $query->where('payment_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        if ($request->has('payment_method') && $request->payment_method) {
            $query->where('payment_method', $request->payment_method);
        }

        $collections = $query->latest()->get();

        $fileName = 'fee_collections_' . now()->format('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($collections) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            // Headers
            fputcsv($file, [
                'Payment Date',
                'Student Name',
                'Admission Number',
                'Class',
                'Amount (KSh)',
                'Payment Method',
                'Reference Number',
                'Verified By',
                'Notes'
            ]);

            // Data
            foreach ($collections as $payment) {
                $className = $payment->fee->rank->stream ? 
                    $payment->fee->rank->name . ' ' . $payment->fee->rank->stream->name : 
                    $payment->fee->rank->name;

                $studentFullName = $this->getStudentFullName($payment->student);

                fputcsv($file, [
                    $payment->payment_date,
                    $studentFullName,
                    $payment->student->admission_number ?? 'N/A',
                    $className,
                    number_format($payment->amount, 2),
                    strtoupper($payment->payment_method),
                    $payment->reference_number,
                    $payment->verifiedBy->name ?? 'N/A',
                    $payment->notes ?? ''
                ]);
            }

            // Summary row
            fputcsv($file, []); // Empty row
            fputcsv($file, ['SUMMARY', '', '', '', '', '', '', '', '']);
            fputcsv($file, [
                'Total Collections:',
                '',
                '',
                '',
                'KSh ' . number_format($collections->sum('amount'), 2),
                '',
                'Total Transactions:',
                $collections->count(),
                ''
            ]);

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Export outstanding fees report to CSV
     */
    public function exportOutstandingReport(Request $request)
    {
        $query = Fee::with(['student' => function($query) {
                $query->select('id', 'first_name', 'middle_name', 'last_name', 'admission_number');
            }, 'rank.stream'])
            ->where('balance', '>', 0);

        // Apply filters
        if ($request->has('class_id') && $request->class_id) {
            $query->where('rank_id', $request->class_id);
        }

        if ($request->has('academic_year') && $request->academic_year) {
            $query->where('academic_year', $request->academic_year);
        }

        $outstandingFees = $query->get()->groupBy('student_id');

        $studentReports = [];
        foreach ($outstandingFees as $studentId => $fees) {
            $student = $fees->first()->student;
            $totalBalance = $fees->sum('balance');
            $totalExpected = $fees->sum('amount');
            $studentFullName = $this->getStudentFullName($student);

            $studentReports[] = [
                'student' => $student,
                'admission_number' => $student->admission_number,
                'class' => $fees->first()->rank->stream ? 
                    $fees->first()->rank->name . ' ' . $fees->first()->rank->stream->name : 
                    $fees->first()->rank->name,
                'total_expected' => $totalExpected,
                'total_balance' => $totalBalance,
                'amount_paid' => $totalExpected - $totalBalance,
                'fees' => $fees,
            ];
        }

        // Sort by balance descending
        usort($studentReports, function($a, $b) {
            return $b['total_balance'] <=> $a['total_balance'];
        });

        $fileName = 'outstanding_fees_' . now()->format('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($studentReports) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            // Headers
            fputcsv($file, [
                'Student Name',
                'Admission Number',
                'Class',
                'Total Expected (KSh)',
                'Amount Paid (KSh)',
                'Outstanding Balance (KSh)',
                'Payment Progress (%)',
                'Number of Fee Items'
            ]);

            $totalOutstanding = 0;
            $totalExpected = 0;

            // Data
            foreach ($studentReports as $report) {
                $progress = $report['total_expected'] > 0 ? 
                    (($report['amount_paid'] / $report['total_expected']) * 100) : 0;
                
                $studentFullName = $this->getStudentFullName($report['student']);
                
                fputcsv($file, [
                    $studentFullName,
                    $report['admission_number'],
                    $report['class'],
                    number_format($report['total_expected'], 2),
                    number_format($report['amount_paid'], 2),
                    number_format($report['total_balance'], 2),
                    round($progress, 2) . '%',
                    count($report['fees'])
                ]);

                $totalOutstanding += $report['total_balance'];
                $totalExpected += $report['total_expected'];
            }

            // Summary rows
            fputcsv($file, []); // Empty row
            fputcsv($file, ['SUMMARY', '', '', '', '', '', '', '']);
            fputcsv($file, [
                'Total Expected:',
                '',
                '',
                'KSh ' . number_format($totalExpected, 2),
                '',
                'Total Outstanding:',
                'KSh ' . number_format($totalOutstanding, 2),
                ''
            ]);
            fputcsv($file, [
                'Total Students:',
                count($studentReports),
                '',
                'Collection Rate:',
                $totalExpected > 0 ? round((($totalExpected - $totalOutstanding) / $totalExpected) * 100, 2) . '%' : '0%',
                '',
                '',
                ''
            ]);

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Export individual student fee report to CSV
     */
    public function exportStudentReport(Student $student)
    {
        $student->load(['currentRank.stream']);

        $fees = Fee::with(['rank.stream'])
            ->where('student_id', $student->id)
            ->get();

        $payments = FeePayment::with(['fee.rank.stream'])
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->latest()
            ->get();

        $totalPaid = $payments->sum('amount');
        $totalBalance = $fees->sum('balance');
        $totalExpected = $fees->sum('amount');

        $fileName = 'student_fee_report_' . $student->admission_number . '_' . now()->format('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($student, $fees, $payments, $totalPaid, $totalBalance, $totalExpected) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            // Student Information
            fputcsv($file, ['STUDENT FEE REPORT']);
            fputcsv($file, []);
            fputcsv($file, ['Student Information']);
            
            $studentFullName = $this->getStudentFullName($student);
            fputcsv($file, ['Name:', $studentFullName]);
            fputcsv($file, ['Admission Number:', $student->admission_number]);
            
            $className = $student->currentRank->stream ? 
                $student->currentRank->name . ' ' . $student->currentRank->stream->name : 
                $student->currentRank->name;
                
            fputcsv($file, ['Class:', $className]);
            fputcsv($file, ['Report Date:', now()->format('Y-m-d H:i:s')]);
            fputcsv($file, []);

            // Summary
            fputcsv($file, ['FEE SUMMARY']);
            fputcsv($file, ['Total Expected Fees:', 'KSh ' . number_format($totalExpected, 2)]);
            fputcsv($file, ['Total Amount Paid:', 'KSh ' . number_format($totalPaid, 2)]);
            fputcsv($file, ['Outstanding Balance:', 'KSh ' . number_format($totalBalance, 2)]);
            fputcsv($file, ['Payment Progress:', $totalExpected > 0 ? round(($totalPaid / $totalExpected) * 100, 2) . '%' : '0%']);
            fputcsv($file, []);

            // Fee Breakdown
            fputcsv($file, ['FEE BREAKDOWN']);
            fputcsv($file, [
                'Academic Year',
                'Term',
                'Fee Description',
                'Class',
                'Total Amount (KSh)',
                'Paid Amount (KSh)',
                'Balance (KSh)',
                'Status',
                'Due Date'
            ]);

            foreach ($fees as $fee) {
                $feeClassName = $fee->rank->stream ? 
                    $fee->rank->name . ' ' . $fee->rank->stream->name : 
                    $fee->rank->name;

                fputcsv($file, [
                    $fee->academic_year,
                    $fee->term,
                    $fee->description ?? 'School Fees',
                    $feeClassName,
                    number_format($fee->amount, 2),
                    number_format($fee->paid_amount, 2),
                    number_format($fee->balance, 2),
                    ucfirst($fee->status),
                    $fee->due_date
                ]);
            }

            fputcsv($file, []);

            // Payment History
            fputcsv($file, ['PAYMENT HISTORY']);
            fputcsv($file, [
                'Payment Date',
                'Amount (KSh)',
                'Payment Method',
                'Reference Number',
                'Verified By',
                'Notes'
            ]);

            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->payment_date,
                    number_format($payment->amount, 2),
                    strtoupper($payment->payment_method),
                    $payment->reference_number,
                    $payment->verifiedBy->name ?? 'N/A',
                    $payment->notes ?? ''
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    /**
     * Export overview report to CSV
     */
    public function exportOverviewReport()
    {
        $currentYear = now()->year;
        
        // Basic statistics
        $totalExpected = Fee::sum('amount');
        $totalCollected = FeePayment::where('status', 'completed')->sum('amount');
        $totalBalance = Fee::sum('balance');
        $collectionRate = $totalExpected > 0 ? ($totalCollected / $totalExpected) * 100 : 0;

        // Payment methods breakdown
        $paymentMethods = FeePayment::where('status', 'completed')
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get();

        // Monthly collection for current year
        $monthlyCollection = FeePayment::where('status', 'completed')
            ->whereYear('payment_date', $currentYear)
            ->select(
                DB::raw('MONTH(payment_date) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month')
            ->get();

        $fileName = 'fee_overview_report_' . now()->format('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($totalExpected, $totalCollected, $totalBalance, $collectionRate, $paymentMethods, $monthlyCollection) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            // Report Header
            fputcsv($file, ['FEE MANAGEMENT OVERVIEW REPORT']);
            fputcsv($file, ['Generated on:', now()->format('Y-m-d H:i:s')]);
            fputcsv($file, []);

            // Key Statistics
            fputcsv($file, ['KEY STATISTICS']);
            fputcsv($file, ['Total Expected Fees:', 'KSh ' . number_format($totalExpected, 2)]);
            fputcsv($file, ['Total Collected:', 'KSh ' . number_format($totalCollected, 2)]);
            fputcsv($file, ['Outstanding Balance:', 'KSh ' . number_format($totalBalance, 2)]);
            fputcsv($file, ['Collection Rate:', round($collectionRate, 2) . '%']);
            fputcsv($file, []);

            // Payment Methods Breakdown
            fputcsv($file, ['PAYMENT METHODS BREAKDOWN']);
            fputcsv($file, ['Payment Method', 'Amount (KSh)', 'Percentage']);
            
            foreach ($paymentMethods as $method) {
                $percentage = $totalCollected > 0 ? ($method->total / $totalCollected) * 100 : 0;
                fputcsv($file, [
                    strtoupper($method->payment_method),
                    number_format($method->total, 2),
                    round($percentage, 2) . '%'
                ]);
            }
            fputcsv($file, []);

            // Monthly Collection
            fputcsv($file, ['MONTHLY COLLECTION FOR ' . $currentYear]);
            fputcsv($file, ['Month', 'Amount (KSh)']);
            
            for ($month = 1; $month <= 12; $month++) {
                $monthData = $monthlyCollection->firstWhere('month', $month);
                $monthName = Carbon::create()->month($month)->format('F');
                $amount = $monthData ? $monthData->total : 0;
                
                fputcsv($file, [
                    $monthName,
                    number_format($amount, 2)
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}