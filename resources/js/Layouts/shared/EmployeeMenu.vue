<template>
   <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/employee/dashboard') }">
      <Link :href="route('employee.dashboard')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-home-alt"></i>
         </span>
         Dashboard
      </Link>
   </li>
   
   <li class="menu-item">
      <a class="menu-link" href="/" target="_blank" rel="noopener noreferrer">
         <span>
            <i class='menu-icon tf-icons bx bx-link-external'></i>
         </span>
         View Website
      </a>
   </li>
   
   <li :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/employee/attendance') || $page.url.startsWith('/employee/reports/attendance') }">
      <a href="#" class="menu-link menu-toggle">
         <i class='bx bx-calendar-check menu-icon tf-icons'></i>
         <div class="text-truncate">Attendance</div>
      </a>
      <ul class="menu-sub">
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/employee/attendances') }">
            <Link :href="route('employee.attendances.index')" class="menu-link">
               <div class="text-truncate">Mark Attendance</div>
            </Link>
         </li>
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/employee/reports/attendance') }">
            <Link :href="route('employee.reports.attendance')" class="menu-link">
               <div class="text-truncate">Attendance Report</div>
            </Link>
         </li>
      </ul>
   </li>

   <!-- Exams Menu for Employees -->
   <li :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/employee/exams') }">
      <a href="#" class="menu-link menu-toggle">
         <i class='bx bx-book-open menu-icon tf-icons'></i>
         <div class="text-truncate">Exams</div>
      </a>
      <ul class="menu-sub">
         <li :class="{ 'menu-item': true, 'active': $page.url === '/employee/exams/enter-marks' }">
            <Link :href="route('employee.exams.enter-marks')" class="menu-link">
               <div class="text-truncate">Enter Exam Marks</div>
            </Link>
         </li>
         <li :class="{ 'menu-item': true, 'active': $page.url === '/employee/exams/submitted-marks' }">
            <Link :href="route('employee.exams.submitted-marks')" class="menu-link">
               <div class="text-truncate">Submitted Marks</div>
            </Link>
         </li>
      </ul>
   </li>
   <!-- Student Promotion Menu -->
<li :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/employee/promotion') }">
    <a href="#" class="menu-link menu-toggle">
        <i class='bx bx-up-arrow-alt menu-icon tf-icons'></i>
        <div class="text-truncate">Student Promotion</div>
    </a>
    <ul class="menu-sub">
        <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/employee/promotion') }">
            <Link :href="route('employee.promotion.index')" class="menu-link">
                <div class="text-truncate">Promote Students</div>
            </Link>
        </li>
    </ul>
</li>

   <!-- My Timetable Menu -->
   <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/teacher/my-timetable') }">
      <Link :href="route('teacher.timetable')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-calendar"></i>
         </span>
         My Timetable
      </Link>
   </li>

   <!-- Remove or comment out the admin exams menu from employee sidebar -->
   <!--
   <li :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/admin/exams')  }">
      <a href="#" class="menu-link menu-toggle">
         <i class='bx  bx-wallet menu-icon tf-icons'></i>
         <div class="text-truncate">Exams</div>
      </a>
      <ul class="menu-sub">
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/exams/manage') }">
            <Link :href="route('admin.exams.manage.index')" class="menu-link">
               <div class="text-truncate">Manage Exam</div>
            </Link>
         </li>
          <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/exams/exam-students') }">
            <Link :href="route('admin.exams.exam-students.index')" class="menu-link">
               <div class="text-truncate">Enroll students</div>
            </Link>
         </li>
           <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/exams/upload-results') }">
            <Link :href="route('admin.exams.upload-results.index')" class="menu-link">
               <div class="text-truncate">Upload Exam</div>
            </Link>
         </li>
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/exams/results') }">
            <Link :href="route('admin.exams.results.index')" class="menu-link">
               <div class="text-truncate">Exam Results</div>
            </Link>
         </li>
      </ul>
   </li>
   -->
</template>

<script>
import {Link} from "@inertiajs/vue3";

export default {
   components: {Link},
   computed: {
      staff() {
         return this.$page.props.auth.employee;
      },
   },
}
</script>