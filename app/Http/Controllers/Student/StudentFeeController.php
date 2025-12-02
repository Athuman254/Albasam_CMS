<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\FeePayment;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class StudentFeeController extends Controller
{
    /**
     * Display a listing of the student's fees and payments.
     */
    public function index()
    {
        $student = Auth::guard('student')->user();

        // Load relationships needed for summary
        $student->load(['fees', 'feePayments']);

        // Fetch fees ordered by date (show all fees including carried_over for transparency)
        $fees = Fee::where('student_id', $student->id)
            ->orderBy('academic_year', 'desc')
            ->orderBy('term', 'desc')
            ->orderBy('due_date', 'desc')
            ->get()
            ->map(function ($fee) {
                return [
                    'id' => $fee->id,
                    'fee_type' => ucfirst(str_replace('_', ' ', $fee->fee_type)),
                    'amount' => $fee->amount,
                    'paid_amount' => $fee->paid_amount,
                    'balance' => $fee->balance,
                    'academic_year' => $fee->academic_year,
                    'term' => $fee->term,
                    'due_date' => $fee->due_date ? $fee->due_date->format('M j, Y') : 'N/A',
                    'status' => $fee->status,
                    'description' => $fee->description,
                ];
            });

        // Fetch payments ordered by date
        $payments = FeePayment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->orderBy('payment_date', 'desc')
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'payment_method' => ucfirst($payment->payment_method),
                    'reference_number' => $payment->reference_number,
                    'payment_date' => $payment->payment_date ? $payment->payment_date->format('M j, Y') : 'N/A',
                    'notes' => $payment->notes,
                ];
            });

        return Inertia::render('Student/Fees/Index', [
            'summary' => $student->fee_summary,
            'fees' => $fees,
            'payments' => $payments,
        ]);
    }

    /**
     * Download fee statement as PDF.
     */
    public function downloadStatement()
    {
        $student = Auth::guard('student')->user();
        $student->load(['rank', 'fees', 'feePayments']);

        $fees = Fee::where('student_id', $student->id)
            ->orderBy('academic_year', 'desc')
            ->orderBy('term', 'desc')
            ->orderBy('due_date', 'desc')
            ->get();

        $payments = FeePayment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->orderBy('payment_date', 'desc')
            ->get();

        // Calculate summary excluding carried_over fees
        $activeFees = $fees->where('status', '!=', 'carried_over');
        $totalFees = $activeFees->sum('amount');
        $totalPaid = $payments->sum('amount');
        $balance = $totalFees - $totalPaid;

        $summary = [
            'total_fees' => $totalFees,
            'total_paid' => $totalPaid,
            'balance' => $balance,
        ];

        $data = [
            'student' => $student,
            'fees' => $fees,
            'payments' => $payments,
            'summary' => $summary,
            'school' => [
                'name' => config('app.name', 'School Management System'),
                'address' => 'P.O. Box 123, Nairobi', // Replace with actual settings if available
                'phone' => '+254 700 000 000',
                'email' => 'info@school.com',
                'logo' => null // Handle logo if needed
            ],
            'generated_at' => now()->format('d M Y, h:i A'),
        ];

        $pdf = Pdf::loadView('student.fees.statement', $data);

        return $pdf->download('Fee_Statement_' . $student->admission_number . '.pdf');
    }
}
