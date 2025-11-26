<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class Exam Report - {{ $institution->name }}</title>
    <style>
        /* Basic reset and styling */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 15px;
            background: #ffffff;
        }
        
        .report-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border: 2px solid #2c5aa0;
        }
        
        /* Header styles */
        .header {
            text-align: center;
            border-bottom: 3px solid #2c5aa0;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .school-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        /* Class info section */
        .class-info {
            margin: 15px 0;
            padding: 15px;
            background: #e8f4fd;
            border: 1px solid #b8d4f0;
            border-radius: 8px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
        }
        .info-item {
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            color: #2c5aa0;
        }
        
        /* Marks table */
        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 11px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .marks-table th {
            background: #2c5aa0;
            color: white;
            padding: 10px 6px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #1e3a8a;
            position: sticky;
            top: 0;
        }
        .marks-table td {
            padding: 8px 6px;
            border: 1px solid #dee2e6;
            text-align: center;
        }
        .marks-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .marks-table tr:hover {
            background: #e8f4fd;
        }
        
        /* Rank styling */
        .rank-1 { background-color: #d4edda !important; }
        .rank-2 { background-color: #fff3cd !important; }
        .rank-3 { background-color: #f8d7da !important; }
        
        /* Grade colors */
        .grade-A { background: #d4edda !important; color: #155724; font-weight: bold; }
        .grade-B-plus { background: #d1ecf1 !important; color: #0c5460; font-weight: bold; }
        .grade-B { background: #d1ecf1 !important; color: #0c5460; font-weight: bold; }
        .grade-B-minus { background: #d1ecf1 !important; color: #0c5460; font-weight: bold; }
        .grade-C-plus { background: #fff3cd !important; color: #856404; font-weight: bold; }
        .grade-C { background: #fff3cd !important; color: #856404; font-weight: bold; }
        .grade-C-minus { background: #fff3cd !important; color: #856404; font-weight: bold; }
        .grade-D-plus { background: #f8d7da !important; color: #721c24; font-weight: bold; }
        .grade-D-minus { background: #f8d7da !important; color: #721c24; font-weight: bold; }
        .grade-E { background: #f5c6cb !important; color: #721c24; font-weight: bold; }
        
        /* Rank badges */
        .rank-badge {
            background: #ffd700;
            color: #000;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: bold;
            font-size: 10px;
            display: inline-block;
        }
        .subject-rank {
            padding: 1px 4px;
            border-radius: 8px;
            font-size: 9px;
            font-weight: bold;
            color: white;
            background: #6c757d;
        }
        .rank-1-badge { background: #ffd700; color: #000; }
        .rank-2-badge { background: #c0c0c0; color: #000; }
        .rank-3-badge { background: #cd7f32; color: #000; }
        .rank-top-badge { background: #28a745; }
        .rank-good-badge { background: #17a2b8; }
        
        /* Statistics section */
        .statistics {
            background: #f8f9fa;
            padding: 20px;
            border: 1px solid #dee2e6;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        .stat-item {
            text-align: center;
            padding: 10px;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #2c5aa0;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 3px;
        }
        
        /* Grade distribution */
        .grade-distribution {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 8px;
            margin-top: 10px;
        }
        .grade-item {
            text-align: center;
            padding: 8px;
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            font-size: 11px;
        }
        
        /* Footer and signatures */
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #dee2e6;
            color: #666;
            font-size: 10px;
        }
        .stamp-area {
            text-align: center;
            margin-top: 20px;
        }
        .school-stamp {
            border: 2px solid #2c5aa0;
            padding: 10px 20px;
            display: inline-block;
            border-radius: 4px;
            color: #2c5aa0;
            font-weight: bold;
            font-size: 12px;
        }
        
        /* Section headers */
        h3 {
            color: #2c5aa0;
            border-bottom: 2px solid #2c5aa0;
            padding-bottom: 8px;
            margin: 20px 0 15px 0;
            font-size: 16px;
        }
        
        /* Utility classes */
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-bold { font-weight: bold; }
        .text-success { color: #28a745; }
        .text-warning { color: #ffc107; }
        .text-danger { color: #dc3545; }
        .mb-10 { margin-bottom: 10px; }
        .mt-10 { margin-top: 10px; }
        
        /* Page break for printing */
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="report-container">
        <!-- Header Section -->
        <div class="header">
            <div class="school-name">{{ $institution->name ?? 'Albasam Comprehensive School' }}</div>
            <div class="report-title">CLASS EXAMINATION REPORT</div>
            <div><strong>{{ $exam->name }} - Form {{ $class->name }} {{ $class->stream->name ?? '' }}</strong></div>
            <div>Academic Year: {{ $exam->academicYear->name ?? '2024/2025' }}</div>
        </div>

        <!-- Class Information -->
        <div class="class-info">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Class:</span> Form {{ $class->name }}
                </div>
                <div class="info-item">
                    <span class="info-label">Stream:</span> {{ $class->stream->name ?? 'N/A' }}
                </div>
                <div class="info-item">
                    <span class="info-label">Exam:</span> {{ $exam->name }}
                </div>
                <div class="info-item">
                    <span class="info-label">Academic Year:</span> {{ $exam->academicYear->name ?? '2024/2025' }}
                </div>
                <div class="info-item">
                    <span class="info-label">Total Students:</span> {{ count($studentResults) }}
                </div>
                <div class="info-item">
                    <span class="info-label">Report Type:</span> {{ ucfirst($reportType) }} Report
                </div>
            </div>
        </div>

        <!-- Student Performance Table -->
        <h3>Student Performance Summary</h3>
        <div style="overflow-x: auto;">
            <table class="marks-table">
                <thead>
                    <tr>
                        <th width="50">Rank</th>
                        <th width="80">Adm No</th>
                        <th width="150" class="text-left">Student Name</th>
                        @foreach($examSubjects as $subject)
                        <th width="80">
                            {{ $subject->subject->name }}<br>
                            <small style="font-size: 9px; font-weight: normal;">/{{ $subject->max_marks }}</small>
                        </th>
                        @endforeach
                        <th width="80">Total</th>
                        <th width="70">%</th>
                        <th width="60">Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($studentResults as $result)
                    @php
                        $gradeClass = 'grade-' . str_replace('+', '-plus', str_replace('-', '-minus', $result['grade']));
                    @endphp
                    <tr class="@if($result['rank'] <= 3) rank-{{ $result['rank'] }} @endif">
                        <td>
                            <span class="rank-badge @if($result['rank'] == 1) rank-1-badge
                                @elseif($result['rank'] == 2) rank-2-badge
                                @elseif($result['rank'] == 3) rank-3-badge
                                @elseif($result['rank'] <= 5) rank-top-badge
                                @elseif($result['rank'] <= 10) rank-good-badge @endif">
                                {{ $result['rank'] }}
                            </span>
                        </td>
                        <td class="text-bold">{{ $result['student']->admission_number }}</td>
                        <td class="text-left">{{ $result['student']->first_name }} {{ $result['student']->last_name }}</td>
                        
                        @foreach($examSubjects as $subject)
                        @php
                            $mark = $result['marks']->firstWhere('examSubject.subject_id', $subject->subject_id);
                            $subjectRank = $result['subject_ranks'][$subject->subject_id] ?? null;
                        @endphp
                        <td>
                            @if($mark)
                                <div class="text-bold">{{ $mark->marks_obtained }}</div>
                                @if($includeRankings && $subjectRank)
                                    @php
                                        $rankClass = 'subject-rank';
                                        if ($subjectRank == 1) $rankClass .= ' rank-1-badge';
                                        elseif ($subjectRank == 2) $rankClass .= ' rank-2-badge';
                                        elseif ($subjectRank == 3) $rankClass .= ' rank-3-badge';
                                        elseif ($subjectRank <= 5) $rankClass .= ' rank-top-badge';
                                        elseif ($subjectRank <= 10) $rankClass .= ' rank-good-badge';
                                    @endphp
                                    <div style="margin-top: 2px;">
                                        <span class="{{ $rankClass }}" style="font-size: 8px;">
                                            {{ $subjectRank }}
                                        </span>
                                    </div>
                                @endif
                            @else
                                <span style="color: #999;">-</span>
                            @endif
                        </td>
                        @endforeach
                        
                        <td class="text-bold">{{ $result['total_marks'] }}/{{ $result['total_max_marks'] }}</td>
                        <td class="text-bold {{ $gradeClass }}">{{ number_format($result['overall_percentage'], 1) }}%</td>
                        <td class="text-bold {{ $gradeClass }}">{{ $result['grade'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($includeAnalysis)
        <!-- Class Statistics -->
        <div class="statistics">
            <h3>Class Performance Statistics</h3>
            <div class="stat-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ $classStatistics['average_marks'] }}</div>
                    <div class="stat-label">Average Marks</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $classStatistics['highest_marks'] }}</div>
                    <div class="stat-label">Highest Marks</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $classStatistics['lowest_marks'] }}</div>
                    <div class="stat-label">Lowest Marks</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $classStatistics['pass_rate'] }}%</div>
                    <div class="stat-label">Pass Rate</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $classStatistics['total_students'] }}</div>
                    <div class="stat-label">Total Students</div>
                </div>
            </div>

            <!-- Grade Distribution -->
            <h4 style="margin: 15px 0 10px 0; color: #2c5aa0;">Grade Distribution</h4>
            <div class="grade-distribution">
                @foreach($classStatistics['grade_distribution'] as $grade => $count)
                @php
                    $gradeBgClass = 'grade-' . str_replace('+', '-plus', str_replace('-', '-minus', $grade));
                @endphp
                <div class="grade-item {{ $gradeBgClass }}">
                    <div class="text-bold" style="font-size: 14px;">{{ $grade }}</div>
                    <div style="font-size: 12px;">{{ $count }} students</div>
                    <div style="font-size: 10px; color: #666;">
                        {{ number_format(($count / $classStatistics['total_students']) * 100, 1) }}%
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="stamp-area">
                <div class="school-stamp">
                    OFFICIAL SCHOOL STAMP
                </div>
                <div style="font-style: italic; color: #666; margin-top: 8px; font-size: 10px;">
                    Not valid without school stamp | Generated: {{ $generatedAt }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>