<template>
  <DefaultLayout>
    <Head title="Transfer Funds" />
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-exchange-alt me-2"></i>Transfer Funds Between Students
                        </h4>
                        <div>
                            <Link :href="route('admin.fees.transfers.index')" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-history me-1"></i> Transfer History
                            </Link>
                            <Link v-if="hasPendingApprovals" :href="route('admin.fees.transfers.pending-approvals')" class="btn btn-outline-warning me-2">
                                <i class="fas fa-clock me-1"></i> Pending Approvals
                                <span class="badge bg-danger ms-1">{{ pendingApprovalsCount }}</span>
                            </Link>
                            <button @click="showQuickGuide" class="btn btn-outline-info">
                                <i class="fas fa-question-circle me-1"></i> Help
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Notification Alert -->
                        <div v-if="notification.show" class="alert" :class="notification.type" role="alert">
                            <i :class="notification.icon" class="me-2"></i>
                            {{ notification.message }}
                            <button type="button" class="btn-close" @click="dismissNotification"></button>
                        </div>

                        <!-- Quick Stats -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card border-primary">
                                    <div class="card-body py-3">
                                        <h6 class="card-title text-primary">Total Transfers Today</h6>
                                        <h4 class="mb-0">{{ stats.today_transfers || 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-success">
                                    <div class="card-body py-3">
                                        <h6 class="card-title text-success">Total Amount Today</h6>
                                        <h4 class="mb-0">KSh {{ formatCurrency(stats.today_amount || 0) }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-info">
                                    <div class="card-body py-3">
                                        <h6 class="card-title text-info">This Month</h6>
                                        <h4 class="mb-0">{{ stats.month_transfers || 0 }} transfers</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-warning">
                                    <div class="card-body py-3">
                                        <h6 class="card-title text-warning">Pending Approvals</h6>
                                        <h4 class="mb-0">{{ stats.pending_approvals || 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">From Student (Admission Number) *</label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           v-model="transferForm.from_admission_number"
                                           @blur="loadFromStudent" 
                                           @keyup.enter="loadFromStudent"
                                           :class="{ 'is-invalid': fromStudentError }"
                                           placeholder="Enter admission number">
                                    <button @click="scanFromStudent" class="btn btn-outline-secondary" type="button">
                                        <i class="fas fa-barcode">Search</i>
                                    </button>
                                </div>
                                <div v-if="fromStudent" class="mt-2 p-3 bg-light rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ fromStudent.full_name }}</strong><br>
                                            <small class="text-muted">
                                                Class: {{ fromStudent.current_rank?.name }} | 
                                                Admission: {{ fromStudent.admission_number }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-success fs-6">
                                                Balance: KSh {{ formatCurrency(fromStudentBalance) }}
                                            </span>
                                        </div>
                                    </div>
                                    <!-- Overdue Fees Information -->
                                    <div v-if="fromStudentOverdueDetails.has_overdue" class="mt-2 p-2 bg-success bg-opacity-10 border border-success rounded">
                                        <i class="fas fa-check-circle me-2 text-success"></i>
                                        <strong class="text-success">Overdue Fees Available:</strong>
                                        <span class="ms-2">KSh {{ formatCurrency(fromStudentOverdueDetails.total_overdue) }}</span>
                                        <small class="d-block text-muted mt-1">
                                            {{ fromStudentOverdueDetails.overdue_count }} overdue fee item(s)
                                        </small>
                                    </div>
                                    <div v-else class="mt-2 p-2 bg-danger bg-opacity-10 border border-danger rounded">
                                        <i class="fas fa-exclamation-circle me-2 text-danger"></i>
                                        <strong class="text-danger">No Overdue Fees:</strong>
                                        <span class="ms-2">This student has no overdue fees for transfer</span>
                                    </div>
                                </div>
                                <div v-if="fromStudentError" class="mt-2 p-2 bg-light border border-danger rounded">
                                    <i class="fas fa-exclamation-circle me-2 text-danger"></i>
                                    <span class="text-danger">{{ fromStudentError }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">To Student (Admission Number) *</label>
                                <div class="input-group">
                                    <input type="text" 
                                           class="form-control" 
                                           v-model="transferForm.to_admission_number"
                                           @blur="loadToStudent" 
                                           @keyup.enter="loadToStudent"
                                           :class="{ 'is-invalid': toStudentError }"
                                           placeholder="Enter admission number">
                                    <button @click="scanToStudent" class="btn btn-outline-secondary" type="button">
                                        <i class="fas fa-barcode">Search</i>
                                    </button>
                                </div>
                                <div v-if="toStudent" class="mt-2 p-3 bg-light rounded">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ toStudent.full_name }}</strong><br>
                                            <small class="text-muted">
                                                Class: {{ toStudent.current_rank?.name }} | 
                                                Admission: {{ toStudent.admission_number }}
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-info fs-6">Recipient</span>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="toStudentError" class="mt-2 p-2 bg-light border border-danger rounded">
                                    <i class="fas fa-exclamation-circle me-2 text-danger"></i>
                                    <span class="text-danger">{{ toStudentError }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label">Transfer Amount *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">KSh</span>
                                    <input type="number" 
                                           class="form-control" 
                                           v-model="transferForm.amount" 
                                           step="0.01" 
                                           min="0.01" 
                                           :max="Math.min(fromStudentBalance, fromStudentOverdueDetails.total_overdue)"
                                           :class="{ 'is-invalid': transferForm.errors?.amount }"
                                           placeholder="Enter amount to transfer"
                                           :disabled="!fromStudentOverdueDetails.has_overdue">
                                    <button @click="setMaxAmount" class="btn btn-outline-secondary" type="button"
                                            :disabled="!fromStudentOverdueDetails.has_overdue">
                                        Max
                                    </button>
                                </div>
                                <div class="mt-2">
                                    <small class="text-muted">
                                        Available balance: <strong>KSh {{ formatCurrency(fromStudentBalance) }}</strong>
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        Overdue amount: <strong>KSh {{ formatCurrency(fromStudentOverdueDetails.total_overdue) }}</strong>
                                    </small>
                                    <div class="progress mt-1" style="height: 5px;">
                                        <div class="progress-bar" 
                                             :class="amountProgressClass"
                                             :style="{ width: amountProgressWidth }">
                                        </div>
                                    </div>
                                </div>
                                <div v-if="transferForm.errors?.amount" class="invalid-feedback d-block">
                                    {{ transferForm.errors.amount[0] }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Transfer Reason *</label>
                                <select class="form-control" 
                                        v-model="transferForm.reason_type"
                                        :class="{ 'is-invalid': transferForm.errors?.reason_type }">
                                    <option value="">Select a reason for transfer</option>
                                    <option v-for="(reason, value) in validReasons" :key="value" :value="value">
                                        {{ reason.label }}
                                        <span v-if="reason.requires_approval" class="text-warning"> (Requires Approval)</span>
                                    </option>
                                </select>
                                <div v-if="transferForm.errors?.reason_type" class="invalid-feedback d-block">
                                    {{ transferForm.errors.reason_type[0] }}
                                </div>

                                <!-- Additional notes for "other" reason -->
                                <div v-if="transferForm.reason_type === 'other'" class="mt-3">
                                    <label class="form-label">Additional Notes *</label>
                                    <textarea class="form-control" 
                                              v-model="transferForm.reason_notes" 
                                              rows="2" 
                                              placeholder="Please provide detailed explanation for this transfer..."
                                              :class="{ 'is-invalid': transferForm.errors?.reason_notes }">
                                    </textarea>
                                    <small class="text-warning">This transfer will require additional approval</small>
                                    <div v-if="transferForm.errors?.reason_notes" class="invalid-feedback d-block">
                                        {{ transferForm.errors.reason_notes[0] }}
                                    </div>
                                </div>

                                <!-- Optional notes for other reasons -->
                                <div v-else-if="transferForm.reason_type" class="mt-3">
                                    <label class="form-label">Additional Notes (Optional)</label>
                                    <textarea class="form-control" 
                                              v-model="transferForm.reason_notes" 
                                              rows="2" 
                                              placeholder="Any additional information..."
                                              :class="{ 'is-invalid': transferForm.errors?.reason_notes }">
                                    </textarea>
                                    <div v-if="transferForm.errors?.reason_notes" class="invalid-feedback d-block">
                                        {{ transferForm.errors.reason_notes[0] }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transfer Summary -->
                        <div v-if="showSummary" class="row mt-4">
                            <div class="col-12">
                                <div class="card border" :class="summaryBorderClass">
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <i class="fas fa-info-circle me-2"></i>Transfer Summary
                                        </h6>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong><i class="fas fa-user-minus me-2"></i>From:</strong><br>
                                                {{ fromStudent.full_name }} ({{ fromStudent.admission_number }})<br>
                                                <strong>Current Balance:</strong> KSh {{ formatCurrency(fromStudentBalance) }}<br>
                                                <strong>After Transfer:</strong> KSh {{ formatCurrency(fromStudentBalance - transferForm.amount) }}<br>
                                                <strong>Overdue Amount:</strong> KSh {{ formatCurrency(fromStudentOverdueDetails.total_overdue) }}
                                            </div>
                                            <div class="col-md-6">
                                                <strong><i class="fas fa-user-plus me-2"></i>To:</strong><br>
                                                {{ toStudent.full_name }} ({{ toStudent.admission_number }})<br>
                                                <strong>Amount:</strong> KSh {{ formatCurrency(transferForm.amount) }}<br>
                                                <strong>Reason:</strong> {{ getReasonLabel(transferForm.reason_type) }}<br>
                                                <span v-if="transferForm.reason_notes" class="text-muted">
                                                    <strong>Notes:</strong> {{ transferForm.reason_notes }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-12">
                                                <div v-if="requiresApproval" class="alert alert-warning mb-0">
                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                    <strong>Approval Required:</strong> This transfer requires additional approval before processing.
                                                </div>
                                                <div v-else class="alert alert-success mb-0">
                                                    <i class="fas fa-check-circle me-2"></i>
                                                    This transfer will be processed immediately.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-12">
                                <button @click="transferFunds" 
                                        class="btn btn-primary btn-lg" 
                                        :disabled="!canTransfer || transferring"
                                        :class="{ 'btn-loading': transferring }">
                                    <span v-if="transferring" class="spinner-border spinner-border-sm me-2"></span>
                                    <i v-else class="fas fa-exchange-alt me-2"></i>
                                    {{ transferButtonText }}
                                </button>
                                <button @click="resetForm" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-redo me-2"></i>Reset Form
                                </button>
                                <button @click="saveDraft" class="btn btn-outline-info ms-2" v-if="hasDraftData">
                                    <i class="fas fa-save me-2"></i>Save Draft
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Guide Modal -->
        <div class="modal fade" id="quickGuideModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Fee Transfer Guide</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <h6>Transfer Requirements:</h6>
                        <ul>
                            <li><strong>From Student:</strong> Must have overdue fees available for transfer</li>
                            <li><strong>To Student:</strong> Any valid student can receive funds</li>
                            <li><strong>Amount:</strong> Cannot exceed available overdue amount</li>
                        </ul>

                        <h6>Valid Transfer Reasons:</h6>
                        <ul>
                            <li v-for="(reason, value) in validReasons" :key="value">
                                <strong>{{ reason.label }}</strong> 
                                <span v-if="reason.requires_approval" class="badge bg-warning ms-1">Requires Approval</span>
                            </li>
                        </ul>
                        
                        <h6>Important Notes:</h6>
                        <ul>
                            <li>Transfers are only allowed from students with overdue fees</li>
                            <li>Select the appropriate reason for the transfer</li>
                            <li>"Other" reason requires detailed explanation and approval</li>
                            <li>Transfers with certain reasons may require additional approval</li>
                            <li>Keep proper documentation for all transfers</li>
                            <li>All transfers are logged for audit purposes</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';

// Reactive data
const fromStudent = ref(null);
const toStudent = ref(null);
const fromStudentBalance = ref(0);
const fromStudentError = ref('');
const toStudentError = ref('');
const transferring = ref(false);
const stats = ref({});
const validReasons = ref({});
const pendingApprovalsCount = ref(0);
const fromStudentOverdueDetails = reactive({
    has_overdue: false,
    total_overdue: 0,
    overdue_count: 0,
    overdue_fees: []
});

// Notification system
const notification = reactive({
    show: false,
    message: '',
    type: 'alert-success',
    icon: 'fas fa-check-circle'
});

const transferForm = reactive({
    from_admission_number: '',
    to_admission_number: '',
    amount: 0,
    reason_type: '',
    reason_notes: '',
    errors: {}
});

// Track loading states to prevent duplicate requests
const loadingFromStudent = ref(false);
const loadingToStudent = ref(false);

// Computed properties
const showSummary = computed(() => {
    return fromStudent.value && 
           toStudent.value && 
           transferForm.amount > 0 && 
           transferForm.reason_type &&
           !fromStudentError.value && 
           !toStudentError.value &&
           fromStudentOverdueDetails.has_overdue;
});

const requiresApproval = computed(() => {
    if (!transferForm.reason_type) return false;
    const reason = validReasons.value[transferForm.reason_type];
    return reason ? reason.requires_approval : false;
});

const canTransfer = computed(() => {
    return fromStudent.value && 
           toStudent.value && 
           transferForm.amount > 0 && 
           transferForm.amount <= fromStudentBalance.value &&
           transferForm.amount <= fromStudentOverdueDetails.total_overdue &&
           transferForm.reason_type.trim() !== '' &&
           (transferForm.reason_type !== 'other' ? true : transferForm.reason_notes.trim() !== '') &&
           fromStudent.value.id !== toStudent.value?.id &&
           !transferring.value &&
           fromStudentOverdueDetails.has_overdue;
});

const transferButtonText = computed(() => {
    if (transferring.value) return 'Processing Transfer...';
    return requiresApproval.value ? 'Submit for Approval' : 'Transfer Funds';
});

const amountProgressWidth = computed(() => {
    if (!transferForm.amount || !fromStudentOverdueDetails.total_overdue) return '0%';
    const percentage = (transferForm.amount / fromStudentOverdueDetails.total_overdue) * 100;
    return Math.min(percentage, 100) + '%';
});

const amountProgressClass = computed(() => {
    const percentage = (transferForm.amount / fromStudentOverdueDetails.total_overdue) * 100;
    if (percentage > 90) return 'bg-danger';
    if (percentage > 75) return 'bg-warning';
    return 'bg-success';
});

const summaryBorderClass = computed(() => {
    const percentage = (transferForm.amount / fromStudentOverdueDetails.total_overdue) * 100;
    if (percentage > 90) return 'border-warning';
    if (percentage > 75) return 'border-info';
    return 'border-success';
});

const hasDraftData = computed(() => {
    return transferForm.from_admission_number || 
           transferForm.to_admission_number || 
           transferForm.amount > 0 || 
           transferForm.reason_type;
});

const hasPendingApprovals = computed(() => {
    return pendingApprovalsCount.value > 0;
});

// Helper methods
const formatCurrency = (amount) => {
    if (!amount) return '0.00';
    return parseFloat(amount).toLocaleString('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const getReasonLabel = (reasonType) => {
    const reason = validReasons.value[reasonType];
    return reason ? reason.label : reasonType;
};

const requiresApprovalForReason = (reasonType) => {
    const reason = validReasons.value[reasonType];
    return reason ? reason.requires_approval : false;
};

// Notification methods
const showNotification = (message, type = 'success') => {
    notification.show = true;
    notification.message = message;
    
    switch (type) {
        case 'success':
            notification.type = 'alert-success';
            notification.icon = 'fas fa-check-circle';
            break;
        case 'error':
            notification.type = 'alert-danger';
            notification.icon = 'fas fa-exclamation-circle';
            break;
        case 'warning':
            notification.type = 'alert-warning';
            notification.icon = 'fas fa-exclamation-triangle';
            break;
        case 'info':
            notification.type = 'alert-info';
            notification.icon = 'fas fa-info-circle';
            break;
        default:
            notification.type = 'alert-info';
            notification.icon = 'fas fa-info-circle';
    }
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        dismissNotification();
    }, 5000);
};

const dismissNotification = () => {
    notification.show = false;
    notification.message = '';
    notification.type = 'alert-success';
    notification.icon = 'fas fa-check-circle';
};

// Methods
const loadStats = async () => {
    try {
        const response = await axios.get(route('admin.fees.transfers.stats'));
        stats.value = response.data;
        pendingApprovalsCount.value = response.data.pending_approvals || 0;
    } catch (error) {
        console.error('Failed to load stats:', error);
        showNotification('Failed to load transfer statistics', 'error');
    }
};

const loadValidReasons = async () => {
    try {
        const response = await axios.get(route('admin.fees.transfers.reasons'));
        validReasons.value = response.data;
    } catch (error) {
        console.error('Failed to load valid reasons:', error);
        // Fallback to default reasons
        validReasons.value = {
            'fee_adjustment': { label: 'Fee Adjustment', requires_approval: false },
            'scholarship_allocation': { label: 'Scholarship Allocation', requires_approval: true },
            'bursary_transfer': { label: 'Bursary Transfer', requires_approval: true },
            'sponsorship_adjustment': { label: 'Sponsorship Adjustment', requires_approval: true },
            'payment_correction': { label: 'Payment Correction', requires_approval: false },
            'family_discount': { label: 'Family Discount', requires_approval: false },
            'sibling_transfer': { label: 'Sibling Transfer', requires_approval: false },
            'staff_discount': { label: 'Staff Discount', requires_approval: true },
            'other': { label: 'Other', requires_approval: true }
        };
        showNotification('Using default transfer reasons', 'info');
    }
};

const loadFromStudentOverdueDetails = async (studentId) => {
    try {
        // Check if route exists before making the request
        if (!route().has('admin.fees.transfers.overdue-details')) {
            console.warn('Overdue details route not found, skipping overdue check');
            fromStudentOverdueDetails.has_overdue = false;
            fromStudentOverdueDetails.total_overdue = 0;
            fromStudentOverdueDetails.overdue_count = 0;
            return;
        }

        const response = await axios.get(route('admin.fees.transfers.overdue-details', studentId));
        if (response.data.success) {
            fromStudentOverdueDetails.has_overdue = response.data.has_overdue;
            fromStudentOverdueDetails.total_overdue = parseFloat(response.data.total_overdue) || 0;
            fromStudentOverdueDetails.overdue_count = response.data.overdue_count || 0;
            fromStudentOverdueDetails.overdue_fees = response.data.overdue_fees || [];
            
            if (!fromStudentOverdueDetails.has_overdue) {
                fromStudentError.value = 'The student has no overdue fees for transfer';
            }
        }
    } catch (error) {
        console.error('Failed to load overdue details:', error);
        fromStudentOverdueDetails.has_overdue = false;
        fromStudentOverdueDetails.total_overdue = 0;
        fromStudentOverdueDetails.overdue_count = 0;
        
        // Don't show notification for route not found errors to avoid duplicates
        if (!error.message?.includes('route') && !error.response?.data?.message?.includes('route')) {
            showNotification('Error loading overdue fee details', 'error');
        }
    }
};

const loadFromStudent = async () => {
    if (loadingFromStudent.value) return;
    
    if (!transferForm.from_admission_number.trim()) {
        fromStudentError.value = 'Please enter an admission number';
        return;
    }

    loadingFromStudent.value = true;
    fromStudentError.value = '';
    fromStudent.value = null;
    fromStudentBalance.value = 0;
    fromStudentOverdueDetails.has_overdue = false;
    fromStudentOverdueDetails.total_overdue = 0;
    
    try {
        const searchResponse = await axios.get(
            route('admin.fees.students.search', transferForm.from_admission_number)
        );
        
        if (searchResponse.data.success && searchResponse.data.student) {
            fromStudent.value = searchResponse.data.student;
            
            const balanceResponse = await axios.get(
                route('admin.fees.students.balance', fromStudent.value.id)
            );
            
            if (balanceResponse.data.success) {
                fromStudentBalance.value = parseFloat(balanceResponse.data.balance) || 0;
                
                if (fromStudentBalance.value === 0) {
                    fromStudentError.value = 'Student has no available balance';
                } else {
                    // Load overdue details
                    await loadFromStudentOverdueDetails(fromStudent.value.id);
                    
                    if (fromStudentOverdueDetails.has_overdue) {
                        showNotification(`Student loaded successfully. Overdue amount: KSh ${formatCurrency(fromStudentOverdueDetails.total_overdue)}`, 'success');
                    } else {
                        // Only show notification if we actually have balance but no overdue
                        if (fromStudentBalance.value > 0) {
                            showNotification('Student has no overdue fees available for transfer', 'warning');
                        }
                    }
                }
            } else {
                fromStudentBalance.value = 0;
                fromStudentError.value = 'Could not load student balance';
            }
        } else {
            fromStudentError.value = searchResponse.data.message || 'Student not found';
        }
    } catch (error) {
        fromStudentError.value = 'Error loading student: ' + 
            (error.response?.data?.message || error.message);
    } finally {
        loadingFromStudent.value = false;
    }
};

const loadToStudent = async () => {
    if (loadingToStudent.value) return;
    
    if (!transferForm.to_admission_number.trim()) {
        toStudentError.value = 'Please enter an admission number';
        return;
    }

    loadingToStudent.value = true;
    toStudentError.value = '';
    toStudent.value = null;
    
    try {
        const response = await axios.get(
            route('admin.fees.students.search', transferForm.to_admission_number)
        );
        
        if (response.data.success && response.data.student) {
            toStudent.value = response.data.student;
            
            if (fromStudent.value && fromStudent.value.id === toStudent.value.id) {
                toStudentError.value = 'Cannot transfer funds to the same student';
                toStudent.value = null;
                showNotification('Cannot transfer funds to the same student', 'error');
            } else {
                showNotification('Recipient student loaded successfully', 'success');
            }
        } else {
            toStudentError.value = response.data.message || 'Student not found';
        }
    } catch (error) {
        toStudentError.value = 'Error loading student: ' + 
            (error.response?.data?.message || error.message);
    } finally {
        loadingToStudent.value = false;
    }
};

const setMaxAmount = () => {
    const maxAmount = Math.min(fromStudentBalance.value, fromStudentOverdueDetails.total_overdue);
    if (maxAmount > 0) {
        transferForm.amount = parseFloat(maxAmount);
        showNotification(`Amount set to maximum overdue amount: KSh ${formatCurrency(maxAmount)}`, 'info');
    }
};

const transferFunds = async () => {
    if (!canTransfer.value) return;
    
    transferring.value = true;
    transferForm.errors = {};
    dismissNotification();
    
    try {
        const response = await axios.post(
            route('admin.fees.transfers.store'), 
            {
                from_admission_number: transferForm.from_admission_number,
                to_admission_number: transferForm.to_admission_number,
                amount: parseFloat(transferForm.amount),
                reason_type: transferForm.reason_type,
                reason_notes: transferForm.reason_notes
            }
        );
        
        if (response.data.success) {
            if (response.data.requires_approval) {
                showNotification('Transfer submitted for approval. Please wait for authorization.', 'warning');
            } else {
                showNotification('Funds transferred successfully!', 'success');
            }
            resetForm();
            loadStats(); // Refresh stats
        } else {
            showNotification('Error transferring funds: ' + response.data.message, 'error');
        }
    } catch (error) {
        if (error.response?.data?.errors) {
            transferForm.errors = error.response.data.errors;
            showNotification('Please fix the form errors and try again.', 'error');
        } else {
            const message = error.response?.data?.message || error.message || 'Unknown error occurred';
            showNotification('Error transferring funds: ' + message, 'error');
        }
    } finally {
        transferring.value = false;
    }
};

const resetForm = () => {
    fromStudent.value = null;
    toStudent.value = null;
    fromStudentBalance.value = 0;
    fromStudentError.value = '';
    toStudentError.value = '';
    transferring.value = false;
    transferForm.from_admission_number = '';
    transferForm.to_admission_number = '';
    transferForm.amount = 0;
    transferForm.reason_type = '';
    transferForm.reason_notes = '';
    transferForm.errors = {};
    fromStudentOverdueDetails.has_overdue = false;
    fromStudentOverdueDetails.total_overdue = 0;
    fromStudentOverdueDetails.overdue_count = 0;
    loadingFromStudent.value = false;
    loadingToStudent.value = false;
    dismissNotification();
};

const scanFromStudent = () => {
    showNotification('Barcode scanning functionality would be implemented here', 'info');
};

const scanToStudent = () => {
    showNotification('Barcode scanning functionality would be implemented here', 'info');
};

const showQuickGuide = () => {
    const modal = new bootstrap.Modal(document.getElementById('quickGuideModal'));
    modal.show();
};

const saveDraft = () => {
    const draft = {
        from_admission_number: transferForm.from_admission_number,
        to_admission_number: transferForm.to_admission_number,
        amount: transferForm.amount,
        reason_type: transferForm.reason_type,
        reason_notes: transferForm.reason_notes,
        timestamp: new Date().toISOString()
    };
    localStorage.setItem('feeTransferDraft', JSON.stringify(draft));
    showNotification('Draft saved successfully', 'success');
};

const loadDraft = () => {
    const draft = localStorage.getItem('feeTransferDraft');
    if (draft) {
        const data = JSON.parse(draft);
        transferForm.from_admission_number = data.from_admission_number || '';
        transferForm.to_admission_number = data.to_admission_number || '';
        transferForm.amount = data.amount || 0;
        transferForm.reason_type = data.reason_type || '';
        transferForm.reason_notes = data.reason_notes || '';
        
        if (transferForm.from_admission_number) {
            setTimeout(() => loadFromStudent(), 500);
        }
        if (transferForm.to_admission_number) {
            setTimeout(() => loadToStudent(), 500);
        }
        
        showNotification('Draft loaded successfully', 'info');
    }
};

// Lifecycle
onMounted(() => {
    loadStats();
    loadValidReasons();
    loadDraft();
});
</script>

<style scoped>
.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    color: #495057;
}

.card-header .card-title {
    color: #495057;
    font-weight: 600;
}

.progress {
    background-color: #e9ecef;
    border-radius: 3px;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
    color: #495057;
}

.alert {
    border-left: 4px solid;
    transition: all 0.3s ease;
    background-color: white;
}

.alert-success {
    border-left-color: #198754;
    color: #0f5132;
}

.alert-info {
    border-left-color: #0dcaf0;
    color: #055160;
}

.alert-warning {
    border-left-color: #ffc107;
    color: #664d03;
}

.alert-danger {
    border-left-color: #dc3545;
    color: #842029;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    transition: all 0.3s ease;
}

.btn-primary:hover:not(:disabled) {
    background-color: #0056b3;
    border-color: #0056b3;
    transform: translateY(-1px);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-loading {
    position: relative;
}

.badge {
    font-size: 0.75em;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid #dee2e6;
}

.card.border-primary {
    border-color: #007bff !important;
}

.card.border-success {
    border-color: #198754 !important;
}

.card.border-info {
    border-color: #0dcaf0 !important;
}

.card.border-warning {
    border-color: #ffc107 !important;
}

.alert {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.bg-light {
    background-color: #f8f9fa !important;
}

.text-primary { color: #007bff !important; }
.text-success { color: #198754 !important; }
.text-info { color: #0dcaf0 !important; }
.text-warning { color: #ffc107 !important; }
.text-danger { color: #dc3545 !important; }

.border-primary { border-color: #007bff !important; }
.border-success { border-color: #198754 !important; }
.border-info { border-color: #0dcaf0 !important; }
.border-warning { border-color: #ffc107 !important; }

.bg-success.bg-opacity-10 {
    background-color: rgba(25, 135, 84, 0.1) !important;
}

.bg-danger.bg-opacity-10 {
    background-color: rgba(220, 53, 69, 0.1) !important;
}
</style>