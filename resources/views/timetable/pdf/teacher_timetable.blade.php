<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Timetable - {{ $teacher->name }}</title>
    <style>
        @page {
            margin: 10mm;
            size: a4 landscape;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0 0 5px 0;
            font-size: 24px;
            font-weight: bold;
            color: #000;
        }

        .header .subtitle {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 3px;
        }

        .header .details {
            font-size: 12px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #999;
            padding: 8px 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        th {
            background-color: #ffffff;
            font-weight: bold;
            font-size: 12px;
            color: #000;
        }

        .time-col {
            width: 100px;
            background-color: #ffffff;
            font-size: 10px;
        }

        .time-col b {
            font-size: 11px;
            display: block;
            margin-bottom: 2px;
        }

        .subject {
            font-weight: bold;
            color: #000;
            font-size: 13px;
            margin-bottom: 3px;
        }

        .class-name {
            font-size: 10px;
            color: #666;
        }

        .break-row {
            background-color: #e9ecef;
            font-weight: bold;
            color: #000;
            font-size: 12px;
            letter-spacing: 2px;
            height: 30px;
        }

        .empty {
            color: #dee2e6;
        }

        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 10px;
            color: #888;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ config('app.name', 'Skaass SMS') }}</h1>
        <div class="subtitle">Teacher Timetable: {{ $teacher->name }}</div>
        <div class="details">Academic Year: {{ $version->academicYear->name }} | Version: {{ $version->version_name }}</div>
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
            @php
            $isFullWidthBreak = $period->is_break && in_array(strtoupper($period->period_name), ['SHORT BREAK', 'LUNCH BREAK', 'GAMES/CLUBS', 'GAMES', 'CLUBS']);
            @endphp

            @if($isFullWidthBreak)
            <tr class="break-row">
                <td>
                    <small>{{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($period->end_time)->format('H:i') }}</small>
                </td>
                <td colspan="{{ count($days) }}">
                    {{ strtoupper($period->period_name) }}
                </td>
            </tr>
            @else
            <tr>
                <td class="time-col">
                    <b>{{ Str::startsWith($period->period_name, 'Period') ? Str::replaceFirst('Period', 'Lesson', $period->period_name) : $period->period_name }}</b>
                    {{ \Carbon\Carbon::parse($period->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($period->end_time)->format('H:i') }}
                </td>
                @foreach($days as $day)
                <td>
                    @if(isset($timetableGrid[$day][$period->period_order]) && $timetableGrid[$day][$period->period_order])
                    @php $allocation = $timetableGrid[$day][$period->period_order]; @endphp
                    <div class="subject">{{ $allocation->subject->name ?? ($allocation->subject->subject_name ?? 'N/A') }}</div>
                    <div class="class-name">{{ $allocation->class->name ?? 'No Class' }}</div>
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
        Generated on {{ now()->format('d M Y, H:i') }}
    </div>
</body>

</html>