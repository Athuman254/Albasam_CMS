<template>
   <Head title="Student Admissions"/>
   
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Student Admissions</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Student Admissions
               </li>
            </ol>
         </nav>
         
         <div class="col-xxl-12">
            <div class="card">
               <div class="card-header flex-column flex-md-row">
                  <div class="row row-gap-1 align-items-center">
                     <div class="col-md-3 col-9">
                        <input type="search" id="search" class="form-control bg-muted-lt rounded-2" placeholder="Search students..."
                               @input="applyFilter" v-model="appendParams.filter.search">
                     </div>
                     <div class="col-md-6 col-3 ms-lg-auto">
                        <div class="flex-wrap text-end">
                           <div class="card-action">
                              <Link :href="route('admin.admissions.form')" class="btn btn-primary d-none d-sm-inline-block">
                                 <i class="icon-base bx bx-plus-circle me-2"></i>
                                 New Registration
                              </Link>
                              
                              <Link :href="route('admin.admissions.form')" class="btn btn-primary btn-icon d-sm-none">
                                 <i class="icon-base bx bx-plus"></i>
                              </Link>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               
               <VueTable
                  api-url="datatable/student-admissions"
                  :fields="fields"
                  ref="admissionsTable"
                  :append-params="appendParams"
                  :per-page="20"
               >
                  <template v-slot:date="props">
                     <span class="text-muted">{{ props.rowData.formatted_date || 'N/A' }}</span>
                  </template>
                  <template v-slot:student="props">
                     <div class="fw-medium">{{ props.rowData.student_name || 'No Student' }}</div>
                  </template>
                  <template v-slot:actions="props">
                     <div class="dropdown">
                        <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                           <i class="icon-base bx bx-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                           <Link class="dropdown-item" :href="route('admin.admissions.show', props.rowData.id)">
                              <i class="icon-base bx bx-detail me-1"></i> Details
                           </Link>
                           <Link :href="route('admin.admissions.edit', props.rowData.id)" class="dropdown-item">
                              <i class="icon-base bx bx-edit-alt me-1"></i> Edit
                           </Link>
                        </div>
                     </div>
                  </template>
               </VueTable>
            </div>
         </div>
      </div>
   </DefaultLayout>
</template>

<script>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import {Head, Link} from "@inertiajs/vue3"
import _debounce from "lodash/debounce.js";

export default {
   components: {DefaultLayout, Head, Link},
   data() {
      return {
         fields: [
            {
               name: '__slot:date',
               title: 'ADMISSION DATE',
               titleClass: 'font-weight-bold',
               width: '15%',
            },
            {
               name: 'admission_number',
               title: 'ADMISSION NO.',
               width: '15%',
            },
            {
               name: '__slot:student',
               title: 'NAME',
               width: 'auto',
            },
            {
               name: 'student_class',
               title: 'CLASS',
               width: '15%',
            },
            {
               name: '__slot:actions',
               title: 'ACTIONS',
               titleClass: 'text-end',
               dataClass: 'text-end',
               width: '10%',
            },
         ],
         appendParams: {
            filter: {
               search: '',
            }
         },
      }
   },
   methods: {
      applyFilter: _debounce(function () {
         this.$refs.admissionsTable.reloadTable()
      }, 800),
   },
}
</script>

<style scoped>
.card {
   box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
   border: 1px solid rgba(0, 0, 0, 0.125);
}

.form-control:focus {
   border-color: #696cff;
   box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
}

.btn-primary {
   background-color: #696cff;
   border-color: #696cff;
}

.btn-primary:hover {
   background-color: #5f62e0;
   border-color: #5f62e0;
}
</style>