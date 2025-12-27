<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Student ID Cards</title>
    <style>
        @font-face {
            font-family: 'Inter';
            font-style: normal;
            font-weight: 400;
            src: local('Inter'), local('Inter-Regular');
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        @page {
            margin: 1cm;
        }

        .container {
            width: 100%;
        }

        /* ID Card Dimensions - CR80 standard: 85.6mm x 53.98mm */
        .id-card-wrapper {
            display: inline-block;
            width: 85.6mm;
            height: 54mm;
            margin: 4mm;
            background-color: #fff;
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            border: 1px solid #e0e6ed;
            /* Subtle blue-ish pattern background */
            background-image: radial-gradient(#1a237e 0.5px, transparent 0.5px);
            background-size: 15px 15px;
            background-color: #fcfdfe;
        }

        .card-inner {
            background-color: rgba(255, 255, 255, 0.96);
            height: 100%;
            width: 100%;
            position: relative;
        }

        /* Front Side Styling */
        .header {
            background-color: #1a237e;
            color: white;
            padding: 8px 10px;
            text-align: left;
            height: 14mm;
            border-bottom: 2px solid #ffd700;
            /* Gold accent */
        }

        .school-name {
            font-size: 11px;
            font-weight: 900;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #ffffff;
        }

        .school-motto {
            font-size: 7px;
            margin: 2px 0 0 0;
            font-style: italic;
            opacity: 0.9;
            color: #ffd700;
        }

        .card-body {
            padding: 8px 10px;
            position: relative;
        }

        .photo-container {
            float: left;
            width: 20mm;
            height: 25mm;
            border: 1.5px solid #1a237e;
            margin-right: 6mm;
            background-color: #f8f9fa;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .student-info {
            float: left;
            width: 46mm;
        }

        .info-group {
            margin-bottom: 5px;
        }

        .info-label {
            font-size: 6.5px;
            color: #566a7f;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 0px;
        }

        .info-value {
            font-size: 10px;
            font-weight: 800;
            color: #111;
            margin-bottom: 0px;
            text-transform: uppercase;
            line-height: 1.1;
        }

        .admission-highlight {
            color: #1a237e;
            font-size: 11px;
        }

        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: #1a237e;
            height: 5mm;
            color: white;
            padding: 0 10px;
            box-sizing: border-box;
            display: table;
        }

        .footer-content {
            display: table-cell;
            vertical-align: middle;
            font-size: 7px;
            font-weight: bold;
        }

        .expiry-text {
            text-align: right;
            color: #ffd700;
        }

        .id-type {
            position: absolute;
            top: 17mm;
            right: 10px;
            font-size: 7px;
            font-weight: 900;
            color: #ffffff;
            background-color: #d32f2f;
            padding: 2px 6px;
            border-radius: 10px;
            text-transform: uppercase;
            box-shadow: 0 2px 4px rgba(211, 47, 47, 0.3);
        }

        .clear {
            clear: both;
        }
    </style>
</head>

<body>
    <div class="container">
        @foreach($students->chunk(2) as $row)
        @foreach($row as $student)
        <div class="id-card-wrapper">
            <div class="card-inner">
                <!-- Front Side -->
                <div class="header">
                    <div class="school-name">{{ $institution->name ?? 'ALBASAM COMPREHENSIVE SCHOOL' }}</div>
                    <div class="school-motto">{{ $institution->motto ?? 'Excellence in Education' }}</div>
                </div>

                <div class="id-type">STUDENT</div>

                <div class="card-body">
                    <div class="photo-container">
                        @if($student->photo_url && file_exists(public_path($student->photo_url)))
                        <img src="{{ public_path($student->photo_url) }}" alt="Photo">
                        @else
                        <div style="text-align:center; padding-top:10mm; font-size:6px; color: #adb5bd;">PASSPORT PHOTO</div>
                        @endif
                    </div>

                    <div class="student-info">
                        <div class="info-group">
                            <div class="info-label">Student Name</div>
                            <div class="info-value">{{ $student->first_name }} {{ $student->last_name }}</div>
                        </div>

                        <div class="info-group">
                            <div class="info-label">Admission Number</div>
                            <div class="info-value admission-highlight">#{{ $student->admission_number }}</div>
                        </div>

                        <div class="info-group">
                            <div class="info-label">Curriculum / Class</div>
                            <div class="info-value">{{ $student->currentRank->name ?? '-' }}</div>
                        </div>

                        <div style="float: left; width: 50%;">
                            <div class="info-label">Gender</div>
                            <div class="info-value">{{ $student->gender->name ?? '-' }}</div>
                        </div>
                        <div style="float: left; width: 50%;">
                            <div class="info-label">Blood Group</div>
                            <div class="info-value">{{ $student->blood_group ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>

                <div class="footer">
                    <div class="footer-content">ALBASAM ID SYSTEM</div>
                    <div class="footer-content expiry-text">VALID UNTIL: {{ $student->expiry_year }}</div>
                </div>
            </div>
        </div>
        @endforeach
        <div class="clear"></div>
        @endforeach
    </div>
</body>

</html>