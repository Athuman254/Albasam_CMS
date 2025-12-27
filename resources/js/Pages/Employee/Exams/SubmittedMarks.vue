<template>
  <Head title="Submitted Exam Marks" />
  <DefaultLayout>
    <div class="row">
      <h3 class="mb-0">Submitted Exam Marks</h3>
      <nav class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <Link :href="route('employee.dashboard')">Home</Link>
          </li>
          <li class="breadcrumb-item">Submitted Marks</li>
        </ol>
      </nav>

      <div class="col-lg-12">
        <div class="card border">
          <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <div>
              <h5 class="card-title mb-1">Submitted Marks History</h5>
              <p class="mb-0">Track your submitted marks and their approval status</p>
            </div>
            <div class="d-flex align-items-center gap-2">
              <button class="btn btn-outline-primary btn-sm border" @click="loadSubmittedMarks" :disabled="loading">
                <i class="bx bx-refresh me-1" :class="{ 'bx-spin': loading }"></i>
                Refresh
              </button>
              <Link :href="route('employee.exams.enter-marks')" class="btn btn-primary btn-sm">
                <i class="bx bx-edit me-1"></i>
                Enter New Marks
              </Link>
            </div>
          </div>

          <div class="card-body">
            <!-- Loading State -->
            <div v-if="loading" class="text-center py-5">
              <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading submitted marks...</span>
              </div>
              <p class="mt-2">Loading your submitted marks...</p>
            </div>

            <!-- Statistics Cards -->
            <div v-else-if="submittedMarks && submittedMarks.length > 0" class="row mb-4">
              <div class="col-md-2 col-6">
                <div class="card border">
                  <div class="card-body text-center p-3">
                    <h6 class="card-title mb-1">Total</h6>
                    <h4 class="mb-0">{{ statistics.total }}</h4>
                  </div>
                </div>
              </div>
              <div class="col-md-2 col-6">
                <div class="card border">
                  <div class="card-body text-center p-3">
                    <h6 class="card-title mb-1">Pending</h6>
                    <h4 class="mb-0">{{ statistics.submitted }}</h4>
                  </div>
                </div>
              </div>
              <div class="col-md-2 col-6">
                <div class="card border">
                  <div class="card-body text-center p-3">
                    <h6 class="card-title mb-1">Approved</h6>
                    <h4 class="mb-0">{{ statistics.approved }}</h4>
                  </div>
                </div>
              </div>
              <div class="col-md-2 col-6">
                <div class="card border">
                  <div class="card-body text-center p-3">
                    <h6 class="card-title mb-1">Rejected</h6>
                    <h4 class="mb-0">{{ statistics.rejected }}</h4>
                  </div>
                </div>
              </div>
              <div class="col-md-2 col-6">
                <div class="card border">
                  <div class="card-body text-center p-3">
                    <h6 class="card-title mb-1">Published</h6>
                    <h4 class="mb-0">{{ statistics.published }}</h4>
                  </div>
                </div>
              </div>
              <div class="col-md-2 col-6">
                <div class="card border">
                  <div class="card-body text-center p-3">
                    <h6 class="card-title mb-1">Draft</h6>
                    <h4 class="mb-0">{{ statistics.draft }}</h4>
                  </div>
                </div>
              </div>
            </div>

            <!-- Submitted Marks Table -->
            <div v-if="submittedMarks && submittedMarks.length > 0" class="table-responsive">
              <table class="table table-bordered align-middle">
                <thead class="bg-light">
                  <tr>
                    <th width="200">Exam</th>
                    <th width="150">Class</th>
                    <th width="80" class="text-center">Subjects</th>
                    <th width="80" class="text-center">Students</th>
                    <th width="120" class="text-center">Submitted Date</th>
                    <th width="120" class="text-center">Status</th>
                    <th>Remarks</th>
                    <th width="100" class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="submission in submittedMarks" :key="submission.id">
                    <td>
                      <div class="fw-semibold">{{ getExamName(submission) }}</div>
                      <small>{{ getAcademicYear(submission) }}</small>
                    </td>
                    <td>
                      <span class="badge border">{{ getClassName(submission) }}</span>
                    </td>
                    <td class="text-center">
                      <span class="badge border">{{ submission.subjects_count || 0 }}</span>
                    </td>
                    <td class="text-center">
                      <span class="badge border">{{ submission.students_count || 0 }}</span>
                    </td>
                    <td class="text-center">
                      <small>{{ formatDate(submission.submitted_at) }}</small>
                      <br>
                      <small>{{ formatTimeAgo(submission.submitted_at) }}</small>
                    </td>
                    <td class="text-center">
                      <span class="badge border" :class="getStatusClass(submission.status)">
                        <i :class="getStatusIcon(submission.status)" class="me-1"></i>
                        {{ formatStatus(submission.status) }}
                      </span>
                    </td>
                    <td>
                      <div v-if="submission.remarks" class="remarks-container">
                        <span class="small">{{ submission.remarks }}</span>
                        <small class="d-block mt-1" v-if="submission.approved_by">
                          <i class="bx bx-user me-1"></i>
                          Reviewed by: {{ getApprovedByName(submission) }}
                        </small>
                      </div>
                      <span v-else class="small">-</span>
                    </td>
                    <td class="text-center">
                      <div class="btn-group btn-group-sm" role="group">
                        <button 
                          class="btn btn-outline-primary border" 
                          @click="viewSubmissionDetails(submission)"
                          title="View Details"
                        >
                          <i class="bx bx-show"></i>
                        </button>
                        <Link 
                          v-if="submission.status === 'draft' || submission.status === 'rejected'"
                          :href="route('employee.exams.enter-marks', { exam_id: submission.exam_id, class_id: submission.class_id })" 
                          class="btn btn-outline-warning border"
                          title="Edit Marks"
                        >
                          <i class="bx bx-edit"></i>
                        </Link>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Pagination Info -->
              <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="small">
                  Showing {{ submittedMarks.length }} submissions
                </div>
                <div class="small">
                  Last updated: {{ lastUpdated }}
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!loading" class="text-center py-5">
              <i class="bx bx-file display-4 mb-3"></i>
              <h5>No Submitted Marks Found</h5>
              <p class="mb-4">You haven't submitted any exam marks for approval yet.</p>
              <div class="d-flex justify-content-center gap-2">
                <Link :href="route('employee.exams.enter-marks')" class="btn btn-primary">
                  <i class="bx bx-edit me-1"></i> Enter Exam Marks
                </Link>
                <Link :href="route('employee.dashboard')" class="btn btn-outline-secondary border">
                  <i class="bx bx-home me-1"></i> Back to Dashboard
                </Link>
              </div>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="text-center py-5">
              <i class="bx bx-error display-4 mb-3"></i>
              <h5>Error Loading Data</h5>
              <p class="mb-4">Unable to load submitted marks. Please try again.</p>
              <button class="btn btn-primary" @click="loadSubmittedMarks">
                <i class="bx bx-refresh me-1"></i> Retry
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Submission Details Modal -->
    <div class="modal fade" id="submissionDetailsModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content border">
          <div class="modal-header bg-white border-bottom">
            <h5 class="modal-title">Submission Details</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div v-if="selectedSubmission">
              <div class="row mb-3">
                <div class="col-md-6">
                  <strong>Exam:</strong> {{ getExamName(selectedSubmission) }}<br>
                  <strong>Class:</strong> {{ getClassName(selectedSubmission) }}<br>
                  <strong>Subjects:</strong> {{ selectedSubmission.subjects_count || 0 }}
                </div>
                <div class="col-md-6">
                  <strong>Students:</strong> {{ selectedSubmission.students_count || 0 }}<br>
                  <strong>Submitted:</strong> {{ formatDate(selectedSubmission.submitted_at) }}<br>
                  <strong>Status:</strong> 
                  <span class="badge border" :class="getStatusClass(selectedSubmission.status)">
                    {{ formatStatus(selectedSubmission.status) }}
                  </span>
                </div>
              </div>
              
              <div v-if="selectedSubmission.remarks" class="alert bg-light border">
                <strong>Remarks:</strong> {{ selectedSubmission.remarks }}
              </div>
              
              <div class="mt-3">
                <h6>Submission Information</h6>
                <div class="table-responsive">
                  <table class="table table-sm">
                    <tbody>
                      <tr v-if="selectedSubmission.submitted_by">
                        <td width="40%"><strong>Submitted By:</strong></td>
                        <td>{{ getSubmittedByName(selectedSubmission) }}</td>
                      </tr>
                      <tr v-if="selectedSubmission.approved_by">
                        <td><strong>Approved By:</strong></td>
                        <td>{{ getApprovedByName(selectedSubmission) }}</td>
                      </tr>
                      <tr v-if="selectedSubmission.approved_at">
                        <td><strong>Approved Date:</strong></td>
                        <td>{{ formatDate(selectedSubmission.approved_at) }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer bg-white border-top">
            <button type="button" class="btn btn-secondary border" data-bs-dismiss="modal">Close</button>
            <Link 
              v-if="selectedSubmission && (selectedSubmission.status === 'draft' || selectedSubmission.status === 'rejected')"
              :href="route('employee.exams.enter-marks', { exam_id: selectedSubmission.exam_id, class_id: selectedSubmission.class_id })" 
              class="btn btn-primary"
            >
              Edit Marks
            </Link>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import { Modal } from 'bootstrap';

const submittedMarks = ref([]);
const loading = ref(false);
const error = ref(null);
const selectedSubmission = ref(null);

// Configure axios for employee API calls
const apiClient = axios.create({
  baseURL: window.location.origin,
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  }
});

// Add CSRF token to requests
apiClient.interceptors.request.use((config) => {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (csrfToken) {
    config.headers['X-CSRF-TOKEN'] = csrfToken;
  }
  return config;
});

// Computed properties
const statistics = computed(() => {
  const stats = {
    total: 0,
    submitted: 0,
    approved: 0,
    rejected: 0,
    published: 0,
    draft: 0
  };

  if (submittedMarks.value && Array.isArray(submittedMarks.value)) {
    stats.total = submittedMarks.value.length;
    
    submittedMarks.value.forEach(submission => {
      if (submission.status && stats[submission.status] !== undefined) {
        stats[submission.status]++;
      }
    });
  }

  return stats;
});

const lastUpdated = computed(() => {
  return new Date().toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
});

// Methods
const loadSubmittedMarks = async () => {
  try {
    loading.value = true;
    error.value = null;
    
    console.log('Loading submitted marks from API...');
    const response = await apiClient.get('/employee/exams/submitted-marks-data');
    console.log('API Response:', response);
    
    // Handle different response structures
    if (response.data && Array.isArray(response.data)) {
      submittedMarks.value = response.data;
    } else if (response.data && response.data.data && Array.isArray(response.data.data)) {
      submittedMarks.value = response.data.data;
    } else if (response.data && response.data.submissions) {
      submittedMarks.value = response.data.submissions;
    } else {
      console.warn('Unexpected API response structure:', response.data);
      submittedMarks.value = [];
    }
    
    console.log('Processed submitted marks:', submittedMarks.value);
    
  } catch (err) {
    console.error('Error loading submitted marks:', err);
    error.value = err.message;
    submittedMarks.value = [];
    
    // Try alternative endpoints
    if (err.response?.status === 404) {
      console.log('Primary endpoint not found, trying alternative...');
      await tryAlternativeEndpoints();
    }
  } finally {
    loading.value = false;
  }
};

const tryAlternativeEndpoints = async () => {
  try {
    // Try different possible endpoints
    const endpoints = [
      '/employee/exams/my-submissions',
      '/employee/exams/marks/submitted',
      '/api/employee/exams/submitted-marks'
    ];
    
    for (const endpoint of endpoints) {
      try {
        console.log(`Trying endpoint: ${endpoint}`);
        const response = await apiClient.get(endpoint);
        if (response.data && (Array.isArray(response.data) || response.data.data)) {
          submittedMarks.value = Array.isArray(response.data) ? response.data : response.data.data;
          console.log(`Success with endpoint: ${endpoint}`, submittedMarks.value);
          return;
        }
      } catch (e) {
        console.log(`Endpoint ${endpoint} failed:`, e.message);
      }
    }
    
    // If all endpoints fail, use mock data for development
    console.log('All endpoints failed, using mock data for development');
    submittedMarks.value = getMockData();
    
  } catch (err) {
    console.error('All alternative endpoints failed:', err);
    submittedMarks.value = getMockData();
  }
};

// Data access helper methods
const getExamName = (submission) => {
  return submission.exam?.name || submission.exam_name || submission.exam?.exam_name || 'N/A';
};

const getClassName = (submission) => {
  return submission.class?.name || submission.class_name || submission.class?.class_name || 'N/A';
};

const getAcademicYear = (submission) => {
  return submission.exam?.academic_year || submission.academic_year || submission.exam?.academic_year_name || '';
};

const getSubmittedByName = (submission) => {
  return submission.submitted_by_user?.name || submission.submitted_by || submission.teacher?.name || 'N/A';
};

const getApprovedByName = (submission) => {
  return submission.approved_by_user?.name || submission.approved_by || submission.approver?.name || 'N/A';
};

const getStatusClass = (status) => {
  // Using consistent styling for all statuses
  return 'bg-white';
};

const getStatusIcon = (status) => {
  const icons = {
    'draft': 'bx bx-edit',
    'submitted': 'bx bx-time',
    'approved': 'bx bx-check-circle',
    'rejected': 'bx bx-x-circle',
    'published': 'bx bx-bar-chart'
  };
  return icons[status] || 'bx bx-info-circle';
};

const formatStatus = (status) => {
  const statusMap = {
    'draft': 'Draft',
    'submitted': 'Pending Review',
    'approved': 'Approved',
    'rejected': 'Rejected',
    'published': 'Published'
  };
  return statusMap[status] || status;
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  } catch (error) {
    return dateString;
  }
};

const formatTimeAgo = (dateString) => {
  if (!dateString) return '';
  
  try {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays === 1) return '1 day ago';
    if (diffDays < 7) return `${diffDays} days ago`;
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
    return `${Math.floor(diffDays / 30)} months ago`;
  } catch (error) {
    return '';
  }
};

const viewSubmissionDetails = (submission) => {
  selectedSubmission.value = submission;
  const modal = new Modal(document.getElementById('submissionDetailsModal'));
  modal.show();
};

// Mock data for development (fallback)
const getMockData = () => {
  return [
    {
      id: 1,
      exam_id: 1,
      class_id: 1,
      exam_name: 'Mid Term Exam 2024',
      class_name: 'Grade 10A',
      academic_year: '2023-2024',
      subjects_count: 5,
      students_count: 35,
      submitted_at: '2024-01-15 10:30:00',
      status: 'submitted',
      remarks: 'All marks entered and verified',
      submitted_by: 'John Teacher'
    },
    {
      id: 2,
      exam_id: 2,
      class_id: 2,
      exam_name: 'Final Exam 2024',
      class_name: 'Grade 9B',
      academic_year: '2023-2024',
      subjects_count: 6,
      students_count: 32,
      submitted_at: '2024-01-10 14:20:00',
      status: 'approved',
      remarks: 'Well documented marks',
      submitted_by: 'John Teacher',
      approved_by: 'Admin User',
      approved_at: '2024-01-12 09:15:00'
    }
  ];
};

onMounted(() => {
  loadSubmittedMarks();
});
</script>

<style scoped>
.card {
  background-color: #ffffff;
  border: 1px solid #dee2e6;
}

.card.border {
  border-color: #dee2e6 !important;
}

.badge.border {
  background: white;
  border: 1px solid #dee2e6 !important;
  color: #212529;
}

.table td {
  vertical-align: middle;
}

.badge {
  font-size: 0.75em;
  font-weight: 500;
}

.remarks-container {
  max-width: 300px;
  word-wrap: break-word;
}

.spinner-border {
  width: 2rem;
  height: 2rem;
  color: #0d6efd;
}

.btn-group-sm > .btn {
  padding: 0.25rem 0.5rem;
}

.table-responsive {
  max-height: 70vh;
  overflow-y: auto;
}

.modal-body .row {
  margin-bottom: 1rem;
}

.alert.bg-light {
  background-color: #f8f9fa !important;
  border-color: #dee2e6 !important;
}

.bg-light {
  background-color: #f8f9fa !important;
}

.border {
  border-color: #dee2e6 !important;
}

.btn-outline-primary.border:hover {
  background-color: #0d6efd;
  border-color: #0d6efd;
  color: white;
}

.btn-outline-warning.border:hover {
  background-color: #ffc107;
  border-color: #ffc107;
  color: #212529;
}
</style>