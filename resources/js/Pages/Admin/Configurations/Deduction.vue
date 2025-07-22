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
                             @click="showcreateDeductionModal">
                        <i class="icon-base bx bx-plus-circle me-2"></i>
                        Add Deduction
                     </button>

                     <button type="button" class="btn btn-primary btn-icon d-sm-none"
                             @click="showcreateDeductionModal">
                        <i class="icon-base bx bx-plus"></i>
                     </button>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <VueTable
         :fields="fields"
         api-url="datatable/settings/deductions"
         :append-params="appendParams"
         ref="allowanceTable"
      >
      <template #name="props">
           <div>{{ props.rowData.name }}</div>
           <small class="text-info">{{ props.rowData.is_ahl_exempted ? 'is AHL exempted' : '' }}</small>
         </template>
         <template #status="props">
            <span v-if="props.rowData.is_active" class="badge bg-success">
               Active
            </span>
            <span v-else-if="!props.rowData.is_active" class="badge bg-danger">
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
                  <a class="dropdown-item" href="#" @click="editEmploymentType(props.rowData)">
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
      id="create-deduction-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="create-deduction-modal-label"
      aria-hidden="true"
      ref="createDeductionModal"
   >
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="create-deduction-modal-label">Add Deduction</h5>
               <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                  @click.prevent="formCleanUp"
               ></button>
            </div>
            <div class="modal-body">
               <form id="createForm" @submit.prevent="createAllowance">
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
                              <input v-model="form.is_active" class="form-check-input" type="checkbox">
                           </label>
                        </span>
                     </label>
                     <div v-if="form.errors.is_active" class="text-danger">{{ form.errors.is_active }}</div>
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
                  @click.prevent="createAllowance"
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
      id="edit-deduction-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="edit-deduction-modal-label"
      aria-hidden="true"
      ref="editDeductionModal"
   >
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="edit-deduction-modal-label">Edit Deduction</h5>
               <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                  @click="editFormCleanUp"
               ></button>
            </div>
            <div class="modal-body">
               <form id="createForm" @submit.prevent="updateDeduction">
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
                              <input v-model="editForm.is_active" class="form-check-input" type="checkbox">
                           </label>
                        </span>
                     </label>
                     <div v-if="editForm.errors.is_active" class="text-danger">{{ editForm.errors.is_active }}</div>
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
                  @click.prevent="updateDeduction"
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
               name: '__slot:name',
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
            is_active: true,
         }),
         editForm: useForm({
            name: '',
            is_active: true,
         }),
      };
   },
   methods: {
      showcreateDeductionModal() {
         const modalElement = this.$refs.createDeductionModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      createAllowance() {
         this.form.post(route('admin.settings.deductions.store'), {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               this.$refs.allowanceTable.reloadTable();
               const modalElement = this.$refs.createDeductionModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Deduction Created Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      editEmploymentType(rowData) {
         this.editForm.id = rowData.hashid;
         this.editForm.name = rowData.name;
         this.editForm.is_active = rowData.is_active ? true : false;

         const modalElement = this.$refs.editDeductionModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      updateDeduction() {
         this.editForm.patch(route('admin.settings.deductions.update', this.editForm.id), {
            onSuccess: () => {
               this.editForm.reset();
               this.editForm.clearErrors();
               this.$refs.allowanceTable.reloadTable();
               const modalElement = this.$refs.editDeductionModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Deduction Updated Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         })
      },
      applyFilter: _debounce(function () {
         this.$refs.allowanceTable.reloadTable();
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
