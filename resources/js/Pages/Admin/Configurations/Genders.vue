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
                             @click="showCreateGenderModal">
                        <i class="icon-base bx bx-plus-circle me-2"></i>
                        Add Gender
                     </button>
                     
                     <button type="button" class="btn btn-primary btn-icon d-sm-none"
                             @click="showCreateGenderModal">
                        <i class="icon-base bx bx-plus"></i>
                     </button>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <VueTable
         :fields="fields"
         api-url="datatable/genders"
         :append-params="appendParams"
         ref="gendersTable"
      >
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
                  <i class="icon-base bx bx-dots-vertical"></i>
               </button>
               <div class="dropdown-menu dropdown-menu-end">
                  <a class="dropdown-item" href="#" @click="editGender(props.rowData)">
                     <i class="icon-base bx bx-edit-alt me-2"></i>Edit
                  </a>
               </div>
            </div>
         </template>
      </VueTable>
   </div>
   
   <!-- Create Modal -->
   <div
      class="modal fade"
      id="create-gender-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="create-gender-modal-label"
      aria-hidden="true"
      ref="createGenderModal"
   >
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="create-gender-modal-label">Add Gender</h5>
               <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                  @click.prevent="formCleanUp"
               ></button>
            </div>
            <div class="modal-body">
               <form id="createForm" @submit.prevent="createGender">
                  <div class="mb-3">
                     <label for="name" class="form-label">Name</label>
                     <input id="name" type="text" v-model="form.name" class="form-control">
                     <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
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
                  @click.prevent="createGender"
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
      id="edit-gender-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="edit-gender-modal-label"
      aria-hidden="true"
      ref="editGenderModal"
   >
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="edit-gender-modal-label">Edit Gender</h5>
               <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                  @click="editFormCleanUp"
               ></button>
            </div>
            <div class="modal-body">
               <form id="createForm" @submit.prevent="updateGender">
                  <div class="mb-3">
                     <label for="name" class="form-label">Name</label>
                     <input id="name" type="text" v-model="editForm.name" class="form-control">
                     <div v-if="editForm.errors.name" class="text-danger">{{ editForm.errors.name }}</div>
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
                  @click.prevent="updateGender"
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
            activated: false,
         }),
         editForm: useForm({
            id: '',
            name: '',
            activated: false,
         }),
      };
   },
   methods: {
      showCreateGenderModal() {
         const modalElement = this.$refs.createGenderModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      createGender() {
         this.form.post(route('admin.genders.store'), {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               this.$refs.gendersTable.reloadTable();
               const modalElement = this.$refs.createGenderModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Gender Created Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      editGender(rowData) {
         this.editForm.id = rowData.hashid;
         this.editForm.name = rowData.name;
         this.editForm.activated = rowData.activated;
         
         const modalElement = this.$refs.editGenderModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      updateGender() {
         this.editForm.patch(route('admin.genders.update', this.editForm.id), {
            onSuccess: () => {
               this.editForm.reset();
               this.editForm.clearErrors();
               this.$refs.gendersTable.reloadTable();
               const modalElement = this.$refs.editGenderModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Gender Updated Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         })
      },
      applyFilter: _debounce(function () {
         this.$refs.gendersTable.reloadTable();
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
