<template>
   <Head title="Attendance"/>
   
   <DefaultLayout>
      <div class="row">
         <div class="col-xxl-12">
            <h3>Record Attendance</h3>
            
            <div class="card">
               <div class="card-header flex-column flex-md-row">
                  <div class="row row-gap-1">
                     <div class="col-md-3 col-6">
                        <label for="">Class</label>
                        <v-select
                           v-model="filterParams.filter.rank_id"
                           :options="classes"
                           variant="outlined"
                           label="name"
                           :reduce="option => option.id"
                           density="comfortable"
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
                        <label for="">Date</label>
                        <date-picker
                           form-class="shadow-sm"
                           :value="filterParams.filter.date"
                           :max-date="new Date()"
                           @on-change="function(dateObj, dateStr) {
                             filterParams.filter.date = dateStr
                           }"
                        ></date-picker>
                     </div>
                  </div>
               </div>
               
               <div class="table-responsive text-nowrap">
                  <table class="table">
                     <thead style="background-color: rgb(34, 48, 62, 0.06);">
                     <tr class="text-nowrap">
                        <th>Adm no</th>
                        <th>Student Name</th>
                        <th>Status</th>
                        <th>Remarks</th>
                     </tr>
                     </thead>
                     <tbody class="table-border-bottom-0">
                     <tr v-for="student in students" :key="student.id">
                        <th scope="row">{{ student.id }}</th>
                        <td>{{ student.first_name }} {{ student.last_name }}</td>
                        <td>
                           <v-select v-model="student.status" :options="statusOptions" variant="outlined" :class="getStatusColor(student.status)" density="comfortable"/>
                        </td>
                        <td>
                           <textarea v-model="student.remarks" class="form-control" name="" id="" rows="2"></textarea>
                        </td>
                     </tr>
                     </tbody>
                  </table>
               </div>
               <div class="justify-content-end d-flex p-3">
                  <button @click="submitAttendance" class="btn btn-primary">
                     Submit
                  </button>
               </div>
            </div>
         </div>
      </div>
   </DefaultLayout>
</template>

<script>
import _debounce from 'lodash/debounce.js';
import axios from 'axios';
import DefaultLayout from '@layouts/DefaultLayout.vue';
import {VueTable} from '@componentsDatable.vue';
import {Head, Link, useForm} from "@inertiajs/vue3";
import {Modal} from 'bootstrap';
import {Inertia} from '@inertiajs/inertia';
import {ref} from 'vue';
import DatePicker from "@components/global/_baseDatePicker.vue";

export default {
   component: {DefaultLayout, Head, Link, DatePicker},
   
   data() {
      return {
         attendanceRecords: [],
         students: [],
         form: useForm({
            name: '',
            division_id: '',
            stream_id: '',
            teacher_id: '',
         }),
         classes: [],
         statusOptions: ['Present', 'Absent', 'Late', 'Excused'],
         filterParams: {
            filter: {
               rank_id: '',
            }
         },
         // form: new useForm({
         //     date: new Date().toISOString().slice(0, 10),
         // })
      };
   },
   
   created() {
      this.fetchStudents()
      this.fetchClasses()
   },
   watch: {
      'filterParams.filter.classfilter': function (query) {
         this.fetchStudents();
         
      }
   },
   methods: {
      buildUrl(rawUrl, params) {
         const baseUrl = rawUrl.startsWith("http")
            ? rawUrl
            : `${window.location.origin}/${rawUrl}`;
         
         const url = new URL(baseUrl);
         const appendNestedParams = (prefix, obj) => {
            for (const [key, value] of Object.entries(obj)) {
               if (typeof value === 'object' && value !== null) {
                  appendNestedParams(`${prefix}[${key}]`, value);
               } else {
                  url.searchParams.append(`${prefix}[${key}]`, value);
               }
            }
         };
         for (const [key, value] of Object.entries(params)) {
            if (typeof value === 'object' && value !== null) {
               appendNestedParams(key, value);
            } else {
               url.searchParams.append(key, value);
            }
         }
         return url.toString();
      },
      fetchStudents() {
         let url = this.buildUrl('datatable/students', this.filterParams);
         console.log(url)
         axios.get(url)
            .then(({data}) => {
               // this.students = data.data;
               this.students = data.data.map((student) => ({
                  ...student, status: 'Present', remarks: ''
               }))
               this.attendanceRecords = this.students.reduce((acc, student) => ({
                  ...acc,
                  [student.id]: {status: 'Present', remarks: ''}
               }), {})
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred when fetching the students.')
         })
      },
      fetchClasses() {
         axios.get('/datatable/ranks', {
            params: {
               filter: {
                  'activated': true,
               }
            }
         }).then(({data}) => {
            this.classes = data.data;
         }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred when fetching the classes.')
         })
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
      
      submitAttendance() {
         if (this.filterParams.filter.classfilter && this.form.date) {
            const attendanceData = Object.entries(this.students).map((student) => ({
               teacher_id: 1,
               id: student[1].id,
               class_id: this.filterParams.filter.classfilter,
               date: this.form.date,
               status: student[1].status,
               remarks: student[1].remarks || null,
               
            }));
            axios.post('/attendance', attendanceData)
               .then(({data}) => {
                  this.$toast.success('Attendance added successfully');
                  this.$inertia.visit('/attendance-record')
                  
               })
               .catch((error) => {
                  let errorMessage = error.response?.data?.message || error.message || "An error occurred";
                  console.error(error);
                  this.$toast.error(errorMessage);
               });
         } else {
            this.$toast.error("Date and Class are required fields");
         }
         
         
      }
   },
}
</script>

<style scoped></style>
