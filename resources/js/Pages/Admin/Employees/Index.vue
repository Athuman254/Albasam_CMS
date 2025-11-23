<template>
  <DefaultLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <div class="flex justify-between items-center">
            <div>
              <h1 class="text-2xl font-bold text-gray-900">Employees</h1>
              <p class="mt-1 text-sm text-gray-600">Manage all employees in the system</p>
            </div>
            <Link
              :href="route('admin.employees.create')"
              class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
            >
              Add Employee
            </Link>
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
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                    <Link
                      :href="route('admin.employees.show', employee.id)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      View
                    </Link>
                    <Link
                      :href="route('admin.employees.edit', employee.id)"
                      class="text-green-600 hover:text-green-900"
                    >
                      Edit
                    </Link>
                    <Link
                      :href="route('admin.employees.assign-classes', employee.id)"
                      class="text-purple-600 hover:text-purple-900"
                    >
                      Assign Classes
                    </Link>
                    <Link
                      :href="route('admin.employees.assign-subjects', employee.id)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      Assign Subjects
                    </Link>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex justify-between items-center">
              <div class="text-sm text-gray-700">
                Showing {{ employees.from }} to {{ employees.to }} of {{ employees.total }} results
              </div>
              <div class="space-x-2">
                <Link
                  v-for="(link, index) in employees.links"
                  :key="index"
                  :href="link.url || '#'"
                  :class="[
                    'px-3 py-1 rounded-md text-sm font-medium',
                    link.active
                      ? 'bg-blue-600 text-white'
                      : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
                    !link.url ? 'opacity-50 cursor-not-allowed' : ''
                  ]"
                  v-html="link.label"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'

const props = defineProps({
  employees: Object,
  filters: Object
})

const filters = ref({
  search: props.filters.search || '',
  trashed: props.filters.trashed || ''
})

// Debounced search
const performSearch = debounce(() => {
  router.get(route('admin.employees.index'), filters.value, {
    preserveState: true,
    replace: true
  })
}, 300)

watch(filters, performSearch, { deep: true })

const resetFilters = () => {
  filters.value = {
    search: '',
    trashed: ''
  }
}
</script>