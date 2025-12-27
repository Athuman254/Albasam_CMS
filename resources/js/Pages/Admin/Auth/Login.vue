<template>
   <GuestLayout>
      <Head title="Login"/>
      
      <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
         {{ status }}
      </div>
      
      
      <div class="nav-align-top mb-4">
         <ul class="nav nav-pills mx-auto" role="tablist">
            <li class="nav-item me-2" role="presentation">
               <Link :href="route('login')" class="nav-link" :class="{ 'active': $page.url.startsWith('/login') }">
                  Administrator
               </Link>
            </li>
            <li class="nav-item me-2" role="presentation">
               <Link :href="route('employee.login')" class="nav-link" :class="{ 'active': $page.url.startsWith('/employee/login') }">
                  Staff
               </Link>
            </li>
<!--            <li class="nav-item" role="presentation">-->
<!--               <Link href="#" class="nav-link" :class="{ 'active': $page.url.startsWith('/guardian/login') }">-->
<!--                  Parent-->
<!--               </Link>-->
<!--            </li>-->
            <li class="nav-item" role="presentation">
               <Link :href="route('login', { portal: 'student' })" class="nav-link" :class="{ 'active': $page.url.includes('portal=student') }">
                  Student
               </Link>
            </li>
         </ul>
      </div>
      
<!--      <h5 class="text-center">Administrator Portal</h5>-->
      
      <div class="card px-sm-6 px-0">
         <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center">
               <a href="#" class="app-brand-link">
                  <span class="app-brand-logo demo">
                     <img src="/images/ecobiz_logo_new.jpeg" alt="logo" style="max-width: 300px; width: 100%; height: auto; filter: invert(1) hue-rotate(180deg);">
                  </span>
               </a>
            </div>
            <!-- /Logo -->
            <form id="formAuthentication" class="mb-6">
               <div class="mb-3">
                  <label for="login_id" class="form-label">Email or Admission Number</label>
                  <input v-model="form.login_id" type="text" class="form-control" id="login_id" required
                         placeholder="Enter your email or admission number"/>
                  <div v-if="form.errors.login_id" class="text-danger">{{ form.errors.login_id }}</div>
               </div>
               <div class="mb-3 form-password-toggle">
                  <label class="form-label" for="password">Password</label>
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
                  <div v-if="form.errors.password" class="text-danger">{{ form.errors.password }}</div>
               </div>
               <div class="mb-8">
                  <div class="d-flex justify-content-between">
                     <div class="form-check mb-0">
                        <Checkbox v-model:checked="form.remember" class="form-check-input" type="checkbox" id="remember"
                                  name="remember"/>
                        <label class="form-check-label" for="remember"> Remember Me </label>
                     </div>
                     <Link v-if="canResetPassword" :href="route('password.request')">
                        <span>Forgot Password?</span>
                     </Link>
                  </div>
               </div>
               <div class="mb-6">
                  <button @click.prevent="submit" class="btn btn-primary d-grid w-100" type="submit">Login</button>
               </div>
            </form>
         </div>
      </div>
   </GuestLayout>
</template>

<script>
import Checkbox from '@components/Checkbox.vue';
import GuestLayout from '@layouts/GuestLayout.vue';
import {Head, Link, useForm} from '@inertiajs/vue3';

export default {
   components: {GuestLayout, Head, Link, Checkbox},
   props: {
      canResetPassword: {
         type: Boolean,
      },
      status: {
         type: String,
      }
   },
   data() {
      return {
         form: useForm({
            login_id: '',
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
         // axios.post('/login', this.form)
         //    .then(({ data }) => {
         //       localStorage.setItem('loggedInAs', this.form.loginAs);
         //       window.location.href = data.redirect;
         //    })
         //    .catch((error) => {
         //       this.$toast.error('Login failed');
         //    });
         this.form.post('login', {
            onSuccess: () => {
               localStorage.setItem('loggedInAs', this.form.loginAs);
               this.form.reset('password')
            },
            onError: () => {
               this.$toast.error('Login failed');
            },
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
