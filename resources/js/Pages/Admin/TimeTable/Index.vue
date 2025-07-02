<template>
   <Head title="Time-table"/>
   
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Time-Table</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Time-Table
               </li>
            </ol>
         </nav>
         
         <div class="col-xxl-12">
            <div class="card">
               <div class="card-body border-bottom">
                  <div class="row">
                     <div class="col-12">
                        <div class="mb-5">
                           <div class="row">
                              <div class="col-md-4 col-7">
                                 <v-select
                                    id="selectedRank"
                                    v-model="selectedRankId"
                                    :options="ranks"
                                    label="name"
                                    placeholder="Select a class"
                                    :reduce="option => option.id"
                                 >
                                    <template #option="option">
                                       {{ option.name }} {{ option.stream?.name }}
                                    </template>
                                    <template #selected-option="option">
                                       {{ option.name }} {{ option.stream?.name }}
                                    </template>
                                 </v-select>
                              </div>
                              <div class="col-md-2 col-2">
                                 <button type="button" class="btn btn-sm btn-outline-primary" @click.prevent="fetchLessons()">
                                    <i class="icon-base bx bx-filter-alt"></i>
                                 </button>
                              </div>
                              <div class="col-md-auto col-auto ms-auto">
                                 <div v-if="rankSelected">
                                    <button class="btn btn-primary d-none d-sm-inline-block" @click.prevent="createLessonModal">
                                       <i class="icon-base bx bx-plus-circle me-2"></i>
                                       Add Lesson
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm d-sm-none" @click.prevent="createLessonModal">
                                       <i class="icon-base bx bx-plus"></i>
                                    </button>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div v-if="lessons.length" class="col-12 d-none d-sm-inline-block">
                        <div class="mb-3">
                           <div class="card-title mb-0">
                              <h5 class="mb-1 me-2">Lessons</h5>
                              <p class="card-subtitle">List of lessons taught</p>
                           </div>
                        </div>
                     </div>
                     <div v-for="(lesson, index) in lessons" :key="index" class="col-md-3 d-none d-sm-inline-block">
                        <div class="d-flex align-items-start p-3 mb-3 bg-lighter rounded-2">
                           <div class="d-flex align-items-center">
                              <div class="me-2">
                                 <h5 class="mb-0">{{ lesson.subject?.name }}</h5>
                                 <small v-if="lesson.teacher_id" class="text-primary">{{ lesson.teacher?.honorific?.name + ' ' + lesson.teacher?.last_name }}</small>
                                 <small v-else class="text-body">-</small>
                              </div>
                           </div>
                           <div class="ms-auto">
                              <div class="dropdown z-2">
                                 <button type="button" class="btn btn-icon btn-text-secondary rounded-pill align-text-top dropdown-toggle p-0 hide-arrow" data-bs-toggle="dropdown">
                                    <i class="icon-base bx bx-dots-vertical-rounded"></i>
                                 </button>
                                 <div class="dropdown-menu dropdown-menu-end py-0">
                                    <a class="dropdown-item" href="#" @click.prevent="editLesson(lesson)">
                                       <i class="icon-base bx bx-edit-alt me-2"></i>
                                       Edit
                                    </a>
                                    <a class="dropdown-item text-danger" href="#">
                                       <i class="icon-base bx bx-trash me-2"></i>
                                       Delete
                                    </a>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div v-if="lessons.length" class="col-12 mt-5">
                        <div class="mb-3">
                           <div class="table-responsive text-nowrap">
                              <table class="table table-bordered">
                                 <thead>
                                 <tr>
                                    <td colspan="8">
                                       <div class="btn btn-light float-end">
                                          <i class="icon-base bx bx-download me-2"></i>
                                          Download
                                       </div>
                                    </td>
                                 </tr>
                                 </thead>
                                 <thead class="bg-lighter">
                                 <tr>
                                    <th class="text-center" style="width:10%;">Time</th>
                                    <th v-for="(weekday, index) in weekdays" :key="index" class="text-center" style="width:18%">{{ weekday.name }}</th>
                                 </tr>
                                 </thead>
                                 <tbody>
                                 <tr v-for="(days, time) in calendarData">
                                    <td>{{ time }}</td>
                                    <template v-for="day in days">
                                       <td v-if="day" :rowspan="day.rowspan" class="align-middle text-center" :style="{ backgroundColor: day.subject_name ? '#f0f0f0' : '' }">
                                          {{ day.subject_name }} <br>
                                          {{ day.teacher_name }}
                                       </td>
                                    </template>
                                 </tr>
                                 </tbody>
                              </table>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      
      <!-- Create Modal -->
      <div
         class="modal fade"
         id="create-lesson-modal"
         data-bs-backdrop="static"
         tabindex="-1"
         aria-labelledby="create-lesson"
         aria-hidden="true"
         ref="createLesson"
      >
         <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="create-lesson-modal-label">Add Lesson</h5>
                  <button
                     type="button"
                     class="btn-close"
                     data-bs-dismiss="modal"
                     aria-label="Close"
                     @click="formCleanUp"
                  ></button>
               </div>
               <div class="modal-body">
                  <form id="createForm" @submit.prevent="storeLesson">
                     <div class="mb-3">
                        <label for="subjectId" class="form-label">Subject</label>
                        <v-select
                           id="subjectId"
                           v-model="form.subject_id"
                           :options="subjects"
                           label="name"
                           :reduce="option => option.id"
                        ></v-select>
                        <div v-if="form.errors.subject_id" class="text-danger">{{ form.errors.subject_id }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="teacherId" class="form-label">Teacher</label>
                        <v-select
                           id="teacherId"
                           v-model="form.teacher_id"
                           :options="teachers"
                           label="name"
                           :reduce="option => option.id"
                        >
                           <template #option="option">
                              {{ option.first_name }} {{ option.last_name }}
                           </template>
                           <template #selected-option="option">
                              {{ option.first_name }} {{ option.last_name }}
                           </template>
                        </v-select>
                        <div v-if="form.errors.teacher_id" class="text-danger">{{ form.errors.teacher_id }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="weekDay" class="form-label">WeekDay</label>
                        <v-select
                           id="weekDay"
                           v-model="form.weekday"
                           :options="weekdays"
                           label="name"
                           :reduce="option => option.id"
                        ></v-select>
                        <div v-if="form.errors.weekday" class="text-danger">{{ form.errors.weekday }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="startTime" class="form-label">Start Time</label>
                        <time-picker
                           id="startTime"
                           form-class="shadow-sm"
                           :value="form.start_time"
                           @on-change="handleStartTimeChange"
                        ></time-picker>
                        <div v-if="form.errors.start_time" class="text-danger">{{ form.errors.start_time }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="endTime" class="form-label">End Time</label>
                        <time-picker
                           id="endTime"
                           form-class="shadow-sm"
                           :value="form.end_time"
                           @on-change="function(timeObj, timeStr) {
                             form.end_time = timeStr
                           }"
                        ></time-picker>
                        <div v-if="form.errors.end_time" class="text-danger">{{ form.errors.end_time }}</div>
                     </div>
                  </form>
               </div>
               <div class="modal-footer">
                  <button
                     type="button"
                     class="btn btn-secondary me-2"
                     data-bs-dismiss="modal"
                     @click="formCleanUp"
                  >
                     Close
                  </button>
                  <button
                     type="button"
                     class="btn btn-primary"
                     @click.prevent="storeLesson"
                  >
                     Submit
                  </button>
               </div>
            </div>
         </div>
      </div>
      
      <!-- Edit Modal -->
      <div
         class="modal fade"
         id="edit-Lesson-modal"
         data-bs-backdrop="static"
         tabindex="-1"
         aria-labelledby="edit-Lesson"
         aria-hidden="true"
         ref="editLesson"
      >
         <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="create-lesson-modal-label">Edit Lesson</h5>
                  <button
                     type="button"
                     class="btn-close"
                     data-bs-dismiss="modal"
                     aria-label="Close"
                     @click="editFormCleanUp"
                  ></button>
               </div>
               <div class="modal-body">
                  <form id="createForm" @submit.prevent="updateLesson">
                     <div class="mb-3">
                        <label for="subjectId" class="form-label">Subject</label>
                        <v-select
                           id="subjectId"
                           v-model="editForm.subject_id"
                           :options="subjects"
                           label="name"
                           :reduce="option => option.id"
                        ></v-select>
                        <div v-if="editForm.errors.subject_id" class="text-danger">{{ editForm.errors.subject_id }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="teacherId" class="form-label">Teacher</label>
                        <v-select
                           id="teacherId"
                           v-model="editForm.teacher_id"
                           :options="teachers"
                           label="name"
                           :reduce="option => option.id"
                        >
                           <template #option="option">
                              {{ option.first_name }} {{ option.last_name }}
                           </template>
                           <template #selected-option="option">
                              {{ option.first_name }} {{ option.last_name }}
                           </template>
                        </v-select>
                        <div v-if="editForm.errors.teacher_id" class="text-danger">{{ editForm.errors.teacher_id }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="weekDay" class="form-label">WeekDay</label>
                        <v-select
                           id="weekDay"
                           v-model="editForm.weekday"
                           :options="weekdays"
                           label="name"
                           :reduce="option => option.id"
                        ></v-select>
                        <div v-if="editForm.errors.weekday" class="text-danger">{{ editForm.errors.weekday }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="startTime" class="form-label">Start Time</label>
                        <time-picker
                           id="startTime"
                           form-class="shadow-sm"
                           :value="editForm.start_time"
                           @on-change="function(timeObj, timeStr) {
                             editForm.start_time = timeStr
                           }"
                        ></time-picker>
                        <div v-if="editForm.errors.start_time" class="text-danger">{{ editForm.errors.start_time }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="endTime" class="form-label">End Time</label>
                        <time-picker
                           id="endTime"
                           form-class="shadow-sm"
                           :value="editForm.end_time"
                           @on-change="function(timeObj, timeStr) {
                             editForm.end_time = timeStr
                           }"
                        ></time-picker>
                        <div v-if="editForm.errors.end_time" class="text-danger">{{ editForm.errors.end_time }}</div>
                     </div>
                  </form>
               </div>
               <div class="modal-footer pb-10 px-10">
                  <button
                     type="button"
                     class="btn btn-secondary me-2"
                     data-bs-dismiss="modal"
                     @click="editFormCleanUp"
                  >
                     Close
                  </button>
                  <button
                     type="button"
                     class="btn btn-primary"
                     @click.prevent="updateLesson"
                  >
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
import {Inertia} from "@inertiajs/inertia";
import {Modal} from "bootstrap";
import TimePicker from "@components/global/_baseTimePicker.vue";
import _debounce from "lodash/debounce.js";

export default {
   components: {DefaultLayout, Head, Link, TimePicker},
   data() {
      return {
         form: useForm({
            rank_id: null,
            subject_id: null,
            teacher_id: null,
            weekday: null,
            start_time: null,
            end_time: null,
         }),
         editForm: useForm({
            id: null,
            rank_id: null,
            subject_id: null,
            teacher_id: null,
            weekday: null,
            start_time: null,
            end_time: null,
         }),
         selectedRankId: null,
         rankSelected: false,
         ranks: [],
         subjects: [],
         teachers: [],
         lessons: [],
         weekdays: [
            { id: 1, name: 'Monday', },
            { id: 2, name: 'Tuesday', },
            { id: 3, name: 'Wednesday', },
            { id: 4, name: 'Thursday', },
            { id: 5, name: 'Friday', },
            // { id: 6, name: 'Saturday', },
            // { id: 7, name: 'Sunday', },
         ],
         calendarData: [],
      }
   },
   created() {
      Inertia.on('navigate', (event) => {
         if (event.detail.page.url === '/admin/lessons/') {
            this.fetchRanks();
            this.fetchSubjects();
            this.fetchTeachers();
         }
      });
   },
   mounted() {
      this.fetchRanks();
      this.fetchSubjects();
      this.fetchTeachers();
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
      fetchSubjects() {
         axios.get('/datatable/subjects', {
            params: {
               filter: {
                  activated: true,
               }
            }
         })
            .then(({ data }) => {
               this.subjects = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred when fetching the subjects.')
         })
      },
      fetchTeachers() {
         axios.get('/datatable/teachers')
            .then(({ data }) => {
               this.teachers = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred when fetching the teachers')
         })
      },
      fetchLessons() {
         if(this.selectedRankId === null) {
            this.rankSelected = false
            this.lessons = []
            this.$toast.info('Select a class first', 'Info')
            return;
         }
         axios.get('/datatable/lessons', {
            params: {
               filter: {
                  rank_id: this.selectedRankId,
               }
            }
         })
         .then(({ data }) => {
            this.lessons = data.data;
            this.rankSelected = true;
            this.fetchTimeTableData();
            if(!this.lessons.length){
               this.$toast.info('No lessons found for the selected class');
               return;
            }
         }).catch((error) => {
            this.rankSelected = false;
            console.error(error)
            this.$toast.error('An error occurred when fetching the class\'s lessons.')
         })
      },
      fetchTimeTableData() {
         axios.get('/datatable/time-table', {
            params: {
               rank_id: this.selectedRankId,
            }
         })
         .then(({ data }) => {
            console.log(data);
            this.calendarData = data;
         }).catch((error) => {
            this.calendarData = [];
            console.error(error)
            this.$toast.error('An error occurred when fetching the time-table')
         })
      },
      createLessonModal() {
         const modalElement = this.$refs.createLesson;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      storeLesson() {
         this.form.rank_id = this.selectedRankId
         this.form.post('/admin/lessons', {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               const modalElement = this.$refs.createLesson;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.fetchLessons();
               this.$toast.success('Lesson Created Successfully', 'Success')
            },
            onError: (errors) => {
               console.log(errors)
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      editLesson(rowData) {
         this.editForm.id = rowData.hashid;
         this.editForm.rank_id = rowData.rank_id;
         this.editForm.subject_id = rowData.subject_id;
         this.editForm.teacher_id = rowData.teacher_id;
         this.editForm.weekday = rowData.weekday;
         this.editForm.start_time = rowData.start_time;
         this.editForm.end_time = rowData.end_time;
         const modalElement = this.$refs.editLesson;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      updateLesson() {
         this.editForm.patch('/admin/lessons/' + this.editForm.id, {
            onSuccess: () => {
               this.editForm.reset();
               this.editForm.clearErrors();
               const modalElement = this.$refs.editLesson;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.fetchLessons();
               this.$toast.success('Lesson Updated Successfully', 'Success')
            },
            onError: (errors) => {
               console.log(errors)
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      handleStartTimeChange(timeObj, timeStr) {
         console.log('Selected Time:', timeStr);
         this.form.start_time = timeStr;
      },
      formCleanUp() {
         this.form.reset()
         this.form.clearErrors();
      },
      editFormCleanUp() {
         this.editForm.reset()
         this.editForm.clearErrors();
      },
   },
}
</script>
