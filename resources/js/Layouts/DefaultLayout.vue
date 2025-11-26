<template>
   <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
         <!-- Sidebar -->
         <Sidebar />
         
         <!-- Page content -->
         <div class="layout-page">
            <Navbar />
            <div class="content-wrapper">
               <div class="container-xxl flex-grow-1 container-p-y">
                  <slot />
               </div>
               <Footer />
            </div>
         </div>
         <div class="layout-overlay layout-menu-toggle" @click="toggleSidebar"></div>
      </div>
   </div>
</template>

<script>
import "@plugins/main.js";
import Sidebar from './shared/Sidebar.vue';
import Navbar from './shared/Navbar.vue';
import Footer from './shared/Footer.vue';

export default {
   components: {
      Sidebar,
      Navbar, 
      Footer,
   },
   methods: {
      toggleSidebar() {
         if (window.Helpers && typeof window.Helpers.toggleCollapsed === 'function') {
            window.Helpers.toggleCollapsed();
         }
      }
   },
   mounted() {
      console.log('DefaultLayout component mounted with sidebar');
   }
};
</script>

<style>
.icon-base {
   line-height: 0.95 !important;
}
</style>