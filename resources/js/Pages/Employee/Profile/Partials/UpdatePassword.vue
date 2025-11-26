<template>
   <h4>Change Password</h4>
   <div class="row">
      <div class="col-md-8">
         <div v-if="passwordChangeMessage" class="alert my-3" :class="passwordChangeSuccess ? 'alert-success' : 'alert-danger'">
            <i :class="passwordChangeSuccess ? 'bx bx-check-circle me-2' : 'bx bx-error me-2'"></i>
            {{ passwordChangeMessage }}
         </div>
         <form @submit.prevent="changePassword" class="password-change-form">
            <div class="mb-3">
               <label for="currentPassword" class="form-label">Current Password</label>
               <div class="input-group input-group-merge">
                  <input
                     :type="showCurrentPassword ? 'text' : 'password'"
                     class="form-control"
                     id="currentPassword"
                     v-model="passwordForm.currentPassword"
                     placeholder="Enter your current password"
                     required
                  >
                  <span class="input-group-text cursor-pointer" @click="showCurrentPassword = !showCurrentPassword">
                     <i :class="showCurrentPassword ? 'bx bx-hide' : 'bx bx-show'"></i>
                  </span>
               </div>
               <div v-if="passwordForm.errors.currentPassword" class="text-danger small mt-1">
                  {{ passwordForm.errors.currentPassword }}</div>
            </div>
            
            <div class="mb-3">
               <label for="newPassword" class="form-label">New Password</label>
               <div class="input-group input-group-merge">
                  <input
                     :type="showNewPassword ? 'text' : 'password'"
                     class="form-control"
                     id="newPassword"
                     v-model="passwordForm.password"
                     placeholder="Enter your new password"
                     required
                  >
                  <span class="input-group-text cursor-pointer" @click="showNewPassword = !showNewPassword">
                     <i :class="showNewPassword ? 'bx bx-hide' : 'bx bx-show'"></i>
                  </span>
               </div>
               <div class="form-text">
                  Password must be at least 8 characters long and contain uppercase, lowercase, number, and special character.
               </div>
            </div>
            
            <div class="mb-3">
               <label for="password_confirmation" class="form-label">Confirm New Password</label>
               <div class="input-group input-group-merge">
                  <input
                     :type="showPasswordConfirmation ? 'text' : 'password'"
                     class="form-control"
                     id="password_confirmation"
                     v-model="passwordForm.password_confirmation"
                     placeholder="Confirm your new password"
                     required
                  >
                  <span class="input-group-text cursor-pointer" @click="showPasswordConfirmation = !showPasswordConfirmation">
                     <i :class="showPasswordConfirmation ? 'bx bx-hide' : 'bx bx-show'"></i>
                  </span>
               </div>
               <div v-if="passwordForm.password && passwordForm.password_confirmation && passwordForm.password !== passwordForm.password_confirmation" class="text-danger small mt-1">
                  Passwords do not match
               </div>
            </div>
            
            
            <div v-if="passwordForm.password" class="mb-3">
               <label class="form-label">Password Strength</label>
               <div class="progress" style="height: 8px;">
                  <div
                     class="progress-bar"
                     :class="passwordStrengthClass"
                     :style="{ width: passwordStrengthWidth + '%' }"
                  ></div>
               </div>
               <small class="text-muted">{{ passwordStrengthText }}</small>
            </div>
            
            <div class="d-flex gap-2 justify-content-end">
               <button type="button" class="btn btn-outline-secondary" @click="resetPasswordForm">
                  Cancel
               </button>
               <button
                  type="submit"
                  class="btn btn-primary"
                  :disabled="!isPasswordFormValid || isChangingPassword"
               >
                  <span v-if="isChangingPassword" class="spinner-border spinner-border-sm me-2"></span>
                  {{ isChangingPassword ? 'Changing...' : 'Change Password' }}
               </button>
            </div>
         </form>
      </div>
   </div>
</template>

<script setup>
import {useForm} from "@inertiajs/vue3";
import {computed, ref, inject} from "vue";

const props = defineProps({
   employee:{
      type:Object,
      required:true
   }
})

const toast = inject('toast');

const passwordForm = useForm({
   currentPassword: '',
   password: '',
   password_confirmation: ''
});


const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showPasswordConfirmation = ref(false);

const isChangingPassword = ref(false);
const passwordChangeMessage = ref('');
const passwordChangeSuccess = ref(false);

const passwordStrength = computed(() => {
   const password = passwordForm.password;
   if (!password) return 0;
   
   let strength = 0;
   if (password.length >= 8) strength += 25;
   if (/[a-z]/.test(password)) strength += 25;
   if (/[A-Z]/.test(password)) strength += 25;
   if (/\d/.test(password)) strength += 12.5;
   if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 12.5;
   
   return Math.min(strength, 100);
});

const passwordStrengthClass = computed(() => {
   const strength = passwordStrength.value;
   if (strength < 25) return 'bg-danger';
   if (strength < 50) return 'bg-warning';
   if (strength < 75) return 'bg-info';
   return 'bg-success';
});

const passwordStrengthWidth = computed(() => passwordStrength.value);

const passwordStrengthText = computed(() => {
   const strength = passwordStrength.value;
   if (strength < 25) return 'Weak';
   if (strength < 50) return 'Fair';
   if (strength < 75) return 'Good';
   return 'Strong';
});

const isPasswordFormValid = computed(() => {
   return passwordForm.currentPassword &&
      passwordForm.password &&
      passwordForm.password_confirmation &&
      passwordForm.password === passwordForm.password_confirmation &&
      passwordForm.password.length >= 8;
});

const changePassword = () => {
   isChangingPassword.value = true;
   passwordChangeMessage.value = '';
   
   passwordForm.post(route('employee.password.update'), {
      onSuccess: () => {
         passwordChangeSuccess.value = true;
         passwordChangeMessage.value = 'Password changed successfully!';
         toast.success('Password changed successfully!', 'Success');
         
         setTimeout(() => {
            resetPasswordForm();
         }, 2000);
         
      },
      onError: (errors) => {
         passwordChangeSuccess.value = false;
         passwordChangeMessage.value = errors.currentPassword ? 'Current password is incorrect.' : 'An error occurred. Please try again later.';
         toast.error('An error occurred! Please try again later!', 'Error');
      },
      onFinish: () => {
         isChangingPassword.value = false;
         setTimeout(() => {
            resetPasswordForm();
         }, 2000);
      }
   });
};

const resetPasswordForm = () => {
   passwordForm.reset()
   passwordForm.clearErrors()
   showCurrentPassword.value = false;
   showNewPassword.value = false;
   showPasswordConfirmation.value = false;
   passwordChangeMessage.value = '';
};
</script>

<style scoped>
.password-change-form {
   background: #f8f9fa;
   padding: 2rem;
   border-radius: 0.375rem;
   border: 1px solid #e9ecef;
}
.password-change-form .form-label {
   font-weight: 600;
   color: #495057;
}
/* .password-change-form .input-group .btn {
   border-left: none;
} */
.password-change-form .progress {
   border-radius: 4px;
}
.spinner-border-sm {
   width: 1rem;
   height: 1rem;
}
</style>
