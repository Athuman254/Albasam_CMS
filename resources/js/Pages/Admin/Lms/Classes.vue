<template>
  <Head title="Online Classes" />
  
  <DefaultLayout>
    <div class="row">
      <h3 class="mb-0">Online Classes</h3>
      <nav class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <Link :href="route('admin.dashboard')">Home</Link>
          </li>
          <li class="breadcrumb-item">
            <span class="text-muted">LMS</span>
          </li>
          <li class="breadcrumb-item text-primary">
            Online Classes
          </li>
        </ol>
      </nav>

      <div class="col-12 mb-4">
        <div class="d-flex justify-content-end">
          <button class="btn btn-primary" @click="showCreateModal">
            <i class="fas fa-video me-1"></i> Schedule Class
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-3">
              <label class="form-label small fw-bold">Subject</label>
              <select v-model="filterForm.subject_id" class="form-select" @change="search">
                <option value="">All Subjects</option>
                <option v-for="subject in subjects" :key="subject.id" :value="subject.id">{{ subject.name }}</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label small fw-bold">Class</label>
              <select v-model="filterForm.rank_id" class="form-select" @change="search">
                <option value="">All Classes</option>
                <option v-for="cls in ranks" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
              </select>
            </div>
             <div class="col-md-3">
              <label class="form-label small fw-bold">Status</label>
              <select v-model="filterForm.status" class="form-select" @change="search">
                <option value="">All Statuses</option>
                <option value="scheduled">Scheduled</option>
                <option value="ongoing">Ongoing</option>
                <option value="completed">Completed</option>
              </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
              <button class="btn btn-outline-secondary w-100" @click="resetFilters">
                <i class="fas fa-undo me-1"></i> Reset
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Classes List -->
      <div class="row g-4">
        <div v-for="cls in onlineClasses.data" :key="cls.id" class="col-md-6 col-xl-4">
          <div class="card h-100 border-0 shadow-sm overflow-hidden">
            <div class="card-header bg-primary py-3 d-flex justify-content-between align-items-center">
              <span class="badge bg-white text-primary text-uppercase">{{ cls.meeting_platform }}</span>
              <div class="dropdown">
                <button class="btn btn-sm btn-icon text-white" type="button" data-bs-toggle="dropdown">
                  <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><button class="dropdown-item text-danger" @click="deleteClass(cls)">
                    <i class="fas fa-trash-alt me-2"></i> Delete
                  </button></li>
                </ul>
              </div>
            </div>
            <div class="card-body p-4">
              <div class="d-flex align-items-center mb-3">
                <div class="me-3 p-2 bg-light-primary rounded">
                  <i class="fas fa-video fa-lg text-primary"></i>
                </div>
                <div>
                  <h5 class="fw-bold mb-0 text-truncate" style="max-width: 200px;">{{ cls.title }}</h5>
                  <small class="text-muted">{{ cls.subject?.name }} | {{ cls.rank?.name }}</small>
                </div>
              </div>
              
              <div class="bg-light p-3 rounded-3 mb-4">
                <div class="d-flex justify-content-between mb-2">
                  <span class="small text-muted"><i class="fas fa-calendar me-1"></i> Date</span>
                  <span class="small fw-bold">{{ formatDateTime(cls.scheduled_at) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="small text-muted"><i class="fas fa-clock me-1"></i> Duration</span>
                  <span class="small fw-bold">{{ cls.duration_minutes }} Min</span>
                </div>
              </div>

              <div class="d-grid">
                <a :href="cls.meeting_link" target="_blank" class="btn btn-primary rounded-pill">
                  <i class="fas fa-external-link-alt me-1"></i> Join Meeting
                </a>
              </div>
              
              <div v-if="cls.meeting_id" class="mt-3 text-center">
                <small class="text-muted">ID: {{ cls.meeting_id }} <span v-if="cls.meeting_password">| Pass: {{ cls.meeting_password }}</span></small>
              </div>
            </div>
            <div class="card-footer bg-white border-top-0 py-3 text-center">
              <small class="text-muted">Scheduled by {{ cls.teacher?.name }}</small>
            </div>
          </div>
        </div>

        <div v-if="onlineClasses.data.length === 0" class="col-12 text-center py-5">
           <div class="bg-white p-5 rounded-4 shadow-sm">
            <i class="fas fa-laptop-house fa-4x text-light mb-3"></i>
            <h4 class="text-muted">No online classes scheduled</h4>
            <p class="text-muted mb-4">Set up your virtual classroom and share links with your students.</p>
            <button class="btn btn-primary px-4" @click="showCreateModal">Schedule Now</button>
          </div>
        </div>
      </div>

      <div class="mt-5">
        <Pagination :links="onlineClasses.links" />
      </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <form class="modal-content" @submit.prevent="submitCreate">
          <div class="modal-header">
            <h5 class="modal-title">Schedule Online Class</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div class="mb-3">
              <label class="form-label fw-bold">Class Title <span class="text-danger">*</span></label>
              <input v-model="form.title" type="text" class="form-control" placeholder="Discussion on Photosynthesis" required>
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
                  <option v-for="cls in ranks" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                </select>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-4">
                <label class="form-label fw-bold">Date <span class="text-danger">*</span></label>
                <input v-model="form.date_only" type="date" class="form-control" required>
              </div>
              <div class="col-md-2">
                <label class="form-label fw-bold">Time <span class="text-danger">*</span></label>
                <input v-model="form.time_only" type="time" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Duration (Min) <span class="text-danger">*</span></label>
                <input v-model="form.duration_minutes" type="number" class="form-control" min="5" placeholder="40" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Platform <span class="text-danger">*</span></label>
              <select v-model="form.meeting_platform" class="form-select" required>
                <option value="zoom">Zoom</option>
                <option value="google_meet">Google Meet</option>
                <option value="teams">Microsoft Teams</option>
                <option value="other">Other</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Meeting Link <span class="text-danger">*</span></label>
              <input v-model="form.meeting_link" type="url" class="form-control" placeholder="https://zoom.us/j/..." required>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">Meeting ID (Opt)</label>
                <input v-model="form.meeting_id" type="text" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Passcode (Opt)</label>
                <input v-model="form.meeting_password" type="text" class="form-control">
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4" :disabled="form.processing">
              <span v-if="form.processing">Scheduling...</span>
              <span v-else>Confirm Schedule</span>
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
  onlineClasses: Object,
  subjects: Array,
  ranks: Array,
  filters: Object,
});

const filterForm = ref({
  subject_id: props.filters.subject_id || '',
  rank_id: props.filters.rank_id || '',
  status: props.filters.status || '',
});

const form = useForm({
  title: '',
  subject_id: '',
  rank_id: '',
  description: '',
  meeting_link: '',
  meeting_platform: 'zoom',
  meeting_id: '',
  meeting_password: '',
  scheduled_at: '',
  date_only: '',
  time_only: '',
  duration_minutes: 40,
});

let createModal = null;

onMounted(() => {
  createModal = new bootstrap.Modal(document.getElementById('createModal'));
});

const search = () => {
  router.get(route('admin.lms.classes.index'), filterForm.value, {
    preserveState: true,
    replace: true,
  });
};

const resetFilters = () => {
  filterForm.value = { subject_id: '', rank_id: '', status: '' };
  search();
};

const showCreateModal = () => {
  form.reset();
  createModal.show();
};

const submitCreate = () => {
  if (form.date_only && form.time_only) {
    form.scheduled_at = `${form.date_only} ${form.time_only}`;
  }
  
  form.post(route('admin.lms.classes.store'), {
    onSuccess: () => {
      createModal.hide();
      form.reset();
    },
  });
};

const deleteClass = (cls) => {
  if (confirm(`Are you sure you want to delete "${cls.title}"?`)) {
    router.delete(route('admin.lms.classes.destroy', cls.id));
  }
};

const formatDateTime = (date) => {
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
.bg-light-primary { background-color: rgba(105, 108, 255, 0.1); }
</style>
