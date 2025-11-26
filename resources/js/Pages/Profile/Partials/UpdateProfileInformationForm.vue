<script>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';

export default {
   props: {
      mustVerifyEmail: {
         type: Boolean,
         default: false,
      },
      status: {
         type: String,
         default: '',
      },
      user: {
         type: Object,
         required: true,
      },
   },
   
   components: {
      InputError,
      InputLabel,
      PrimaryButton,
      TextInput,
   },
   
   data() {
      return {
         form: useForm({
            name: this.user.name,
            username: this.user.username,
            email: this.user.email,
            phone: this.user.phone,
         }),
      };
   },
   methods: {
      updateUserDetails() {
         this.form.patch(route('profile.update'), {
            onSuccess: () => {
               this.$toast.success('Details updated successfully', 'Success')
            },
            onError: (errors) => {
               console.log(errors);
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         })
      },
   }
};
</script>

<template>
   <div class="col-12">
      <div class="row mb-3">
         <div class="col-lg-6 col-mb-6 col-12">
            <div class="mb-3">
               <InputLabel for="name" value="Name"/>
               <TextInput
                  id="name"
                  type="text"
                  v-model="form.name"
                  required
                  autofocus
                  autocomplete="name"
               />
               <InputError class="mt-2" :message="form.errors.name"/>
            </div>
         </div>
         <div class="col-lg-6 col-mb-6 col-12">
            <div class="mb-3">
               <InputLabel for="username" value="Username"/>
               <TextInput
                  id="username"
                  type="text"
                  v-model="form.username"
                  autocomplete="username"
               />
               <InputError class="mt-2" :message="form.errors.username"/>
            </div>
         </div>
         <div class="col-lg-6 col-mb-6 col-12">
            <div class="mb-3">
               <InputLabel for="email" value="Email"/>
               <TextInput
                  id="email"
                  type="email"
                  v-model="form.email"
                  required
                  autocomplete="username"
               />
               <InputError class="mt-2" :message="form.errors.email"/>
            </div>
         </div>
         <div class="col-lg-6 col-mb-6 col-12">
            <div class="mb-3">
               <InputLabel for="phone" value="Telephone"/>
               <TextInput
                  id="phone"
                  type="text"
                  v-model="form.phone"
                  required
                  autocomplete="phone"
               />
               <InputError class="mt-2" :message="form.errors.phone"/>
            </div>
         </div>
      </div>
   
      <div class="mb-3" v-if="mustVerifyEmail && user.email_verified_at === null">
         <p class="pb-1 mb-0 text-muted">
            Your email address is unverified.
         </p>
         <Link
            :href="route('verification.send')"
            method="post"
            as="button"
            class="btn btn-secondary mb-3"
         >
            Click here to re-send the verification email.
         </Link>
         
         <div v-show="status === 'verification-link-sent'" class="mt-2 text-muted">
            A new verification link has been sent to your email address.
         </div>
      </div>
      
      <div class="flex items-center gap-4">
         <PrimaryButton @click.prevent="updateUserDetails" :disabled="form.processing">Save</PrimaryButton>
      </div>
   </div>
</template>
