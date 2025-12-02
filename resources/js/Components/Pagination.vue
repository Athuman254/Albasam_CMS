<template>
  <nav v-if="links.length > 3" aria-label="Page navigation">
    <ul class="pagination justify-content-center mb-0">
      <li v-for="(link, key) in links" :key="key" class="page-item" :class="{ 'active': link.active, 'disabled': link.url === null }">
        <div
          v-if="link.url === null"
          class="page-link"
          v-html="link.label"
        />
        <Link
          v-else
          class="page-link"
          :href="link.url"
          v-html="link.label"
        />
      </li>
    </ul>
  </nav>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  links: Array,
});
</script>

<style scoped>
.page-link {
  cursor: pointer;
}
</style>
