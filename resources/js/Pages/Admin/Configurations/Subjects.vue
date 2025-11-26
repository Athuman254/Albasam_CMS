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
                        <i class="icon-base bx bx-plus-circle me-2"></i>
                        Add Subject
                     </button>
                     
                     <button type="button" class="btn btn-primary btn-icon d-sm-none"
                             @click="showCreateSubjectModal">
                        <i class="icon-base bx bx-plus"></i>
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

         <template #skills_count="props">
            <span class="badge bg-primary">
               {{ props.rowData.skills_count || 0 }} Skills
            </span>
         </template>
         
         <template #actions="props">
            <div class="dropdown">
               <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                  <i class="icon-base bx bx-dots-vertical"></i>
               </button>
               <div class="dropdown-menu dropdown-menu-end">
                  <a class="dropdown-item" href="#" @click="editSubject(props.rowData)">
                     <i class="icon-base bx bx-edit-alt me-2"></i>Edit
                  </a>
                  <a class="dropdown-item" href="#" @click="manageSkills(props.rowData)">
                     <i class="icon-base bx bx-cog me-2"></i>Manage Skills
                  </a>
                  <!-- <a class="dropdown-item text-danger" href="#">
                     <i class="icon-base bx bx-trash me-2"></i>Delete
                  </a> -->
               </div>
            </div>
         </template>
      </VueTable>
   </div>
   
   <!-- Create Subject Modal -->
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
   
   <!-- Edit Subject Modal -->
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

   <!-- Manage Skills Modal -->
   <div
      class="modal fade"
      id="manage-skills-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="manage-skills-modal-label"
      aria-hidden="true"
      ref="manageSkillsModal"
   >
      <div class="modal-dialog modal-lg">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="manage-skills-modal-label">
                  Manage Skills - {{ currentSubject?.name }}
               </h5>
               <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                  @click="skillsFormCleanUp"
               ></button>
            </div>
            <div class="modal-body">
               <!-- Add New Skill Form -->
               <div class="card mb-4">
                  <div class="card-header">
                     <h6 class="card-title mb-0">Add New Skill</h6>
                  </div>
                  <div class="card-body">
                     <form @submit.prevent="createSkill">
                        <div class="row">
                           <div class="col-md-5">
                              <div class="mb-3">
                                 <label for="skillName" class="form-label">Skill Name *</label>
                                 <input 
                                    id="skillName" 
                                    type="text" 
                                    v-model="skillForm.name" 
                                    class="form-control" 
                                    placeholder="Enter skill name"
                                 >
                                 <div v-if="skillForm.errors.name" class="text-danger small">{{ skillForm.errors.name }}</div>
                              </div>
                           </div>
                           <div class="col-md-5">
                              <div class="mb-3">
                                 <label for="skillDescription" class="form-label">Description</label>
                                 <input 
                                    id="skillDescription" 
                                    type="text" 
                                    v-model="skillForm.description" 
                                    class="form-control" 
                                    placeholder="Enter skill description"
                                 >
                                 <div v-if="skillForm.errors.description" class="text-danger small">{{ skillForm.errors.description }}</div>
                              </div>
                           </div>
                           <div class="col-md-2">
                              <div class="mb-3">
                                 <label class="form-label">Active</label>
                                 <div class="form-check form-switch mt-2">
                                    <input 
                                       v-model="skillForm.is_active" 
                                       class="form-check-input" 
                                       type="checkbox" 
                                       checked
                                    >
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="text-end">
                           <button 
                              type="submit" 
                              class="btn btn-primary"
                              :disabled="skillForm.processing"
                           >
                              <span v-if="skillForm.processing" class="spinner-border spinner-border-sm me-2"></span>
                              Add Skill
                           </button>
                        </div>
                     </form>
                  </div>
               </div>

               <!-- Skills List -->
               <div class="card">
                  <div class="card-header">
                     <h6 class="card-title mb-0">Subject Skills ({{ skills.length }})</h6>
                  </div>
                  <div class="card-body">
                     <div v-if="loadingSkills" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                           <span class="visually-hidden">Loading...</span>
                        </div>
                     </div>
                     
                     <div v-else-if="skills.length === 0" class="text-center py-4">
                        <i class="icon-base bx bx-brain fs-1 text-muted mb-3"></i>
                        <p class="text-muted">No skills added yet. Add your first skill above.</p>
                     </div>
                     
                     <div v-else class="table-responsive">
                        <table class="table table-hover">
                           <thead>
                              <tr>
                                 <th>Name</th>
                                 <th>Description</th>
                                 <th>Status</th>
                                 <th>Used in Exams</th>
                                 <th>Actions</th>
                              </tr>
                           </thead>
                           <tbody>
                              <tr v-for="skill in skills" :key="skill.id">
                                 <td>
                                    <div class="d-flex align-items-center">
                                       <i class="icon-base bx bx-brain me-2 text-primary"></i>
                                       <strong>{{ skill.name }}</strong>
                                    </div>
                                 </td>
                                 <td>
                                    <span class="text-muted">{{ skill.description || 'No description' }}</span>
                                 </td>
                                 <td>
                                    <span 
                                       :class="skill.is_active ? 'badge bg-success' : 'badge bg-danger'"
                                    >
                                       {{ skill.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                 </td>
                                 <td>
                                    <span class="badge bg-info">
                                       {{ skill.exams_count || 0 }} exams
                                    </span>
                                 </td>
                                 <td>
                                    <div class="btn-group btn-group-sm">
                                       <button 
                                          type="button" 
                                          class="btn btn-outline-primary"
                                          @click="editSkill(skill)"
                                       >
                                          <i class="icon-base bx bx-edit"></i>
                                       </button>
                                       <button 
                                          type="button" 
                                          class="btn btn-outline-danger"
                                          @click="deleteSkill(skill)"
                                          :disabled="skill.exams_count > 0"
                                       >
                                          <i class="icon-base bx bx-trash"></i>
                                       </button>
                                    </div>
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button
                  type="button"
                  class="btn btn-secondary"
                  data-bs-dismiss="modal"
                  @click="skillsFormCleanUp"
               >
                  Close
               </button>
            </div>
         </div>
      </div>
   </div>

   <!-- Edit Skill Modal -->
   <div
      class="modal fade"
      id="edit-skill-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="edit-skill-modal-label"
      aria-hidden="true"
      ref="editSkillModal"
   >
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="edit-skill-modal-label">Edit Skill</h5>
               <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                  @click="editSkillFormCleanUp"
               ></button>
            </div>
            <div class="modal-body">
               <form @submit.prevent="updateSkill">
                  <div class="mb-3">
                     <label for="editSkillName" class="form-label">Skill Name *</label>
                     <input 
                        id="editSkillName" 
                        type="text" 
                        v-model="editSkillForm.name" 
                        class="form-control"
                     >
                     <div v-if="editSkillForm.errors.name" class="text-danger small">{{ editSkillForm.errors.name }}</div>
                  </div>
                  
                  <div class="mb-3">
                     <label for="editSkillDescription" class="form-label">Description</label>
                     <textarea 
                        id="editSkillDescription" 
                        v-model="editSkillForm.description" 
                        class="form-control" 
                        rows="3"
                     ></textarea>
                     <div v-if="editSkillForm.errors.description" class="text-danger small">{{ editSkillForm.errors.description }}</div>
                  </div>
                  
                  <div class="mb-3">
                     <label class="form-check form-switch">
                        <input 
                           v-model="editSkillForm.is_active" 
                           class="form-check-input" 
                           type="checkbox"
                        >
                        <span class="form-check-label fw-bold">Active</span>
                     </label>
                     <div v-if="editSkillForm.errors.is_active" class="text-danger small">{{ editSkillForm.errors.is_active }}</div>
                  </div>
               </form>
            </div>
            <div class="modal-footer">
               <button
                  type="button"
                  class="btn btn-secondary me-2"
                  data-bs-dismiss="modal"
                  @click="editSkillFormCleanUp"
               >
                  Cancel
               </button>
               <button
                  type="button"
                  class="btn btn-primary"
                  @click.prevent="updateSkill"
                  :disabled="editSkillForm.processing"
               >
                  <span v-if="editSkillForm.processing" class="spinner-border spinner-border-sm me-2"></span>
                  Update Skill
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
               name: '__slot:skills_count',
               title: 'SKILLS',
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
         skillForm: useForm({
            name: '',
            description: '',
            is_active: true,
         }),
         editSkillForm: useForm({
            id: '',
            name: '',
            description: '',
            is_active: true,
         }),
         learningAreas: [
            {id: 1, name: 'Languages'},
            {id: 2, name: 'Sciences'},
            {id: 3, name: 'Applied Science'},
            {id: 4, name: 'Humanities'},
            {id: 5, name: 'Creative Arts'},
            {id: 6, name: 'Technical Subject'},
         ],
         currentSubject: null,
         skills: [],
         loadingSkills: false,
      };
   },
   methods: {
      showCreateSubjectModal() {
         const modalElement = this.$refs.createSubjectModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      createSubject() {
         this.form.post(route('admin.subjects.store'), {
            onSuccess: () => {
               this.form.reset();
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
         this.editForm.id = rowData.hashid;
         this.editForm.name = rowData.name;
         this.editForm.code = rowData.code;
         this.editForm.group = rowData.group;
         this.editForm.activated = rowData.activated;
         
         const modalElement = this.$refs.editSubjectModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      updateSubject() {
         this.editForm.patch(route('admin.subjects.store', this.editForm.id), {
            onSuccess: () => {
               this.editForm.reset();
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
      // Skills Management Methods
      manageSkills(subject) {
         this.currentSubject = subject;
         this.loadSkills();
         
         const modalElement = this.$refs.manageSkillsModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      async loadSkills() {
         this.loadingSkills = true;
         try {
            const response = await axios.get(route('admin.subjects.skills', this.currentSubject.hashid));
            this.skills = response.data;
         } catch (error) {
            this.$toast.error('Failed to load skills', 'Error');
            console.error('Error loading skills:', error);
         } finally {
            this.loadingSkills = false;
         }
      },
      createSkill() {
         this.skillForm.post(route('admin.subjects.skills.store', this.currentSubject.hashid), {
            onSuccess: () => {
               this.skillForm.reset();
               this.skillForm.clearErrors();
               this.loadSkills();
               this.$toast.success('Skill Created Successfully', 'Success');
            },
            onError: (errors) => {
               this.$toast.error('Failed to create skill', 'Error');
            },
         });
      },
      editSkill(skill) {
         this.editSkillForm.id = skill.hashid;
         this.editSkillForm.name = skill.name;
         this.editSkillForm.description = skill.description;
         this.editSkillForm.is_active = skill.is_active;
         
         const modalElement = this.$refs.editSkillModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      updateSkill() {
         this.editSkillForm.patch(route('admin.subjects.skills.update', {
            subject: this.currentSubject.hashid,
            skill: this.editSkillForm.id
         }), {
            onSuccess: () => {
               this.editSkillForm.reset();
               this.editSkillForm.clearErrors();
               this.loadSkills();
               const modalElement = this.$refs.editSkillModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Skill Updated Successfully', 'Success');
            },
            onError: (errors) => {
               this.$toast.error('Failed to update skill', 'Error');
            },
         });
      },
      async deleteSkill(skill) {
         if (skill.exams_count > 0) {
            this.$toast.error('Cannot delete skill. It is being used in exams.', 'Error');
            return;
         }
         
         if (!confirm('Are you sure you want to delete this skill?')) {
            return;
         }
         
         try {
            await axios.delete(route('admin.subjects.skills.destroy', {
               subject: this.currentSubject.hashid,
               skill: skill.hashid
            }));
            
            this.$toast.success('Skill Deleted Successfully', 'Success');
            this.loadSkills();
         } catch (error) {
            this.$toast.error('Failed to delete skill', 'Error');
            console.error('Error deleting skill:', error);
         }
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
      skillsFormCleanUp() {
         this.skillForm.reset();
         this.editSkillForm.reset();
         this.currentSubject = null;
         this.skills = [];
      },
      editSkillFormCleanUp() {
         this.editSkillForm.reset();
      },
   },
}
</script>