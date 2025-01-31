<template>
    <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
        <div class="app-brand demo">
            <a href="#" class="app-brand-link">
                <span class="app-brand-logo demo">
                    <img src="/public/nassir_logo.png" alt="logo" class="app-brand-logo demo" style="width: 160px; height: auto;"/>
                </span>
            </a>

            <a href="#" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none" @click.prevent="toggleSidebar">
                <i class="bx bx-chevron-left bx-sm d-flex align-items-center justify-content-center"></i>
            </a>
        </div>

        <div class="menu-inner-shadow"></div>

        <ul class="menu-inner py-1">
            <!-- Dashboard -->
            <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/dashboard') }">
                <Link href="/admin/dashboard" class="menu-link">
                    <span>
                        <i class="menu-icon tf-icons bx bxs-home-smile"></i>
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

            <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/student-admissions')}">
                <Link href="/admin/student-admissions" class="menu-link">
                    <span>
                        <i class="menu-icon tf-icons bx bxs-graduation"></i>
                    </span>
                    Student Admissions
                </Link>
            </li>

            <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/employees/teachers')}">
                <Link href="/admin/employees/teachers" class="menu-link">
                    <span>
                        <i class="menu-icon tf-icons bx bx-book"></i>
                    </span>
                    Registered Teachers
                </Link>
            </li>

            <li :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/admin/sms') }">
                <a href="#" class="menu-link menu-toggle">
                    <i class='menu-icon tf-icons bx bxs-message-dots'></i>
                    <div class="text-truncate" data-i18n="Account Settings">Sms Messages</div>
                </a>
                <ul class="menu-sub">
                    <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/sms/compose') }">
                        <Link href="/admin/sms/compose" class="menu-link">
                            <div class="text-truncate">Compose</div>
                        </Link>
                    </li>
                </ul>
                <ul class="menu-sub">
                    <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/sms/outbox') }">
                        <Link href="/admin/sms/outbox" class="menu-link">
                            <div class="text-truncate">Outbox</div>
                        </Link>
                    </li>
                </ul>
            </li>
            <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/attendance') }">
                <a href="#" class="menu-link menu-toggle">
                    <i class='bx bx-calendar-check menu-icon tf-icons' ></i>
                    <div class="text-truncate" data-i18n="Account Settings">Attendance</div>
                </a>
                <ul class="menu-sub">
                    <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/attendance') }">
                        <Link href="/attendance" class="menu-link">
                            <div class="text-truncate">Mark</div>
                        </Link>
                    </li>
                    <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/attendance-record') }">
                        <Link href="/attendance-record" class="menu-link">
                            <div class="text-truncate">Records</div>
                        </Link>
                    </li>
                </ul>
            </li>
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Configurations</span>
            </li>

            <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/institutions') }">
                <Link href="/admin/institutions" class="menu-link">
                    <span>
                        <i class="menu-icon tf-icons bx bxs-school"></i>
                    </span>
                    Institution Details
                </Link>
            </li>

            <li v-if="can('access-users-workspace')" :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/users') }">
                <Link href="/admin/users" class="menu-link">
                    <span>
                        <i class="menu-icon tf-icons bx bxs-user"></i>
                    </span>
                    System users
                </Link>
            </li>

            <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/roles') }">
                <Link href="/admin/roles" class="menu-link">
                    <span>
                        <i class="menu-icon tf-icons bx bx-shield-quarter"></i>
                    </span>
                    System Roles
                </Link>
            </li>

            <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/permissions') }">
                <Link href="/admin/permissions" class="menu-link">
                    <span>
                        <i class="menu-icon tf-icons bx bxs-key"></i>
                    </span>
                    Permissions
                </Link>
            </li>

            <!-- Pages -->
            <li :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/admin/settings') }">
                <a href="#" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bxs-cog"></i>
                    <div class="text-truncate">System Settings</div>
                </a>
                <ul class="menu-sub">

<!--                    <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/settings/teachers') }">-->
<!--                        <Link href="/admin/settings/teachers" class="menu-link">-->
<!--                            <div class="text-truncate">Teachers</div>-->
<!--                        </Link>-->
<!--                    </li>-->
<!--                    <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/settings/parents') }">-->
<!--                        <Link href="/admin/settings/parents" class="menu-link">-->
<!--                            <div class="text-truncate">Parents</div>-->
<!--                        </Link>-->
<!--                    </li>-->
                    <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/settings/divisions') }">
                        <Link href="/admin/settings/divisions" class="menu-link">
                            <div class="text-truncate">Divisions</div>
                        </Link>
                    </li>
                    <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/settings/streams') }">
                        <Link href="/admin/settings/streams" class="menu-link">
                            <div class="text-truncate">Streams</div>
                        </Link>
                    </li>
                    <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/admin/settings/ranks') }">
                        <Link href="/admin/settings/ranks" class="menu-link">
                            <div class="text-truncate">Classes</div>
                        </Link>
                    </li>
                </ul>
            </li>
             <!-- Pages -->
             <li :class="{ 'menu-item': true, 'active open': $page.url.startsWith('/website') }">
                <a href="#" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-globe"></i>
                    <div class="text-truncate" data-i18n="Account Settings">Web Settings</div>
                </a>
                <ul class="menu-sub">
                    <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/website/pages') }">
                        <Link href="/website/pages" class="menu-link">
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
    </aside>
</template>

<script>
export default {
    computed: {
        user() {
            return this.$page.props.auth.user;
        },
        institution() {
            return this.$page.props.institution;
        }
    },
    mounted() {
        const layoutMenuEl = document.querySelector('#layout-menu');
        if (layoutMenuEl) {
            new window.Menu(layoutMenuEl, {
                orientation: 'vertical',
                closeChildren: false,
            });
            // console.log('Menu initialized:', menu);
        }
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
