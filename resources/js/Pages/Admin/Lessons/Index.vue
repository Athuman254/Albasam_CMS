<template>
   <Head title="Lessons"/>
   
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Lessons</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Lessons
               </li>
            </ol>
         </nav>
         
         <div class="col-xxl-12">
            <div class="card">
               <div class="card-header d-flex justify-content-between">
                  <div class="card-title mb-0">
                     <h5 class="mb-1 me-2">Lessons</h5>
                     <p class="card-subtitle">List of lessons taught</p>
                  </div>
                  <div>
                     <button class="btn btn-primary d-none d-sm-inline-block" @click.prevent="createLessonModal">
                        <i class="bx bx-plus-circle me-2"></i>
                        Add Lesson
                     </button>
                     <button type="button" class="btn btn-primary btn-icon d-sm-none" @click.prevent="createLessonModal">
                        <i class="bx bx-plus"></i>
                     </button>
                  </div>
               </div>
               <div class="card-body border-bottom py-5">
                  <div class="row">
                     <div class="col-12">
                        <div class="mb-3">
                           <div class="row">
                              <div class="col-4">
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
                              <div class="col-2">
                                 <div class="mb-3">
                                    <button type="button" class="btn btn-sm btn-outline-primary" @click.prevent="fetchLessons()">
                                       <i class="bx bx-filter-alt"></i>
                                    </button>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div v-for="(lesson, index) in lessons" :key="index" class="col-md-3 col-6">
                        <div class="d-flex align-items-center p-3 bg-secondary-subtle rounded-2">
                           <div class="d-flex align-items-center">
                              <div>
                                 <p class="fw-medium mb-0">{{ lesson.subject?.name }}</p>
                                 <small v-if="lesson.teacher_id" class="text-primary">{{ lesson.teacher?.honorific?.name + ' ' + lesson.teacher?.first_name + ' ' + lesson.teacher?.last_name }}</small>
                                 <small v-else>-</small>
                              </div>
                           </div>
                           <div class="ms-auto">
                              <div class="dropdown">
                                 <button type="button" class="btn align-text-top py-1" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                 </button>
                                 <div class="dropdown-menu dropdown-menu-end">
                                    <a class="dropdown-item" href="#" @click.prevent="editLesson(lesson)">
                                       <i class="bx bx-edit-alt me-2"></i>
                                       Edit
                                    </a>
                                    <a class="dropdown-item text-danger" href="#">
                                       <i class="bx bx-trash me-2"></i>
                                       Delete
                                    </a>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
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
import _debounce from "lodash/debounce.js";

export default {
   components: {DefaultLayout, Head, Link},
   data() {
      return {
         form: useForm({
            rank_id: null,
            subject_id: null,
            teacher_id: null,
         }),
         editForm: useForm({
            id: null,
            rank_id: null,
            subject_id: null,
            teacher_id: null,
         }),
         selectedRankId: null,
         rankSelected: false,
         ranks: [],
         subjects: [],
         teachers: [],
         lessons: [],
      }
   },
   created() {
      Inertia.on('navigate', (event) => {
         if (event.detail.page.url === '/admin/lessons/') {
            this.fetchRanks();
            this.fetchSubjects();
            this.fetchTeachers();
            this.fetchLessons();
         }
      });
   },
   mounted() {
      this.fetchRanks();
      this.fetchSubjects();
      this.fetchTeachers();
      this.fetchLessons();
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
               if(!this.lessons.length){
                  this.$toast.info('No lessons found for the selected class');
                  return;
               }
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred when fetching the class\'s lessons.')
         })
      },
      createLessonModal() {
         const modalElement = this.$refs.createLesson;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      storeLesson() {
         this.form.post('/admin/lessons', {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               const modalElement = this.$refs.createLesson;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.fetchLessons();
               this.$toast.success('Class Subject Added Successfully', 'Success')
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
               this.$toast.success('Class Subject Updated Successfully', 'Success')
            },
            onError: (errors) => {
               console.log(errors)
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      formCleanUp() {
         this.form.reset()
      },
      editFormCleanUp() {
         this.editForm.reset()
      },
   },
}
</script>
