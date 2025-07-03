<template>
   <div>
      <div class="d-flex align-items-center mb-3 pb-1">
         <h4 class="mb-0">Work History Details</h4>
         <div class="ms-auto">
            <ul class="list-inline mb-0 d-flex align-items-end">
               <li class="list-inline-item">
                  <button type="button" class="btn btn-primary" @click.prevent="showCreateEmergencyContactModal">
                     Add
                  </button>
               </li>
            </ul>
         </div>
      </div>
      
      <div v-if="workHistories.length === 0" class="alert alert-info">
         <i class='icon-base bx bx-briefcase me-2'></i>
         Previous work experience and employment history will be displayed here when available.
      </div>
      <table v-else class="table table-sm table-bordered text-nowrap" style="max-width: inherit;">
         <thead>
         <tr>
            <th class="p-2 fw-medium text-heading" style="width: 40%;">Name</th>
            <th class="p-2 fw-medium text-heading" style="width: 25%;">Start Date</th>
            <th class="p-2 fw-medium text-heading" style="width: 25%;">End Date</th>
            <th class="p-2 fw-medium text-heading" style="width: 10%;"></th>
         </tr>
         </thead>
         <tbody>
         <tr v-for="(history, index) in workHistories" :key="index">
            <td class="p-2">{{ history.institution_name ?? '-' }}</td>
            <td class="p-2">{{ history.start_date ?? '-' }}</td>
            <td class="p-2">{{ history.end_date ?? '-' }}</td>
            <td class="p-2">
               <div class="dropdown">
                  <button type="button" class="btn btn-icon" data-bs-toggle="dropdown">
                     <i class="icon-base bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-start">
                     <button type="button" class="dropdown-item" @click.prevent="showEditWorkHistoryModal(history)">
                        <i class="icon-base bx bx-edit-alt me-2"></i>Edit
                     </button>
                     <button type="button" class="dropdown-item text-danger" @click.prevent="deleteWorkHistory(history)">
                        <i class="icon-base bx bx-trash me-2"></i>Delete
                     </button>
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
      id="create-work-history-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="create-work-history-modal-label"
      aria-hidden="true"
      ref="createWorkHistoryModal"
   >
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="create-work-history-modal-label">Add Work History</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                  @click="formCleanUp"
               ></button>
            </div>
            <div class="modal-body">
               <div id="createForm">
                  <div class="mb-3">
                     <label for="institutionName" class="form-label">Institution</label>
                     <input id="institutionName" type="text" v-model="form.institution_name" class="form-control">
                     <div v-if="form.errors.institution_name" class="text-danger">{{ form.errors.institution_name }}</div>
                  </div>
                  <div class="mb-3">
                     <label for="startDate" class="form-label">Start Date</label>
                     <date-picker
                        id="startDate"
                        form-class="shadow-sm"
                        :value="form.start_date"
                        :max-date="new Date()"
                        @on-change="function(dateObj, dateStr) {
                           form.start_date = dateStr
                        }"
                     ></date-picker>
                     <div v-if="form.errors.start_date" class="text-danger">{{ form.errors.start_date }}</div>
                  </div>
                  <div class="mb-3">
                     <label for="endDate" class="form-label">End Date</label>
                     <date-picker
                        id="endDate"
                        form-class="shadow-sm"
                        :value="form.end_date"
                        :max-date="new Date()"
                        @on-change="function(dateObj, dateStr) {
                           form.end_date = dateStr
                        }"
                     ></date-picker>
                     <div v-if="form.errors.end_date" class="text-danger">{{ form.errors.end_date }}</div>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" @click="formCleanUp" :disabled="form.processing">
                  Close
               </button>
               <button type="button" class="btn btn-primary" @click.prevent="submit" :disabled="form.processing">
                  Submit
               </button>
            </div>
         </div>
      </div>
   </div>
   
   <!-- Edit Modal -->
   <div
      class="modal fade"
      id="edit-work-history-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="edit-work-history-modal-label"
      aria-hidden="true"
      ref="editWorkHistoryModal"
   >
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="edit-work-history-modal-label">Edit Work History</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                       @click="editFormCleanUp"
               ></button>
            </div>
            <div class="modal-body">
               <div id="createForm">
                  <div class="mb-3">
                     <label for="institutionName" class="form-label">Institution</label>
                     <input id="institutionName" type="text" v-model="editForm.institution_name" class="form-control">
                     <div v-if="editForm.errors.institution_name" class="text-danger">{{ editForm.errors.institution_name }}</div>
                  </div>
                  <div class="mb-3">
                     <label for="startDate" class="form-label">Start Date</label>
                     <date-picker
                        id="startDate"
                        form-class="shadow-sm"
                        :value="editForm.start_date"
                        :max-date="new Date()"
                        @on-change="function(dateObj, dateStr) {
                           editForm.start_date = dateStr
                        }"
                     ></date-picker>
                     <div v-if="editForm.errors.start_date" class="text-danger">{{ editForm.errors.start_date }}</div>
                  </div>
                  <div class="mb-3">
                     <label for="endDate" class="form-label">End Date</label>
                     <date-picker
                        id="endDate"
                        form-class="shadow-sm"
                        :value="editForm.end_date"
                        :max-date="new Date()"
                        @on-change="function(dateObj, dateStr) {
                           editForm.end_date = dateStr
                        }"
                     ></date-picker>
                     <div v-if="editForm.errors.end_date" class="text-danger">{{ editForm.errors.end_date }}</div>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" @click="editFormCleanUp" :disabled="editForm.processing">
                  Close
               </button>
               <button type="button" class="btn btn-primary" @click.prevent="update" :disabled="editForm.processing">
                  Submit
               </button>
            </div>
         </div>
      </div>
   </div>
</template>

<script setup>
import {inject, onMounted, ref} from "vue";
import functions from "@/Functions/Functions.js";
import axios from "axios";
import {Modal} from "bootstrap";
import {router, useForm} from "@inertiajs/vue3";

const props = defineProps({
   employee: {
      type: Object,
      required: true
   }
})
const toast = inject('toast');

const workHistories = ref(() => {})

const fetchWorkHistoryDetails = () => {
   const url = functions.buildUrl(route('employee.datatable.work-histories'),{ filter:{employee_id: props.employee.id}})
   axios.get(url)
      .then((response)=>{
         workHistories.value = response.data.data
      }).catch((error)=>{
      console.error(error)
   })
}

const form = useForm({
   institution_name: null,
   start_date: null,
   end_date: null,
   employee_id: props.employee.id,
});

const editForm = useForm({
   id: null,
   institution_name: null,
   start_date: null,
   end_date: null,
   employee_id: props.employee.id,
});

const createWorkHistoryModal = ref(null);

const showCreateEmergencyContactModal = () => {
   const modalInstance = Modal.getOrCreateInstance(createWorkHistoryModal.value);
   modalInstance.show();
}

const submit = () => {
   form.post(route('employee.work-histories.store'), {
      onSuccess: () => {
         form.reset();
         form.clearErrors();
         fetchWorkHistoryDetails();
         const modalInstance = Modal.getOrCreateInstance(createWorkHistoryModal.value);
         modalInstance.hide();
         toast.success('Work history saved!', 'Success');
      },
      onError: (error) => {
         console.log(error)
         toast.error('Something went wrong!', 'Error')
      },
      onFinish: () => {}
   })
}

const editWorkHistoryModal = ref(null);

const showEditWorkHistoryModal = (history) => {
   editForm.id = history.hashid;
   editForm.institution_name = history.institution_name;
   editForm.start_date = history.start_date;
   editForm.end_date = history.end_date;
   const modalInstance = Modal.getOrCreateInstance(editWorkHistoryModal.value);
   modalInstance.show();
}

const update = () => {
   editForm.patch(route('employee.work-histories.update', editForm.id), {
      onSuccess: () => {
         editForm.reset();
         editForm.clearErrors();
         fetchWorkHistoryDetails();
         const modalInstance = Modal.getOrCreateInstance(editWorkHistoryModal.value);
         modalInstance.hide();
         toast.success('Work history updated!', 'Success');
      },
      onError: (error) => {
         console.log(error)
         toast.error('Something went wrong!', 'Error')
      },
      onFinish: () => {}
   })
}

const deleteWorkHistory = (history) => {
   // toast.question(`Are you sure? Delete ${contact.name} details?`, 'You are deleting an emergency contact!').then(() => {
   router.delete(route('employee.work-histories.destroy', history.hashid), {
      preserveState: true,
      onSuccess: () => {
         toast.success('Work history deleted!', 'Success');
         setTimeout(() => {
            fetchWorkHistoryDetails();
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

const formCleanUp = () => {
   form.reset();
   form.clearErrors();
}

const editFormCleanUp = () => {
   editForm.reset();
   editForm.clearErrors();
}

onMounted(() => {
   fetchWorkHistoryDetails()
})
</script>

<style scoped></style>
