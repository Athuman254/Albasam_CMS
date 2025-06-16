<template>
   <div class="card">
      <div class="card-header flex-column flex-md-row">
         <div class="row row-gap-1">
            <div class="col-md-3 col-9">
               <input type="search" id="search" class="form-control bg-muted-lt rounded-2" placeholder="Search..."
                      @input="applyFilter" v-model="appendParams.filter.name">
            </div>
            <div class="col-md-6 col-3 ms-lg-auto">
               <div class="flex-wrap text-end">
                  <div class="card-action">
                     <button type="button" class="btn btn-primary d-none d-sm-inline-block"
                             @click="showCreateSubjectModal">
                        <i class="bx bx-plus-circle me-2"></i>
                        Add Subject
                     </button>
                     
                     <button type="button" class="btn btn-primary btn-icon d-sm-none"
                             @click="showCreateSubjectModal">
                        <i class="bx bx-plus"></i>
                     </button>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <VueTable
         :fields="fields"
         api-url="datatable/subjects"
         :append-params="appendParams"
         ref="subjectsTable"
      >
         <template #groups="props">
                     <span v-if="props.rowData.group === 1" class="badge bg-label-warning">
                        Language
                     </span>
            <span v-else-if="props.rowData.group === 2" class="badge bg-label-primary">
                        Science
                     </span>
            <span v-else-if="props.rowData.group === 3" class="badge bg-label-info">
                        Applied Science
                     </span>
            <span v-else-if="props.rowData.group === 4" class="badge bg-label-success">
                        Humanities
                     </span>
            <span v-else-if="props.rowData.group === 5" class="badge bg-label-dark">
                        Creative Arts
                     </span>
            <span v-else class="badge bg-secondary">
                        Unknown
                     </span>
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
                  <a class="dropdown-item" href="#" @click="editSubject(props.rowData)">
                     <i class="bx bx-edit-alt me-2"></i>Edit
                  </a>
                  <!--                                <a class="dropdown-item text-danger" href="#">-->
                  <!--                                    <i class="bx bx-trash me-2"></i>Delete-->
                  <!--                                </a>-->
               </div>
            </div>
         </template>
      </VueTable>
   </div>
   
   <!-- Create Modal -->
   <div
      class="modal fade"
      id="create-subject-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="create-subject-modal-label"
      aria-hidden="true"
      ref="createSubjectModal"
   >
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="create-subject-modal-label">Add Subject</h5>
               <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                  @click.prevent="formCleanUp"
               ></button>
            </div>
            <div class="modal-body">
               <form id="createForm" @submit.prevent="createSubject">
                  <div class="mb-3">
                     <label for="name" class="form-label">Name</label>
                     <input id="name" type="text" v-model="form.name" class="form-control">
                     <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
                  </div>
                  
                  <div class="mb-3">
                     <label for="code" class="form-label">Code</label>
                     <input id="code" type="text" v-model="form.code" class="form-control">
                     <div v-if="form.errors.code" class="text-danger">{{ form.errors.code }}</div>
                  </div>
                  
                  <div class="mb-3">
                     <label for="group" class="form-label">Group</label>
                     <v-select
                        id="streamId"
                        v-model="form.group"
                        :options="learningAreas"
                        label="name"
                        :reduce="option => option.id"
                     ></v-select>
                     <div v-if="form.errors.group" class="text-danger">{{ form.errors.group }}</div>
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
                        <!--                           <span class="form-check-description">When enabled, the subject will be used during students' admission process.</span>-->
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
                  @click.prevent="createSubject"
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
      id="edit-subject-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="edit-subject-modal-label"
      aria-hidden="true"
      ref="editSubjectModal"
   >
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="edit-subject-modal-label">Edit Subject</h5>
               <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                  @click="editFormCleanUp"
               ></button>
            </div>
            <div class="modal-body">
               <form id="createForm" @submit.prevent="updateSubject">
                  <div class="mb-3">
                     <label for="name" class="form-label">Name</label>
                     <input id="name" type="text" v-model="editForm.name" class="form-control">
                     <div v-if="editForm.errors.name" class="text-danger">{{ editForm.errors.name }}</div>
                  </div>
                  
                  <div class="mb-3">
                     <label for="code" class="form-label">Code</label>
                     <input id="code" type="text" v-model="editForm.code" class="form-control">
                     <div v-if="editForm.errors.code" class="text-danger">{{ editForm.errors.code }}</div>
                  </div>
                  
                  <div class="mb-3">
                     <label for="group" class="form-label">Group</label>
                     <v-select
                        id="streamId"
                        v-model="editForm.group"
                        :options="learningAreas"
                        label="name"
                        :reduce="option => option.id"
                     ></v-select>
                     <div v-if="editForm.errors.group" class="text-danger">{{ editForm.errors.group }}</div>
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
                        <!--                           <span class="form-check-description">When enabled, the subject will be used during students' admission process.</span>-->
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
                  @click.prevent="updateSubject"
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

export default {
   components: {DefaultLayout, Head, Link},
   data() {
      return {
         fields: [
            {
               name: 'name',
               title: 'NAME',
            },
            {
               name: 'code',
               title: 'CODE',
            },
            {
               name: '__slot:groups',
               title: 'GROUP',
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
               name: '',
            }
         },
         form: useForm({
            name: '',
            code: '',
            group: '',
            activated: '',
         }),
         editForm: useForm({
            id: '',
            name: '',
            code: '',
            group: '',
            activated: '',
         }),
         
         learningAreas: [
            {id: 1, name: 'Languages'},
            {id: 2, name: 'Sciences'},
            {id: 3, name: 'Applied Science'},
            {id: 4, name: 'Humanities'},
            {id: 5, name: 'Creative Arts'},
            {id: 6, name: 'Technical Subject'},
         ]
      };
   },
   methods: {
      showCreateSubjectModal() {
         const modalElement = this.$refs.createSubjectModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      createSubject() {
         this.form.post('/admin/settings/subjects', {
            onSuccess: () => {
               this.form.reset(); // Reset the form on success
               this.form.clearErrors();
               this.$refs.subjectsTable.reloadTable();
               const modalElement = this.$refs.createSubjectModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Subject Created Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      editSubject(rowData) {
         this.editForm.id = rowData.hashid; // Assign the ID manually
         this.editForm.name = rowData.name;
         this.editForm.code = rowData.code;
         this.editForm.group = rowData.group;
         this.editForm.activated = rowData.activated;
         
         const modalElement = this.$refs.editSubjectModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      updateSubject() {
         this.editForm.patch('/admin/settings/subjects/' + this.editForm.id, {
            onSuccess: () => {
               this.editForm.reset(); // Reset the form on success
               this.editForm.clearErrors();
               this.$refs.subjectsTable.reloadTable();
               const modalElement = this.$refs.editSubjectModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Subject Updated Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         })
      },
      applyFilter: _debounce(function () {
         this.$refs.subjectsTable.reloadTable();
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
