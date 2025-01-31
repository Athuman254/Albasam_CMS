<template>
    <div>
        <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
             id="layout-navbar">
            <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
                <a class="nav-item nav-link px-0 me-xl-6" href="#" @click.prevent="toggleSidebar">
                    <i class="bx bx-menu bx-md"></i>
                </a>
            </div>

            <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                <!-- Search -->
<!--                <div class="navbar-nav align-items-center">-->
<!--                    <div class="nav-item d-flex align-items-center">-->
<!--                        <i class="bx bx-search bx-md"></i>-->
<!--                        <input-->
<!--                            type="text"-->
<!--                            class="form-control border-0 shadow-none ps-1 ps-sm-2"-->
<!--                            placeholder="Search..."-->
<!--                            aria-label="Search..." />-->
<!--                    </div>-->
<!--                </div>-->
                <!-- /Search -->

                <ul class="navbar-nav flex-row align-items-center ms-auto">
                    <!-- User -->
                    <li class="nav-item navbar-dropdown dropdown-user dropdown">
                        <a
                            class="nav-link dropdown-toggle hide-arrow p-0"
                            href="#"
                            data-bs-toggle="dropdown"
                        >
                            <div class="avatar-wrapper">
                                <div class="avatar avatar-online">
                                    <i class=" avatar-initial rounded-circle bg-label-secondary bx bxs-user bx-sm"></i>
                                </div>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <Link class="dropdown-item" href="/profile">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="avatar-wrapper">
                                                <div class="avatar avatar-online">
                                                    <i class=" avatar-initial rounded-circle bg-label-secondary bx bxs-user bx-sm"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">{{ user.name }}</h6>
                                            <small class="text-muted">{{ user.username }}</small>
                                        </div>
                                    </div>
                                </Link>
                            </li>
                            <li>
                                <div class="dropdown-divider my-1"></div>
                            </li>
                            <li>
                                <Link class="dropdown-item" href="/profile">
                                    <i class="bx bx-user bx-sm me-3"></i>My Profile
                                </Link>
                            </li>
                            <li>
                                <!-- Logout Button -->
                                <a class="dropdown-item" href="#" @click.prevent="logout">
                                    <i class="bx bx-power-off bx-sm me-3"></i>Log Out
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!--/ User -->
                </ul>
            </div>
        </nav>
    </div>
</template>

<script>
import { router } from '@inertiajs/vue3';

export default {
    computed: {
        user() {
            return this.$page.props.auth.user;
        },
    },
    methods: {
        toggleSidebar() {
            if (window.Helpers && typeof window.Helpers.toggleCollapsed === 'function') {
                window.Helpers.toggleCollapsed(); // Call the existing helper method
            } else {
                console.warn('Helpers.toggleCollapsed is not defined.');
            }
        },
        logout() {
            router.post('/logout');
        }
    },
};
</script>

<style scoped>
#layout-menu.menu-open {
    transform: translateX(0); /* Adjust for visible state */
}
#layout-menu {
    transform: translateX(-100%); /* Adjust for hidden state */
    transition: transform 0.3s ease;
}
</style>
