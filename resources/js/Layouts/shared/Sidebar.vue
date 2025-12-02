<template>
   <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
      <div class="app-brand demo">
         <a href="#" class="app-brand-link">
            <span v-if="logo" class="app-brand-logo demo">
               <img :src="logo" alt="logo" >
            </span>
            <span v-else class="app-brand-logo demo">
               <img src="/assets/img/skasssms.jpeg" alt="logo" style="max-width: 200px; height: auto;">
            </span>
         </a>

         <a href="#" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none"
            @click.prevent="toggleSidebar">
            <i class="bx bx-chevron-left bx-sm d-flex align-items-center justify-content-center"></i>
         </a>
      </div>

      <div class="menu-inner-shadow"></div>

      <ul v-if="user && ($page.url.startsWith('/admin') || $page.url.startsWith('/timetable'))" class="menu-inner py-1 ps">
         <AdminMenu />
      </ul>
      <ul v-if="staff && ($page.url.startsWith('/employee') || $page.url.startsWith('/teacher'))" class="menu-inner py-1 ps">
         <EmployeeMenu />
      </ul>
      <ul v-if="student && $page.url.startsWith('/student')" class="menu-inner py-1 ps">
         <StudentMenu />
      </ul>

   </aside>
</template>

<script>
import {Link} from "@inertiajs/vue3";
import AdminMenu from "@layouts/shared/AdminMenu.vue";
import EmployeeMenu from "@layouts/shared/EmployeeMenu.vue";
import StudentMenu from "@layouts/shared/StudentMenu.vue";
import {Menu} from '@plugins/menu.js';

export default {
   components: {Link, AdminMenu, EmployeeMenu, StudentMenu},
   computed: {
      logo() {
         return this.$page.props.logoUrl;
      },
      user() {
         return this.$page.props.auth.user;
      },
      staff() {
         return this.$page.props.auth.employee;
      },
      student() {
         return this.$page.props.auth.student;
      },
   },
   mounted() {
      this.$nextTick(() => {
         this.initializeMenu();
      });
   },
   methods: {
      initializeMenu() {
         const layoutMenuEl = document.querySelector('#layout-menu');

         if (layoutMenuEl && layoutMenuEl.querySelector('.menu-inner')) {
            new Menu(layoutMenuEl, {
               orientation: 'vertical',
               closeChildren: false,
            }, window.PerfectScrollbar);
         } else {
            console.warn('Menu element not found. Initialization skipped.');
         }
      },
      toggleSidebar() {
         if (window.Helpers && typeof window.Helpers.toggleCollapsed === 'function') {
            window.Helpers.toggleCollapsed(); 
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
   width: 100% !important;
}
.app-brand-logo.demo {
   width: 100%;
}
.app-brand-logo img {
   height: auto;
   width: 100%;
}

/* Fixed sidebar that stays in place when scrolling */
#layout-menu {
   position: fixed !important;
   top: 0;
   left: 0;
   height: 100vh;
   overflow-y: auto;
   overflow-x: hidden;
   z-index: 1000;
}

/* Ensure menu content is scrollable */
.menu-inner {
   max-height: calc(100vh - 80px);
   overflow-y: auto;
}
</style>
