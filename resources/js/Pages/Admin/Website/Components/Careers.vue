<template>
   <div class="card">
      <div class="card-header flex-column flex-md-row">
         <div class="row row-gap-1">
            <div class="col-md-3 col-9">
               <input type="search" id="search" class="form-control bg-muted-lt rounded-2" placeholder="Search Career"
                      @input="applyFilter" v-model="appendParams.filter.title">
            </div>
            <div class="col-md-6 col-3 ms-auto">
               <div class="flex-wrap text-end">
                  <div class="card-action">
                     <button type="button" class="btn btn-primary d-none d-sm-inline-block"
                             @click="showCreateCareerModal">
                        <i class="bx bx-plus-circle me-2"></i>
                        Add Career
                     </button>
                     <button type="button" class="btn btn-primary btn-icon d-sm-none"
                             @click="showCreateCareerModal">
                        <i class="bx bx-plus"></i>
                     </button>
                  </div>
               </div>
            </div>
         </div>
      </div>
      
      <VueTable
         :fields="fields"
         api-url="datatable/website/careers"
         :append-params="appendParams"
         ref="careersTable"
      >
         <template #status="props">
            <span v-if="props.rowData.active" class="badge bg-success">
               Active
            </span>
            <span v-else-if="!props.rowData.active" class="badge bg-danger">
               Deactivated
            </span>
            <span v-else class="badge bg-secondary">
               Unknown
            </span>
         </template>
         <template v-slot:actions="props">
            <div class="dropdown">
               <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                  <i class="bx bx-dots-vertical"></i>
               </button>
               <div class="dropdown-menu dropdown-menu-end">
                  <a class="dropdown-item" href="#" @click.prevent="editCareer(props.rowData)">
                     <i class="bx bx-edit-alt me-2"></i>Edit
                  </a>
                  <a class="dropdown-item text-danger" href="#" @click.prevent="deleteCareer(props.rowData)">
                     <i class="bx bx-trash me-2"></i>Delete
                  </a>
               </div>
            </div>
         </template>
      </VueTable>
      
      <!-- Modal -->
      <div
         class="modal fade"
         id="create-career-modal"
         data-bs-backdrop="static"
         tabindex="-1"
         aria-labelledby="create-career-modal-label"
         aria-hidden="true"
         ref="createCareerModal"
      >
         <div class="modal-dialog modal-xl modal-body-simple">
            <div class="modal-content">
               <div class="modal-header pb-5">
                  <h5 class="modal-title" id="create-career-modal-label">Add Career/Vacancy</h5>
                  <button
                     type="button"
                     class="btn-close"
                     data-bs-dismiss="modal"
                     aria-label="Close"
                     @click="formCleanUp"
                     :disabled="form.processing"
                  ></button>
               </div>
               <div class="modal-body">
                  <div id="createForm" class="row">
                     <div class="col-lg-6 col-12">
                        <div class="mb-3">
                           <label for="title" class="form-label">Title</label>
                           <input id="title" type="text" v-model="form.title" class="form-control">
                           <div v-if="form.errors.title" class="text-danger">{{ form.errors.title }}</div>
                        </div>
                     </div>
                     <div class="col-lg-6 col-12">
                        <div class="mb-3">
                           <label for="location" class="form-label">Location</label>
                           <input id="location" type="text" v-model="form.location" class="form-control">
                           <div v-if="form.errors.location" class="text-danger">{{ form.errors.location }}</div>
                        </div>
                     </div>
                     <div class="col-lg-6 col-12">
                        <div class="mb-3">
                           <label for="employmentId" class="form-label">Contract Type</label>
                           <v-select
                              id="employmentId"
                              v-model="form.employment_type_id"
                              :options="employmentTypes"
                              label="name"
                              :reduce="option => option.id"
                           />
                           <div v-if="form.errors.employment_type_id" class="text-danger">{{ form.errors.employment_type_id }}</div>
                        </div>
                     </div>
                     <div class="col-lg-6 col-12">
                        <div class="mb-3">
                           <label for="deadlineDate" class="form-label">Deadline Date</label>
                           <date-picker
                              id="deadlineDate"
                              form-class="shadow-sm"
                              :value="form.deadline_date"
                              @on-change="function(dateObj, dateStr) {
                                form.deadline_date = dateStr
                              }"
                           ></date-picker>
                           <div v-if="form.errors.deadline_date" class="text-danger">{{ form.errors.deadline_date }}</div>
                        </div>
                     </div>
                     <div class="col-lg-12 col-12">
                        <div class="mb-3">
                           <label for="details" class="form-label">Job Description</label>
                           <editor v-model="form.job_description" id="editor" class="editor-control"/>
                           <div v-if="form.errors.job_description" class="text-danger">{{ form.errors.job_description }}</div>
                        </div>
                     </div>
                     <div class="col-lg-6 col-12">
                        <div class="mb-3">
                           <label class="row d-flex">
                                 <span class="col">
                                    <span class="fw-bold me-3">Active</span>
                                 </span>
                              <span class="col-auto">
                                    <label class="form-check form-switch">
                                       <input v-model="form.active" class="form-check-input" type="checkbox">
                                    </label>
                                 </span>
                              <span class="form-check-description">When enabled, the career will be displayed in the system.</span>
                           </label>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="modal-footer pt-5">
                  <button
                     type="button"
                     class="btn btn-secondary me-2"
                     data-bs-dismiss="modal"
                     @click="formCleanUp"
                     :disabled="form.processing"
                  >
                     Close
                  </button>
                  <button
                     type="button"
                     class="btn btn-primary"
                     @click.prevent="createCareer"
                     :disabled="form.processing"
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
         id="edit-career-modal"
         data-bs-backdrop="static"
         tabindex="-1"
         aria-labelledby="edit-career-modal-label"
         aria-hidden="true"
         ref="editCareerModal"
      >
         <div class="modal-dialog modal-xl modal-body-simple">
            <div class="modal-content">
               <div class="modal-header pb-5">
                  <h5 class="modal-title" id="create-career-modal-label">Edit Career/Vacancy</h5>
                  <button
                     type="button"
                     class="btn-close"
                     data-bs-dismiss="modal"
                     aria-label="Close"
                     @click="formCleanUp"
                     :disabled="form.processing"
                  ></button>
               </div>
               <div class="modal-body">
                  <div id="createForm" class="row">
                     <div class="col-lg-6 col-12">
                        <div class="mb-3">
                           <label for="title" class="form-label">Title</label>
                           <input id="title" type="text" v-model="form.title" class="form-control">
                           <div v-if="form.errors.title" class="text-danger">{{ form.errors.title }}</div>
                        </div>
                     </div>
                     <div class="col-lg-6 col-12">
                        <div class="mb-3">
                           <label for="slug" class="form-label">Slug</label>
                           <input id="slug" type="text" v-model="form.slug" class="form-control">
                           <div v-if="form.errors.slug" class="text-danger">{{ form.errors.slug }}</div>
                        </div>
                     </div>
                     <div class="col-lg-6 col-12">
                        <div class="mb-3">
                           <label for="location" class="form-label">Location</label>
                           <input id="location" type="text" v-model="form.location" class="form-control">
                           <div v-if="form.errors.location" class="text-danger">{{ form.errors.location }}</div>
                        </div>
                     </div>
                     <div class="col-lg-6 col-12">
                        <div class="mb-3">
                           <label for="categoryId" class="form-label">Contract Type</label>
                           <v-select
                              id="pageId"
                              v-model="form.employment_type_id"
                              :options="employmentTypes"
                              label="name"
                              :reduce="option => option.id"
                           />
                           <div v-if="form.errors.employment_type_id" class="text-danger">{{ form.errors.employment_type_id }}</div>
                        </div>
                     </div>
                     <div class="col-lg-6 col-12">
                        <div class="mb-3">
                           <label for="deadlineDate" class="form-label">Deadline Date</label>
                           <date-picker
                              id="deadlineDate"
                              form-class="shadow-sm"
                              :value="form.deadline_date"
                              @on-change="function(dateObj, dateStr) {
                                form.deadline_date = dateStr
                              }"
                           ></date-picker>
                           <div v-if="form.errors.deadline_date" class="text-danger">{{ form.errors.deadline_date }}</div>
                        </div>
                     </div>
                     <div class="col-lg-12 col-12">
                        <div class="mb-3">
                           <label for="details" class="form-label">Job Description</label>
                           <editor v-model="form.job_description" id="editor" class="editor-control"/>
                           <div v-if="form.errors.job_description" class="text-danger">{{ form.errors.job_description }}</div>
                        </div>
                     </div>
                     <div class="col-lg-6 col-12">
                        <div class="mb-3">
                           <label class="row d-flex">
                                 <span class="col">
                                    <span class="fw-bold me-3">Active</span>
                                 </span>
                              <span class="col-auto">
                                    <label class="form-check form-switch">
                                       <input v-model="form.active" class="form-check-input" type="checkbox">
                                    </label>
                                 </span>
                              <span class="form-check-description">When enabled, the career will be displayed in the system.</span>
                           </label>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="modal-footer pt-5">
                  <button
                     type="button"
                     class="btn btn-secondary me-2"
                     data-bs-dismiss="modal"
                     @click="formCleanUp"
                     :disabled="form.processing"
                  >
                     Close
                  </button>
                  <button
                     type="button"
                     class="btn btn-primary"
                     @click.prevent="updateCareer"
                     :disabled="form.processing"
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
import {useForm} from "@inertiajs/vue3";
import {Modal} from 'bootstrap';
import _debounce from "lodash/debounce.js";
import Editor from '@/Components/global/Editor.vue'
import axios from "axios";

export default {
   components: {Editor},
   data() {
      return {
         fields: [
            {
               name: 'title',
               title: 'TITLE',
            },
            {
               name: 'contract_type.name',
               title: 'CONTRACT TYPE',
            },
            {
               name: 'location',
               title: 'LOCATION',
            },
            {
               name: 'deadline_date',
               title: 'DEADLINE',
            },
            {
               name: '__slot:status',
               title: 'STATUS',
            },
            {
               name: '__slot:actions',
               title: 'ACTIONS',
               titleClass: '5%',
               dataClass: '5%',
            },
         ],
         appendParams: {
            filter: {
               title: '',
            }
         },
         form: useForm({
            id: '',
            title: '',
            slug: '',
            employment_type_id: null,
            location: null,
            start_date: null,
            deadline_date: null,
            job_description: '',
            active: true,
         }),
         employmentTypes: [],
      }
   },
   created() {
      this.fetchEmploymentTypes();
   },
   methods: {
      fetchEmploymentTypes() {
         axios.get('/datatable/employment-types', {
            params: {
               filter: {
                  'activated': true,
               }
            }
         })
            .then(({data}) => {
               this.employmentTypes = data.data;
            }).catch((error) => {
            console.log(error);
            // this.$toast.error("An error occurred while fetching the pages!", "Error");
         })
      },
      showCreateCareerModal() {
         const modalElement = this.$refs.createCareerModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      createCareer() {
         this.form.post(route('admin.components.careers.store'), {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               this.form.media = null;
               this.$refs.careersTable.reloadTable();
               const modalElement = this.$refs.createCareerModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Career Created Successfully', 'Success')
            },
            onError: (error) => {
               console.log(error)
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      editCareer(rowData) {
         this.form.id = rowData.hashid;
         this.form.title = rowData.title;
         this.form.slug = rowData.slug;
         this.form.location = rowData.location;
         this.form.deadline_date = rowData.deadline_date;
         this.form.employment_type_id = rowData.employment_type_id;
         this.form.job_description = rowData.job_description;
         this.form.active = rowData.active;
         
         const modalElement = this.$refs.editCareerModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      updateCareer() {
         this.form.patch(route('admin.components.careers.update', this.form.id), {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               this.form.media = null;
               this.$refs.careersTable.reloadTable();
               const modalElement = this.$refs.editCareerModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Career details Updated Successfully', 'Success')
            },
            onError: (errors) => {
               console.log(errors);
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         })
      },
      deleteCareer(career) {
         this.$toast.question('Are you sure?', `Deleting ${career.title}`).then(() => {
            this.$inertia.delete(route('admin.components.careers.destroy', career.id), {
               onSuccess: () => {
                  this.$toast.success('Career deleted', 'Success');
                  this.$refs.careersTable.reloadTable();
               },
               onError: (error) => {
                  console.log(error)
                  this.$toast.error('An error occurred while deleting the career!', 'Error');
               }
            })
         })
      },
      applyFilter: _debounce(function () {
         this.$refs.careersTable.reloadTable();
      }, 800),
      formCleanUp() {
         this.form.reset();
         this.form.clearErrors();
      },
   },
}
</script>

<style scoped></style>
