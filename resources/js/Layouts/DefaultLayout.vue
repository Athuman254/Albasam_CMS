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

<style scoped>
.icon-base {
   line-height: 0.95 !important;
}

/* Ensure main content doesn't hide behind fixed sidebar */
.layout-page {
   margin-left: 260px; /* Default sidebar width */
}

/* Adjust for collapsed sidebar */
@media (min-width: 1200px) {
   .layout-wrapper.layout-menu-collapsed .layout-page {
      margin-left: 80px; /* Collapsed sidebar width */
   }
}

/* Mobile - no margin needed as sidebar is overlay */
@media (max-width: 1199.98px) {
   .layout-page {
      margin-left: 0 !important;
   }
}
</style>