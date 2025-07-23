<template>
   <div class="col-xl-12">
      <div class="card">
         <div class="card-header flex-column flex-md-row">
            <div class="row row-gap-1">
               <div class="col-md-3 col-9">
                  <input type="search" id="search" class="form-control bg-muted-lt rounded-2" placeholder="Search..."
                     v-model="appendParams.filter.search" @input="applyFilter">
               </div>
               <div class="col-md-6 col-3 ms-lg-auto">
                  <div class="flex-wrap text-end">
                     <div class="card-action">
                        <button type="button" class="btn btn-primary d-none d-sm-inline-block" @click="openCreateModal">
                           <i class="bx bx-plus-circle me-2"></i>
                           Add Deduction
                        </button>
                        <button type="button" class="btn btn-primary btn-icon d-sm-none" @click="openCreateModal">
                           <i class="bx bx-plus"></i>
                        </button>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <VueTable :fields="fields" api-url="datatable/deduction/adjustments" :append-params="appendParams"
            ref="deductionTable">
            <template #employee_id="props">
               <small class="text-info">{{ props.rowData.employee?.staff_number }}</small>
               <div>{{ props.rowData.employee.first_name }} - {{ props.rowData.employee.last_name }}</div>
            </template>
            <template #amount="props">
               <div>{{ props.rowData.amount / 100 }}</div>
            </template>
            <template #deducted="props">
               <span v-if="props.rowData.deducted" class="badge bg-success">
                  deducted
               </span>
               <span v-else-if="!props.rowData.deducted" class="badge bg-danger">
                  Not yet deducted
               </span>
               <span v-else class="badge bg-secondary">
                  Unknown
               </span>
            </template>

            <template #actions="props">
               <div class="dropdown">
                  <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                     <i class="icon-base bx bx-dots-vertical"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end">
                     <a v-if="!props.rowData.is_included" class="dropdown-item" href="#"
                        @click="openUpdateModal(props.rowData)">
                        <i class="icon-base bx bx-edit-alt me-2"></i>Edit
                     </a>
                  </div>
               </div>
            </template>
         </VueTable>
      </div>

      <!-- Create Modal -->
      <div class="modal fade" id="create-deduction-modal" data-bs-backdrop="static" tabindex="-1"
         aria-labelledby="create-deduction-modal-label" aria-hidden="true" ref="createModal">
         <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="create-deduction-modal-label">Add Deduction</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                     @click="formCleanUp"></button>
               </div>
               <form id="createForm" @submit.prevent="createEmployeeDeduction">
                  <div class="modal-body">
                     <div class="mb-3">
                        <!-- {{ employees }} -->
                        <label for="name" class="form-label">Employee( {{ form.employee_ids.length }} )</label>
                        <v-select :multiple="true" v-model="form.employee_ids" :options="employees" label="first_name"
                           :reduce="option => option.id"></v-select>
                        <div v-if="form.errors.employee_ids" class="text-danger">{{ form.errors.employee_ids }}</div>
                     </div>

                     <div class="mb-3">
                        <label for="divisionId" class="form-label">Deduction</label>
                        <v-select v-model="form.deduction_id" :options="deductions" label="name"
                           :reduce="option => option.id"></v-select>
                        <div v-if="form.errors.deduction_id" class="text-danger">{{ form.errors.deduction_id }}</div>
                     </div>

                     <div class="mb-3">
                        <label for="streamId" class="form-label">Reason</label>
                        <textarea v-model="form.reason" class="form-control"></textarea>
                        <div v-if="form.errors.reason" class="text-danger">{{ form.errors.reason }}</div>
                     </div>

                     <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="text" v-model="form.amount" class="form-control">

                        <div v-if="form.errors.amount" class="text-danger">{{ form.errors.amount }}</div>
                     </div>
                     <div class="mb-3">
                        <label for="amount" class="form-label">Date</label>
                        <input type="date" v-model="form.date" class="form-control">

                        <div v-if="form.errors.date" class="text-danger">{{ form.errors.date }}</div>
                     </div>
                  </div>
                  <div class="modal-footer">
                     <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" @click="formCleanUp">
                        Close
                     </button>
                     <button type="submit" class="btn btn-primary">
                        Submit
                     </button>
                  </div>
               </form>
            </div>
         </div>
      </div>
      <!-- Edit Modal -->
      <div class="modal fade" id="create-deduction-modal" data-bs-backdrop="static" tabindex="-1"
         aria-labelledby="create-deduction-modal-label" aria-hidden="true" ref="editModal">
         <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="create-deduction-modal-label">Edit Allowance</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                     @click="formCleanUp"></button>
               </div>
               <form id="editForm" @submit.prevent="updateEmployeeAllowance">
                  <div class="modal-body">
                     <div class="mb-3">
                        <!-- {{ employees }} -->
                        <label for="name" class="form-label">Employee</label>
                        <v-select v-model="editForm.employee_id" :options="employees" label="first_name"
                           :reduce="option => option.id"></v-select>
                        <div v-if="editForm.errors.employee_id" class="text-danger">{{ editForm.errors.employee_id }}
                        </div>
                     </div>

                     <div class="mb-3">
                        <label for="divisionId" class="form-label">Deduction</label>
                        <v-select v-model="editForm.deduction_id" :options="deductions" label="name"
                           :reduce="option => option.id"></v-select>
                        <div v-if="editForm.errors.deduction_id" class="text-danger">{{ editForm.errors.deduction_id }}
                        </div>
                     </div>

                     <div class="mb-3">
                        <label for="streamId" class="form-label">Reason</label>
                        <textarea v-model="editForm.reason" class="form-control"></textarea>
                        <div v-if="editForm.errors.reason" class="text-danger">{{ editForm.errors.reason }}</div>
                     </div>

                     <div class="mb-3">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="text" v-model="editForm.amount" class="form-control">

                        <div v-if="editForm.errors.amount" class="text-danger">{{ editForm.errors.amount }}</div>
                     </div>
                     <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" v-model="editForm.date" class="form-control">

                        <div v-if="editForm.errors.date" class="text-danger">{{ editForm.errors.date }}</div>
                     </div>
                  </div>
                  <div class="modal-footer">
                     <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" @click="formCleanUp">
                        Close
                     </button>
                     <button type="submit" class="btn btn-primary">
                        Submit
                     </button>
                  </div>
               </form>
            </div>
         </div>
      </div>

   </div>
</template>
<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import iziToast from "izitoast";
import { ref } from 'vue'
import _debounce from "lodash/debounce.js";
import { Modal } from 'bootstrap';

const props = defineProps(['employees', 'deductions'])
const createModal = ref(null)
const editModal = ref(null)
const deductionTable = ref(null)
const form = useForm({
   employee_ids: [],
   deduction_id: '',
   reason: '',
   amount: '',
   date: ''

})

const editForm = useForm({
   employee_id: null,
   id: null,
   deduction_id: null,
   reason: '',
   amount: null,
   date: '',

})

const appendParams = {
   filter: {
      search: '',
   }
};
const fields = [
   {
      name: '__slot:employee_id',
      title: 'EMPLOYEE',
   },
   {
      name: 'deduction.name',
      title: 'DEDUCTION',
   },
   {
      name: '__slot:amount',
      title: 'AMOUNT',
   },
   {
      name: 'month',
      title: 'MONTH',
   },
   {
      name: '__slot:deducted',
      title: 'STATUS',
   },
   {
      name: '__slot:actions',
      title: 'ACTIONS',
      // titleClass: 'text-end w-5',
      // dataClass: 'text-end w-5',
   },
];
// METHODES
const openCreateModal = () => {
   const modalInstance = Modal.getOrCreateInstance(createModal.value);
   modalInstance.show();
}
const closeCreateModal = () => {
   const modalInstance = Modal.getOrCreateInstance(createModal.value);
   modalInstance.hide();
}

const openUpdateModal = (rowData) => {
   editForm.id = rowData.hashid
   editForm.employee_id = rowData.employee_id,
      editForm.deduction_id = rowData.deduction_id
   editForm.amount = rowData.amount / 100
   editForm.reason = rowData.reason
   editForm.date = rowData.date

   const modalInstance = Modal.getOrCreateInstance(editModal.value);
   modalInstance.show();
}
const closeUpdateModal = () => {
   const modalInstance = Modal.getOrCreateInstance(editModal.value);
   modalInstance.hide();
}
const applyFilter = _debounce(function () {
   deductionTable.value.reloadTable();
}, 800)
// iziToast.error('dssdsd')
// iziToast()
// $toasterror('sdd')
// iziToast.error('An error occurred. Please try again', 'Error')
const createEmployeeDeduction = () => {
   form.post(route('admin.deduction.adjustment'), {
      onSuccess: () => {
         deductionTable.value.reloadTable();
         // this.$toast.success('Payroll Adjustment Allowance  has been created', 'Success')
         closeCreateModal()
      },
      onError: () => {
         // this.$toast.error('An error occurred. Please try again', 'Error')
      }
   })
}
const updateEmployeeAllowance = () => {
   editForm.patch(route('admin.deduction.adjustment.update', editForm.id), {
      onSuccess: () => {
         deductionTable.value.reloadTable();
         closeUpdateModal()
      },
      onError: () => {

      }
   })
}
</script>
