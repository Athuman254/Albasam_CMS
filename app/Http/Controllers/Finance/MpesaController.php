<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Services\MpesaService;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaController extends Controller
{
    protected $mpesaService;

    public function __construct(MpesaService $mpesaService)
    {
        $this->mpesaService = $mpesaService;
    }

    /**
     * Initiate STK Push
     */
    public function initiateStkPush(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'account_reference' => 'required|string', // e.g., Admission Number
            'student_id' => 'nullable|exists:students,id'
        ]);

        $phoneNumber = $request->phone_number;
        $amount = $request->amount;
        $accountReference = $request->account_reference;

        // If student_id is provided, use it to fetch admission number for reference safety
        if ($request->student_id) {
            $student = Student::find($request->student_id);
            if ($student) {
                $accountReference = $student->admission_number;
            }
        }

        try {
            $response = $this->mpesaService->stkPush($phoneNumber, $amount, $accountReference);

            if (isset($response['success']) && $response['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'STK Push initiated successfully. Please check your phone.',
                    'data' => $response
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $response['message'] ?? 'Failed to initiate STK Push.',
                'error' => $response
            ], 400);
        } catch (\Exception $e) {
            Log::error('MpesaController STK Push Exception: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while initiating STK Push: ' . $e->getMessage()
            ], 500);
        }
    }
}
