<template>
   <Head title="Record Attendance" />
   
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Attendance Form</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Attendance
               </li>
            </ol>
         </nav>
         
         <div class="col-xxl-12">
            <div class="card">
               <div class="card-header flex-column flex-md-row">
                  <div class="row row-gap-1">
                     <div class="col-md-3 col-6">
                        <label for="classId" class="form-label-md mb-1">Class</label>
                        <v-select
                           id="classId"
                           v-model="filterParams.filter.rank_id"
                           :options="ranks"
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
                        <label for="date" class="form-label-md mb-1">Date</label>
                        <date-picker
                           id="date"
                           form-class="shadow-sm"
                           :value="filterParams.filter.date"
                           :max-date="new Date()"
                           @on-change="function(dateObj, dateStr) {
                             filterParams.filter.date = dateStr
                             form.date = dateStr
                           }"
                        ></date-picker>
                     </div>
                  </div>
               </div>
               <div class="table-responsive text-nowrap">
                  <table class="table">
                     <thead style="background-color: rgb(34, 48, 62, 0.06);">
                     <tr class="text-nowrap">
                        <th style="width:15%;">Adm no</th>
                        <th style="width:25%;">Student Name</th>
                        <th style="width:25%;">Status</th>
                        <th style="width:35%;">Remarks</th>
                     </tr>
                     </thead>
                     <tbody class="table-border-bottom-0">
                     <tr v-for="student in students" :key="student.id" :class="getStatusColor(form.attendances[student.id].status)">
                        <td style="width:15%;">
                           {{ student.admission_number }}
                        </td>
                        <td style="width:25%;">
                           {{ student.first_name }} {{ student.last_name }}
                        </td>
                        <td style="width:25%;">
                           <v-select
                              v-model="form.attendances[student.id].status"
                              :options="statuses"
                              variant="outlined"
                              :class="getStatusColor(form.attendances[student.id].status)"
                              label="name"
                              :reduce="option => option.value"
                              density="comfortable"
                           >
                           </v-select>
                        </td>
                        <td style="width:35%;">
                           <input v-model="form.attendances[student.id].remarks"
                                  class="form-control"
                                  placeholder="Remarks (optional)"
                           />
                        </td>
                     </tr>
                     </tbody>
                  </table>
               </div>
               <div class="justify-content-end d-flex p-3">
                  <button class="btn btn-primary"
                         @click.prevent="submitAttendance"
                         :disabled="form.processing || !students.length || !form.date || !form.rank_id">
                     Submit
                  </button>
               </div>
            </div>
         </div>
      </div>
   </DefaultLayout>
   
</template>

<script>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import {Head, Link, useForm} from "@inertiajs/vue3";
import axios from "axios";

export default {
   components: {DefaultLayout, Head, Link},
   data() {
      return {
         filterParams: {
            filter: {
               rank_id: null,
               date: null,
            }
         },
         form: useForm({
            rank_id: null,
            date: null,
            attendances: {},
         }),
         ranks: [],
         students: [],
         statuses: [
            {value: 'Present', name: 'Present'},
            {value: 'Absent', name: 'Absent'},
            {value: 'Late', name: 'Late'},
            {value: 'Excused', name: 'Excused'},
         ],
      }
   },
   watch: {
      'filterParams.filter.rank_id': function (newValue) {
         this.form.rank_id = newValue
         this.students = [];
         if(newValue) {
            this.fetchStudents(newValue)
            this.fetchExistingAttendance()
         }
      },
      'filterParams.filter.date': function (newValue) {
         if(newValue) {
            this.form.date = newValue
            this.fetchExistingAttendance()
         }
      }
   },
   mounted() {
      this.fetchRanks();
   },
   methods: {
      fetchRanks() {
         axios.get('/datatable/ranks', {
            params: {
               filter: {
                  activated: true,
               }
            }
         })
         .then(({ data }) => {
            this.ranks = data.data;
         }).catch((error) => {
         console.error(error)
         this.$toast.error('An error occurred when fetching the classes.')
         })
      },
      fetchStudents(rankId) {
         axios.get('/datatable/students', {
            params: {
               filter: {
                  rank_id: rankId,
               }
            }
         })
         .then(({ data }) => {
            this.students = data.data;

            this.form.attendances = data.data.reduce((acc, student) => {
               acc[student.id] = {
                  student_id: student.id,
                  status: 'Present',
                  remarks: ''
               }
               return acc
            }, {})
         }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred when fetching the students.')
         })
      },
      fetchExistingAttendance() {
         const { rank_id, date } = this.filterParams.filter
         if (!rank_id || !date) return
         
         axios.get('/admin/attendance/fetch', {
            params: { rank_id, date }
         }).then(({ data }) => {
            const existing = data.attendances.reduce((acc, a) => {
               acc[a.student_id] = {
                  student_id: a.student_id,
                  status: a.status,
                  remarks: a.remarks
               }
               return acc
            }, {})
            
            Object.entries(existing).forEach(([id, values]) => {
               if (this.form.attendances[id]) {
                  this.form.attendances[id] = values
               }
            })
         })
      },
      getStatusColor(status) {
         const colors = {
            'Present': 'table-info',
            'Absent': 'table-danger',
            'Late': 'table-warning',
            'Excused': 'table-secondary'
         }
         return colors[status] || 'table-default'
      },
      submitAttendance() {
         if (!this.form.rank_id) {
            this.$toast.info('Select a class first', 'Info');
            return;
         }
         if (!this.form.date) {
            this.$toast.info('Select attendance date', 'Info');
            return;
         }
         
         this.form.post(route('admin.attendances.store'), {
            onSuccess: () => {
               this.form.rank_id = null;
               this.form.date = null;
               this.form.attendances = {};
               this.filterParams.filter.rank_id = '';
               this.filterParams.filter.date = '';
               
               this.$toast.success('Attendance recorded!', 'Success');
            },
            onError: (error) => {
               console.log(error);
               this.$toast.error('Something went wrong!', 'Error');
            },
         });
      }
   },
}
</script>
