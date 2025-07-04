<template>
   <Head title="Attendance Report" />
   
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Attendance History</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('employee.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Attendance Report
               </li>
            </ol>
         </nav>
         <div class="col-xxl-12">
            <div class="card">
               <div class="card-header flex-column flex-md-row">
                  <div class="row row-gap-1">
                     <div class="col-md-3">
                        <label for="date" class="form-label-md mb-1">Date</label>
                        <date-picker
                           id="date"
                           form-class="shadow-sm"
                           :value="appendParams.filter.date"
                           :max-date="new Date()"
                           @on-change="function(dateObj, dateStr) {
                              appendParams.filter.date= dateStr
                              applyFilter()
                           }"
                        ></date-picker>
                     </div>
                     <div class="col-md-3 col-6">
                        <label for="search" class="form-label-md mb-1">Search Student</label>
                        <input type="search" id="search" class="form-control bg-muted-lt rounded-2" placeholder="Search..."
                               @input="applyFilter" v-model="appendParams.filter.search">
                     </div>
                  </div>
               </div>
               
               <VueTable
                  api-url="employee/datatable/attendance"
                  :fields="fields"
                  ref="attendanceReportTable"
                  :append-params="appendParams"
               >
                  <template v-slot:student="props">
                     <div>
                        {{ props.rowData.student.first_name + ' ' + props.rowData.student.last_name }}
                     </div>
                  </template>
                  <template v-slot:teacher="props">
                     <div>
                        {{ props.rowData.teacher.first_name + ' ' + props.rowData.teacher.last_name }}
                     </div>
                  </template>
                  <template v-slot:status="props">
                     <div :class="'badge rounded-pill ' + getStatusColor(props.rowData.status)">
                        {{ props.rowData.status }}
                     </div>
                  </template>
                  <template v-slot:actions="props">
                     <div class="dropdown">
                        <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                           <i class="icon-base bx bx-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                           <a class="dropdown-item" href="#" @click="viewRecord(props.rowData)">
                              <i class='icon-base bx bx-minus-back'></i> view
                           </a>
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
import DefaultLayout from '@layouts/DefaultLayout.vue';
import {Head, Link} from "@inertiajs/vue3";
import _debounce from 'lodash/debounce.js';
import axios from 'axios';
import {Modal} from 'bootstrap';

export default {
   components: {DefaultLayout, Head, Link},
   props: {
      teacher: {
         type: Object,
         required: true,
      },
      rank: {
         type: Object,
         required: true,
      },
   },
   data() {
      return {
         fields: [
            {
               name: 'student.admission_number',
               title: 'ADM NO',
            },
            {
               name: '__slot:student',
               title: 'NAME',
            },
            {
               name: '__slot:status',
               title: 'STATUS',
            },
            {
               name: '__slot:teacher',
               title: 'CLASS TEACHER',
            },
            {
               name: 'date',
               title: 'DATE',
            },
            {
               name: '__slot:actions',
               title: '',
            },
         ],
         appendParams: {
            filter: {
               date: new Date().toISOString().slice(0, 10),
               search: ''
            }
         },
         
         selectedRecord: null,
      };
   },
   watch: {
      'appendParams.filter.date': function (newValue) {
         if(newValue) {
            this.$refs.attendanceReportTable.reloadTable()
         }
      },
   },
   methods: {
      viewRecord(rowData) {
         this.selectedRecord = rowData;
         const modalElement = this.$refs.viewRecordModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      getStatusColor(status) {
         const colors = {
            'Present': 'bg-success',
            'Absent': 'bg-danger',
            'Late': 'bg-yellow',
            'Excused': 'bg-secondary'
         }
         return colors[status] || 'bg-gray-100 text-gray-800'
      },
      applyFilter: _debounce(function () {
         this.$refs.attendanceReportTable.reloadTable()
      }, 800),
   },
}
</script>

<style scoped>
</style>
