<template>
   <div class="col-xl-12">
      <div class="card">
         <div class="card-header flex-column flex-md-row">
            <div class="row row-gap-1">
               <div class="col-md-3 col-9">
                  <input type="search" id="search" class="form-control bg-muted-lt rounded-2" placeholder="Search..."
                     @input="applyFilter">
               </div>
               <div class="col-md-6 col-3 ms-lg-auto">
                  <div class="flex-wrap text-end">
                     <div class="card-action">
                        <button type="button" class="btn btn-primary d-none d-sm-inline-block" @click="openCreateModal">
                           <i class="bx bx-plus-circle me-2"></i>
                           Add Allowance
                        </button>
                        <button type="button" class="btn btn-primary btn-icon d-sm-none" @click="openCreateModal">
                           <i class="bx bx-plus"></i>
                        </button>
                     </div>
                  </div>
               </div>
            </div>
         </div>


      </div>

      <!-- Create Modal -->
      <div class="modal fade" id="create-rank-modal" data-bs-backdrop="static" tabindex="-1"
         aria-labelledby="create-rank-modal-label" aria-hidden="true" ref="createModal">
         <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="create-rank-modal-label">Add Allowance</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                     @click="formCleanUp"></button>
               </div>
               <div class="modal-body">
                  <form id="createForm" @submit.prevent="createEmployeeAllowance">
                     <div class="mb-3">
                        <label for="name" class="form-label">Employee( {{ form.employee_ids.length }} )</label>
                        <v-select multiple="true"  v-model="form.employee_ids" :options="divisions" label="name"
                           :reduce="option => option.id"></v-select>
                        <div v-if="form.errors.employee_ids" class="text-danger">{{ form.errors.employee_ids }}</div>
                     </div>

                     <div class="mb-3">
                        <label for="divisionId" class="form-label">Allowance</label>
                        <v-select  v-model="form.allowance_id" :options="divisions" label="name"
                           :reduce="option => option.id"></v-select>
                        <div v-if="form.errors.allowance_id" class="text-danger">{{ form.errors.allowance_id }}</div>
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
                  </form>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" @click="formCleanUp">
                     Close
                  </button>
                  <button type="button" class="btn btn-primary" @click.prevent="createRank">
                     Submit
                  </button>
               </div>
            </div>
         </div>
      </div>


   </div>
</template>
<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import { ref } from 'vue'
import { Modal } from 'bootstrap';
const createModal = ref(null)
const form = useForm({
   employee_ids: [],
   allowance_id: '',
   reason: '',
   amount: '',
   deduction_date: ''

})
// METHODES
const openCreateModal = () => {
   const modalInstance = Modal.getOrCreateInstance(createModal.value);
   modalInstance.show();
}
</script>
