<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Report Form</title>

   <style>
      /* ✅ Register the Arabic font */
      @font-face {
         font-family: 'Amiri';
         src: url('{{ storage_path("fonts/Amiri-Regular.ttf") }}') format('truetype');
         font-weight: normal;
         font-style: normal;
      }

      /* ✅ Global font + base layout */
      body {
         font-family: 'Amiri', sans-serif;
         margin: 7px;
         font-size: 13px;
         direction: ltr;
         /* Default: English content LTR */
      }

      .container {
         border: 1px solid #000;
         padding: 15px;
      }

      /* Header styling */
      .header-table {
         width: 100%;
         border-collapse: collapse;
         text-align: center;
      }

      .header-table td {
         vertical-align: middle;
      }

      .header-logo {
         width: 100px;
      }

      .school-name {
         font-size: 18px;
         font-weight: bold;
      }

      .sub-title {
         font-size: 15px;
      }

      .report-title {
         font-weight: bold;
         margin: 10px 0;
         text-decoration: underline;
      }

      .info-table {
         width: 100%;
         margin-top: 10px;
      }

      .info-table td {
         padding: 4px 0;
      }

      .marks-table,
      .grades-table {
         width: 100%;
         border-collapse: collapse;
         margin-top: 15px;
         text-align: center;
      }

      .marks-table th,
      .marks-table td,
      .grades-table th,
      .grades-table td {
         border: 1px solid #000;
         padding: 6px;
      }

      .footer-table {
         width: 100%;
         margin-top: 25px;
      }

      .footer-table td {
         padding: 5px 0;
      }


      .rtl {
         direction: rtl;
         text-align: right;
         font-family: 'Amiri';
      }


      .ltr {
         direction: ltr;
         text-align: left;
      }
   </style>
</head>

<body>

   <div class="container">

      <table class="header-table">
         <tr>
            <td><img src="madra.jpeg" alt="School Logo" class="header-logo"></td>
            <td>
               <div class="school-name">{{ $institution->name ?? "غير محدد" }}</div>
               {{-- <div class="sub-title ltr">dffdfd</div> --}}
               {{-- <div class="rtl">كشف الدرجات</div> --}}
               <div class="report-title">REPORT FORM</div>
            </td>
            <td><img src="madra.jpeg" alt="School Logo" class="header-logo"></td>
         </tr>
      </table>

      <table class="info-table">
         <tr>
            <td class="ltr"><strong>NAME:</strong>{{ $student->first_name }} {{ $student->middle_name }} {{ $student->last_name }} ({{ $student->admission_number }})</td>
         </tr>
         <tr>
            <td class="ltr"><strong>CLASS:</strong>{{ $class->name ?? '' }} rowda(dot)</td>
         </tr>
         <tr>
            <td class="ltr"><strong>PERCENTAGE(%):</strong> ___________________</td>
         </tr>
         <tr>
            <td class="ltr"><strong>GRADE:</strong> __________________________</td>
         </tr>
         <tr>
            <td class="ltr">
               @if ($rank)
                  <strong>POSITION:</strong> {{ $rank }}
               @else
                  <strong>POSITION:</strong> ___________________
               @endif
            </td>
            <td class="ltr">
                @if ($classSize)
                  <strong>OUT OF:</strong> {{ $classSize }}
               @else
                  <strong>OUT OF:</strong> ___________________
               @endif
            </td>
         </tr>
      </table>

      <table class="marks-table">
         <tr>
            <th class="ltr">SUBJECT</th>
            <th class="ltr">MARKS</th>
            <th class="ltr">OUT OF</th>
            <th class="rtl">المواد الدراسية</th>
         </tr>
         @foreach($marks as $mark)
            @php
               $out_of =
                  $max = $mark->examSubject->max_marks;
               $obtained = $mark->marks_obtained;
               $percentage = $max > 0 ? round(($obtained / $max) * 100, 2) : 0;

               if ($percentage >= 86)
                  $grade = 'A';
               elseif ($percentage >= 76)
                  $grade = 'B';
               elseif ($percentage >= 66)
                  $grade = 'C';
               elseif ($percentage >= 50)
                  $grade = 'D';
               else
                  $grade = 'E';
              @endphp
            <tr>
               <td>{{ $mark->examSubject->subject->name }}</td>
               <td>{{ $obtained }}</td>
               <td>{{ $max }}</td>
               <td>{{ $grade }}</td>
            </tr>
         @endforeach
         <tr>
            <th class="ltr" colspan="1">TOTAL</th>
            <th>{{ $total }}</th>
            <th></th>
            <th class="rtl">المجموع</th>
         </tr>
      </table>

      <table class="grades-table">
         <tr>
            <th class="ltr">GRADES</th>
            <th class="ltr">MARKS</th>
            <th class="rtl">التقديرات</th>
         </tr>
         <tr>
            <td class="ltr">Excellent</td>
            <td>86–100</td>
            <td class="rtl">ممتاز</td>
         </tr>
         <tr>
            <td class="ltr">V. good</td>
            <td>76–85</td>
            <td class="rtl">جيد جدا</td>
         </tr>
         <tr>
            <td class="ltr">Good</td>
            <td>66–75</td>
            <td class="rtl">جيد</td>
         </tr>
         <tr>
            <td class="ltr">Pass</td>
            <td>50–65</td>
            <td class="rtl">مقبول</td>
         </tr>
         <tr>
            <td class="ltr">Fail</td>
            <td>00–49</td>
            <td class="rtl"> راسب </td>
         </tr>
      </table>

      <table class="footer-table">
         <tr>
            <td class="ltr">
               @if ($class->teacher)
               <strong>Class teacher:</strong> {{ $class->teacher->first_name }} {{ $class->teacher->last_name }}
           @else

           <strong>Class teacher:</strong>__________________________________________
           @endif
            </td>
            <td>
               Date:________________
            </td>
         </tr>
         <tr>
            <td class="ltr"><strong>Head teacher:</strong> Adan Mahamed</td>
            <td class="ltr"><strong>Date:</strong> ________________</td>
         </tr>
      </table>

   </div>

</body>

</html>
