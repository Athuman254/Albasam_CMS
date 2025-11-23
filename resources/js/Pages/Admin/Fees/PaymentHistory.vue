<template>
  <DefaultLayout>
    <Head title="Payment History" />
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-history me-2"></i>Payment History
                        </h4>
                        <div class="btn-group">
                            <Link :href="route('admin.fees.payments.verify')" class="btn btn-primary">
                                <i class="fas fa-check-circle me-1"></i> Verify Payment
                            </Link>
                            <Link :href="route('admin.fees.index')" class="btn btn-secondary">
                                <i class="fas fa-money-bill-wave me-1"></i> Manage Fees
                            </Link>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Payment Method</label>
                                <select class="form-select" v-model="filters.payment_method" @change="loadPayments">
                                    <option value="">All Methods</option>
                                    <option value="mpesa">M-Pesa</option>
                                    <option value="bank">Bank</option>
                                    <option value="cash">Cash</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" v-model="filters.status" @change="loadPayments">
                                    <option value="">All Status</option>
                                    <option value="completed">Completed</option>
                                    <option value="pending">Pending</option>
                                    <option value="reversed">Reversed</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date From</label>
                                <input type="date" class="form-control" v-model="filters.date_from" @change="loadPayments">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Date To</label>
                                <input type="date" class="form-control" v-model="filters.date_to" @change="loadPayments">
                            </div>
                        </div>

                        <!-- Stats Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h4 class="mb-0">{{ stats.total_payments?.toLocaleString() || 0 }}</h4>
                                                <p class="mb-0">Total Payments</p>
                                            </div>
                                            <div class="align-self-center">
                                                <i class="fas fa-receipt fa-2x"></i>
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
                                                <h4 class="mb-0">{{ stats.today_payments?.toLocaleString() || 0 }}</h4>
                                                <p class="mb-0">Today's Payments</p>
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

                        <!-- Payments Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Receipt No</th>
                                        <th>Student</th>
                                        <th>Admission No</th>
                                        <th>Class</th>
                                        <th>Fee Type</th>
                                        <th>Amount</th>
                                        <th>Payment Method</th>
                                        <th>Reference</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Verified By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="payment in payments.data" :key="payment.id" class="align-middle">
                                        <td class="fw-bold">
                                            RCPT-{{ String(payment.id).padStart(6, '0') }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-graduate text-primary me-2"></i>
                                                {{ payment.student?.full_name }}
                                            </div>
                                        </td>
                                        <td>{{ payment.student?.admission_number }}</td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                {{ payment.fee?.rank?.name }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ formatFeeType(payment.fee?.fee_type) }}
                                            </span>
                                        </td>
                                        <td class="fw-bold text-success">
                                            KSh {{ formatCurrency(payment.amount) }}
                                        </td>
                                        <td>
                                            <span :class="`badge bg-${getPaymentMethodBadge(payment.payment_method)}`">
                                                {{ formatPaymentMethod(payment.payment_method) }}
                                            </span>
                                        </td>
                                        <td>
                                            <code>{{ payment.reference_number }}</code>
                                        </td>
                                        <td>{{ formatDate(payment.payment_date) }}</td>
                                        <td>
                                            <span :class="`badge bg-${getPaymentStatusBadge(payment.status)}`">
                                                {{ payment.status?.charAt(0)?.toUpperCase() + payment.status?.slice(1) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ payment.verified_by?.name || 'N/A' }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <Link 
                                                    :href="route('admin.fees.payments.receipt', payment.id)"
                                                    class="btn btn-outline-primary"
                                                    target="_blank"
                                                    title="View Receipt"
                                                >
                                                    <i class="fas fa-receipt"></i>
                                                </Link>
                                                <button 
                                                    v-if="payment.status === 'completed'"
                                                    @click="showReverseModal(payment)"
                                                    class="btn btn-outline-warning"
                                                    title="Reverse Payment"
                                                >
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty State -->
                        <div v-if="payments.data && payments.data.length === 0" class="text-center py-5">
                            <i class="fas fa-receipt fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No Payments Found</h4>
                            <p class="text-muted mb-4">No payment records match your current filters</p>
                            <button @click="resetFilters" class="btn btn-primary">
                                <i class="fas fa-refresh me-2"></i>Reset Filters
                            </button>
                        </div>

                        <!-- Pagination -->
                        <div v-if="payments.meta && payments.meta.last_page > 1" class="mt-4">
                            <nav aria-label="Payments pagination">
                                <ul class="pagination justify-content-center mb-0">
                                    <li 
                                        v-for="link in payments.meta.links" 
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

    <!-- Reverse Payment Modal -->
    <div class="modal fade" id="reversePaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reverse Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div v-if="selectedPayment" class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        You are about to reverse a payment. This action cannot be undone.
                    </div>
                    
                    <div v-if="selectedPayment" class="payment-details mb-3">
                        <p><strong>Student:</strong> {{ selectedPayment.student?.full_name }}</p>
                        <p><strong>Amount:</strong> KSh {{ formatCurrency(selectedPayment.amount) }}</p>
                        <p><strong>Reference:</strong> {{ selectedPayment.reference_number }}</p>
                        <p><strong>Date:</strong> {{ formatDate(selectedPayment.payment_date) }}</p>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Reason for Reversal *</label>
                        <textarea 
                            class="form-control" 
                            v-model="reverseForm.reason" 
                            rows="3" 
                            placeholder="Please provide a reason for reversing this payment..."
                            :class="{ 'is-invalid': reverseForm.errors.reason }"
                        ></textarea>
                        <div v-if="reverseForm.errors.reason" class="invalid-feedback">
                            {{ reverseForm.errors.reason }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button 
                        type="button" 
                        class="btn btn-warning" 
                        @click="reversePayment"
                        :disabled="!reverseForm.reason.trim() || reversingPayment"
                    >
                        <span v-if="reversingPayment" class="spinner-border spinner-border-sm me-2"></span>
                        {{ reversingPayment ? 'Reversing...' : 'Reverse Payment' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

// Define props from controller
const props = defineProps({
    payments: Object,
    filters: Object,
    stats: Object
});

// Reactive data - initialize with props
const filters = reactive({
    payment_method: props.filters?.payment_method || '',
    status: props.filters?.status || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || ''
});

const selectedPayment = ref(null);
const reversingPayment = ref(false);

const reverseForm = reactive({
    reason: '',
    errors: {}
});

// Helper methods
const formatFeeType = (feeType) => {
    if (!feeType) return 'Fee';
    return feeType.charAt(0).toUpperCase() + feeType.slice(1).replace('_', ' ') + ' Fee';
};

const formatPaymentMethod = (method) => {
    const methods = {
        'mpesa': 'M-Pesa',
        'bank': 'Bank Transfer',
        'cash': 'Cash',
        'cheque': 'Cheque'
    };
    return methods[method] || method;
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatCurrency = (amount) => {
    if (!amount) return '0.00';
    return parseFloat(amount).toLocaleString('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const getPaymentMethodBadge = (method) => {
    const badgeMap = {
        'mpesa': 'success',
        'bank': 'info',
        'cash': 'warning',
        'cheque': 'secondary'
    };
    return badgeMap[method] || 'secondary';
};

const getPaymentStatusBadge = (status) => {
    const statusMap = {
        'completed': 'success',
        'pending': 'warning',
        'failed': 'danger',
        'reversed': 'secondary'
    };
    return statusMap[status] || 'secondary';
};

// Action methods
const loadPayments = () => {
    router.get(route('admin.fees.payments.index'), filters, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
};

const showReverseModal = (payment) => {
    selectedPayment.value = payment;
    reverseForm.reason = '';
    reverseForm.errors = {};
    
    // Show modal using Bootstrap
    const modalElement = document.getElementById('reversePaymentModal');
    if (modalElement) {
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
    }
};

const reversePayment = async () => {
    if (!selectedPayment.value || !reverseForm.reason.trim()) return;

    reversingPayment.value = true;
    reverseForm.errors = {};

    try {
        const response = await axios.post(
            route('admin.fees.payments.reverse', selectedPayment.value.id),
            { reason: reverseForm.reason }
        );

        if (response.data.success) {
            // Close modal
            const modalElement = document.getElementById('reversePaymentModal');
            if (modalElement) {
                const modal = bootstrap.Modal.getInstance(modalElement);
                modal.hide();
            }
            
            // Reload the page to get updated data
            router.reload();
            
            // Show success message
            alert('Payment reversed successfully!');
        } else {
            reverseForm.errors.reason = response.data.message;
        }
    } catch (error) {
        if (error.response?.data?.errors) {
            reverseForm.errors = error.response.data.errors;
        } else {
            const message = error.response?.data?.message || error.message;
            reverseForm.errors.reason = 'Error reversing payment: ' + message;
        }
    } finally {
        reversingPayment.value = false;
    }
};

const resetFilters = () => {
    filters.payment_method = '';
    filters.status = '';
    filters.date_from = '';
    filters.date_to = '';
    loadPayments();
};

// Initialize with props data
onMounted(() => {
    // Stats are already loaded from controller props
    console.log('Payment History component mounted');
});
</script>

<style scoped>
.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

.badge {
    font-size: 0.75em;
    font-weight: 500;
}

.btn-group .btn {
    border-radius: 0.375rem;
    margin: 0 2px;
}

.page-item.active .page-link {
    background-color: #667eea;
    border-color: #667eea;
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.payment-details {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    border-left: 4px solid #667eea;
}
</style>