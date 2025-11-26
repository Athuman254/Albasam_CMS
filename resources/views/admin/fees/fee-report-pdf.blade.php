<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fee Report - {{ $className }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #000000;
        }
        .school-header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .school-header h1 {
            margin: 0 0 5px 0;
            color: #333;
            font-size: 24px;
            font-weight: bold;
        }
        .school-header .school-motto {
            font-style: italic;
            color: #666;
            margin: 3px 0;
            font-size: 11px;
        }
        .school-header .school-details {
            color: #555;
            margin: 3px 0;
            font-size: 10px;
        }
        .report-title {
            text-align: center;
            margin: 5px 0 15px 0;
        }
        .report-title h2 {
            margin: 0;
            color: #2c3e50;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .report-details {
            margin: 15px 0 20px 0;
            padding: 0;
            font-size: 11px;
            color: #333;
            line-height: 1.6;
        }
        .report-details p {
            margin: 3px 0;
        }
        .report-details strong {
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th {
            background-color: #ffffff;
            color: #2c3e50;
            border: 1px solid #34495e;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            border-bottom: 2px solid #2c3e50;
        }
        td {
            border: 1px solid #dee2e6;
            padding: 6px;
            font-size: 10px;
            color: #000000;
        }
        .footer {
            margin-top: 25px;
            text-align: center;
            color: #666;
            font-size: 9px;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .totals-row {
            border-top: 2px solid #333;
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .text-end {
            text-align: right;
        }
        .school-stamp {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #666;
        }
        .no-records {
            text-align: center;
            padding: 30px;
            color: #666;
            background-color: #f8f9fa;
            border-radius: 5px;
            margin: 20px 0;
        }
        .no-records h3 {
            margin: 0 0 8px 0;
            color: #6c757d;
            font-size: 14px;
        }
        .no-records p {
            margin: 0;
            font-size: 11px;
        }
        @media print {
            body {
                margin: 0;
                padding: 15px;
                color: #000000;
            }
            .school-header {
                margin-bottom: 8px;
            }
            td {
                color: #000000 !important;
            }
            th {
                background-color: #ffffff !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <!-- School Information Header -->
    <div class="school-header">
        <h1>{{ config('school.name', 'SCHOOL MANAGEMENT SYSTEM') }}</h1>
        
        <!-- Report Title - Moved below school name -->
        <div class="report-title">
            <h2>FEE COLLECTION REPORT</h2>
        </div>
        
        @if(config('school.motto'))
            <div class="school-motto">"{{ config('school.motto') }}"</div>
        @endif
        <div class="school-details">
            @if(config('school.address'))
                {{ config('school.address') }} |
            @endif
            @if(config('school.phone'))
                Tel: {{ config('school.phone') }} |
            @endif
            @if(config('school.email'))
                Email: {{ config('school.email') }} |
            @endif
            @if(config('school.website'))
                Website: {{ config('school.website') }}
            @endif
        </div>
    </div>

    <!-- Report Details - Left aligned in sentence form -->
    <div class="report-details">
        <p>
            This report shows fee collection details for 
            <strong>{{ $className }}</strong> 
            @if($term && $term !== 'All Terms')
                for <strong>{{ $term }}</strong>
            @endif
            . Zero balance students are <strong>{{ $includeZeroBalance ? 'included' : 'excluded' }}</strong>. 
            Report generated on <strong>{{ $generatedDate }}</strong>.
        </p>
    </div>

    @if(count($reportData) > 0)
        <table>
            <thead>
                <tr>
                    <th>Admission Number</th>
                    <th>Student Name</th>
                    <th>Class</th>
                    <th>Total Amount (KSh)</th>
                    <th>Paid Amount (KSh)</th>
                    <th>Balance (KSh)</th>
                    <th>Collection Rate</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $index => $data)
                <tr>
                    <td>{{ $data['admission_number'] }}</td>
                    <td>{{ $data['student_name'] }}</td>
                    <td>{{ $data['class_name'] }}</td>
                    <td style="text-align: right;">{{ number_format($data['total_amount'], 2) }}</td>
                    <td style="text-align: right;">{{ number_format($data['paid_amount'], 2) }}</td>
                    <td style="text-align: right; color: #000000;">
                        {{ number_format($data['balance'], 2) }}
                    </td>
                    <td style="text-align: center; color: #000000;">
                        {{ $data['collection_rate'] }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td colspan="3" class="text-end"><strong>GRAND TOTALS:</strong></td>
                    <td style="text-align: right;"><strong>{{ number_format($summary['total_amount'], 2) }}</strong></td>
                    <td style="text-align: right;"><strong>{{ number_format($summary['total_paid'], 2) }}</strong></td>
                    <td style="text-align: right; color: #000000;">
                        <strong>{{ number_format($summary['total_balance'], 2) }}</strong>
                    </td>
                    <td style="text-align: center; color: #000000;">
                        <strong>{{ $summary['collection_rate'] }}%</strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-records">
            <h3>No Records Found</h3>
            <p>No fee records found for the selected criteria. Please adjust your filters and try again.</p>
        </div>
    @endif

    <div class="school-stamp">
        <div style="margin-bottom: 8px;">
            <strong>Official School Stamp</strong>
        </div>
        <div>Generated by: {{ config('school.name', 'School Management System') }}</div>
        <div>Date: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
        @if(config('school.principal'))
            <div>Principal: {{ config('school.principal') }}</div>
        @endif
    </div>

    <div class="footer">
        <p>
            Generated by {{ config('school.name', 'School Management System') }} | 
            Total Students: {{ count($reportData) }} | 
            Page 1 of 1 | 
            Generated on: {{ $generatedDate }}
        </p>
    </div>
</body>
</html>