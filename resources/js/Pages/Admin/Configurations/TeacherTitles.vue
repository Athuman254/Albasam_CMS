<template>
   <div class="card">
      <div class="card-header flex-column flex-md-row">
         <div class="row row-gap-1">
            <div class="col-md-3 col-9">
               <input type="search" id="search" class="form-control bg-muted-lt rounded-2" placeholder="Search..."
                      @input="applyFilter" v-model="appendParams.filter.title">
            </div>
            <div class="col-md-6 col-3 ms-lg-auto">
               <div class="flex-wrap text-end">
                  <div class="card-action">
                     <button type="button" class="btn btn-primary d-none d-sm-inline-block"
                             @click="showCreateTeacherTitleModal">
                        <i class="bx bx-plus-circle me-2"></i>
                        Add Teacher Title
                     </button>
                     
                     <button type="button" class="btn btn-primary btn-icon d-sm-none"
                             @click="showCreateTeacherTitleModal">
                        <i class="bx bx-plus"></i>
                     </button>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <VueTable
         :fields="fields"
         api-url="datatable/teacher-titles"
         :append-params="appendParams"
         ref="teacherTitlesTable"
      >
         <template #scale="props">
            <div>
               <span>{{ props.rowData.scale.name }}</span>
               <span class="me-2">Grade:</span><span class="text-primary">{{ props.rowData.grade.name }}</span>
            </div>
         </template>
         <template #status="props">
            <span v-if="props.rowData.activated" class="badge bg-success">
               Active
            </span>
            <span v-else-if="!props.rowData.activated" class="badge bg-danger">
               Deactivated
            </span>
            <span v-else class="badge bg-secondary">
               Unknown
            </span>
         </template>
         <template #actions="props">
            <div class="dropdown">
               <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                  <i class="bx bx-dots-vertical"></i>
               </button>
               <div class="dropdown-menu dropdown-menu-end">
                  <a class="dropdown-item" href="#" @click="editTeacherTitle(props.rowData)">
                     <i class="bx bx-edit-alt me-2"></i>Edit
                  </a>
               </div>
            </div>
         </template>
      </VueTable>
   </div>
   
   <!-- Create Modal -->
   <div
      class="modal fade"
      id="create-teacher-title-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="create-teacher-title-modal-label"
      aria-hidden="true"
      ref="createTeacherTitleModal"
   >
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="create-teacher-title-modal-label">Add Teacher Title</h5>
               <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                  @click.prevent="formCleanUp"
               ></button>
            </div>
            <div class="modal-body">
               <form id="createForm" @submit.prevent="createTeacherTitle">
                  <div class="mb-3">
                     <label for="title" class="form-label">Title</label>
                     <input id="title" type="text" v-model="form.title" class="form-control">
                     <div v-if="form.errors.title" class="text-danger">{{ form.errors.title }}</div>
                  </div>
                  <div class="mb-3">
                     <label for="scaleId" class="form-label">Salary Scale</label>
                     <v-select
                        id="scaleId"
                        v-model="form.salary_scale_id"
                        :options="salaryScales"
                        label="name"
                        :reduce="option => option.id"
                     >
                        <template #option="option">
                           <div>{{ option.name }}</div>
                           <div class="small text-primary">Grade: {{ option.grade?.name }}</div>
                        </template>
                        <template #selected-option="option">
                           <div>{{ option.name }}</div>
                           <div class="small text-muted">Grade: {{ option.grade?.name }}</div>
                        </template>
                     </v-select>
                     <div v-if="form.errors.salary_scale_id" class="text-danger">
                        {{ form.errors.salary_scale_id }}
                     </div>
                  </div>
                  <div class="mb-3">
                     <label class="row d-flex">
                        <span class="col">
                           <span class="fw-bold me-3">Activate</span>
                        </span>
                        <span class="col-auto">
                           <label class="form-check form-switch">
                              <input v-model="form.activated" class="form-check-input" type="checkbox">
                           </label>
                        </span>
                     </label>
                     <div v-if="form.errors.activated" class="text-danger">{{ form.errors.activated }}</div>
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
                  @click.prevent="createTeacherTitle"
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
      id="edit-teacher-title-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="edit-teacher-title-modal-label"
      aria-hidden="true"
      ref="editTeacherTitleModal"
   >
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="edit-teacher-title-modal-label">Edit Teacher Title</h5>
               <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                  @click="editFormCleanUp"
               ></button>
            </div>
            <div class="modal-body">
               <form id="createForm" @submit.prevent="updateTeacherTitle">
                  <div class="mb-3">
                     <label for="title" class="form-label">Title</label>
                     <input id="title" type="text" v-model="editForm.title" class="form-control">
                     <div v-if="editForm.errors.title" class="text-danger">{{ editForm.errors.title }}</div>
                  </div>
                  <div class="mb-3">
                     <label for="scaleId" class="form-label">Salary Scale</label>
                     <v-select
                        id="scaleId"
                        v-model="editForm.salary_scale_id"
                        :options="salaryScales"
                        label="name"
                        :reduce="option => option.id"
                     >
                        <template #option="option">
                           <div>{{ option.name }}</div>
                           <div class="small text-primary">Grade: {{ option.grade?.name }}</div>
                        </template>
                        <template #selected-option="option">
                           <div>{{ option.name }}</div>
                           <div class="small text-muted">Grade: {{ option.grade?.name }}</div>
                        </template>
                     </v-select>
                     <div v-if="editForm.errors.salary_scale_id" class="text-danger">
                        {{ editForm.errors.salary_scale_id }}
                     </div>
                  </div>
                  <div class="mb-3">
                     <label class="row d-flex">
                        <span class="col">
                           <span class="fw-bold me-3">Activate</span>
                        </span>
                        <span class="col-auto">
                           <label class="form-check form-switch">
                              <input v-model="editForm.activated" class="form-check-input" type="checkbox">
                           </label>
                        </span>
                     </label>
                     <div v-if="editForm.errors.activated" class="text-danger">{{ editForm.errors.activated }}</div>
                  </div>
               </form>
            </div>
            <div class="modal-footer">
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
                  @click.prevent="updateTeacherTitle"
               >
                  Submit
               </button>
            </div>
         </div>
      </div>
   </div>
</template>

<script>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import {Head, Link, useForm} from "@inertiajs/vue3";
import {Modal} from 'bootstrap';
import _debounce from "lodash/debounce.js";
import axios from "axios";

export default {
   components: {DefaultLayout, Head, Link},
   data() {
      return {
         fields: [
            {
               name: 'title',
               title: 'TITLE',
            },
            {
               name: '__slot:scale',
               title: 'SCALE',
            },
            {
               name: '__slot:status',
               title: 'STATUS',
            },
            {
               name: '__slot:actions',
               title: 'ACTIONS',
               titleClass: 'text-end w-5',
               dataClass: 'text-end w-5',
            },
         ],
         appendParams: {
            filter: {
               title: '',
            }
         },
         form: useForm({
            title: '',
            salary_scale_id: null,
            activated: false,
         }),
         editForm: useForm({
            id: '',
            title: '',
            salary_scale_id: null,
            activated: false,
         }),
         salaryScales: [],
      };
   },
   created() {
      this.fetchSalaryScales();
   },
   methods: {
      fetchSalaryScales() {
         axios.get('/datatable/salary-scales', {
            params: {
               filter: {
                  activated: true,
               }
            }
         })
            .then(({data}) => {
               this.salaryScales = data.data;
            }).catch((error) => {
            console.error(error)
         })
      },
      showCreateTeacherTitleModal() {
         const modalElement = this.$refs.createTeacherTitleModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      createTeacherTitle() {
         this.form.post(route('admin.teacher-titles.store'), {
            onSuccess: () => {
               this.form.reset(); // Reset the form on success
               this.form.clearErrors();
               this.$refs.teacherTitlesTable.reloadTable();
               const modalElement = this.$refs.createTeacherTitleModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Teacher Title Created Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      editTeacherTitle(rowData) {
         this.editForm.id = rowData.hashid; // Assign the ID manually
         this.editForm.title = rowData.title;
         this.editForm.salary_scale_id = rowData.salary_scale_id;
         this.editForm.activated = rowData.activated;
         
         const modalElement = this.$refs.editTeacherTitleModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      updateTeacherTitle() {
         this.editForm.patch(route('admin.teacher-titles.update', this.editForm.id), {
            onSuccess: () => {
               this.editForm.reset(); // Reset the form on success
               this.editForm.clearErrors();
               this.$refs.teacherTitlesTable.reloadTable();
               const modalElement = this.$refs.editTeacherTitleModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Teacher Title Updated Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         })
      },
      applyFilter: _debounce(function () {
         this.$refs.teacherTitlesTable.reloadTable();
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
