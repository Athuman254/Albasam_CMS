<template>
   <Head title="Student Details"/>
   
   <DefaultLayout>
      <div class="row">
         <div class="row align-items-center d-print-none">
            <div class="col-8">
               <h3 class="mb-0">{{ student ? student.first_name + ' ' + student.last_name : 'Student Details' }}</h3>
            </div>
         </div>

         <!-- Professional Print Header -->
         <div class="d-none d-print-block mb-4">
            <div class="row align-items-center border-bottom pb-3 mb-3">
               <div class="col-2">
                  <img v-if="institution?.logo_url || institution?.logo" :src="institution?.logo_url || '/logo.png'" 
                       alt="School Logo" style="width: 80px; height: 80px; object-fit: contain;">
                  <img v-else src="/logo.png" alt="Logo" style="width: 80px; height: 80px; object-fit: contain;">
               </div>
               <div class="col-10 text-center">
                  <h3 class="fw-bold mb-1 text-uppercase" style="font-size: 1.5rem; color: #333;">{{ institution?.name || 'School Management System' }}</h3>
                  <p class="mb-0 text-muted" style="font-size: 0.9rem;">
                     {{ institution?.physical_address || '' }} | {{ institution?.phone || '' }} | {{ institution?.email || '' }}
                  </p>
                  <h5 class="mt-2 fw-bold text-decoration-underline text-uppercase">Student Information Report</h5>
               </div>
            </div>
            
            <div class="row mb-4">
               <div class="col-3 text-center">
                   <img v-if="student.photo_url" :src="student.photo_url" alt="Student Photo" 
                        class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover; border: 2px solid #ddd;">
               </div>
               <div class="col-9">
                  <div class="row">
                     <div class="col-6 mb-2"><strong>Full Name:</strong> {{ student.first_name }} {{ student.middle_name }} {{ student.last_name }}</div>
                     <div class="col-6 mb-2"><strong>Admission No:</strong> {{ student.admission_number }}</div>
                     <div class="col-6 mb-2"><strong>Class:</strong> {{ student.rank?.name || 'N/A' }}</div>
                     <div class="col-6 mb-2"><strong>Gender:</strong> {{ student.gender?.name || 'N/A' }}</div>
                  </div>
               </div>
            </div>
         </div>

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
         
         <!-- Error State -->
         <div class="col-xxl-12" v-if="!student">
            <div class="card">
               <div class="card-body text-center py-5">
                  <i class="bx bx-user-x text-muted mb-3" style="font-size: 3rem;"></i>
                  <h4 class="text-muted">No Student Data Found</h4>
                  <p class="text-muted">This admission record doesn't have an associated student.</p>
                  <Link :href="route('admin.admissions.index')" class="btn btn-primary">
                     Back to Admissions
                  </Link>
               </div>
            </div>
         </div>

         <!-- Student Data -->
         <div class="col-xxl-12" v-else>
            <div class="row">
               <!-- Student Profile Card -->
               <div class="col-xl-4 col-md-6 order-1 order-md-1">
                  <div class="card mb-4">
                     <div class="card-body text-center">
                        <div class="student-avatar mb-3">
                           <img v-if="student.photo_url" :src="student.photo_url" alt="Student Photo" 
                                class="rounded-circle img-thumbnail shadow-sm" style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #696cff;">
                           <i v-else class="bx bx-user-circle text-primary" style="font-size: 4rem;"></i>
                        </div>
                        <h4 class="card-title">{{ student.first_name }} {{ student.last_name }}</h4>
                        <p class="text-muted">{{ student.admission_number }}</p>
                        
                        <div class="student-status-badge mb-3">
                           <span class="badge bg-success" v-if="admission?.is_active">Active</span>
                           <span class="badge bg-danger" v-else>Exited</span>
                        </div>
                     </div>
                  </div>

                  <div class="card mb-4 quick-info-card">
                     <div class="card-header">
                        <h6 class="card-title mb-0">Quick Information</h6>
                     </div>
                     <div class="card-body">
                        <ul class="list-unstyled mb-0">
                           <li class="d-flex align-items-center mb-3">
                              <i class="icon-base bx bx-calendar text-primary me-2"></i>
                              <div>
                                 <small class="text-muted">Admission Date</small>
                                 <div class="fw-medium">{{ admission?.formatted_date || 'N/A' }}</div>
                              </div>
                           </li>
                           <li class="d-flex align-items-center mb-3">
                              <i class="icon-base bx bx-chalkboard text-primary me-2"></i>
                              <div>
                                 <small class="text-muted">Class</small>
                                 <div class="fw-medium">{{ student.rank?.name || 'N/A' }} {{ student.rank?.stream?.name || '' }}</div>
                              </div>
                           </li>
                           <li class="d-flex align-items-center mb-3">
                              <i class="icon-base bx bx-male-female text-primary me-2"></i>
                              <div>
                                 <small class="text-muted">Gender</small>
                                 <div class="fw-medium">{{ student.gender?.name || 'N/A' }}</div>
                              </div>
                           </li>
                           <li class="d-flex align-items-center mb-3">
                              <i class="icon-base bx bx-church text-primary me-2"></i>
                              <div>
                                 <small class="text-muted">Religion</small>
                                 <div class="fw-medium">{{ student.religion?.name || 'N/A' }}</div>
                              </div>
                           </li>
                           <li class="d-flex align-items-center" v-if="admission?.has_exit_school">
                              <i class="icon-base bx bx-calendar-x text-danger me-2"></i>
                              <div>
                                 <small class="text-muted">Date of Exit</small>
                                 <div class="fw-medium text-danger">{{ safeDateFormat(admission.date_of_exit) }}</div>
                              </div>
                           </li>
                        </ul>
                     </div>
                  </div>

                  <!-- Action Buttons -->
                  <div class="card">
                     <div class="card-body">
                        <div class="d-grid gap-2">
                           <Link :href="route('admin.admissions.edit', admission?.hashid)" class="btn btn-primary d-print-none" v-if="admission">
                              <i class="bx bx-edit me-2"></i>Edit Details
                           </Link>
                           <button @click="printReport" class="btn btn-outline-primary d-print-none">
                              <i class="bx bx-printer me-2"></i>Print Details
                           </button>
                           <Link :href="route('admin.admissions.index')" class="btn btn-outline-secondary d-print-none">
                              <i class="bx bx-arrow-back me-2"></i>Back to List
                           </Link>
                        </div>
                     </div>
                  </div>
               </div>
               
               <!-- Details Accordion -->
               <div class="col-xl-8 col-md-6 order-2 order-md-2">
                  <div class="accordion" id="studentDetailsAccordion">
                     <!-- General Details Accordion -->
                     <div class="card accordion-item">
                        <h2 class="accordion-header">
                           <button
                              type="button"
                              class="accordion-button"
                              :class="{ collapsed: openAccordion !== 'general-details' }"
                              @click="toggleAccordion('general-details')">
                              <i class="bx bx-user-circle me-2"></i>
                              General Details
                           </button>
                        </h2>
                    <!-- General Details Body -->
<div id="generalDetails" v-show="openAccordion === 'general-details' || isPrinting" aria-labelledby="headingOne" data-bs-parent="#studentDetailsAccordion">
    <div class="accordion-body fw-normal">

                              <div class="row">
                                 <div class="col-md-6">
                                    <table class="table table-sm table-borderless">
                                       <tbody>
                                       <tr>
                                          <td class="w-50 text-muted">Date of Birth</td>
                                          <td class="fw-medium">{{ student.date_of_birth ? safeDateFormat(student.date_of_birth) : 'N/A' }}</td>
                                       </tr>
                                       <tr>
                                          <td class="text-muted">Birth Certificate Number</td>
                                          <td class="fw-medium">{{ student.birth_certificate_number || 'N/A' }}</td>
                                       </tr>
                                       <tr>
                                          <td class="text-muted">Assessment Number</td>
                                          <td class="fw-medium">{{ student.assessment_number || 'N/A' }}</td>
                                       </tr>
                                       <tr>
                                          <td class="text-muted">Citizenship</td>
                                          <td class="fw-medium">{{ student.citizenship || 'N/A' }}</td>
                                       </tr>
                                       <tr>
                                          <td class="text-muted">County/State</td>
                                          <td class="fw-medium">{{ student.county || 'N/A' }}</td>
                                       </tr>
                                       <tr>
                                          <td class="text-muted">Ward</td>
                                          <td class="fw-medium">{{ student.ward || 'N/A' }}</td>
                                       </tr>
                                       </tbody>
                                    </table>
                                 </div>
                                 <div class="col-md-6">
                                    <table class="table table-sm table-borderless">
                                       <tbody>
                                       <tr>
                                          <td class="w-50 text-muted">Permanent Address</td>
                                          <td class="fw-medium">{{ student.permanent_address || 'N/A' }}</td>
                                       </tr>
                                       <tr>
                                          <td class="text-muted">Previous School</td>
                                          <td class="fw-medium">{{ student.previous_school || 'N/A' }}</td>
                                       </tr>
                                       <tr>
                                          <td class="text-muted">KCPE Score</td>
                                          <td class="fw-medium">{{ student.kcpe_score || 'N/A' }}</td>
                                       </tr>
                                       <tr>
                                          <td class="text-muted">Physical Disability</td>
                                          <td class="fw-medium">{{ student.physical_disability || 'None' }}</td>
                                       </tr>
                                       <tr>
                                          <td class="text-muted">Hobby</td>
                                          <td class="fw-medium">{{ student.hobby || 'N/A' }}</td>
                                       </tr>
                                       </tbody>
                                    </table>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     
                     <!-- Guardian Details Accordion -->
                     <div class="card accordion-item">
                        <h2 class="accordion-header">
                           <button
                              type="button"
                              class="accordion-button"
                              :class="{ collapsed: openAccordion !== 'guardian-details' }"
                              @click="toggleAccordion('guardian-details')">
                              <i class="bx bx-group me-2"></i>
                              Guardian Details
                              <span class="badge bg-primary ms-2">{{ guardianDetails.length }}</span>
                           </button>
                        </h2>
                    <!-- Guardian Details Body -->
<div id="guardianDetails" v-show="openAccordion === 'guardian-details' || isPrinting" aria-labelledby="headingTwo" data-bs-parent="#studentDetailsAccordion">
    <div class="accordion-body fw-normal">

                              <div class="table-responsive">
                                 <table class="table table-hover" v-if="guardianDetails.length > 0">
                                    <thead class="table-light">
                                       <tr>
                                          <th class="p-2 fw-medium">Name</th>
                                          <th class="p-2 fw-medium">Relationship</th>
                                          <th class="p-2 fw-medium">Email</th>
                                          <th class="p-2 fw-medium">Phone</th>
                                          <th class="p-2 fw-medium">ID Number</th>
                                          <th class="p-2 fw-medium">Profession</th>
                                       </tr>
                                    </thead>
                                    <tbody>
                                       <tr v-for="(guardian, index) in guardianDetails" :key="index">
                                          <td class="p-2">
                                             <div class="fw-medium">{{ guardian.first_name || '' }} {{ guardian.last_name || '' }}</div>
                                             <small class="text-muted">{{ guardian.middle_name || '' }}</small>
                                          </td>
                                          <td class="p-2">
                                             <span class="badge bg-light text-dark">{{ guardian.relationship?.name || 'N/A' }}</span>
                                          </td>
                                          <td class="p-2">
                                             <a v-if="guardian.email" :href="`mailto:${guardian.email}`" class="text-primary">
                                                {{ guardian.email }}
                                             </a>
                                             <span v-else class="text-muted">N/A</span>
                                          </td>
                                          <td class="p-2">
                                             <a v-if="guardian.phone" :href="`tel:${guardian.phone}`" class="text-primary">
                                                {{ guardian.phone }}
                                             </a>
                                             <span v-else class="text-muted">N/A</span>
                                          </td>
                                          <td class="p-2">{{ guardian.identification_number || 'N/A' }}</td>
                                          <td class="p-2">{{ guardian.profession || 'N/A' }}</td>
                                       </tr>
                                    </tbody>
                                 </table>
                                 <div v-else class="text-center py-5">
                                    <i class="bx bx-group text-muted mb-3" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted">No Guardian Details</h5>
                                    <p class="text-muted">No guardian information has been added for this student.</p>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     
                     <!-- Medical & Other Details Accordion -->
                     <div class="card accordion-item">
                        <h2 class="accordion-header">
                           <button
                              type="button"
                              class="accordion-button"
                              :class="{ collapsed: openAccordion !== 'medical-details' }"
                              @click="toggleAccordion('medical-details')">
                              <i class="bx bx-plus-medical me-2"></i>
                              Medical & Other Details
                           </button>
                        </h2>
                        <div id="medicalDetails" class="accordion-collapse collapse" :class="{ show: openAccordion === 'medical-details' || isPrinting }">
                           <div class="accordion-body">
                              <div class="row">
                                 <div class="col-md-6">
                                    <div class="mb-4">
                                       <h6 class="text-muted mb-2">Physical Disability</h6>
                                       <p class="fw-medium">{{ student.physical_disability || 'None' }}</p>
                                    </div>
                                    <div class="mb-4">
                                       <h6 class="text-muted mb-2">Hobby/Special Interest</h6>
                                       <p class="fw-medium">{{ student.hobby || 'N/A' }}</p>
                                    </div>
                                 </div>
                                 <div class="col-md-6">
                                    <div class="mb-4">
                                       <h6 class="text-muted mb-2">Medical Details</h6>
                                       <p class="fw-medium" v-if="student.medical_details">{{ student.medical_details }}</p>
                                       <p class="text-muted fst-italic" v-else>No medical details provided</p>
                                    </div>
                                    <div class="mb-4">
                                       <h6 class="text-muted mb-2">Character Book</h6>
                                       <p class="fw-medium" v-if="student.character_book">{{ student.character_book }}</p>
                                       <p class="text-muted fst-italic" v-else>No character book details provided</p>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>

                     <!-- Siblings Details Accordion -->
                     <div class="card accordion-item" v-if="siblingDetails.length > 0">
                        <h2 class="accordion-header">
                           <button
                              type="button"
                              class="accordion-button"
                              :class="{ collapsed: openAccordion !== 'sibling-details' }"
                              @click="toggleAccordion('sibling-details')">
                              <i class="bx bx-user-plus me-2"></i>
                              Sibling Details
                              <span class="badge bg-info ms-2">{{ siblingDetails.length }}</span>
                           </button>
                        </h2>
                    <!-- Sibling Details Body -->
<div id="siblingDetails" v-show="openAccordion === 'sibling-details' || isPrinting" aria-labelledby="headingFour" data-bs-parent="#studentDetailsAccordion">
    <div class="accordion-body fw-normal">

                              <div class="table-responsive">
                                 <table class="table table-hover">
                                    <thead class="table-light">
                                       <tr>
                                          <th class="p-2 fw-medium">Name</th>
                                          <th class="p-2 fw-medium">Age</th>
                                          <th class="p-2 fw-medium">Gender</th>
                                          <th class="p-2 fw-medium">Current School</th>
                                          <th class="p-2 fw-medium">Current Class</th>
                                       </tr>
                                    </thead>
                                    <tbody>
                                       <tr v-for="(sibling, index) in siblingDetails" :key="index">
                                          <td class="p-2 fw-medium">{{ sibling.name || 'N/A' }}</td>
                                          <td class="p-2">{{ sibling.age || 'N/A' }}</td>
                                          <td class="p-2">
                                             <span class="badge bg-light text-dark">{{ sibling.gender?.name || 'N/A' }}</span>
                                          </td>
                                          <td class="p-2">{{ sibling.current_school || 'N/A' }}</td>
                                          <td class="p-2">{{ sibling.current_class || 'N/A' }}</td>
                                       </tr>
                                    </tbody>
                                 </table>
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

<script>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import {Head, Link} from "@inertiajs/vue3";
import axios from "axios";

export default{
   components: {DefaultLayout, Head, Link},
   props: {
      admission: {
         type: Object,
         default: null
      },
      student: {
         type: Object,
         default: null
      },
      institution: {
         type: Object,
         default: null
      }
   },
   data() {
      return {
         openAccordion: 'general-details',
         isPrinting: false
      }
   },
   mounted() {
      console.log('Student Prop:', this.student);
      console.log('Siblings:', this.student?.siblings);
      console.log('Guardians:', this.student?.guardians);
   },
   computed: {
      guardianDetails() {
         return this.student?.guardians || [];
      },
      siblingDetails() {
         return this.student?.siblings || [];
      }
   },
   methods: {
      toggleAccordion(section) {
         this.openAccordion = this.openAccordion === section ? null : section;
      },
      printReport() {
         this.isPrinting = true;
         // Store original title
         const originalTitle = document.title;
         // Set blank title to hide "Skaass SMS" from browser header
         document.title = "";

         // Give Vue a moment to expand all sections in the DOM
         this.$nextTick(() => {
            setTimeout(() => {
               window.print();
               // Restore title
               document.title = originalTitle;
               this.isPrinting = false;
            }, 500);
         });
      },
      safeDateFormat(dateValue) {
         if (!dateValue) return 'N/A';
         
         try {
            // Use the existing filter if it's safe
            if (this.$filters && this.$filters.date_DAY_MONTH_YEAR) {
               return this.$filters.date_DAY_MONTH_YEAR(dateValue);
            } else {
               // Fallback formatting
               const date = new Date(dateValue);
               if (isNaN(date.getTime())) {
                  return 'N/A';
               }
               
               return date.toLocaleDateString('en-GB', {
                  day: '2-digit',
                  month: 'short',
                  year: 'numeric'
               });
            }
         } catch (error) {
            console.warn('Date formatting error:', error);
            return 'N/A';
         }
      }
   },

}
</script>

<style scoped>
.student-avatar {
   display: flex;
   justify-content: center;
   align-items: center;
}

.accordion-button {
   font-weight: 600;
}

.accordion-button:not(.collapsed) {
   background-color: #f8f9fa;
   color: #696cff;
}

.accordion-button:focus {
   box-shadow: none;
   border-color: rgba(0,0,0,.125);
}

.table-borderless td {
   border: none;
   padding: 0.5rem 0.75rem;
}

.badge {
   font-size: 0.75em;
}

.card {
   box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
   border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-title {
   color: #566a7f;
}

.text-muted {
   color: #6c757d !important;
}

.btn {
   border-radius: 0.375rem;
}

.table-hover tbody tr:hover {
   background-color: rgba(105, 108, 255, 0.04);
}

.accordion-item {
   margin-bottom: 0.5rem;
}

.accordion-body {
   padding: 1.5rem;
}
</style>
<style>
@media print {
   @page {
      margin: 0.5cm;
   }
   
   .d-print-none, 
   .layout-navbar,
   .layout-menu,
   .btn,
   .breadcrumb,
   nav,
   .footer {
      display: none !important;
   }

   .card {
      border: none !important;
      box-shadow: none !important;
   }

   .accordion-button::after {
      display: none !important;
   }

   .accordion-button {
      background: #f0f0f0 !important;
      color: #000 !important;
      border: 1px solid #ddd !important;
      padding: 10px !important;
      width: 100% !important;
      text-align: left !important;
   }

   body {
      background: white !important;
      font-size: 11pt !important;
      color: black !important;
   }

   .container-xxl, .row, .col-xl-4, .col-xl-8 {
      width: 100% !important;
      max-width: 100% !important;
      padding: 0 !important;
      margin: 0 !important;
      float: none !important;
   }

   .card-body {
      padding: 10px 0 !important;
   }

   h3 {
      text-align: center;
      margin-bottom: 30px !important;
      font-size: 18pt !important;
   }

   .table td {
      padding: 4px !important;
   }

   /* Ensure all accordions are visible */
   .accordion-collapse {
      display: block !important;
      height: auto !important;
      visibility: visible !important;
   }
   
   .accordion-body {
      display: block !important;
   }

   .student-status-badge, .student-avatar {
      display: none !important;
   }

   .quick-info-card {
      display: none !important;
   }

   /* Professional Report Styling */
   .table-borderless td {
      padding: 8px 10px !important;
      border-bottom: 1px solid #eee !important;
   }
   
   .fw-medium {
      color: #000 !important;
   }

   .accordion-item {
      border: 1px solid #ddd !important;
      margin-bottom: 20px !important;
      page-break-inside: avoid;
   }
}
</style>
