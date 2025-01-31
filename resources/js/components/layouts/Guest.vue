<script>
import "../../plugins/main.js";

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
