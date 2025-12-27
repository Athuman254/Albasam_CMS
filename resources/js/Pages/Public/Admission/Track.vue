<template>
  <Head title="Application Status" />
  
  <div class="track-result-page bg-light min-vh-100 py-5" :class="{ 'py-2': is_modal }">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div v-if="!is_modal" class="d-flex align-items-center mb-4">
            <Link :href="route('public.admission.track.form')" class="btn btn-white shadow-sm rounded-circle me-3">
              <i class="fas fa-arrow-left"></i>
            </Link>
            <h2 class="fw-bold mb-0">Application Details</h2>
          </div>
          <div v-else class="mb-4">
             <h4 class="fw-bold">Application Details</h4>
          </div>

          <div class="card shadow-lg border-0 rounded-4 overflow-hidden mb-4">
            <div class="p-4 p-md-5 bg-white border-bottom">
              <div class="row align-items-center">
                <div class="col-md-8">
                  <span class="badge text-uppercase px-3 py-2 rounded-pill mb-3" :class="'bg-' + application.status_badge">
                    {{ application.status }}
                  </span>
                  <h3 class="fw-bold mb-1">{{ application.full_name }}</h3>
                  <p class="text-muted mb-0">Applied for {{ application.rank?.name }} - {{ application.academic_year }}</p>
                </div>
                <div class="col-md-4 text-md-end mt-4 mt-md-0">
                  <div class="text-muted small text-uppercase fw-bold mb-1">Application ID</div>
                  <div class="h5 fw-bold text-primary mb-0">{{ application.application_number }}</div>
                </div>
              </div>
            </div>

            <div class="card-body p-4 p-md-5">
              <div class="timeline">
                <div class="timeline-item pb-4" :class="{ 'active': true }">
                  <div class="timeline-marker bg-success"></div>
                  <div class="timeline-content ps-4">
                    <h5 class="fw-bold mb-1">Application Submitted</h5>
                    <p class="text-muted small">{{ formatDate(application.created_at) }}</p>
                    <p class="small">Your application was successfully received.</p>
                  </div>
                </div>

                <div v-if="application.status !== 'pending'" class="timeline-item pb-4" :class="{ 'active': true }">
                  <div class="timeline-marker" :class="application.status === 'rejected' ? 'bg-danger' : 'bg-primary'"></div>
                  <div class="timeline-content ps-4">
                    <h5 class="fw-bold mb-1">
                      {{ application.status === 'approved' ? 'Application Approved' : 
                         application.status === 'rejected' ? 'Application Rejected' : 'Under Review' }}
                    </h5>
                    <p class="text-muted small">{{ formatDate(application.reviewed_at) }}</p>
                    <p v-if="application.admin_notes" class="bg-light p-3 rounded-3 border-start border-4 border-info">
                      <strong>Admin Message:</strong> {{ application.admin_notes }}
                    </p>
                  </div>
                </div>

                <div v-if="application.status === 'pending'" class="timeline-item pb-4">
                  <div class="timeline-marker bg-light border"></div>
                  <div class="timeline-content ps-4">
                    <h5 class="fw-bold text-muted mb-1">Pending Review</h5>
                    <p class="small text-muted">Our admissions team will contact you once reviewed.</p>
                  </div>
                </div>
              </div>

              <div v-if="application.status === 'approved'" class="alert alert-success rounded-4 p-4 mt-4 border-0">
                <div class="d-flex align-items-center">
                  <div class="me-3">
                    <i class="fas fa-graduation-cap fa-2x"></i>
                  </div>
                  <div>
                    <h5 class="fw-bold mb-1">Congratulations!</h5>
                    <p class="mb-0">You have been admitted. Please visit the school with original documents for final registration.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row g-4">
            <div class="col-md-6">
              <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h6 class="text-muted text-uppercase fw-bold small mb-3">Guardian Details</h6>
                <p class="mb-1 fw-bold">{{ application.guardian_name }}</p>
                <p class="mb-1 small"><i class="fas fa-envelope me-2 text-muted"></i>{{ application.guardian_email }}</p>
                <p class="mb-0 small"><i class="fas fa-phone me-2 text-muted"></i>{{ application.guardian_phone }}</p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h6 class="text-muted text-uppercase fw-bold small mb-3">Next Steps</h6>
                <ul class="list-unstyled small mb-0">
                  <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Monitor your status often</li>
                  <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Check your email for updates</li>
                  <li><i class="fas fa-check-circle text-success me-2"></i> Contact school office if needed</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
  application: Object,
  is_modal: Boolean,
});

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>

<style scoped>
.track-result-page {
  font-family: 'Inter', sans-serif;
}

.timeline {
  position: relative;
  padding-left: 20px;
}

.timeline::before {
  content: '';
  position: absolute;
  left: 31px;
  top: 5px;
  bottom: 0;
  width: 2px;
  background-color: #e2e8f0;
}

.timeline-item {
  position: relative;
}

.timeline-marker {
  position: absolute;
  left: -2px;
  top: 5px;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  z-index: 1;
}

.bg-info-light {
  background-color: rgba(13, 202, 240, 0.1);
}
</style>
