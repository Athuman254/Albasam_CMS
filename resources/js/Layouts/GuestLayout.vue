<script>
import "@plugins/main.js";
import "../../scss/pages/page-auth.scss";

export default {

};
</script>

<template>
   <div class="container-xxl">
      <div class="authentication-wrapper authentication-basic container-p-y">
         <div class="authentication-inner">
            <!-- Register -->
            <slot />
            <!-- /Register -->
         </div>
      </div>
   </div>
</template>

<style>
/* Add your layout-specific styles here if needed */
</style>
