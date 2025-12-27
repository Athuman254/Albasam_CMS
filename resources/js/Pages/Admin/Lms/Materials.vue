<template>
  <Head title="Lesson Materials" />
  
  <DefaultLayout>
    <div class="row">
      <h3 class="mb-0">Lesson Materials</h3>
      <nav class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <Link :href="route('admin.dashboard')">Home</Link>
          </li>
          <li class="breadcrumb-item">
            <span class="text-muted">LMS</span>
          </li>
          <li class="breadcrumb-item text-primary">
            Lesson Materials
          </li>
        </ol>
      </nav>

      <div class="col-12 mb-4">
        <div class="d-flex justify-content-end">
          <button class="btn btn-primary" @click="showUploadModal">
            <i class="fas fa-upload me-1"></i> Upload Material
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

      <!-- Materials List -->
      <div class="row g-4">
        <div v-for="material in materials.data" :key="material.id" class="col-md-6 col-lg-4 col-xl-3">
          <div class="card h-100 border-0 shadow-sm material-card">
            <div class="card-body p-4">
              <div class="d-flex align-items-start justify-content-between mb-3">
                <div class="avatar bg-light-primary rounded p-2 text-primary">
                  <i :class="getIcon(material.material_type)" class="fa-2x"></i>
                </div>
                <div class="dropdown">
                  <button class="btn btn-flush p-0" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v text-muted"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" :href="getDownloadLink(material)" target="_blank">
                      <i class="fas fa-download me-2"></i> {{ material.material_type === 'link' ? 'Open Link' : 'Download' }}
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><button class="dropdown-item text-danger" @click="deleteMaterial(material)">
                      <i class="fas fa-trash-alt me-2"></i> Delete
                    </button></li>
                  </ul>
                </div>
              </div>
              <h5 class="fw-bold mb-1 text-truncate" :title="material.title">{{ material.title }}</h5>
              <div class="mb-3">
                <span class="badge bg-label-info me-1">{{ material.subject?.name }}</span>
                <span class="badge bg-label-secondary">{{ material.rank?.name }}</span>
              </div>
              <p class="text-muted small mb-3 line-clamp-2" style="min-height: 38px;">{{ material.description || 'No description provided.' }}</p>
              
              <div class="mt-auto pt-3 border-top">
                <div class="d-flex align-items-center">
                  <div class="small">
                    <p class="mb-0 fw-bold">{{ material.teacher?.name }}</p>
                    <p class="mb-0 text-muted smaller tracking-tight">{{ formatDate(material.created_at) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div v-if="materials.data.length === 0" class="col-12 text-center py-5">
          <div class="bg-white p-5 rounded-4 shadow-sm d-inline-block w-100">
            <i class="fas fa-folder-open fa-4x text-light mb-3"></i>
            <h4 class="text-muted">No materials found</h4>
            <p class="text-muted mb-4">Start by uploading your first lesson material for students.</p>
            <button class="btn btn-primary px-4" @click="showUploadModal">Upload Now</button>
          </div>
        </div>
      </div>

      <div class="mt-5">
        <Pagination :links="materials.links" />
      </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <form class="modal-content" @submit.prevent="submitUpload">
          <div class="modal-header">
            <h5 class="modal-title">Upload Lesson Material</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
            <div class="mb-3">
              <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
              <input v-model="uploadForm.title" type="text" class="form-control" placeholder="Chapter 1: Intro to Physics" required>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">Subject <span class="text-danger">*</span></label>
                <select v-model="uploadForm.subject_id" class="form-select" required>
                  <option value="">Select Subject</option>
                  <option v-for="subject in subjects" :key="subject.id" :value="subject.id">{{ subject.name }}</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Class <span class="text-danger">*</span></label>
                <select v-model="uploadForm.rank_id" class="form-select" required>
                  <option value="">Select Class</option>
                  <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                </select>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold">Material Type <span class="text-danger">*</span></label>
              <div class="d-flex gap-3 mt-1">
                <div v-for="type in materialTypes" :key="type.value" class="form-check custom-option custom-option-icon border rounded-3 p-3 flex-fill text-center cursor-pointer" 
                     :class="{ 'border-primary bg-light-primary': uploadForm.material_type === type.value }"
                     @click="uploadForm.material_type = type.value">
                  <i :class="type.icon" class="fa-lg d-block mb-1"></i>
                  <span class="smaller">{{ type.label }}</span>
                </div>
              </div>
            </div>

            <div v-if="uploadForm.material_type !== 'link'" class="mb-3">
              <label class="form-label fw-bold">File <span class="text-danger">*</span></label>
              <input type="file" class="form-control" @change="handleFileUpload" required>
              <div class="form-text smaller">Max size: 20MB. Supports PDF, DOC, Video, Audio.</div>
            </div>

            <div v-else class="mb-3">
              <label class="form-label fw-bold">Link (URL) <span class="text-danger">*</span></label>
              <input v-model="uploadForm.link" type="url" class="form-control" placeholder="https://example.com/video-lesson" required>
            </div>

            <div class="mb-0">
              <label class="form-label fw-bold">Description (Optional)</label>
              <textarea v-model="uploadForm.description" class="form-control" rows="3" placeholder="Briefly describe what this material covers..."></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary px-4" :disabled="uploadForm.processing">
              <span v-if="uploadForm.processing">Uploading...</span>
              <span v-else>Confirm Upload</span>
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
  materials: Object,
  subjects: Array,
  classes: Array,
  filters: Object,
});

const filterForm = ref({
  subject_id: props.filters.subject_id || '',
  rank_id: props.filters.rank_id || '',
});

const materialTypes = [
  { value: 'document', label: 'PDF/Doc', icon: 'fas fa-file-pdf text-danger' },
  { value: 'video', label: 'Video', icon: 'fas fa-video text-info' },
  { value: 'link', label: 'Link', icon: 'fas fa-link text-primary' },
  { value: 'audio', label: 'Audio', icon: 'fas fa-music text-warning' },
];

const uploadForm = useForm({
  title: '',
  subject_id: '',
  rank_id: '',
  description: '',
  material_type: 'document',
  file: null,
  link: '',
});

let uploadModal = null;

onMounted(() => {
  uploadModal = new bootstrap.Modal(document.getElementById('uploadModal'));
});

const search = () => {
  router.get(route('admin.lms.materials.index'), filterForm.value, {
    preserveState: true,
    replace: true,
  });
};

const resetFilters = () => {
  filterForm.value = { subject_id: '', rank_id: '' };
  search();
};

const showUploadModal = () => {
  uploadForm.reset();
  uploadModal.show();
};

const handleFileUpload = (e) => {
  uploadForm.file = e.target.files[0];
};

const submitUpload = () => {
  uploadForm.post(route('admin.lms.materials.store'), {
    onSuccess: () => {
      uploadModal.hide();
      uploadForm.reset();
    },
  });
};

const deleteMaterial = (material) => {
  if (confirm(`Are you sure you want to delete "${material.title}"?`)) {
    router.delete(route('admin.lms.materials.destroy', material.id));
  }
};

const getIcon = (type) => {
  switch (type) {
    case 'document': return 'fas fa-file-pdf text-danger';
    case 'video': return 'fas fa-video text-info';
    case 'link': return 'fas fa-link text-primary';
    case 'audio': return 'fas fa-music text-warning';
    default: return 'fas fa-file-alt';
  }
};

const getDownloadLink = (material) => {
  if (material.material_type === 'link') return material.file_path;
  return '/storage/' + material.file_path;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
};
</script>

<style scoped>
.bg-light-primary { background-color: rgba(105, 108, 255, 0.1); }
.text-primary { color: #696cff !important; }
.material-card { transition: all 0.2s ease-in-out; }
.material-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
.cursor-pointer { cursor: pointer; }
.smaller { font-size: 0.75rem; }
.tracking-tight { letter-spacing: -0.01em; }
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;  
  overflow: hidden;
}
.bg-label-info { background-color: #e1f5fe; color: #03a9f4; }
.bg-label-secondary { background-color: #f1f1f1; color: #6c757d; }
</style>
