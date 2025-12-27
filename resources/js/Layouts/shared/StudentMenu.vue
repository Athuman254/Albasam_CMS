<template>
   <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/student/dashboard') }">
      <Link :href="route('student.dashboard')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-home-alt"></i>
         </span>
         Dashboard
      </Link>
   </li>

   <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/student/results') }">
      <Link :href="route('student.results.index')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-file"></i>
         </span>
         My Results
      </Link>
   </li>

   <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/student/fees') }">
      <Link :href="route('student.fees.index')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-wallet"></i>
         </span>
         Fee Statement
      </Link>
   </li>

   <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/student/attendance') }">
      <Link :href="route('student.attendance.index')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-calendar-check"></i>
         </span>
         Attendance
      </Link>
   </li>

   <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/student/calendar') }">
      <Link :href="route('student.calendar.index')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-calendar"></i>
         </span>
         Calendar
      </Link>
   </li>

   <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/student/notices') }">
      <Link :href="route('student.notices.index')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-bell"></i>
         </span>
         Announcements
      </Link>
   </li>

   <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Learning Management</span>
   </li>

   <li :class="{ 'menu-item': true, 'active': $page.url === '/student/lms/dashboard' }">
      <Link :href="route('student.lms.dashboard')" class="menu-link">
         <i class="menu-icon tf-icons bx bx-desktop"></i>
         <div>LMS Dashboard</div>
      </Link>
   </li>

   <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/student/lms/materials') }">
      <Link :href="route('student.lms.materials.index')" class="menu-link">
         <i class="menu-icon tf-icons bx bx-book-open"></i>
         <div>Materials</div>
      </Link>
   </li>

   <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/student/lms/assignments') }">
      <Link :href="route('student.lms.assignments.index')" class="menu-link">
         <i class="menu-icon tf-icons bx bx-edit"></i>
         <div>Assignments</div>
      </Link>
   </li>

   <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/student/lms/classes') }">
      <Link :href="route('student.lms.classes.index')" class="menu-link">
         <i class="menu-icon tf-icons bx bx-video"></i>
         <div>Online Classes</div>
      </Link>
   </li>

   <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Account</span>
   </li>

   <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/student/profile') }">
      <Link :href="route('student.profile.edit')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-user"></i>
         </span>
         My Profile
      </Link>
   </li>
</template>

<script>
import {Link} from "@inertiajs/vue3";

export default {
   components: {Link},
}
</script>
