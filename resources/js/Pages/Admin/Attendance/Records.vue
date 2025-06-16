<script>
import _debounce from 'lodash/debounce.js';
import axios from 'axios';
import DefaultLayout from '@layouts/DefaultLayout.vue';
import {VueTable} from '@componentsDatable.vue';
import {useForm} from "@inertiajs/vue3";
import {Modal} from 'bootstrap';
import {Inertia} from '@inertiajs/inertia';

export default {
   layout: DefaultLayout,
   
   component: {VueTable},
   
   data() {
      return {
         fields: [
            {
               name: 'student.student_admission_id',
               title: 'ADM NO',
            },
            {
               name: '__slot:student',
               title: 'NAME',
            },
            {
               name: 'rank.name',
               title: 'CLASS',
            },
            {
               name: '__slot:status',
               title: 'Status',
            },
            {
               name: 'teacher.employee.first_name',
               title: 'CL TEARCHER',
            },
            {
               name: 'date',
               title: 'Date',
            },
            {
               name: '__slot:actions',
               title: '',
            },
         ],
         appendParams: {
            filter: {
               search: '',
               date: new Date().toISOString().slice(0, 10),
               class_id: ''
            }
         },
         
         form: useForm({
            name: '',
            division_id: '',
            stream_id: '',
            teacher_id: '',
         }),
         
         editForm: useForm({
            id: '',
            name: '',
            division_id: '',
            stream_id: '',
            teacher_id: '',
         }),
         
         classes: [],
         divisions: [],
         selectedRecord: null,
      };
   },
   created() {
      this.fetchClasses()
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
            'Present': 'bg-green-100 text-green-800',
            'Absent': 'bg-red-100 text-red-800',
            'Late': 'bg-yellow-100 text-yellow-800',
            'Excused': 'bg-blue-100 text-blue-800'
         }
         return colors[status] || 'bg-gray-100 text-gray-800'
      },
      fetchClasses() {
         axios.get('/datatable/ranks')
            .then(({data}) => {
               this.classes = data.data;
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

<template>
   <div class="row">
      <div class="col-xxl-12">
         <h3>Attendance Records</h3>
         
         <div class="card">
            <div class="card-header flex-column flex-md-row">
               <div class="row row-gap-1">
                  <div class="col-md-3 col-6">
                     <input type="search" id="search" class="form-control bg-muted-lt rounded-2" placeholder="Search..."
                            @input="applyFilter" v-model="appendParams.filter.search">
                  
                  </div>
                  <div class="col-md-3 col-6">
                     <select v-model="appendParams.filter.class_id" @change="applyFilter" class="form-select">
                        
                        <option v-bind:key="cls.id" v-for="cls in classes" :value="cls.id">{{ cls.name }}</option>
                     </select>
                     <!-- <label for="">Class</label> -->
                  
                  </div>
                  <div class="col-md-3">
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
                  <div :class="'py-1 px-2 rounded-pill '+getStatusColor(props.rowData.status)">
                     {{ props.rowData.status }}
                  </div>
               </template>
               <template v-slot:actions="props">
                  <div class="dropdown">
                     <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical"></i>
                     </button>
                     <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="#" @click="viewRecord(props.rowData)">
                           <i class='bx bx-minus-back'></i> view
                        </a>
                     </div>
                  </div>
               </template>
            </VueTable>
         </div>
         
         <!-- Create Modal -->
         <div
            class="modal fade"
            id="view-attendance-modal"
            tabindex="-1"
            aria-labelledby="create-rank-modal-label"
            aria-hidden="true"
            ref="viewRecordModal"
         >
            <div class="modal-dialog">
               <div v-if="selectedRecord !== null" class="modal-content">
                  <div class="modal-header">
                     <h5 class="modal-title" id="create-rank-modal-label">{{ selectedRecord?.student.first_name }}
                        {{ selectedRecord?.student.last_name }}</h5>
                     <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                     ></button>
                  </div>
                  <div class="modal-body">
                     <div class="mb-2"><span class="text-muted fw-bold">Adm no: </span>
                        {{ selectedRecord?.student.student_admission_id }}
                     </div>
                     <div class="mb-2"><span class="text-muted fw-bold">Class: </span>{{ selectedRecord?.rank.name }}
                     </div>
                     <span :class="'p-2 rounded  '+getStatusColor(selectedRecord?.status)">{{ selectedRecord?.status }} On {{
                           selectedRecord?.date
                        }}</span>
                     <div class="form my-3">
                        <label class="mb-2">Remarks</label>
                        <textarea disabled class="form-control" name="" id="">{{ selectedRecord?.remarks }}</textarea>
                     </div>
                     <span class="text-muted fw-bold">Done by:</span> {{ selectedRecord?.teacher.employee.first_name }}
                     - {{ selectedRecord?.teacher.employee.last_name }}
                  </div>
                  <div class="modal-footer">
                     <button
                        type="button"
                        class="btn btn-secondary me-2"
                        data-bs-dismiss="modal"
                     >
                        Close
                     </button>
                     <button
                        type="button"
                        class="btn btn-primary"
                        @click.prevent="createRank"
                     >
                        Submit
                     </button>
                  </div>
               </div>
            </div>
         </div>
      
      
      </div>
   </div>
</template>

<style scoped>


</style>
