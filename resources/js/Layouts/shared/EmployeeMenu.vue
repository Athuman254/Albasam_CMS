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
