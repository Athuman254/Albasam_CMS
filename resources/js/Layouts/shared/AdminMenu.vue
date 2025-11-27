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

         
         <!-- Assign Classes Link in Employee Management Submenu - ALWAYS SHOW -->
         <li :class="{ 'menu-item': true, 'active': $page.url.includes('assign-classes') }">
            <Link :href="route('admin.employees.index')" class="menu-link">
               <div class="text-truncate">Assign Classes</div>
            </Link>
         </li>

         <!-- Assign Subjects Link in Employee Management Submenu - ALWAYS SHOW -->
         <li :class="{ 'menu-item': true, 'active': $page.url.includes('assign-subjects') }">
            <Link :href="route('admin.employees.index')" class="menu-link">
               <div class="text-truncate">Assign Subjects</div>
            </Link>
         </li>
      </ul>
   </li>

   <!-- Contextual Assign Classes Link (Visible when viewing any employee) -->
   <li v-if="$page.props.employee && $page.props.employee.id && can('access-teacher-workspace')" 
       :class="{ 'menu-item': true, 'active': $page.url.includes('assign-classes') }">
      <Link :href="route('admin.employees.assign-classes', $page.props.employee.id)" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-chalkboard"></i>
         </span>
         Assign Classes
         <span v-if="$page.props.employee.assigned_classes_count > 0" class="badge bg-primary ms-auto">
            {{ $page.props.employee.assigned_classes_count }}
         </span>
      </Link>
   </li>

   <!-- Contextual Assign Subjects Link (Visible when viewing any employee) -->
   <li v-if="$page.props.employee && $page.props.employee.id && can('access-teacher-workspace')" 
       :class="{ 'menu-item': true, 'active': $page.url.includes('assign-subjects') }">
      <Link :href="route('admin.employees.assign-subjects', $page.props.employee.id)" class="menu-link">
         <span>
            <i class="menu-icon tf-icons bx bx-book"></i>
         </span>
         Assign Subjects
         <span v-if="$page.props.employee.assigned_subjects_count > 0" class="badge bg-info ms-auto">
            {{ $page.props.employee.assigned_subjects_count }}
         </span>
      </Link>
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

   <li :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/timetable')}">
      <a href="#" class="menu-link menu-toggle">
         <i class="menu-icon tf-icons bx bx-table"></i>
         <div class="text-truncate">Timetable</div>
      </a>
      <ul class="menu-sub">
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/timetable/setup/periods') }">
            <Link :href="route('timetable.periods.index')" class="menu-link">
               <div class="text-truncate">Periods</div>
            </Link>
         </li>
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/timetable/setup/allocations') }">
            <Link :href="route('timetable.allocations.index')" class="menu-link">
               <div class="text-truncate">Allocations</div>
            </Link>
         </li>
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/timetable/setup/constraints') }">
            <Link :href="route('timetable.constraints.index')" class="menu-link">
               <div class="text-truncate">Constraints</div>
            </Link>
         </li>
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/timetable/generate') }">
            <Link :href="route('timetable.generate.index')" class="menu-link">
               <div class="text-truncate">Generate</div>
            </Link>
         </li>
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/timetable/view/class') }">
            <Link :href="route('timetable.view.class')" class="menu-link">
               <div class="text-truncate">Class View</div>
            </Link>
         </li>
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/timetable/view/teacher') }">
            <Link :href="route('timetable.view.teacher')" class="menu-link">
               <div class="text-truncate">Teacher View</div>
            </Link>
         </li>
      </ul>
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
         <li v-if="can('access-attendance-report')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/reports/attendance') }">
            <Link :href="route('admin.reports.attendance')" class="menu-link">
               <div class="text-truncate">Attendance Report</div>
            </Link>
         </li>
      </ul>
   </li>

   <li :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/admin/exams')  }">
      <a href="#" class="menu-link menu-toggle">
         <i class='bx bx-wallet menu-icon tf-icons'></i>
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
         <!-- Changed from Upload Exam to Approval Queue -->
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/exams/approval-queue') }">
            <Link :href="route('admin.exams.approval-queue.index')" class="menu-link">
               <div class="text-truncate">Approval Results</div>
            </Link>
         </li>
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/exams/results') }">
            <Link :href="route('admin.exams.results.index')" class="menu-link">
               <div class="text-truncate">Exam Reports</div>
            </Link>
         </li>
      </ul>
   </li>
   
  <!-- FEE MANAGEMENT - CORRECTED WITH ACTUAL ROUTE NAMES -->
<li :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/admin/fees') || $page.url.startsWith('/admin/fee-structures') }">
  <a href="javascript:void(0)" class="menu-link menu-toggle">
    <i class='bx bx-credit-card menu-icon tf-icons'></i>
    <div class="text-truncate">Fee Management</div>
  </a>
  

  <ul class="menu-sub">
    <!-- Main Fees Page -->
    <li :class="{ 'menu-item': true, 'active': $page.url === '/admin/fees' }">
      <Link :href="route('admin.fees.index')" class="menu-link">
        <div class="text-truncate">Fee Statement</div>
      </Link>
    </li>
    <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/fees/reports/fee-report') }">
      <Link :href="route('admin.fees.reports.fee-report')" class="menu-link">
        <div class="text-truncate">Fee Reports</div>
      </Link>
    </li>
    <!-- Fee Structures -->
    <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/fee-structures') }">
      <Link :href="route('admin.fee-structures.index')" class="menu-link">
        <div class="text-truncate">Fee Structures</div>
      </Link>
    </li>
    
    <!-- Payment Verification -->
    <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/fees/payments/verify') }">
      <Link :href="route('admin.fees.payments.verify')" class="menu-link">
        <div class="text-truncate">Verify Payment</div>
      </Link>
    </li>
    
    <!-- Payment History
    <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/fees/payments') && !$page.url.includes('/verify') }">
      <Link :href="route('admin.fees.payments.index')" class="menu-link">
        <div class="text-truncate">Payment History</div>
      </Link>
    </li>
     -->
    <!-- Transfer Funds -->
    <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/fees/transfers') }">
      <Link :href="route('admin.fees.transfers.create')" class="menu-link">
        <div class="text-truncate">Transfer Funds</div>
      </Link>
    </li>
    
    <!-- Transfer History
    <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/fees/transfers') && $page.url.endsWith('/transfers') }">
      <Link :href="route('admin.fees.transfers.index')" class="menu-link">
        <div class="text-truncate">Transfer History</div>
      </Link>
    </li> -->
    
    <!-- Reports
    <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/fees/reports') }">
      <Link :href="route('admin.fees.reports.overview')" class="menu-link">
        <div class="text-truncate">Reports & Analysis</div>
      </Link>
    </li> -->
  </ul>
</li>

<li :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/admin/reports') }">
   <a href="#" class="menu-link menu-toggle">
      <i class='bx bx-bar-chart-alt-2 menu-icon tf-icons'></i>
      <div class="text-truncate">Reports</div>
   </a>
   <ul class="menu-sub">
      <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/reports/promotions') }">
         <Link :href="route('admin.reports.promotions')" class="menu-link">
            <div class="text-truncate">Promotion Report</div>
         </Link>
      </li>
   </ul>
</li>

   <li :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/admin/attendance') || $page.url.startsWith('/admin/payroll/run') }">
      <a href="#" class="menu-link menu-toggle">
         <i class='bx bx-wallet menu-icon tf-icons'></i>
         <div class="text-truncate">Payroll</div>
      </a>
      <ul class="menu-sub">
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/reports/attendance') }">
            <Link :href="route('admin.adjustment.index')" class="menu-link">
               <div class="text-truncate">Payroll Adjustments</div>
            </Link>
         </li>
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/payroll/run') }">
            <Link :href="route('admin.payroll.run')" class="menu-link">
               <div class="text-truncate">Run</div>
            </Link>
         </li>
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/payroll/run') }">
            <Link :href="route('admin.payroll.index')" class="menu-link">
               <div class="text-truncate">payrolls</div>
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