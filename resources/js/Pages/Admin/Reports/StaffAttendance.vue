<template>
    <DefaultLayout>
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold">Staff Attendance Report</h4>
                <button @click="exportReport" class="btn btn-primary">
                    <i class="bx bx-download me-1"></i>Export
                </button>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="bx bx-user fs-4"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Total Records</small>
                                    <h4 class="mb-0">{{ summary.total_records }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="bx bx-check-circle fs-4"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Present</small>
                                    <h4 class="mb-0">{{ summary.present }} <small class="text-success">({{ summary.present_percentage }}%)</small></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-warning">
                                        <i class="bx bx-time-five fs-4"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Late</small>
                                    <h4 class="mb-0">{{ summary.late }} <small class="text-warning">({{ summary.late_percentage }}%)</small></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-info">
                                        <i class="bx bx-calendar fs-4"></i>
                                    </span>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Half Day</small>
                                    <h4 class="mb-0">{{ summary.half_day }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input v-model="filters.start_date" type="date" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input v-model="filters.end_date" type="date" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Employee</label>
                            <select v-model="filters.employee_id" class="form-select">
                                <option value="">All Employees</option>
                                <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                    {{ emp.name }} ({{ emp.staff_number }})
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select v-model="filters.status" class="form-select">
                                <option value="">All Status</option>
                                <option value="present">Present</option>
                                <option value="late">Late</option>
                                <option value="half_day">Half Day</option>
                                <option value="absent">Absent</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button @click="applyFilters" class="btn btn-primary me-2">
                                <i class="bx bx-filter me-1"></i>Apply Filters
                            </button>
                            <button @click="resetFilters" class="btn btn-outline-secondary">
                                <i class="bx bx-reset me-1"></i>Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Attendance Table -->
            <div class="card">
                <div class="card-body">
                    <div v-if="loading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                    </div>
                    <div v-else-if="attendances.length === 0" class="text-center py-5 text-muted">
                        <i class="bx bx-calendar-x fs-1"></i>
                        <p class="mb-0">No attendance records found</p>
                    </div>
                    <div v-else>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Staff Number</th>
                                        <th>Employee Name</th>
                                        <th>Date</th>
                                        <th>Clock In</th>
                                        <th>Clock Out</th>
                                        <th>Total Hours</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="record in attendances" :key="record.id">
                                        <td>{{ record.staff_number }}</td>
                                        <td>{{ record.employee_name }}</td>
                                        <td>{{ record.formatted_date }}</td>
                                        <td>{{ record.clock_in }}</td>
                                        <td>{{ record.clock_out || '-' }}</td>
                                        <td>{{ record.total_hours || '-' }}</td>
                                        <td>
                                            <span :class="['badge', getStatusClass(record.status)]">
                                                {{ record.status_label }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="pagination.last_page > 1" class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Showing {{ ((pagination.current_page - 1) * pagination.per_page) + 1 }} 
                                to {{ Math.min(pagination.current_page * pagination.per_page, pagination.total) }} 
                                of {{ pagination.total }} records
                            </div>
                            <nav>
                                <ul class="pagination mb-0">
                                    <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                                        <button @click="changePage(pagination.current_page - 1)" class="page-link">Previous</button>
                                    </li>
                                    <li v-for="page in visiblePages" :key="page" class="page-item" :class="{ active: page === pagination.current_page }">
                                        <button @click="changePage(page)" class="page-link">{{ page }}</button>
                                    </li>
                                    <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                                        <button @click="changePage(pagination.current_page + 1)" class="page-link">Next</button>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DefaultLayout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import axios from 'axios';

const props = defineProps({
    employees: Array,
});

const attendances = ref([]);
const loading = ref(false);
const summary = ref({
    total_records: 0,
    present: 0,
    late: 0,
    half_day: 0,
    present_percentage: 0,
    late_percentage: 0,
});

const pagination = ref({
    total: 0,
    per_page: 15,
    current_page: 1,
    last_page: 1,
});

const filters = ref({
    start_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
    end_date: new Date().toISOString().split('T')[0],
    employee_id: '',
    status: '',
});

const visiblePages = computed(() => {
    const pages = [];
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;
    
    let start = Math.max(1, current - 2);
    let end = Math.min(last, current + 2);
    
    for (let i = start; i <= end; i++) {
        pages.push(i);
    }
    
    return pages;
});

const loadData = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get(route('admin.reports.staff-attendance.data'), {
            params: {
                ...filters.value,
                page,
                per_page: pagination.value.per_page,
            }
        });
        
        attendances.value = response.data.data;
        pagination.value = response.data.pagination;
        
        loadSummary();
    } catch (error) {
        console.error('Error loading attendance data:', error);
    } finally {
        loading.value = false;
    }
};

const loadSummary = async () => {
    try {
        const response = await axios.get(route('admin.reports.staff-attendance.data').replace('/data', '/summary'), {
            params: filters.value
        });
        summary.value = response.data;
    } catch (error) {
        console.error('Error loading summary:', error);
    }
};

const applyFilters = () => {
    loadData(1);
};

const resetFilters = () => {
    filters.value = {
        start_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
        end_date: new Date().toISOString().split('T')[0],
        employee_id: '',
        status: '',
    };
    loadData(1);
};

const changePage = (page) => {
    if (page >= 1 && page <= pagination.value.last_page) {
        loadData(page);
    }
};

const exportReport = async () => {
    try {
        const response = await axios.post(route('admin.reports.staff-attendance.export'), filters.value, {
            responseType: 'blob'
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `staff_attendance_${new Date().toISOString().split('T')[0]}.pdf`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Error exporting report:', error);
    }
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
    loadData();
});
</script>
