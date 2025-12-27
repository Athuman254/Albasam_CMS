<template>
   <GuestLayout>
      <Head title="Staff Portal"/>
      
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
         </ul>
      </div>
      
      <div class="card px-sm-6 px-0">
         <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center mb-4">
               <a href="#" class="app-brand-link gap-2">
                  <span class="app-brand-logo demo">
                     <img src="/images/ecobiz_logo_new.jpeg" alt="logo" style="max-width: 300px; width: 100%; height: auto; filter: invert(1) hue-rotate(180deg);">
                  </span>
               </a>
            </div>
            <!-- /Logo -->
            
            <div class="text-center mb-4">
               <h4 class="fw-bold mb-1">Welcome to Staff Portal</h4>
               <p class="text-muted">Sign in to your account</p>
            </div>

            <!-- Form with proper error handling -->
            <form @submit.prevent="submit">
               <div class="mb-3">
                  <label for="identifier" class="form-label fw-medium">Email, Staff Number or Phone</label>
                  <input 
                    v-model="form.identifier" 
                    type="text" 
                    class="form-control form-control-lg" 
                    id="identifier"
                    :class="{ 'is-invalid': form.errors.identifier }"
                    placeholder="Enter your email, staff number or phone" 
                    required 
                    autofocus
                  />
                  <div v-if="form.errors.identifier" class="text-danger small mt-2">
                     <i class="bx bx-error-circle me-1"></i>{{ form.errors.identifier }}
                  </div>
               </div>
               
               <div class="mb-4 form-password-toggle">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                     <label class="form-label fw-medium mb-0" for="password">Password</label>
                     <a href="#" class="text-primary small text-decoration-none">Forgot Password?</a>
                  </div>
                  <div class="input-group input-group-merge">
                     <input
                        :type="showPassword ? 'text' : 'password'"
                        id="password"
                        v-model="form.password"
                        class="form-control form-control-lg"
                        :class="{ 'is-invalid': form.errors.password }"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                     />
                     <span class="input-group-text cursor-pointer bg-transparent" @click="toggleShow" type="button">
                        <i :class="showPassword ? 'bx bx-show-alt' : 'bx bx-hide'" class="fs-5"></i>
                     </span>
                  </div>
                  <div v-if="form.errors.password" class="text-danger small mt-2">
                     <i class="bx bx-error-circle me-1"></i>{{ form.errors.password }}
                  </div>
               </div>
               
               <div class="mb-3">
                  <button 
                    type="submit" 
                    class="btn btn-primary btn-lg d-grid w-100 py-2" 
                    :disabled="form.processing"
                    :class="{ 'btn-loading': form.processing }"
                  >
                     <span v-if="form.processing">
                        <i class="bx bx-loader bx-spin me-2"></i>Signing in...
                     </span>
                     <span v-else>
                        <i class="bx bx-log-in me-2"></i>Sign In
                     </span>
                  </button>
               </div>

            </form>
         </div>
      </div>
   </GuestLayout>
</template>

<script>
import GuestLayout from '@layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

export default {
   components: { GuestLayout, Head, Link },
   data() {
      return {
         form: useForm({
            identifier: '',
            password: '',
         }),
         showPassword: false,
         debugMode: process.env.NODE_ENV === 'development', // Remove in production
      }
   },
   computed: {
      logo() {
         return this.$page.props.logoUrl;
      },
   },
   methods: {
      submit() {
         console.log('🔐 Submitting employee login form:', {
            identifier: this.form.identifier,
            password_length: this.form.password.length,
            timestamp: new Date().toISOString()
         });
         
         this.form.post(route('employee.login.submit'), {
            onSuccess: (response) => {
               console.log('✅ Employee login successful', response);
               this.form.reset('password');
               // Optional: Show success message
               this.$toast.success('Login successful! Redirecting...');
            },
            onError: (errors) => {
               console.log('❌ Employee login failed with errors:', errors);
               // Optional: Show error message
               if (errors.password) {
                  this.$toast.error('Invalid password. Please try again.');
               } else if (errors.identifier) {
                  this.$toast.error('No staff member found with those credentials.');
               }
            },
            onFinish: () => {
               console.log('📝 Employee login request finished');
            }
         });
      },
      toggleShow() {
         this.showPassword = !this.showPassword;
      },
   },
   mounted() {
      console.log('👥 Employee Login component mounted');
      // Auto-focus identifier field
      this.$nextTick(() => {
         const identifierInput = document.getElementById('identifier');
         if (identifierInput) {
            identifierInput.focus();
         }
      });
   }
}
</script>

<style scoped>
.nav-align-top {
   max-width: 400px;
   margin: 0 auto;
}

.card {
   max-width: 400px;
   margin: 0 auto;
   border: none;
   box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
   border-radius: 1rem;
   overflow: hidden;
}

.card-body {
   padding: 2.5rem;
}

.app-brand {
   margin-bottom: 2rem;
}

.app-brand-logo {
   display: flex;
   align-items: center;
   justify-content: center;
}

.form-control-lg {
   padding: 0.75rem 1rem;
   font-size: 1rem;
   border-radius: 0.5rem;
   border: 1px solid #d1d5db;
   transition: all 0.15s ease-in-out;
}

.form-control-lg:focus {
   border-color: #696cff;
   box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
}

.form-password-toggle .input-group-text {
   cursor: pointer;
   background-color: transparent;
   border: 1px solid #d1d5db;
   border-left: none;
   padding: 0.75rem 1rem;
   border-radius: 0 0.5rem 0.5rem 0;
   transition: all 0.15s ease-in-out;
}

.form-password-toggle .input-group-text:hover {
   background-color: #f8f9fa;
   color: #696cff;
}

.btn-lg {
   padding: 0.75rem 1.5rem;
   font-size: 1.1rem;
   font-weight: 600;
   border-radius: 0.5rem;
   transition: all 0.15s ease-in-out;
}

.btn-primary {
   background: linear-gradient(135deg, #696cff 0%, #5468ff 100%);
   border: none;
   box-shadow: 0 4px 6px rgba(105, 108, 255, 0.3);
}

.btn-primary:hover {
   background: linear-gradient(135deg, #5468ff 0%, #4054ff 100%);
   transform: translateY(-1px);
   box-shadow: 0 6px 8px rgba(105, 108, 255, 0.4);
}

.btn-primary:active {
   transform: translateY(0);
   box-shadow: 0 2px 4px rgba(105, 108, 255, 0.3);
}

.btn-loading {
   position: relative;
   color: transparent !important;
   pointer-events: none;
}

.btn-loading::after {
   content: '';
   position: absolute;
   width: 20px;
   height: 20px;
   top: 50%;
   left: 50%;
   margin-left: -10px;
   margin-top: -10px;
   border: 2px solid #ffffff;
   border-radius: 50%;
   border-right-color: transparent;
   animation: spinner 0.75s linear infinite;
}

@keyframes spinner {
   to {
      transform: rotate(360deg);
   }
}

.is-invalid {
   border-color: #dc3545 !important;
   background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 3.6.4.4.4-.4'/%3e%3cpath d='M6 7v1'/%3e%3c/svg%3e");
   background-repeat: no-repeat;
   background-position: right calc(0.375em + 0.1875rem) center;
   background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
}

.text-danger small {
   display: flex;
   align-items: center;
   margin-top: 0.25rem;
}

.nav-pills .nav-link {
   border-radius: 0.5rem;
   padding: 0.5rem 1rem;
   font-weight: 500;
   transition: all 0.15s ease-in-out;
}

.nav-pills .nav-link.active {
   background: linear-gradient(135deg, #696cff 0%, #5468ff 100%);
   box-shadow: 0 2px 4px rgba(105, 108, 255, 0.3);
}

.nav-pills .nav-link:not(.active) {
   color: #6b7280;
   background-color: #f9fafb;
}

.nav-pills .nav-link:not(.active):hover {
   color: #374151;
   background-color: #f3f4f6;
}

@media (max-width: 576px) {
   .card-body {
      padding: 2rem 1.5rem;
   }
   
   .nav-align-top {
      max-width: 100%;
   }
}
</style>