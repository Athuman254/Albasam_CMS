<template>

   <Head title="Payroll Run" />
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Payroll Run</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Payroll Run
               </li>
            </ol>
         </nav>
         <div class="col-lg-12">
            <div class="card">
               <div class="card-header flex-column flex-md-row">
                  <div class="row row-gap-1">
                     <div class="col-md-3 col-9">
                        <input type="search" id="search" class="form-control bg-muted-lt rounded-2"
                           placeholder="Search..." v-model="appendParams.filter.search" @input="applyFilter">
                     </div>
                     <div class="col-md-6 col-3 ms-lg-auto">
                        <div class="flex-wrap text-end">
                           <div class="card-action">
                              <button type="button" class="btn btn-primary d-none d-sm-inline-block"
                                 @click="openRunModal">
                                 <i class="bx bx-plus-circle me-2"></i>
                                 Run
                              </button>
                              <button type="button" class="btn btn-primary btn-icon d-sm-none" @click="openRunModal">
                                 <i class="bx bx-plus"></i>
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- Table Section -->
               <div class="card-body">
                  <!-- Bulk Actions -->
                  <div class="mb-3" v-if="selectedItems.length > 0">
                     <div class="alert alert-info d-flex align-items-center">
                        <i class="bx bx-info-circle me-2"></i>
                        <span>{{ selectedItems.length }} Employee(s) selected</span>
                        <div class="ms-auto">
                           <!-- <button class="btn btn-sm btn-success me-2" @click="processBulkPayroll">
                    <i class="bx bx-play me-1"></i>Process Selected
                  </button>
                  <button class="btn btn-sm btn-danger" @click="deleteBulkPayroll">
                    <i class="bx bx-trash me-1"></i>Delete Selected
                  </button> -->
                        </div>
                     </div>
                  </div>

                  <div class="table-responsive">
                     <table class="table table-striped table-hover">
                        <thead class="table-drk">
                           <tr>
                              <th width="50">
                                 <div>
                                    <input class="form-check-input" type="checkbox" id="selectAll" v-model="selectAll"
                                       @change="toggleSelectAll">
                                    <!-- <label class="form-check-label" for="selectAll"></label> -->
                                 </div>
                              </th>
                              <th>Employee ID</th>
                              <th>Employee Name</th>
                              <!-- <th>Department</th> -->
                              <th>Employee Type</th>
                              <th>Income Salary</th>
                           </tr>
                        </thead>
                        <tbody>
                           <tr v-for="payroll in employees" :key="payroll.id">
                              <td>
                                 <div class="form-check">
                                    <input class="form-check-input" type="checkbox" :id="`check-${payroll.id}`"
                                       :value="payroll.id" v-model="selectedItems" @change="updateSelectAll">
                                    <label class="form-check-label" :for="`check-${payroll.id}`"></label>
                                 </div>
                              </td>
                              <td>{{ payroll.staff_number }}</td>
                              <td>
                                 <div class="d-flex align-items-center">
                                    <!-- {{ payroll }} -->
                                    <img :src="getAvater(payroll.first_name)" :alt="payroll.name"
                                       class="avatar avatar-sm me-2 rounded-circle">
                                    <strong>{{ payroll.first_name }}</strong>
                                 </div>
                              </td>
                              <!-- <td>{{ payroll.department }}</td> -->
                              <td>{{ payroll.employment_type.name }}</td>
                              <td>
                                 <span class="badge bg-success">KES {{ formatCurrency(payroll.gross_salary) }}</span>
                              </td>

                           </tr>
                           <tr v-if="employees.length === 0">
                              <td colspan="11" class="text-center py-4">
                                 <div class="empty">
                                    <div class="empty-img">
                                       <i class="bx bx-receipt" style="font-size: 3rem; color: #ccc;"></i>
                                    </div>
                                    <p class="empty-title">No payroll employees found</p>
                                    <p class="empty-subtitle text-muted">
                                       Get started by selecting employees to be in payroll.
                                    </p>
                                 </div>
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </div>


                  <div class="d-flex justify-content-between align-items-center mt-3" v-if="employees.length > 0">
                     <div class="text-muted">
                        Showing {{ ((currentPage - 1) * itemsPerPage) + 1 }} to
                        {{ Math.min(currentPage * itemsPerPage, totalItems) }} of {{ totalItems }} entries
                     </div>

                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Create Modal -->
      <div class="modal fade" id="create-run-modal" data-bs-backdrop="static" tabindex="-1"
         aria-labelledby="create-run-modal-label" aria-hidden="true" ref="runModal">
         <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="create-run-modal-label">Run</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                     @click.prevent="formCleanUp"></button>
               </div>
               <form id="createForm" @submit.prevent="processPayroll">
                  <div class="modal-body">
                     <div class="mb-3">
                        <label for="date" class="form-label">Name</label>
                        <input id="date" type="date" v-model="form.date" class="form-control">
                        <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
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

   </DefaultLayout>
</template>

<script setup>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { Modal } from 'bootstrap';
import _debounce from 'lodash/debounce';
import { ref, computed, watch, onMounted } from 'vue'
import axios from "axios";
import { progress } from "izitoast/dist/js/iziToast.min";

const appendParams = {
   filter: {
      search: '',
   }
};

const selectedItems = ref([]);
const selectAll = ref(false);
const employees = ref([])
const currentPage = ref(1);
const itemsPerPage = ref(100);
const runModal = ref(null)
const totalItems = computed(() => employees.value.length);

const colors = [
   '007bff',
   '28a745',
   'ffc107',
   'dc3545',
   '6f42c1'
]
// Methods
const openRunModal = () => {
   Modal.getOrCreateInstance(runModal.value).show()
}
const processPayroll = () => {
   alert('contin')
}
const form = useForm({
   date: ''
})
const getAvater = (name) => {
   return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=${getBgColor()}&color=fff`
}
const getBgColor = () => {
   let index = Math.floor(Math.random() * colors.length)
   return colors[index]
}
const toggleSelectAll = () => {
   if (selectAll.value) {
      selectedItems.value = employees.value.map(item => item.id);
   } else {
      selectedItems.value = [];
   }
};
const getEmployees = () => {
   axios.get('/employees-payroll')
      .then((res) => {
         employees.value = res.data.data
      })
      .catch((error) => {
         console.log(error)
      })
}
const updateSelectAll = () => {
   selectAll.value = selectedItems.value.length === employees.value.length;
};

const formatCurrency = (amount) => {
   return new Intl.NumberFormat('KES').format(amount);
};


const applyFilter = _debounce(() => {
   console.log('Applying filter:', appendParams.filter.search);
}, 300);

const formCleanUp = () => form.reset()

watch(selectedItems, () => {
   updateSelectAll();
}, { deep: true });

onMounted(() => {
   getEmployees()
})
</script>
