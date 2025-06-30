<template>
   <li :class="{ 'menu-item': true, 'active': $page.url.startsWith('/employee/dashboard') }">
      <Link :href="route('employee.dashboard')" class="menu-link">
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
</template>

<script>
import {Link} from "@inertiajs/vue3";

export default {
   components: {Link},
   computed: {
      staff() {
         return this.$page.props.auth.employee;
      },
   },
}
</script>
