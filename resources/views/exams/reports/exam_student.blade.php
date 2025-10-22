<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Exam Report - {{ $student->first_name }}</title>
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; line-height: 1.5; }
    .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
    .header img { max-height: 70px; }
    .school-name { font-size: 18px; font-weight: bold; }
    .school-meta { font-size: 12px; color: #555; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #444; padding: 6px; text-align: center; }
    th { background: #f0f0f0; }
    .summary { margin-top: 20px; }
    .summary p { margin: 3px 0; }
    .footer { margin-top: 40px; font-size: 12px; text-align: center; border-top: 1px solid #ccc; padding-top: 10px; }
  </style>
</head>
<body>
  <div class="header">
    <img src="{{ public_path('images/school_logo.png') }}" alt="School Logo">
    <div class="school-name">{{ $institution->name ?? "not set" }}</div>
    <div class="school-meta">P.O. Box {{ $institution->postal_address }}, {{ $institution->city }} | Tel: {{ $institution->phone }} | Email: {{ $institution->email }}</div>
  </div>

  <h3 style="text-align:center;">Exam Report</h3>

  <p><strong>Exam:</strong> {{ $exam->name }}</p>
  <p><strong>Student:</strong> {{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }} ({{ $student->admission_number }})</p>
  <p><strong>Class:</strong> {{ $class->name ?? '' }}</p>

  <table>
    <thead>
      <tr>
        <th>Subject</th>
        <th>Max Marks</th>
        <th>Marks Obtained</th>
        <th>Grade</th>
        <th>Percentage</th>
      </tr>
    </thead>
    <tbody>
      @foreach($marks as $mark)
        @php
          $max = $mark->examSubject->max_marks;
          $obtained = $mark->marks_obtained;
          $percentage = $max > 0 ? round(($obtained / $max) * 100, 2) : 0;

          if ($percentage >= 80) $grade = 'A';
          elseif ($percentage >= 70) $grade = 'B';
          elseif ($percentage >= 60) $grade = 'C';
          elseif ($percentage >= 50) $grade = 'D';
          else $grade = 'E';
        @endphp
        <tr>
          <td>{{ $mark->examSubject->subject->name }}</td>
          <td>{{ $max }}</td>
          <td>{{ $obtained }}</td>
          <td>{{ $grade }}</td>
          <td>{{ $percentage }}%</td>
        </tr>
      @endforeach
      <tr>
        <td colspan="2"><strong>Total</strong></td>
        <td colspan="3"><strong>{{ $total }}</strong></td>
      </tr>
    </tbody>
  </table>

  <div class="summary">
    <p><strong>Total Marks:</strong> {{ $total }}</p>
    <p><strong>Rank:</strong> {{ $rank }} out of {{ $classSize }}</p>
    <p><strong>Remarks:</strong>
      @if($rank == 1) Excellent performance!
      @elseif($rank <= 5) Very good!
      @elseif($rank <= 10) Keep improving.
      @else Work harder next time.
      @endif
    </p>
  </div>

  <div class="footer">
    Generated on {{ now()->format('d M, Y') }} by {{ $institution->name ?? "not set" }}
  </div>
</body>
</html>
