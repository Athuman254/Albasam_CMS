<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Timetable - All Classes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h1 {
            margin: 5px 0;
            font-size: 16px;
        }

        .header p {
            margin: 3px 0;
            font-size: 10px;
            color: #666;
        }

        .class-section {
            page-break-inside: avoid;
            margin-bottom: 30px;
        }

        .class-title {
            background-color: #333;
            color: white;
            padding: 5px 10px;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 5px 3px;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 9px;
        }

        .time-col {
            width: 60px;
            background-color: #f9f9f9;
            font-size: 8px;
        }

        .subject {
            font-weight: bold;
            color: #000;
            font-size: 9px;
        }

        .teacher {
            font-size: 8px;
            color: #666;
        }

        .empty {
            background-color: #fafafa;
            color: #ccc;
        }

        .break-row td {
            background-color: #e0e0e0;
            font-weight: bold;
            color: #333;
            padding: 4px;
            text-align: center;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 9px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ config('app.name', 'School') }}</h1>
        <p>Complete Timetable - All Classes</p>
        <p>Academic Year: {{ $version->academicYear->name }} | Version: {{ $version->version_name }}</p>
    </div>

    @foreach($classTimetables as $classData)
    <div class="class-section">
        <div class="class-title">{{ $classData['class']->full_name }}</div>

        <table>
            <thead>
                <tr>
                    <th class="time-col">Time</th>
                    @foreach($days as $day)
                    <th>{{ $day }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($periods as $period)
                @if($period->is_break)
                <tr class="break-row">
                    <td class="time-col">
                        <small>{{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }}</small>
                    </td>
                    <td colspan="{{ count($days) }}">
                        {{ $period->period_name }}
                    </td>
                </tr>
                @else
                <tr>
                    <td class="time-col">
                        <strong>{{ Str::replace('Period', 'Lesson', $period->period_name) }}</strong><br>
                        <small>{{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }}</small>
                    </td>
                    @foreach($days as $day)
                    <td>
                        @if(isset($classData['grid'][$day][$period->period_order]) && $classData['grid'][$day][$period->period_order])
                        @php $allocation = $classData['grid'][$day][$period->period_order]; @endphp
                        <div class="subject">{{ $allocation->subject->name ?? 'N/A' }}</div>
                        <div class="teacher">{{ $allocation->teacher->name ?? 'No Teacher' }}</div>
                        @else
                        <span class="empty">-</span>
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

    <div class="footer">
        <p>Generated on {{ now()->format('d M Y, H:i') }}</p>
    </div>
</body>

</html>