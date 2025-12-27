<template>
  <Head title="Lesson Materials" />
  
  <DefaultLayout>
    <div class="row">
      <div class="col-12 mb-4">
        <h3 class="mb-0">Lesson Materials</h3>
        <nav class="mb-3">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><Link :href="route('student.lms.dashboard')">LMS</Link></li>
            <li class="breadcrumb-item active">Materials</li>
          </ol>
        </nav>
      </div>

      <!-- Materials List -->
      <div v-if="materials.data.length > 0" class="row g-4">
        <div v-for="material in materials.data" :key="material.id" class="col-md-6 col-lg-4 col-xl-3">
          <div class="card h-100 border-0 shadow-sm material-card">
            <div class="card-body p-4 d-flex flex-column">
              <div class="d-flex align-items-center mb-3">
                <div class="avatar bg-light-primary rounded p-2 text-primary me-3">
                  <i :class="getIcon(material.material_type)" class="fa-2x"></i>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                  <h6 class="fw-bold mb-0 text-truncate" :title="material.title">{{ material.title }}</h6>
                  <span class="badge bg-label-info smaller">{{ material.subject?.name }}</span>
                </div>
              </div>
              
              <p class="text-muted small mb-3 line-clamp-3 flex-grow-1">{{ material.description || 'No description provided.' }}</p>
              
              <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                <div class="small text-muted">
                  <p class="mb-0 smaller">By {{ material.teacher?.name }}</p>
                  <p class="mb-0 smaller">{{ formatDate(material.created_at) }}</p>
                </div>
                <a :href="getDownloadLink(material)" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3">
                  <i :class="material.material_type === 'link' ? 'fas fa-external-link-alt' : 'fas fa-download'" class="me-1"></i>
                  {{ material.material_type === 'link' ? 'Open' : 'Get' }}
                </a>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-12 mt-5">
          <Pagination :links="materials.links" />
        </div>
      </div>
      
      <div v-else class="col-12 text-center py-5">
        <div class="bg-white p-5 rounded-4 shadow-sm">
          <i class="fas fa-folder-open fa-4x text-light mb-3"></i>
          <h4 class="text-muted">No materials found</h4>
          <p class="text-muted">There are no lesson materials uploaded for your class yet.</p>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
  materials: Object
});

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
.material-card { transition: all 0.2s ease-in-out; border-radius: 12px; }
.material-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
.smaller { font-size: 0.7rem; }
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;  
  overflow: hidden;
}
.bg-label-info { background-color: #e1f5fe; color: #03a9f4; }
</style>
