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
                  <Link href="/admin/employees/teachers">Registered Teachers</Link>
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
                        <i class="icon-base bx bx-male-female"></i>
                        <span class="fw-medium mx-2">Gender :</span>
                        <span>{{ employee.gender?.name ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-church"></i>
                        <span class="fw-medium mx-2">Religion :</span>
                        <span>{{ employee.religion?.name ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center">
                        <i class="icon-base bx bx-category-alt"></i>
                        <span class="fw-medium mx-2">Marital Status :</span>
                        <span>{{ employee.marital_status?.name ?? '-'}}</span>
                     </li>
                  </ul>
                  
                  <small class="card-text text-uppercase text-light small">CONTACT</small>
                  <ul class="list-unstyled my-3 py-1">
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-envelope"></i>
                        <span class="fw-medium mx-2">Email :</span>
                        <span>{{ employee.email ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-phone"></i>
                        <span class="fw-medium mx-2">Primary Phone :</span>
                        <span>{{ employee.primary_phone ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-phone"></i>
                        <span class="fw-medium mx-2">Secondary Phone :</span>
                        <span>{{ employee.secondary_phone ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-current-location"></i>
                        <span class="fw-medium mx-2">Permanent Address :</span>
                        <span>{{ employee.permanent_physical_address ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-current-location"></i>
                        <span class="fw-medium mx-2">Secondary Address :</span>
                        <span>{{ employee.secondary_physical_address ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center">
                        <i class="icon-base bx bx-box"></i>
                        <span class="fw-medium mx-2">Postal Address :</span>
                        <span>{{ employee.postal_address ?? '-' }}</span>
                     </li>
                  </ul>
               </div>
            </div>
            <div class="card mb-6">
               <div class="card-body">
                  <h3>System Access</h3>
                  <div v-if="!employee.user" class="d-flex align-items-start row">
                     <div class="col-12">
                        <p>No user account found for the teacher!<br>Click the button below to give access</p>
                        <button type="button" class="btn btn-sm btn-outline-primary" @click.prevent="showCreateCredentialsModal">
                           Give Access
                        </button>
                     </div>
                  </div>
                  <div v-else>
                     <small class="card-text text-uppercase text-light small">DETAILS</small>
                     <ul class="list-unstyled my-3 py-1">
                        <li class="d-flex align-items-center mb-4">
                           <i class="icon-base bx bx-user"></i>
                           <span class="fw-medium mx-2">Name :</span>
                           <span>{{ employee.user?.name }}</span>
                        </li>
                        <li class="d-flex align-items-center mb-4">
                           <i class="icon-base bx bx-user-circle"></i>
                           <span class="fw-medium mx-2">Username :</span>
                           <span>{{ employee.user?.username }}</span>
                        </li>
                        <li class="d-flex align-items-center mb-4">
                           <i class="icon-base bx bx-envelope"></i>
                           <span class="fw-medium mx-2">Email :</span>
                           <span>{{ employee.user?.email }}</span>
                        </li>
                        <li class="d-flex align-items-center mb-4">
                           <i class="icon-base bx bx-phone"></i>
                           <span class="fw-medium mx-2">Phone :</span>
                           <span>{{ employee.user?.phone }}</span>
                        </li>
                        <li class="d-flex align-items-center mb-4">
                           <i class="icon-base bx bx-check-square"></i>
                           <span class="fw-medium mx-2">Status :</span>
                           <span v-if="employee.user?.activated" class="badge bg-success">Active</span>
                           <span v-else class="badge bg-danger">Deactivated</span>
                        </li>
                     </ul>
                     <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-primary me-4" @click.prevent="showEditCredentialsModal(this.employee.user)">Edit</button>
                        <button type="button" class="btn btn-outline-danger">Suspend</button>
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
                              <td class="fw-medium text-heading">{{ employee.job_title?.name ?? '-'}}</td>
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
                              <th class="p-2 fw-medium text-heading" style="width: 10%;"></th>
                           </tr>
                           </thead>
                           <tbody>
                           <tr v-for="(contact, index) in emergencyContacts" :key="index">
                              <td class="p-2">{{ contact.name ?? '-' }}</td>
                              <td class="p-2">{{ contact.email ?? '-' }}</td>
                              <td class="p-2">{{ contact.phone ?? '-' }}</td>
                              <td class="p-2">{{ contact.relationship?.name ?? '-' }}</td>
                              <td class="p-2"></td>
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
                              <th class="p-2 fw-medium text-heading" style="width: 10%;"></th>
                           </tr>
                           </thead>
                           <tbody>
                           <tr v-for="(qualification, index) in qualificationDetails" :key="index">
                              <td class="p-2">{{ qualification.institution_name }}</td>
                              <td class="p-2">{{ qualification.course_name }}</td>
                              <td class="p-2">{{ qualification.qualification_type?.name }}</td>
                              <td class="p-2">{{ qualification.year_of_completion }}</td>
                              <td class="p-2"></td>
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
                              <th class="p-2 fw-medium text-heading" style="width: 10%;"></th>
                           </tr>
                           </thead>
                           <tbody>
                           <tr v-for="(history, index) in workHistories" :key="index">
                              <td class="p-2">{{ history.institution_name ?? '-' }}</td>
                              <td class="p-2">{{ history.start_date ?? '-' }}</td>
                              <td class="p-2">{{ history.end_date ?? '-' }}</td>
                              <td class="p-2"></td>
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
                     <label class="row d-flex">
                        <span class="col">
                           <span class="fw-bold me-3">Use Existing User Account</span>
                        </span>
                        <span class="col-auto">
                           <label class="form-check form-switch">
                              <input v-model="form.use_existing_user" class="form-check-input" type="checkbox">
                           </label>
                        </span>
                        <span class="form-check-description">When enabled, the teacher will use an existing user account to login to the system</span>
                     </label>
                  </div>
                  <div v-if="form.use_existing_user">
                     <div class="mb-3">
                        <label for="userId" class="form-label">User</label>
                        <v-select
                           id="userId"
                           v-model="form.user_id"
                           :options="users"
                           label="name"
                           :reduce="option => option.id"
                        ></v-select>
                        <div v-if="form.errors.user_id" class="text-danger">{{ form.errors.user_id }}</div>
                     </div>
                  </div>
                  <div v-else>
                     <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input id="name" type="text" v-model="form.name" class="form-control">
                        <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input id="username" type="text" v-model="form.username" class="form-control">
                        <div v-if="form.errors.username" class="text-danger">{{ form.errors.username }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" v-model="form.email" class="form-control">
                        <div v-if="form.errors.email" class="text-danger">{{ form.errors.email }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input id="phone" type="text" v-model="form.phone" class="form-control">
                        <div v-if="form.errors.phone" class="text-danger">{{ form.errors.phone }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password" v-model="form.password" class="form-control">
                        <div v-if="form.errors.password" class="text-danger">{{ form.errors.password }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label class="row d-flex">
                        <span class="col">
                           <span class="fw-bold me-3">Activate Account</span>
                        </span>
                           <span class="col-auto">
                           <label class="form-check form-switch">
                              <input v-model="form.activated" class="form-check-input" type="checkbox">
                           </label>
                        </span>
                           <span class="form-check-description">When enabled, the user can login into the system.</span>
                        </label>
                     </div>
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
               >
                  Submit
               </button>
            </div>
         </div>
      </div>
   </div>
   <!-- End Create Modal -->
   
   <!-- Start Edit Modal -->
   <div
      class="modal fade"
      id="edit-credentials-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="edit-credentials-modal-label"
      aria-hidden="true"
      ref="editCredentialsModal"
   >
      <div class="modal-dialog modal-body-simple">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="create-credentials-modal-label">Edit System Access</h5>
               <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                  @click="editCredentialsFormCleanUp"
               ></button>
            </div>
            <div class="modal-body">
               <form id="createForm" @submit.prevent="updateCredentials">
                  <div class="mb-3">
                     <label class="row d-flex">
                        <span class="col">
                           <span class="fw-bold me-3">Use Existing User Account</span>
                        </span>
                        <span class="col-auto">
                           <label class="form-check form-switch">
                              <input v-model="editForm.use_existing_user" class="form-check-input" type="checkbox">
                           </label>
                        </span>
                        <span class="form-check-description">When enabled, the teacher will use an existing user account to login to the system</span>
                     </label>
                  </div>
                  <div v-if="editForm.use_existing_user">
                     <div class="mb-3">
                        <label for="userId" class="form-label">User</label>
                        <v-select
                           id="userId"
                           v-model="editForm.user_id"
                           :options="users"
                           label="name"
                           :reduce="option => option.id"
                        ></v-select>
                        <div v-if="editForm.errors.user_id" class="text-danger">{{ editForm.errors.user_id }}</div>
                     </div>
                  </div>
                  <div v-else>
                     <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input id="name" type="text" v-model="editForm.name" class="form-control">
                        <div v-if="editForm.errors.name" class="text-danger">{{ editForm.errors.name }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input id="username" type="text" v-model="editForm.username" class="form-control">
                        <div v-if="editForm.errors.username" class="text-danger">{{ editForm.errors.username }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" v-model="editForm.email" class="form-control">
                        <div v-if="editForm.errors.email" class="text-danger">{{ editForm.errors.email }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input id="phone" type="text" v-model="editForm.phone" class="form-control">
                        <div v-if="editForm.errors.phone" class="text-danger">{{ editForm.errors.phone }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password" v-model="editForm.password" class="form-control">
                        <div v-if="editForm.errors.password" class="text-danger">{{ editForm.errors.password }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label class="row d-flex">
                        <span class="col">
                           <span class="fw-bold me-3">Activate Account</span>
                        </span>
                           <span class="col-auto">
                           <label class="form-check form-switch">
                              <input v-model="editForm.activated" class="form-check-input" type="checkbox">
                           </label>
                        </span>
                           <span class="form-check-description">When enabled, the user can login into the system.</span>
                        </label>
                     </div>
                  </div>
               </form>
            </div>
            <div class="modal-footer">
               <button
                  type="button"
                  class="btn btn-secondary me-2"
                  data-bs-dismiss="modal"
                  @click="editCredentialsFormCleanUp"
               >
                  Close
               </button>
               <button
                  type="button"
                  class="btn btn-primary"
                  @click.prevent="updateCredentials"
               >
                  Submit
               </button>
            </div>
         </div>
      </div>
   </div>
   <!-- End Edit Modal -->
</template>

<script>
import axios from "axios";
import {Inertia} from "@inertiajs/inertia";
import {useForm} from "@inertiajs/vue3";
import {Modal} from "bootstrap";
import DefaultLayout from "@layouts/DefaultLayout.vue";

export default {
   components: {DefaultLayout},
   props: ['teacher', 'employee'],
   
   data() {
      return {
         form: useForm({
            use_existing_user: true,
            teacher_id: this.teacher.id,
            employee_id: this.employee.id,
            user_id: null,
            name: '',
            username: '',
            email: '',
            phone: '',
            password: '',
            activated: '',
            is_teacher: true,
         }),
         editForm: useForm({
            use_existing_user: this.employee.use_existing_user,
            teacher_id: this.teacher.id,
            employee_id: this.employee.id,
            id: this.employee.user?.hashid ?? null,
            user_id: this.employee.user_id ?? null,
            name: '',
            username: '',
            email: '',
            phone: '',
            password: '',
            activated: '',
            is_teacher: true,
         }),
         users: [],
         emergencyContacts: [],
         qualificationDetails: [],
         workHistories: [],
         
         dataFetched: false,
         openAccordion: 'employee'
      };
   },
   watch: {
      'form.use_existing_user':  function(value) {
         if(value) {
            this.form.name = '';
            this.form.username = '';
            this.form.email = '';
            this.form.phone = '';
            this.form.password = '';
         } else {
            this.form.user_id = null;
            const firstName = this.employee.first_name;
            const lastName = this.employee.last_name;
            this.form.name = `${firstName} ${lastName}`.trim();
            this.form.username = (`${firstName.substr(0,1)}.${lastName ?? ''}`).toLowerCase() ?? '';
            this.form.email = this.employee.email ?? '';
            this.form.phone = this.employee.primary_phone ?? '';
         }
      },
      'editForm.use_existing_user':  function(value) {
         if(value) {
            this.editForm.name = '';
            this.editForm.username = '';
            this.editForm.email = '';
            this.editForm.phone = '';
            this.editForm.password = '';
         } else {
            this.editForm.user_id = null;
            const firstName = this.employee.first_name;
            const lastName = this.employee.last_name;
            this.editForm.name = `${firstName} ${lastName}`.trim();
            this.editForm.username = (`${firstName.substr(0,1)}.${lastName ?? ''}`).toLowerCase() ?? '';
            this.editForm.email = this.employee.email ?? '';
            this.editForm.phone = this.employee.primary_phone ?? '';
         }
      }
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
         this.fetchUsers();
         this.fetchedEmergencyContactDetails();
         this.fetchedQualificationDetails();
         this.fetchedWorkHistoryDetails();
         this.dataFetched = true;
      },
      fetchUsers() {
         axios.get('/datatable/users')
            .then(({data}) => {
               this.users = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred when fetching the users.')
         })
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
         this.form.post('/admin/employees/teachers/credentials', {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               const modalElement = this.$refs.createCredentialsModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('User Created Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      showEditCredentialsModal(user) {
         this.editForm.id = user.hashid; // Assign the ID manually
         this.use_existing_user = this.employee.use_existing_user;
         this.editForm.name = user.name;
         this.editForm.username = user.username;
         this.editForm.email = user.email;
         this.editForm.phone = user.phone;
         this.editForm.activated = user.activated;
         
         const modalElement = this.$refs.editCredentialsModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      updateCredentials() {
         this.editForm.patch('/admin/employees/teachers/credentials/' + this.editForm.id, {
            onSuccess: () => {
               this.editForm.reset(); // Reset the form on success
               this.editForm.clearErrors();
               
               const modalElement = this.$refs.editCredentialsModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('User Updated Successfully', 'Success')
            },
            onError: (errors) => {
               console.log(errors);
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         })
      },
      credentialsFormCleanUp() {
         this.form.reset()
      },
      editCredentialsFormCleanUp() {
         this.editForm.reset()
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
