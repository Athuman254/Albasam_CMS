<template>
  <DefaultLayout>
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200 text-center">
            <div class="mb-4">
              <i class="fas fa-exchange-alt text-4xl text-blue-500"></i>
            </div>
            <h2 class="text-2xl font-bold mb-4">Page Moved</h2>
            <p class="text-gray-600 mb-6">
              Subject allocation has been moved to the new Timetable module for better management.
            </p>
            <div class="flex justify-center gap-4">
              <a 
                :href="route('timetable.allocations.index')" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150"
              >
                Go to Subject Allocation
              </a>
              <Link 
                :href="route('admin.employees.show', employee.id)"
                class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 active:bg-gray-400 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150"
              >
                Back to Employee Profile
              </Link>
            </div>
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