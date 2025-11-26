<template>
   <Head title="Employee Profile" />

   <DefaultLayout>
      <div class="py-3 py-lg-4">
         <!-- start page title -->
         <div class="row pt-3">
            <div class="col-xl-10 profile">

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
                              <h3>{{ `${employee.honorific?.name || ''} ${employee.first_name || ''} ${employee.middle_name || ''} ${employee.last_name || ''}` }}</h3>
                              <!-- <Link class="small"><i class="bi bi-pencil-square"></i> Edit</Link> -->
                           </span>

                           <span class="fw-semibold">
                              {{ employee.teacher?.job.title }}
                              <span class="badge bg-success ms-2" v-if="employee.employment_status">
                                 <i class='bx bx-wifi'></i> {{ employee.employment_status.name }}
                              </span>
                           </span>
                           <p class="mt-3">Employee ID: {{ employee.staff_number ?? '-'  }}</p>
                        </div>
                     </div>

                     <!-- Navigation Tabs -->
                     <div class="nav-align-top mb-6">
                        <ul class="nav nav-pills mb-4 gap-2" role="tablist">
                           <li class="nav-item" role="presentation">
                              <button type="button" class="nav-link" :class="{ active: activeTab === 'details' }" @click="activeTab = 'details'">
                                 Employee Details
                              </button>
                           </li>
                           <li class="nav-item" role="presentation">
                              <button type="button" class="nav-link" :class="{ active: activeTab === 'contacts' }" @click.prevent="activeTab = 'contacts'">
                                 Emergency Contact
                              </button>
                           </li>
                           <li class="nav-item" role="presentation">
                              <button type="button" class="nav-link" :class="{ active: activeTab === 'qualifications' }" @click.prevent="activeTab = 'qualifications'">
                                 Qualifications
                              </button>
                           </li>
                           <li class="nav-item" role="presentation">
                              <button type="button" class="nav-link" :class="{ active: activeTab === 'work-histories' }" @click.prevent="activeTab = 'work-histories'">
                                 Work History
                              </button>
                           </li>
                           <li class="nav-item" role="presentation">
                              <button type="button" class="nav-link" :class="{ active: activeTab === 'password' }" @click.prevent="activeTab = 'password'">
                                 Change Password
                              </button>
                           </li>
                        </ul>
                        
                        <!-- Employee details Tab -->
                        <div v-if="activeTab === 'details'">
                           <employee-details :employee="employee" />
                        </div>
                        
                        <!-- Emergency Contact Tab -->
                        <div v-if="activeTab === 'contacts'">
                           <emergency-contacts :employee="employee" />
                        </div>
                        
                        <!-- Qualifications Tab -->
                        <div v-if="activeTab === 'qualifications'">
                           <qualifications :employee="employee" />
                        </div>
                        
                        <!-- Work History Tab -->
                        <div v-if="activeTab === 'work-histories'">
                           <work-histories :employee="employee" />
                        </div>
                        
                        <!-- Change Password Tab -->
                        <div v-if="activeTab === 'password'">
                           <update-password :employee="employee" />
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
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import UpdatePassword from "./Partials/UpdatePassword.vue"
import WorkHistories from "./Partials/WorkHistories.vue";
import Qualifications from "./Partials/Qualifications.vue";
import EmergencyContacts from "./Partials/EmergencyContacts.vue";
import EmployeeDetails from "./Partials/EmployeeDetails.vue";

const props = defineProps({
   employee:{
      type:Object,
      required:true
   }
})

const activeTab = ref('details')

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
   border-radius: 0.375rem 0.375rem 0 0;
}
.profile .card {
   border-radius: 0.375rem;
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
   text-decoration: none;
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
.nav-pills .nav-item .nav-link:not(.active) {
   border: 1px solid #e9ecef;
}
</style>
