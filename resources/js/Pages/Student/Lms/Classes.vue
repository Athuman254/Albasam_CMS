<template>
  <Head title="Online Classes" />
  
  <DefaultLayout>
    <div class="row">
      <div class="col-12 mb-4">
        <h3 class="mb-0">Online Classes</h3>
        <nav class="mb-3">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><Link :href="route('student.lms.dashboard')">LMS</Link></li>
            <li class="breadcrumb-item active">Classes</li>
          </ol>
        </nav>
      </div>

      <div v-if="onlineClasses.data.length > 0" class="row g-4">
        <div v-for="cls in onlineClasses.data" :key="cls.id" class="col-md-6 col-lg-4">
          <div class="card h-100 border-0 shadow-sm class-card overflow-hidden">
            <!-- Platform Header -->
            <div class="card-header border-0 py-3 d-flex justify-content-between align-items-center" 
                 :class="platformClass(cls.meeting_platform)">
              <span class="badge bg-white text-dark text-uppercase shadow-sm">{{ cls.meeting_platform }}</span>
              <span class="badge rounded-pill shadow-xs" :class="statusBadge(cls.status)">
                {{ cls.status }}
              </span>
            </div>
            
            <div class="card-body p-4">
              <div class="d-flex align-items-center mb-3">
                <div class="me-3 p-2 bg-light-primary rounded shadow-xs">
                  <i class="fas fa-video fa-lg text-primary"></i>
                </div>
                <div class="overflow-hidden">
                  <h5 class="fw-bold mb-0 text-truncate" :title="cls.title">{{ cls.title }}</h5>
                  <small class="text-muted">{{ cls.subject?.name }}</small>
                </div>
              </div>
              
              <div class="bg-light p-3 rounded-3 mb-4">
                <div class="d-flex justify-content-between mb-2">
                  <span class="smaller text-muted"><i class="fas fa-calendar me-1"></i> Date</span>
                  <span class="smaller fw-bold">{{ formatDate(cls.scheduled_at) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span class="smaller text-muted"><i class="fas fa-clock me-1"></i> Time</span>
                  <span class="smaller fw-bold text-primary">{{ formatTime(cls.scheduled_at) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="smaller text-muted"><i class="fas fa-hourglass-half me-1"></i> Duration</span>
                  <span class="smaller fw-bold">{{ cls.duration_minutes }} Minutes</span>
                </div>
              </div>

              <p class="text-muted smaller mb-4 line-clamp-2" style="min-height: 34px;">
                {{ cls.description || 'Join this live session for a deep dive into the topic.' }}
              </p>

              <div class="d-grid mt-auto">
                <div v-if="isJoinable(cls)">
                  <a :href="cls.meeting_link" target="_blank" class="btn btn-primary rounded-pill py-2 shadow-sm w-100">
                    <i class="fas fa-external-link-alt me-2"></i> Join Live Session
                  </a>
                </div>
                <div v-else-if="isUpcoming(cls)">
                  <button class="btn btn-light rounded-pill py-2 w-100" disabled>
                    <i class="fas fa-clock me-2"></i> Starts soon
                  </button>
                </div>
                <div v-else>
                  <button class="btn btn-outline-secondary rounded-pill py-2 w-100" disabled>
                    <i class="fas fa-times-circle me-2"></i> Session Ended
                  </button>
                </div>
              </div>
              
              <div v-if="cls.meeting_id" class="mt-3 text-center p-2 bg-light rounded-pill border border-dashed">
                <code class="smaller text-muted">ID: {{ cls.meeting_id }} <span v-if="cls.meeting_password">| Pass: {{ cls.meeting_password }}</span></code>
              </div>
            </div>
            
            <div class="card-footer bg-white border-top-0 py-3 text-center border-top">
              <small class="text-muted smaller">
                <i class="fas fa-user-tie me-1"></i> Hosted by {{ cls.teacher?.name }}
              </small>
            </div>
          </div>
        </div>
        
        <div class="col-12 mt-4">
          <Pagination :links="onlineClasses.links" />
        </div>
      </div>

      <div v-else class="col-12 text-center py-5">
        <div class="bg-white p-5 rounded-4 shadow-sm">
          <i class="fas fa-laptop-house fa-4x text-light mb-3"></i>
          <h4 class="text-muted">No classes scheduled</h4>
          <p class="text-muted">There are no upcoming virtual classes for your class at the moment.</p>
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
  onlineClasses: Object
});

const isJoinable = (cls) => {
  const now = new Date();
  const start = new Date(cls.scheduled_at);
  const end = new Date(start.getTime() + (cls.duration_minutes || 60) * 60000);
  return now >= start && now <= end;
};

const isUpcoming = (cls) => {
  const now = new Date();
  const start = new Date(cls.scheduled_at);
  return now < start;
};

const platformClass = (platform) => {
  switch (platform) {
    case 'zoom': return 'bg-zoom text-white';
    case 'google_meet': return 'bg-meet text-white';
    case 'teams': return 'bg-teams text-white';
    default: return 'bg-primary text-white';
  }
};

const statusBadge = (status) => {
  switch (status) {
    case 'ongoing': return 'bg-success text-white';
    case 'completed': return 'bg-secondary text-white';
    default: return 'bg-light text-dark';
  }
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
};

const formatTime = (date) => {
  return new Date(date).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
};
</script>

<style scoped>
.class-card { border-radius: 16px; transition: all 0.2s; }
.class-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; }
.bg-light-primary { background-color: rgba(105, 108, 255, 0.1); }
.bg-zoom { background-color: #2D8CFF; }
.bg-meet { background-color: #00897B; }
.bg-teams { background-color: #464EB8; }
.smaller { font-size: 0.75rem; }
.shadow-xs { box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.border-dashed { border-style: dashed !important; }
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;  
  overflow: hidden;
}
</style>
