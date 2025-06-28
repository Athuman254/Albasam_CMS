<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import {useForm} from '@inertiajs/vue3';
import {ref} from 'vue';
import { getCurrentInstance } from 'vue';

const passwordInput = ref(null);
const currentPasswordInput = ref(null);
const toast = getCurrentInstance().proxy.$toast;

const form = useForm({
   current_password: '',
   password: '',
   password_confirmation: '',
});

const updatePassword = () => {
   form.put(route('password.update'), {
      preserveScroll: true,
      onSuccess: () => {
         form.reset();
         toast.success('Password updated successfully', 'Success')
      },
      onError: () => {
         if (form.errors.password) {
            form.reset('password', 'password_confirmation');
            passwordInput.value.focus();
         }
         if (form.errors.current_password) {
            form.reset('current_password');
            currentPasswordInput.value.focus();
         }
      },
   });
};
</script>

<template>
   <form @submit.prevent="updatePassword" class="col-12">
      <div class="row mb-3">
         <div class="col-lg-6 col-md-6 col-12">
            <div class="mb-3">
               <InputLabel for="current_password" value="Current Password"/>
               <TextInput
                  id="current_password"
                  ref="currentPasswordInput"
                  v-model="form.current_password"
                  type="password"
                  autocomplete="current-password"
               />
               <InputError :message="form.errors.current_password" class="mt-2"/>
            </div>
         </div>
         <div class="col-lg-6 col-md-6 col-12"></div>
         <div class="col-lg-6 col-md-6 col-12">
            <div class="mb-3">
               <InputLabel for="password" value="New Password"/>
               <TextInput
                  id="password"
                  ref="passwordInput"
                  v-model="form.password"
                  type="password"
                  autocomplete="new-password"
               />
               <InputError :message="form.errors.password" class="mt-2"/>
            </div>
         </div>
         <div class="col-lg-6 col-md-6 col-12">
            <div class="mb-3">
               <InputLabel for="password_confirmation" value="Confirm Password"/>
               <TextInput
                  id="password_confirmation"
                  v-model="form.password_confirmation"
                  type="password"
                  autocomplete="new-password"
               />
               <InputError :message="form.errors.password_confirmation" class="mt-2"/>
            </div>
         </div>
      </div>
      
      <div class="flex items-center gap-4">
         <PrimaryButton :disabled="form.processing">Save</PrimaryButton>
      </div>
   </form>
</template>
