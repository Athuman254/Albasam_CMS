<template>
   <AdminLayout>
      <Head title="Edit Employee" />

      <div class="container-xxl flex-grow-1 container-p-y">
         <h4 class="fw-bold py-3 mb-4">
            <span class="text-muted fw-light">Employees /</span> Edit Employee
         </h4>

         <div class="row">
            <div class="col-md-12">
               <div class="card mb-4">
                  <h5 class="card-header">Edit Employee Details</h5>
                  <div class="card-body">
                     <form @submit.prevent="submit">
                        <!-- Personal Information -->
                        <div class="divider text-start">
                           <div class="divider-text">Personal Information</div>
                        </div>
                        
                        <div class="row">
                           <div class="mb-3 col-md-6">
                              <label for="first_name" class="form-label">First Name</label>
                              <input class="form-control" type="text" id="first_name" v-model="form.first_name" :class="{ 'is-invalid': form.errors.first_name }" />
                              <div v-if="form.errors.first_name" class="invalid-feedback">{{ form.errors.first_name }}</div>
                           </div>
                           <div class="mb-3 col-md-6">
                              <label for="last_name" class="form-label">Last Name</label>
                              <input class="form-control" type="text" id="last_name" v-model="form.last_name" :class="{ 'is-invalid': form.errors.last_name }" />
                              <div v-if="form.errors.last_name" class="invalid-feedback">{{ form.errors.last_name }}</div>
                           </div>
                           <div class="mb-3 col-md-6">
                              <label for="email" class="form-label">E-mail</label>
                              <input class="form-control" type="text" id="email" v-model="form.email" :class="{ 'is-invalid': form.errors.email }" />
                              <div v-if="form.errors.email" class="invalid-feedback">{{ form.errors.email }}</div>
                           </div>
                           <div class="mb-3 col-md-6">
                              <label for="staff_number" class="form-label">Staff Number</label>
                              <input class="form-control" type="text" id="staff_number" v-model="form.staff_number" :class="{ 'is-invalid': form.errors.staff_number }" />
                              <div v-if="form.errors.staff_number" class="invalid-feedback">{{ form.errors.staff_number }}</div>
                           </div>
                           <div class="mb-3 col-md-6">
                              <label for="phone" class="form-label">Phone Number</label>
                              <input class="form-control" type="text" id="phone" v-model="form.primary_phone" :class="{ 'is-invalid': form.errors.primary_phone }" />
                              <div v-if="form.errors.primary_phone" class="invalid-feedback">{{ form.errors.primary_phone }}</div>
                           </div>
                        </div>

                        <!-- Role Assignment -->
                        <div class="divider text-start">
                           <div class="divider-text">Role & Designation</div>
                        </div>

                        <div class="row">
                           <div class="mb-3 col-md-12">
                              <label for="role" class="form-label">Staff Role</label>
                              <select id="role" class="form-select" v-model="form.role_id" :class="{ 'is-invalid': form.errors.role_id }">
                                 <option value="">Select Role</option>
                                 <option v-for="role in roles" :key="role.id" :value="role.id">
                                    {{ role.display_name }}
                                 </option>
                              </select>
                              <div v-if="selectedRoleDescription" class="form-text text-primary mt-1">
                                 <i class='bx bx-info-circle'></i> {{ selectedRoleDescription }}
                              </div>
                              <div v-if="form.errors.role_id" class="invalid-feedback">{{ form.errors.role_id }}</div>
                           </div>
                        </div>

                        <!-- Teacher Specific Fields -->
                        <div v-if="isTeacherRole" class="teacher-fields animate__animated animate__fadeIn">
                           <div class="alert alert-primary d-flex align-items-center" role="alert">
                              <i class='bx bx-chalkboard me-2'></i>
                              <div>
                                 Additional information required for teaching staff
                              </div>
                           </div>
                           <div class="row">
                              <div class="mb-3 col-md-6">
                                 <label for="subject_specialization" class="form-label">Subject Specialization</label>
                                 <input class="form-control" type="text" id="subject_specialization" v-model="form.subject_specialization" :class="{ 'is-invalid': form.errors.subject_specialization }" placeholder="e.g. Mathematics, Science" />
                                 <div v-if="form.errors.subject_specialization" class="invalid-feedback">{{ form.errors.subject_specialization }}</div>
                              </div>
                              <div class="mb-3 col-md-6">
                                 <label for="teaching_qualification" class="form-label">Qualification</label>
                                 <select id="teaching_qualification" class="form-select" v-model="form.teaching_qualification" :class="{ 'is-invalid': form.errors.teaching_qualification }">
                                    <option value="">Select Qualification</option>
                                    <option value="Certificate">Certificate</option>
                                    <option value="Diploma">Diploma</option>
                                    <option value="Bachelor's Degree">Bachelor's Degree</option>
                                    <option value="Master's Degree">Master's Degree</option>
                                    <option value="PhD">PhD</option>
                                 </select>
                                 <div v-if="form.errors.teaching_qualification" class="invalid-feedback">{{ form.errors.teaching_qualification }}</div>
                              </div>
                           </div>
                        </div>

                        <!-- System Access -->
                        <div class="divider text-start">
                           <div class="divider-text">System Access</div>
                        </div>

                        <div class="row">
                           <div class="mb-3 col-md-12">
                              <div class="form-check form-switch mb-2">
                                 <input class="form-check-input" type="checkbox" id="has_system_access" v-model="form.has_system_access">
                                 <label class="form-check-label" for="has_system_access">Grant System Access</label>
                              </div>
                              <div v-if="employee.user" class="form-text text-success">
                                 <i class='bx bx-check-circle'></i> User account currently active: <strong>{{ employee.user.username }}</strong>
                              </div>
                              <div v-else class="form-text">If enabled, a user account will be created for this employee.</div>
                           </div>

                           <div v-if="form.has_system_access" class="mb-3 col-md-6 animate__animated animate__fadeIn">
                              <label for="password" class="form-label">Password {{ employee.user ? '(Leave blank to keep current)' : '' }}</label>
                              <div class="input-group input-group-merge">
                                 <input :type="showPassword ? 'text' : 'password'" id="password" class="form-control" v-model="form.password" :class="{ 'is-invalid': form.errors.password }" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                                 <span class="input-group-text cursor-pointer" @click="showPassword = !showPassword">
                                    <i class="bx" :class="showPassword ? 'bx-hide' : 'bx-show'"></i>
                                 </span>
                                 <div v-if="form.errors.password" class="invalid-feedback">{{ form.errors.password }}</div>
                              </div>
                           </div>
                        </div>

                        <div class="mt-2">
                           <button type="submit" class="btn btn-primary me-2" :disabled="form.processing">
                              <span v-if="form.processing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                              Update Employee
                           </button>
                           <Link :href="route('admin.employees.index')" class="btn btn-outline-secondary">Cancel</Link>
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
   employee: Object,
   roles: Array,
   currentRole: Object,
});

const showPassword = ref(false);

const form = useForm({
   first_name: props.employee.first_name,
   last_name: props.employee.last_name,
   email: props.employee.email,
   staff_number: props.employee.staff_number,
   primary_phone: props.employee.primary_phone,
   role_id: props.employee.role_id || props.currentRole?.id || '',
   subject_specialization: props.employee.subject_specialization || '',
   teaching_qualification: props.employee.teaching_qualification || '',
   has_system_access: props.employee.has_system_access || !!props.employee.user,
   password: '',
});

const selectedRole = computed(() => {
   return props.roles.find(r => r.id === form.role_id);
});

const isTeacherRole = computed(() => {
   return selectedRole.value?.name === 'teacher';
});

const selectedRoleDescription = computed(() => {
   return selectedRole.value?.description || '';
});

const submit = () => {
   form.patch(route('admin.employees.update', props.employee.id), {
      onSuccess: () => form.reset('password'),
   });
};
</script>

<style scoped>
.teacher-fields {
   background-color: #f8f9fa;
   padding: 1.5rem;
   border-radius: 0.5rem;
   margin-bottom: 1.5rem;
   border: 1px dashed #696cff;
}
</style>
