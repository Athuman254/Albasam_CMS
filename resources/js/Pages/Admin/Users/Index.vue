<template>
   <Head title="Users"/>
   
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Registered Users</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  System Users
               </li>
            </ol>
         </nav>
         
         <div class="col-xxl-12">
            <div class="card">
               <div class="card-header flex-column flex-md-row">
                  <div class="row row-gap-1">
                     <div class="col-md-3 col-9">
                        <input type="search" id="search" class="form-control bg-muted-lt rounded-2" placeholder="Search Users"
                               @input="applyFilter" v-model="appendParams.filter.name">
                     </div>
                     <div v-if="can('add-users')" class="col-md-6 col-3 ms-lg-auto">
                        <div class="flex-wrap text-end">
                           <div class="card-action">
                              <button type="button" class="btn btn-primary d-none d-sm-inline-block"
                                      @click="showCreateUserModal">
                                 <i class="bx bx-plus-circle me-2"></i>
                                 Add User
                              </button>
                              <button type="button" class="btn btn-primary btn-icon d-sm-none"
                                      @click="showCreateUserModal">
                                 <i class="bx bx-plus"></i>
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               
               <VueTable
                  :fields="fields"
                  api-url="datatable/users"
                  :append-params="appendParams"
                  ref="usersTable"
               >
                  <template v-slot:actions="props">
                     <div class="dropdown">
                        <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                           <i class="bx bx-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                           <a v-if="can('edit-users')" class="dropdown-item" href="#" @click="editUser(props.rowData)">
                              <i class="bx bx-edit-alt me-2"></i>Edit
                           </a>
                           <button class="dropdown-item" type="button" @click="editPermissions(props.rowData)">
                              <i class="bx bx-key me-2"></i>Permissions
                           </button>
                           <a class="dropdown-item text-danger" href="#">
                              <i class="bx bx-trash me-2"></i>Delete
                           </a>
                        </div>
                     </div>
                  </template>
               </VueTable>
            </div>
         </div>
         
         <!-- Modal -->
         <div
            class="modal fade"
            id="create-user-modal"
            data-bs-backdrop="static"
            tabindex="-1"
            aria-labelledby="create-user-modal-label"
            aria-hidden="true"
            ref="createUserModal"
         >
            <div class="modal-dialog modal-body-simple">
               <div class="modal-content">
                  <div class="modal-header pb-5">
                     <h5 class="modal-title" id="create-user-modal-label">Add User</h5>
                     <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        @click="formCleanUp"
                     ></button>
                  </div>
                  <div class="modal-body">
                     <form id="createForm" @submit.prevent="createUser">
                        <div class="mb-3">
                           <label for="name" class="form-label">Name</label>
                           <input id="name" type="text" v-model="form.name" class="form-control">
                           <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
                        </div>
                        
                        <div class="mb-3">
                           <label for="username" class="form-label">Username</label>
                           <input id="username" type="text" v-model="form.username" class="form-control">
                           <div v-if="form.errors.username" class="text-danger">{{ form.errors.username }}</div>
                        </div>
                        
                        <div class="mb-3">
                           <label for="email" class="form-label">Email</label>
                           <input id="email" type="email" v-model="form.email" class="form-control">
                           <div v-if="form.errors.email" class="text-danger">{{ form.errors.email }}</div>
                        </div>
                        
                        <div class="mb-3">
                           <label for="phone" class="form-label">Phone</label>
                           <input id="phone" type="text" v-model="form.phone" class="form-control">
                           <div v-if="form.errors.phone" class="text-danger">{{ form.errors.phone }}</div>
                        </div>
                        
                        <div class="mb-3">
                           <label for="roleId" class="form-label">Role</label>
                           <v-select
                              id="roleId"
                              v-model="form.role_id"
                              :options="roles"
                              label="display_name"
                              :reduce="option => option.id"
                           ></v-select>
                           <div v-if="form.errors.role_id" class="text-danger">{{ form.errors.role_id }}</div>
                        </div>
                        
                        <div class="mb-3">
                           <label for="password" class="form-label">Password</label>
                           <div class="input-group input-group-merge">
                              <input id="password" :type="showPassword ? 'text' : 'password'" v-model="form.password" class="form-control">
                              <span class="input-group-text cursor-pointer" @click="toggleShow">
                                 <i :class="showPassword ? 'bx bx-show' : 'bx bx-hide'"></i>
                              </span>
                           </div>
                           <div v-if="form.errors.password" class="text-danger">{{ form.errors.password }}</div>
                        </div>
                        
                        <div class="mb-3">
                           <label class="row d-flex">
                              <span class="col">
                                 <span class="fw-bold me-3">Activate Account</span>
                              </span>
                              <span class="col-auto">
                                 <label class="form-check form-switch">
                                    <input v-model="form.activated" class="form-check-input" type="checkbox">
                                 </label>
                              </span>
                              <span class="form-check-description">When enabled, the user can login into the system.</span>
                           </label>
                        </div>
                        
                        <div class="mb-3">
                           <label class="row d-flex">
                              <span class="col">
                                 <span class="fw-bold me-3">Admin</span>
                              </span>
                              <span class="col-auto">
                                 <label class="form-check form-switch">
                                    <input v-model="form.is_admin" class="form-check-input" type="checkbox">
                                 </label>
                              </span>
                              <span class="form-check-description">When enabled, the user can login into the system as an admin.</span>
                           </label>
                        </div>
                        
                        <div class="mb-3">
                           <label class="row d-flex">
                              <span class="col">
                                 <span class="fw-bold me-3">Teacher</span>
                              </span>
                              <span class="col-auto">
                                 <label class="form-check form-switch">
                                    <input v-model="form.is_teacher" class="form-check-input" type="checkbox">
                                 </label>
                              </span>
                              <span class="form-check-description">When enabled, the user can login into the system as a teacher.</span>
                           </label>
                        </div>
                        
                        <div class="mb-3">
                           <label class="row">
                              <span class="col">
                                 <span class="fw-bold">Parent</span>
                              </span>
                              <span class="col-auto">
                                 <label class="form-check form-switch">
                                    <input v-model="form.is_parent" class="form-check-input" type="checkbox">
                                 </label>
                              </span>
                              <span class="form-check-description">When enabled, the user can login into the system as a parent.</span>
                           </label>
                        </div>
                     </form>
                  </div>
                  <div class="modal-footer pt-5">
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
                        @click.prevent="createUser"
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
            id="edit-user-modal"
            data-bs-backdrop="static"
            tabindex="-1"
            aria-labelledby="edit-user-modal-label"
            aria-hidden="true"
            ref="editUserModal"
         >
            <div class="modal-dialog modal-body-simple">
               <div class="modal-content">
                  <div class="modal-header pb-5">
                     <h5 class="modal-title" id="edit-user-modal-label">Edit User</h5>
                     <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        @click="editFormCleanUp"
                     ></button>
                  </div>
                  <div class="modal-body">
                     <form id="createForm" @submit.prevent="createUser">
                        <div class="mb-3">
                           <label for="name" class="form-label">Name</label>
                           <input id="name" type="text" v-model="editForm.name" class="form-control">
                           <div v-if="editForm.errors.name" class="text-danger">{{ editForm.errors.name }}</div>
                        </div>
                        
                        <div class="mb-3">
                           <label for="username" class="form-label">Username</label>
                           <input id="username" type="text" v-model="editForm.username" class="form-control">
                           <div v-if="editForm.errors.username" class="text-danger">{{ editForm.errors.username }}</div>
                        </div>
                        
                        <div class="mb-3">
                           <label for="email" class="form-label">Email</label>
                           <input id="email" type="email" v-model="editForm.email" class="form-control">
                           <div v-if="editForm.errors.email" class="text-danger">{{ editForm.errors.email }}</div>
                        </div>
                        
                        <div class="mb-3">
                           <label for="phone" class="form-label">Phone</label>
                           <input id="phone" type="text" v-model="editForm.phone" class="form-control">
                           <div v-if="editForm.errors.phone" class="text-danger">{{ editForm.errors.phone }}</div>
                        </div>
                        
                        <div class="mb-3">
                           <label for="roleId" class="form-label">Role</label>
                           <v-select
                              id="roleId"
                              v-model="editForm.role_id"
                              :options="roles"
                              label="display_name"
                              :reduce="option => option.id"
                           ></v-select>
                           <div v-if="editForm.errors.role_id" class="text-danger">{{ editForm.errors.role_id }}</div>
                        </div>
                        
                        <div class="mb-3">
                           <label for="password" class="form-label">Password</label>
                           <div class="input-group input-group-merge">
                              <input id="password" :type="showPassword ? 'text' : 'password'" v-model="editForm.password" class="form-control">
                              <span class="input-group-text cursor-pointer" @click="toggleShow">
                                 <i :class="showPassword ? 'bx bx-show' : 'bx bx-hide'"></i>
                              </span>
                           </div>
                           <div v-if="editForm.errors.password" class="text-danger">{{ editForm.errors.password }}</div>
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
                              <span class="form-check-description">When enabled, the user can login into the system.</span>
                           </label>
                        </div>
                        
                        <div class="mb-3">
                           <label class="row d-flex">
                              <span class="col">
                                 <span class="fw-bold me-3">Admin</span>
                              </span>
                              <span class="col-auto">
                                 <label class="form-check form-switch">
                                    <input v-model="editForm.is_admin" class="form-check-input"
                                           type="checkbox">
                                 </label>
                              </span>
                              <span class="form-check-description">When enabled, the user can login into the system as an admin.</span>
                           </label>
                        </div>
                        
                        <div class="mb-3">
                           <label class="row d-flex">
                              <span class="col">
                                 <span class="fw-bold me-3">Teacher</span>
                              </span>
                              <span class="col-auto">
                                 <label class="form-check form-switch">
                                    <input v-model="editForm.is_teacher" class="form-check-input"
                                           type="checkbox">
                                 </label>
                              </span>
                              <span class="form-check-description">When enabled, the user can login into the system as a teacher.</span>
                           </label>
                        </div>
                        
                        <div class="mb-3">
                           <label class="row">
                              <span class="col">
                                 <span class="fw-bold">Parent</span>
                              </span>
                              <span class="col-auto">
                                 <label class="form-check form-switch">
                                    <input v-model="editForm.is_parent" class="form-check-input" type="checkbox">
                                 </label>
                              </span>
                              <span class="form-check-description">When enabled, the user can login into the system as a parent.</span>
                           </label>
                        </div>
                     </form>
                  </div>
                  <div class="modal-footer pt-5">
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
                        @click.prevent="updateUser"
                     >
                        Submit
                     </button>
                  </div>
               </div>
            </div>
         </div>
         
         <!-- Permissions Modal -->
         <div
            class="modal fade"
            id="edit-permissions-modal"
            data-bs-backdrop="static"
            tabindex="-1"
            aria-labelledby="edit-user-permissions"
            aria-hidden="true"
            ref="editUserPermissions"
         >
            <div class="modal-dialog modal-lg" role="document">
               <div class="modal-content">
                  <div class="modal-header">
                     <h5 class="mb-6" data-bs-backdrop="static">Edit User Permissions</h5>
                     <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        @click="permissionFormCleanUp"
                     ></button>
                  </div>
                  <div class="modal-body p-10">
                     <div class="col-12 mb-6">
                        <div class="row">
                           <div v-for="permission in permissions" :key="permission.id"
                                class="col-md-4 col-6 text-md-nowrap">
                              <div class="mb-2">
                                 <label class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" :value="permission.id"
                                           v-model="permissionForm.permissions">
                                    <span class="form-check-label">{{ permission.name }}</span>
                                 </label>
                              </div>
                           </div>
                        </div>
                        <div v-if="permissionForm.errors.permissions" class="text-danger">
                           {{ permissionForm.errors.permissions }}
                        </div>
                     </div>
                  </div>
                  <div class="modal-footer">
                     <button
                        type="button"
                        class="btn btn-secondary me-2"
                        data-bs-dismiss="modal"
                        @click="permissionFormCleanUp"
                     >
                        Close
                     </button>
                     <button
                        type="button"
                        class="btn btn-primary"
                        @click.prevent="updatePermissions"
                     >
                        Submit
                     </button>
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
import axios from "axios";
import {Inertia} from "@inertiajs/inertia";
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
               name: 'username',
               title: 'USERNAME',
            },
            {
               name: 'email',
               title: 'EMAIL',
            },
            {
               name: 'phone',
               title: 'PHONE',
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
               name: '',
            }
         },
         form: useForm({
            name: '',
            username: '',
            email: '',
            phone: '',
            role_id: '',
            password: '',
            activated: '',
            is_admin: '',
            is_teacher: '',
            is_parent: '',
         }),
         editForm: useForm({
            id: '',
            name: '',
            username: '',
            email: '',
            phone: '',
            role_id: '',
            password: '',
            activated: '',
            is_admin: '',
            is_teacher: '',
            is_parent: '',
         }),
         permissionForm: useForm({
            id: '',
            permissions: [],
         }),
         
         user: '',
         roles: [],
         permissions: [],
         userPermissions: [],
         showPassword: false,
      };
   },
   created() {
      Inertia.on('navigate', (event) => {
         if (event.detail.page.url === '/admin/users') {
            this.fetchRoles();
            this.fetchPermissions();
         }
      });
   },
   computed: {
      authUser() {
         return this.$page.props.auth.user;
      },
   },
   methods: {
      can(permission) {
         return this.authUser?.permissions?.includes(permission);
      },
      fetchRoles() {
         axios.get('/datatable/roles')
            .then(({data}) => {
               this.roles = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred when fetching the roles.')
         })
      },
      fetchPermissions() {
         axios.get('/datatable/permissions')
            .then(({data}) => {
               this.permissions = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred when fetching the permissions.')
         })
      },
      showCreateUserModal() {
         const modalElement = this.$refs.createUserModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      createUser() {
         this.form.post('/admin/users', {
            onSuccess: () => {
               this.form.reset(); // Reset the form on success
               this.form.clearErrors();
               this.$refs.usersTable.reloadTable();
               const modalElement = this.$refs.createUserModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('User Created Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      editUser(rowData) {
         this.editForm.id = rowData.hashid; // Assign the ID manually
         this.editForm.name = rowData.name;
         this.editForm.username = rowData.username;
         this.editForm.email = rowData.email;
         this.editForm.phone = rowData.phone;
         this.editForm.activated = rowData.activated;
         this.editForm.is_admin = rowData.is_admin;
         this.editForm.is_teacher = rowData.is_teacher;
         this.editForm.is_parent = rowData.is_parent;
         
         if (rowData.roles.length) {
            this.editForm.role_id = rowData.roles[0].id
         }
         
         const modalElement = this.$refs.editUserModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      updateUser() {
         this.editForm.patch('/admin/users/' + this.editForm.id, {
            onSuccess: () => {
               this.editForm.reset(); // Reset the form on success
               this.editForm.clearErrors();
               this.$refs.usersTable.reloadTable();
               const modalElement = this.$refs.editUserModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('User Updated Successfully', 'Success')
            },
            onError: (errors) => {
               console.log(errors);
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         })
      },
      editPermissions(user) {
         this.user = user
         this.permissionForm.id = user.id
         this.userPermissions = user.permissions
         this.permissionForm.permissions = this.userPermissions.map(permission => permission.id)
         
         const modalElement = this.$refs.editUserPermissions;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      updatePermissions() {
         this.permissionForm.post('/admin/users/' + this.user.hashid + '/permissions', {
            onSuccess: () => {
               this.permissionForm.reset(); // Reset the form on success
               this.permissionForm.clearErrors();
               this.$refs.usersTable.reloadTable();
               const modalElement = this.$refs.editUserPermissions;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('User Permissions Updated', 'Success')
            },
            onError: (errors) => {
               // console.log("Permission Form Errors:", errors)
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      toggleShow() {
         this.showPassword = !this.showPassword;
      },
      applyFilter: _debounce(function () {
         this.$refs.usersTable.reloadTable();
      }, 800),
      formCleanUp() {
         this.form.reset()
      },
      editFormCleanUp() {
         this.editForm.reset()
      },
      permissionFormCleanUp() {
         this.permissionForm.reset()
      }
   },
};
</script>

<style scoped>
</style>
