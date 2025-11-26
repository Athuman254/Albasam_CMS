<template>
  <DefaultLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-2xl font-bold text-gray-900">Assign Subjects to Employee</h1>
          <p class="mt-1 text-sm text-gray-600">
            Assign subjects to {{ employee.first_name }} {{ employee.last_name }}
          </p>
        </div>

        <!-- Employee Info -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Staff Number</label>
              <p class="mt-1 text-sm text-gray-900">{{ employee.staff_number }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Employment Type</label>
              <p class="mt-1 text-sm text-gray-900">{{ employee.employment_type?.name }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Gender</label>
              <p class="mt-1 text-sm text-gray-900">{{ employee.gender?.name }}</p>
            </div>
          </div>
        </div>

        <!-- Assignment Form -->
        <div class="bg-white shadow rounded-lg p-6">
          <form @submit.prevent="submitAssignments">
            <!-- Academic Year Selection -->
            <div class="mb-6">
              <label for="academic_year_id" class="block text-sm font-medium text-gray-700">
                Academic Year *
              </label>
              <select
                id="academic_year_id"
                v-model="form.academic_year_id"
                required
                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
              >
                <option value="">Select Academic Year</option>
                <option
                  v-for="year in academicYears"
                  :key="year.id"
                  :value="year.id"
                >
                  {{ year.name }}
                </option>
              </select>
            </div>

            <!-- Assignments -->
            <div class="mb-6">
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Subject Assignments</h3>
                <button
                  type="button"
                  @click="addAssignment"
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  Add Subject
                </button>
              </div>

              <div
                v-for="(assignment, index) in form.assignments"
                :key="index"
                class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 p-4 border border-gray-200 rounded-lg"
              >
                <!-- Subject Selection -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Subject *</label>
                  <select
                    v-model="assignment.subject_id"
                    required
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                  >
                    <option value="">Select Subject</option>
                    <option
                      v-for="subject in subjects"
                      :key="subject.id"
                      :value="subject.id"
                    >
                      {{ subject.name }}
                    </option>
                  </select>
                </div>

                <!-- Class Selection -->
                <div>
                  <label class="block text-sm font-medium text-gray-700">Class</label>
                  <select
                    v-model="assignment.class_id"
                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md"
                  >
                    <option :value="null">All Classes</option>
                    <option
                      v-for="classItem in classes"
                      :key="classItem.id"
                      :value="classItem.id"
                    >
                      {{ classItem.name }}
                    </option>
                  </select>
                </div>

                <!-- Remove Button -->
                <div class="flex items-center">
                  <button
                    type="button"
                    @click="removeAssignment(index)"
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                  >
                    Remove
                  </button>
                </div>
              </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-3">
              <Link
                :href="route('admin.employees.show', employee.id)"
                class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                Cancel
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                <span v-if="form.processing">Saving...</span>
                <span v-else>Save Assignments</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Current Assignments -->
        <div class="bg-white shadow rounded-lg p-6 mt-6" v-if="assignedSubjects.length > 0">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Current Subject Assignments</h3>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Subject
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Class
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Academic Year
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="assignment in assignedSubjects" :key="assignment.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ assignment.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ assignment.pivot.class_id ? getClassName(assignment.pivot.class_id) : 'All Classes' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ getAcademicYearName(assignment.pivot.academic_year_id) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button
                      @click="removeSubjectAssignment(assignment)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Remove
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'

const props = defineProps({
  employee: Object,
  subjects: Array,
  classes: Array,
  academicYears: Array,
  currentAcademicYear: Object,
  assignedSubjects: Array,
})

const form = useForm({
  academic_year_id: props.currentAcademicYear?.id || '',
  assignments: [
    {
      subject_id: '',
      class_id: null,
    }
  ],
})

const addAssignment = () => {
  form.assignments.push({
    subject_id: '',
    class_id: null,
  })
}

const removeAssignment = (index) => {
  if (form.assignments.length > 1) {
    form.assignments.splice(index, 1)
  }
}

const submitAssignments = () => {
  form.post(route('admin.employees.subject-assignments.store', props.employee.id), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      form.academic_year_id = props.currentAcademicYear?.id || ''
      form.assignments = [{
        subject_id: '',
        class_id: null,
      }]
      window.location.reload()
    },
  })
}

const getClassName = (classId) => {
  const classItem = props.classes.find(c => c.id === classId)
  return classItem?.name || 'Unknown Class'
}

const getAcademicYearName = (academicYearId) => {
  const year = props.academicYears.find(y => y.id === academicYearId)
  return year?.name || 'Unknown Year'
}

const removeSubjectAssignment = (assignment) => {
  if (confirm('Are you sure you want to remove this subject assignment?')) {
    axios.delete(route('admin.employees.subject-assignments.remove', props.employee.id), {
      data: {
        subject_id: assignment.id,
        academic_year_id: assignment.pivot.academic_year_id,
      }
    }).then(response => {
      if (response.data.success) {
        window.location.reload()
      }
    }).catch(error => {
      console.error('Error removing assignment:', error)
      alert('Failed to remove assignment. Please try again.')
    })
  }
}
</script>