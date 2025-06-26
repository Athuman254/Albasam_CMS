<template>
   <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
      <div class="app-brand demo">
         <a href="#" class="app-brand-link">
            <span v-if="logo" class="app-brand-logo demo">
               <img :src="logo" alt="logo" >
            </span>
            <span v-else class="app-brand-logo demo">
               <svg
                  width="25"
                  viewBox="0 0 25 42"
                  version="1.1"
                  xmlns="http://www.w3.org/2000/svg"
                  xmlns:xlink="http://www.w3.org/1999/xlink">
                        <defs>
                          <path
                             d="M13.7918663,0.358365126 L3.39788168,7.44174259 C0.566865006,9.69408886 -0.379795268,12.4788597 0.557900856,15.7960551 C0.68998853,16.2305145 1.09562888,17.7872135 3.12357076,19.2293357 C3.8146334,19.7207684 5.32369333,20.3834223 7.65075054,21.2172976 L7.59773219,21.2525164 L2.63468769,24.5493413 C0.445452254,26.3002124 0.0884951797,28.5083815 1.56381646,31.1738486 C2.83770406,32.8170431 5.20850219,33.2640127 7.09180128,32.5391577 C8.347334,32.0559211 11.4559176,30.0011079 16.4175519,26.3747182 C18.0338572,24.4997857 18.6973423,22.4544883 18.4080071,20.2388261 C17.963753,17.5346866 16.1776345,15.5799961 13.0496516,14.3747546 L10.9194936,13.4715819 L18.6192054,7.984237 L13.7918663,0.358365126 Z"
                             id="path-1"></path>
                          <path
                             d="M5.47320593,6.00457225 C4.05321814,8.216144 4.36334763,10.0722806 6.40359441,11.5729822 C8.61520715,12.571656 10.0999176,13.2171421 10.8577257,13.5094407 L15.5088241,14.433041 L18.6192054,7.984237 C15.5364148,3.11535317 13.9273018,0.573395879 13.7918663,0.358365126 C13.5790555,0.511491653 10.8061687,2.3935607 5.47320593,6.00457225 Z"
                             id="path-3"></path>
                          <path
                             d="M7.50063644,21.2294429 L12.3234468,23.3159332 C14.1688022,24.7579751 14.397098,26.4880487 13.008334,28.506154 C11.6195701,30.5242593 10.3099883,31.790241 9.07958868,32.3040991 C5.78142938,33.4346997 4.13234973,34 4.13234973,34 C4.13234973,34 2.75489982,33.0538207 2.37032616e-14,31.1614621 C-0.55822714,27.8186216 -0.55822714,26.0572515 -4.05231404e-15,25.8773518 C0.83734071,25.6075023 2.77988457,22.8248993 3.3049379,22.52991 C3.65497346,22.3332504 5.05353963,21.8997614 7.50063644,21.2294429 Z"
                             id="path-4"></path>
                          <path
                             d="M20.6,7.13333333 L25.6,13.8 C26.2627417,14.6836556 26.0836556,15.9372583 25.2,16.6 C24.8538077,16.8596443 24.4327404,17 24,17 L14,17 C12.8954305,17 12,16.1045695 12,15 C12,14.5672596 12.1403557,14.1461923 12.4,13.8 L17.4,7.13333333 C18.0627417,6.24967773 19.3163444,6.07059163 20.2,6.73333333 C20.3516113,6.84704183 20.4862915,6.981722 20.6,7.13333333 Z"
                             id="path-5"></path>
                        </defs>
                        <g id="g-app-brand" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                          <g id="Brand-Logo" transform="translate(-27.000000, -15.000000)">
                            <g id="Icon" transform="translate(27.000000, 15.000000)">
                              <g id="Mask" transform="translate(0.000000, 8.000000)">
                                <mask id="mask-2" fill="white">
                                  <use xlink:href="#path-1"></use>
                                </mask>
                                <use fill="#696cff" xlink:href="#path-1"></use>
                                <g id="Path-3" mask="url(#mask-2)">
                                  <use fill="#696cff" xlink:href="#path-3"></use>
                                  <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-3"></use>
                                </g>
                                <g id="Path-4" mask="url(#mask-2)">
                                  <use fill="#696cff" xlink:href="#path-4"></use>
                                  <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-4"></use>
                                </g>
                              </g>
                              <g
                                 id="Triangle"
                                 transform="translate(19.000000, 11.000000) rotate(-300.000000) translate(-19.000000, -11.000000) ">
                                <use fill="#696cff" xlink:href="#path-5"></use>
                                <use fill-opacity="0.2" fill="#FFFFFF" xlink:href="#path-5"></use>
                              </g>
                            </g>
                          </g>
                        </g>
                      </svg>
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
          
          <li v-if="can('access-class-workspace')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/ranks')}">
              <Link href="/admin/ranks" class="menu-link">
               <span>
                  <i class="menu-icon tf-icons bx bx-chalkboard"></i>
               </span>
                  Classes
              </Link>
          </li>
         
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
         
         <li v-if="can('access-divisions-workspace')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/settings') }">
            <Link :href="route('admin.settings.index')" class="menu-link">
               <span>
                  <i class="menu-icon tf-icons bx bx-cog"></i>
               </span>
               System Settings
            </Link>
         </li>
         
<!--         <li v-if="canAny(['access-divisions-workspace', 'access-streams-workspace', 'access-subjects-workspace'])"  :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/admin/settings') }">-->
<!--            <a href="#" class="menu-link menu-toggle">-->
<!--               <i class="menu-icon tf-icons bx bx-cog"></i>-->
<!--               <div class="text-truncate">System Settings</div>-->
<!--            </a>-->
<!--            <ul class="menu-sub">-->
<!--               <li v-if="can('access-divisions-workspace')"  :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/settings/divisions') }">-->
<!--                  <Link href="/admin/settings/divisions" class="menu-link">-->
<!--                     <div class="text-truncate">Divisions</div>-->
<!--                  </Link>-->
<!--               </li>-->
<!--               <li v-if="can('access-streams-workspace')"  :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/settings/streams') }">-->
<!--                  <Link href="/admin/settings/streams" class="menu-link">-->
<!--                     <div class="text-truncate">Streams</div>-->
<!--                  </Link>-->
<!--               </li>-->
<!--               <li v-if="can('access-subjects-workspace')"  :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/settings/subjects') }">-->
<!--                  <Link href="/admin/settings/subjects" class="menu-link">-->
<!--                     <div class="text-truncate">Subjects</div>-->
<!--                  </Link>-->
<!--               </li>-->
<!--            </ul>-->
<!--         </li>-->
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
      </ul>
      
<!--      <ul v-if="loggedInAs === 'teacher'" class="menu-inner py-1">-->
<!--         &lt;!&ndash; Dashboard &ndash;&gt;-->
<!--         <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/dashboard') }">-->
<!--            <Link :href="route('dashboard')" class="menu-link">-->
<!--               <span>-->
<!--                  <i class="menu-icon tf-icons bx bx-home-alt"></i>-->
<!--               </span>-->
<!--               Dashboard-->
<!--            </Link>-->
<!--         </li>-->
<!--      </ul>-->
   </aside>
</template>

<script>
import {Link} from "@inertiajs/vue3";
export default {
   components: {Link},
   computed: {
      logo() {
         return this.$page.props.logoUrl;
      },
      user() {
         return this.$page.props.auth.user;
      },
      loggedInAs() {
         return localStorage.getItem('loggedInAs') || this.$page.props.auth.user.logged_in_as;
      },
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
.menu .app-brand.demo {
   height: auto;
   margin-bottom: 12px;
}
.app-brand-link {
   width: inherit !important;
}
.app-brand-logo.demo {
   width: inherit;
}
.app-brand-logo img {
   object-fit: cover;
   height: auto;
   width: auto;
}
</style>
