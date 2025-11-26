<script setup>
import { onMounted, ref } from 'vue';

const showPassword = ref(false);

const toggleShow = () => {
   showPassword.value = !showPassword.value
}

const model = defineModel({
   type: String,
   required: true,
});

const input = ref(null);

onMounted(() => {
   if (input.value.hasAttribute('autofocus')) {
      input.value.focus();
   }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
   <div class="input-group input-group-merge">
      <input
         class="form-control"
         v-model="model"
         ref="input"
      />
      <span class="input-group-text cursor-pointer" @click="toggleShow">
      <i :class="showPassword ? 'bx bx-show' : 'bx bx-hide'"></i>
   </span>
   </div>
</template>
