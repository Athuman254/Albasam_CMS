<template>
   <div>
      <nav
         class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
         id="layout-navbar">
         <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0 d-xl-none">
            <a class="nav-item nav-link px-0 me-xl-6" href="#" @click.prevent="toggleSidebar">
               <i class="bx bx-menu bx-md"></i>
            </a>
         </div>
         
         <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
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
                        <Link v-if="user" class="dropdown-item" :href="route('profile.edit')">
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
                        <Link v-if="staff" class="dropdown-item" :href="route('employee.profile.index')">
                           <div class="d-flex">
                              <div class="flex-shrink-0 me-3">
                                 <div class="avatar-wrapper">
                                    <div class="avatar avatar-online">
                                       <i class=" avatar-initial rounded-circle bg-label-secondary bx bxs-user bx-sm"></i>
                                    </div>
                                 </div>
                              </div>
                              <div class="flex-grow-1">
                                 <h6 class="mb-0">{{ staff.name }}</h6>
                                 <small class="text-muted">{{ staff.staff_number }}</small>
                              </div>
                           </div>
                        </Link>
                     </li>
                     <li>
                        <div class="dropdown-divider my-1"></div>
                     </li>
                     <li>
                        <a v-if="user" class="dropdown-item" href="#" @click.prevent="logout">
                           <i class="icon-base bx bx-power-off bx-sm me-3"></i>Log Out
                        </a>
                        <a v-if="staff" class="dropdown-item" href="#" @click.prevent="staffLogout">
                           <i class="icon-base bx bx-power-off bx-sm me-3"></i>Log Out
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
import {router, Link} from '@inertiajs/vue3';

export default {
   components: {Link},
   computed: {
      user() {
         return this.$page.props.auth.user;
      },
      staff() {
         return this.$page.props.auth.employee;
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
      },
      staffLogout() {
         router.post('/employee/logout');
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
