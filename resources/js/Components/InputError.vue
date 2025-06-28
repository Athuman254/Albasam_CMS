<script setup>
defineProps({
   message: {
      type: String,
   },
});
</script>

<template>
   <span v-show="message" class="text-danger small">
      {{ message }}
   </span>
</template>
