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
                  <div class="row row-gap-1">
                     <div class="col-md-3 col-9">
                        <input type="search" id="search" class="form-control bg-muted-lt rounded-2" placeholder="Search..."
                               @input="applyFilter" v-model="appendParams.filter.search">
                     </div>
                     <div class="col-md-6 col-3 ms-lg-auto">
                        <div class="flex-wrap text-end">
                           <div class="card-action">
                              <Link :href="route('admin.admissions.form')" class="btn btn-primary d-none d-sm-inline-block">
                                 <i class="bx bx-plus-circle me-2"></i>
                                 New Registration
                              </Link>
                              
                              <Link :href="route('admin.admissions.form')" class="btn btn-primary btn-icon d-sm-none">
                                 <i class="bx bx-plus"></i>
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
               >
                  <template v-slot:date="props">
                     {{ $filters.date_DAY_MONTH_YEAR(props.rowData.date) }}
                  </template>
                  <template v-slot:student="props">
                     {{ props.rowData.student.first_name }} {{ props.rowData.student.last_name }}
                  </template>
                  <template v-slot:actions="props">
                     <div class="dropdown">
                        <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                           <i class="bx bx-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                           <Link class="dropdown-item" :href="route('admin.admissions.show', props.rowData.hashid)">
                              <i class="icon-base bx bx-detail me-1"></i> Details
                           </Link>
                           <Link :href="route('admin.admissions.edit', props.rowData.hashid)" class="dropdown-item">
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
               width: '20%',
            },
            {
               name: 'student.admission_number',
               title: 'ADMISSION NUMBER',
               width: '20%',
            },
            {
               name: '__slot:student',
               title: 'STUDENT',
               width: 'auto',
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
</style>
