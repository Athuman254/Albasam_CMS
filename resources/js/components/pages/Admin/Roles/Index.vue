<template>
   <div class="row">
      <h3 class="mb-0">User Roles</h3>
      <nav class="mb-3">
         <ol class="breadcrumb">
            <li class="breadcrumb-item">
               <Link href="/admin/dashboard">Home</Link>
            </li>
            <li class="breadcrumb-item text-primary">
               Registered Roles
            </li>
         </ol>
      </nav>
      
      <div class="col-xxl-12">
         <div class="card">
            <div class="card-header flex-column flex-md-row">
               <div class="row row-gap-1">
                  <div class="col-md-3 col-9">
                     <input type="search" id="search" class="form-control bg-muted-lt rounded-2"
                            placeholder="Search Roles"
                            @input="applyFilter" v-model="appendParams.filter.display_name">
                  </div>
                  <div class="col-md-6 col-3 ms-lg-auto">
                     <div class="flex-wrap text-end">
                        <div class="card-action">
                           <button type="button" class="btn btn-primary d-none d-sm-inline-block"
                                   @click="showCreateRoleModal">
                              <i class="bx bx-plus-circle me-2"></i>
                              Add Role
                           </button>
                           
                           <button type="button" class="btn btn-primary btn-icon d-sm-none"
                                   @click="showCreateRoleModal">
                              <i class="bx bx-plus"></i>
                           </button>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <VueTable
               :fields="fields"
               api-url="datatable/roles"
               :append-params="appendParams"
               ref="rolesTable"
            >
               <template #actions="props">
                  <div class="dropdown">
                     <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical"></i>
                     </button>
                     <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="#" @click="editRole(props.rowData)">
                           <i class="bx bx-edit-alt me-2"></i>Edit
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
         id="create-role-modal"
         data-bs-backdrop="static"
         tabindex="-1"
         aria-labelledby="create-role-modal-label"
         aria-hidden="true"
         ref="createRoleModal"
      >
         <div class="modal-dialog modal-lg modal-body-simple">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="create-role-modal-label">Add Role</h5>
                  <button
                     type="button"
                     class="btn-close"
                     data-bs-dismiss="modal"
                     aria-label="Close"
                     @click="formCleanUp"
                  ></button>
               </div>
               <div class="modal-body">
                  <form id="createForm" @submit.prevent="createRole">
                     <div class="row">
                        <div class="col-md-12">
                           <div class="mb-3">
                              <label for="name" class="form-label">Name</label>
                              <input id="name" type="text" v-model="form.name" class="form-control">
                              <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
                           </div>
                        </div>
                        <div class="col-md-12">
                           <div class="mb-3">
                              <label for="description" class="form-label">Description</label>
                              <textarea id="description" rows="3" v-model="form.description"
                                        class="form-control"></textarea>
                              <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
                           </div>
                        </div>
                        
                        <div class="col-md-12">
                           <label for="permissions" class="h5 form-label mb-3">Permissions</label>
                           <div class="row">
                              <div v-for="permission in permissions" :key="permission.id"
                                   class="col-md-4 col-6 text-md-nowrap">
                                 <label class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" :value="permission.id"
                                           v-model="form.permissions">
                                    <span class="form-check-label">{{ permission.name }}</span>
                                 </label>
                              </div>
                           </div>
                           <div v-if="form.errors.permissions" class="text-danger">
                              {{ form.errors.permissions }}
                           </div>
                        </div>
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
                     @click.prevent="createRole"
                     :disabled="form.processing"
                  >
                     Submit
                  </button>
               </div>
               <progress v-if="form.progress" :value="form.progress.percentage" max="100">
                  {{ form.progress.percentage }}%
               </progress>
            </div>
         </div>
      </div>
      
      <!-- Edit Modal -->
      <div
         class="modal fade"
         id="edit-role-modal"
         data-bs-backdrop="static"
         tabindex="-1"
         aria-labelledby="edit-role-modal-label"
         aria-hidden="true"
         ref="editRoleModal"
      >
         <div class="modal-dialog modal-lg modal-body-simple">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="create-role-modal-label">Edit Role</h5>
                  <button
                     type="button"
                     class="btn-close"
                     data-bs-dismiss="modal"
                     aria-label="Close"
                     @click="editFormCleanUp"
                  ></button>
               </div>
               <div class="modal-body">
                  <form id="createForm" @submit.prevent="updateRole">
                     <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input id="name" type="text" v-model="editForm.name" class="form-control">
                        <div v-if="editForm.errors.name" class="text-danger">{{ editForm.errors.name }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" rows="3" v-model="editForm.description"
                                  class="form-control"></textarea>
                        <div v-if="editForm.errors.name" class="text-danger">{{ editForm.errors.name }}</div>
                     </div>
                     
                     <div class="col-md-12">
                        <label for="permissions" class="h5 form-label mb-3">Permissions</label>
                        <div class="row">
                           <div v-for="permission in permissions" :key="permission.id"
                                class="col-md-4 col-sm-6 text-md-nowrap">
                              <label class="form-check form-check-inline">
                                 <input class="form-check-input" type="checkbox" :value="permission.id"
                                        v-model="editForm.permissions">
                                 <span class="form-check-label">{{ permission.name }}</span>
                              </label>
                           </div>
                        </div>
                        <div v-if="editForm.errors.permissions" class="text-danger">
                           {{ editForm.errors.permissions }}
                        </div>
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
                     @click.prevent="updateRole"
                     :disabled="editForm.processing"
                  >
                     Update
                  </button>
               </div>
               <progress v-if="editForm.progress" :value="editForm.progress.percentage" max="100">
                  {{ editForm.progress.percentage }}%
               </progress>
            </div>
         </div>
      </div>
   </div>
</template>

<script>
import _debounce from "lodash/debounce.js";
import {Modal} from 'bootstrap';
import {useForm} from "@inertiajs/vue3";
import axios from "axios";
import {Inertia} from "@inertiajs/inertia";

export default {
   data() {
      return {
         fields: [
            {
               name: 'display_name',
               title: 'NAME',
            },
            // {
            //     name: 'name',
            //     title: 'DESCRIPTION',
            // },
            // {
            //     name: '__slot:status',
            //     title: 'STATUS',
            // },
            {
               name: '__slot:actions',
               title: 'ACTIONS',
               titleClass: 'text-end w-5',
               dataClass: 'text-end w-5',
            },
         ],
         appendParams: {
            filter: {
               display_name: '',
            }
         },
         form: useForm({
            name: '',
            description: '',
            permissions: [],
            activated: '',
         }),
         editForm: useForm({
            id: '',
            name: '',
            description: '',
            permissions: [
               {
                  id: ''
               }
            ],
            activated: '',
         }),
         
         permissions: [],
         selectedPermissions: [],
      };
   },
   created() {
      Inertia.on('navigate', (event) => {
         if (event.detail.page.url === '/admin/roles') {
            this.fetchPermissions();
         }
      });
   },
   methods: {
      fetchPermissions() {
         axios.get('/datatable/permissions')
            .then(({data}) => {
               this.permissions = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred when fetching the permissions.')
         })
      },
      showCreateRoleModal() {
         const modalElement = this.$refs.createRoleModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      createRole() {
         if (this.form.permissions.length === 0) {
            this.$toast.error('Select at least one permission');
            return;
         }
         this.form.post('/admin/roles', {
            onSuccess: () => {
               this.form.reset(); // Reset the form on success
               this.form.clearErrors();
               this.$refs.rolesTable.reloadTable();
               const modalElement = this.$refs.createRoleModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Role Created Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      editRole(rowData) {
         this.editForm.id = rowData.id; // Assign the ID manually
         this.editForm.name = rowData.display_name;
         this.editForm.description = rowData.description;
         this.editForm.permissions = rowData.permissions.map(permission => permission.id);
         
         const modalElement = this.$refs.editRoleModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      updateRole() {
         this.editForm.patch('/admin/roles/' + this.editForm.id, {
            onSuccess: () => {
               this.editForm.reset(); // Reset the form on success
               this.editForm.clearErrors();
               this.$refs.rolesTable.reloadTable();
               const modalElement = this.$refs.editRoleModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Role Updated Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         })
      },
      applyFilter: _debounce(function () {
         this.$refs.rolesTable.reloadTable();
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

<style scoped></style>
