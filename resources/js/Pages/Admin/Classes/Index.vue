<template>
   <Head title="Classes"/>
   
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Registered Class</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Classes
               </li>
            </ol>
         </nav>
         
         <div class="col-xl-12">
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
                              <button type="button" class="btn btn-primary d-none d-sm-inline-block" @click="createRankModal">
                                 <i class="bx bx-plus-circle me-2"></i>
                                 Add Class
                              </button>
                              <button type="button" class="btn btn-primary btn-icon d-sm-none" @click="createRankModal">
                                 <i class="bx bx-plus"></i>
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               
               <VueTable
                  api-url="datatable/ranks"
                  :fields="fields"
                  ref="classTable"
                  :append-params="appendParams"
               >
                  <template #name="props">
                     <span class="me-1">{{ props.rowData.name }}</span> {{ props.rowData.stream?.name }}
                  </template>
                  
                  <template #teacher="props">
                     {{ props.rowData.teacher?.honorific?.name }} {{ props.rowData.teacher?.first_name }} {{ props.rowData.teacher?.last_name }}
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
                  
                  <template v-slot:actions="props">
                     <div class="dropdown">
                        <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                           <i class="bx bx-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                           <Link class="dropdown-item" :href="'/admin/ranks/' + props.rowData.hashid">
                              <i class="bx bx-detail me-2"></i> Details
                           </Link>
                           <a class="dropdown-item" href="#" @click="editRank(props.rowData)">
                              <i class="bx bx-edit-alt me-2"></i>Edit
                           </a>
   <!--                        <a class="dropdown-item text-danger" href="#">-->
   <!--                           <i class="bx bx-trash me-2"></i>Delete-->
   <!--                        </a>-->
                        </div>
                     </div>
                  </template>
               </VueTable>
            </div>
            
            <!-- Create Modal -->
            <div
               class="modal fade"
               id="create-rank-modal"
               data-bs-backdrop="static"
               tabindex="-1"
               aria-labelledby="create-rank-modal-label"
               aria-hidden="true"
               ref="createRankModal"
            >
               <div class="modal-dialog">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title" id="create-rank-modal-label">Add Class</h5>
                        <button
                           type="button"
                           class="btn-close"
                           data-bs-dismiss="modal"
                           aria-label="Close"
                           @click="formCleanUp"
                        ></button>
                     </div>
                     <div class="modal-body">
                        <form id="createForm" @submit.prevent="createRank">
                           <div class="mb-3">
                              <label for="name" class="form-label">Name</label>
                              <input id="name" type="text" v-model="form.name" class="form-control">
                              <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
                           </div>
                           
                           <div class="mb-3">
                              <label for="divisionId" class="form-label">Division</label>
                              <v-select
                                 id="divisionId"
                                 v-model="form.division_id"
                                 :options="divisions"
                                 label="name"
                                 :reduce="option => option.id"
                              ></v-select>
                              <div v-if="form.errors.division_id" class="text-danger">{{ form.errors.division_id }}</div>
                           </div>
                           
                           <div class="mb-3">
                              <label for="streamId" class="form-label">Stream</label>
                              <v-select
                                 id="streamId"
                                 v-model="form.stream_id"
                                 :options="streams"
                                 label="name"
                                 :reduce="option => option.id"
                              ></v-select>
                              <div v-if="form.errors.stream_id" class="text-danger">{{ form.errors.stream_id }}</div>
                           </div>
                           
                           <div class="mb-3">
                              <label for="teacherId" class="form-label">Class Teacher</label>
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
                           @click.prevent="createRank"
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
               id="edit-rank-modal"
               data-bs-backdrop="static"
               tabindex="-1"
               aria-labelledby="edit-rank-modal-label"
               aria-hidden="true"
               ref="editRankModal"
            >
               <div class="modal-dialog">
                  <div class="modal-content">
                     <div class="modal-header">
                        <h5 class="modal-title" id="edit-rank-modal-label">Edit Class</h5>
                        <button
                           type="button"
                           class="btn-close"
                           data-bs-dismiss="modal"
                           aria-label="Close"
                           @click="editFormCleanUp"
                        ></button>
                     </div>
                     <div class="modal-body">
                        <form id="createForm" @submit.prevent="updateRank">
                           <div class="mb-3">
                              <label for="name" class="form-label">Name</label>
                              <input id="name" type="text" v-model="editForm.name" class="form-control">
                              <div v-if="editForm.errors.name" class="text-danger">{{ editForm.errors.name }}</div>
                           </div>
                           <div class="mb-3">
                              <label for="divisionId" class="form-label">Division</label>
                              <v-select
                                 id="divisionId"
                                 v-model="editForm.division_id"
                                 :options="divisions"
                                 label="name"
                                 :reduce="option => option.id"
                              >
                              </v-select>
                              <div v-if="editForm.errors.division_id" class="text-danger">
                                 {{ editForm.errors.division_id }}
                              </div>
                           </div>
                           
                           <div class="mb-3">
                              <label for="streamId" class="form-label">Stream</label>
                              <v-select
                                 id="streamId"
                                 v-model="editForm.stream_id"
                                 :options="streams"
                                 label="name"
                                 :reduce="option => option.id"
                              ></v-select>
                              <div v-if="editForm.errors.stream_id" class="text-danger">
                                 {{ editForm.errors.stream_id }}
                              </div>
                           </div>
                           
                           <div class="mb-3">
                              <label for="teacherId" class="form-label">Class Teacher</label>
                              <v-select
                                 id="streamId"
                                 v-model="editForm.teacher_id"
                                 :options="teachers"
                                 :reduce="option => option.id"
                              >
                                 <template #option="option">
                                    {{ option.first_name }} {{ option.last_name }}
                                 </template>
                                 <template #selected-option="option">
                                    {{ option.first_name }} {{ option.last_name }}
                                 </template>
                              </v-select>
                              <div v-if="editForm.errors.teacher_id" class="text-danger">
                                 {{ editForm.errors.teacher_id }}
                              </div>
                           </div>
                           
                           <div class="mb-3">
                              <label class="row d-flex">
                                 <span class="col">
                                    <span class="fw-bold me-3">Activate Account</span>
                                 </span>
                                 <span class="col-auto">
                                    <label class="form-check form-switch">
                                       <input v-model="editForm.activated" class="form-check-input"
                                              type="checkbox">
                                    </label>
                                 </span>
   <!--                              <span class="form-check-description">When enabled, the class will be used during students' admission process.</span>-->
                              </label>
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
                           @click.prevent="updateRank"
                        >
                           Submit
                        </button>
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
import axios from 'axios';
import {Inertia} from '@inertiajs/inertia';
import {Modal} from 'bootstrap';
import _debounce from 'lodash/debounce';

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
               name: '__slot:teacher',
               title: 'CLASS TEACHER',
            },
            {
               name: 'division.name',
               title: 'DIVISION',
            },
            // {
            //    name: 'stream.name',
            //    title: 'STREAM',
            // },
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
            division_id: null,
            stream_id: null,
            teacher_id: null,
            activated: true,
         }),
         editForm: useForm({
            id: '',
            name: '',
            division_id: null,
            stream_id: null,
            teacher_id: null,
            activated: null,
         }),
         ranks: [],
         divisions: [],
         streams: [],
         teachers: [],
      };
   },
   created() {
      // Re-fetch data when navigating back to this component
      Inertia.on('navigate', (event) => {
         if (event.detail.page.url === '/admin/ranks') {
            this.fetchDivisions();
            this.fetchStreams();
            this.fetchTeachers();
         }
      });
   },
   methods: {
      fetchDivisions() {
         axios.get('/datatable/divisions', {
            params: {
               filter: {
                  activated: true,
               }
            }
         })
            .then(({data}) => {
               this.divisions = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred when fetching the divisions.')
         })
      },
      fetchStreams() {
         axios.get('/datatable/streams', {
            params: {
               filter: {
                  activated: true,
               }
            }
         })
            .then(({data}) => {
               this.streams = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred when fetching the streams.')
         })
      },
      fetchTeachers() {
         axios.get('/datatable/teachers')
            .then(({data}) => {
               this.teachers = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred when fetching the teachers.')
         })
      },
      createRankModal() {
         const modalElement = this.$refs.createRankModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      createRank() {
         this.form.post(route('admin.ranks.store'), {
            onSuccess: () => {
               this.form.reset(); // Reset the form on success
               this.form.clearErrors();
               this.$refs.classTable.reloadTable();
               const modalElement = this.$refs.createRankModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Class Created Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      editRank(rowData) {
         this.editForm.id = rowData.hashid; // Assign the ID manually
         this.editForm.name = rowData.name;
         this.editForm.division_id = rowData.division_id;
         this.editForm.stream_id = rowData.stream_id;
         this.editForm.teacher_id = rowData.teacher_id;
         this.editForm.activated = rowData.activated;
         const modalElement = this.$refs.editRankModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      updateRank() {
         this.editForm.patch( route('admin.ranks.update', this.editForm.id), {
            onSuccess: () => {
               this.editForm.reset(); // Reset the form on success
               this.editForm.clearErrors();
               this.$refs.classTable.reloadTable();
               const modalElement = this.$refs.editRankModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Class Updated Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         })
      },
      applyFilter: _debounce(function () {
         this.$refs.classTable.reloadTable()
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
</style>
