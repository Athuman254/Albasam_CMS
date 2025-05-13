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
            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
    </div>
</template>

<script>
import "../../plugins/main.js";
import Sidebar from './shared/Sidebar.vue';
import Navbar from './shared/Navbar.vue';
import Footer from './shared/Footer.vue';
import {Inertia} from "@inertiajs/inertia";

export default {
    components: {
        Sidebar,
        Navbar,
        Footer,
    },
   mounted() {
      Inertia.on('navigate', () => {
         setTimeout(() => {
            if (window.history.state) {
               window.scrollTo(0, window.history.state.scrollY || 0);
            } else {
               window.scrollTo(0, 0);
            }
         }, 100);
      })
   }
};
</script>

<style></style>
