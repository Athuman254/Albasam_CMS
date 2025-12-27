<template>
  <Head title="LMS Dashboard" />
  
  <DefaultLayout>
    <div class="row">
      <div class="col-12 mb-4">
        <h3 class="mb-0">Learning Portal - {{ studentClass }}</h3>
        <p class="text-muted">Welcome back! Access your lessons, assignments, and virtual classes here.</p>
      </div>

      <!-- Quick Stats / Cards -->
      <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100 py-2">
          <div class="card-body d-flex align-items-center">
            <div class="avatar bg-label-primary rounded p-3 me-3 text-primary position-relative">
              <i class="fas fa-book-open fa-2x"></i>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-primary border border-white" style="font-size: 0.65rem;">{{ counts.materials }}</span>
            </div>
            <div class="flex-grow-1">
              <h5 class="card-title mb-1">Lessons</h5>
              <p class="card-text small text-muted mb-2">{{ counts.materials }} topics available</p>
              <Link :href="route('student.lms.materials.index')" class="btn btn-sm btn-outline-primary px-3 rounded-pill fw-semibold">Browse</Link>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100 py-2">
          <div class="card-body d-flex align-items-center">
            <div class="avatar bg-label-info rounded p-3 me-3 text-info position-relative">
              <i class="fas fa-tasks fa-2x"></i>
              <span v-if="counts.pending_assignments > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info border border-white" style="font-size: 0.65rem;">{{ counts.pending_assignments }}</span>
            </div>
            <div class="flex-grow-1">
              <h5 class="card-title mb-1">Homework</h5>
              <p class="card-text small text-muted mb-2 text-nowrap">{{ counts.pending_assignments }} pending tasks</p>
              <Link :href="route('student.lms.assignments.index')" class="btn btn-sm btn-outline-info px-3 rounded-pill fw-semibold">View Tasks</Link>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4 mb-4">
        <div class="card border-0 shadow-sm h-100 py-2">
          <div class="card-body d-flex align-items-center">
            <div class="avatar bg-label-success rounded p-3 me-3 text-success position-relative">
              <i class="fas fa-video fa-2x"></i>
              <span v-if="counts.classes > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success border border-white" style="font-size: 0.65rem;">{{ counts.classes }}</span>
            </div>
            <div class="flex-grow-1">
              <h5 class="card-title mb-1">Virtual Classes</h5>
              <p class="card-text small text-muted mb-2">{{ counts.classes }} upcoming</p>
              <Link :href="route('student.lms.classes.index')" class="btn btn-sm btn-outline-success px-3 rounded-pill fw-semibold">Connect</Link>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-8">
        <!-- Upcoming Classes -->
        <div class="card border-0 shadow-sm mb-4 overflow-hidden">
          <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-bold">Online Classes</h5>
            <div class="d-flex align-items-center">
              <span v-if="upcomingClasses.length" class="badge bg-label-primary rounded-pill me-2">{{ upcomingClasses.length }} Scheduled</span>
              <Link :href="route('student.lms.classes.index')" class="btn btn-xs btn-light rounded-pill px-2"><i class="fas fa-external-link-alt"></i></Link>
            </div>
          </div>
          <div class="card-body p-0">
            <div v-if="upcomingClasses.length > 0" class="table-responsive scroll-area">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="ps-4">Subject</th>
                    <th>Topic</th>
                    <th>Teacher</th>
                    <th>Time</th>
                    <th class="pe-4 text-end">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="cls in upcomingClasses" :key="cls.id">
                    <td class="ps-4">
                      <div class="d-flex align-items-center">
                        <div class="badge bg-label-info p-2 rounded me-2">
                          <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <span class="fw-semibold text-dark">{{ cls.subject?.name }}</span>
                      </div>
                    </td>
                    <td><span class="text-secondary small">{{ cls.title }}</span></td>
                    <td class="text-muted small">{{ cls.teacher?.name }}</td>
                    <td class="small text-nowrap">
                      <div class="d-flex flex-column">
                        <span>{{ formatTime(cls.scheduled_at) }}</span>
                      </div>
                    </td>
                    <td class="pe-4 text-end">
                      <div v-if="isJoinable(cls)">
                        <a :href="cls.meeting_link" target="_blank" class="btn btn-sm btn-primary px-3 rounded-pill shadow-sm">
                          <i class="fas fa-play me-1 small"></i> Join Now
                        </a>
                      </div>
                      <div v-else-if="isUpcoming(cls)">
                        <button class="btn btn-sm btn-light px-3 rounded-pill" disabled>
                          <i class="fas fa-clock me-1 small"></i> Waiting...
                        </button>
                      </div>
                      <div v-else>
                        <span class="badge bg-label-secondary rounded-pill">Session Ended</span>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="text-center py-5">
              <div class="bg-light rounded-circle p-3 d-inline-block mb-3">
                <i class="fas fa-video-slash fa-2x text-muted opacity-50"></i>
              </div>
              <p class="text-muted mb-0">No upcoming classes scheduled.</p>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <!-- Pending Assignments -->
        <div class="card border-0 shadow-sm h-100 overflow-hidden">
          <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Assignments</h5>
            <Link :href="route('student.lms.assignments.index')" class="btn btn-xs btn-light rounded-pill px-3">View All</Link>
          </div>
          <div class="card-body p-0">
            <div v-if="pendingAssignments.length > 0" class="scroll-area">
              <div v-for="assignment in pendingAssignments" :key="assignment.id" class="p-3 border-bottom assignment-item">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div class="d-flex align-items-center">
                    <div class="bg-label-danger rounded p-2 me-2 smaller">
                      <i class="fas fa-file-alt"></i>
                    </div>
                    <h6 class="mb-0 fw-bold text-truncate" style="max-width: 150px;">{{ assignment.title }}</h6>
                  </div>
                  <span class="badge bg-label-danger smaller rounded-pill">Pending</span>
                </div>
                <div class="ps-4 ms-2">
                  <p class="small text-muted mb-3">{{ assignment.subject?.name }}</p>
                  <div class="d-flex justify-content-between align-items-center">
                    <span class="smaller text-danger fw-medium">
                      <i class="fas fa-clock me-1"></i> Due: {{ formatDate(assignment.due_date) }}
                    </span>
                    <Link :href="route('student.lms.assignments.index')" class="btn btn-xs btn-outline-primary px-3 rounded-pill">Submit</Link>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-5">
              <div class="bg-light rounded-circle p-3 d-inline-block mb-3">
                <i class="fas fa-check-circle fa-2x text-success opacity-50"></i>
              </div>
              <p class="text-muted mb-0 fw-medium">All caught up!</p>
              <small class="text-muted">No pending assignments.</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';

const props = defineProps({
  upcomingClasses: Array,
  pendingAssignments: Array,
  studentClass: String,
  counts: Object,
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

const formatTime = (date) => {
  return new Date(date).toLocaleString('en-GB', { 
    day: 'numeric', 
    month: 'short', 
    hour: '2-digit',
    minute: '2-digit'
  });
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-GB', { 
    day: 'numeric', 
    month: 'short'
  });
};
</script>

<style scoped>
.avatar { width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; }
.bg-label-primary { background-color: rgba(105, 108, 255, 0.1) !important; color: #696cff !important; }
.bg-label-info { background-color: rgba(3, 195, 236, 0.1) !important; color: #03c3ec !important; }
.bg-label-success { background-color: rgba(113, 221, 55, 0.1) !important; color: #71dd37 !important; }
.bg-label-danger { background-color: rgba(255, 62, 29, 0.1) !important; color: #ff3e1d !important; }
.bg-label-warning { background-color: rgba(255, 171, 0, 0.1) !important; color: #ffab00 !important; }

.assignment-item { transition: all 0.2s; border-left: 2px solid transparent; }
.assignment-item:hover { background-color: rgba(105, 108, 255, 0.02); border-left-color: #696cff; }
.smaller { font-size: 0.75rem; }
.btn-xs { padding: 0.25rem 0.5rem; font-size: 0.75rem; }

.scroll-area {
  max-height: 400px;
  overflow-y: auto;
}

.scroll-area::-webkit-scrollbar {
  width: 4px;
}

.scroll-area::-webkit-scrollbar-thumb {
  background: #e6e6e6;
  border-radius: 10px;
}

.table thead th {
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  font-weight: 700;
  color: #566a7f;
}

.card-title {
  color: #566a7f;
  font-weight: 600;
}
</style>
