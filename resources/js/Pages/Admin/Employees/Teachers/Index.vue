<template>
   <Head title="Teachers"/>
   
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Registered Teachers</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Registered Teachers
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
                              <Link :href="route('admin.teachers.create')"
                                    class="btn btn-primary d-none d-sm-inline-block">
                                 <i class="icon-base bx bx-plus-circle me-2"></i>
                                 Register Teacher
                              </Link>
                              
                              <Link :href="route('admin.teachers.create')" class="btn btn-primary btn-icon d-sm-none">
                                 <i class="icon-base bx bx-plus"></i>
                              </Link>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- Add loading and error states -->
               <div v-if="loading" class="card-body">
                  <div class="text-center py-4">
                     <div class="spinner-border text-primary" role="status"></div>
                     <p class="mt-2">Loading teachers...</p>
                  </div>
               </div>
               
               <div v-else-if="error" class="card-body">
                  <div class="alert alert-danger">
                     <h5>Error Loading Teachers</h5>
                     <p>{{ error }}</p>
                     <button @click="loadTeachers" class="btn btn-primary">Retry</button>
                  </div>
               </div>
   
               <VueTable
                  v-else
                  api-url="datatable/teachers"
                  :fields="fields"
                  ref="teachersTable"
                  :append-params="appendParams"
                  @loading="loading = $event"
                  @error="handleTableError"
               >
                  <template v-slot:name="props">
                     <div>
                        {{ getFullName(props.rowData) }}
                     </div>
                  </template>
                  
                  <!-- Add email slot -->
                  <template v-slot:email="props">
                     <div>
                        {{ props.rowData.employee?.email || 'N/A' }}
                     </div>
                  </template>
                  
                  <!-- Add employee number slot -->
                  <template v-slot:employee_number="props">
                     <div>
                        {{ props.rowData.employee?.staff_number || 'N/A' }}
                     </div>
                  </template>
                  
                  <!-- Add employment type slot -->
                  <template v-slot:employment_type="props">
                     <div>
                        {{ props.rowData.employee?.employment_type?.name || 'N/A' }}
                     </div>
                  </template>
                  
                  <!-- Add employment status slot -->
                  <template v-slot:employment_status="props">
                     <div>
                        {{ props.rowData.employee?.employment_status?.name || 'N/A' }}
                     </div>
                  </template>
                  
                  <template v-slot:actions="props">
                     <div class="dropdown">
                        <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                           <i class="icon-base bx bx-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                           <Link class="dropdown-item" :href="route('admin.teachers.show', props.rowData.hashid)">
                              <i class="icon-base bx bx-detail me-2"></i> Details
                           </Link>
                           <Link class="dropdown-item"
                                 :href="route('admin.teachers.edit', props.rowData.hashid)">
                              <i class="icon-base bx bx-edit-alt me-2"></i> Edit
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
import {Head, Link} from "@inertiajs/vue3";
import _debounce from "lodash/debounce.js";

export default {
   components: {DefaultLayout, Head, Link},
   data() {
      return {
         fields: [
            {
               name: '__slot:name',
               title: 'NAME',
            },
            {
               name: '__slot:email', // Use slot for email
               title: 'EMAIL',
            },
            {
               name: '__slot:employee_number', // Use slot for employee number
               title: 'EMPLOYEE NUMBER',
            },
            {
               name: '__slot:employment_type', // Use slot for employment type
               title: 'TYPE',
            },
            {
               name: '__slot:employment_status', // Use slot for employment status
               title: 'STATUS',
            },
            {
               name: '__slot:actions',
               title: 'ACTIONS',
               titleClass: 'text-end w-5',
               dataClass: 'text-end w-5',
            },
         ],
         appendParams: {
            filter: {
               search: '',
            }
         },
         loading: false,
         error: null,
      };
   },
   methods: {
      applyFilter: _debounce(function () {
         this.$refs.teachersTable.reloadTable();
      }, 800),
      
      handleTableError(error) {
         console.error('Table error:', error);
         this.error = error.message || 'Failed to load teachers data';
         this.$toast.error('Failed to load teachers');
      },
      
      loadTeachers() {
         this.error = null;
         if (this.$refs.teachersTable) {
            this.$refs.teachersTable.reloadTable();
         }
      },
      
      getFullName(teacher) {
         const honorific = teacher.honorific?.name || '';
         const firstName = teacher.first_name || '';
         const lastName = teacher.last_name || '';
         
         return `${honorific} ${firstName} ${lastName}`.trim();
      }
   },
   
   mounted() {
      // Add a small delay to ensure the table is mounted
      setTimeout(() => {
         if (this.$refs.teachersTable && this.$refs.teachersTable.reloadTable) {
            this.loadTeachers();
         }
      }, 100);
   },
}
</script>

<style scoped>
</style>