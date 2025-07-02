<template>
   <Head title="{{ rank.name }}"/>
   
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Class Details</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item">
                  <Link :href="route('admin.ranks.index')">Classes</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Details
               </li>
            </ol>
         </nav>
         
         <div class="col-xxl-12">
            <div class="card">
               <div class="card-widget-separator-wrapper">
                  <div class="card-body card-widget-separator border-bottom">
                     <div class="row gy-3 gy-sm-1">
                        <div class="col-sm-6 col-lg-2">
                           <div class="d-flex justify-content-between align-items-center card-widget-1 border-end pb-4 pb-sm-0">
                              <div>
                                 <h4 class="mb-0">{{ rank.name }}</h4>
                                 <p class="mb-0">Class</p>
                              </div>
                              <div class="avatar me-sm-6">
                                 <span class="avatar-initial rounded bg-label-secondary text-heading">
                                    <i class="icon-base bx bxs-chalkboard icon-26px"></i>
                                 </span>
                              </div>
                           </div>
                           <hr class="d-none d-sm-block d-lg-none me-6">
                        </div>
                        <div class="col-sm-6 col-lg-3">
                           <div class="d-flex justify-content-between align-items-center card-widget-2 border-end pb-4 pb-sm-0">
                              <div>
                                 <h4 class="mb-0">{{ rank.stream?.name }}</h4>
                                 <p class="mb-0">Stream</p>
                              </div>
                              <div class="avatar me-sm-6">
                                 <span class="avatar-initial rounded bg-label-secondary text-heading">
                                    <i class="icon-base bx bxs-category icon-26px"></i>
                                 </span>
                              </div>
                           </div>
                           <hr class="d-none d-sm-block d-lg-none me-6">
                        </div>
                        <div class="col-sm-6 col-lg-3">
                           <div class="d-flex justify-content-between align-items-center card-widget-3 border-end pb-4 pb-sm-0">
                              <div>
                                 <h4 class="mb-0">{{ rank.division?.name }}</h4>
                                 <p class="mb-0">Division</p>
                              </div>
                              <div class="avatar me-sm-6">
                                 <span class="avatar-initial rounded bg-label-secondary text-heading">
                                    <i class="icon-base bx bxs-institution icon-26px"></i>
                                 </span>
                              </div>
                           </div>
                           <hr class="d-none d-sm-block d-lg-none me-6">
                        </div>
                        <div class="col-sm-6 col-lg-4">
                           <div class="d-flex justify-content-between align-items-center pb-4 pb-sm-0">
                              <div>
                                 <h4 v-if="rank.teacher" class="mb-0">{{ rank.teacher?.first_name + ' ' + rank.teacher?.last_name }}</h4>
                                 <h4 v-else class="mb-0">-</h4>
                                 <p class="mb-0">Class Teacher</p>
                              </div>
                              <div class="avatar me-sm-6">
                                 <span class="avatar-initial rounded bg-label-secondary text-heading">
                                    <i class="icon-base bx bxs-user icon-26px"></i>
                                 </span>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="card-header flex-column flex-md-row">
                  <div class="row row-gap-1">
                     <div class="col-md-9 col-9 justify-content-center">
                        <h5 class="card-title mb-0">Students</h5>
                     </div>
                     <div class="col-md-3 col-3 ms-lg-auto">
                        <div class="flex-wrap text-end">
                           <input type="search" id="search" class="form-control bg-muted-lt rounded-2" placeholder="Search Students"
                                  @input="applyFilter" v-model="appendParams.filter.admission_number" >
                        </div>
                     </div>
                  </div>
               </div>
               <VueTable
                  api-url="datatable/students"
                  :fields="fields"
                  ref="studentsTable"
                  :append-params="appendParams"
               >
                  <template v-slot:student="props">
                     {{ props.rowData.first_name }} {{ props.rowData.last_name }}
                  </template>
                  <template v-slot:actions="props">
                     <div class="dropdown">
                        <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                           <i class="icon-base bx bx-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                           <a class="dropdown-item" href="#">
                              <i class="icon-base bx bx-detail me-2"></i> Details
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
import _debounce from "lodash/debounce.js";
import axios from "axios";
import {Inertia} from "@inertiajs/inertia";
import {Modal} from "bootstrap";
import {Link, useForm} from "@inertiajs/vue3";
import DefaultLayout from "@layouts/DefaultLayout.vue";

export default {
   components: {DefaultLayout, Link},
   props: {
      rank: {
         required: true,
         type: Object,
      }
   },
   data() {
      return {
         fields: [
            {
               name: '__slot:student',
               title: 'STUDENT',
               width: 'auto',
            },
            {
               name: 'admission_number',
               title: 'ADMISSION NUMBER',
               width: '20%',
            },
            {
               name: 'gender.name',
               title: 'GENDER',
               width: 'auto',
            },
            {
               name: 'religion.name',
               title: 'RELIGION',
               width: 'auto',
            },
            {
               name: '__slot:actions',
               title: 'ACTIONS',
               titleClass: 'text-end',
               dataClass: 'text-end',
               width: '10%',
            },
         ],
         appendParams: {
            filter: {
               rank_id: this.rank.id,
               admission_number: '',
            }
         },
         form: useForm({
            rank_id: this.rank.id,
            subject_id: null,
            teacher_id: null,
         }),
         editForm: useForm({
            id: null,
            rank_id: this.rank.id,
            subject_id: null,
            teacher_id: null,
         }),
         subjects: [],
         teachers: [],
         lessons: [],
      }
   },
   created() {
      Inertia.on('navigate', (event) => {
         if (event.detail.page.url === '/admin/ranks/' + this.rank.id) {
            this.fetchLessons();
            this.fetchSubjects();
            this.fetchTeachers();
         }
      });
   },
   mounted() {
      this.fetchLessons();
      this.fetchSubjects();
      this.fetchTeachers();
   },
   methods: {
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
            this.$toast.error('An error occurred when fetching the class\'s subjects.')
         })
      },
      fetchLessons() {
         axios.get('/datatable/lessons', {
            params: {
               filter: {
                  rank_id: this.rank.id
               }
            }
         })
            .then(({ data }) => {
               this.lessons = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred when fetching the class\'s lessons.')
         })
      },
      fetchTeachers() {
         axios.get('/datatable/teachers')
         .then(({ data }) => {
            this.teachers = data.data;
         }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred when fetching the class\'s subjects.')
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
      // updateLesson() {
      //    this.editForm.patch(`/admin/lessons/` + this.editForm.id, {
      //       onSuccess: () => {
      //          this.editForm.reset();
      //          this.editForm.clearErrors();
      //          const modalElement = this.$refs.editLesson;
      //          const modalInstance = Modal.getInstance(modalElement);
      //          modalInstance.hide();
      //          this.fetchLessons();
      //          this.$toast.success('Class Subject Updated Successfully', 'Success')
      //       },
      //       onError: (errors) => {
      //          console.log(errors)
      //          this.$toast.error('An error occurred. Please try again', 'Error')
      //       },
      //    })
      // },
      applyFilter: _debounce(function () {
         this.$refs.studentsTable.reloadTable()
      }, 800),
      formCleanUp() {
         this.form.reset()
      },
      editFormCleanUp() {
         this.editForm.reset()
      },
   },
}
</script>

<style scoped>
.icon-26px,
.icon-26px::before {
   block-size:26px !important;
   font-size:26px !important;
   inline-size:26px !important
}
@media(max-width: 992px) {
   .card .card-widget-separator-wrapper .card-widget-separator .card-widget-2.border-end {
      border-inline-end:none !important;
      border-inline-start:none !important
   }
}
@media(max-width: 576px) {
   .card .card-widget-separator-wrapper .card-widget-separator .card-widget-1.border-end,
   .card .card-widget-separator-wrapper .card-widget-separator .card-widget-2.border-end,
   .card .card-widget-separator-wrapper .card-widget-separator .card-widget-3.border-end {
      border-block-end:1px solid var(--bs-card-border-color);
      border-inline-end:none !important;
      border-inline-start:none !important
   }
}
</style>
