<template>
   <div class="row">
      <h3 class="mb-0">Class Details</h3>
      <nav class="mb-3">
         <ol class="breadcrumb">
            <li class="breadcrumb-item">
               <Link href="/admin/dashboard">Home</Link>
            </li>
            <li class="breadcrumb-item">
               <Link href="/admin/ranks">Classes</Link>
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
                     <div class="col-sm-6 col-lg-3">
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
                     <div class="col-sm-6 col-lg-3">
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
            <div class="card-header d-flex justify-content-between">
               <div class="card-title mb-0">
                  <h5 class="mb-1 me-2">Subjects</h5>
                  <p class="card-subtitle">List of subjects taught</p>
               </div>
               <div>
                  <button class="btn btn-primary d-none d-sm-inline-block" @click.prevent="createRankSubjectModal">
                     <i class="bx bx-plus-circle me-2"></i>
                     Add Subject
                  </button>
                  <button type="button" class="btn btn-primary btn-icon d-sm-none" @click.prevent="createRankSubjectModal">
                     <i class="bx bx-plus"></i>
                  </button>
               </div>
            </div>
            <div class="card-body border-bottom py-5">
               <div class="row">
                  <div v-for="(rankSubject, index) in rankSubjects" :key="index" class="col-md-3 col-6">
                     <div class="d-flex align-items-center p-3 bg-secondary-subtle rounded-2">
                        <div class="d-flex align-items-center">
                           <div>
                              <p class="fw-medium mb-0">{{ rankSubject.subject.name }}</p>
                              <small v-if="rankSubject.teacher_id" class="text-primary">{{ rankSubject.teacher?.honorific?.name + ' ' + rankSubject.teacher?.first_name + ' ' + rankSubject.teacher?.last_name }}</small>
                              <small v-else>-</small>
                           </div>
                        </div>
                        <div class="ms-auto">
                           <div class="dropdown">
                              <button type="button" class="btn align-text-top py-1" data-bs-toggle="dropdown">
                                 <i class="bx bx-dots-vertical-rounded"></i>
                              </button>
                              <div class="dropdown-menu dropdown-menu-end">
                                 <a class="dropdown-item" href="#" @click.prevent="editRankSubject(rankSubject)">
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
                        <i class="bx bx-dots-vertical"></i>
                     </button>
                     <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="#">
                           <i class="bx bx-detail me-2"></i> Details
                        </a>
                     </div>
                  </div>
               </template>
            </VueTable>
         </div>
      </div>
      
      <!-- Create Modal -->
      <div
         class="modal fade"
         id="create-rank-subject-modal"
         data-bs-backdrop="static"
         tabindex="-1"
         aria-labelledby="create-rank-subject"
         aria-hidden="true"
         ref="createRankSubject"
      >
         <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="create-rank-modal-label">Add Subject</h5>
                  <button
                     type="button"
                     class="btn-close"
                     data-bs-dismiss="modal"
                     aria-label="Close"
                     @click="formCleanUp"
                  ></button>
               </div>
               <div class="modal-body">
                  <form id="createForm" @submit.prevent="storeRankSubject">
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
                     @click.prevent="storeRankSubject"
                  >
                     Submit
                  </button>
               </div>
            </div>
         </div>
      </div>
      
      <div
         class="modal fade"
         id="edit-rank-subject-modal"
         data-bs-backdrop="static"
         tabindex="-1"
         aria-labelledby="edit-rank-subject"
         aria-hidden="true"
         ref="editRankSubject"
      >
         <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="create-rank-modal-label">Edit Subject</h5>
                  <button
                     type="button"
                     class="btn-close"
                     data-bs-dismiss="modal"
                     aria-label="Close"
                     @click="editFormCleanUp"
                  ></button>
               </div>
               <div class="modal-body">
                  <form id="createForm" @submit.prevent="updateRankSubject">
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
                     @click.prevent="updateRankSubject"
                  >
                     Submit
                  </button>
               </div>
            </div>
         </div>
      </div>
   </div>
</template>

<script>
import _debounce from "lodash/debounce.js";
import axios from "axios";
import {Inertia} from "@inertiajs/inertia";
import {Modal} from "bootstrap";
import {useForm} from "@inertiajs/vue3";

export default {
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
         rankSubjects: [],
      }
   },
   created() {
      Inertia.on('navigate', (event) => {
         if (event.detail.page.url === '/admin/ranks/' + this.rank.id) {
            this.fetchRankSubjects();
            this.fetchSubjects();
            this.fetchTeachers();
         }
      });
   },
   mounted() {
      this.fetchRankSubjects();
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
      fetchRankSubjects() {
         axios.get('/datatable/rank-subjects', {
            params: {
               filter: {
                  rank_id: this.rank.id
               }
            }
         })
            .then(({ data }) => {
               this.rankSubjects = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred when fetching the class\'s subjects.')
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
      createRankSubjectModal() {
         const modalElement = this.$refs.createRankSubject;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      storeRankSubject() {
         this.form.post('/admin/rank_subjects', {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               const modalElement = this.$refs.createRankSubject;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.fetchRankSubjects();
               this.$toast.success('Class Subject Added Successfully', 'Success')
            },
            onError: (errors) => {
               console.log(errors)
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      editRankSubject(rowData) {
         this.editForm.id = rowData.hashid;
         this.editForm.rank_id = rowData.rank_id;
         this.editForm.subject_id = rowData.subject_id;
         this.editForm.teacher_id = rowData.teacher_id;
         const modalElement = this.$refs.editRankSubject;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      updateRankSubject() {
         this.editForm.patch('/admin/rank_subjects/' + this.editForm.id, {
            onSuccess: () => {
               this.editForm.reset();
               this.editForm.clearErrors();
               const modalElement = this.$refs.editRankSubject;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.fetchRankSubjects();
               this.$toast.success('Class Subject Updated Successfully', 'Success')
            },
            onError: (errors) => {
               console.log(errors)
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      // updateRankSubject() {
      //    this.editForm.patch(`/admin/rank_subjects/` + this.editForm.id, {
      //       onSuccess: () => {
      //          this.editForm.reset();
      //          this.editForm.clearErrors();
      //          const modalElement = this.$refs.editRankSubject;
      //          const modalInstance = Modal.getInstance(modalElement);
      //          modalInstance.hide();
      //          this.fetchRankSubjects();
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
