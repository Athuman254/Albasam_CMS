<template>
  <Head title="Assignment Submissions" />
  
  <DefaultLayout>
    <div class="row">
      <div class="col-12 mb-4">
        <h3 class="mb-0">Submissions: {{ assignment.title }}</h3>
        <nav class="mb-3">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><Link :href="route('admin.lms.assignments.index')">Assignments</Link></li>
            <li class="breadcrumb-item active">Submissions</li>
          </ol>
        </nav>
      </div>

      <div class="col-12">
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Student Submissions</h5>
              <div class="small text-muted">Max Points: {{ assignment.max_points }}</div>
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                  <tr>
                    <th>Student</th>
                    <th>Admission No.</th>
                    <th>Submitted At</th>
                    <th>Status</th>
                    <th>Grade</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="submission in submissions" :key="submission.id">
                    <td>
                      <div class="fw-bold">{{ submission.student?.first_name }} {{ submission.student?.last_name }}</div>
                    </td>
                    <td><code>{{ submission.student?.admission_number }}</code></td>
                    <td>{{ formatDateTime(submission.submitted_at) }}</td>
                    <td>
                      <span class="badge" :class="submission.status === 'graded' ? 'bg-label-success' : 'bg-label-primary'">
                        {{ submission.status }}
                      </span>
                    </td>
                    <td>
                      <span v-if="submission.status === 'graded'" class="fw-bold">
                        {{ submission.points_earned }} / {{ assignment.max_points }}
                      </span>
                      <span v-else class="text-muted small">Not Graded</span>
                    </td>
                    <td class="text-end">
                      <div class="d-flex justify-content-end gap-2">
                        <a :href="'/storage/' + submission.submission_file" target="_blank" class="btn btn-sm btn-outline-primary">
                          <i class="fas fa-download me-1"></i> View Work
                        </a>
                        <button class="btn btn-sm btn-primary" @click="openGradeModal(submission)">
                          {{ submission.status === 'graded' ? 'Regrade' : 'Grade' }}
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="submissions.length === 0">
                    <td colspan="6" class="text-center py-5 text-muted">
                      No submissions found for this assignment yet.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Grade Modal -->
    <div class="modal fade" id="gradeModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <form v-if="activeSubmission" class="modal-content" @submit.prevent="submitGrade">
          <div class="modal-header">
            <h5 class="modal-title">Grade Submission: {{ activeSubmission.student?.first_name }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div class="mb-3">
              <label class="form-label fw-bold">Points Earned (Max: {{ assignment.max_points }})</label>
              <input v-model="form.points_earned" type="number" class="form-control" :max="assignment.max_points" min="0" required>
            </div>
            <div class="mb-0">
              <label class="form-label fw-bold">Feedback</label>
              <textarea v-model="form.teacher_feedback" class="form-control" rows="3" placeholder="Great job! Keep it up."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4" :disabled="form.processing">
              Confirm Grade
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

const props = defineProps({
  assignment: Object,
  submissions: Array
});

const activeSubmission = ref(null);
const form = useForm({
  points_earned: 0,
  teacher_feedback: '',
});

let gradeModal = null;

onMounted(() => {
  gradeModal = new bootstrap.Modal(document.getElementById('gradeModal'));
});

const openGradeModal = (submission) => {
  activeSubmission.value = submission;
  form.points_earned = submission.points_earned || 0;
  form.teacher_feedback = submission.teacher_feedback || '';
  gradeModal.show();
};

const submitGrade = () => {
  form.post(route('admin.lms.submissions.grade', activeSubmission.value.id), {
    onSuccess: () => {
      gradeModal.hide();
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
.bg-label-success { background-color: #e8f5e9; color: #2e7d32; }
.bg-label-primary { background-color: #e3f2fd; color: #1976d2; }
</style>
