<template>
   <Head title="Attendance Report" />
   
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Attendance History</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
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
                        <label for="classId" class="form-label-md mb-1">Class</label>
                        <v-select
                           id="classId"
                           v-model="appendParams.filter.rank_id"
                           :options="ranks"
                           label="name"
                           :reduce="(option) => option.id"
                        >
                           <template #option="option">
                              {{ option.name }} {{ option.stream?.name }}
                           </template>
                           <template #selected-option="option">
                              {{ option.name }} {{ option.stream?.name }}
                           </template>
                        </v-select>
                     </div>
                     <div class="col-md-3 col-6">
                        <label for="search" class="form-label-md mb-1">Search Student</label>
                        <input type="search" id="search" class="form-control bg-muted-lt rounded-2" placeholder="Search..."
                               @input="applyFilter" v-model="appendParams.filter.search">
                     </div>
                  </div>
               </div>
               
               <VueTable
                  api-url="datatable/attendance"
                  :fields="fields"
                  ref="classTable"
                  :append-params="appendParams"
               >
                  <template v-slot:student="props">
                     <div>
                        {{ props.rowData.student.first_name + ' ' + props.rowData.student.last_name }}
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
               name: 'teacher.employee.last_name',
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
               rank_id: '',
               search: ''
            }
         },
         
         ranks: [],
         divisions: [],
         selectedRecord: null,
      };
   },
   created() {
      this.fetchClasses()
   },
   watch: {
      'appendParams.filter.rank_id': function (newValue) {
         if(newValue) {
            this.$refs.classTable.reloadTable()
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
      fetchClasses() {
         axios.get('/datatable/ranks')
            .then(({data}) => {
               this.ranks = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred when fetching the divisions.')
         })
      },
      // applyFilter(){
      //     this.$refs.classTable.reloadTable();
      // },
      applyFilter: _debounce(function () {
         this.$refs.classTable.reloadTable()
      }, 800),
   },
}
</script>

<style scoped>
</style>
