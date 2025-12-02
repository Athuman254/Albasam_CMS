<template>
    <DefaultLayout>
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold">Mark Attendance</h4>
                <span class="badge bg-label-primary">{{ currentDate }}</span>
            </div>

            <!-- Flash Messages -->
            <div v-if="$page.props.flash.success" class="alert alert-success alert-dismissible" role="alert">
                {{ $page.props.flash.success }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <div v-if="$page.props.flash.error" class="alert alert-danger alert-dismissible" role="alert">
                {{ $page.props.flash.error }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <!-- GPS Status Card -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">GPS Location Status</h5>
                            <div v-if="gpsLoading" class="text-center py-3">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Getting location...</span>
                                </div>
                                <p class="mt-2 text-muted">Detecting your location...</p>
                            </div>
                            <div v-else-if="gpsError" class="alert alert-danger">
                                <i class="bx bx-error-circle me-2"></i>{{ gpsError }}
                            </div>
                            <div v-else-if="currentLocation">
                                <div class="d-flex align-items-center mb-3">
                                    <i :class="['bx', withinGeofence ? 'bx-check-circle text-success' : 'bx-x-circle text-danger', 'fs-3 me-2']"></i>
                                    <div>
                                        <div class="fw-semibold">{{ withinGeofence ? 'Within School Compound' : 'Outside School Compound' }}</div>
                                        <small class="text-muted">Distance: {{ distance }}m (Max: {{ geofenceRadius }}m)</small>
                                    </div>
                                </div>
                                <button @click="refreshLocation" class="btn btn-sm btn-outline-primary">
                                    <i class="bx bx-refresh me-1"></i>Refresh Location
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Status Card -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Today's Status</h5>
                            <div v-if="todayStatus.has_clocked_in">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Clock In:</span>
                                        <span class="fw-semibold">{{ todayStatus.clock_in_time }}</span>
                                    </div>
                                    <div v-if="todayStatus.has_clocked_out" class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Clock Out:</span>
                                        <span class="fw-semibold">{{ todayStatus.clock_out_time }}</span>
                                    </div>
                                    <div v-if="todayStatus.total_hours" class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-muted">Total Hours:</span>
                                        <span class="fw-semibold">{{ todayStatus.total_hours }} hrs</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Status:</span>
                                        <span :class="['badge', getStatusClass(todayStatus.status)]">
                                            {{ todayStatus.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-3 text-muted">
                                <i class="bx bx-time-five fs-1"></i>
                                <p class="mb-0">Not clocked in yet</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clock In/Out Buttons -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <div v-if="!todayStatus.has_clocked_in">
                                <button 
                                    @click="clockIn" 
                                    :disabled="!withinGeofence || processing"
                                    class="btn btn-lg btn-success"
                                >
                                    <i class="bx bx-log-in me-2"></i>
                                    {{ processing ? 'Processing...' : 'Clock In' }}
                                </button>
                                <p v-if="!withinGeofence" class="text-danger mt-2 mb-0">
                                    <small>You must be within the school compound to clock in</small>
                                </p>
                            </div>
                            <div v-else-if="!todayStatus.has_clocked_out">
                                <button 
                                    @click="clockOut" 
                                    :disabled="processing"
                                    class="btn btn-lg btn-danger"
                                >
                                    <i class="bx bx-log-out me-2"></i>
                                    {{ processing ? 'Processing...' : 'Clock Out' }}
                                </button>
                            </div>
                            <div v-else class="text-success">
                                <i class="bx bx-check-circle fs-1"></i>
                                <p class="mb-0 fw-semibold">Attendance marked for today</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent History -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Attendance (Last 7 Days)</h5>
                </div>
                <div class="card-body">
                    <div v-if="historyLoading" class="text-center py-3">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div v-else-if="history.length === 0" class="text-center py-3 text-muted">
                        No attendance records found
                    </div>
                    <div v-else class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Clock In</th>
                                    <th>Clock Out</th>
                                    <th>Total Hours</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="record in history" :key="record.date">
                                    <td>{{ record.formatted_date }}</td>
                                    <td>{{ record.clock_in }}</td>
                                    <td>{{ record.clock_out || '-' }}</td>
                                    <td>{{ record.total_hours || '-' }}</td>
                                    <td>
                                        <span :class="['badge', getStatusClass(record.status)]">
                                            {{ record.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </DefaultLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';

const props = defineProps({
    todayStatus: Object,
    schoolCoordinates: Object,
});

const currentLocation = ref(null);
const gpsLoading = ref(false);
const gpsError = ref(null);
const processing = ref(false);
const history = ref([]);
const historyLoading = ref(false);

const currentDate = computed(() => {
    return new Date().toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
});

const withinGeofence = ref(false);
const distance = ref(0);
const geofenceRadius = ref(props.schoolCoordinates?.geofence_radius || 100);

const getLocation = () => {
    gpsLoading.value = true;
    gpsError.value = null;

    if (!navigator.geolocation) {
        gpsError.value = 'Geolocation is not supported by your browser';
        gpsLoading.value = false;
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (position) => {
            currentLocation.value = {
                latitude: position.coords.latitude,
                longitude: position.coords.longitude,
            };
            checkGeofence();
            gpsLoading.value = false;
        },
        (error) => {
            gpsError.value = 'Unable to get your location. Please enable GPS and try again.';
            gpsLoading.value = false;
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
};

const checkGeofence = () => {
    if (!currentLocation.value || !props.schoolCoordinates) return;

    const dist = calculateDistance(
        currentLocation.value.latitude,
        currentLocation.value.longitude,
        props.schoolCoordinates.latitude,
        props.schoolCoordinates.longitude
    );

    distance.value = Math.round(dist);
    withinGeofence.value = dist <= geofenceRadius.value;
};

const calculateDistance = (lat1, lon1, lat2, lon2) => {
    const R = 6371000; // Earth's radius in meters
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
};

const refreshLocation = () => {
    getLocation();
};

const clockIn = () => {
    if (!currentLocation.value) return;
    
    processing.value = true;
    // Use current URL path to determine the correct endpoint
    const baseUrl = window.location.pathname.startsWith('/admin') ? '/admin/attendance' : '/employee/attendance';
    router.post(`${baseUrl}/clock-in`, {
        latitude: currentLocation.value.latitude,
        longitude: currentLocation.value.longitude,
    }, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
            loadHistory();
        }
    });
};

const clockOut = () => {
    if (!currentLocation.value) {
        getLocation();
        return;
    }
    
    processing.value = true;
    // Use current URL path to determine the correct endpoint
    const baseUrl = window.location.pathname.startsWith('/admin') ? '/admin/attendance' : '/employee/attendance';
    router.post(`${baseUrl}/clock-out`, {
        latitude: currentLocation.value.latitude,
        longitude: currentLocation.value.longitude,
    }, {
        preserveScroll: true,
        onFinish: () => {
            processing.value = false;
            loadHistory();
        }
    });
};

const loadHistory = () => {
    historyLoading.value = true;
    // Use current URL path to determine the correct endpoint
    const baseUrl = window.location.pathname.startsWith('/admin') ? '/admin/attendance' : '/employee/attendance';
    axios.get(`${baseUrl}/history`, { params: { days: 7 } })
        .then(response => {
            history.value = response.data;
        })
        .finally(() => {
            historyLoading.value = false;
        });
};

const getStatusClass = (status) => {
    const classes = {
        'present': 'bg-label-success',
        'late': 'bg-label-warning',
        'half_day': 'bg-label-info',
        'absent': 'bg-label-danger',
    };
    return classes[status] || 'bg-label-secondary';
};

onMounted(() => {
    getLocation();
    loadHistory();
});
</script>
