<template>
   <div>
      <div class="d-flex align-items-center mb-3 pb-1">
         <h4 class="mb-0">Qualification Details</h4>
         <div class="ms-auto">
            <ul class="list-inline mb-0 d-flex align-items-end">
               <li class="list-inline-item">
                  <button type="button" class="btn btn-primary" @click.prevent="showCreateQualificationModal">
                     Add
                  </button>
               </li>
            </ul>
         </div>
      </div>
      <div v-if="qualificationDetails.length === 0" class="alert alert-info">
         <i class='icon-base bx bxs-graduation me-2'></i>
         Educational qualifications and certifications will be displayed here when available.
      </div>
      <table v-else class="table table-sm table-bordered text-nowrap" style="max-width: inherit;">
         <thead>
         <tr>
            <th class="p-2 fw-medium text-heading" style="width: 30%;">Institution</th>
            <th class="p-2 fw-medium text-heading" style="width: 30%;">Course</th>
            <th class="p-2 fw-medium text-heading" style="width: 15%;">Qualification</th>
            <th class="p-2 fw-medium text-heading" style="width: 15%;">Completion Year</th>
            <th class="p-2 fw-medium text-heading" style="width: 10%;"></th>
         </tr>
         </thead>
         <tbody>
         <tr v-for="(qualification, index) in qualificationDetails" :key="index">
            <td class="p-2">{{ qualification.institution_name }}</td>
            <td class="p-2">{{ qualification.course_name }}</td>
            <td class="p-2">{{ qualification.qualification_type?.name }}</td>
            <td class="p-2">{{ qualification.year_of_completion }}</td>
            <td class="p-2">
               <div class="dropdown">
                  <button type="button" class="btn btn-icon" data-bs-toggle="dropdown">
                     <i class="icon-base bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-start">
                     <a class="dropdown-item" href="#" @click.prevent="showEditQualificationModal(qualification)">
                        <i class="icon-base bx bx-edit-alt me-2"></i>Edit
                     </a>
                     <a class="dropdown-item text-danger" href="#" @click.prevent="deleteQualification(qualification)">
                        <i class="icon-base bx bx-trash me-2"></i>Delete
                     </a>
                  </div>
               </div>
            </td>
         </tr>
         </tbody>
      </table>
   </div>
   
   <!-- Create Modal -->
   <div
      class="modal fade"
      id="create-qualification-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="create-qualification-modal-label"
      aria-hidden="true"
      ref="createQualificationModal"
   >
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="create-qualification-modal-label">Add Qualification</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="formCleanUp">
               </button>
            </div>
            <div class="modal-body">
               <div id="createForm">
                  <div class="mb-3">
                     <label for="institutionName" class="form-label">Institution</label>
                     <input id="institutionName" type="text" v-model="form.institution_name" class="form-control">
                     <div v-if="form.errors.institution_name" class="text-danger">{{ form.errors.institution_name }}</div>
                  </div>
                  <div class="mb-3">
                     <label for="courseName" class="form-label">Course</label>
                     <input id="courseName" type="text" v-model="form.course_name" class="form-control">
                     <div v-if="form.errors.course_name" class="text-danger">{{ form.errors.course_name }}</div>
                  </div>
                  <div class="mb-3">
                     <label for="qualificationTypeId" class="form-label">Qualification</label>
                     <v-select
                        id="qualificationTypeId"
                        v-model="form.qualification_type_id"
                        :options="types"
                        label="name"
                        :reduce="option => option.id"
                     />
                     <div v-if="form.errors.qualification_type_id" class="text-danger">{{ form.errors.qualification_type_id }}</div>
                  </div>
                  <div class="mb-3">
                     <label for="yearOfCompletion" class="form-label">Year Of Completion</label>
                     <input id="yearOfCompletion" type="text" v-model="form.year_of_completion" class="form-control">
                     <div v-if="form.errors.year_of_completion" class="text-danger">{{ form.errors.year_of_completion }}</div>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" @click="formCleanUp" :disabled="form.processing">
                  Close
               </button>
               <button type="button" class="btn btn-primary" @click.prevent="submitQualification" :disabled="form.processing">
                  Submit
               </button>
            </div>
         </div>
      </div>
   </div>
   
   <!-- Update Modal -->
   <div
      class="modal fade"
      id="edit-qualification-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="edit-qualification-modal-label"
      aria-hidden="true"
      ref="editQualificationModal"
   >
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="edit-qualification-modal-label">Edit Qualification</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="editFormCleanUp">
               </button>
            </div>
            <div class="modal-body">
               <div id="createForm">
                  <div class="mb-3">
                     <label for="institutionName" class="form-label">Institution</label>
                     <input id="institutionName" type="text" v-model="editForm.institution_name" class="form-control">
                     <div v-if="editForm.errors.institution_name" class="text-danger">{{ editForm.errors.institution_name }}</div>
                  </div>
                  <div class="mb-3">
                     <label for="courseName" class="form-label">Course</label>
                     <input id="courseName" type="text" v-model="editForm.course_name" class="form-control">
                     <div v-if="editForm.errors.course_name" class="text-danger">{{ editForm.errors.course_name }}</div>
                  </div>
                  <div class="mb-3">
                     <label for="qualificationTypeId" class="form-label">Qualification</label>
                     <v-select
                        id="qualificationTypeId"
                        v-model="editForm.qualification_type_id"
                        :options="types"
                        label="name"
                        :reduce="option => option.id"
                     />
                     <div v-if="editForm.errors.qualification_type_id" class="text-danger">{{ editForm.errors.qualification_type_id }}</div>
                  </div>
                  <div class="mb-3">
                     <label for="yearOfCompletion" class="form-label">Year Of Completion</label>
                     <input id="yearOfCompletion" type="text" v-model="editForm.year_of_completion" class="form-control">
                     <div v-if="editForm.errors.year_of_completion" class="text-danger">{{ editForm.errors.year_of_completion }}</div>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" @click="editFormCleanUp" :disabled="editForm.processing">
                  Close
               </button>
               <button type="button" class="btn btn-primary" @click.prevent="updateQualification" :disabled="editForm.processing">
                  Submit
               </button>
            </div>
         </div>
      </div>
   </div>
</template>

<script setup>
import {ref, onMounted, inject} from "vue"
import functions from "@/Functions/Functions.js";
import axios from "axios";
import {Modal} from 'bootstrap';
import {router, useForm} from "@inertiajs/vue3";

const props = defineProps({
   employee:{
      type:Object,
      required:true
   }
})
const toast = inject('toast')

const qualificationDetails = ref(() => {})

const fetchQualificationDetails = () => {
   const url = functions.buildUrl(route('employee.datatable.qualifications'),{ filter:{employee_id: props.employee.id}})
   axios.get(url).then((response) => {
      qualificationDetails.value = response.data.data
   }).catch((error) => {
      console.error(error);
   })
}

const types = ref([]);

const fetchQualificationTypes = () => {
   axios.get(route('employee.datatable.qualification-types')).then((response) => {
      types.value = response.data.data
   }).catch((error) => {
      console.error(error);
   })
}

const form = useForm({
   institution_name: null,
   course_name: null,
   year_of_completion: null,
   qualification_type_id: null,
   employee_id: props.employee.id,
});

const createQualificationModal = ref(null)

const showCreateQualificationModal = () => {
   const modalInstance = Modal.getOrCreateInstance(createQualificationModal.value);
   modalInstance.show();
}

const submitQualification = () => {
   form.post(route('employee.qualifications.store'), {
      onSuccess: () => {
         form.reset();
         form.clearErrors();
         fetchQualificationDetails();
         const modalInstance = Modal.getOrCreateInstance(createQualificationModal.value);
         modalInstance.hide();
         toast.success('Qualification details saved!', 'Success');
      },
      onError: (error) => {
         console.log(error)
         toast.error('Something went wrong!', 'Error')
      },
      onFinish: () => {}
   })
}

const formCleanUp = () => {
   form.reset();
   form.clearErrors();
}

const editForm = useForm({
   id: null,
   institution_name: null,
   course_name: null,
   year_of_completion: null,
   qualification_type_id: null,
   employee_id: props.employee.id,
});

const editQualificationModal = ref(null)

const showEditQualificationModal = (qualification) => {
   editForm.id = qualification.hashid;
   editForm.institution_name = qualification.institution_name;
   editForm.course_name = qualification.course_name;
   editForm.year_of_completion = qualification.year_of_completion;
   editForm.qualification_type_id = qualification.qualification_type_id;
   const modalInstance = Modal.getOrCreateInstance(editQualificationModal.value);
   modalInstance.show();
}

const updateQualification = () => {
   editForm.patch(route('employee.qualifications.update', editForm.id), {
      onSuccess: () => {
         editForm.reset();
         editForm.clearErrors();
         fetchQualificationDetails();
         const modalInstance = Modal.getOrCreateInstance(editQualificationModal.value);
         modalInstance.hide();
         toast.success('Qualification details updated!', 'Success');
      },
      onError: (error) => {
         console.log(error)
         toast.error('Something went wrong!', 'Error')
      },
      onFinish: () => {}
   })
}

const editFormCleanUp = () => {
   editForm.reset();
   editForm.clearErrors();
}

const deleteQualification = (qualification) => {
   // toast.question(`Are you sure? Delete ${contact.name} details?`, 'You are deleting an emergency contact!').then(() => {
   router.delete(route('employee.qualifications.destroy', qualification.hashid), {
      preserveState: true,
      onSuccess: () => {
         toast.success('Qualification details deleted!', 'Success');
         setTimeout(() => {
            fetchQualificationDetails();
         }, 500);
      },
      onError: (error) => {
         console.log(error)
         toast.error('Something went wrong!', 'Error')
      },
      onFinish: () => {}
   });
   // })
}

onMounted(() => {
   fetchQualificationDetails()
   fetchQualificationTypes()
})
</script>
