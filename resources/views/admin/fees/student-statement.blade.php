<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fee Statement - {{ $student->first_name }} {{ $student->last_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            .container { max-width: 100% !important; }
            .card { border: none !important; box-shadow: none !important; }
            body { font-size: 10px; margin: 0; padding: 3px; }
            .table { font-size: 9px; }
            .summary-box { padding: 6px; margin-bottom: 8px; }
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            font-size: 11px; 
            background: white;
            line-height: 1.1;
            color: #000000 !important; /* Changed to black */
        }
        
        .header { 
            border-bottom: 1px solid #e0e0e0; 
            margin-bottom: 12px; 
            padding-bottom: 8px; 
        }
        
        .summary-box { 
            background: #f8f9fa; 
            padding: 8px; 
            border-radius: 4px; 
            margin-bottom: 12px; 
            border: 1px solid #dee2e6; 
        }
        
        .text-success { color: #059669 !important; }
        .text-danger { color: #dc2626 !important; }
        .text-warning { color: #d97706 !important; }
        .text-primary { color: #2563eb !important; }
        
        .badge { font-size: 0.65em; font-weight: 500; }
        .table th { 
            background-color: #f1f5f9; 
            font-weight: 600; 
            padding: 3px 4px; 
            font-size: 9px;
        }
        .table td { 
            padding: 3px 4px; 
            font-size: 9px;
        }
        
        .school-header { 
            text-align: center; 
            margin-bottom: 12px; 
            padding: 8px 0; 
            border-bottom: 1px solid #334155;
        }
        
        .school-name { 
            font-size: 16px; 
            font-weight: 700; 
            color: #1e293b; 
            margin-bottom: 2px; 
            letter-spacing: -0.025em;
        }
        
        .school-address { 
            color: #64748b; 
            margin-bottom: 2px; 
            font-size: 10px; 
            font-weight: 400;
        }
        
        .school-contacts { 
            color: #64748b; 
            font-size: 9px; 
            font-weight: 400;
        }
        
        .statement-title { 
            text-align: center; 
            font-size: 14px; 
            font-weight: 700; 
            margin: 8px 0; 
            color: #1e293b;
            letter-spacing: -0.025em;
        }
        
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 50px;
            color: rgba(0,0,0,0.05);
            z-index: -1;
            font-weight: bold;
            opacity: 0.7;
        }
        
        .compact-row { margin-bottom: 4px; }
        .compact-text { margin-bottom: 1px; font-size: 10px;text-align: left; }

        
        .transaction-table th, .transaction-table td {
            font-size: 8.5px;
            padding: 2px 3px;
        }
        
        .footer-section {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
        }
        
        .student-name {
            font-size: 13px;
            font-weight: 600;
            color: #1e40af;
        }
        
        .summary-value {
            font-size: 11px;
            font-weight: 600;
            text-align: left;
        }
        
        .table-smaller {
            font-size: 8.5px;
        }
        
        .card-header-custom {
            padding: 6px 8px;
            font-size: 10px;
            font-weight: 600;
        }
        
        /* New styles for improved organization */
        .student-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .student-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .student-info {
            padding: 15px;
        }
        
        .student-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #2563eb;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            margin-right: 15px;
        }
        
        .student-details {
            flex: 1;
        }
        
        .student-name-large {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .student-meta {
            color: #6c757d;
            font-size: 14px;
        }
        
        .student-status {
            text-align: right;
        }
        
        .status-badge {
            font-size: 14px;
            padding: 6px 12px;
        }
        
        .financial-summary {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .summary-item {
            text-align: center;
            padding: 10px;
        }
        
        .summary-label {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .summary-amount {
            font-size: 20px;
            font-weight: 700;
        }
        
        .progress-container {
            margin-top: 15px;
        }
        
        .progress {
            height: 10px;
        }
        
        .search-container {
            margin-bottom: 20px;
        }
        
        .search-input {
            border-radius: 8px 0 0 8px;
        }
        
        .search-btn {
            border-radius: 0 8px 8px 0;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .data-section {
            margin-bottom: 25px;
        }
        
        .no-results {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .no-results i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #adb5bd;
        }
    </style>
</head>
<body>
    <div class="watermark">OFFICIAL COPY</div>
    
    <div class="container-fluid">
        <!-- School Header -->
        <div class="school-header">
            <div class="school-name">{{ $institution->name ?? 'YOUR SCHOOL NAME' }}</div>
            <div class="school-address">
                {{ $institution->physical_address ?? 'School Address' }}, 
                {{ $institution->city ?? 'City' }}, 
                {{ $institution->country ?? 'Country' }}
            </div>
            <div class="school-contacts">
                @if($institution->phone)
                    Phone: {{ $institution->phone }} |
                @endif
                @if($institution->email)
                    Email: {{ $institution->email }} |
                @endif
                @if($institution->tax_identification_pin)
                    PIN: {{ $institution->tax_identification_pin }}
                @endif
            </div>
        </div>

        <!-- Statement Title -->
        <div class="statement-title">STUDENT FEE STATEMENT</div>

        <!-- Student Information -->
        <div class="header">
            <div class="row compact-row">
                <div class="col-md-8">
                    <div class="student-name mb-1">{{ $student->first_name }} {{ $student->last_name }}</div>
                    <p class="mb-0 compact-text"><strong>Admission No:</strong> {{ $student->admission_number }}</p>
                    <p class="mb-0 compact-text"><strong>Class:</strong> {{ $student->current_rank }}</p>
                    <p class="mb-0 compact-text"><strong>Period:</strong> 
                        @if($academicYear)
                            {{ $academicYear }}
                            @if($term)
                                - Term {{ $term }}
                            @endif
                        @else
                            All Periods
                        @endif
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <p class="mb-0 compact-text"><strong>Date:</strong> {{ now()->format('M j, Y') }}</p>
                    <p class="mb-0 compact-text"><strong>Statement No:</strong> STMT-{{ $student->id }}-{{ date('Ymd') }}</p>
                    <p class="mb-0 compact-text"><strong>By:</strong> {{ auth()->user()->name ?? 'System' }}</p>
                </div>
            </div>
        </div>

        <!-- Print Button -->
        <div class="no-print mb-2 text-center">
            <button onclick="window.print()" class="btn btn-primary btn-sm me-1">
                <i class="fas fa-print me-1"></i> Print
            </button>
            <button onclick="window.close()" class="btn btn-secondary btn-sm">
                <i class="fas fa-times me-1"></i> Close
            </button>
        </div>

        <!-- Summary -->
        <div class="summary-box">
            <div class="row text-center">
                <div class="col-md-3 border-end">
                    <div class="compact-text"><strong>Total Fees Due</strong></div>
                    <div class="summary-value text-primary">Ksh {{ number_format($totalFees, 2) }}</div>
                </div>
                <div class="col-md-3 border-end">
                    <div class="compact-text"><strong>Total Paid</strong></div>
                    <div class="summary-value text-success">Ksh {{ number_format($totalPaid, 2) }}</div>
                </div>
                <div class="col-md-3 border-end">
                    <div class="compact-text"><strong>Balance</strong></div>
                    <div class="summary-value {{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                        Ksh {{ number_format($balance, 2) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="compact-text"><strong>Status</strong></div>
                    <span class="badge {{ $balance == 0 ? 'bg-success' : ($balance == $totalFees ? 'bg-danger' : 'bg-warning') }}">
                        {{ $balance == 0 ? 'PAID' : ($balance == $totalFees ? 'PENDING' : 'PARTIAL') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Single Transaction Table -->
        <div class="card border-0">
            <div class="card-header-custom bg-light">
                <i class="fas fa-exchange-alt me-1"></i>FEE TRANSACTIONS & BALANCE HISTORY
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0 table-smaller">
                        <thead class="table-light">
                            <tr>
                                <th width="3%">#</th>
                                <th width="8%">Date</th>
                                <th width="10%">Type</th>
                                <th width="12%">Fee Type</th>
                                <th width="8%">Year</th>
                                <th width="5%">Term</th>
                                <th width="10%" class="text-end">Amount</th>
                                <th width="10%" class="text-end">Payment</th>
                                <th width="12%" class="text-end">Balance</th>
                                <th width="10%">Reference</th>
                                <th width="7%">Method</th>
                                <th width="5%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $runningBalance = 0;
                                $transactionNumber = 1;
                            @endphp
                            
                            <!-- Initial Balance Row -->
                            <tr>
                                <td>{{ $transactionNumber++ }}</td>
                                <td>{{ now()->subDays(1)->format('M j, Y') }}</td>
                                <td><span class="badge bg-secondary">OPENING</span></td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td class="text-end">-</td>
                                <td class="text-end">-</td>
                                <td class="text-end fw-bold">Ksh {{ number_format($runningBalance, 2) }}</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                            
                            <!-- Fee Assessment Transactions -->
                            @foreach($fees as $fee)
                                @php
                                    $runningBalance += $fee->amount;
                                @endphp
                                <tr>
                                    <td>{{ $transactionNumber++ }}</td>
                                    <td>{{ $fee->created_at ? $fee->created_at->format('M j, Y') : 'N/A' }}</td>
                                    <td><span class="badge bg-info">FEE</span></td>
                                    <td>{{ $feeTypes[$fee->fee_type] ?? ucfirst($fee->fee_type) }}</td>
                                    <td>{{ $fee->academic_year }}</td>
                                    <td>T{{ $fee->term }}</td>
                                    <td class="text-end text-danger fw-bold">Ksh {{ number_format($fee->amount, 2) }}</td>
                                    <td class="text-end">-</td>
                                    <td class="text-end fw-bold {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                                        Ksh {{ number_format($runningBalance, 2) }}
                                    </td>
                                    <td>FEE-{{ $fee->id }}</td>
                                    <td>-</td>
                                    <td>
                                        <span class="badge {{ $fee->balance == 0 ? 'bg-success' : ($fee->paid_amount > 0 ? 'bg-warning' : 'bg-secondary') }}">
                                            {{ $fee->balance == 0 ? 'Paid' : ($fee->paid_amount > 0 ? 'Partial' : 'Due') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                            
                            <!-- Payment Transactions -->
                            @if($payments->count() > 0)
                                @foreach($payments as $payment)
                                    @php
                                        $runningBalance -= $payment->amount;
                                        $allocatedFees = [];
                                        if (isset($paymentAllocations) && is_array($paymentAllocations)) {
                                            $allocatedFees = array_filter($paymentAllocations, function($allocation) use ($payment) {
                                                return isset($allocation['payment_id']) && $allocation['payment_id'] == $payment->id;
                                            });
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $transactionNumber++ }}</td>
                                        <td>{{ $payment->payment_date ? $payment->payment_date->format('M j, Y') : 'N/A' }}</td>
                                        <td><span class="badge bg-success">PAYMENT</span></td>
                                        <td>
                                            @if(!empty($allocatedFees))
                                                @foreach($allocatedFees as $allocation)
                                                    {{ $allocation['fee_type_name'] ?? 'General' }}<br>
                                                @endforeach
                                            @else
                                                General
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($allocatedFees))
                                                @foreach($allocatedFees as $allocation)
                                                    {{ $allocation['academic_year'] ?? '-' }}<br>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($allocatedFees))
                                                @foreach($allocatedFees as $allocation)
                                                    T{{ $allocation['term'] ?? '-' }}<br>
                                                @endforeach
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-end">-</td>
                                        <td class="text-end text-success fw-bold">Ksh {{ number_format($payment->amount, 2) }}</td>
                                        <td class="text-end fw-bold {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                                            Ksh {{ number_format($runningBalance, 2) }}
                                        </td>
                                        <td>{{ $payment->reference_number }}</td>
                                        <td>{{ ucfirst($payment->payment_method) }}</td>
                                        <td>
                                            <span class="badge {{ $payment->status == 'completed' ? 'bg-success' : ($payment->status == 'pending' ? 'bg-warning' : 'bg-danger') }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            
                            <!-- Final Balance Row -->
                            <tr class="table-active fw-bold">
                                <td>{{ $transactionNumber }}</td>
                                <td>{{ now()->format('M j, Y') }}</td>
                                <td><span class="badge bg-primary">CLOSING</span></td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td class="text-end">-</td>
                                <td class="text-end">-</td>
                                <td class="text-end {{ $runningBalance > 0 ? 'text-danger' : 'text-success' }}">
                                    Ksh {{ number_format($runningBalance, 2) }}
                                </td>
                                <td>-</td>
                                <td>-</td>
                                <td>
                                    <span class="badge {{ $runningBalance == 0 ? 'bg-success' : ($runningBalance == $totalFees ? 'bg-danger' : 'bg-warning') }}">
                                        {{ $runningBalance == 0 ? 'PAID' : ($runningBalance == $totalFees ? 'UNPAID' : 'PARTIAL') }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-section">
            <div class="row">
                <div class="col-md-6 text-center">
                    <div class="border-top pt-2">
                        <strong>Student/Parent Signature</strong><br>
                        <div class="mt-1" style="border-bottom: 1px solid #000; width: 120px; margin: 0 auto;"></div>
                        <small class="text-muted">Acknowledgement</small>
                    </div>
                </div>
                <div class="col-md-6 text-center">
                    <div class="border-top pt-2">
                        <strong>School Stamp</strong><br>
                        <div class="mt-1" style="border-bottom: 1px solid #000; width: 120px; margin: 0 auto;"></div>
                        <small class="text-muted">Authorized Signature</small>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-2">
                <small class="text-muted">
                    Computer-generated document • Generated: {{ now()->format('M j, Y g:i A') }}
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // window.onload = function() {
        //     setTimeout(function() {
        //         window.print();
        //     }, 1000);
        // };

        window.onafterprint = function() {
            setTimeout(function() {
                window.close();
            }, 1000);
        };
    </script>
</body>
</html>

