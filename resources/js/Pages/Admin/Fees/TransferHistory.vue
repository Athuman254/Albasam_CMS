<template>
  <DefaultLayout>
    <Head title="Transfer History" />
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-exchange-alt me-2"></i>Fund Transfer History
                        </h4>
                        <div class="btn-group">
                            <Link :href="route('admin.fees.transfers.create')" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> New Transfer
                            </Link>
                            <Link :href="route('admin.fees.payments.index')" class="btn btn-secondary">
                                <i class="fas fa-history me-1"></i> Payment History
                            </Link>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Search and Filters -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Search Transfers</label>
                                <input type="text" class="form-control" v-model="filters.search" 
                                       placeholder="Search by student name or admission number..."
                                       @input="loadTransfers">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date From</label>
                                <input type="date" class="form-control" v-model="filters.date_from" @change="loadTransfers">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date To</label>
                                <input type="date" class="form-control" v-model="filters.date_to" @change="loadTransfers">
                            </div>
                        </div>

                        <!-- Stats Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4 class="mb-0">{{ stats.total_transfers?.toLocaleString() || 0 }}</h4>
                                                <p class="mb-0">Total Transfers</p>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-exchange-alt fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4 class="mb-0">KSh {{ formatCurrency(stats.total_amount) || 0 }}</h4>
                                                <p class="mb-0">Total Amount</p>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-money-bill-wave fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4 class="mb-0">{{ stats.today_transfers?.toLocaleString() || 0 }}</h4>
                                                <p class="mb-0">Today's Transfers</p>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-calendar-day fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4 class="mb-0">KSh {{ formatCurrency(stats.today_amount) || 0 }}</h4>
                                                <p class="mb-0">Today's Amount</p>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-coins fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transfers Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Transfer ID</th>
                                        <th>From Student</th>
                                        <th>To Student</th>
                                        <th>Amount</th>
                                        <th>Reason</th>
                                        <th>Initiated By</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="transfer in transfers.data" :key="transfer.id" class="align-middle">
                                        <td class="fw-bold">
                                            TRANS-{{ String(transfer.id).padStart(6, '0') }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-graduate text-danger me-2"></i>
                                                <div>
                                                    <div>{{ transfer.from_student?.full_name }}</div>
                                                    <small class="text-muted">{{ transfer.from_student?.admission_number }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-graduate text-success me-2"></i>
                                                <div>
                                                    <div>{{ transfer.to_student?.full_name }}</div>
                                                    <small class="text-muted">{{ transfer.to_student?.admission_number }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fw-bold text-primary">
                                            KSh {{ formatCurrency(transfer.amount) }}
                                        </td>
                                        <td>
                                            <span class="text-muted">{{ transfer.reason }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ transfer.initiated_by?.name || 'System' }}
                                            </small>
                                        </td>
                                        <td>{{ formatDate(transfer.created_at) }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-info" 
                                                    @click="viewTransferDetails(transfer)"
                                                    title="View Transfer Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty State -->
                        <div v-if="transfers.data && transfers.data.length === 0" class="text-center py-5">
                            <i class="fas fa-exchange-alt fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No Transfers Found</h4>
                            <p class="text-muted mb-4">No fund transfer records match your current filters</p>
                            <Link :href="route('admin.fees.transfers.create')" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Create First Transfer
                            </Link>
                        </div>

                        <!-- Pagination -->
                        <div v-if="transfers.meta && transfers.meta.last_page > 1" class="mt-4">
                            <nav aria-label="Transfers pagination">
                                <ul class="pagination justify-content-center mb-0">
                                    <li 
                                        v-for="link in transfers.meta.links" 
                                        :key="link.label"
                                        :class="['page-item', { 
                                            active: link.active, 
                                            disabled: !link.url 
                                        }]"
                                    >
                                        <Link 
                                            :href="link.url || '#'" 
                                            class="page-link"
                                            v-html="link.label"
                                            preserve-scroll
                                        />
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, reactive, computed } from 'vue';

// Define props from controller
const props = defineProps({
    transfers: Object,
    filters: Object,
    stats: Object
});

// Reactive data
const filters = reactive({
    search: props.filters?.search || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || ''
});

// Helper methods
const formatCurrency = (amount) => {
    if (!amount) return '0.00';
    return parseFloat(amount).toLocaleString('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Action methods
const loadTransfers = () => {
    router.get(route('admin.fees.transfers.index'), filters, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
};

const viewTransferDetails = (transfer) => {
    // Implement view transfer details modal or page
    alert(`Transfer Details:\n\nFrom: ${transfer.from_student?.full_name}\nTo: ${transfer.to_student?.full_name}\nAmount: KSh ${formatCurrency(transfer.amount)}\nReason: ${transfer.reason}\nDate: ${formatDate(transfer.created_at)}`);
};

const resetFilters = () => {
    filters.search = '';
    filters.date_from = '';
    filters.date_to = '';
    loadTransfers();
};
</script>

<style scoped>
.card-header {
    
    color: white;
    border-bottom: none;
}

.card-header .card-title {
    color: white;
}

.table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}

.page-item.active .page-link {
    background-color: #667eea;
    border-color: #667eea;
}
</style>