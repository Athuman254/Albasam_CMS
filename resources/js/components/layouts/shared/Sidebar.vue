<template>
   <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
      <div class="app-brand demo">
         <a href="#" class="app-brand-link">
            <span class="app-brand-logo demo">
               <img src="/public/logo.png" alt="logo" class="app-brand-logo demo"
                    style="width: 160px; height: auto;"/>
            </span>
         </a>
         
         <a href="#" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none"
            @click.prevent="toggleSidebar">
            <i class="bx bx-chevron-left bx-sm d-flex align-items-center justify-content-center"></i>
         </a>
      </div>
      
      <div class="menu-inner-shadow"></div>
      
      <ul v-if="loggedInAs === 'admin'" class="menu-inner py-1">
         <!-- Dashboard -->
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/dashboard') }">
            <Link href="/admin/dashboard" class="menu-link">
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
         
         <li v-if="can('access-admission-workspace')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/student-admissions')}">
            <Link href="/admin/student-admissions" class="menu-link">
               <span>
                  <i class="menu-icon tf-icons bx bx-spreadsheet"></i>
               </span>
               Admissions
            </Link>
         </li>
         
         <!--         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/students')}">-->
         <!--            <Link href="#" class="menu-link menu-toggle">-->
         <!--               <span>-->
         <!--                  <i class="menu-icon tf-icons bx bxs-graduation"></i>-->
         <!--               </span>-->
         <!--               Students-->
         <!--            </Link>-->
         <!--         </li>-->
         
         <li v-if="canAny(['access-employee-workspace', 'access-teacher-workspace'])" :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/admin/employees') }">
            <a href="#" class="menu-link menu-toggle">
               <i class='bx bxs-user-badge menu-icon tf-icons'></i>
               <div class="text-truncate" data-i18n="Account Settings">Employee Management</div>
            </a>
            <ul v-if="can('access-teacher-workspace')"  class="menu-sub">
               <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/employees/teachers') }">
                  <Link href="/admin/employees/teachers" class="menu-link">
                     <div class="text-truncate">Teachers</div>
                  </Link>
               </li>
            </ul>
         </li>
         
         <li v-if="can('access-class-workspace')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/ranks')}">
            <Link href="/admin/ranks" class="menu-link">
               <span>
                  <i class="menu-icon tf-icons bx bx-chalkboard"></i>
               </span>
               Classes
            </Link>
         </li>
         
         <li v-if="can('access-calendar')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/calendar')}">
            <Link href="#" class="menu-link">
               <span>
                  <i class="menu-icon tf-icons bx bx-calendar"></i>
               </span>
               Calendar
            </Link>
         </li>
         
         <li v-if="can('access-time-table')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/time-table')}">
            <Link href="/admin/time-table" class="menu-link">
               <span>
                  <i class="menu-icon tf-icons bx bx-table"></i>
               </span>
               Time-Table
            </Link>
         </li>
         
         <li v-if="canAny(['access-bulk-sms', 'access-sms-outbox'])" :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/admin/sms') }">
            <a href="#" class="menu-link menu-toggle">
               <i class='menu-icon tf-icons bx bx-message-dots'></i>
               <div class="text-truncate" data-i18n="Account Settings">Sms Messages</div>
            </a>
            <ul class="menu-sub">
               <li v-if="can('access-bulk-sms')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/sms/compose') }">
                  <Link href="/admin/sms/compose" class="menu-link">
                     <div class="text-truncate">Compose</div>
                  </Link>
               </li>
            </ul>
            <ul class="menu-sub">
               <li v-if="can('access-sms-outbox')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/sms/outbox') }">
                  <Link href="/admin/sms/outbox" class="menu-link">
                     <div class="text-truncate">Outbox</div>
                  </Link>
               </li>
            </ul>
         </li>
         <li v-if="canAny(['access-attendance-workspace', 'access-attendance-report'])" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/attendance') }">
            <a href="#" class="menu-link menu-toggle">
               <i class='bx bx-calendar-check menu-icon tf-icons'></i>
               <div class="text-truncate" data-i18n="Account Settings">Attendance</div>
            </a>
            <ul class="menu-sub">
               <li v-if="can('access-attendance-workspace')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/attendance') }">
                  <Link href="/attendance" class="menu-link">
                     <div class="text-truncate">Mark</div>
                  </Link>
               </li>
               <li v-if="can('access-attendance-report')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/attendance-record') }">
                  <Link href="/attendance-record" class="menu-link">
                     <div class="text-truncate">Records</div>
                  </Link>
               </li>
            </ul>
         </li>
         <li v-if="canAny(['access-institution-workspace', 'access-users-workspace', 'access-roles-workspace', 'access-divisions-workspace', 'access-streams-workspace', 'access-subjects-workspace'])"
               class="menu-header small text-uppercase">
            <span class="menu-header-text">Configurations</span>
         </li>
         
         <li v-if="can('access-institution-workspace')"  :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/institutions') }">
            <Link href="/admin/institutions" class="menu-link">
               <span>
                  <i class="menu-icon tf-icons bx bxs-school"></i>
               </span>
               Institution Details
            </Link>
         </li>
         
         <li v-if="can('access-users-workspace')"
             :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/users') }">
            <Link href="/admin/users" class="menu-link">
               <span>
                  <i class="menu-icon tf-icons bx bx-user"></i>
               </span>
               System users
            </Link>
         </li>
         
         <li v-if="can('access-roles-workspace')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/roles') }">
            <Link href="/admin/roles" class="menu-link">
               <span>
                  <i class="menu-icon tf-icons bx bx-shield-quarter"></i>
               </span>
               System Roles
            </Link>
         </li>
         
         <!--            <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/permissions') }">-->
         <!--                <Link href="/admin/permissions" class="menu-link menu-toggle">-->
         <!--                    <span>-->
         <!--                        <i class="menu-icon tf-icons bx bxs-key"></i>-->
         <!--                    </span>-->
         <!--                    Permissions-->
         <!--                </Link>-->
         <!--            </li>-->
         
         <!-- Pages -->
         <li v-if="canAny(['access-divisions-workspace', 'access-streams-workspace', 'access-subjects-workspace'])"  :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/admin/settings') }">
            <a href="#" class="menu-link menu-toggle  ">
               <i class="menu-icon tf-icons bx bx-cog"></i>
               <div class="text-truncate">System Settings</div>
            </a>
            <ul class="menu-sub">
               <li v-if="can('access-divisions-workspace')"  :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/settings/divisions') }">
                  <Link href="/admin/settings/divisions" class="menu-link">
                     <div class="text-truncate">Divisions</div>
                  </Link>
               </li>
               <li v-if="can('access-streams-workspace')"  :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/settings/streams') }">
                  <Link href="/admin/settings/streams" class="menu-link">
                     <div class="text-truncate">Streams</div>
                  </Link>
               </li>
               <li v-if="can('access-subjects-workspace')"  :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/settings/subjects') }">
                  <Link href="/admin/settings/subjects" class="menu-link">
                     <div class="text-truncate">Subjects</div>
                  </Link>
               </li>
            </ul>
         </li>
         <!-- Pages -->
         <li v-if="canAny(['access-pages-workspace'])" :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/admin/website') }">
            <a href="#" class="menu-link menu-toggle">
               <i class="menu-icon tf-icons bx bx-globe"></i>
               <div class="text-truncate" data-i18n="Account Settings">Web Settings</div>
            </a>
            <ul class="menu-sub">
               <li v-if="can('access-pages-workspace')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/website/pages') }">
                  <Link href="/admin/website/pages" class="menu-link">
                     <div class="text-truncate">Pages</div>
                  </Link>
               </li>
               <li class="menu-item">
                  <Link href="#" class="menu-link">
                     <div class="text-truncate">Site Settings</div>
                  </Link>
               </li>
            </ul>
         </li>
      </ul>
      
      <ul v-if="loggedInAs === 'teacher'" class="menu-inner py-1">
         <!-- Dashboard -->
         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/teacher/dashboard') }">
            <Link href="/teacher/dashboard" class="menu-link">
               <span>
                  <i class="menu-icon tf-icons bx bx-home-alt"></i>
               </span>
               Dashboard
            </Link>
         </li>
      </ul>
   </aside>
</template>

<script>
export default {
   computed: {
      user() {
         return this.$page.props.auth.user;
      },
      loggedInAs() {
         return localStorage.getItem('loggedInAs') || this.$page.props.auth.user.logged_in_as;
      },
      // loggedInAs() {
      //    return this.auth.user.logged_in_as;
      // },
      institution() {
         return this.$page.props.institution;
      }
   },
   mounted() {
      this.initializeMenu();
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
      initializeMenu() {
         const layoutMenuEl = document.querySelector('#layout-menu');
         if (layoutMenuEl) {
            new window.Menu(layoutMenuEl, {
               orientation: 'vertical',
               closeChildren: false,
            });
            // console.log('Menu initialized:', layoutMenuEl);
         } else {
            console.warn('Menu element not found. Initialization skipped.');
         }
      },
      toggleSidebar() {
         if (window.Helpers && typeof window.Helpers.toggleCollapsed === 'function') {
            window.Helpers.toggleCollapsed(); // Call the helper method to close the menu
         } else {
            console.warn('Helpers.toggleCollapsed is not defined.');
         }
      },
   },
};
</script>

<style scoped>
</style>
