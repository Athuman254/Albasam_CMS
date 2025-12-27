<template>
  <DefaultLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success" class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded-md animate-fade-in-down">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class='bx bx-check-circle text-green-400 text-xl'></i>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-green-800">
                {{ $page.props.flash.success }}
              </p>
            </div>
            <div class="ml-auto pl-3">
              <button @click="$page.props.flash.success = null" class="inline-flex text-green-400 hover:text-green-600">
                <i class='bx bx-x text-xl'></i>
              </button>
            </div>
          </div>
        </div>

        <div v-if="$page.props.flash?.error" class="mb-4 bg-red-50 border-l-4 border-red-400 p-4 rounded-md animate-fade-in-down">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class='bx bx-error-circle text-red-400 text-xl'></i>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-red-800">
                {{ $page.props.flash.error }}
              </p>
            </div>
            <div class="ml-auto pl-3">
              <button @click="$page.props.flash.error = null" class="inline-flex text-red-400 hover:text-red-600">
                <i class='bx bx-x text-xl'></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Header -->
        <div class="mb-6">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Employees</h1>
              <p class="mt-1 text-sm text-gray-600">Manage all employees in the system</p>
            </div>
            <button
              @click="openCreateModal"
              class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
              Add Employee
            </button>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-4 mb-6">
          <div class="flex space-x-4">
            <div class="flex-1">
              <input
                type="text"
                v-model="filters.search"
                placeholder="Search employees..."
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
            <button
              @click="resetFilters"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500"
            >
              Reset
            </button>
          </div>
        </div>

        <!-- Employees Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Name
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Staff Number
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Employment Type
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="employee in employees.data" :key="employee.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">
                          {{ employee.first_name }} {{ employee.last_name }}
                        </div>
                        <div class="text-sm text-gray-500">
                          {{ employee.gender?.name }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ employee.staff_number }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ employee.employment_type?.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                        employee.employment_status?.name === 'Active' 
                          ? 'bg-green-100 text-green-800'
                          : 'bg-red-100 text-red-800'
                      ]"
                    >
                      {{ employee.employment_status?.name }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <Dropdown align="right" width="48">
                      <template #trigger>
                        <button class="text-gray-500 hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                          <i class='bx bx-dots-vertical-rounded text-xl'></i>
                        </button>
                      </template>

                      <template #content>
                        <button
                          @click="openViewModal(employee)"
                          class="block w-full text-left px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
                        >
                          View
                        </button>
                        <button
                          @click="openEditModal(employee)"
                          class="block w-full text-left px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out"
                        >
                          Edit
                        </button>
                        <button
                          @click="confirmDelete(employee)"
                          class="block w-full text-left px-4 py-2 text-sm leading-5 text-red-600 hover:bg-red-50 focus:outline-none focus:bg-red-50 transition duration-150 ease-in-out"
                        >
                          Delete
                        </button>
                      </template>
                    </Dropdown>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
              <!-- Showing Info -->
              <div class="text-sm text-gray-700">
                Showing {{ employees.from }} to {{ employees.to }} of {{ employees.total }} results
              </div>

              <div class="flex items-center gap-4">
                <!-- Per Page Selector -->
                <div class="flex items-center">
                   <label class="text-sm text-gray-600 mr-2">Show:</label>
                   <select v-model="perPage" @change="updatePerPage" class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 py-1">
                      <option :value="10">10</option>
                      <option :value="20">20</option>
                      <option :value="50">50</option>
                      <option :value="100">100</option>
                   </select>
                </div>

                <!-- Pagination Controls -->
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <!-- First Page -->
                  <Link
                    :href="employees.first_page_url"
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                    :class="{ 'opacity-50 cursor-not-allowed': employees.current_page === 1 }"
                    :disabled="employees.current_page === 1"
                  >
                    <span class="sr-only">First</span>
                    <i class='bx bx-chevrons-left text-lg'></i>
                  </Link>

                  <!-- Previous Page -->
                  <Link
                    :href="employees.prev_page_url || '#'"
                    class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                    :class="{ 'opacity-50 cursor-not-allowed': !employees.prev_page_url }"
                    :disabled="!employees.prev_page_url"
                  >
                    <span class="sr-only">Previous</span>
                    <i class='bx bx-chevron-left text-lg'></i>
                  </Link>

                  <!-- Page Numbers -->
                  <template v-for="(link, index) in employees.links">
                    <Link
                      v-if="!link.label.includes('Previous') && !link.label.includes('Next')"
                      :key="index"
                      :href="link.url || '#'"
                      class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                      :class="[
                        link.active
                          ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                          : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                        !link.url ? 'opacity-50 cursor-not-allowed' : ''
                      ]"
                      v-html="link.label"
                    />
                  </template>

                  <!-- Next Page -->
                  <Link
                    :href="employees.next_page_url || '#'"
                    class="relative inline-flex items-center px-2 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                    :class="{ 'opacity-50 cursor-not-allowed': !employees.next_page_url }"
                    :disabled="!employees.next_page_url"
                  >
                    <span class="sr-only">Next</span>
                    <i class='bx bx-chevron-right text-lg'></i>
                  </Link>

                  <!-- Last Page -->
                  <Link
                    :href="employees.last_page_url"
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50"
                    :class="{ 'opacity-50 cursor-not-allowed': employees.current_page === employees.last_page }"
                    :disabled="employees.current_page === employees.last_page"
                  >
                    <span class="sr-only">Last</span>
                    <i class='bx bx-chevrons-right text-lg'></i>
                  </Link>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Employee Modal -->
    <EmployeeFormModal 
        :show="showCreateModal" 
        :employee="editingEmployeeId ? employees.data.find(e => e.id === editingEmployeeId) : null"
        :roles="roles"
        :subjects="subjects"
        :options="options"
        :nextStaffNumber="nextStaffNumber"
        @close="closeCreateModal"
        @success="handleSuccess"
    />

    <!-- View Employee Modal -->
    <Modal :show="showViewModal" @close="closeViewModal" maxWidth="3xl">
      <div class="p-6" v-if="selectedEmployee">
        <div class="flex justify-between items-center mb-6">
           <h2 class="text-lg font-medium text-gray-900">
             Employee Details
           </h2>
           <button @click="closeViewModal" class="text-gray-400 hover:text-gray-500">
              <i class='bx bx-x text-2xl'></i>
           </button>
        </div>

        <div class="max-h-[80vh] overflow-y-auto pr-2">
           <!-- Header with Avatar and Basic Info -->
           <div class="flex items-center mb-6 pb-6 border-b border-gray-200">
              <img v-if="selectedEmployee.photo_url" :src="selectedEmployee.photo_url" class="h-20 w-20 rounded-full object-cover border-4 border-white shadow-md mr-4" alt="" />
              <div v-else class="h-20 w-20 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 text-2xl font-bold mr-4 border-4 border-white shadow-md">
                 {{ selectedEmployee.first_name.charAt(0) }}{{ selectedEmployee.last_name.charAt(0) }}
              </div>
              <div>
                 <h3 class="text-xl font-bold text-gray-900">
                    {{ selectedEmployee.honorific?.name }} {{ selectedEmployee.first_name }} {{ selectedEmployee.middle_name }} {{ selectedEmployee.last_name }}
                 </h3>
                 <p class="text-sm text-gray-500">{{ selectedEmployee.staff_number }} • {{ selectedEmployee.employment_type?.name }}</p>
                 <span
                   :class="[
                     'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-2',
                     selectedEmployee.employment_status?.name === 'Active' 
                       ? 'bg-green-100 text-green-800'
                       : 'bg-red-100 text-red-800'
                   ]"
                 >
                   {{ selectedEmployee.employment_status?.name }}
                 </span>
              </div>
           </div>

           <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Personal Information -->
              <div>
                 <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Personal Information</h4>
                 <dl class="space-y-2">
                    <div class="flex justify-between">
                       <dt class="text-sm text-gray-500">Gender:</dt>
                       <dd class="text-sm font-medium text-gray-900">{{ selectedEmployee.gender?.name }}</dd>
                    </div>
                    <div class="flex justify-between">
                       <dt class="text-sm text-gray-500">Marital Status:</dt>
                       <dd class="text-sm font-medium text-gray-900">{{ selectedEmployee.marital_status?.name || 'N/A' }}</dd>
                    </div>
                    <div class="flex justify-between">
                       <dt class="text-sm text-gray-500">Religion:</dt>
                       <dd class="text-sm font-medium text-gray-900">{{ selectedEmployee.religion?.name || 'N/A' }}</dd>
                    </div>
                    <div class="flex justify-between" v-if="selectedEmployee.tsc_number">
                       <dt class="text-sm text-gray-500">TSC Number:</dt>
                       <dd class="text-sm font-medium text-blue-600">{{ selectedEmployee.tsc_number }}</dd>
                    </div>
                 </dl>
              </div>

              <!-- Contact Information -->
              <div>
                 <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Contact Information</h4>
                 <dl class="space-y-2">
                    <div class="flex justify-between">
                       <dt class="text-sm text-gray-500">Email:</dt>
                       <dd class="text-sm font-medium text-gray-900">{{ selectedEmployee.email || 'N/A' }}</dd>
                    </div>
                    <div class="flex justify-between">
                       <dt class="text-sm text-gray-500">Phone:</dt>
                       <dd class="text-sm font-medium text-gray-900">{{ selectedEmployee.primary_phone }}</dd>
                    </div>
                    <div class="flex justify-between" v-if="selectedEmployee.secondary_phone">
                       <dt class="text-sm text-gray-500">Alt Phone:</dt>
                       <dd class="text-sm font-medium text-gray-900">{{ selectedEmployee.secondary_phone }}</dd>
                    </div>
                 </dl>
              </div>

              <!-- Employment Details -->
              <div>
                 <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Employment Details</h4>
                 <dl class="space-y-2">
                    <div class="flex justify-between">
                       <dt class="text-sm text-gray-500">Date of Hire:</dt>
                       <dd class="text-sm font-medium text-gray-900">{{ selectedEmployee.date_of_hire }}</dd>
                    </div>
                    <div class="flex justify-between">
                       <dt class="text-sm text-gray-500">ID Number:</dt>
                       <dd class="text-sm font-medium text-gray-900">{{ selectedEmployee.identification_number }}</dd>
                    </div>
                    <div class="flex justify-between">
                       <dt class="text-sm text-gray-500">KRA PIN:</dt>
                       <dd class="text-sm font-medium text-gray-900">{{ selectedEmployee.tax_identification_pin || 'N/A' }}</dd>
                    </div>
                 </dl>
              </div>

              <!-- Payroll Information -->
              <div>
                 <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Payroll Information</h4>
                 <dl class="space-y-2">
                    <div class="flex justify-between">
                       <dt class="text-sm text-gray-500">In Payroll:</dt>
                       <dd class="text-sm font-medium" :class="selectedEmployee.in_payroll ? 'text-green-600' : 'text-gray-500'">
                          {{ selectedEmployee.in_payroll ? 'Yes' : 'No' }}
                       </dd>
                    </div>
                    <div class="flex justify-between" v-if="selectedEmployee.in_payroll">
                       <dt class="text-sm text-gray-500">Pays PAYE:</dt>
                       <dd class="text-sm font-medium text-gray-900">{{ selectedEmployee.pays_paye ? 'Yes' : 'No' }}</dd>
                    </div>
                    <div class="flex justify-between" v-if="selectedEmployee.in_payroll">
                       <dt class="text-sm text-gray-500">SHA No:</dt>
                       <dd class="text-sm font-medium text-gray-900">{{ selectedEmployee.sha_no || 'N/A' }}</dd>
                    </div>
                    <div class="flex justify-between" v-if="selectedEmployee.in_payroll">
                       <dt class="text-sm text-gray-500">NSSF No:</dt>
                       <dd class="text-sm font-medium text-gray-900">{{ selectedEmployee.nssf_no || 'N/A' }}</dd>
                    </div>
                 </dl>
              </div>
           </div>
           
           <!-- Addresses -->
           <div class="mt-6 pt-6 border-t border-gray-200">
              <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Addresses</h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <div v-if="selectedEmployee.postal_address">
                    <dt class="text-sm text-gray-500 mb-1">Postal Address:</dt>
                    <dd class="text-sm text-gray-900 bg-gray-50 p-3 rounded">{{ selectedEmployee.postal_address }}</dd>
                 </div>
                 <div v-if="selectedEmployee.permanent_physical_address">
                    <dt class="text-sm text-gray-500 mb-1">Permanent Address:</dt>
                    <dd class="text-sm text-gray-900 bg-gray-50 p-3 rounded">{{ selectedEmployee.permanent_physical_address }}</dd>
                 </div>
              </div>
           </div>
        </div>

        <div class="mt-6 flex justify-end space-x-3 pt-4 border-t border-gray-200">
           <a
             :href="route('admin.employees.export-pdf', selectedEmployee.id)"
             target="_blank"
             class="inline-flex items-center px-4 py-2 border border-blue-600 rounded-md shadow-sm text-sm font-medium text-blue-600 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
           >
             <i class='bx bxs-file-pdf mr-2 text-lg'></i>
             Export PDF
           </a>
           <button
             type="button"
             class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
             @click="closeViewModal"
           >
             Close
           </button>
           <button
             type="button"
             class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
             @click="() => { closeViewModal(); openEditModal(selectedEmployee); }"
           >
             Edit Employee
           </button>
        </div>
      </div>
    </Modal>

    <!-- Delete Confirmation Modal -->
    <Modal :show="showDeleteModal" @close="cancelDelete" maxWidth="md">
      <div class="p-6">
        <div class="flex items-center mb-4">
          <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <i class='bx bx-trash text-red-600 text-2xl'></i>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">
              Delete Employee
            </h3>
          </div>
        </div>

        <div class="mt-4">
          <p class="text-sm text-gray-500">
            Are you sure you want to delete <strong class="text-gray-900">{{ deletingEmployee?.first_name }} {{ deletingEmployee?.last_name }}</strong>?
          </p>
          <p class="text-sm text-gray-500 mt-2">
            This action cannot be undone. All employee data including assignments and records will be permanently removed.
          </p>
        </div>

        <div class="mt-6 flex justify-end space-x-3">
          <button
            type="button"
            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            @click="cancelDelete"
          >
            Cancel
          </button>
          <button
            type="button"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            @click="deleteEmployee"
            :disabled="deleteForm.processing"
          >
            <span v-if="deleteForm.processing" class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
            {{ deleteForm.processing ? 'Deleting...' : 'Delete Employee' }}
          </button>
        </div>
      </div>
    </Modal>
  </DefaultLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import Modal from '@/Components/Modal.vue'
import EmployeeFormModal from '@/Components/Employees/EmployeeFormModal.vue'
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'

const props = defineProps({
  employees: Object,
  filters: Object,
  roles: Array,
  subjects: Array,
  options: Object,
  nextStaffNumber: String
})

const filters = ref({
  search: props.filters.search || '',
  trashed: props.filters.trashed || ''
})

const perPage = ref(20)

const updatePerPage = () => {
  router.get(route('admin.employees.index'), { 
    per_page: perPage.value,
    ...filters.value 
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}

const resetFilters = () => {
  filters.value = { search: '', trashed: '' }
  router.get(route('admin.employees.index'))
}

watch(filters, debounce(() => {
  router.get(route('admin.employees.index'), { 
    per_page: perPage.value,
    ...filters.value 
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true
  })
}, 300), { deep: true })

// Modal State
const showCreateModal = ref(false)
const editMode = ref(false)
const editingEmployeeId = ref(null)

// View Modal State
const showViewModal = ref(false)
const selectedEmployee = ref(null)

const openViewModal = (employee) => {
  selectedEmployee.value = employee
  showViewModal.value = true
}

const closeViewModal = () => {
  showViewModal.value = false
  selectedEmployee.value = null
}

// Modal Actions
const openCreateModal = () => {
  editMode.value = false
  editingEmployeeId.value = null
  showCreateModal.value = true
}

const openEditModal = (employee) => {
  editMode.value = true
  editingEmployeeId.value = employee.id
  showCreateModal.value = true
}

const closeCreateModal = () => {
  showCreateModal.value = false
  editMode.value = false
  editingEmployeeId.value = null
}

const handleSuccess = () => {
  // Optionally reload the page or show a success message
  router.reload({ only: ['employees'] })
}

// Delete Modal State
const showDeleteModal = ref(false)
const deletingEmployee = ref(null)

const deleteForm = useForm({})

const confirmDelete = (employee) => {
  deletingEmployee.value = employee
  showDeleteModal.value = true
}

const cancelDelete = () => {
  showDeleteModal.value = false
  deletingEmployee.value = null
}

const deleteEmployee = () => {
  if (!deletingEmployee.value) return
  
  deleteForm.delete(route('admin.employees.destroy', deletingEmployee.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false
      deletingEmployee.value = null
      router.reload({ only: ['employees'] })
    },
    onError: (errors) => {
      console.error('Delete failed', errors)
    }
  })
}
</script>

<style scoped>
.animate-fade-in-down {
   animation: fadeInDown 0.3s ease-out;
}
@keyframes fadeInDown {
   from {
      opacity: 0;
      transform: translateY(-10px);
   }
   to {
      opacity: 1;
      transform: translateY(0);
   }
}
</style>