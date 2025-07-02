<template>
   <Head title="Employee Profile" />

   <DefaultLayout>
      <div class="py-3 py-lg-4">
         <!-- start page title -->
         <div class="row pt-3">
            <div class="col-xl-9 profile">

               <div class="card">
                  <div :style="{ backgroundImage: `url(/website/images/mission.jpeg)` }" class="d-flex profile-cover position-relative mb-2">
                     <div class="company-logo">
                        <img class="w-100" src="/assets/profile/avatar.svg" alt="Employee Avatar">
                     </div>
                  </div>
                  <div class="d-flex justify-content-end">
                     <!-- <button class="btn bg-yellow fw-bold px-4 rounded-lg me-2"><i class='bx bx-plus'></i> View Details</button> -->
                  </div>
                  <div class="card-body">
                     <div class="d-flex justify-content-between align-items-start">
                        <div class="company-name mt-2">
                           <span class="d-flex gap-4">
                              <h3>{{ `${employee.first_name || ''} ${employee.middle_name || ''} ${employee.last_name || ''}` }}</h3>
                              <!-- <Link class="small"><i class="bi bi-pencil-square"></i> Edit</Link> -->
                           </span>

                           <span class="fw-semibold">{{ employee.teacher?.job.title }}
                              <span class="badge bg-success ms-2" v-if="employee.employment_status">
                                 <i class='bx bx-wifi-0'></i> {{ employee.employment_status.name }}
                              </span>
                           </span>
                           <p class="text-muted">Employee ID: {{ employee.staff_number  }}</p>
                        </div>
                     </div>

                     <!-- Navigation Tabs -->
                     <ul class="nav nav-pills mb-4 gap-2" role="tablist">
                        <li class="nav-item" role="presentation">
                           <a href="#" class="nav-link active px-4" data-bs-toggle="tab"
                              data-bs-target="#employee-details-section" role="tab" aria-selected="true">
                              Employee Details
                           </a>
                        </li>
                        <li class="nav-item" role="presentation">
                           <a href="#" class="nav-link px-4" data-bs-toggle="tab" data-bs-target="#emergency-contact-section"
                              type="button" role="tab" aria-selected="false">
                              Emergency Contact
                           </a>
                        </li>
                        <li class="nav-item" role="presentation">
                           <a href="#" class="nav-link px-4" data-bs-toggle="tab" data-bs-target="#qualification-section"
                              type="button" role="tab" aria-selected="false">
                              Qualifications
                           </a>
                        </li>
                        <li class="nav-item" role="presentation">
                           <a href="#" class="nav-link px-4" data-bs-toggle="tab" data-bs-target="#work-history-section"
                              type="button" role="tab" aria-selected="false">
                              Work History
                           </a>
                        </li>
                        <li class="nav-item" role="presentation">
                           <a href="#" class="nav-link px-4" data-bs-toggle="tab" data-bs-target="#change-password-section"
                              type="button" role="tab" aria-selected="false">
                              Change Password
                           </a>
                        </li>
                     </ul>

                     <!-- Tab Content -->
                     <div class="tab-content px-0">
                        <!-- Employee details Tab -->
                        <div class="tab-pane fade show active px-0" id="employee-details-section" role="tabpanel">
                           <h4>Employee Information</h4>

                           <div class="row mb-4">
                              <div class="col-md-6">
                                 <div class="info-group mb-3">
                                    <h6 class="text-muted mb-1">Employee Number</h6>
                                    <p class="mb-0">{{ employee.staff_number }}</p>
                                 </div>
                                 <div class="info-group mb-3">
                                    <h6 class="text-muted mb-1">Date of Hire</h6>
                                    <p class="mb-0">{{ employee.date_of_hire }}</p>
                                 </div>
                                 <div class="info-group mb-3">
                                    <h6 class="text-muted mb-1">Employment Type</h6>
                                    <p class="mb-0">{{ employee.employment_type?.name }}</p>
                                 </div>
                                 <div class="info-group mb-3">
                                    <h6 class="text-muted mb-1">Employment Status</h6>
                                    <span class="badge bg-warning">{{ employee.employment_status?.name }}</span>
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="info-group mb-3">
                                    <h6 class="text-muted mb-1">Passport/ID Number</h6>
                                    <p class="mb-0">{{ employee.identification_number }}</p>
                                 </div>
                                 <div class="info-group mb-3">
                                    <h6 class="text-muted mb-1">Tax Identification Number (KRA)</h6>
                                    <p class="mb-0">{{ employee.tax_identification_pin }}</p>
                                 </div>
                                 <div class="info-group mb-3">
                                    <h6 class="text-muted mb-1">TSC Number</h6>
                                    <p class="mb-0">{{ employee.teacher?.tsc_number}}</p>
                                 </div>
                                 <div class="info-group mb-3">
                                    <h6 class="text-muted mb-1">Job Title</h6>
                                    <p class="mb-0">{{ employee.teacher?.job.title }}</p>
                                 </div>
                              </div>
                           </div>

                           <div class="row">
                              <div class="col-md-6">
                                 <div class="info-group mb-3">
                                    <h6 class="text-muted mb-1">Specialization</h6>
                                    <p class="mb-0">{{ employee.specialization ?? 'To be pulled' }}</p>
                                 </div>
                                 <div class="info-group mb-3">
                                    <h6 class="text-muted mb-1">Years of Experience</h6>
                                    <p class="mb-0">{{ employee.teacher?.years_of_experience || 'Not specified' }}</p>
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="info-group mb-3">
                                    <h6 class="text-muted mb-1">Gender</h6>
                                    <p class="mb-0">{{ employee.gender?.name }}</p>
                                 </div>
                                 <div class="info-group mb-3">
                                    <h6 class="text-muted mb-1">Religion</h6>
                                    <p class="mb-0">{{ employee.religion?.name }}</p>
                                 </div>
                                 <div class="info-group mb-3">
                                    <h6 class="text-muted mb-1">Marital Status</h6>
                                    <p class="mb-0">{{ employee.marital_status?.name }}</p>
                                 </div>
                              </div>
                           </div>
                           <h4 class="mt-4">Contact Information</h4>
                           <div class="row">
                              <div class="col-md-6">
                                 <div class="info-group mb-3">
                                    <h6 class="text-muted mb-1">Email</h6>
                                    <p class="mb-0">{{ employee.email }}</p>
                                 </div>
                                 <div class="info-group mb-3">
                                    <h6 class="text-muted mb-1">Primary Phone</h6>
                                    <p class="mb-0">{{ employee.primary_phone }}</p>
                                 </div>
                                 <div class="info-group mb-3">
                                    <h6 class="text-muted mb-1">Secondary Phone</h6>
                                    <p class="mb-0">{{ employee.secondary_phone }}</p>
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="info-group mb-3">
                                    <h6 class="text-muted mb-1">Permanent Address</h6>
                                    <p class="mb-0">{{ employee.permanent_physical_address }}</p>
                                 </div>
                                 <div class="info-group mb-3">
                                    <h6 class="text-muted mb-1">Secondary Address</h6>
                                    <p class="mb-0">{{ employee.secondary_physical_address }}</p>
                                 </div>
                                 <div class="info-group mb-3">
                                    <h6 class="text-muted mb-1">Postal Address</h6>
                                    <p class="mb-0">{{ employee.postal_address }}</p>
                                 </div>
                              </div>
                           </div>
                        </div>

                        <!-- Emergency Contact Tab -->
                        <div class="tab-pane fade" id="emergency-contact-section" role="tabpanel">
                           <h4>Emergency Contact Details</h4>
                           <div class="alert alert-info" v-if="emergencyContacts.length == 0">
                              <i class='bx bx-info-circle me-2'></i>
                              Emergency contact information will be displayed here when available.
                           </div>
                             <table class="table table-sm table-bordered text-nowrap" style="max-width: inherit;" v-else>
                           <thead>
                           <tr>
                              <th class="p-2 fw-medium text-heading" style="width: 30%;">Name</th>
                              <th class="p-2 fw-medium text-heading" style="width: 25%;">Email</th>
                              <th class="p-2 fw-medium text-heading" style="width: 15%;">Phone</th>
                              <th class="p-2 fw-medium text-heading" style="width: 15%;">Relationship</th>
<!--                              <th class="p-2 fw-medium text-heading" style="width: 10%;"></th>-->
                           </tr>
                           </thead>
                           <tbody>
                           <tr v-for="(contact, index) in emergencyContacts" :key="index">
                              <td class="p-2">{{ contact.name ?? '-' }}</td>
                              <td class="p-2">{{ contact.email ?? '-' }}</td>
                              <td class="p-2">{{ contact.phone ?? '-' }}</td>
                              <td class="p-2">{{ contact.relationship?.name ?? '-' }}</td>
<!--                              <td class="p-2"></td>-->
                           </tr>
                           </tbody>
                        </table>
                        </div>

                        <!-- Qualifications Tab -->
                        <div class="tab-pane fade" id="qualification-section" role="tabpanel">
                           <h4>Qualification Details</h4>
                           <div class="alert alert-info">
                              <i class='bx bx-graduation me-2'></i>
                              Educational qualifications and certifications will be displayed here when available.
                           </div>
                        </div>

                        <!-- Work History Tab -->
                        <div class="tab-pane fade" id="work-history-section" role="tabpanel">
                           <h4>Work History Details</h4>
                           <div class="alert alert-info">
                              <i class='bx bx-briefcase me-2'></i>
                              Previous work experience and employment history will be displayed here when available.
                           </div>
                        </div>

                        <!-- Change Password Tab -->
                        <div class="tab-pane fade" id="change-password-section" role="tabpanel">
                           <h4>Change Password</h4>
                           <div class="row justify-content-center">
                              <div class="col-md-8">
                                   <div v-if="passwordChangeMessage" class="alert my-3" :class="passwordChangeSuccess ? 'alert-success' : 'alert-danger'">
                                    <i :class="passwordChangeSuccess ? 'bx bx-check-circle me-2' : 'bx bx-error me-2'"></i>
                                    {{ passwordChangeMessage }}
                                 </div>
                                 <form @submit.prevent="changePassword" class="password-change-form">
                                    <div class="mb-3">
                                       <label for="currentPassword" class="form-label">Current Password</label>
                                       <div class="input-group">
                                          <input
                                             :type="showCurrentPassword ? 'text' : 'password'"
                                             class="form-control"
                                             id="currentPassword"
                                             v-model="passwordForm.currentPassword"
                                             placeholder="Enter your current password"
                                             required
                                          >
                                          <button
                                             class="btn btn-outline-secondary"
                                             type="button"
                                             @click="showCurrentPassword = !showCurrentPassword"
                                          >
                                             <i :class="showCurrentPassword ? 'bx bx-hide' : 'bx bx-show'"></i>
                                          </button>
                                       </div>
                                       <div v-if="passwordForm.errors.currentPassword" class="text-danger small mt-1">
                                          {{ passwordForm.errors.currentPassword }}</div>
                                    </div>

                                    <div class="mb-3">
                                       <label for="newPassword" class="form-label">New Password</label>
                                       <div class="input-group">
                                          <input
                                             :type="showNewPassword ? 'text' : 'password'"
                                             class="form-control"
                                             id="newPassword"
                                             v-model="passwordForm.password"
                                             placeholder="Enter your new password"
                                             required
                                          >
                                          <button
                                             class="btn btn-outline-secondary"
                                             type="button"
                                             @click="showNewPassword = !showNewPassword"
                                          >
                                             <i :class="showNewPassword ? 'bx bx-hide' : 'bx bx-show'"></i>
                                          </button>
                                       </div>
                                       <div class="form-text">
                                          Password must be at least 8 characters long and contain uppercase, lowercase, number, and special character.
                                       </div>
                                    </div>

                                    <div class="mb-3">
                                       <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                       <div class="input-group">
                                          <input
                                             :type="showpassword_confirmation ? 'text' : 'password'"
                                             class="form-control"
                                             id="password_confirmation"
                                             v-model="passwordForm.password_confirmation"
                                             placeholder="Confirm your new password"
                                             required
                                          >
                                          <button
                                             class="btn btn-outline-secondary"
                                             type="button"
                                             @click="showpassword_confirmation = !showpassword_confirmation"
                                          >
                                             <i :class="showpassword_confirmation ? 'bx bx-hide' : 'bx bx-show'"></i>
                                          </button>
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
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </DefaultLayout>
</template>

<script setup>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import axios from "axios";
import functions from "@/Functions/Functions";
import { filter } from "lodash";
import { set } from "nprogress";


const props = defineProps({
   employee:{
      type:Object,
      required:true
   }
})

const emergencyContacts = ref([])

const passwordForm = useForm({
   currentPassword: '',
   password: '',
   password_confirmation: ''
});


const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showpassword_confirmation = ref(false);


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


// Methods
const changePassword = async () => {
   isChangingPassword.value = true;
   passwordChangeMessage.value = '';

   passwordForm.post(route('employee.password.update'), {
      onSuccess: () => {
         passwordChangeSuccess.value = true;
         passwordChangeMessage.value = 'Password changed successfully!';

         setTimeout(() => {
            resetPasswordForm();
         }, 2000);

      },
      onError: (errors) => {
         passwordChangeSuccess.value = false;
         passwordChangeMessage.value = errors.currentPassword ? 'Current password is incorrect.' : 'An error occurred. Please try again later.';
      },
      onFinish: () => {
         isChangingPassword.value = false;
      }
   });
};

const resetPasswordForm = () => {
   passwordForm.reset()
   showCurrentPassword.value = false;
   showNewPassword.value = false;
   showpassword_confirmation.value = false;
   passwordChangeMessage.value = '';
};

const fetchEmergenceInfo = () => {
   const url = functions.buildUrl(route('employee.emergence.info'),{ filter:{employee_id: props.employee.id}})
   axios.get(url).then((response)=>{
      emergencyContacts.value = response.data.data
   }).catch((error)=>{
      console.error(error)
   })
}
const fetchQualificationDetails = () => {
   return
   const url = functions.buildUrl(route('employee.emergence.info'),{ filter:{employee_id: props.employee.id}})
   axios.get(url).then((response)=>{
      emergencyContacts.value = response.data.data
   }).catch((error)=>{
      console.error(error)
   })
}
const fetchWorkHistoryDetails = () => {
   return
   const url = functions.buildUrl(route('employee.emergence.info'),{ filter:{employee_id: props.employee.id}})
   axios.get(url).then((response)=>{
      emergencyContacts.value = response.data.data
   }).catch((error)=>{
      console.error(error)
   })
}
onMounted(()=>{
   fetchEmergenceInfo()
   fetchQualificationDetails()
   fetchWorkHistoryDetails()
})
</script>

<style scoped>
.bg-yellow {
   /* background-color: #61305A; */
   /* color: #fff !important; */
}

.profile-cover {
   background-image: url('/website/images/mission.jpeg');
   height: 170px;
   background-size: cover;
   background-repeat: no-repeat;
   border-radius: 20px 20px 0px 0px;
}

.profile .card {
   border-radius: 20px 20px 0px 0px;
}

.profile-cover .company-logo {
   position: absolute;
   width: 116px;
   height: 116px;
   left: 15px;
   bottom: -2rem;
   overflow: hidden;
   border-radius: 50%;
   border: 4px solid white;
   background: white;
}

.company-name span {
   font-size: 18px;
   color: #44444F;
   font-weight: 500;
}

.company-name p {
   color: #92929D;
}

.nav-item a {
   border: 1px solid #F1F1F5;
}

.nav-item a.active {
   border: none;
   /* background-color: #61305A; */
   color: white;
}

.info-group h6 {
   font-weight: 600;
   font-size: 0.9rem;
}

.info-group p {
   font-size: 1rem;
   color: #44444F;
}

.badge {
   font-size: 0.8rem;
}

.alert {
   border-radius: 10px;
}

.password-change-form {
   background: #f8f9fa;
   padding: 2rem;
   border-radius: 15px;
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
