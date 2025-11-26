<template>
   <div>
      <div class="d-flex align-items-center mb-3 pb-1">
         <h4 class="mb-0">Emergency Contact Details</h4>
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
      <div class="alert alert-info" v-if="emergencyContacts.length === 0">
         <i class='icon-base bx bx-info-circle me-2'></i>
         Emergency contact information will be displayed here when available.
      </div>
      <table class="table table-sm table-bordered text-nowrap" style="max-width: inherit;" v-else>
         <thead>
         <tr>
            <th class="p-2 fw-medium text-heading" style="width: 30%;">Name</th>
            <th class="p-2 fw-medium text-heading" style="width: 25%;">Email</th>
            <th class="p-2 fw-medium text-heading" style="width: 15%;">Phone</th>
            <th class="p-2 fw-medium text-heading" style="width: 15%;">Relationship</th>
            <th class="p-2 fw-medium text-heading" style="width: 10%;"></th>
         </tr>
         </thead>
         <tbody>
         <tr v-for="(contact, index) in emergencyContacts" :key="index">
            <td class="p-2">{{ contact.name ?? '-' }}</td>
            <td class="p-2">{{ contact.email ?? '-' }}</td>
            <td class="p-2">{{ contact.phone ?? '-' }}</td>
            <td class="p-2">{{ contact.relationship?.name ?? '-' }}</td>
            <td class="p-2">
               <div class="dropdown">
                  <button type="button" class="btn btn-icon" data-bs-toggle="dropdown">
                     <i class="icon-base bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-start">
                     <button class="dropdown-item" type="button" @click.prevent="showEditEmergencyContactModal(contact)">
                        <i class="icon-base bx bx-edit-alt me-2"></i>Edit
                     </button>
                     <Link class="dropdown-item text-danger" type="button" @click.prevent="deleteEmergencyContact(contact)">
                        <i class="icon-base bx bx-trash me-2"></i>Delete
                     </Link>
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
      id="create-emergency-contact-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="create-emergency-contact-modal-label"
      aria-hidden="true"
      ref="newEmergencyContactModal"
   >
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="create-emergency-contact-modal-label">Add Emergency Contact</h5>
               <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                  @click="formCleanUp"
               ></button>
            </div>
            <div class="modal-body">
               <div id="createForm">
                  <div class="mb-3">
                     <label for="name" class="form-label">Name</label>
                     <input id="name" type="text" v-model="form.name" class="form-control">
                     <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
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
                     <label for="relationshipId" class="form-label">Relationship</label>
                     <v-select
                        id="relationshipId"
                        v-model="form.relationship_id"
                        :options="relationships"
                        label="name"
                        :reduce="option => option.id"
                     />
                     <div v-if="form.errors.relationship_id" class="text-danger">{{ form.errors.relationship_id }}</div>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" @click="formCleanUp" :disabled="form.processing">
                  Close
               </button>
               <button type="button" class="btn btn-primary" @click.prevent="submitNewContact" :disabled="form.processing">
                  Submit
               </button>
            </div>
         </div>
      </div>
   </div>
   
   <!-- Update Modal -->
   <div
      class="modal fade"
      id="edit-rank-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="edit-emergency-contact-modal-label"
      aria-hidden="true"
      ref="editEmergencyContactModal"
   >
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="edit-emergency-contact-modal-label">Edit Emergency Contact</h5>
               <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                  @click="editFormCleanUp"
               ></button>
            </div>
            <div class="modal-body">
               <div id="createForm">
                  <div class="mb-3">
                     <label for="name" class="form-label">Name</label>
                     <input id="name" type="text" v-model="editForm.name" class="form-control">
                     <div v-if="editForm.errors.name" class="text-danger">{{ editForm.errors.name }}</div>
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
                     <label for="relationshipId" class="form-label">Relationship</label>
                     <v-select
                        id="relationshipId"
                        v-model="editForm.relationship_id"
                        :options="relationships"
                        label="name"
                        :reduce="option => option.id"
                     />
                     <div v-if="editForm.errors.relationship_id" class="text-danger">{{ editForm.errors.relationship_id }}</div>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" @click="editFormCleanUp" :disabled="editForm.processing">
                  Close
               </button>
               <button type="button" class="btn btn-primary" @click.prevent="updateEmergencyContact" :disabled="editForm.processing">
                  Submit
               </button>
            </div>
         </div>
      </div>
   </div>
</template>

<script setup>
import {ref, inject, onMounted} from 'vue';
import functions from "@/Functions/Functions";
import axios from "axios";
import {Link, useForm} from "@inertiajs/vue3";
import {Modal} from "bootstrap";
import { router } from '@inertiajs/vue3';

const props = defineProps({
   employee:{
      type:Object,
      required:true
   }
})
const toast = inject('toast');

const emergencyContacts = ref(() => {});

const fetchEmergencyContacts = () => {
   const url = functions.buildUrl(route('employee.datatable.contacts'),{ filter:{employee_id: props.employee.id}})
   axios.get(url).then((response) => {
      emergencyContacts.value = response.data.data
   }).catch((error) => {
      console.error(error)
   })
}

const relationships = ref([]);

const fetchRelationships = () => {
   axios.get(route('employee.datatable.relationships')).then((response) => {
      relationships.value = response.data.data
   }).catch((error) => {
      console.error(error)
   })
}

const form = useForm({
   name: null,
   email: null,
   phone: null,
   relationship_id: null,
   employee_id: props.employee.id,
});

const newEmergencyContactModal = ref(null)

const showCreateEmergencyContactModal = () => {
   const modalInstance = Modal.getOrCreateInstance(newEmergencyContactModal.value)
   modalInstance.show()
}

const submitNewContact = () => {
   form.post(route('employee.emergency-contacts.store'), {
      onSuccess: () => {
         form.reset();
         form.clearErrors();
         fetchEmergencyContacts();
         const modalInstance = Modal.getOrCreateInstance(newEmergencyContactModal.value);
         modalInstance.hide();
         toast.success('Emergency contact saved!', 'Success');
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
   name: null,
   email: null,
   phone: null,
   relationship_id: null,
   employee_id: props.employee.id,
});

const editEmergencyContactModal = ref(null);

const showEditEmergencyContactModal = (contact) => {
   editForm.id = contact.hashid;
   editForm.name = contact.name;
   editForm.email = contact.email;
   editForm.phone = contact.phone;
   editForm.relationship_id = contact.relationship_id;
   const modalInstance = Modal.getOrCreateInstance(editEmergencyContactModal.value);
   modalInstance.show();
}

const updateEmergencyContact = () => {
   editForm.patch(route('employee.emergency-contacts.update', editForm.id), {
      onSuccess: () => {
         editForm.reset();
         editForm.clearErrors();
         fetchEmergencyContacts();
         const modalInstance = Modal.getOrCreateInstance(editEmergencyContactModal.value);
         modalInstance.hide();
         toast.success('Emergency contact updated!', 'Success');
      },
      onError: (error) => {
         console.log(error)
         toast.error('Something went wrong!', 'Error')
      },
      onFinish: () => {}
   })
}

const deleteEmergencyContact = (contact) => {
   // toast.question(`Are you sure? Delete ${contact.name} details?`, 'You are deleting an emergency contact!').then(() => {
      router.delete(route('employee.emergency-contacts.destroy', contact.hashid), {
         preserveState: true,
         onSuccess: () => {
            toast.success('Emergency contact deleted!', 'Success');
            setTimeout(() => {
               fetchEmergencyContacts();
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

const editFormCleanUp = () => {
   editForm.reset();
   editForm.clearErrors();
}

onMounted(() => {
   fetchRelationships()
   fetchEmergencyContacts()
})
</script>
