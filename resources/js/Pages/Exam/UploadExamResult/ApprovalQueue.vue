<template>
  <Head title="Marks Approval Queue" />
  <DefaultLayout>
    <div class="row">
      <h3 class="mb-0">Marks Approval Queue</h3>
      <nav class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <Link :href="route('admin.dashboard')">Home</Link>
          </li>
          <li class="breadcrumb-item text-primary">Approval Queue</li>
        </ol>
      </nav>

      <!-- Statistics Cards - Consistent White Styling -->
      <div class="col-lg-12 mb-4">
        <div class="row">
          <div class="col-md-3">
            <div class="card card-body bg-white border">
              <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                  <h4 class="mb-0 text-primary">{{ stats.pending || 0 }}</h4>
                  <small class="text-muted">Pending Submissions</small>
                </div>
                <div class="flex-shrink-0">
                  <i class="bx bx-time-five display-4 text-primary opacity-25"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card card-body bg-white border">
              <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                  <h4 class="mb-0 text-success">{{ stats.approved || 0 }}</h4>
                  <small class="text-muted">Approved</small>
                </div>
                <div class="flex-shrink-0">
                  <i class="bx bx-check-circle display-4 text-success opacity-25"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card card-body bg-white border">
              <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                  <h4 class="mb-0 text-danger">{{ stats.rejected || 0 }}</h4>
                  <small class="text-muted">Rejected</small>
                </div>
                <div class="flex-shrink-0">
                  <i class="bx bx-x-circle display-4 text-danger opacity-25"></i>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card card-body bg-white border">
              <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                  <h4 class="mb-0 text-info">{{ stats.total || 0 }}</h4>
                  <small class="text-muted">Total Processed</small>
                </div>
                <div class="flex-shrink-0">
                  <i class="bx bx-stats display-4 text-info opacity-25"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <div>
              <h5 class="card-title mb-1">
                {{ activeTab === 'pending' ? 'Pending Approval' : 'Approved History' }}
              </h5>
              <p class="text-muted mb-0">
                {{ activeTab === 'pending' ? 'Review and approve submitted exam marks' : 'View history of approved exam submissions' }}
              </p>
            </div>
            <div class="d-flex gap-2">
              <div class="btn-group me-2">
                <button 
                  class="btn btn-sm" 
                  :class="activeTab === 'pending' ? 'btn-primary' : 'btn-outline-primary'"
                  @click="switchTab('pending')"
                >
                  Pending
                </button>
                <button 
                  class="btn btn-sm" 
                  :class="activeTab === 'approved' ? 'btn-primary' : 'btn-outline-primary'"
                  @click="switchTab('approved')"
                >
                  Approved
                </button>
              </div>
              <div class="input-group input-group-sm me-2" style="width: 280px;">
                <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                <input 
                  type="text" 
                  class="form-control" 
                  :placeholder="activeTab === 'pending' ? 'Search teacher, class, subject, student...' : 'Search student, exam...'"
                  v-model="searchQuery"
                  @input="handleSearch"
                >
              </div>
              <select class="form-select form-select-sm me-2" style="width: 100px;" v-model="perPage" @change="handlePerPageChange">
                <option :value="10">10</option>
                <option :value="25">25</option>
                <option :value="50">50</option>
                <option :value="100">100</option>
              </select>
              <button class="btn btn-outline-primary btn-sm" @click="loadApprovalQueue" :disabled="loading">
                <i class="bx bx-refresh me-1" :class="{ 'bx-spin': loading }"></i>
                Refresh
              </button>
              <button class="btn btn-outline-secondary btn-sm" @click="exportToExcel" :disabled="pendingMarks.length === 0">
                <i class="bx bx-download me-1"></i>
                Export
              </button>
              <button class="btn btn-outline-warning btn-sm" @click="fixMissingGrades" :disabled="loading">
                <i class="bx bx-cog me-1"></i>
                Fix Grades
              </button>
            </div>
          </div>

          <div class="card-body">
            <!-- Debug Info -->
            <div v-if="debugInfo" class="alert alert-info alert-sm mb-3">
              <small><strong>Debug:</strong> {{ debugInfo }}</small>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading approval queue...</span>
              </div>
              <p class="mt-2 text-muted">Loading pending approvals...</p>
            </div>

            <!-- Error State -->
            <div v-else-if="error" class="alert alert-danger">
              <i class="bx bx-error me-2"></i>
              {{ error }}
              <button class="btn btn-sm btn-outline-danger ms-2" @click="loadApprovalQueue">Try Again</button>
            </div>

            <!-- Approval Queue -->
            <div v-else-if="pendingMarks.length > 0" class="table-responsive">
              <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th width="50" v-if="activeTab === 'pending'">
                      <input type="checkbox" v-model="selectAll" @change="toggleSelectAll">
                    </th>
                    <th>Exam</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th class="text-center">Students</th>
                    <th class="text-center">{{ activeTab === 'pending' ? 'Submitted Date' : 'Approved Date' }}</th>
                    <th width="180" class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="submission in pendingMarks" :key="submission.id" 
                      :class="{ 'table-warning': isRecentSubmission(submission.submitted_date) && activeTab === 'pending' }">
                    <td v-if="activeTab === 'pending'">
                      <input type="checkbox" v-model="selectedSubmissions" :value="submission.id">
                    </td>
                    <td>
                      <div class="fw-semibold">{{ submission.exam_name }}</div>
                    </td>
                    <td>
                      <span class="badge border text-dark">{{ submission.class_name }}</span>
                    </td>
                    <td>
                      <div class="fw-medium">{{ submission.subject_name }}</div>
                      <small class="text-muted">{{ submission.subject_code }}</small>
                    </td>
                    <td>
                      <div class="fw-semibold">{{ submission.teacher_name }}</div>
                      <div class="small text-muted">{{ submission.teacher_email }}</div>
                      
                      <!-- Matched Student Display -->
                      <div v-if="submission.matched_students && submission.matched_students.length > 0" class="mt-2">
                        <span class="badge bg-warning text-dark">
                          <i class="bx bx-search-alt me-1"></i>
                          Found: {{ submission.matched_students[0] }}
                          <span v-if="submission.matched_students.length > 1">
                            + {{ submission.matched_students.length - 1 }} others
                          </span>
                        </span>
                      </div>
                    </td>
                    <td class="text-center">
                      <span class="badge border text-dark">{{ submission.students_count }}</span>
                    </td>
                    <td class="text-center">
                      <small :class="{ 'text-success fw-bold': isRecentSubmission(submission.submitted_date) && activeTab === 'pending' }">
                        {{ activeTab === 'pending' ? formatDateTime(submission.submitted_date) : formatDateTime(submission.approved_date) }}
                      </small>
                      <div v-if="isRecentSubmission(submission.submitted_date) && activeTab === 'pending'" class="mt-1">
                        <span class="badge bg-success">New</span>
                      </div>
                      <div v-if="activeTab === 'approved'" class="mt-1">
                        <small class="text-muted">by {{ submission.approved_by_name }}</small>
                      </div>
                    </td>
                    <td class="text-center">
                      <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-primary" @click="viewSubmission(submission)" 
                                :disabled="actionLoading" title="Review Details">
                          <i class="bx bx-show me-1"></i>{{ activeTab === 'pending' ? 'Review' : 'View' }}
                        </button>
                        <button v-if="activeTab === 'pending'" class="btn btn-outline-success" @click="approveSubmission(submission.id)" 
                                :disabled="actionLoading" title="Approve All Students">
                          <i class="bx bx-check me-1"></i>Approve All
                        </button>
                        <button v-if="activeTab === 'pending'" class="btn btn-outline-danger" @click="rejectSubmission(submission.id)" 
                                :disabled="actionLoading" title="Reject All Students">
                          <i class="bx bx-x me-1"></i>Reject All
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Bulk Actions -->
              <div v-if="selectedSubmissions.length > 0" class="mt-3 p-3 bg-light rounded">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <strong>{{ selectedSubmissions.length }}</strong> submissions selected
                    <small class="text-muted ms-2">(Total students: {{ getSelectedStudentsCount() }})</small>
                  </div>
                  <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" @click="bulkApprove" :disabled="actionLoading">
                      <i class="bx bx-check me-1"></i>Approve All Selected
                    </button>
                    <button class="btn btn-danger btn-sm" @click="showBulkRejectModal" :disabled="actionLoading">
                      <i class="bx bx-x me-1"></i>Reject All Selected
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" @click="clearSelection">
                      <i class="bx bx-x me-1"></i>Clear
                    </button>
                  </div>
                </div>
              </div>

              <!-- Pagination -->
              <div class="d-flex justify-content-between align-items-center mt-3 px-1">
                <div class="text-muted small">
                  <span v-if="totalItems > 0">
                    Showing {{ ((currentPage - 1) * perPage) + 1 }} to {{ Math.min(currentPage * perPage, totalItems) }} of {{ totalItems }} entries
                  </span>
                  <span v-else>
                    No records found
                  </span>
                </div>
                
                <!-- Pagination Controls for Both Tabs -->
                <nav aria-label="Page navigation" v-if="totalItems > perPage">
                  <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="{ disabled: currentPage === 1 }">
                      <button class="page-link" @click="changePage(currentPage - 1)" :disabled="currentPage === 1">Previous</button>
                    </li>
                    <li class="page-item" v-for="page in visiblePages" :key="page" :class="{ active: currentPage === page }">
                      <button class="page-link" @click="changePage(page)">{{ page }}</button>
                    </li>
                    <li class="page-item" :class="{ disabled: currentPage === lastPage }">
                      <button class="page-link" @click="changePage(currentPage + 1)" :disabled="currentPage === lastPage">Next</button>
                    </li>
                  </ul>
                </nav>
                
                <div class="text-muted small">
                  Last updated: {{ lastUpdated }}
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center text-muted py-5">
              <i class="bx bx-check-circle display-4 text-muted mb-3"></i>
              <h5>{{ activeTab === 'pending' ? 'No Pending Approvals' : 'No Approved History' }}</h5>
              <p class="mb-4">
                {{ activeTab === 'pending' ? 'All submitted marks have been reviewed and processed.' : 'No approved submissions found in history.' }}
              </p>
              <button class="btn btn-primary" @click="loadApprovalQueue">
                <i class="bx bx-refresh me-1"></i>Check Again
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Review Modal with Per-Student Approval -->
    <div class="modal fade" id="reviewModal">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              Review Submission - {{ currentSubmission?.exam_name }} 
              <span class="badge border text-dark ms-2">{{ currentSubmission?.class_name }}</span>
              <span class="badge bg-primary ms-2">{{ currentSubmission?.subject_name }}</span>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div v-if="currentSubmission && submissionDetails">
              <!-- Submission Overview -->
              <div class="row mb-4">
                <div class="col-md-3">
                  <div class="card card-body bg-white border">
                    <small class="text-muted">Teacher</small>
                    <div class="fw-semibold">{{ currentSubmission.teacher_name }}</div>
                    <small class="text-muted">{{ currentSubmission.teacher_email }}</small>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="card card-body bg-white border">
                    <small class="text-muted">Students</small>
                    <div class="fw-semibold">{{ submissionDetails.summary?.total_students || 0 }}</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="card card-body bg-white border">
                    <small class="text-muted">Total Marks</small>
                    <div class="fw-semibold">{{ submissionDetails.summary?.total_marks || 0 }}</div>
                  </div>
                </div>
                <div class="col-md-2">
                  <div class="card card-body bg-white border">
                    <small class="text-muted">Average</small>
                    <div class="fw-semibold">{{ submissionDetails.summary?.average_percentage?.toFixed(1) || 0 }}%</div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card card-body bg-white border">
                    <small class="text-muted">Submitted</small>
                    <div class="fw-semibold">{{ formatDateTime(currentSubmission.submitted_date) }}</div>
                  </div>
                </div>
              </div>

              <!-- Student Selection Actions -->
              <div v-if="selectedStudentMarks.length > 0" class="mb-3 p-3 bg-light rounded">
                <div class="d-flex justify-content-between align-items-center">
                  <div>
                    <strong>{{ selectedStudentMarks.length }}</strong> students selected
                  </div>
                  <div class="d-flex gap-2">
                    <button class="btn btn-success btn-sm" @click="approveSelectedStudents" :disabled="actionLoading">
                      <i class="bx bx-check me-1"></i>Approve Selected
                    </button>
                    <button class="btn btn-danger btn-sm" @click="showStudentRejectModal" :disabled="actionLoading">
                      <i class="bx bx-x me-1"></i>Reject Selected
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" @click="clearStudentSelection">
                      <i class="bx bx-x me-1"></i>Clear Selection
                    </button>
                  </div>
                </div>
              </div>

              <!-- Modal Search -->
              <div class="mb-3">
                <input 
                  type="text" 
                  class="form-control form-control-sm" 
                  placeholder="Search student in this list..." 
                  v-model="modalSearchQuery"
                >
              </div>

              <!-- Marks Table with Checkboxes -->
              <div class="table-responsive" style="max-height: 400px;">
                <table class="table table-bordered table-sm table-striped">
                  <thead class="table-light sticky-top">
                    <tr>
                      <th width="50">
                        <input type="checkbox" v-model="selectAllStudents" @change="toggleSelectAllStudents">
                      </th>
                      <th>Student Name</th>
                      <th>Admission No</th>
                      <th class="text-center">Marks</th>
                      <th class="text-center">Percentage</th>
                      <th class="text-center">Grade</th>
                      <th>Status</th>
                      <th width="120" class="text-center">Individual Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="mark in filteredModalMarks" :key="mark.id"
                        :class="{ 'table-success': mark.status === 'approved', 'table-danger': mark.status === 'rejected' }">
                      <td>
                        <input type="checkbox" v-model="selectedStudentMarks" :value="mark.id" 
                               :disabled="mark.status !== 'submitted'">
                      </td>
                      <td class="fw-medium">{{ mark.student_name }}</td>
                      <td class="text-muted">{{ mark.admission_number }}</td>
                      <td class="text-center">
                        <span class="fw-semibold">{{ mark.marks_obtained }}</span>
                        <small class="text-muted">/{{ mark.maximum_marks }}</small>
                      </td>
                      <td class="text-center">
                        <span :class="getPercentageClass(mark.percentage)">
                          {{ mark.formatted_percentage }}
                        </span>
                      </td>
                      <td class="text-center">
                        <span class="badge" :class="getGradeBadgeClass(mark.grade)">
                          {{ mark.grade || calculateGrade(mark.marks_obtained, mark.maximum_marks) }}
                        </span>
                      </td>
                      <td>
                        <span class="badge" :class="getStatusBadgeClass(mark.status)">
                          {{ mark.status }}
                        </span>
                      </td>
                      <td class="text-center">
                        <div class="btn-group btn-group-sm" role="group">
                          <button v-if="mark.status === 'submitted'" 
                                  class="btn btn-outline-success btn-sm" 
                                  @click="approveSingleStudent(mark.id)"
                                  :disabled="actionLoading"
                                  title="Approve this student">
                            <i class="bx bx-check"></i>
                          </button>
                          <button v-if="mark.status === 'submitted'" 
                                  class="btn btn-outline-danger btn-sm" 
                                  @click="rejectSingleStudent(mark.id)"
                                  :disabled="actionLoading"
                                  title="Reject this student">
                            <i class="bx bx-x"></i>
                          </button>
                          <button v-if="mark.status === 'approved' && canEditMarks" 
                                  class="btn btn-outline-warning btn-sm" 
                                  @click="openEditMarkModal(mark)"
                                  :disabled="actionLoading"
                                  title="Edit approved mark (Permission Required)">
                            <i class="bx bx-pencil"></i>
                          </button>
                          <span v-if="mark.status !== 'submitted' && mark.status !== 'approved'" class="text-muted small">Processed</span>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div v-else class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading details...</span>
              </div>
              <p class="mt-2 text-muted">Loading submission details...</p>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-success" @click="approveAllStudents" :disabled="actionLoading || !hasPendingMarks">
              <i class="bx bx-check me-1"></i>Approve All Students
            </button>
            <button type="button" class="btn btn-danger" @click="showRejectReasonModal" :disabled="actionLoading || !hasPendingMarks">
              <i class="bx bx-x me-1"></i>Reject All Students
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Reason Modal for All Students -->
    <div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Reject All Students</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="rejectionReason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
              <textarea class="form-control" id="rejectionReason" v-model="rejectionReason" 
                        rows="4" placeholder="Please provide a reason for rejection..." 
                        :class="{ 'is-invalid': rejectionError }"></textarea>
              <div class="invalid-feedback" v-if="rejectionError">
                {{ rejectionError }}
              </div>
              <div class="form-text">
                This reason will be applied to all students in this submission.
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="confirmRejectAll" :disabled="!rejectionReason.trim()">
              <i class="bx bx-x me-1"></i>Reject All Students
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Reject Reason Modal for Selected Students -->
    <div class="modal fade" id="studentRejectReasonModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Reject Selected Students</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="studentRejectionReason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
              <textarea class="form-control" id="studentRejectionReason" v-model="studentRejectionReason" 
                        rows="4" placeholder="Please provide a reason for rejection..." 
                        :class="{ 'is-invalid': studentRejectionError }"></textarea>
              <div class="invalid-feedback" v-if="studentRejectionError">
                {{ studentRejectionError }}
              </div>
              <div class="form-text">
                This reason will be applied to the {{ selectedStudentMarks.length }} selected students.
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="confirmRejectSelectedStudents" :disabled="!studentRejectionReason.trim()">
              <i class="bx bx-x me-1"></i>Reject Selected Students
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Admin Edit Mark Modal -->
    <AdminEditMarkModal 
      :show="showEditMarkModal"
      :mark="selectedMarkForEdit"
      @close="closeEditMarkModal"
      @updated="handleMarkUpdated"
    />
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import AdminEditMarkModal from "@/Components/Exams/AdminEditMarkModal.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref, onMounted, computed, watch, inject } from "vue";
import axios from "axios";
import { Modal } from 'bootstrap';
import { usePage } from '@inertiajs/vue3';

const toast = inject('toast');

// Refs
const loading = ref(false);
const actionLoading = ref(false);
const pendingMarks = ref([]);
const selectedSubmissions = ref([]);
const selectedStudentMarks = ref([]);
const selectAll = ref(false);
const selectAllStudents = ref(false);
const currentSubmission = ref(null);
const submissionDetails = ref(null);
const rejectionReason = ref('');
const studentRejectionReason = ref('');
const rejectionError = ref('');
const studentRejectionError = ref('');
const stats = ref({});
const error = ref('');
const debugInfo = ref('');
const activeTab = ref('pending'); // 'pending' or 'approved'
const showEditMarkModal = ref(false);
const selectedMarkForEdit = ref(null);

// Pagination & Search
const searchQuery = ref('');
const modalSearchQuery = ref('');
const currentPage = ref(1);
const perPage = ref(10);
const totalItems = ref(0);
const lastPage = ref(1);
const searchTimeout = ref(null);

// Check if user has permission to edit approved marks
const page = usePage();
const canEditMarks = computed(() => {
  return page.props.auth?.user?.permissions?.includes('edit-approved-marks') || false;
});

// Computed properties
const lastUpdated = computed(() => {
  return new Date().toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
});

const hasPendingMarks = computed(() => {
  if (!submissionDetails.value) return false;
  return submissionDetails.value.marks.some(mark => mark.status === 'submitted');
});

const filteredModalMarks = computed(() => {
  if (!submissionDetails.value || !submissionDetails.value.marks) return [];
  
  if (!modalSearchQuery.value) return submissionDetails.value.marks;
  
  const query = modalSearchQuery.value.toLowerCase();
  return submissionDetails.value.marks.filter(mark => 
    mark.student_name.toLowerCase().includes(query) || 
    mark.admission_number.toLowerCase().includes(query)
  );
});

const visiblePages = computed(() => {
  const pages = [];
  const delta = 2;
  const left = currentPage.value - delta;
  const right = currentPage.value + delta + 1;
  
  for (let i = 1; i <= lastPage.value; i++) {
    if (i === 1 || i === lastPage.value || (i >= left && i < right)) {
      pages.push(i);
    } else if (pages[pages.length - 1] !== '...') {
      pages.push('...');
    }
  }
  return pages.filter(p => p !== '...'); // Simplified for now
});

// Methods
const loadApprovalQueue = async () => {
  try {
    loading.value = true;
    error.value = '';
    debugInfo.value = `Loading ${activeTab.value} queue...`;
    
    console.log(`🔄 Loading ${activeTab.value} queue...`);
    
    const endpoint = activeTab.value === 'pending' 
      ? '/admin/exams/approval-queue/pending-submissions'
      : '/admin/exams/approval-queue/approved-submissions';
      
    // Send pagination and search params for BOTH tabs
    const params = {
      search: searchQuery.value,
      page: currentPage.value,
      per_page: perPage.value
    };

    const response = await axios.get(endpoint, { params });
    console.log('✅ Response:', response.data);
    
    // Handle paginated response for BOTH tabs
    if (response.data && response.data.data) {
      pendingMarks.value = response.data.data;
      if (response.data.meta) {
        currentPage.value = response.data.meta.current_page;
        lastPage.value = response.data.meta.last_page;
        totalItems.value = response.data.meta.total;
      }
    } else {
      pendingMarks.value = [];
      totalItems.value = 0;
    }
    
    // Update stats if available
    if (response.data.debug) {
      debugInfo.value = `Loaded ${pendingMarks.value.length} submissions.`;
    }
  } catch (err) {
    console.error('❌ Error loading queue:', err);
    error.value = 'Failed to load approval queue. Please try again.';
    debugInfo.value = `Error: ${err.message}`;
    toast.error('Failed to load data');
  } finally {
    loading.value = false;
  }
};

const loadStats = async () => {
  try {
    const response = await axios.get('/admin/exams/approval-queue/stats');
    if (response.data && response.data.data) {
      stats.value = response.data.data;
    }
  } catch (err) {
    console.error('Error loading stats:', err);
    stats.value = {
      pending: pendingMarks.value.length,
      approved: 0,
      rejected: 0,
      total: pendingMarks.value.length
    };
  }
};

const fixMissingGrades = async () => {
  if (!confirm('This will fix missing grades for all submitted marks. Continue?')) return;
  
  try {
    loading.value = true;
    const response = await axios.post('/admin/exams/approval-queue/fix-missing-grades');
    
    if (response.data) {
      toast.success(response.data.message || 'Grades fixed successfully');
      await loadApprovalQueue();
    }
  } catch (err) {
    console.error('Error fixing grades:', err);
    toast.error('Failed to fix grades');
  } finally {
    loading.value = false;
  }
};

const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedSubmissions.value = pendingMarks.value.map(sub => sub.id);
  } else {
    selectedSubmissions.value = [];
  }
};

const toggleSelectAllStudents = () => {
  if (selectAllStudents.value && submissionDetails.value) {
    selectedStudentMarks.value = filteredModalMarks.value
      .filter(mark => mark.status === 'submitted')
      .map(mark => mark.id);
  } else {
    selectedStudentMarks.value = [];
  }
};

const clearSelection = () => {
  selectedSubmissions.value = [];
  selectAll.value = false;
};

const clearStudentSelection = () => {
  selectedStudentMarks.value = [];
  selectAllStudents.value = false;
};

const getSelectedStudentsCount = () => {
  return pendingMarks.value
    .filter(sub => selectedSubmissions.value.includes(sub.id))
    .reduce((total, sub) => total + sub.students_count, 0);
};

const viewSubmission = async (submission) => {
  try {
    currentSubmission.value = submission;
    submissionDetails.value = null;
    selectedStudentMarks.value = [];
    selectAllStudents.value = false;
    modalSearchQuery.value = ''; // Reset modal search query
    
    const modal = new Modal(document.getElementById('reviewModal'), {
      focus: false // Important: Allows nested/overlay modals to receive focus
    });
    modal.show();
    
    console.log('Loading details for submission:', submission.id);
    
    const response = await axios.get(`/admin/exams/approval-queue/${submission.id}/details`);
    
    if (response.data && response.data.data) {
      submissionDetails.value = response.data.data;
      console.log('Submission details loaded:', submissionDetails.value);
    } else {
      throw new Error('No data returned from details endpoint');
    }
    
  } catch (err) {
    console.error('Error loading submission details:', err);
    toast.error('Failed to load submission details');
    // Close modal if details can't be loaded
    closeModals();
  }
};

// Bulk submission approval (all students in submission)
const approveSubmission = async (submissionId) => {
  if (!confirm('Are you sure you want to approve ALL students in this submission?')) return;
  
  try {
    actionLoading.value = true;
    await axios.post('/admin/exams/approval-queue/approve-marks', {
      submission_ids: [submissionId]
    });
    
    toast.success('All students approved successfully');
    await loadApprovalQueue();
    await loadStats();
    closeModals();
  } catch (err) {
    console.error('Error approving submission:', err);
    toast.error('Failed to approve submission: ' + (err.response?.data?.message || err.message));
  } finally {
    actionLoading.value = false;
  }
};

// Individual student approval
const approveSingleStudent = async (markId) => {
  if (!confirm('Are you sure you want to approve this student?')) return;
  
  try {
    actionLoading.value = true;
    const response = await axios.post('/admin/exams/approval-queue/approve-student-marks', {
      mark_ids: [markId]
    });
    
    toast.success('Student approved successfully');
    
    // Instead of reloading details (which causes 404), update the local state
    if (submissionDetails.value && submissionDetails.value.marks) {
      const markIndex = submissionDetails.value.marks.findIndex(mark => mark.id === markId);
      if (markIndex !== -1) {
        submissionDetails.value.marks[markIndex].status = 'approved';
      }
    }
    
    // Remove from selected students
    selectedStudentMarks.value = selectedStudentMarks.value.filter(id => id !== markId);
    
    // Refresh the main queue to reflect changes
    await loadApprovalQueue();
    await loadStats();
    
  } catch (err) {
    console.error('Error approving student:', err);
    toast.error('Failed to approve student: ' + (err.response?.data?.message || err.message));
  } finally {
    actionLoading.value = false;
  }
};

// Approve all students in current submission
const approveAllStudents = async () => {
  if (!confirm('Are you sure you want to approve ALL students in this submission?')) return;
  
  try {
    actionLoading.value = true;
    await axios.post('/admin/exams/approval-queue/approve-marks', {
      submission_ids: [currentSubmission.value.id]
    });
    
    toast.success('All students approved successfully');
    closeModals();
    await loadApprovalQueue();
    await loadStats();
  } catch (err) {
    console.error('Error approving all students:', err);
    toast.error('Failed to approve all students: ' + (err.response?.data?.message || err.message));
  } finally {
    actionLoading.value = false;
  }
};

// Approve selected students
const approveSelectedStudents = async () => {
  if (!confirm(`Are you sure you want to approve ${selectedStudentMarks.length} selected students?`)) return;
  
  try {
    actionLoading.value = true;
    const response = await axios.post('/admin/exams/approval-queue/approve-student-marks', {
      mark_ids: selectedStudentMarks.value
    });
    
    toast.success(`${selectedStudentMarks.length} students approved successfully`);
    
    // Update local state instead of reloading
    if (submissionDetails.value && submissionDetails.value.marks) {
      submissionDetails.value.marks.forEach(mark => {
        if (selectedStudentMarks.value.includes(mark.id)) {
          mark.status = 'approved';
        }
      });
    }
    
    clearStudentSelection();
    
    // Refresh the main queue
    await loadApprovalQueue();
    await loadStats();
    
  } catch (err) {
    console.error('Error approving selected students:', err);
    toast.error('Failed to approve selected students: ' + (err.response?.data?.message || err.message));
  } finally {
    actionLoading.value = false;
  }
};

const rejectSubmission = async (submissionId) => {
  currentSubmission.value = { id: submissionId };
  showRejectReasonModal();
};

const rejectSingleStudent = async (markId) => {
  selectedStudentMarks.value = [markId];
  showStudentRejectModal();
};

const showRejectReasonModal = () => {
  rejectionReason.value = '';
  rejectionError.value = '';
  const reviewModal = Modal.getInstance(document.getElementById('reviewModal'));
  if (reviewModal) reviewModal.hide();
  
  const rejectModal = new Modal(document.getElementById('rejectReasonModal'));
  rejectModal.show();
};

const showStudentRejectModal = () => {
  studentRejectionReason.value = '';
  studentRejectionError.value = '';
  const studentRejectModal = new Modal(document.getElementById('studentRejectReasonModal'));
  studentRejectModal.show();
};

const showBulkRejectModal = () => {
  rejectionReason.value = '';
  rejectionError.value = '';
  const rejectModal = new Modal(document.getElementById('rejectReasonModal'));
  rejectModal.show();
};

const confirmRejectAll = async () => {
  if (!rejectionReason.value.trim()) {
    rejectionError.value = 'Rejection reason is required';
    return;
  }

  try {
    actionLoading.value = true;
    const submissionIds = currentSubmission.value ? [currentSubmission.value.id] : selectedSubmissions.value;
    
    await axios.post('/admin/exams/approval-queue/reject-marks', {
      submission_ids: submissionIds,
      rejection_reason: rejectionReason.value
    });
    
    const message = submissionIds.length > 1 
      ? `${submissionIds.length} submissions rejected` 
      : 'All students rejected successfully';
    
    toast.success(message);
    closeModals();
    await loadApprovalQueue();
    await loadStats();
    clearSelection();
  } catch (err) {
    console.error('Error rejecting submission:', err);
    toast.error('Failed to reject submission: ' + (err.response?.data?.message || err.message));
  } finally {
    actionLoading.value = false;
  }
};

const confirmRejectSelectedStudents = async () => {
  if (!studentRejectionReason.value.trim()) {
    studentRejectionError.value = 'Rejection reason is required';
    return;
  }

  try {
    actionLoading.value = true;
    const response = await axios.post('/admin/exams/approval-queue/reject-student-marks', {
      mark_ids: selectedStudentMarks.value,
      rejection_reason: studentRejectionReason.value
    });
    
    toast.success(`${selectedStudentMarks.length} students rejected successfully`);
    
    // Update local state
    if (submissionDetails.value && submissionDetails.value.marks) {
      submissionDetails.value.marks.forEach(mark => {
        if (selectedStudentMarks.value.includes(mark.id)) {
          mark.status = 'rejected';
        }
      });
    }
    
    clearStudentSelection();
    closeStudentRejectModal();
    
    // Refresh main queue
    await loadApprovalQueue();
    await loadStats();
    
  } catch (err) {
    console.error('Error rejecting selected students:', err);
    toast.error('Failed to reject selected students: ' + (err.response?.data?.message || err.message));
  } finally {
    actionLoading.value = false;
  }
};

const bulkApprove = async () => {
  if (!confirm(`Are you sure you want to approve ${selectedSubmissions.value.length} submissions?`)) return;
  
  try {
    actionLoading.value = true;
    await axios.post('/admin/exams/approval-queue/approve-marks', {
      submission_ids: selectedSubmissions.value
    });
    
    toast.success(`${selectedSubmissions.value.length} submissions approved successfully`);
    await loadApprovalQueue();
    await loadStats();
    clearSelection();
  } catch (err) {
    console.error('Error bulk approving:', err);
    toast.error('Failed to bulk approve: ' + (err.response?.data?.message || err.message));
  } finally {
    actionLoading.value = false;
  }
};

const closeModals = () => {
  const modals = ['reviewModal', 'rejectReasonModal', 'studentRejectReasonModal'];
  modals.forEach(modalId => {
    const modal = Modal.getInstance(document.getElementById(modalId));
    if (modal) modal.hide();
  });
};

const closeStudentRejectModal = () => {
  const modal = Modal.getInstance(document.getElementById('studentRejectReasonModal'));
  if (modal) modal.hide();
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString();
};

const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleString();
};

const isRecentSubmission = (dateString) => {
  if (!dateString) return false;
  const submissionDate = new Date(dateString);
  const now = new Date();
  const hoursDiff = (now - submissionDate) / (1000 * 60 * 60);
  return hoursDiff < 24;
};

const calculateGrade = (marksObtained, maximumMarks) => {
  if (!maximumMarks || maximumMarks <= 0) return 'N/A';
  
  const percentage = (marksObtained / maximumMarks) * 100;
  
  if (percentage >= 80) return 'A';
  if (percentage >= 70) return 'B';
  if (percentage >= 60) return 'C';
  if (percentage >= 50) return 'D';
  if (percentage >= 40) return 'E';
  return 'F';
};

const getGradeBadgeClass = (grade) => {
  const gradeClasses = {
    'A': 'bg-success text-white',
    'B': 'bg-primary text-white',
    'C': 'bg-info text-white',
    'D': 'bg-warning text-white',
    'E': 'bg-danger text-white',
    'F': 'bg-dark text-white'
  };
  return gradeClasses[grade] || 'bg-secondary text-white';
};

const getStatusBadgeClass = (status) => {
  const statusClasses = {
    'submitted': 'bg-warning text-dark',
    'approved': 'bg-success text-white',
    'rejected': 'bg-danger text-white'
  };
  return statusClasses[status] || 'bg-secondary text-white';
};

const getPercentageClass = (percentage) => {
  if (percentage >= 80) return 'text-success fw-bold';
  if (percentage >= 60) return 'text-primary';
  if (percentage >= 40) return 'text-warning';
  return 'text-danger';
};

const exportToExcel = () => {
  toast.info('Export feature coming soon');
};

const switchTab = (tab) => {
  if (activeTab.value === tab) return;
  activeTab.value = tab;
  // Reset pagination when switching tabs
  currentPage.value = 1;
  searchQuery.value = '';
  loadApprovalQueue();
};

const handleSearch = () => {
  if (searchTimeout.value) clearTimeout(searchTimeout.value);
  searchTimeout.value = setTimeout(() => {
    currentPage.value = 1; // Reset to first page on search
    loadApprovalQueue();
  }, 500); // Debounce search
};

const handlePerPageChange = () => {
  currentPage.value = 1; // Reset to first page when changing per page
  loadApprovalQueue();
};

const changePage = (page) => {
  if (page < 1 || page > lastPage.value) return;
  currentPage.value = page;
  loadApprovalQueue();
};

// Admin Edit Mark Modal Methods
const openEditMarkModal = (mark) => {
  selectedMarkForEdit.value = mark;
  showEditMarkModal.value = true;
};

const closeEditMarkModal = () => {
  showEditMarkModal.value = false;
  selectedMarkForEdit.value = null;
};

const handleMarkUpdated = async (updatedData) => {
  // Update the mark in the local state for immediate feedback
  if (submissionDetails.value && submissionDetails.value.marks) {
    const markIndex = submissionDetails.value.marks.findIndex(m => m.id === updatedData.id);
    if (markIndex !== -1) {
      submissionDetails.value.marks[markIndex].marks_obtained = updatedData.new_marks;
      submissionDetails.value.marks[markIndex].grade = updatedData.new_grade;
      submissionDetails.value.marks[markIndex].percentage = updatedData.percentage;
    }
  }
  
  // Re-fetch the submission details to ensure we have the full, correct state from server
  // We use submissionDetails (which contains the current view) instead of selectedSubmission (which is undefined/loop variable)
  if (submissionDetails.value) {
    // The viewSubmission function expects a submission object, but we might only need to reload the current one
    // So we can just call the endpoint directly or use the existing data to recall viewSubmission
    // But since viewSubmission expects the loop object, we can't easily call it.
    // Instead we'll just reload the details by ID
    const submissionId = submissionDetails.value.group_id || submissionDetails.value.id;
    // We can't access `selectedSubmission` here as it was a v-for variable
    // But we don't strictly need to re-call viewSubmission if we updated the local state above.
    // If we really want to reload, we need the original submission object or to refactor how viewSubmission works.
    // For now, updating local state (done above) is sufficient for immediate feedback.
    // If we want to be safe, we can just reload the queue:
  }
  
  // Refresh the approval queue list
  await loadApprovalQueue();
  await loadStats();
};

// Watchers
watch(selectedSubmissions, (newVal) => {
  selectAll.value = newVal.length === pendingMarks.value.length && pendingMarks.value.length > 0;
});

watch(selectedStudentMarks, (newVal) => {
  if (!submissionDetails.value) return;
  
  const submittedMarks = submissionDetails.value.marks.filter(mark => mark.status === 'submitted');
  selectAllStudents.value = newVal.length === submittedMarks.length && submittedMarks.length > 0;
});

// Lifecycle
onMounted(() => {
  loadApprovalQueue();
  loadStats();
});
</script>

<style scoped>
.table-hover tbody tr:hover {
  background-color: rgba(0, 123, 255, 0.04);
}

.badge {
  font-size: 0.75em;
}

.btn-group-sm > .btn {
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
}

.card-body {
  padding: 1.25rem;
}

.modal-xl {
  max-width: 1200px;
}

.text-truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Consistent white card styling */
.card-body.bg-white {
  background-color: #ffffff !important;
  border: 1px solid #dee2e6;
  border-radius: 0.375rem;
}

.card-body.bg-white:hover {
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.alert-sm {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

.sticky-top {
  position: sticky;
  top: 0;
  background: white;
  z-index: 10;
}

/* Status colors */
.table-success {
  background-color: rgba(25, 135, 84, 0.05) !important;
}

.table-danger {
  background-color: rgba(220, 53, 69, 0.05) !important;
}
</style>