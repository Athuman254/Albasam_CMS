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
                             @click="showCreateModal">
                        <i class="icon-base bx bx-plus-circle me-2"></i>
                        Add Academic Year
                     </button>
                     
                     <button type="button" class="btn btn-primary btn-icon d-sm-none"
                             @click="showCreateModal">
                        <i class="icon-base bx bx-plus"></i>
                     </button>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <VueTable
         :fields="fields"
         api-url="datatable/academic-years"
         :append-params="appendParams"
         ref="academicYearsTable"
      >
         <template #status="props">
            <span v-if="props.rowData.is_active" class="badge bg-success">
               Active
            </span>
            <span v-else class="badge bg-secondary">
               Inactive
            </span>
         </template>
         
         <template #actions="props">
            <div class="dropdown">
               <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                  <i class="icon-base bx bx-dots-vertical"></i>
               </button>
               <div class="dropdown-menu dropdown-menu-end">
                  <a class="dropdown-item" href="#" @click.prevent="editAcademicYear(props.rowData)">
                     <i class="icon-base bx bx-edit-alt me-2"></i>Edit
                  </a>
                  <a v-if="!props.rowData.is_active" class="dropdown-item" href="#" @click.prevent="activateYear(props.rowData)">
                     <i class="icon-base bx bx-check-circle me-2"></i>Set as Active
                  </a>
                  <a class="dropdown-item text-danger" href="#" @click.prevent="deleteYear(props.rowData)">
                     <i class="icon-base bx bx-trash me-2"></i>Delete
                  </a>
               </div>
            </div>
         </template>
      </VueTable>
   </div>
   
   <!-- Create/Edit Modal -->
   <div
      class="modal fade"
      id="academic-year-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="academic-year-modal-label"
      aria-hidden="true"
      ref="academicYearModal"
   >
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="academic-year-modal-label">
                  {{ editMode ? 'Edit Academic Year' : 'Add Academic Year' }}
               </h5>
               <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                  @click.prevent="formCleanUp"
               ></button>
            </div>
            <div class="modal-body">
               <form id="academicYearForm" @submit.prevent="submitForm">
                  <div class="mb-3">
                     <label for="name" class="form-label">Name</label>
                     <input id="name" type="text" v-model="form.name" class="form-control" placeholder="e.g., 2025-2026">
                     <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
                  </div>
                  
                  <div class="mb-3">
                     <label for="start_date" class="form-label">Start Date</label>
                     <input id="start_date" type="date" v-model="form.start_date" class="form-control">
                     <div v-if="form.errors.start_date" class="text-danger">{{ form.errors.start_date }}</div>
                  </div>

                  <div class="mb-3">
                     <label for="end_date" class="form-label">End Date</label>
                     <input id="end_date" type="date" v-model="form.end_date" class="form-control">
                     <div v-if="form.errors.end_date" class="text-danger">{{ form.errors.end_date }}</div>
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
                        <span class="form-check-description">When enabled, this will be the current academic year for the system.</span>
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
                  @click.prevent="submitForm"
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
import {Link, Head, useForm} from "@inertiajs/vue3";
import {Modal} from 'bootstrap';
import _debounce from "lodash/debounce.js";

export default {
   components: {DefaultLayout, Head, Link},
   data() {
      return {
         editMode: false,
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
            id: '',
            name: '',
            start_date: '',
            end_date: '',
            is_active: false,
         }),
      };
   },
   methods: {
      showCreateModal() {
         this.editMode = false;
         this.formCleanUp();
         const modalElement = this.$refs.academicYearModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      submitForm() {
         if (this.editMode) {
            this.updateAcademicYear();
         } else {
            this.createAcademicYear();
         }
      },
      createAcademicYear() {
         this.form.post(route('admin.academic-years.store'), {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               this.$refs.academicYearsTable.reloadTable();
               const modalElement = this.$refs.academicYearModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Academic Year Created Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      editAcademicYear(rowData) {
         this.editMode = true;
         this.form.id = rowData.id;
         this.form.name = rowData.name;
         this.form.start_date = rowData.start_date;
         this.form.end_date = rowData.end_date;
         this.form.is_active = rowData.is_active;
         
         const modalElement = this.$refs.academicYearModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      updateAcademicYear() {
         this.form.put(route('admin.academic-years.update', this.form.id), {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               this.$refs.academicYearsTable.reloadTable();
               const modalElement = this.$refs.academicYearModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Academic Year Updated Successfully', 'Success')
            },
            onError: (error) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         })
      },
      activateYear(rowData) {
         if (confirm('Are you sure you want to set this as the active academic year? This will deactivate all other years.')) {
            this.$inertia.post(route('admin.academic-years.activate', rowData.id), {}, {
               onSuccess: () => {
                  this.$refs.academicYearsTable.reloadTable();
                  this.$toast.success('Academic Year Activated Successfully', 'Success')
               },
               onError: () => {
                  this.$toast.error('Failed to activate academic year', 'Error')
               },
            });
         }
      },
      deleteYear(rowData) {
         if (rowData.is_active) {
            this.$toast.error('Cannot delete the active academic year', 'Error');
            return;
         }
         
         if (confirm('Are you sure you want to delete this academic year? This action cannot be undone.')) {
            this.$inertia.delete(route('admin.academic-years.destroy', rowData.id), {
               onSuccess: () => {
                  this.$refs.academicYearsTable.reloadTable();
                  this.$toast.success('Academic Year Deleted Successfully', 'Success')
               },
               onError: () => {
                  this.$toast.error('Failed to delete academic year', 'Error')
               },
            });
         }
      },
      applyFilter: _debounce(function () {
         this.$refs.academicYearsTable.reloadTable();
      }, 800),
      formCleanUp() {
         this.form.reset();
         this.form.clearErrors();
         this.editMode = false;
      },
   },
}

</script>
