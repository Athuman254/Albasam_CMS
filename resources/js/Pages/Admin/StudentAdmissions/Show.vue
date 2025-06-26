<template>
   <Head title="Student Details"/>
   
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">{{ student.first_name + ' ' + student.last_name }}</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item">
                  <Link :href="route('admin.admissions.index')">Student Admissions</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Student Details
               </li>
            </ol>
         </nav>
         
         <div class="col-xl-4 col-md-6 order-1 order-md-1">
            <div class="card mb-6">
               <div class="card-body">
                  <h3>{{ student.first_name + ' ' + student.last_name }}</h3>
                  <small class="card-text text-uppercase text-light small">ABOUT</small>
                  <ul class="list-unstyled my-3 py-1">
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bxs-user-badge"></i>
                        <span class="fw-bold mx-2">Admission Number :</span>
                        <span>{{ student.admission_number ?? '' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-calendar"></i>
                        <span class="fw-bold mx-2">Date Of Admission :</span>
                        <span>{{ admission.date ? $filters.date_DAY_MONTH_YEAR(admission.date) : '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-chalkboard"></i>
                        <span class="fw-bold mx-2">Class :</span>
                        <span>{{ student.rank?.name ?? '-'}} {{ student.rank?.stream?.name }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-male-female"></i>
                        <span class="fw-bold mx-2">Gender :</span>
                        <span>{{ student.gender?.name ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-church"></i>
                        <span class="fw-bold mx-2">Religion :</span>
                        <span>{{ student.religion?.name ?? '-' }}</span>
                     </li>
                     <li v-if="admission.has_exit_school" class="d-flex align-items-center mb-4 text-danger">
                        <i class="icon-base bx bx-calendar"></i>
                        <span class="fw-bold mx-2">Date Of Exit :</span>
                        <span>{{ $filters.date_DAY_MONTH_YEAR(admission.date_of_exit ?? '') }}</span>
                     </li>
                  </ul>
               </div>
            </div>
         </div>
         
         <div class="col-xl-8 col-md-6 order-3 order-md-2">
            <div class="accordion">
               <!-- Student Data Accordion -->
               <div class="card accordion-item">
                  <h2 class="accordion-header border-bottom">
                     <button
                        type="button"
                        class="accordion-button"
                        :class="{ collapsed: openAccordion !== 'general-details' }"
                        @click="toggleAccordion('general-details')">
                        General Details
                     </button>
                  </h2>
                  <div class="accordion-collapse" :class="{ show: openAccordion === 'general-details' }">
                     <div class="accordion-body px-0 py-4 d-flex align-items-baseline flex-wrap flex-xl-nowrap flex-sm-nowrap flex-md-wrap">
                        <table class="table table-sm table-borderless text-nowrap small">
                           <tbody>
                           <tr>
                              <td class="w-50">Date Of Birth</td>
                              <td class="fw-medium text-heading">{{ student.date_of_birth ? $filters.date_DAY_MONTH_YEAR(student.date_of_birth) : '-' }}</td>
                           </tr>
                           <tr>
                              <td>Birth Certificate Number</td>
                              <td class="fw-medium text-heading">{{ student.birth_certificate_number ?? '-' }}</td>
                           </tr>
                           <tr>
                              <td>Citizenship</td>
                              <td class="fw-medium text-heading">{{ student.citizenship ?? '-' }}</td>
                           </tr>
                           <tr>
                              <td>County/State</td>
                              <td class="fw-medium text-heading">{{ student.county ?? '-' }}</td>
                           </tr>
                           <tr>
                              <td>Ward</td>
                              <td class="fw-medium text-heading">{{ student.ward ?? '-' }}</td>
                           </tr>
                           </tbody>
                        </table>
                        <table class="table table-sm table-borderless text-nowrap small">
                           <tbody>
                           <tr>
                              <td class="w-50">Permanent Address</td>
                              <td class="fw-medium text-heading">{{ student.permanent_address ?? '-' }}</td>
                           </tr>
                           <tr>
                              <td>Previous School</td>
                              <td class="fw-medium text-heading">{{ student.previous_school ?? '-' }}</td>
                           </tr>
                           <tr>
                              <td>KCPE Score</td>
                              <td class="fw-medium text-heading">{{ student.kcpe_score ?? '-' }}</td>
                           </tr>
                           <tr>
                              <td>Physical Disability</td>
                              <td class="fw-medium text-heading">{{ student.physical_disability ?? '-' }}</td>
                           </tr>
                           <tr>
                              <td>Hobby</td>
                              <td class="fw-medium text-heading">{{ student.hobby ?? '-' }}</td>
                           </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
               
               <!-- Guardian Details Accordion -->
               <div class="card accordion-item">
                  <h2 class="accordion-header border-bottom">
                     <button
                        type="button"
                        class="accordion-button"
                        :class="{ collapsed: openAccordion !== 'guardian-details' }"
                        @click="toggleAccordion('guardian-details')">
                        Guardian Details
                     </button>
                  </h2>
                  <div class="accordion-collapse" :class="{ show: openAccordion === 'guardian-details' }">
                     <div class="accordion-body py-4 d-flex align-items-baseline flex-wrap flex-xl-nowrap flex-sm-nowrap flex-md-wrap">
                        <div class="table-responsive" style="width:100%; min-width:100%;">
                        <table class="table table-sm table-bordered text-nowrap">
                           <thead>
                           <tr>
                              <th class="p-2 fw-medium text-heading" style="width: 30%;">Name</th>
                              <th class="p-2 fw-medium text-heading" style="width: 20%;">Email</th>
                              <th class="p-2 fw-medium text-heading" style="width: 15%;">Phone</th>
                              <th class="p-2 fw-medium text-heading" style="width: 15%;">Relationship</th>
                              <th class="p-2 fw-medium text-heading" style="width: 15%;">ID</th>
                           </tr>
                           </thead>
                           <tbody>
                           <tr v-for="(guardian, index) in guardianDetails" :key="index">
                              <td class="p-2">{{ guardian.first_name ?? '-' }} {{ contact.last_name ?? '-' }}</td>
                              <td class="p-2">{{ guardian.email ?? '-' }}</td>
                              <td class="p-2">{{ guardian.phone ?? '-' }}</td>
                              <td class="p-2">{{ guardian.relationship?.name ?? '-' }}</td>
                              <td class="p-2">{{ guardian.identification_number ?? '-' }}</td>
                           </tr>
                           </tbody>
                        </table>
                        </div>
                     </div>
                  </div>
               </div>
               
               <!-- Student Other Details Accordion -->
               <div class="card accordion-item">
                  <h2 class="accordion-header border-bottom">
                     <button
                        type="button"
                        class="accordion-button"
                        :class="{ collapsed: openAccordion !== 'other-details' }"
                        @click="toggleAccordion('other-details')">
                        Other Details
                     </button>
                  </h2>
                  <div class="accordion-collapse" :class="{ show: openAccordion === 'other-details' }">
                     <div class="accordion-body px-0 py-4 d-flex align-items-baseline flex-wrap flex-xl-nowrap flex-sm-nowrap flex-md-wrap">
                        <table class="table table-sm table-borderless text-nowrap small">
                           <tbody>
                           <tr>
                              <td class="w-25">Physical Disability</td>
                              <td class="fw-medium text-heading">{{ student.physical_disability ?? '-'}}</td>
                           </tr>
                           <tr>
                              <td class="w-25">Hobby</td>
                              <td class="fw-medium text-heading">{{ student.hobby ?? '-' }}</td>
                           </tr>
                           <tr>
                              <td class="w-25">Medical Details</td>
                              <td class="fw-medium text-heading">{{ student.medical_details ?? '-' }}</td>
                           </tr>
                           <tr>
                              <td class="w-25">Character Book</td>
                              <td class="fw-medium text-heading">{{ student.character_book ?? '-' }}</td>
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
</template>

<script>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import {Head, Link} from "@inertiajs/vue3";
import {Inertia} from "@inertiajs/inertia";
import axios from "axios";

export default{
   components: {DefaultLayout, Head, Link},
   props: ['admission', 'student'],
   data() {
      return {
         guardianDetails: [],
         
         dataFetched: false,
         openAccordion: 'general-details'
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
         const targetUrl = '/admin/student-admissions/' + this.admission.hashid;
         if (event.detail.page.url === targetUrl && !this.dataFetched) {
            this.fetchAllData();
         }
      },
      fetchAllData() {
         this.fetchedGuardianDetails();
         this.dataFetched = true;
      },
      fetchedGuardianDetails() {
         if (!this.admission.student) {
            return;
         }
         axios.get('/datatable/guardians', {
            params: {
               filter: {
                  student_id: this.admission.student.id,
               },
            },
         }).then(({data}) => {
            this.guardianDetails = data.data;
         }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the guardian details.')
         });
      },
      toggleAccordion(section) {
         this.openAccordion = this.openAccordion === section ? null : section;
      },
   },
}
</script>

<style scoped></style>
