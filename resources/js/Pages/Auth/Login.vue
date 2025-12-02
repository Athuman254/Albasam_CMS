<template>
   <GuestLayout>
      <Head title="Login"/>
      
      <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
         {{ status }}
      </div>
      
      <div class="card px-sm-6 px-0">
         <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center mb-4">
               <a href="#" class="app-brand-link gap-2">
                  <span class="app-brand-logo demo">
                     <img src="/images/ecobiz_logo_colored.png" alt="EcoBiz Logo" style="width:120px; height:auto;">
                  </span>
               </a>
            </div>
            <!-- /Logo -->
            
          
            <p class="mb-4 text-center text-muted">Secure Login Portal</p>

            <form id="formAuthentication" class="mb-3" @submit.prevent="submit">
               <div class="mb-3">
                  <label for="username" class="form-label">Username / Admission Number</label>
                  <input 
                     v-model="form.username" 
                     type="text" 
                     class="form-control" 
                     id="username" 
                     required
                     autofocus
                     placeholder="Enter your username"
                  />
                  <div v-if="form.errors.username" class="text-danger mt-1 text-sm">{{ form.errors.username }}</div>
               </div>
               
               <div class="mb-3 form-password-toggle">
                  <div class="d-flex justify-content-between">
                     <label class="form-label" for="password">Password</label>
                  </div>
                  <div class="input-group input-group-merge">
                     <input
                        :type="showPassword ? 'text' : 'password'"
                        id="password"
                        v-model="form.password"
                        class="form-control"
                        name="password"
                        placeholder="Enter your password"
                        aria-describedby="password"
                     />
                     <span class="input-group-text cursor-pointer" @click="toggleShow">
                        <i :class="showPassword ? 'bx bx-show' : 'bx bx-hide'"></i>
                     </span>
                  </div>
                  <div v-if="form.errors.password" class="text-danger mt-1 text-sm">{{ form.errors.password }}</div>
               </div>
               
               <div class="mb-3">
                  <div class="form-check">
                     <Checkbox v-model:checked="form.remember" class="form-check-input" type="checkbox" id="remember" name="remember"/>
                     <label class="form-check-label" for="remember"> Remember Me </label>
                  </div>
               </div>
               
               <div class="mb-3">
                  <button class="btn btn-primary d-grid w-100" type="submit" :disabled="form.processing">
                     {{ form.processing ? 'Signing in...' : 'Sign in' }}
                  </button>
               </div>
            </form>
            
            <div class="text-center">
                <Link v-if="canResetPassword" :href="route('password.request')" class="d-flex align-items-center justify-content-center">
                    <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm"></i>
                    Forgot Password?
                </Link>
            </div>
         </div>
      </div>
   </GuestLayout>
</template>

<script>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import {Head, Link, useForm} from '@inertiajs/vue3';

export default {
   components: {GuestLayout, Head, Link, Checkbox},
   props: {
      canResetPassword: {
         type: Boolean,
         default: true
      },
      status: {
         type: String,
      }
   },
   data() {
      return {
         form: useForm({
            username: '',
            password: '',
            remember: false,
         }),
         showPassword: false,
      }
   },
   computed: {
      logo() {
         return this.$page.props.logoUrl;
      },
   },
   methods: {
      submit() {
         this.form.post(route('login'), {
            onFinish: () => this.form.reset('password'),
         });
      },
      toggleShow() {
         this.showPassword = !this.showPassword;
      },
   }
}
</script>

<style scoped>
</style>
