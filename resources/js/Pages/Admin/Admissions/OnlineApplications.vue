<template>
  <Head title="Online Admission Applications" />
  
  <DefaultLayout>
    <div class="row">
      <h3 class="mb-0">Online Applications</h3>
      <nav class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <Link :href="route('admin.dashboard')">Home</Link>
          </li>
          <li class="breadcrumb-item">
            <span class="text-muted">Admissions</span>
          </li>
          <li class="breadcrumb-item text-primary">
            Online Applications
          </li>
        </ol>
      </nav>

      <!-- Flash Messages -->
      <div v-if="$page.props.flash.success" class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ $page.props.flash.success }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>

      <div v-if="$page.props.flash.error" class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ $page.props.flash.error }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>

      <!-- Filters & Search -->
      <div class="card mb-4">
        <div class="card-body">
          <div class="row g-3 align-items-center">
            <div class="col-md-4">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input v-model="filterForm.filter.global" type="text" class="form-control" placeholder="Search by name, app #, or guardian..." @input="debounceSearch">
              </div>
            </div>
            <div class="col-md-3">
              <select v-model="filterForm.filter.status" class="form-select" @change="search">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="reviewed">Reviewed</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
              </select>
            </div>
            <div class="col-md-2">
              <button class="btn btn-outline-secondary w-100" @click="resetFilters">
                <i class="fas fa-undo me-1"></i> Reset
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Applications Table -->
      <div class="card shadow-sm border-0">
        <div class="table-responsive text-nowrap">
          <table class="table table-hover mb-0">
            <thead class="table-light">
              <tr>
                <th>App #</th>
                <th>Student Name</th>
                <th>Class/Stream</th>
                <th>Guardian</th>
                <th>Applied Date</th>
                <th>Status</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody class="table-border-bottom-0">
              <tr v-for="app in applications.data" :key="app.id">
                <td><span class="fw-bold">{{ app.application_number }}</span></td>
                <td>{{ app.first_name }} {{ app.last_name }}</td>
                <td>{{ app.rank?.name }} <span v-if="app.division">({{ app.division.name }})</span></td>
                <td>
                  <div>{{ app.guardian_name }}</div>
                  <small class="text-muted">{{ app.guardian_phone }}</small>
                </td>
                <td>{{ formatDate(app.created_at) }}</td>
                <td>
                  <span class="badge" :class="'bg-label-' + getStatusColor(app.status)">
                    {{ app.status }}
                  </span>
                </td>
                <td class="text-center">
                  <div class="dropdown">
                    <button class="btn align-text-top py-1" type="button" data-bs-toggle="dropdown">
                      <i class="icon-base bx bx-dots-vertical"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                      <button class="dropdown-item" @click="viewApplication(app)">
                        <i class="icon-base bx bx-detail me-1"></i> Details
                      </button>
                      <button v-if="app.status !== 'approved' && app.status !== 'rejected'" 
                              class="dropdown-item" 
                              @click="showReviewModal(app)">
                        <i class="icon-base bx bx-check-circle me-1"></i> Review & Action
                      </button>
                    </div>
                  </div>
                </td>
              </tr>
              <tr v-if="applications.data.length === 0">
                <td colspan="7" class="text-center py-5 text-muted">No applications found.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="card-footer bg-white border-0">
          <Pagination :links="applications.links" />
        </div>
      </div>
    </div>

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <form class="modal-content" @submit.prevent="submitReview">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title text-white">Review Application: {{ selectedApp?.application_number }}</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div class="row g-4 mb-4">
              <div class="col-md-6">
                <h6>Student Details</h6>
                <p class="mb-1"><strong>Full Name:</strong> {{ selectedApp?.first_name }} {{ selectedApp?.middle_name }} {{ selectedApp?.last_name }}</p>
                <p class="mb-1"><strong>DOB:</strong> {{ selectedApp?.date_of_birth }}</p>
                <p class="mb-1" v-if="selectedApp?.assessment_number"><strong>Assessment Number:</strong> {{ selectedApp?.assessment_number }}</p>
                <p class="mb-1"><strong>Class Applying:</strong> {{ selectedApp?.rank?.name }}</p>
                <p class="mb-1"><strong>Previous School:</strong> {{ selectedApp?.previous_school || 'N/A' }}</p>
              </div>
              <div class="col-md-6">
                <h6>Guardian Details</h6>
                <p class="mb-1"><strong>Name:</strong> {{ selectedApp?.guardian_name }}</p>
                <p class="mb-1"><strong>Phone:</strong> {{ selectedApp?.guardian_phone }}</p>
                <p class="mb-1"><strong>Email:</strong> {{ selectedApp?.guardian_email }}</p>
              </div>
            </div>

            <hr>

            <div class="mt-4">
              <label class="form-label fw-bold">Admin Notes / Feedback</label>
              <textarea v-model="reviewForm.admin_notes" class="form-control" rows="3" placeholder="Add notes for the guardian or internal review..."></textarea>
            </div>

            <div class="mt-4 border-top pt-4 text-center">
              <div class="d-flex justify-content-center gap-3">
                <button type="button" class="btn btn-outline-danger px-4" @click="updateStatus('rejected')" :disabled="reviewForm.processing">
                  <i class="fas fa-times-circle me-1"></i> Reject
                </button>
                <button type="button" class="btn btn-outline-info px-4" @click="updateStatus('reviewed')" :disabled="reviewForm.processing">
                  <i class="fas fa-search me-1"></i> Mark Reviewed
                </button>
                <button type="button" class="btn btn-success px-4" @click="confirmApprove" :disabled="reviewForm.processing">
                  <i class="fas fa-user-plus me-1"></i> Approve & Enroll
                </button>
              </div>
            </div>
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
import { debounce } from 'lodash';

const props = defineProps({
  applications: Object,
  filters: Object,
});

const filterForm = ref({
  filter: {
    global: props.filters.global || '',
    status: props.filters.status || '',
  }
});

const selectedApp = ref(null);
const reviewForm = useForm({
  status: '',
  admin_notes: '',
});

let reviewModal = null;

onMounted(() => {
  reviewModal = new bootstrap.Modal(document.getElementById('reviewModal'));
});

const search = () => {
  router.get(route('admin.admission-applications.index'), filterForm.value, {
    preserveState: true,
    replace: true,
  });
};

const debounceSearch = debounce(search, 500);

const resetFilters = () => {
  filterForm.value.filter = { global: '', status: '' };
  search();
};

const getStatusColor = (status) => {
  switch (status) {
    case 'pending': return 'warning';
    case 'reviewed': return 'info';
    case 'approved': return 'success';
    case 'rejected': return 'danger';
    default: return 'secondary';
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const viewApplication = (app) => {
  selectedApp.value = app;
  reviewForm.admin_notes = app.admin_notes || '';
  reviewModal.show();
};

const showReviewModal = (app) => {
  selectedApp.value = app;
  reviewForm.admin_notes = app.admin_notes || '';
  reviewModal.show();
};

const updateStatus = (status) => {
  reviewForm.status = status;
  reviewForm.put(route('admin.admission-applications.update', selectedApp.value.id), {
    onSuccess: () => reviewModal.hide(),
  });
};

const confirmApprove = () => {
  if (confirm(`Are you sure you want to approve this application and create a student record for ${selectedApp.value.first_name}?`)) {
    reviewForm.post(route('admin.admission-applications.approve', selectedApp.value.id), {
      onSuccess: () => reviewModal.hide(),
    });
  }
};
</script>

<style scoped>
.bg-label-warning { background-color: #fff8e1; color: #ffab00; }
.bg-label-info { background-color: #e1f5fe; color: #03a9f4; }
.bg-label-success { background-color: #e8f5e9; color: #2e7d32; }
.bg-label-danger { background-color: #ffebee; color: #d32f2f; }

.timeline-marker {
  width: 12px;
  height: 12px;
  border-radius: 50%;
}
</style>
