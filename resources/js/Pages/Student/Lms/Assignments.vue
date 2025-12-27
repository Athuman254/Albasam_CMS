<template>
  <Head title="Assignments" />
  
  <DefaultLayout>
    <div class="row">
      <div class="col-12 mb-4">
        <h3 class="mb-0">Assignments</h3>
        <nav class="mb-3">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><Link :href="route('student.lms.dashboard')">LMS</Link></li>
            <li class="breadcrumb-item active">Assignments</li>
          </ol>
        </nav>
      </div>

      <div v-if="assignments.data.length > 0" class="col-12">
        <div v-for="assignment in assignments.data" :key="assignment.id" class="card border-0 shadow-sm mb-4 overflow-hidden assignment-card">
          <div class="card-body p-0">
            <div class="row g-0">
              <div class="col-md-8 p-4">
                <div class="d-flex align-items-center mb-2">
                  <span class="badge bg-label-info me-2">{{ assignment.subject?.name }}</span>
                  <span v-if="assignment.isOverdue" class="badge bg-label-danger">Overdue</span>
                </div>
                <h5 class="fw-bold mb-2">{{ assignment.title }}</h5>
                <p class="text-muted small mb-3">{{ assignment.instructions }}</p>
                
                <div class="d-flex flex-wrap gap-4 text-muted smaller mb-3">
                  <div><i class="fas fa-calendar-alt me-1"></i> Due: {{ formatDateTime(assignment.due_date) }}</div>
                  <div><i class="fas fa-star me-1"></i> {{ assignment.max_points }} Points Max</div>
                  <div><i class="fas fa-user-edit me-1"></i> Teacher: {{ assignment.teacher?.name }}</div>
                </div>

                <div v-if="assignment.attachment_path" class="mb-0">
                  <a :href="'/storage/' + assignment.attachment_path" target="_blank" class="btn btn-xs btn-outline-secondary">
                    <i class="fas fa-paperclip me-1"></i> Download Reference Material
                  </a>
                </div>
              </div>
              
              <div class="col-md-4 bg-light p-4 d-flex flex-column align-items-center justify-content-center border-start text-center">
                <div v-if="assignment.submissions && assignment.submissions.length > 0" class="w-100">
                  <div class="avatar bg-success rounded-circle p-3 mx-auto mb-2 text-white shadow-sm">
                    <i class="fas fa-check fa-lg"></i>
                  </div>
                  <h6 class="fw-bold text-success mb-1">Submitted</h6>
                  <p class="smaller text-muted mb-3">On: {{ formatDateTime(assignment.submissions[0].submitted_at) }}</p>
                  
                  <div v-if="assignment.submissions[0].status === 'graded'" class="bg-white p-3 rounded-3 shadow-xs mb-0">
                    <div class="small fw-bold text-primary mb-1">Grade: {{ assignment.submissions[0].points_earned }} / {{ assignment.max_points }}</div>
                    <div v-if="assignment.submissions[0].teacher_feedback" class="smaller text-muted font-italic">"{{ assignment.submissions[0].teacher_feedback }}"</div>
                  </div>
                  <div v-else class="badge bg-label-secondary">Awaiting Grading</div>
                </div>
                
                <div v-else class="w-100">
                  <div class="avatar bg-light-danger rounded-circle p-3 mx-auto mb-2 text-danger">
                    <i class="fas fa-clock fa-lg"></i>
                  </div>
                  <h6 class="fw-bold text-danger mb-3">Not Submitted</h6>
                  <button class="btn btn-primary rounded-pill px-4" @click="openSubmitModal(assignment)">
                    Submit Work
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="mt-4">
          <Pagination :links="assignments.links" />
        </div>
      </div>

      <div v-else class="col-12 text-center py-5">
        <div class="bg-white p-5 rounded-4 shadow-sm">
          <i class="fas fa-tasks fa-4x text-light mb-3"></i>
          <h4 class="text-muted">No assignments yet</h4>
          <p class="text-muted">You don't have any assignments pending for your class.</p>
        </div>
      </div>
    </div>

    <!-- Submit Modal -->
    <div class="modal fade" id="submitModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <form v-if="activeAssignment" class="modal-content" @submit.prevent="submitWork">
          <div class="modal-header">
            <h5 class="modal-title">Submit Work: {{ activeAssignment.title }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div class="mb-4">
              <label class="form-label fw-bold">Upload File <span class="text-danger">*</span></label>
              <input type="file" class="form-control" @change="handleFileUpload" required>
              <div class="form-text smaller">Max size: 10MB. PDF, Word, Images, or ZIP.</div>
            </div>
            <div class="mb-0">
              <label class="form-label fw-bold">Submission Notes (Optional)</label>
              <textarea v-model="form.notes" class="form-control" rows="3" placeholder="Any comments for your teacher?"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4" :disabled="form.processing">
              <span v-if="form.processing">Submitting...</span>
              <span v-else>Turn in Work</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  assignments: Object
});

const activeAssignment = ref(null);
const form = useForm({
  file: null,
  notes: '',
});

let submitModal = null;

onMounted(() => {
  submitModal = new bootstrap.Modal(document.getElementById('submitModal'));
});

const openSubmitModal = (assignment) => {
  activeAssignment.value = assignment;
  form.reset();
  submitModal.show();
};

const handleFileUpload = (e) => {
  form.file = e.target.files[0];
};

const submitWork = () => {
  form.post(route('student.lms.assignments.submit', activeAssignment.value.id), {
    onSuccess: () => {
      submitModal.hide();
      form.reset();
    },
  });
};

const formatDateTime = (date) => {
  return new Date(date).toLocaleString('en-GB', { 
    day: 'numeric', 
    month: 'short', 
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

<style scoped>
.assignment-card { border-radius: 16px; transition: transform 0.2s; }
.assignment-card:hover { transform: scale(1.01); }
.bg-label-info { background-color: #e1f5fe; color: #03a9f4; }
.bg-label-danger { background-color: #ffebee; color: #d32f2f; }
.bg-label-secondary { background-color: #f1f1f1; color: #6c757d; }
.bg-light-danger { background-color: #fff5f5; }
.avatar { width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; }
.smaller { font-size: 0.75rem; }
.btn-xs { padding: 0.25rem 0.5rem; font-size: 0.7rem; }
.shadow-xs { box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
</style>
