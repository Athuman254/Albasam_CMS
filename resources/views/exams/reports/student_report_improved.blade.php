<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        line-height: 1.3;
        color: #000;
        background: white;
        font-size: 11px;
    }

    .page-margin {
        padding: 20px 40px;
        position: relative;
        background: white;
        height: auto;
    }

    .watermark {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg);
        z-index: -1;
        opacity: 0.08;
        pointer-events: none;
        width: 100%;
        text-align: center;
    }

    .watermark-text {
        font-size: 60px;
        font-weight: bold;
        color: #000000;
    }

    .report-container {
        width: 100%;
        background: transparent;
        z-index: 1;
    }

    .header {
        width: 100%;
        margin-bottom: 20px;
        border-bottom: 2px solid #000;
        padding-bottom: 10px;
        position: relative;
        min-height: 100px;
    }

    .logo-container {
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        text-align: right;
    }

    .institution-logo {
        max-width: 90px;
        max-height: 90px;
        object-fit: contain;
    }

    .school-info {
        text-align: center;
        width: 100%;
        padding: 0 100px;
    }

    .school-name {
        font-size: 18px;
        font-weight: bold;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .school-details {
        font-size: 11px;
        margin-bottom: 3px;
    }

    .report-title {
        font-size: 14px;
        font-weight: bold;
        text-transform: uppercase;
        text-align: center;
        margin: 15px 0;
        padding: 5px;
        background-color: #f0f0f0;
        border: 1px solid #000;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    th,
    td {
        border: 1px solid #000;
        padding: 6px;
        text-align: center;
        font-size: 10px;
    }

    th {
        background-color: #e0e0e0;
        font-weight: bold;
        text-transform: uppercase;
    }

    .subject-name {
        text-align: left;
        font-weight: bold;
        background-color: #f9f9f9;
    }

    .section-title {
        font-size: 12px;
        font-weight: bold;
        margin-bottom: 8px;
        text-transform: uppercase;
        text-decoration: underline;
    }

    .remarks-section {
        margin-top: 20px;
        border: 1px solid #000;
        padding: 10px;
        page-break-inside: avoid;
    }

    .footer {
        margin-top: 30px;
        border-top: 1px solid #ccc;
        padding-top: 5px;
        font-size: 9px;
        text-align: right;
        color: #555;
    }
</style>

<div class="page-margin">
    <!-- WATERMARK -->
    <div class="watermark">
        <div class="watermark-text">
            {{ $institution->name ?? 'SCHOOL REPORT' }}<br>
            {{ strtoupper($student->first_name) }} {{ strtoupper($student->last_name) }}
        </div>
    </div>

    <div class="report-container">
        <!-- HEADER WITH LOGO -->
        <div class="header">
            <div class="school-info">
                <div class="school-name">{{ $institution->name ?? 'SCHOOL NAME' }}</div>
                <div class="school-details">
                    {{ $institution->address ?? '' }}<br>
                    {{ $institution->email ?? '' }}<br>
                    @if($institution->phone)
                    Tel: {{ $institution->phone }}
                    @endif
                </div>
            </div>
            <div class="logo-container">
                @if($logoBase64)
                <img src="{{ $logoBase64 }}" alt="Logo" class="institution-logo">
                @endif
            </div>
        </div>

        <div class="report-title">
            ACADEMIC REPORT FORM – {{ $class->name }} – END TERM – ({{ $exam->academicYear->display_name ?? '2024' }} TERM 1)
        </div>

        <!-- STUDENT INFO TABLE -->
        <table class="student-info-table">
            <thead>
                <tr>
                    <th>NAME</th>
                    <th>GENDER</th>
                    <th>CLASS</th>
                    <th>TERM</th>
                    <th>{{ $exam->academicYear->display_name ?? '2024' }} CLOSING DATE</th>
                    <th>{{ $nextTermYear ?? '2025' }} OPENING DATE</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ strtoupper($student->first_name) }} {{ strtoupper($student->last_name) }}</td>
                    <td>{{ $student->gender == 'Male' ? 'M' : 'F' }}</td>
                    <td>{{ $class->name }}</td>
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