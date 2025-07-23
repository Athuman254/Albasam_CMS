<template>
   <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/dashboard') }">
      <Link :href="route('admin.dashboard')" class="menu-link">
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

   <li v-if="can('access-admissions-workspace')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/student-admissions')}">
      <Link :href="route('admin.admissions.index')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-spreadsheet"></i>
         </span>
         Admissions
      </Link>
   </li>

   <li v-if="can('access-class-workspace')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/ranks')}">
      <Link :href="route('admin.ranks.index')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-chalkboard"></i>
         </span>
         Classes
      </Link>
   </li>

   <li v-if="canAny(['access-employee-workspace', 'access-teacher-workspace'])" :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/admin/employees') }">
      <a href="#" class="menu-link menu-toggle">
         <i class='bx bxs-user-badge menu-icon tf-icons'></i>
         <div class="text-truncate">Employee Management</div>
      </a>
      <ul v-if="can('access-teacher-workspace')"  class="menu-sub">
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/employees/teachers') }">
            <Link :href="route('admin.teachers.index')" class="menu-link">
               <div class="text-truncate">Teachers</div>
            </Link>
         </li>
      </ul>
   </li>

   <li v-if="can('access-calendar')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/calendar')}">
      <Link href="#" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-calendar"></i>
         </span>
         Calendar
         <span class="badge bg-label-success ms-4 text-end">Coming Soon</span>
      </Link>
   </li>

   <li v-if="can('access-time-table')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/time-table')}">
      <Link :href="route('admin.timetable.index')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-table"></i>
         </span>
         Time-Table
      </Link>
   </li>

   <li v-if="canAny(['access-bulk-sms', 'access-sms-outbox'])" :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/admin/sms') }">
      <a href="#" class="menu-link menu-toggle">
         <i class='menu-icon tf-icons bx bx-message-dots'></i>
         <div class="text-truncate">Sms Messages</div>
      </a>
      <ul class="menu-sub">
         <li v-if="can('access-bulk-sms')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/sms/compose') }">
            <Link :href="route('admin.sms.compose')" class="menu-link">
               <div class="text-truncate">Compose</div>
            </Link>
         </li>
      </ul>
      <ul class="menu-sub">
         <li v-if="can('access-sms-outbox')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/sms/outbox') }">
            <Link :href="route('admin.sms.outbox')" class="menu-link">
               <div class="text-truncate">Outbox</div>
            </Link>
         </li>
      </ul>
   </li>
 <li v-if="canAny(['access-attendance-workspace', 'access-attendance-report'])" :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/admin/attendance') || $page.url.startsWith('/admin/reports/attendance') }">
      <a href="#" class="menu-link menu-toggle">
         <i class='bx bx-calendar-check menu-icon tf-icons'></i>
         <div class="text-truncate">Attendance</div>
      </a>
      <ul class="menu-sub">
<!--         <li v-if="can('access-attendance-workspace')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/attendances') }">-->
<!--            <Link :href="route('admin.attendances.index')" class="menu-link">-->
<!--               <div class="text-truncate">Mark Attendance</div>-->
<!--            </Link>-->
<!--         </li>-->
         <li v-if="can('access-attendance-report')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/reports/attendance') }">
            <Link :href="route('admin.reports.attendance')" class="menu-link">
               <div class="text-truncate">Attendance Report</div>
            </Link>
         </li>
      </ul>
   </li>
   <li v-if="canAny(['access-institution-workspace', 'access-users-workspace', 'access-roles-workspace', 'access-divisions-workspace', 'access-streams-workspace', 'access-subjects-workspace'])"
       class="menu-header small text-uppercase">
      <span class="menu-header-text">Human Resource</span>
   </li>


    <li :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/admin/attendance') || $page.url.startsWith('/admin/reports/attendance') }">
      <a href="#" class="menu-link menu-toggle">
         <i class='bx  bx-wallet menu-icon tf-icons'></i>
         <div class="text-truncate">Payroll</div>
      </a>
      <ul class="menu-sub">
          <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/reports/attendance') }">
            <Link :href="route('admin.adjustment.index')" class="menu-link">
               <div class="text-truncate">Employees</div>
            </Link>
         </li>
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/reports/attendance') }">
            <Link :href="route('admin.adjustment.index')" class="menu-link">
               <div class="text-truncate">Payroll Adjustments</div>
            </Link>
         </li>
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/reports/attendance') }">
            <Link :href="route('admin.adjustment.index')" class="menu-link">
               <div class="text-truncate">Run</div>
            </Link>
         </li>
      </ul>
   </li>



   <li v-if="canAny(['access-institution-workspace', 'access-users-workspace', 'access-roles-workspace', 'access-divisions-workspace', 'access-streams-workspace', 'access-subjects-workspace'])"
       class="menu-header small text-uppercase">
      <span class="menu-header-text">Configurations</span>
   </li>

   <li v-if="can('access-institution-workspace')"  :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/institutions') }">
      <Link :href="route('admin.institutions.index')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bxs-school"></i>
         </span>
         Institution Details
      </Link>
   </li>

   <li v-if="can('access-users-workspace')"
       :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/users') }">
      <Link :href="route('admin.users.index')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-user"></i>
         </span>
         System users
      </Link>
   </li>

   <li v-if="can('access-roles-workspace')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/roles') }">
      <Link :href="route('admin.roles.index')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-shield-quarter"></i>
         </span>
         System Roles
      </Link>
   </li>

   <li v-if="can('access-divisions-workspace')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/settings') }">
      <Link :href="route('admin.settings.index')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-cog"></i>
         </span>
         System Settings
      </Link>
   </li>




   <li v-if="canAny(['access-pages-workspace'])"
       class="menu-header small text-uppercase">
      <span class="menu-header-text">Website Management</span>
   </li>
   <li v-if="can('access-pages-workspace')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/website/customisations') }">
      <Link :href="route('admin.customisations.index')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-palette"></i>
         </span>
         Customisation
      </Link>
   </li>
   <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/website/components') }">
      <Link class="menu-link" :href="route('admin.components.index')">
         <span>
            <i class='menu-icon tf-icons bx bx-package'></i>
         </span>
         Components
      </Link>
   </li>
   <li v-if="can('access-pages-workspace')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/website/pages') }">
      <Link :href="route('admin.pages.index')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-folder-open"></i>
         </span>
         Pages
      </Link>
   </li>
   <li v-if="can('access-pages-workspace')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/website/menus') }">
      <Link :href="route('admin.menus.index')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-food-menu"></i>
         </span>
         Menu Items
      </Link>
   </li>
   <li v-if="can('access-pages-workspace')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/website/seo-metas') }">
      <Link :href="route('admin.seo-metas.index')" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bxs-dashboard"></i>
         </span>
         SEO Settings
      </Link>
   </li>
</template>

<script>
import {Link} from "@inertiajs/vue3";

export default {
   components: {Link},
   computed: {
      user() {
         return this.$page.props.auth.user;
      },
   },
   methods: {
      hasRole(role) {
         return this.user?.roles?.includes(role);
      },
      hasAnyRole(roles) {
         return roles.some(role => this.user?.roles?.includes(role));
      },
      hasAllRoles(roles) {
         return roles.every(role => this.user?.roles?.includes(role));
      },
      can(permission) {
         return this.user?.permissions?.includes(permission);
      },
      canAny(permissions) {
         return permissions.some(permission => this.user?.permissions?.includes(permission));
      },
      canAll(permissions) {
         return permissions.every(permission => this.user?.permissions?.includes(permission));
      },
   }
}
</script>
