<template>
    <DefaultLayout>
        <Head title="Collection Report" />
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-12">
                    <!-- Navigation Tabs -->
                    <div class="nav-align-top mb-4">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <Link :href="route('admin.fees.reports.fee-report')" class="nav-link">
                                    <i class="tf-icons bx bx-wallet me-1"></i> Fee Balances
                                </Link>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link active" role="tab">
                                    <i class="tf-icons bx bx-list-check me-1"></i> Collections Summary
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header d-flex justify-content-between align-items-center bg-transparent border-bottom py-3">
                            <h4 class="card-title mb-0 fw-bold text-primary">
                                <i class="bx bx-chart me-2"></i>Payment Collection Report
                            </h4>
                        </div>
                        <div class="card-body pt-4">
                            <!-- Filters -->
                            <form @submit.prevent="generateReport" class="filter-section mb-5 p-4 rounded-3 bg-light">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Date From</label>
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                            <input type="date" v-model="form.date_from" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Date To</label>
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="bx bx-calendar"></i></span>
                                            <input type="date" v-model="form.date_to" class="form-control" />
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-semibold">Payment Method</label>
                                        <select v-model="form.payment_method" class="form-select border-1">
                                            <option value="">All Methods</option>
                                            <option v-for="method in paymentMethods" :key="method" :value="method">
                                                {{ method.toUpperCase() }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary w-100 shadow-sm" :disabled="loading">
                                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                            <i v-else class="bx bx-search-alt me-1"></i> Filter
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Results Section -->
                            <div v-if="reportData.data && reportData.data.length" class="animate__animated animate__fadeIn">
                                <!-- Summary Cards -->
                                <div class="row mb-4">
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card bg-label-success border-0 shadow-none">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-2">
                                                    <div class="avatar avatar-md me-2">
                                                        <span class="avatar-initial rounded bg-success"><i class="bx bx-money"></i></span>
                                                    </div>
                                                    <h6 class="mb-0 text-success fw-bold">Total Collected</h6>
                                                </div>
                                                <h3 class="mb-0 fw-bold">{{ formatCurrency(summary.total_amount) }}</h3>
                                                <small class="text-muted">{{ summary.total_transactions }} transactions</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex justify-content-end mb-3 gap-2">
                                    
                                    <button @click="exportPDF" class="btn btn-outline-danger btn-sm" :disabled="loading">
                                        <i class="bx bxs-file-pdf me-1"></i> Export PDF
                                    </button>
                                    <button @click="printReport" class="btn btn-outline-info btn-sm">
                                        <i class="bx bx-printer me-1"></i> Print
                                    </button>
                                </div>

                                <div class="table-responsive border rounded-3 overflow-hidden shadow-sm">
                                    <table class="table table-hover mb-0 align-middle">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="py-3 px-4">Date</th>
                                                <th class="py-3">Student Name</th>
                                                <th class="py-3">ADM No</th>
                                                <th class="py-3">Method</th>
                                                <th class="py-3">Reference</th>
                                                <th class="py-3 text-end px-4">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="payment in reportData.data" :key="payment.id" class="border-bottom-0">
                                                <td class="px-4"><small class="text-muted">{{ formatDate(payment.payment_date) }}</small></td>
                                                <td>
                                                    <div class="fw-semibold text-dark">{{ getStudentName(payment.student) }}</div>
                                                    <small class="text-muted">{{ getClassName(payment.fee) }}</small>
                                                </td>
                                                <td><span class="badge bg-label-secondary">{{ payment.student?.admission_number || 'N/A' }}</span></td>
                                                <td>
                                                    <span class="badge" :class="getMethodClass(payment.payment_method)">
                                                        {{ payment.payment_method?.toUpperCase() }}
                                                    </span>
                                                </td>
                                                <td><small class="text-truncate d-inline-block" style="max-width: 120px;" :title="payment.reference_number">{{ payment.reference_number }}</small></td>
                                                <td class="text-end px-4 fw-bold text-success">{{ formatCurrency(payment.amount) }}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="bg-light fw-bold border-top-2">
                                            <tr>
                                                <td colspan="5" class="text-end py-3 px-4">PAGE TOTAL:</td>
                                                <td class="text-end py-3 px-4 text-success">{{ formatCurrency(pageTotal) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="d-flex justify-content-between align-items-center mt-4 px-2">
                                    <div class="text-muted small">
                                        Showing {{ reportData.from }} to {{ reportData.to }} of {{ reportData.total }} results
                                    </div>
                                    <nav v-if="reportData.links && reportData.links.length > 3">
                                        <ul class="pagination pagination-sm mb-0">
                                            <li v-for="(link, k) in reportData.links" :key="k" class="page-item" :class="{ 'active': link.active, 'disabled': !link.url }">
                                                <button class="page-link" @click="changePage(link.url)" v-html="link.label"></button>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-else class="text-center py-5">
                                <div class="mb-3">
                                    <i class="bx bx-search-alt text-light display-1"></i>
                                </div>
                                <h5 class="text-muted">No collections found</h5>
                                <p class="text-muted small">Try adjusting your filters to see more results</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </DefaultLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    collections: Object,
    summary: Object,
    paymentMethods: Array,
    filters: Object
});

const form = ref({
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
    payment_method: props.filters?.payment_method || ''
});

const reportData = ref(props.collections || { data: [] });
const summary = ref(props.summary || { total_amount: 0, total_transactions: 0 });
const loading = ref(false);

const pageTotal = computed(() => {
    return reportData.value?.data?.reduce((acc, curr) => acc + parseFloat(curr.amount), 0) || 0;
});

const generateReport = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('admin.fees.reports.collection'), {
            params: form.value
        });
        reportData.value = response.data.collections;
        summary.value = response.data.summary;
    } catch (error) {
        console.error('Error fetching collection report:', error);
    } finally {
        loading.value = false;
    }
};

const changePage = async (url) => {
    if (!url) return;
    loading.value = true;
    try {
        const response = await axios.get(url, {
            params: form.value
        });
        reportData.value = response.data.collections;
        summary.value = response.data.summary;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (error) {
        console.error('Error changing page:', error);
    } finally {
        loading.value = false;
    }
};

const getStudentName = (student) => {
    if (!student) return 'N/A';
    return `${student.first_name} ${student.middle_name || ''} ${student.last_name}`;
};

const getClassName = (fee) => {
    if (!fee || !fee.rank) return 'N/A';
    return fee.rank.full_name || fee.rank.name;
};

const getMethodClass = (method) => {
    method = method?.toLowerCase();
    if (method === 'mpesa') return 'bg-label-success';
    if (method === 'cash') return 'bg-label-primary';
    if (method === 'bank') return 'bg-label-info';
    return 'bg-label-secondary';
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-KE', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
};

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES'
    }).format(amount);
};

const exportPDF = () => {
    const params = new URLSearchParams(form.value).toString();
    window.location.href = `${route('admin.fees.reports.export-collection-pdf')}?${params}`;
};

const exportCSV = () => {
    const params = new URLSearchParams(form.value).toString();
    window.location.href = `${route('admin.fees.reports.export-collection')}?${params}`;
};

const printReport = () => {
    window.print();
};
</script>

<style scoped>
.bg-label-success { background-color: #e8fadf !important; color: #71dd37 !important; }
.bg-label-primary { background-color: #e7e7ff !important; color: #696cff !important; }
.bg-label-info { background-color: #d7f5fc !important; color: #03c3ec !important; }
.bg-label-secondary { background-color: #ebeef1 !important; color: #8592a3 !important; }

.filter-section {
    border: 1px solid #f0f2f4;
}

@media print {
    .nav-tabs, .filter-section, .pagination, .btn, .layout-navbar, .layout-menu {
        display: none !important;
    }
    .card {
        box-shadow: none !important;
        border: none !important;
    }
    .container-xxl {
        padding: 0 !important;
        margin: 0 !important;
    }
    body {
        background: white !important;
    }
    .table-responsive {
        border: none !important;
        overflow: visible !important;
    }
    .table {
        border: 1px solid #dee2e6 !important;
    }
}
</style>
