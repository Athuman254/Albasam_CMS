<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Fee Statement - {{ $student->admission_number }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }

        .header p {
            margin: 2px 0;
        }

        .student-info {
            margin-bottom: 20px;
            width: 100%;
        }

        .student-info td {
            padding: 5px;
            vertical-align: top;
        }

        .summary-box {
            background-color: #f5f5f5;
            padding: 10px;
            border: 1px solid #ddd;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .status-paid {
            color: green;
        }

        .status-pending {
            color: orange;
        }

        .status-partial {
            color: blue;
        }

        .status-overdue {
            color: red;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $school['name'] }}</h1>
        <p>{{ $school['address'] }}</p>
        <p>Tel: {{ $school['phone'] }} | Email: {{ $school['email'] }}</p>
        <h2 style="margin-top: 10px; font-size: 16px;">STUDENT FEE STATEMENT</h2>
    </div>

    <table class="student-info" style="border: none;">
        <tr style="border: none;">
            <td style="border: none; width: 50%;">
                <strong>Name:</strong> {{ $student->full_name }}<br>
                <strong>Admission No:</strong> {{ $student->admission_number }}<br>
                <strong>Class:</strong> {{ $student->rank->name ?? 'N/A' }}
            </td>
            <td style="border: none; width: 50%; text-align: right;">
                <strong>Date Generated:</strong> {{ $generated_at }}<br>
                <strong>Academic Year:</strong> {{ now()->year }}
            </td>
        </tr>
    </table>

    <div class="summary-box">
        <table style="margin: 0; border: none;">
            <tr style="border: none;">
                <td style="border: none;"><strong>Total Billed:</strong> KSh {{ number_format($summary['total_fees'], 2) }}</td>
                <td style="border: none;"><strong>Total Paid:</strong> KSh {{ number_format($summary['total_paid'], 2) }}</td>
                <td style="border: none;"><strong>Outstanding Balance:</strong> <span style="color: black">KSh {{ number_format($summary['balance'], 2) }}</span></td>
            </tr>
        </table>
    </div>

    <h3>Fee Breakdown</h3>
    <table>
        <thead>
            <tr>
                <th>Date/Term</th>
                <th>Description</th>
                <th class="text-right">Amount (KSh)</th>
                <th class="text-right">Paid (KSh)</th>
                <th class="text-right">Balance (KSh)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($fees as $fee)
            <tr>
                <td>{{ $fee->academic_year }} - Term {{ $fee->term }}</td>
                <td>
                    {{ ucfirst(str_replace('_', ' ', $fee->fee_type)) }}
                    @if($fee->description) <br><small>{{ $fee->description }}</small> @endif
                </td>
                <td class="text-right">{{ number_format($fee->amount, 2) }}</td>
                <td class="text-right">{{ number_format($fee->paid_amount, 2) }}</td>
                <td class="text-right">{{ number_format($fee->balance, 2) }}</td>
                <td>
                    <span class="status-{{ $fee->status }}">{{ ucfirst($fee->status) }}</span>
                </td>
            </tr>
            @endforeach
            <tr style="background-color: #f9f9f9; font-weight: bold;">
                <td colspan="2" class="text-right">Totals</td>
                <td class="text-right">{{ number_format($summary['total_fees'], 2) }}</td>
                <td class="text-right">{{ number_format($summary['total_paid'], 2) }}</td>
                <td class="text-right">{{ number_format($summary['balance'], 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <h3>Payment History</h3>
    @if($payments->isEmpty())
    <p>No payments recorded.</p>
    @else
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Method</th>
                <th class="text-right">Amount (KSh)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $payment)
            <tr>
                <td>{{ $payment->payment_date ? $payment->payment_date->format('d M Y') : 'N/A' }}</td>
                <td>{{ $payment->reference_number }}</td>
                <td>{{ ucfirst($payment->payment_method) }}</td>
                <td class="text-right">{{ number_format($payment->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        <p>This is a computer-generated document and does not require a signature.</p>
    </div>
</body>

</html>