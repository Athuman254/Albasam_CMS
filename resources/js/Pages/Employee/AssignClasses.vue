<template>
  <Head :title="`Assign Classes - ${employee.full_name}`" />
  <DefaultLayout>
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div>
              <h4 class="card-title mb-1">Class Assignment - {{ employee.full_name }}</h4>
              <p class="text-muted mb-0">Manage class and subject assignments for this teacher</p>
            </div>
            <div>
              <Link :href="route('admin.employees.show', employee.id)" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-arrow-back me-1"></i> Back to Profile
              </Link>
            </div>
          </div>
          <div class="card-body">
            <!-- Academic Year Selection -->
            <div class="row mb-4">
              <div class="col-md-6">
                <label for="academic_year" class="form-label">Academic Year <span class="text-danger">*</span></label>
                <select 
                  v-model="academicYearId" 
                  id="academic_year" 
                  class="form-select"
                  @change="loadAssignments"
                >
                  <option value="">Select Academic Year</option>
                  <option 
                    v-for="year in academicYears" 
                    :key="year.id" 
                    :value="year.id"
                  >
                    {{ year.display_name }} {{ year.is_active ? '(Current)' : '' }}
                  </option>
                </select>
              </div>
              <div class="col-md-6">
                <div class="d-flex align-items-end h-100 gap-2">
                  <button 
                    @click="openAssignmentModal" 
                    class="btn btn-primary"
                    :disabled="!academicYearId"
                  >
                    <i class="bx bx-plus me-1"></i> Add Assignment
                  </button>
                  <button 
                    @click="loadAssignments" 
                    class="btn btn-outline-secondary"
                    :disabled="!academicYearId"
                  >
                    <i class="bx bx-refresh me-1"></i> Refresh
                  </button>
                </div>
              </div>
            </div>

            <!-- Current Assignments Table -->
            <div v-if="assignments.length > 0" class="mb-4">
              <h6 class="mb-3">Current Assignments for {{ selectedAcademicYearName }}</h6>
              <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                  <thead class="table-light">
                    <tr>
                      <th>Class</th>
                      <th>Stream</th>
                      <th>Subject(s)</th>
                      <th class="text-center">Class Teacher</th>
                      <th>Type</th>
                      <th class="text-center">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="classAssignment in groupedAssignments" :key="classAssignment.class_id">
                      <td>
                        <strong>{{ classAssignment.class_name }}</strong>
                      </td>
                      <td>
                        <span class="badge bg-secondary" v-if="classAssignment.stream_name">
                          {{ classAssignment.stream_name }}
                        </span>
                        <span class="badge bg-light text-dark" v-else>
                          No Stream
                        </span>
                      </td>
                      <td>
                        <div v-if="classAssignment.is_class_teacher">
                          <span class="badge bg-success">All Subjects (Class Teacher)</span>
                        </div>
                        <div v-else>
                          <span 
                            v-for="subject in classAssignment.subjects" 
                            :key="subject.id"
                            class="badge bg-info me-1 mb-1"
                          >
                            {{ subject.name }}
                          </span>
                          <span 
                            v-if="classAssignment.canAddMoreSubjects" 
                            class="badge bg-light text-muted border"
                            title="Can add more subjects"
                          >
                            +{{ classAssignment.remainingSlots }} more
                          </span>
                        </div>
                      </td>
                      <td class="text-center">
                        <span v-if="classAssignment.is_class_teacher" class="badge bg-success">Yes</span>
                        <span v-else class="badge bg-secondary">No</span>
                      </td>
                      <td>
                        <span class="badge" :class="getAssignmentTypeBadge(classAssignment)">
                          {{ getAssignmentType(classAssignment) }}
                        </span>
                      </td>
                      <td class="text-center">
                        <div class="btn-group btn-group-sm">
                          <button 
                            @click="editClassAssignment(classAssignment)"
                            class="btn btn-outline-primary"
                            title="Edit Assignment"
                            :disabled="classAssignment.is_class_teacher"
                          >
                            <i class="bx bx-edit"></i>
                          </button>
                          <button 
                            @click="removeClassAssignment(classAssignment)"
                            class="btn btn-outline-danger"
                            title="Remove All Assignments for this Class"
                          >
                            <i class="bx bx-trash"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="academicYearId" class="text-center text-muted py-5">
              <i class="bx bx-book-open display-4 mb-3"></i>
              <h5>No Assignments Found</h5>
              <p>This teacher has no class/subject assignments for the selected academic year.</p>
              <button @click="openAssignmentModal" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add First Assignment
              </button>
            </div>

            <div v-else class="text-center text-muted py-5">
              <i class="bx bx-calendar display-4 mb-3"></i>
              <h5>Select Academic Year</h5>
              <p>Please select an academic year to manage class assignments.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Assignment Modal -->
    <div class="modal fade" :class="{ 'show d-block': showAssignmentModal }" v-if="showAssignmentModal" tabindex="-1">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ editingAssignment ? 'Edit Class Assignment' : 'Add New Class Assignment' }}
            </h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <!-- Class Selection -->
            <div class="mb-3">
              <label class="form-label">Class <span class="text-danger">*</span></label>
              <v-select
                v-model="assignmentForm.class_id"
                :options="availableClasses"
                label="full_name"
                :reduce="option => option.id"
                placeholder="Select Class"
                :disabled="editingAssignment"
                :class="{ 'is-invalid': assignmentForm.errors.class_id }"
              >
                <template #option="{ name, stream }">
                  <div>
                    <strong>{{ name }}</strong>
                    <span v-if="stream" class="text-muted"> - {{ stream.name }}</span>
                  </div>
                </template>
                <template #selected-option="{ name, stream }">
                  {{ name }}<span v-if="stream"> - {{ stream.name }}</span>
                </template>
              </v-select>
              <div v-if="assignmentForm.errors.class_id" class="text-danger small mt-1">
                {{ assignmentForm.errors.class_id }}
              </div>
            </div>

            <!-- Class Teacher Option -->
            <div class="mb-3">
              <div class="form-check">
                <input 
                  v-model="assignmentForm.is_class_teacher" 
                  class="form-check-input" 
                  type="checkbox" 
                  id="is_class_teacher"
                  @change="onClassTeacherChange"
                >
                <label class="form-check-label" for="is_class_teacher">
                  Set as Class Teacher
                </label>
              </div>
              <div class="form-text">
                Class Teacher can manage all subjects for this class. When checked, subject selection will be disabled.
              </div>
            </div>

            <!-- Subject Selection - Multiple Subjects -->
            <div class="mb-3" v-if="!assignmentForm.is_class_teacher">
              <label class="form-label">Subjects (Select up to 2)</label>
              <v-select
                v-model="assignmentForm.subject_ids"
                :options="availableSubjects"
                label="name"
                :reduce="option => option.id"
                multiple
                :max="2"
                placeholder="Select Subjects"
                :class="{ 'is-invalid': assignmentForm.errors.subject_ids }"
              >
                <template #maxElements>
                  <div class="text-warning small mt-1">
                    Maximum 2 subjects allowed per class
                  </div>
                </template>
              </v-select>
              <div class="form-text">
                You can select up to 2 subjects for this teacher in the same class
              </div>
              <div v-if="assignmentForm.errors.subject_ids" class="text-danger small mt-1">
                {{ assignmentForm.errors.subject_ids }}
              </div>
            </div>

            <!-- Assignment Type Preview -->
            <div v-if="assignmentForm.class_id" class="alert" :class="getPreviewAlertClass()">
              <strong>Assignment Type:</strong> 
              {{ getAssignmentTypePreview() }}
            </div>

            <!-- Validation Errors -->
            <div v-if="assignmentForm.errors.general" class="alert alert-danger">
              {{ assignmentForm.errors.general }}
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Cancel</button>
            <button 
              type="button" 
              class="btn btn-primary" 
              @click="submitAssignment"
              :disabled="!assignmentForm.class_id || assignmentForm.loading || (!assignmentForm.is_class_teacher && assignmentForm.subject_ids.length === 0)"
            >
              <span v-if="assignmentForm.loading" class="spinner-border spinner-border-sm me-2"></span>
              {{ editingAssignment ? 'Update' : 'Save' }} Assignment
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" v-if="showAssignmentModal"></div>

    <!-- Success Toast -->
    <div v-if="showSuccess" class="position-fixed top-0 end-0 p-3" style="z-index: 1050">
      <div class="toast show" role="alert">
        <div class="toast-header bg-success text-white">
          <i class="bx bx-check-circle me-2"></i>
          <strong class="me-auto">Success</strong>
          <button type="button" class="btn-close btn-close-white" @click="showSuccess = false"></button>
        </div>
        <div class="toast-body">
          {{ successMessage }}
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed, reactive, watch } from 'vue'
import { toast } from 'vue3-toastify'

const props = defineProps({
  employee: Object,
  classes: Array,
  subjects: Array,
  academicYears: Array,
  currentAcademicYear: Object,
  currentAssignments: Array,
})

// Reactive data
const assignments = ref(props.currentAssignments || [])
const academicYearId = ref(props.currentAcademicYear?.id || '')
const showSuccess = ref(false)
const showAssignmentModal = ref(false)
const editingAssignment = ref(null)
const successMessage = ref('')

// Assignment form
const assignmentForm = reactive({
  class_id: '',
  subject_ids: [],
  is_class_teacher: false,
  academic_year_id: props.currentAcademicYear?.id || '',
  loading: false,
  errors: {}
})

// Computed properties
const selectedAcademicYearName = computed(() => {
  const year = props.academicYears.find(y => y.id == academicYearId.value)
  return year ? year.display_name : 'Selected Year'
})

// Add full_name computed property to classes for v-select
const classesWithFullName = computed(() => {
  return props.classes.map(classItem => ({
    ...classItem,
    full_name: classItem.stream ? `${classItem.name} - ${classItem.stream.name}` : classItem.name
  }))
})

const availableClasses = computed(() => {
  // Filter out classes that are already assigned to this teacher for the selected year
  const assignedClassIds = assignments.value.map(assignment => assignment.class_id)
  return classesWithFullName.value.filter(classItem => !assignedClassIds.includes(classItem.id))
})

const availableSubjects = computed(() => {
  return props.subjects
})

// Group assignments by class
const groupedAssignments = computed(() => {
  const classMap = {}
  
  assignments.value.forEach(assignment => {
    const classId = assignment.class_id
    
    if (!classMap[classId]) {
      classMap[classId] = {
        class_id: classId,
        class_name: assignment.class?.name,
        stream_name: assignment.class?.stream?.name,
        is_class_teacher: false,
        subjects: [],
        assignment_ids: []
      }
    }
    
    if (assignment.is_class_teacher) {
      classMap[classId].is_class_teacher = true
    } else if (assignment.subject) {
      classMap[classId].subjects.push({
        id: assignment.subject.id,
        name: assignment.subject.name,
        assignment_id: assignment.id
      })
    }
    
    classMap[classId].assignment_ids.push(assignment.id)
  })
  
  // Add computed properties for each class assignment
  return Object.values(classMap).map(classAssignment => ({
    ...classAssignment,
    subjectCount: classAssignment.subjects.length,
    canAddMoreSubjects: !classAssignment.is_class_teacher && classAssignment.subjects.length < 2,
    remainingSlots: 2 - classAssignment.subjects.length
  }))
})

// Methods
const getSubjectName = (subjectId) => {
  const subject = props.subjects.find(s => s.id == subjectId)
  return subject ? subject.name : 'Unknown Subject'
}

const getAssignmentType = (assignment) => {
  if (assignment.is_class_teacher) {
    return 'Class Teacher'
  } else if (assignment.subjects.length > 0) {
    return `Subject Teacher (${assignment.subjects.length} subjects)`
  }
  return 'General'
}

const getAssignmentTypeBadge = (assignment) => {
  if (assignment.is_class_teacher) {
    return 'bg-success'
  } else if (assignment.subjects.length > 0) {
    return 'bg-info'
  }
  return 'bg-secondary'
}

const getAssignmentTypePreview = () => {
  if (assignmentForm.is_class_teacher) {
    return 'Class Teacher - Manages all subjects in this class'
  } else if (assignmentForm.subject_ids.length > 0) {
    const subjectNames = assignmentForm.subject_ids.map(id => getSubjectName(id)).join(', ')
    return `Subject Teacher - Will teach ${subjectNames} (${assignmentForm.subject_ids.length} subjects)`
  }
  return 'Please select at least one subject or enable Class Teacher'
}

const getPreviewAlertClass = () => {
  if (!assignmentForm.class_id) return 'alert-secondary'
  if (assignmentForm.is_class_teacher) return 'alert-success'
  if (assignmentForm.subject_ids.length > 0) return 'alert-info'
  return 'alert-warning'
}

const loadAssignments = async () => {
  if (!academicYearId.value) return

  try {
    const response = await fetch(route('admin.employees.get-employee-assignments', {
      employee: props.employee.id,
      academic_year_id: academicYearId.value
    }))

    if (response.ok) {
      const result = await response.json()
      if (result.success) {
        assignments.value = result.assignments
      } else {
        toast.error('Failed to load assignments')
      }
    } else {
      toast.error('Failed to load assignments')
    }
  } catch (error) {
    console.error('Error loading assignments:', error)
    toast.error('Failed to load assignments')
  }
}

const openAssignmentModal = () => {
  editingAssignment.value = null
  resetAssignmentForm()
  showAssignmentModal.value = true
}

const editClassAssignment = (classAssignment) => {
  if (classAssignment.is_class_teacher) {
    toast.warning('Cannot edit Class Teacher assignment directly. Remove and recreate if needed.')
    return
  }
  
  editingAssignment.value = classAssignment
  assignmentForm.class_id = classAssignment.class_id
  assignmentForm.subject_ids = classAssignment.subjects.map(subject => subject.id)
  assignmentForm.is_class_teacher = false
  assignmentForm.academic_year_id = academicYearId.value
  showAssignmentModal.value = true
}

const closeModal = () => {
  showAssignmentModal.value = false
  editingAssignment.value = null
  resetAssignmentForm()
}

const resetAssignmentForm = () => {
  assignmentForm.class_id = ''
  assignmentForm.subject_ids = []
  assignmentForm.is_class_teacher = false
  assignmentForm.academic_year_id = academicYearId.value
  assignmentForm.loading = false
  assignmentForm.errors = {}
}

const onClassTeacherChange = () => {
  if (assignmentForm.is_class_teacher) {
    assignmentForm.subject_ids = []
  }
}

const submitAssignment = async () => {
  if (!assignmentForm.class_id) {
    assignmentForm.errors.class_id = 'Please select a class'
    return
  }

  if (!academicYearId.value) {
    assignmentForm.errors.general = 'Please select an academic year'
    return
  }

  if (!assignmentForm.is_class_teacher && assignmentForm.subject_ids.length === 0) {
    assignmentForm.errors.subject_ids = 'Please select at least one subject'
    return
  }

  assignmentForm.loading = true
  assignmentForm.errors = {}

  try {
    const url = editingAssignment.value 
      ? route('admin.employees.class-assignments.update', { 
          employee: props.employee.id, 
          assignment: editingAssignment.value.assignment_ids[0] // Use first assignment ID
        })
      : route('admin.employees.class-assignments.store', props.employee.id)

    const method = editingAssignment.value ? 'PUT' : 'POST'

    const response = await fetch(url, {
      method: method,
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        class_id: assignmentForm.class_id,
        subject_ids: assignmentForm.subject_ids,
        is_class_teacher: assignmentForm.is_class_teacher,
        academic_year_id: academicYearId.value
      })
    })

    const result = await response.json()

    if (result.success) {
      successMessage.value = editingAssignment.value ? 'Assignment updated successfully' : 'Assignment added successfully'
      showSuccess.value = true
      setTimeout(() => showSuccess.value = false, 3000)
      
      closeModal()
      await loadAssignments()
    } else {
      if (result.errors) {
        assignmentForm.errors = result.errors
      } else {
        assignmentForm.errors.general = result.message || 'Failed to save assignment'
      }
      toast.error(assignmentForm.errors.general || 'Please fix the form errors')
    }
  } catch (error) {
    console.error('Error saving assignment:', error)
    assignmentForm.errors.general = 'Failed to save assignment: ' + error.message
    toast.error('Failed to save assignment')
  } finally {
    assignmentForm.loading = false
  }
}

const removeClassAssignment = async (classAssignment) => {
  const assignmentType = classAssignment.is_class_teacher ? 'Class Teacher' : `${classAssignment.subjects.length} subject(s)`
  
  if (!confirm(`Are you sure you want to remove ${assignmentType} assignment for ${classAssignment.class_name}?`)) return

  try {
    const url = route('admin.employees.class-assignments.remove', { 
      employee: props.employee.id
    })
    
    const response = await fetch(url, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        assignment_ids: classAssignment.assignment_ids,
        class_id: classAssignment.class_id,
        academic_year_id: academicYearId.value
      })
    })

    const result = await response.json()

    if (result.success) {
      successMessage.value = 'Assignment removed successfully'
      showSuccess.value = true
      setTimeout(() => showSuccess.value = false, 3000)
      
      await loadAssignments()
    } else {
      toast.error(result.message || 'Failed to remove assignment')
    }
  } catch (error) {
    console.error('Error removing assignment:', error)
    toast.error('Failed to remove assignment')
  }
}

// Watchers
watch(() => academicYearId.value, (newYearId) => {
  if (newYearId) {
    assignmentForm.academic_year_id = newYearId
    loadAssignments()
  }
})

// Initialize
if (props.currentAcademicYear) {
  academicYearId.value = props.currentAcademicYear.id
  assignmentForm.academic_year_id = props.currentAcademicYear.id
}
</script>

<style scoped>
.modal-backdrop {
  z-index: 1040;
}

.modal {
  z-index: 1050;
}

.toast {
  min-width: 300px;
}

.badge {
  font-size: 0.75em;
}

.table-sm td, .table-sm th {
  padding: 0.75rem;
}

.card .card-body {
  padding: 1rem;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

.v-select {
  width: 100%;
}

.modal-backdrop {
  background-color: rgba(0, 0, 0, 0.5);
}

.is-invalid {
  border-color: #dc3545;
}

.text-danger {
  color: #dc3545;
}

.alert {
  border-radius: 0.375rem;
}

.alert-secondary {
  background-color: #f8f9fa;
  border-color: #dee2e6;
  color: #6c757d;
}

.alert-success {
  background-color: #d1e7dd;
  border-color: #badbcc;
  color: #0f5132;
}

.alert-info {
  background-color: #cff4fc;
  border-color: #b6effb;
  color: #055160;
}

.alert-warning {
  background-color: #fff3cd;
  border-color: #ffecb5;
  color: #664d03;
}

.alert-danger {
  background-color: #f8d7da;
  border-color: #f5c6cb;
  color: #721c24;
}

.badge.me-1 {
  margin-right: 0.25rem;
}

.badge.mb-1 {
  margin-bottom: 0.25rem;
}
</style>