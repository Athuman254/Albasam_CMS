<template>
   <Head title="Teacher Details Page"/>
   
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Registered Teachers</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item">
                  <Link :href="route('admin.teachers.index')">Registered Teachers</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Details
               </li>
            </ol>
         </nav>
         
         <div class="col-xl-4 col-md-6 order-1 order-md-1">
            <div class="card mb-6">
               <div class="card-body">
                  <h3>{{ employee.honorific?.name + ' ' + employee.first_name + ' ' + employee.last_name }}</h3>
                  <small class="card-text text-uppercase text-light small">ABOUT</small>
                  <ul class="list-unstyled my-3 py-1">
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bxs-user-badge"></i>
                        <span class="fw-bold mx-2">Employee Number :</span>
                        <span>{{ employee.staff_number ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-male-female"></i>
                        <span class="fw-bold mx-2">Gender :</span>
                        <span>{{ employee.gender?.name ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-church"></i>
                        <span class="fw-bold mx-2">Religion :</span>
                        <span>{{ employee.religion?.name ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center">
                        <i class="icon-base bx bx-category-alt"></i>
                        <span class="fw-bold mx-2">Marital Status :</span>
                        <span>{{ employee.marital_status?.name ?? '-'}}</span>
                     </li>
                  </ul>
                  
                  <small class="card-text text-uppercase text-light small">CONTACT</small>
                  <ul class="list-unstyled my-3 py-1">
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-envelope"></i>
                        <span class="fw-bold mx-2">Email :</span>
                        <span>{{ employee.email ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-phone"></i>
                        <span class="fw-bold mx-2">Primary Phone :</span>
                        <span>{{ employee.primary_phone ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-phone"></i>
                        <span class="fw-bold mx-2">Secondary Phone :</span>
                        <span>{{ employee.secondary_phone ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-current-location"></i>
                        <span class="fw-bold mx-2">Permanent Address :</span>
                        <span>{{ employee.permanent_physical_address ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-current-location"></i>
                        <span class="fw-bold mx-2">Secondary Address :</span>
                        <span>{{ employee.secondary_physical_address ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center">
                        <i class="icon-base bx bx-box"></i>
                        <span class="fw-bold mx-2">Postal Address :</span>
                        <span>{{ employee.postal_address ?? '-' }}</span>
                     </li>
                  </ul>
               </div>
            </div>
            <div class="card mb-6">
               <div class="card-body">
                  <h3>System Access</h3>
                  <div v-if="!employee.has_system_access" class="d-flex align-items-start row">
                     <div class="col-12">
                        <p>The employee has no system access! <br>
                           Click the button below to give access.
                        </p>
                        <button type="button" class="btn btn-sm btn-outline-primary" @click.prevent="showCreateCredentialsModal">
                           Give Access
                        </button>
                     </div>
                  </div>
                  <div v-else>
                     <div class="d-flex align-items-start row">
                        <div class="col-12">
                           <p>The employee has access to the system! <br>
                              Click the button below to revoke access
                           </p>
                           <button type="button" class="btn btn-outline-danger">Suspend</button>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         
         <div class="col-xl-8 col-md-6 order-3 order-md-2">
            <div class="accordion">
               <!-- Employee Data Accordion -->
               <div class="card accordion-item">
                  <h2 class="accordion-header border-bottom">
                     <button
                        type="button"
                        class="accordion-button"
                        :class="{ collapsed: openAccordion !== 'employee' }"
                        @click="toggleAccordion('employee')">
                        Employee Details
                     </button>
                  </h2>
                  <div class="accordion-collapse" :class="{ show: openAccordion === 'employee' }">
                     <div class="accordion-body px-0 py-4 d-flex align-items-baseline flex-wrap flex-xl-nowrap flex-sm-nowrap flex-md-wrap">
                        <table class="table table-sm table-borderless text-nowrap small">
                           <tbody>
                           <tr>
                              <td class="w-50">Employee Number</td>
                              <td class="fw-medium text-heading">{{ employee.staff_number ?? '-' }}</td>
                           </tr>
                           <tr>
                              <td>Passport/ID Number</td>
                              <td class="fw-medium text-heading">{{ employee.identification_number ?? '-'}}</td>
                           </tr>
                           <tr>
                              <td>Tax Identification Number (KRA)</td>
                              <td class="fw-medium text-heading">{{ employee.tax_identification_pin ?? '-' }}</td>
                           </tr>
                           <tr>
                              <td>TSC Number</td>
                              <td class="fw-medium text-heading">{{ teacher.tsc_number ?? '-'}}</td>
                           </tr>
                           <tr>
                              <td>Specialization</td>
                              <td class="fw-medium text-heading">{{ teacher.specialization?.name ?? '-'}}</td>
                           </tr>
                           </tbody>
                        </table>
                        <table class="table table-sm table-borderless text-nowrap small">
                           <tbody>
                           <tr>
                              <td class="w-50">Date of hire</td>
                              <td class="fw-medium text-heading">{{ employee.date_of_hire ?? '-' }}</td>
                           </tr>
                           <tr>
                              <td>Employment Type</td>
                              <td class="fw-medium text-heading">{{ employee.employment_type?.name ?? '-'}}</td>
                           </tr>
                           <tr>
                              <td>Employment Status</td>
                              <td class="fw-medium text-heading">{{ employee.employment_status?.name ?? '-' }}</td>
                           </tr>
                           <tr>
                              <td>Job Title</td>
                              <td class="fw-medium text-heading">{{ teacher.job?.title ?? '-'}}</td>
                           </tr>
                           <tr>
                              <td>Year of experience</td>
                              <td class="fw-medium text-heading">{{ teacher.years_of_experience + ' yrs' ?? '-'}}</td>
                           </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
               
               <!-- Emergency Contact Details Accordion -->
               <div class="card accordion-item">
                  <h2 class="accordion-header border-bottom">
                     <button
                        type="button"
                        class="accordion-button"
                        :class="{ collapsed: openAccordion !== 'contact' }"
                        @click="toggleAccordion('contact')">
                        Emergency Contact Details
                     </button>
                  </h2>
                  <div class="accordion-collapse" :class="{ show: openAccordion === 'contact' }">
                     <div class="accordion-body py-4 d-flex align-items-baseline flex-wrap flex-xl-nowrap flex-sm-nowrap flex-md-wrap">
                        <table class="table table-sm table-bordered text-nowrap" style="max-width: inherit;">
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
                  </div>
               </div>
               
               <!-- Qualification Details Accordion -->
               <div class="card accordion-item">
                  <h2 class="accordion-header border-bottom">
                     <button
                        type="button"
                        class="accordion-button"
                        :class="{ collapsed: openAccordion !== 'qualification' }"
                        @click="toggleAccordion('qualification')">
                        Qualification Details
                     </button>
                  </h2>
                  <div class="accordion-collapse" :class="{ show: openAccordion === 'qualification' }">
                     <div class="accordion-body py-4 d-flex align-items-baseline flex-wrap flex-xl-nowrap flex-sm-nowrap flex-md-wrap">
                        <table class="table table-sm table-bordered text-nowrap" style="max-width: inherit;">
                           <thead>
                           <tr>
                              <th class="p-2 fw-medium text-heading" style="width: 30%;">Institution</th>
                              <th class="p-2 fw-medium text-heading" style="width: 25%;">Course</th>
                              <th class="p-2 fw-medium text-heading" style="width: 15%;">Qualification</th>
                              <th class="p-2 fw-medium text-heading" style="width: 15%;">Completion Year</th>
<!--                              <th class="p-2 fw-medium text-heading" style="width: 10%;"></th>-->
                           </tr>
                           </thead>
                           <tbody>
                           <tr v-for="(qualification, index) in qualificationDetails" :key="index">
                              <td class="p-2">{{ qualification.institution_name }}</td>
                              <td class="p-2">{{ qualification.course_name }}</td>
                              <td class="p-2">{{ qualification.qualification_type?.name }}</td>
                              <td class="p-2">{{ qualification.year_of_completion }}</td>
<!--                              <td class="p-2"></td>-->
                           </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
               
               <!-- Work History Details Accordion -->
               <div class="card accordion-item">
                  <h2 class="accordion-header border-bottom">
                     <button
                        type="button"
                        class="accordion-button"
                        :class="{ collapsed: openAccordion !== 'history' }"
                        @click="toggleAccordion('history')">
                        Work History Details
                     </button>
                  </h2>
                  <div class="accordion-collapse" :class="{ show: openAccordion === 'history' }">
                     <div class="accordion-body py-4 d-flex align-items-baseline flex-wrap flex-xl-nowrap flex-sm-nowrap flex-md-wrap">
                        <table class="table table-sm table-bordered text-nowrap" style="max-width: inherit;">
                           <thead>
                           <tr>
                              <th class="p-2 fw-medium text-heading" style="width: 35%;">Name</th>
                              <th class="p-2 fw-medium text-heading" style="width: 25%;">Start Date</th>
                              <th class="p-2 fw-medium text-heading" style="width: 25%;">End Date</th>
<!--                              <th class="p-2 fw-medium text-heading" style="width: 10%;"></th>-->
                           </tr>
                           </thead>
                           <tbody>
                           <tr v-for="(history, index) in workHistories" :key="index">
                              <td class="p-2">{{ history.institution_name ?? '-' }}</td>
                              <td class="p-2">{{ history.start_date ?? '-' }}</td>
                              <td class="p-2">{{ history.end_date ?? '-' }}</td>
<!--                              <td class="p-2"></td>-->
                           </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </DefaultLayout>
   
   <!-- Start Create Modal -->
   <div
      class="modal fade"
      id="create-credentials-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="create-credentials-modal-label"
      aria-hidden="true"
      ref="createCredentialsModal"
   >
      <div class="modal-dialog modal-body-simple">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="create-credentials-modal-label">System Access</h5>
               <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                  @click="credentialsFormCleanUp"
               ></button>
            </div>
            <div class="modal-body">
               <form id="createForm" @submit.prevent="storeCredentials">
                  <div class="mb-3">
                     <label for="password" class="form-label">Password</label>
                     <input id="password" type="password" v-model="form.password" class="form-control">
                     <div v-if="form.errors.password" class="text-danger">{{ form.errors.password }}</div>
                  </div>
                  
                  <div class="mb-3">
                     <label class="row d-flex">
                        <span class="col">
                           <span class="fw-bold me-1">Activate Account</span>
                        </span>
                        <span class="col-auto">
                           <label class="form-check form-switch">
                              <input v-model="form.has_system_access" class="form-check-input" type="checkbox">
                           </label>
                        </span>
                        <span class="form-check-description">When enabled, the employee can login to the system.</span>
                     </label>
                  </div>
               </form>
            </div>
            <div class="modal-footer">
               <button
                  type="button"
                  class="btn btn-secondary me-2"
                  data-bs-dismiss="modal"
                  @click="credentialsFormCleanUp"
               >
                  Close
               </button>
               <button
                  type="button"
                  class="btn btn-primary"
                  @click.prevent="storeCredentials"
                  :disabled="form.processing"
               >
                  Submit
               </button>
            </div>
         </div>
      </div>
   </div>
   <!-- End Create Modal -->
</template>

<script>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import {Head, Link, useForm} from "@inertiajs/vue3";
import {Inertia} from "@inertiajs/inertia";
import axios from "axios";
import {Modal} from "bootstrap";

export default {
   components: {DefaultLayout, Head, Link},
   props: ['teacher', 'employee'],
   
   data() {
      return {
         form: useForm({
            has_system_access: true,
            password: '',
         }),
         emergencyContacts: [],
         qualificationDetails: [],
         workHistories: [],
         
         dataFetched: false,
         openAccordion: 'employee'
      };
   },
   beforeDestroy() {
      // Clean up the listener when the component is destroyed
      Inertia.off('navigate', this.handleNavigation);
   },
   mounted() {
      this.fetchAllData();
   },
   methods: {
      handleNavigation(event) {
         const targetUrl = '/admin/employees/teachers/' + this.teacher.hashid;
         if (event.detail.page.url === targetUrl && !this.dataFetched) {
            this.fetchAllData();
         }
      },
      fetchAllData() {
         this.fetchedEmergencyContactDetails();
         this.fetchedQualificationDetails();
         this.fetchedWorkHistoryDetails();
         this.dataFetched = true;
      },
      fetchedEmergencyContactDetails() {
         if (!this.employee) {
            return;
         }
         axios.get('/datatable/emergency-contacts', {
            params: {
               filter: {
                  employee_id: this.employee.id,
               },
            },
         }).then(({data}) => {
            this.emergencyContacts = data.data;
         }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the employee emergency contact details.')
         });
      },
      fetchedQualificationDetails() {
         if (!this.employee) {
            return;
         }
         axios.get('/datatable/employee-qualifications', {
            params: {
               filter: {
                  employee_id: this.employee.id,
               },
            },
         }).then(({data}) => {
            this.qualificationDetails = data.data;
         }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the employee qualifications.')
         });
      },
      fetchedWorkHistoryDetails() {
         if (!this.employee) {
            return;
         }
         axios.get('/datatable/work-histories', {
            params: {
               filter: {
                  employee_id: this.employee.id,
               },
            },
         }).then(({data}) => {
            this.workHistories = data.data;
         }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the employee work history details.')
         });
      },
      toggleAccordion(section) {
         this.openAccordion = this.openAccordion === section ? null : section;
      },
      showCreateCredentialsModal() {
         const modalElement = this.$refs.createCredentialsModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      storeCredentials() {
         this.form.post(route('admin.employees.system-access', this.employee.hashid), {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               const modalElement = this.$refs.createCredentialsModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Employee now has system access', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      credentialsFormCleanUp() {
         this.form.reset()
      },
   }
}
</script>

<style scoped>
.accordion-collapse {
   display: none;
}
.accordion-collapse.show {
   display: block;
}
</style>
