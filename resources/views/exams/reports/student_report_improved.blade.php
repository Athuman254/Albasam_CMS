<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Exam Report - {{ $student->first_name }} {{ $student->last_name }}</title>
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
            overflow: auto;
        }

        .page-margin {
            margin-top: 2%;
            border: 8px solid #1a73e8;
            border-radius: 20px;
            height: 96%;
            position: relative;
            background: white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            margin-left: 1%;
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
            max-width: 1000px;
            margin: 0 auto;
            background: transparent;
            position: relative;
            z-index: 1;
            padding: 20px;
            flex: 1;
            width: 100%;
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

        .logo-container {
            position: absolute;
            top: 0;
            right: 0;
            text-align: right;
            margin-right: 5%;
        }

        .institution-logo {
            max-width: 80px;
            max-height: 80px;
            object-fit: contain;
        }

        .school-info {
            width: 100%;
            text-align: center;
            padding: 0 100px 0 20px;
        }

        .school-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            margin-bottom: 2px;
        }

        .school-details {
            font-size: 10px;
            color: #000;
            line-height: 1.3;
        }

        .report-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            color: #000;
            margin: 8px 0 5px;
            padding: 8px 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            text-align: center;
        }

        /* STUDENT INFO TABLE */
        .student-info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10px;
        }

        .student-info-table th, .student-info-table td {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
        }

        .student-info-table th {
            font-weight: bold;
            background-color: #f0f0f0;
        }

        /* PERFORMANCE SECTION */
        .performance-section {
            margin-bottom: 10px;
        }

        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
            text-align: center;
            text-decoration: underline;
        }

        .marks-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        .marks-table th {
            font-weight: bold;
            padding: 6px 4px;
            border: 1px solid #000;
            text-align: center;
        }

        .marks-table td {
            padding: 6px 4px;
            border: 1px solid #000;
            text-align: center;
        }

        .subject-name {
            text-align: left;
            font-weight: bold;
        }

        /* REMARKS SECTION */
        .remarks-section {
            margin-bottom: 10px;
            padding: 10px;
        }

        .remarks-title {
            font-size: 11px;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
            text-align: center;
            text-decoration: underline;
        }

        .remarks-text {
            font-size: 10px;
            color: #000;
            text-align: left;
        }

        /* FOOTER */
        .footer {
            margin-top: auto;
            padding-top: 15px;
        }

        .generated-date {
            margin-top: 5px;
            color: #666;
            font-size: 9px;
            text-align: right;
        }

        /* Fallback for when logo is not available */
        .no-logo {
            width: 80px;
            height: 80px;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #999;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="page-margin">
        <!-- WATERMARK -->
        <div class="watermark">
            <div class="watermark-text">
                {{ $institution->name ?? 'ALBASAM COMPREHENSIVE SCHOOL' }}<br>
                {{ strtoupper($student->first_name) }} {{ strtoupper($student->last_name) }}
            </div>
        </div>

        <div class="report-container">
            <!-- HEADER WITH LOGO -->
            <div class="header">
                <div class="school-info">
                    <div class="school-name">{{ $institution->name ?? 'ALBASAM COMPREHENSIVE SCHOOL' }}</div>
                    <div class="school-details">
                        {{ $institution->address ?? '13-80102 MOMBASA' }}<br>
                        {{ $institution->email ?? 'info@albasamcomprehensive.sc.ke' }}<br>
                        @if($institution->phone)
                            Tel: {{ $institution->phone }}
                        @endif
                    </div>
                </div>
                <div class="logo-container">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="{{ $institution->name }} Logo" class="institution-logo">
                    @else
                        <div class="no-logo">
                            No Logo<br>Available
                        </div>
                    @endif
                </div>
            </div>

            <div class="report-title">
                ACADEMIC REPORT FORM – FORM {{ $class->name }} – END TERM – ({{ $exam->academicYear->name ?? '2024/2025' }} TERM 1)
            </div>

            <!-- STUDENT INFO TABLE -->
            <table class="student-info-table">
                <thead>
                    <tr>
                        <th>NAME</th>
                        <th>G</th>
                        <th>CLASS</th>
                        <th>TERM</th>
                        <th>{{ $exam->academicYear->name ?? '2024/2025' }} CLOSING DATE</th>
                        <th>{{ $nextTermYear ?? '2025/2026' }} OPENING DATE</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ strtoupper($student->first_name) }} {{ strtoupper($student->last_name) }}</td>
                        <td>{{ $student->gender == 'Male' ? 'G I' : 'G II' }}</td>
                        <td>Form {{ $class->name }}</td>
                        <td>{{ $exam->term ?? 'Term 1' }}</td>
                        <td>
                            @if($closing_date)
                                {{ \Carbon\Carbon::parse($closing_date)->format('l d/m/Y') }}
                            @else
                                {{ $exam->closing_date ?? $exam->end_date ?? 'To be announced' }}
                            @endif
                        </td>
                        <td>
                            @if($opening_date)
                                {{ \Carbon\Carbon::parse($opening_date)->format('l d/m/Y') }}
                            @else
                                {{ $nextTermOpeningDate ?? 'To be announced' }}
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- ACADEMIC, ATTENDANCE, ATTITUDE AND SKILLS PERFORMANCE -->
            <div class="performance-section">
                <div class="section-title">ACADEMIC, ATTENDANCE, ATTITUDE AND SKILLS PERFORMANCE</div>
                <table class="marks-table">
                    <thead>
                        <tr>
                            <th width="20%">PAPER</th>
                            <th width="30%">SKILL TESTED</th>
                            <th width="10%">SCORE</th>
                            <th width="10%">OUT OF</th>
                            <th width="30%">REMARKS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Academic Subjects -->
                        @foreach($marksWithGrades as $mark)
                            @if(isset($mark['breakdown']) && is_array($mark['breakdown']))
                                <!-- Subject with skill breakdown -->
                                @foreach($mark['breakdown'] as $index => $skill)
                                    <tr>
                                        @if($index === 0)
                                            <td class="subject-name" rowspan="{{ count($mark['breakdown']) }}">{{ $mark['subject_name'] }}</td>
                                        @endif
                                        <td>{{ $skill['skill_name'] }}</td>
                                        <td>{{ $skill['marks_obtained'] }}</td>
                                        <td>{{ $skill['maximum_marks'] }}</td>
                                        <td>{{ $skill['remarks'] ?? 'You met expectations' }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <!-- Regular subject without breakdown -->
                                <tr>
                                    <td class="subject-name">{{ $mark['subject_name'] }}</td>
                                    <td>Overall Performance</td>
                                    <td>{{ $mark['marks_obtained'] }}</td>
                                    <td>{{ $mark['maximum_marks'] }}</td>
                                    <td>{{ $mark['remarks'] ?? 'You met expectations' }}</td>
                                </tr>
                            @endif
                        @endforeach

                        <!-- Attendance Data -->
                        @if(isset($attendanceData))
                        <tr>
                            <td class="subject-name" rowspan="3">ATTENDANCE</td>
                            <td>Days present</td>
                            <td>{{ $attendanceData['days_present'] ?? '0' }}</td>
                            <td>{{ $attendanceData['total_days'] ?? '0' }}</td>
                            <td>{{ $attendanceData['present_remark'] ?? 'Good. Continue with the spirit' }}</td>
                        </tr>
                        <tr>
                            <td>Days absent</td>
                            <td>{{ $attendanceData['days_absent'] ?? '0' }}</td>
                            <td>{{ $attendanceData['total_days'] ?? '0' }}</td>
                            <td>{{ $attendanceData['absent_remark'] ?? 'Good. Continue with the spirit' }}</td>
                        </tr>
                        <tr>
                            <td>Academic clinic attended</td>
                            <td>{{ $attendanceData['academic_clinic'] ?? '0' }}</td>
                            <td>{{ $attendanceData['academic_clinic_total'] ?? '0' }}</td>
                            <td>{{ $attendanceData['clinic_remark'] ?? 'You were absent. Please support your child' }}</td>
                        </tr>
                        @endif

                        <!-- Behavior & Attitude -->
                        @if(isset($behaviorData))
                        <tr>
                            <td class="subject-name" rowspan="4">BEHAVIOUR & ATTITUDE</td>
                            <td>Respect of teachers & fellow learners</td>
                            <td>{{ $behaviorData['respect_score'] ?? '0' }}</td>
                            <td>{{ $behaviorData['respect_max'] ?? '4' }}</td>
                            <td>{{ $behaviorData['respect_remark'] ?? 'Good. Continue with the spirit' }}</td>
                        </tr>
                        <tr>
                            <td>General discipline</td>
                            <td>{{ $behaviorData['discipline_score'] ?? '0' }}</td>
                            <td>{{ $behaviorData['discipline_max'] ?? '4' }}</td>
                            <td>{{ $behaviorData['discipline_remark'] ?? 'Good. Continue with the spirit' }}</td>
                        </tr>
                        <tr>
                            <td>Team work</td>
                            <td>{{ $behaviorData['teamwork_score'] ?? '0' }}</td>
                            <td>{{ $behaviorData['teamwork_max'] ?? '4' }}</td>
                            <td>{{ $behaviorData['teamwork_remark'] ?? 'Satisfactory. Put more effort' }}</td>
                        </tr>
                        <tr>
                            <td>Leadership quality</td>
                            <td>{{ $behaviorData['leadership_score'] ?? '0' }}</td>
                            <td>{{ $behaviorData['leadership_max'] ?? '4' }}</td>
                            <td>{{ $behaviorData['leadership_remark'] ?? 'Good. Continue with the spirit' }}</td>
                        </tr>
                        @endif

                        <!-- Skills and Strengths -->
                        @if(isset($skillsData))
                        <tr>
                            <td class="subject-name" rowspan="4">SKILLS AND STRENGTHS</td>
                            <td>Communication skills</td>
                            <td>{{ $skillsData['communication_score'] ?? '0' }}</td>
                            <td>{{ $skillsData['communication_max'] ?? '4' }}</td>
                            <td>{{ $skillsData['communication_remark'] ?? 'Good. Continue with the spirit' }}</td>
                        </tr>
                        <tr>
                            <td>Problem solving ability</td>
                            <td>{{ $skillsData['problem_solving_score'] ?? '0' }}</td>
                            <td>{{ $skillsData['problem_solving_max'] ?? '4' }}</td>
                            <td>{{ $skillsData['problem_solving_remark'] ?? 'Satisfactory. Put more effort' }}</td>
                        </tr>
                        <tr>
                            <td>Creativity and innovation</td>
                            <td>{{ $skillsData['creativity_score'] ?? '0' }}</td>
                            <td>{{ $skillsData['creativity_max'] ?? '4' }}</td>
                            <td>{{ $skillsData['creativity_remark'] ?? 'Excellent keep it up' }}</td>
                        </tr>
                        <tr>
                            <td>Co-curricular activities</td>
                            <td>{{ $skillsData['co_curricular_score'] ?? '0' }}</td>
                            <td>{{ $skillsData['co_curricular_max'] ?? '1' }}</td>
                            <td>{{ $skillsData['co_curricular_remark'] ?? 'Seen participating. Continue nurturing your talent' }}</td>
                        </tr>
                        @endif

                        <!-- TOTAL MARKS -->
                        <tr>
                            <td class="subject-name" colspan="2">TOTAL MARKS SCORED</td>
                            <td>{{ $total_marks ?? '0' }}</td>
                            <td>{{ $total_max_marks ?? '0' }}</td>
                            <td>POSITION: {{ $class_rank ?? 'N/A' }}/{{ $class_size ?? 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- CLASS TEACHER REMARKS -->
            <div class="remarks-section">
                <div class="remarks-title">CLASS TEACHERS REMARKS</div>
                <div class="remarks-text">
                    NAME: {{ $classTeacher->name ?? 'Class Teacher' }}<br>
                    DATE: {{ $teacherRemarkDate ?? now()->format('d/m/Y') }}<br>
                    CONTACT: {{ $classTeacher->phone ?? 'N/A' }}<br>
                    SIGN: _________________________
                </div>
            </div>

            <!-- HOUSE REMARKS -->
            <div class="remarks-section">
                <div class="remarks-title">HOUSE REMARKS</div>
                <div class="remarks-text">
                    {{ $houseRemarks ?? 'I take this opportunity to appreciate you for contributing towards the betterment of this learner. Your support helps create a great learning environment for your child.' }}
                </div>
            </div>

            <!-- FOOTER -->
            <div class="footer">
                <div class="generated-date">
                    Report Generated on: {{ $generatedAt }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>