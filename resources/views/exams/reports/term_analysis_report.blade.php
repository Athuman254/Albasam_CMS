<!DOCTYPE html>
<html>

<head>
    <title>{{ $class->name }} - Term Analysis Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .school-name {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
        }

        .report-title {
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 10px;
        }

        .details {
            margin-bottom: 15px;
        }

        .details table {
            width: 100%;
        }

        .details td {
            padding: 2px;
        }

        table.marks-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 11px;
        }

        table.marks-table th,
        table.marks-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        table.marks-table th {
            background-color: #f0f0f0;
        }

        .text-left {
            text-align: left !important;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="header">
        @if($institution->media->first())
        <img src="{{ $institution->media->first()->getUrl() }}" class="logo">
        @endif
        <div class="school-name">{{ $institution->name }}</div>
        <div>{{ $institution->address }}</div>
        <div class="report-title">TERM SUMMARY REPORT - {{ strtoupper($term) }}</div>
    </div>

    <div class="details">
        <table>
            <tr>
                <td><strong>Class:</strong> {{ $class->name }} {{ $class->stream ? $class->stream->name : '' }}</td>
                <td style="text-align: right;"><strong>Year:</strong> {{ date('Y') }}</td>
            </tr>
            <tr>
                <td><strong>Dates:</strong> {{ $opening_date }} - {{ $closing_date }}</td>
                <td style="text-align: right;"><strong>Generated:</strong> {{ date('d-m-Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <table class="marks-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="10%">Adm No</th>
                <th width="25%" class="text-left">Student Name</th>

                @if(isset($exams['opening']))
                <th width="15%">
                    Opening Exam<br>
                    <small>({{ $exams['opening']->name }})</small>
                </th>
                @endif

                @if(isset($exams['mid']))
                <th width="15%">
                    Mid Term<br>
                    <small>({{ $exams['mid']->name }})</small>
                </th>
                @endif

                @if(isset($exams['end']))
                <th width="15%">
                    End Term<br>
                    <small>({{ $exams['end']->name }})</small>
                </th>
                @endif

                <th width="15%">Average<br>(Op+Mid+End)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row['student']->adm_no }}</td>
                <td class="text-left">{{ $row['student']->name }}</td>

                @if(isset($exams['opening']))
                <td>
                    @if(isset($row['exams']['opening']))
                    {{ $row['exams']['opening']['total'] }}
                    <br><small>({{ round($row['exams']['opening']['percentage']) }}%)</small>
                    @else
                    -
                    @endif
                </td>
                @endif

                @if(isset($exams['mid']))
                <td>
                    @if(isset($row['exams']['mid']))
                    {{ $row['exams']['mid']['total'] }}
                    <br><small>({{ round($row['exams']['mid']['percentage']) }}%)</small>
                    @else
                    -
                    @endif
                </td>
                @endif

                @if(isset($exams['end']))
                <td>
                    @if(isset($row['exams']['end']))
                    {{ $row['exams']['end']['total'] }}
                    <br><small>({{ round($row['exams']['end']['percentage']) }}%)</small>
                    @else
                    -
                    @endif
                </td>
                @endif

                <td>
                    <strong>{{ round($row['average'], 1) }}%</strong>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated by {{ config('app.name') }} School Management System
    </div>
</body>

</html>