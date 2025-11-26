<template>
   <Head title="Dashboard"/>
   
   <DefaultLayout>
      <div class="row">
         <h3>Dashboard</h3>
         
         <div class="col-12">
            <!-- Loading State -->
            <div v-if="loading" class="text-center py-5">
               <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
               </div>
               <p class="mt-2 text-muted">Loading dashboard data...</p>
            </div>

            <!-- Dashboard Content -->
            <div v-else>
               <!-- Quick Stats -->
               <div class="row mb-4">
                  <div class="col-md-3">
                     <div class="card border">
                        <div class="card-body text-center p-3">
                           <h6 class="card-title mb-1 text-muted">My Classes</h6>
                           <h4 class="mb-0 text-dark">{{ statistics.classes || 0 }}</h4>
                           <small class="text-muted">Assigned Classes</small>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="card border">
                        <div class="card-body text-center p-3">
                           <h6 class="card-title mb-1 text-muted">Draft Marks</h6>
                           <h4 class="mb-0 text-dark">{{ statistics.pendingMarks || 0 }}</h4>
                           <small class="text-muted">Not Submitted</small>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="card border">
                        <div class="card-body text-center p-3">
                           <h6 class="card-title mb-1 text-muted">Submitted</h6>
                           <h4 class="mb-0 text-dark">{{ statistics.submittedMarks || 0 }}</h4>
                           <small class="text-muted">Pending Approval</small>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-3">
                     <div class="card border">
                        <div class="card-body text-center p-3">
                           <h6 class="card-title mb-1 text-muted">Approved</h6>
                           <h4 class="mb-0 text-dark">{{ statistics.approvedMarks || 0 }}</h4>
                           <small class="text-muted">Completed</small>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- Main Content Area -->
               <div class="row">
                  <div class="col-md-6">
                     <div class="card border">
                        <div class="card-header bg-white border-bottom">
                           <h5 class="card-title mb-0 text-dark">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                           <div class="d-grid gap-2">
                              <Link :href="route('employee.exams.enter-marks')" class="btn btn-primary">
                                 <i class="bx bx-edit me-2"></i> Enter Exam Marks
                              </Link>
                              <Link :href="route('employee.exams.submitted-marks')" class="btn btn-outline-primary">
                                 <i class="bx bx-list-check me-2"></i> View Submitted Marks
                              </Link>
                              <Link :href="route('employee.attendances.index')" class="btn btn-outline-secondary">
                                 <i class="bx bx-calendar-check me-2"></i> Mark Attendance
                              </Link>
                           </div>
                           
                           <!-- Quick Stats Summary -->
                           <div class="mt-3 p-3 bg-light rounded">
                              <div class="row text-center">
                                 <div class="col-4">
                                    <div class="fw-bold text-dark">{{ statistics.classes || 0 }}</div>
                                    <small class="text-muted">Classes</small>
                                 </div>
                                 <div class="col-4">
                                    <div class="fw-bold text-dark">{{ totalMarks }}</div>
                                    <small class="text-muted">Total Marks</small>
                                 </div>
                                 <div class="col-4">
                                    <div class="fw-bold text-dark">{{ completionRate }}%</div>
                                    <small class="text-muted">Completion</small>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>

                     <!-- Performance Summary -->
                     <div class="card border mt-4">
                        <div class="card-header bg-white border-bottom">
                           <h5 class="card-title mb-0 text-dark">Performance Summary</h5>
                        </div>
                        <div class="card-body">
                           <div class="row text-center">
                              <div class="col-6 mb-3">
                                 <div class="p-3 border rounded">
                                    <div class="fw-bold text-primary fs-4">{{ statistics.pendingMarks || 0 }}</div>
                                    <small class="text-muted">Draft Entries</small>
                                 </div>
                              </div>
                              <div class="col-6 mb-3">
                                 <div class="p-3 border rounded">
                                    <div class="fw-bold text-warning fs-4">{{ statistics.submittedMarks || 0 }}</div>
                                    <small class="text-muted">Pending Review</small>
                                 </div>
                              </div>
                              <div class="col-6">
                                 <div class="p-3 border rounded">
                                    <div class="fw-bold text-success fs-4">{{ statistics.approvedMarks || 0 }}</div>
                                    <small class="text-muted">Approved</small>
                                 </div>
                              </div>
                              <div class="col-6">
                                 <div class="p-3 border rounded">
                                    <div class="fw-bold text-info fs-4">{{ completionRate }}%</div>
                                    <small class="text-muted">Completion Rate</small>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  
                  <div class="col-md-6">
                     <div class="card border">
                        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                           <h5 class="card-title mb-0 text-dark">Recent Activity</h5>
                           <span class="badge bg-primary">{{ recentActivity.length }}</span>
                        </div>
                        <div class="card-body">
                           <div v-if="recentActivity.length > 0" class="activity-timeline">
                              <div v-for="activity in recentActivity" :key="activity.id" class="activity-item d-flex mb-3">
                                 <div class="flex-shrink-0">
                                    <span class="avatar avatar-sm rounded-circle bg-light border">
                                       <i :class="`bx ${getActivityIcon(activity.type)} text-dark`"></i>
                                    </span>
                                 </div>
                                 <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1 text-dark">{{ activity.title }}</h6>
                                    <p class="mb-0 text-muted small">{{ activity.description }}</p>
                                    <small class="text-muted">{{ activity.time }}</small>
                                 </div>
                              </div>
                           </div>
                           <div v-else class="text-center text-muted py-4">
                              <i class="bx bx-time-five display-4 text-muted"></i>
                              <p class="mt-2 text-muted">No recent activity</p>
                              <small class="text-muted">Your recent exam and attendance activities will appear here</small>
                           </div>
                        </div>
                     </div>

                     <!-- Quick Tips -->
                     <div class="card border mt-4">
                        <div class="card-header bg-white border-bottom">
                           <h5 class="card-title mb-0 text-dark">Quick Tips</h5>
                        </div>
                        <div class="card-body">
                           <div class="d-flex align-items-start mb-3">
                              <i class="bx bx-info-circle text-primary me-2 mt-1"></i>
                              <div>
                                 <small class="text-dark fw-semibold">Enter marks promptly</small>
                                 <p class="mb-0 text-muted small">Submit exam marks within 48 hours of the exam for timely processing.</p>
                              </div>
                           </div>
                           <div class="d-flex align-items-start mb-3">
                              <i class="bx bx-check-circle text-success me-2 mt-1"></i>
                              <div>
                                 <small class="text-dark fw-semibold">Review before submission</small>
                                 <p class="mb-0 text-muted small">Double-check all marks before final submission to avoid corrections.</p>
                              </div>
                           </div>
                           <div class="d-flex align-items-start">
                              <i class="bx bx-calendar text-info me-2 mt-1"></i>
                              <div>
                                 <small class="text-dark fw-semibold">Regular attendance</small>
                                 <p class="mb-0 text-muted small">Mark attendance daily to maintain accurate student records.</p>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- Empty State Guidance -->
               <div v-if="showEmptyState" class="row mt-4">
                  <div class="col-12">
                     <div class="card border-warning">
                        <div class="card-body text-center py-5">
                           <i class="bx bx-info-circle display-4 text-warning mb-3"></i>
                           <h5 class="text-warning">Getting Started</h5>
                           <p class="text-muted">It looks like you're new here. Here's what you can do:</p>
                           <div class="row justify-content-center">
                              <div class="col-md-8">
                                 <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                                    <Link :href="route('employee.exams.enter-marks')" class="btn btn-warning me-md-2">
                                       <i class="bx bx-edit me-1"></i> Start Entering Marks
                                    </Link>
                                    <Link :href="route('employee.attendances.index')" class="btn btn-outline-warning">
                                       <i class="bx bx-calendar me-1"></i> Mark Attendance
                                    </Link>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </DefaultLayout>
</template>

<script setup>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const loading = ref(true);
const statistics = ref({});
const recentActivity = ref([]);

// Computed properties
const totalMarks = computed(() => {
   const pending = statistics.value.pendingMarks || 0;
   const submitted = statistics.value.submittedMarks || 0;
   const approved = statistics.value.approvedMarks || 0;
   return pending + submitted + approved;
});

const completionRate = computed(() => {
   const submitted = statistics.value.submittedMarks || 0;
   const approved = statistics.value.approvedMarks || 0;
   const total = totalMarks.value;
   
   if (total === 0) return 0;
   return Math.round(((submitted + approved) / total) * 100);
});

const showEmptyState = computed(() => {
   return totalMarks.value === 0 && 
          (statistics.value.classes || 0) === 0;
});

const loadDashboardData = async () => {
   try {
      loading.value = true;
      
      // Load all data in parallel for better performance
      const [statsResponse, activityResponse] = await Promise.all([
         axios.get('/employee/dashboard-statistics'),
         axios.get('/employee/recent-activity')
      ]);

      statistics.value = statsResponse.data;
      recentActivity.value = activityResponse.data;

   } catch (error) {
      console.error('Error loading dashboard data:', error);
      
      // Set default values if API fails
      statistics.value = {
         classes: 0,
         pendingMarks: 0,
         submittedMarks: 0,
         approvedMarks: 0
      };
      recentActivity.value = [];
      
      // Show error message to user
      if (error.response?.status === 401) {
         console.error('Authentication error - please log in again');
      } else if (error.response?.status === 500) {
         console.error('Server error - please try again later');
      }
   } finally {
      loading.value = false;
   }
};

const getActivityIcon = (type) => {
   const icons = {
      'mark_entry': 'bx-edit',
      'submission': 'bx-send',
      'approval': 'bx-check',
      'attendance': 'bx-calendar',
      'exam_created': 'bx-book',
      'results_published': 'bx-bar-chart'
   };
   return icons[type] || 'bx-info-circle';
};

// Refresh data every 2 minutes if needed
const startAutoRefresh = () => {
   setInterval(() => {
      if (document.visibilityState === 'visible') {
         loadDashboardData();
      }
   }, 120000);
};

onMounted(() => {
   loadDashboardData();
});
</script>

<style scoped>
.avatar {
   width: 40px;
   height: 40px;
   display: flex;
   align-items: center;
   justify-content: center;
}

.activity-timeline {
   max-height: 300px;
   overflow-y: auto;
}

.card {
   box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
   border: 1px solid #dee2e6;
   transition: box-shadow 0.15s ease-in-out;
   background-color: #ffffff;
}

.card:hover {
   box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}

.spinner-border {
   width: 3rem;
   height: 3rem;
}

.table-hover tbody tr:hover {
   background-color: rgba(0, 0, 0, 0.025);
}

.badge {
   font-size: 0.75em;
   font-weight: 500;
}

.bg-light {
   background-color: #f8f9fa !important;
}

.border {
   border-color: #dee2e6 !important;
}

.text-dark {
   color: #212529 !important;
}

.text-muted {
   color: #6c757d !important;
}

.card-header.bg-white {
   background-color: #ffffff !important;
}

.card-header.border-bottom {
   border-bottom: 1px solid #dee2e6 !important;
}
</style>