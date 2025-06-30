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
                              <Link href="/admin/employees/teachers/create"
                                    class="btn btn-primary d-none d-sm-inline-block">
                                 <i class="bx bx-plus-circle me-2"></i>
                                 Register Teacher
                              </Link>
                              <Link href="/admin/employees/teachers/create" class="btn btn-primary btn-icon d-sm-none">
                                 <i class="bx bx-plus"></i>
                              </Link>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
   
               <VueTable
                  api-url="datatable/teachers"
                  :fields="fields"
                  ref="teachersTable"
                  :append-params="appendParams"
               >
                  <template v-slot:name="props">
                     <div>
                        {{ props.rowData.honorific?.name + ' ' + props.rowData.first_name + ' ' + props.rowData.last_name }}
                     </div>
                  </template>
                  <template v-slot:actions="props">
                     <div class="dropdown">
                        <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                           <i class="bx bx-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                           <Link class="dropdown-item" :href="'/admin/employees/teachers/' + props.rowData.hashid">
                              <i class="icon-base bx bx-detail me-2"></i> Details
                           </Link>
                           <Link class="dropdown-item"
                                 :href="'/admin/employees/teachers/' + props.rowData.hashid + '/edit'">
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
               name: 'employee.email',
               title: 'EMAIL',
            },
            {
               name: 'employee.staff_number',
               title: 'EMPLOYEE NUMBER',
            },
            {
               name: 'employee.employment_type.name',
               title: 'TYPE',
            },
            {
               name: 'employee.employment_status.name',
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
      };
   },
   methods: {
      applyFilter: _debounce(function () {
         this.$refs.teachersTable.reloadTable();
      }, 800),
   },
}
</script>

<style scoped>
</style>
