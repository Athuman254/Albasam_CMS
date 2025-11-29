<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Timetable - {{ $class->name }}</title>
    <style>
        @page {
            margin: 10mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
        }

        .header h1 {
            margin: 0 0 4px 0;
            font-size: 18px;
        }

        .header p {
            margin: 2px 0;
            font-size: 11px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 5px 4px;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            font-size: 10px;
        }

        .time-col {
            width: 75px;
            background-color: #f9f9f9;
            font-size: 9px;
        }

        .subject {
            font-weight: bold;
            color: #000;
            font-size: 11px;
            margin-bottom: 2px;
        }

        .teacher {
            font-size: 9px;
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
            padding: 6px;
            text-align: center;
            text-transform: uppercase;
        }

        .footer {
            margin-top: 12px;
            text-align: center;
            font-size: 9px;
            color: #999;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ config('app.name', 'School') }}</h1>
        <p>Class Timetable: <strong>{{ $class->full_name }}</strong></p>
        <p>Academic Year: {{ $version->academicYear->name }} | Version: {{ $version->version_name }}</p>
    </div>

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
                    <small>{{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($period->end_time)->format('H:i') }}</small>
                </td>
                <td colspan="{{ count($days) }}">
                    {{ $period->period_name }}
                </td>
            </tr>
            @else
            <tr>
                <td class="time-col">
                    <strong>{{ Str::replace('Period', 'Lesson', $period->period_name) }}</strong><br>
                    <small>{{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($period->end_time)->format('H:i') }}</small>
                </td>
                @foreach($days as $day)
                <td>
                    @if(isset($timetableGrid[$day][$period->period_order]) && $timetableGrid[$day][$period->period_order])
                    @php $allocation = $timetableGrid[$day][$period->period_order]; @endphp
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

    <div class="footer">
        <p>Generated on {{ now()->format('d M Y, H:i') }}</p>
    </div>
</body>

</html>