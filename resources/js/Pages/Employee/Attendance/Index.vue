<template>
   <Head title="Record Attendance" />
   
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Attendance Form</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('employee.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Attendance
               </li>
            </ol>
         </nav>
         
         <div v-if="!teacher.is_class_teacher" class="col-10">
            <div class="alert alert-info">
               You are not a class teacher, you cannot mark attendance. Once a class is assigned to you, you can mark
               the student attendance of your class.
            </div>
         </div>
         
         <div v-else class="col-xxl-12">
            <div class="card">
               <div class="card-header flex-column flex-md-row">
                  <div class="row row-gap-1">
                     <div class="col-md-3 col-6">
<!--                        <label for="date" class="form-label-md mb-1">Date</label>-->
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
import functions from "@/Functions/Functions.js";

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
         filterParams: {
            filter: {
               date: null,
            }
         },
         form: useForm({
            teacher_id: this.teacher.id,
            rank_id: this.rank.id,
            date: null,
            attendances: {},
         }),
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
      'filterParams.filter.date': function (newValue) {
         this.students = []
         if(newValue) {
            this.form.date = newValue
            this.fetchStudents().then(() => {
               this.fetchExistingAttendance()
            })
         }
      }
   },
   mounted() {
      this.fetchStudents();
   },
   methods: {
      fetchStudents() {
         return axios.get('/employee/datatable/students', {
            params: {
               filter: {
                  rank_id: this.rank.id,
               }
            }
         })
         .then(({ data }) => {
            this.students = data.data;
            
            this.form.attendances = data.data.reduce((acc, student) => {
               acc[student.id] = {
                  student_id: student.id,
                  status: '',
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
         const rankId = this.rank.id
         const filterDate = this.filterParams.filter.date
         if( !rankId || !filterDate) return
         
         const url = functions.buildUrl(route('employee.attendance.fetch'), {
               rank_id: rankId,
               date: filterDate
         })
         axios.get(url)
            .then(({ data }) => {
               if (data.attendances.length > 0) {
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
                  
                  this.$toast.info('You are editing an existing attendance record!', 'Message');
               } else {
                  this.$toast.info('No attendance record found for the selected date!', 'Message');
                  this.form.attendances = this.students.reduce((acc, student) => {
                     acc[student.id] = {
                        student_id: student.id,
                        status: '',
                        remarks: ''
                     }
                     return acc
                  }, {})
               }
            })
            .catch(error => {
               console.error(error)
               this.$toast.error('Failed to fetch attendance data.', 'Error')
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
         
         this.form.post(route('employee.attendances.store'), {
            onSuccess: () => {
               this.form.rank_id = this.rank.id;
               this.form.date = this.filterParams.filter.date;
               this.fetchStudents().then(() => {
                  this.fetchExistingAttendance()
               })
               
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
