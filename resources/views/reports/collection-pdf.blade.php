<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Fee Collection Report</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #444;
            padding-bottom: 10px;
        }

        .school-info {
            float: left;
            width: 60%;
        }

        .school-name {
            font-size: 18px;
            font-weight: bold;
            color: #1a237e;
            margin-bottom: 5px;
        }

        .report-title {
            float: right;
            width: 35%;
            text-align: right;
        }

        .report-title h2 {
            margin: 0;
            color: #555;
            font-size: 16px;
        }

        .report-title p {
            margin: 5px 0 0;
            color: #777;
            font-size: 10px;
        }

        .clear {
            clear: both;
        }

        .summary-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }

        .summary-item {
            display: inline-block;
            width: 30%;
            vertical-align: top;
        }

        .summary-label {
            font-size: 9px;
            color: #777;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #f1f3f5;
            color: #495057;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }

        .text-end {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .text-success {
            color: #28a745;
        }

        .badge {
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 8px;
            text-transform: uppercase;
        }

        .bg-light {
            background-color: #f8f9fa;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            font-size: 9px;
            color: #999;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 5px;
        }

        .page-number:after {
            content: counter(page);
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="school-info">
            <div class="school-name">{{ $institution->name ?? 'SKAASS SCHOOL MANAGEMENT SYSTEM' }}</div>
            <div>{{ $institution->address }}</div>
            <div>{{ $institution->email }} | {{ $institution->phone }}</div>
            @if($institution->website)<div>{{ $institution->website }}</div>@endif
        </div>
        <div class="report-title">
            <h2>FEE COLLECTION REPORT</h2>
            <p>Generated: {{ now()->format('d M Y, H:i') }}</p>
            <p>Period: {{ $summary['period'] }}</p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="summary-box">
        <div class="summary-item">
            <div class="summary-label">Total Amount Collected</div>
            <div class="summary-value">KSh {{ number_format($summary['total_amount'], 2) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Total Transactions</div>
            <div class="summary-value">{{ number_format($summary['total_transactions']) }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-label">Active Filters</div>
            <div class="summary-value" style="font-size: 10px;">
                Method: {{ $filters['payment_method'] ? strtoupper($filters['payment_method']) : 'ALL' }}
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="12%">Date</th>
                <th width="20%">Student Name</th>
                <th width="10%">Adm No</th>
                <th width="15%">Class</th>
                <th width="12%">Method</th>
                <th width="18%">Reference</th>
                <th width="13%" class="text-end">Amount (KSh)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($collections as $payment)
            @php
            $className = $payment->fee->rank->stream ?
            $payment->fee->rank->name . ' ' . $payment->fee->rank->stream->name :
            $payment->fee->rank->name;
            $studentName = $payment->student->first_name . ' ' . ($payment->student->middle_name ? $payment->student->middle_name . ' ' : '') . $payment->student->last_name;
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</td>
                <td><span class="fw-bold">{{ $studentName }}</span></td>
                <td>{{ $payment->student->admission_number ?? 'N/A' }}</td>
                <td>{{ $className }}</td>
                <td>{{ strtoupper($payment->payment_method) }}</td>
                <td><small>{{ $payment->reference_number }}</small></td>
                <td class="text-end fw-bold">{{ number_format($payment->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-light">
                <td colspan="6" class="text-end fw-bold">GRANT TOTAL:</td>
                <td class="text-end fw-bold text-success" style="font-size: 12px; border-top: 2px solid #444;">{{ number_format($summary['total_amount'], 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Page <span class="page-number"></span> | This is a computer generated report.
    </div>
</body>

</html>