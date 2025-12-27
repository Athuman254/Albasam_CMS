<template>
  <Head title="Assignments" />
  
  <DefaultLayout>
    <div class="row">
      <h3 class="mb-0">Assignments</h3>
      <nav class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <Link :href="route('admin.dashboard')">Home</Link>
          </li>
          <li class="breadcrumb-item">
            <span class="text-muted">LMS</span>
          </li>
          <li class="breadcrumb-item text-primary">
            Assignments
          </li>
        </ol>
      </nav>

      <div class="col-12 mb-4">
        <div class="d-flex justify-content-end">
          <button class="btn btn-primary" @click="showCreateModal">
            <i class="fas fa-plus me-1"></i> Create Assignment
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label small fw-bold">Subject</label>
              <select v-model="filterForm.subject_id" class="form-select" @change="search">
                <option value="">All Subjects</option>
                <option v-for="subject in subjects" :key="subject.id" :value="subject.id">{{ subject.name }}</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold">Class</label>
              <select v-model="filterForm.rank_id" class="form-select" @change="search">
                <option value="">All Classes</option>
                <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
              </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <button class="btn btn-outline-secondary w-100" @click="resetFilters">
                <i class="fas fa-undo me-1"></i> Reset Filters
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Assignments List -->
      <div class="row g-4">
        <div v-for="assignment in assignments.data" :key="assignment.id" class="col-md-12">
          <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-body p-0">
              <div class="row g-0">
                <div class="col-md-9 p-4">
                  <div class="d-flex align-items-center mb-2">
                    <span class="badge bg-label-info me-2">{{ assignment.subject?.name }}</span>
                    <span class="badge bg-label-secondary">{{ assignment.rank?.name }}</span>
                    <span v-if="assignment.isOverdue" class="badge bg-label-danger ms-2">Overdue</span>
                  </div>
                  <h5 class="fw-bold mb-1">{{ assignment.title }}</h5>
                  <p class="text-muted small mb-3 line-clamp-2">{{ assignment.instructions }}</p>
                  
                  <div class="d-flex flex-wrap gap-4 text-muted smaller">
                    <div><i class="fas fa-calendar-alt me-1"></i> Due: {{ formatDate(assignment.due_date) }}</div>
                    <div><i class="fas fa-star me-1"></i> {{ assignment.max_points }} Points</div>
                    <div><i class="fas fa-user-edit me-1"></i> By: {{ assignment.teacher?.name }}</div>
                  </div>
                </div>
                <div class="col-md-3 bg-light-info border-start d-flex flex-column align-items-center justify-content-center p-4">
                  <div class="text-center mb-3">
                    <h3 class="fw-bold mb-0">{{ assignment.submissions_count }}</h3>
                    <p class="small text-muted mb-0">Submissions</p>
                  </div>
                  <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-white shadow-sm" @click="viewSubmissions(assignment)">
                      <i class="fas fa-eye me-1"></i> View
                    </button>
                    <button class="btn btn-sm btn-outline-danger" @click="deleteAssignment(assignment)">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="assignments.data.length === 0" class="col-12 text-center py-5">
           <div class="bg-white p-5 rounded-4 shadow-sm">
            <i class="fas fa-tasks fa-4x text-light mb-3"></i>
            <h4 class="text-muted">No assignments yet</h4>
            <p class="text-muted mb-4">Create your first assignment to start assessing students.</p>
            <button class="btn btn-primary px-4" @click="showCreateModal">Create Now</button>
          </div>
        </div>
      </div>

      <div class="mt-5">
        <Pagination :links="assignments.links" />
      </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <form class="modal-content" @submit.prevent="submitCreate">
          <div class="modal-header">
            <h5 class="modal-title">Create New Assignment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div class="mb-3">
              <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
              <input v-model="form.title" type="text" class="form-control" placeholder="Periodic Table Quiz" required>
            </div>
            
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">Subject <span class="text-danger">*</span></label>
                <select v-model="form.subject_id" class="form-select" required>
                  <option value="">Select Subject</option>
                  <option v-for="subject in subjects" :key="subject.id" :value="subject.id">{{ subject.name }}</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Class <span class="text-danger">*</span></label>
                <select v-model="form.rank_id" class="form-select" required>
                  <option value="">Select Class</option>
                  <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                </select>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="form-label fw-bold">Due Date <span class="text-danger">*</span></label>
                <input v-model="form.due_date_only" type="date" class="form-control" required>
              </div>
              <div class="col-md-2">
                <label class="form-label fw-bold">Time <span class="text-danger">*</span></label>
                <input v-model="form.due_time_only" type="time" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Max Points <span class="text-danger">*</span></label>
                <input v-model="form.max_points" type="number" class="form-control" min="0" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Instructions <span class="text-danger">*</span></label>
              <textarea v-model="form.instructions" class="form-control" rows="4" placeholder="List the assignment requirements and steps..." required></textarea>
            </div>

            <div class="row g-3 align-items-center">
              <div class="col-md-6">
                <label class="form-label fw-bold">Attachment (Optional)</label>
                <input type="file" class="form-control" @change="handleFileUpload">
              </div>
              <div class="col-md-6 pt-3">
                <div class="form-check form-switch">
                  <input v-model="form.allow_late_submission" class="form-check-input" type="checkbox" id="lateSubmission">
                  <label class="form-check-label fw-bold" for="lateSubmission">Allow Late Submission</label>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4" :disabled="form.processing">
              <span v-if="form.processing">Creating...</span>
              <span v-else>Confirm Create</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head, router, useForm, Link } from '@inertiajs/vue3';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  assignments: Object,
  subjects: Array,
  classes: Array,
  filters: Object,
});

const filterForm = ref({
  subject_id: props.filters.subject_id || '',
  rank_id: props.filters.rank_id || '',
});

const form = useForm({
  title: '',
  subject_id: '',
  rank_id: '',
  instructions: '',
  due_date: '',
  due_date_only: '',
  due_time_only: '23:59',
  max_points: 100,
  allow_late_submission: false,
  file: null,
});

let createModal = null;

onMounted(() => {
  createModal = new bootstrap.Modal(document.getElementById('createModal'));
});

const search = () => {
  router.get(route('admin.lms.assignments.index'), filterForm.value, {
    preserveState: true,
    replace: true,
  });
};

const resetFilters = () => {
  filterForm.value = { subject_id: '', rank_id: '' };
  search();
};

const showCreateModal = () => {
  form.reset();
  createModal.show();
};

const handleFileUpload = (e) => {
  form.file = e.target.files[0];
};

const submitCreate = () => {
  // Combine date and time
  if (form.due_date_only && form.due_time_only) {
    form.due_date = `${form.due_date_only} ${form.due_time_only}`;
  }
  
  form.post(route('admin.lms.assignments.store'), {
    onSuccess: () => {
      createModal.hide();
      form.reset();
    },
  });
};

const deleteAssignment = (assignment) => {
  if (confirm(`Are you sure you want to delete "${assignment.title}"?`)) {
    router.delete(route('admin.lms.assignments.destroy', assignment.id));
  }
};

const viewSubmissions = (assignment) => {
  router.get(route('admin.lms.assignments.submissions', assignment.id));
};

const formatDate = (date) => {
  return new Date(date).toLocaleString('en-GB', { 
    day: 'numeric', 
    month: 'short', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

<style scoped>
.bg-light-info { background-color: rgba(3, 169, 244, 0.05); }
.smaller { font-size: 0.8rem; }
.bg-label-info { background-color: #e1f5fe; color: #03a9f4; }
.bg-label-secondary { background-color: #f1f1f1; color: #6c757d; }
.bg-label-danger { background-color: #ffebee; color: #d32f2f; }
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;  
  overflow: hidden;
}
</style>
