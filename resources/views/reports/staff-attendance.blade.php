<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Attendance Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            color: #000;
            background: white;
            font-size: 12px;
            position: relative;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            height: 100vh;
        }

        .page-margin {
            margin-top: 2%;
            border: 8px solid #1a73e8;
            border-radius: 20px;
            height: 96%;
            position: relative;
            background: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            margin-left: 1%;
            margin-right: 1%;
        }

        .watermark {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            z-index: 0;
            opacity: 0.08;
        }

        .watermark-text {
            font-size: 60px;
            font-weight: bold;
            color: #000000;
            transform: rotate(-45deg);
            white-space: nowrap;
            text-align: center;
        }

        .report-container {
            width: 100%;
            margin: 0 auto;
            background: transparent;
            position: relative;
            z-index: 1;
            padding: 20px;
            flex: 1;
        }

        /* HEADER */
        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 5px 0;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            position: relative;
        }

        .school-info {
            width: 100%;
            text-align: center;
            padding: 0 20px;
        }

        .school-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            margin-bottom: 5px;
        }

        .school-details {
            font-size: 10px;
            color: #000;
            line-height: 1.3;
        }

        .report-title {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            margin: 10px 0;
            padding: 8px 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            text-align: center;
        }

        /* SUMMARY SECTION */
        .summary-boxes {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-collapse: separate;
            border-spacing: 10px 0;
        }

        .summary-box {
            display: table-cell;
            text-align: center;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            width: 25%;
        }

        .summary-box .label {
            font-size: 10px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .summary-box .value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .summary-box.present .value {
            color: #10b981;
        }

        .summary-box.late .value {
            color: #f5f90b;
        }

        .summary-box.half-day .value {
            color: #3b82f6;
        }

        .summary-box.absent .value {
            color: #ef4444;
        }

        /* TABLE */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10px;
        }

        th {
            background-color: #f0f0f0;
            color: #000;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #000;
            text-transform: uppercase;
        }

        td {
            padding: 6px 8px;
            border: 1px solid #000;
        }

        .status-badge {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }

        .status-present {
            color: #065f46;
        }

        .status-late {
            color: #92400e;
        }

        .status-half-day {
            color: #1e40af;
        }

        .status-absent {
            color: #991b1b;
        }

        .footer {
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid #ddd;
        }

        .generated-date {
            color: #666;
            font-size: 9px;
            text-align: right;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-style: italic;
            border: 1px solid #ddd;
        }
    </style>
</head>

<body>
    <div class="page-margin">
        <!-- WATERMARK -->
        <div class="watermark">
            <div class="watermark-text">
                ALBASAM COMPREHENSIVE SCHOOL<br>
                STAFF ATTENDANCE REPORT
            </div>
        </div>

        <div class="report-container">
            <!-- HEADER -->
            <div class="header">
                <div class="school-info">
                    <div class="school-name">ALBASAM COMPREHENSIVE SCHOOL</div>
                    <div class="school-details">
                        13-80102 MOMBASA<br>
                        info@albasamcomprehensive.sc.ke<br>
                        Tel: +254 700 000 000
                    </div>
                </div>
            </div>

            <div class="report-title">
                STAFF ATTENDANCE REPORT ({{ $start_date }} - {{ $end_date }})
            </div>

            <!-- SUMMARY -->
            <div class="summary-boxes">
                <div class="summary-box present">
                    <div class="label">Present (On-Time)</div>
                    <div class="value">{{ $summary['present'] - $summary['late'] }}</div>
                </div>
                <div class="summary-box late">
                    <div class="label">Present (Late)</div>
                    <div class="value">{{ $summary['late'] }}</div>
                </div>
                <div class="summary-box half-day">
                    <div class="label">Half Day</div>
                    <div class="value">{{ $summary['half_day'] }}</div>
                </div>
                <div class="summary-box absent">
                    <div class="label">Absent</div>
                    <div class="value">{{ $summary['absent'] }}</div>
                </div>
            </div>

            <!-- TABLE -->
            @if($attendances->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%;">Staff #</th>
                        <th style="width: 25%;">Employee Name</th>
                        <th style="width: 15%;">Date</th>
                        <th style="width: 10%;">Clock In</th>
                        <th style="width: 10%;">Clock Out</th>
                        <th style="width: 10%;">Hours</th>
                        <th style="width: 20%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->employee->staff_number }}</td>
                        <td>{{ $attendance->employee->full_name }}</td>
                        <td>{{ $attendance->date->format('M d, Y') }}</td>
                        <td>{{ $attendance->formatted_clock_in ?? '-' }}</td>
                        <td>{{ $attendance->formatted_clock_out ?? '-' }}</td>
                        <td>{{ $attendance->total_hours ? number_format($attendance->total_hours, 2) . 'h' : '-' }}</td>
                        <td>
                            @if($attendance->status === 'present' && $attendance->is_late)
                            <span class="status-badge status-late">Present (Late)</span>
                            @elseif($attendance->status === 'present')
                            <span class="status-badge status-present">Present</span>
                            @elseif($attendance->status === 'half_day')
                            <span class="status-badge status-half-day">Half Day</span>
                            @elseif($attendance->status === 'absent')
                            <span class="status-badge status-absent">Absent</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="no-data">
                No attendance records found for the selected period.
            </div>
            @endif

            <div class="footer">
                <div class="generated-date">
                    Report Generated on: {{ $generated_at }}
                </div>
            </div>
        </div>
    </div>
</body>

</html>