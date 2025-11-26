<template>
  <DefaultLayout>
    <Head title="Verify Fee Payment" />
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h4 class="card-title mb-0 fw-bold text-dark">
                            <i class="fas fa-check-circle me-2 text-primary"></i>Verify Fee Payment
                        </h4>
                        <div>
                            <Link :href="route('admin.fees.payments.recorded.payments')" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fas fa-list me-1"></i> Verification Queue
                            </Link>
                            <Link :href="route('admin.fees.payments.index')" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-history me-1"></i> Payment History
                            </Link>
                        </div>
                    </div>
                    <div class="card-body bg-white">
                        <!-- Search Form -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-semibold">Reference Number *</label>
                                    <input 
                                        type="text" 
                                        class="form-control border" 
                                        v-model="form.reference_number" 
                                        placeholder="M-Pesa Code, Cheque No, Receipt No"
                                        :class="{ 'is-invalid': errors.reference_number }"
                                        @keyup.enter="checkPayment"
                                    >
                                    <div v-if="errors.reference_number" class="invalid-feedback">
                                        {{ errors.reference_number }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-semibold">Admission Number *</label>
                                    <input 
                                        type="text" 
                                        class="form-control border" 
                                        v-model="form.admission_number" 
                                        placeholder="Student admission number"
                                        :class="{ 'is-invalid': errors.admission_number }"
                                        @keyup.enter="checkPayment"
                                    >
                                    <div v-if="errors.admission_number" class="invalid-feedback">
                                        {{ errors.admission_number }}
                                    </div>
                                    <small class="text-muted">
                                        Required to link payment to student
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-12">
                                <button @click="checkPayment" class="btn btn-primary" :disabled="loading">
                                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                    <i class="fas fa-search me-1"></i>
                                    {{ loading ? 'Checking...' : 'Check Payment' }}
                                </button>
                                <button @click="resetForm" class="btn btn-outline-secondary ms-2">
                                    <i class="fas fa-redo me-1"></i> Reset
                                </button>
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div v-if="errorMessage" class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-danger border-0 shadow-sm">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    {{ errorMessage }}
                                </div>
                            </div>
                        </div>

                        <!-- Success Message -->
                        <div v-if="successMessage" class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-success border-0 shadow-sm">
                                    <i class="fas fa-check-circle me-2"></i>
                                    {{ successMessage }}
                                </div>
                            </div>
                        </div>

                        <!-- Verification Results -->
                        <div v-if="student" class="row mb-4">
                            <div class="col-12">
                                <div class="card border shadow-sm">
                                    <div class="card-header bg-white py-3">
                                        <h5 class="mb-0 text-dark">
                                            <i class="fas fa-user-graduate me-2 text-primary"></i>Student Details
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>Name:</strong> {{ student.full_name }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Admission No:</strong> {{ student.admission_number }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Class:</strong> {{ student.current_rank?.name }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong>Total Balance:</strong> 
                                                <span class="text-danger fw-bold">
                                                    KSh {{ totalOutstandingBalance.toLocaleString() }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Outstanding Fees -->
                        <div v-if="outstandingFees.length > 0" class="row mb-4">
                            <div class="col-12">
                                <div class="card border shadow-sm">
                                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0 text-dark">
                                            <i class="fas fa-money-bill-wave me-2 text-primary"></i>Outstanding Fees
                                            <span class="badge bg-danger ms-2">{{ outstandingFees.length }}</span>
                                        </h5>
                                        <div>
                                            <button @click="selectAllFees" class="btn btn-outline-primary btn-sm me-2">
                                                <i class="fas fa-check-square me-1"></i> Select All
                                            </button>
                                            <button @click="deselectAllFees" class="btn btn-outline-secondary btn-sm">
                                                <i class="fas fa-times-circle me-1"></i> Deselect All
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th width="60">
                                                            <input 
                                                                type="checkbox" 
                                                                v-model="allFeesSelected"
                                                                @change="toggleAllFees"
                                                                class="form-check-input"
                                                            >
                                                        </th>
                                                        <th>Fee Type</th>
                                                        <th>Academic Year</th>
                                                        <th>Term</th>
                                                        <th>Total Amount</th>
                                                        <th>Paid Amount</th>
                                                        <th>Balance</th>
                                                        <th>Due Date</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="fee in outstandingFees" :key="fee.id" 
                                                        :class="{'table-warning': selectedFeeIds.includes(fee.id), 'table-danger': isOverdue(fee.due_date)}">
                                                        <td class="text-center">
                                                            <input 
                                                                type="checkbox" 
                                                                v-model="selectedFeeIds" 
                                                                :value="fee.id"
                                                                class="form-check-input fee-checkbox"
                                                            >
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-primary">
                                                                {{ formatFeeType(fee.fee_type) }}
                                                            </span>
                                                            <span v-if="fee.is_carry_over" class="badge bg-warning ms-1" title="Includes carried over balance">
                                                                Carry-over
                                                            </span>
                                                        </td>
                                                        <td>{{ fee.academic_year }}</td>
                                                        <td>Term {{ fee.term }}</td>
                                                        <td class="fw-bold">KSh {{ fee.amount?.toLocaleString() }}</td>
                                                        <td class="text-success">KSh {{ fee.paid_amount?.toLocaleString() }}</td>
                                                        <td class="text-danger fw-bold">KSh {{ fee.balance?.toLocaleString() }}</td>
                                                        <td>
                                                            <span :class="{'text-danger fw-bold': isOverdue(fee.due_date)}">
                                                                {{ formatDate(fee.due_date) }}
                                                                <i v-if="isOverdue(fee.due_date)" class="fas fa-exclamation-triangle ms-1" title="Overdue"></i>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span :class="`badge bg-${getFeeStatusBadge(fee.status)}`">
                                                                {{ fee.status }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <button 
                                                                @click="openVerificationModal(fee)" 
                                                                class="btn btn-primary btn-sm"
                                                                :disabled="!autoPayment"
                                                            >
                                                                <i class="fas fa-check me-1"></i> Verify Single
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bulk Verification Button -->
                        <div v-if="student && outstandingFees.length > 0 && selectedFeeIds.length > 0" class="row mb-4">
                            <div class="col-12">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <h5 class="text-success mb-3">
                                            <i class="fas fa-bolt me-2"></i>
                                            Bulk Payment Verification Ready
                                        </h5>
                                        <p class="mb-3">
                                            <strong>{{ selectedFeeIds.length }}</strong> fee(s) selected with total balance of 
                                            <strong class="text-danger">KSh {{ totalSelectedBalance.toLocaleString() }}</strong>
                                        </p>
                                        <button @click="openBulkVerificationModal" class="btn btn-success btn-lg">
                                            <i class="fas fa-check-double me-2"></i>
                                            Verify Payment for Selected Fees
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- No Outstanding Fees Message -->
                        <div v-if="student && outstandingFees.length === 0" class="row mb-4">
                            <div class="col-12">
                                <div class="alert alert-success border-0 shadow-sm">
                                    <i class="fas fa-check-circle me-2"></i>
                                    This student has no outstanding fees. All fees are paid up to date.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Single Fee Verification Modal -->
    <div class="modal fade" id="verificationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-white border-bottom py-3">
                    <h5 class="modal-title text-dark fw-bold">
                        <i class="fas fa-check-circle me-2 text-primary"></i>Confirm Payment Verification
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-white">
                    <!-- Payment Details -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0 text-dark">
                                        <i class="fas fa-money-bill-wave me-2"></i>Payment Details
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <strong>Reference:</strong> 
                                            <span class="badge bg-primary">{{ autoPayment?.reference_number }}</span>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong>Amount:</strong> 
                                            <span class="text-success fw-bold">KSh {{ autoPayment?.amount?.toLocaleString() }}</span>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong>Method:</strong> 
                                            <span class="badge bg-info text-capitalize">{{ autoPayment?.payment_method }}</span>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong>Date:</strong> {{ formatDate(autoPayment?.payment_date) }}
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong>Payer:</strong> {{ autoPayment?.payer_name || 'N/A' }}
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong>Phone:</strong> {{ autoPayment?.payer_phone || 'N/A' }}
                                        </div>
                                    </div>
                                    <div v-if="autoPayment?.narration" class="row mt-2">
                                        <div class="col-12">
                                            <strong>Narration:</strong> {{ autoPayment.narration }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Student Details -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0 text-dark">
                                        <i class="fas fa-user-graduate me-2"></i>Student Details
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <strong>Name:</strong> {{ student?.full_name }}
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong>Admission No:</strong> {{ student?.admission_number }}
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong>Class:</strong> {{ student?.current_rank?.name }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fee Details -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0 text-dark">
                                        <i class="fas fa-file-invoice me-2"></i>Selected Fee Details
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <strong>Fee Type:</strong> {{ selectedFeeType }}
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong>Academic Year:</strong> {{ selectedFee?.academic_year }}
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong>Term:</strong> {{ selectedFee?.term }}
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <strong>Balance:</strong> 
                                            <span class="text-danger fw-bold">KSh {{ selectedFeeBalance?.toLocaleString() }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Options for Overpayment -->
                    <div v-if="showPaymentOptions" class="row mb-4">
                        <div class="col-12">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark py-2">
                                    <h6 class="mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>Payment Distribution Options
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        <strong>Payment Amount:</strong> KSh {{ formatCurrency(autoPayment?.amount) }}<br>
                                        <strong>Selected Fee Balance:</strong> KSh {{ formatCurrency(selectedFeeBalance) }}<br>
                                        <strong>Excess Amount:</strong> KSh {{ formatCurrency(autoPayment?.amount - selectedFeeBalance) }}
                                    </div>

                                    <div class="form-check mb-3">
                                        <input 
                                            class="form-check-input" 
                                            type="checkbox" 
                                            v-model="applyToOtherFees" 
                                            id="applyToOtherFees"
                                        >
                                        <label class="form-check-label fw-semibold" for="applyToOtherFees">
                                            Apply excess amount to other outstanding fees
                                        </label>
                                        <small class="form-text text-muted d-block">
                                            Distribute the excess payment to other unpaid fees automatically
                                        </small>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input 
                                            class="form-check-input" 
                                            type="checkbox" 
                                            v-model="acceptOverpayment" 
                                            id="acceptOverpayment"
                                        >
                                        <label class="form-check-label fw-semibold" for="acceptOverpayment">
                                            Accept overpayment and create credit balance
                                        </label>
                                        <small class="form-text text-muted d-block">
                                            Credit balance can be used for future fees or transferred to other students
                                        </small>
                                    </div>

                                    <div v-if="applyToOtherFees" class="other-fees-list mt-3">
                                        <h6 class="fw-semibold">Other Outstanding Fees:</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Fee Type</th>
                                                        <th>Academic Year</th>
                                                        <th>Term</th>
                                                        <th class="text-end">Balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="fee in otherOutstandingFees" :key="fee.id" class="fee-item">
                                                        <td>{{ formatFeeType(fee.fee_type) }}</td>
                                                        <td>{{ fee.academic_year }}</td>
                                                        <td>Term {{ fee.term }}</td>
                                                        <td class="text-end text-danger fw-bold">KSh {{ fee.balance?.toLocaleString() }}</td>
                                                    </tr>
                                                </tbody>
                                                <tfoot v-if="otherOutstandingFees.length > 0" class="table-light">
                                                    <tr>
                                                        <td colspan="3" class="fw-bold">Total Other Outstanding:</td>
                                                        <td class="text-end fw-bold text-danger">
                                                            KSh {{ totalOtherOutstandingBalance.toLocaleString() }}
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        <div v-if="otherOutstandingFees.length === 0" class="text-muted text-center py-2">
                                            No other outstanding fees found.
                                        </div>
                                    </div>

                                    <!-- Payment Distribution Summary -->
                                    <div v-if="applyToOtherFees || acceptOverpayment" class="payment-distribution-summary mt-3 p-3 bg-light rounded">
                                        <h6 class="fw-semibold mb-3">Payment Distribution Summary:</h6>
                                        
                                        <div class="distribution-breakdown">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Applied to selected fee:</span>
                                                <span class="fw-bold">KSh {{ Math.min(autoPayment?.amount, selectedFeeBalance).toLocaleString() }}</span>
                                            </div>
                                            
                                            <div v-if="applyToOtherFees && otherOutstandingFees.length > 0" class="d-flex justify-content-between mb-2">
                                                <span>Applied to other fees:</span>
                                                <span class="fw-bold text-success">
                                                    KSh {{ Math.min(autoPayment?.amount - selectedFeeBalance, totalOtherOutstandingBalance).toLocaleString() }}
                                                </span>
                                            </div>
                                            
                                            <div v-if="showCreditAmount" class="d-flex justify-content-between mb-2">
                                                <span>Credit balance created:</span>
                                                <span class="fw-bold text-info">
                                                    KSh {{ creditAmount.toLocaleString() }}
                                                </span>
                                            </div>
                                            
                                            <hr>
                                            <div class="d-flex justify-content-between fw-bold">
                                                <span>Total Payment:</span>
                                                <span>KSh {{ autoPayment?.amount?.toLocaleString() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Verification Form -->
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label class="form-label fw-semibold">Verification Notes (Optional)</label>
                                <textarea 
                                    class="form-control border" 
                                    v-model="paymentForm.notes" 
                                    rows="3" 
                                    placeholder="Additional verification notes or remarks..."
                                    :class="{ 'is-invalid': errors.notes }"
                                ></textarea>
                                <div v-if="errors.notes" class="invalid-feedback">
                                    {{ errors.notes }}
                                </div>
                            </div>

                            <!-- Payment Impact -->
                            <div class="alert" :class="paymentImpactClass">
                                <strong>{{ paymentImpactText }}</strong>
                                <div class="mt-1">{{ paymentImpactDescription }}</div>
                                <div v-if="showCreditAmount" class="mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    A credit balance of <strong>KSh {{ creditAmount.toLocaleString() }}</strong> will be created for future use.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button 
                        @click="confirmSinglePayment" 
                        class="btn btn-success" 
                        :disabled="!canConfirmPayment || confirmingPayment"
                    >
                        <span v-if="confirmingPayment" class="spinner-border spinner-border-sm me-2"></span>
                        <i class="fas fa-check-circle me-2"></i>
                        {{ confirmingPayment ? 'Verifying...' : 'Verify Payment' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Verification Modal -->
    <div class="modal fade" id="bulkVerificationModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-white border-bottom py-3">
                    <h5 class="modal-title text-dark fw-bold">
                        <i class="fas fa-check-double me-2 text-primary"></i>Bulk Payment Verification
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-white">
                    <!-- Payment Details -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border">
                                <div class="card-header bg-light py-2">
                                    <h6 class="mb-0 text-dark">
                                        <i class="fas fa-money-bill-wave me-2"></i>Payment Details
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-2">
                                            <strong>Reference:</strong> 
                                            <span class="badge bg-primary">{{ autoPayment?.reference_number }}</span>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong>Amount:</strong> 
                                            <span class="text-success fw-bold">KSh {{ autoPayment?.amount?.toLocaleString() }}</span>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong>Method:</strong> 
                                            <span class="badge bg-info text-capitalize">{{ autoPayment?.payment_method }}</span>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong>Date:</strong> {{ formatDate(autoPayment?.payment_date) }}
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong>Payer:</strong> {{ autoPayment?.payer_name || 'N/A' }}
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <strong>Phone:</strong> {{ autoPayment?.payer_phone || 'N/A' }}
                                        </div>
                                    </div>
                                    <div v-if="autoPayment?.narration" class="row mt-2">
                                        <div class="col-12">
                                            <strong>Narration:</strong> {{ autoPayment.narration }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Fees Summary -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white py-2">
                                    <h6 class="mb-0">
                                        <i class="fas fa-list me-2"></i>Selected Fees Summary
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 text-center">
                                            <div class="border rounded p-3 bg-light">
                                                <h4 class="text-primary mb-1">{{ selectedFeeIds.length }}</h4>
                                                <small class="text-muted">Selected Fees</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <div class="border rounded p-3 bg-light">
                                                <h4 class="text-danger mb-1">KSh {{ totalSelectedBalance.toLocaleString() }}</h4>
                                                <small class="text-muted">Total Balance</small>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-center">
                                            <div class="border rounded p-3 bg-light">
                                                <h4 class="text-success mb-1">KSh {{ autoPayment?.amount?.toLocaleString() }}</h4>
                                                <small class="text-muted">Payment Amount</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Distribution Options -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark py-2">
                                    <h6 class="mb-0">
                                        <i class="fas fa-cogs me-2"></i>Payment Distribution Options
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check card h-100 border-primary">
                                                <div class="card-body">
                                                    <input 
                                                        class="form-check-input" 
                                                        type="radio" 
                                                        v-model="distributionType" 
                                                        value="auto_distribute"
                                                        id="autoDistribute"
                                                    >
                                                    <label class="form-check-label fw-semibold" for="autoDistribute">
                                                        <i class="fas fa-robot me-2 text-primary"></i>Auto Distribute
                                                    </label>
                                                    <small class="form-text text-muted d-block mt-2">
                                                        Automatically distribute payment across all selected fees. 
                                                        Overdue fees will be prioritized first.
                                                    </small>
                                                    <div class="mt-2">
                                                        <small class="text-success">
                                                            <i class="fas fa-check me-1"></i>
                                                            Recommended for most cases
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check card h-100 border-info">
                                                <div class="card-body">
                                                    <input 
                                                        class="form-check-input" 
                                                        type="radio" 
                                                        v-model="distributionType" 
                                                        value="accept_overpayment"
                                                        id="acceptOverpaymentBulk"
                                                    >
                                                    <label class="form-check-label fw-semibold" for="acceptOverpaymentBulk">
                                                        <i class="fas fa-piggy-bank me-2 text-info"></i>Accept Overpayment
                                                    </label>
                                                    <small class="form-text text-muted d-block mt-2">
                                                        Apply payment to selected fees and accept any overpayment as credit balance.
                                                    </small>
                                                    <div class="mt-2">
                                                        <small class="text-info">
                                                            <i class="fas fa-info-circle me-1"></i>
                                                            Creates credit for future use
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Distribution Preview -->
                                    <div v-if="distributionType" class="distribution-preview mt-4">
                                        <h6 class="fw-semibold mb-3">Distribution Preview:</h6>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Fee Type</th>
                                                        <th>Academic Year</th>
                                                        <th>Term</th>
                                                        <th class="text-end">Current Balance</th>
                                                        <th class="text-end">Amount to Apply</th>
                                                        <th class="text-end">New Balance</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="fee in selectedFeesForPreview" :key="fee.id">
                                                        <td>{{ formatFeeType(fee.fee_type) }}</td>
                                                        <td>{{ fee.academic_year }}</td>
                                                        <td>Term {{ fee.term }}</td>
                                                        <td class="text-end text-danger">KSh {{ fee.balance?.toLocaleString() }}</td>
                                                        <td class="text-end text-success">
                                                            KSh {{ calculateAmountToApply(fee)?.toLocaleString() }}
                                                        </td>
                                                        <td class="text-end" :class="getNewBalanceClass(fee)">
                                                            KSh {{ calculateNewBalance(fee)?.toLocaleString() }}
                                                        </td>
                                                        <td>
                                                            <span class="badge" :class="getNewStatusBadge(fee)">
                                                                {{ getNewStatus(fee) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <td colspan="3" class="fw-bold">Totals:</td>
                                                        <td class="text-end fw-bold text-danger">
                                                            KSh {{ totalSelectedBalance.toLocaleString() }}
                                                        </td>
                                                        <td class="text-end fw-bold text-success">
                                                            KSh {{ Math.min(autoPayment?.amount, totalSelectedBalance).toLocaleString() }}
                                                        </td>
                                                        <td class="text-end fw-bold" :class="getTotalNewBalanceClass()">
                                                            KSh {{ calculateTotalNewBalance().toLocaleString() }}
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                    <tr v-if="distributionType === 'accept_overpayment' && autoPayment?.amount > totalSelectedBalance" class="table-info">
                                                        <td colspan="5" class="fw-bold text-info">Credit Balance Created:</td>
                                                        <td class="text-end fw-bold text-info">
                                                            KSh {{ (autoPayment?.amount - totalSelectedBalance).toLocaleString() }}
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Verification Form -->
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label class="form-label fw-semibold">Verification Notes (Optional)</label>
                                <textarea 
                                    class="form-control border" 
                                    v-model="bulkPaymentForm.notes" 
                                    rows="3" 
                                    placeholder="Additional verification notes or remarks..."
                                ></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                    <button 
                        @click="confirmBulkPayment" 
                        class="btn btn-success" 
                        :disabled="!canConfirmBulkPayment || confirmingPayment"
                    >
                        <span v-if="confirmingPayment" class="spinner-border spinner-border-sm me-2"></span>
                        <i class="fas fa-check-double me-2"></i>
                        {{ confirmingPayment ? 'Verifying...' : 'Verify Bulk Payment' }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-white border-bottom py-3">
                    <h5 class="modal-title text-dark fw-bold">
                        <i class="fas fa-receipt me-2 text-primary"></i>Payment Receipt
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-white">
                    <div class="text-center mb-4">
                        <h4 class="text-dark fw-bold">{{ school.name }}</h4>
                        <p class="text-muted mb-2">{{ school.address }}</p>
                        <p class="text-muted mb-3">Tel: {{ school.phone }} | Email: {{ school.email }}</p>
                        <h5 class="text-primary">FEE PAYMENT RECEIPT</h5>
                    </div>

                    <!-- Student Details -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td class="fw-semibold bg-light" width="30%">Receipt Number:</td>
                                    <td>{{ receiptData.receipt_number }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold bg-light">Student Name:</td>
                                    <td>{{ receiptData.student_name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold bg-light">Admission Number:</td>
                                    <td>{{ receiptData.admission_number }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold bg-light">Class:</td>
                                    <td>{{ receiptData.class }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold bg-light">Payment Method:</td>
                                    <td>{{ receiptData.payment_method }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold bg-light">Reference Number:</td>
                                    <td>{{ receiptData.reference_number }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold bg-light">Payment Date:</td>
                                    <td>{{ receiptData.payment_date }}</td>
                                </tr>
                                <tr v-if="receiptData.credit_balance > 0">
                                    <td class="fw-semibold bg-light text-success">Credit Balance:</td>
                                    <td class="text-success fw-bold">KSh {{ receiptData.credit_balance?.toLocaleString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Fee Breakdown -->
                    <h5 class="mb-3">Fee Breakdown</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Amount (KSh)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Previous Balances -->
                                <template v-if="receiptData.fee_breakdown?.breakdown?.filter(item => item.type === 'previous_balance').length">
                                    <tr class="table-secondary">
                                        <td colspan="2" class="fw-bold">PREVIOUS BALANCES</td>
                                    </tr>
                                    <tr v-for="(balance, index) in receiptData.fee_breakdown.breakdown.filter(item => item.type === 'previous_balance')" :key="index">
                                        <td>{{ balance.description }}</td>
                                        <td class="text-end">{{ formatCurrency(balance.amount) }}</td>
                                    </tr>
                                    <tr class="table-light">
                                        <td class="fw-bold">Total Previous Balance</td>
                                        <td class="text-end fw-bold">{{ formatCurrency(receiptData.fee_breakdown.summary.total_previous_balance) }}</td>
                                    </tr>
                                </template>

                                <!-- Current Term Fees -->
                                <template v-if="receiptData.fee_breakdown?.breakdown?.filter(item => item.type === 'current_fee').length">
                                    <tr class="table-secondary">
                                        <td colspan="2" class="fw-bold">CURRENT TERM FEES</td>
                                    </tr>
                                    <tr v-for="(fee, index) in receiptData.fee_breakdown.breakdown.filter(item => item.type === 'current_fee')" :key="index">
                                        <td>{{ fee.description }}</td>
                                        <td class="text-end">{{ formatCurrency(fee.amount) }}</td>
                                    </tr>
                                    <tr class="table-light">
                                        <td class="fw-bold">Total Current Term Fees</td>
                                        <td class="text-end fw-bold">{{ formatCurrency(receiptData.fee_breakdown.summary.total_current_fees) }}</td>
                                    </tr>
                                </template>

                                <!-- Previous Credits -->
                                <template v-if="receiptData.fee_breakdown?.breakdown?.filter(item => item.type === 'previous_credit').length">
                                    <tr class="table-secondary">
                                        <td colspan="2" class="fw-bold">PREVIOUS CREDITS</td>
                                    </tr>
                                    <tr v-for="(credit, index) in receiptData.fee_breakdown.breakdown.filter(item => item.type === 'previous_credit')" :key="index">
                                        <td>{{ credit.description }}</td>
                                        <td class="text-end text-success">- {{ formatCurrency(credit.amount) }}</td>
                                    </tr>
                                    <tr class="table-light">
                                        <td class="fw-bold">Total Previous Credit</td>
                                        <td class="text-end fw-bold text-success">- {{ formatCurrency(receiptData.fee_breakdown.summary.total_previous_credit) }}</td>
                                    </tr>
                                </template>

                                <!-- Summary -->
                                <tr class="table-secondary">
                                    <td colspan="2" class="fw-bold">SUMMARY</td>
                                </tr>
                                <tr>
                                    <td>Total Balance Before Payment</td>
                                    <td class="text-end">{{ formatCurrency(receiptData.fee_breakdown.summary.total_balance_before) }}</td>
                                </tr>
                                <tr class="table-success">
                                    <td class="fw-bold">Amount Paid</td>
                                    <td class="text-end fw-bold text-success">- {{ formatCurrency(receiptData.fee_breakdown.summary.amount_paid) }}</td>
                                </tr>
                                <tr v-if="receiptData.fee_breakdown.summary.new_balance < 0" class="table-info">
                                    <td class="fw-bold">CREDIT BALANCE</td>
                                    <td class="text-end fw-bold text-info">{{ formatCurrency(Math.abs(receiptData.fee_breakdown.summary.new_balance)) }}</td>
                                </tr>
                                <tr v-else class="table-warning">
                                    <td class="fw-bold">NEW BALANCE</td>
                                    <td class="text-end fw-bold text-danger">{{ formatCurrency(receiptData.fee_breakdown.summary.new_balance) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center mt-4 text-muted">
                        <small>This is a computer generated receipt. No signature required.</small>
                        <div>Generated on: {{ new Date().toLocaleDateString() }}</div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top py-3">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                    <button @click="downloadReceipt" class="btn btn-primary">
                        <i class="fas fa-download me-1"></i> Download PDF
                    </button>
                    <button @click="printReceipt" class="btn btn-success">
                        <i class="fas fa-print me-1"></i> Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, reactive, onMounted, watch } from 'vue';
import { Modal } from 'bootstrap';
import axios from 'axios';

// Reactive data
const loading = ref(false);
const confirmingPayment = ref(false);
const student = ref(null);
const autoPayment = ref(null);
const outstandingFees = ref([]);
const selectedFeeIds = ref([]);
const selectedFee = ref(null);
const errorMessage = ref('');
const successMessage = ref('');
const errors = ref({});
const verificationModal = ref(null);
const bulkVerificationModal = ref(null);
const receiptModal = ref(null);

// New payment options
const applyToOtherFees = ref(false);
const acceptOverpayment = ref(false);
const otherOutstandingFees = ref([]);
const distributionType = ref('auto_distribute');

const form = reactive({
    reference_number: '',
    admission_number: ''
});

const paymentForm = reactive({
    notes: ''
});

const bulkPaymentForm = reactive({
    notes: ''
});

const receiptData = reactive({
    receipt_number: '',
    student_name: '',
    admission_number: '',
    class: '',
    amount_paid: 0,
    payment_method: '',
    reference_number: '',
    payment_date: '',
    credit_balance: 0,
    fee_breakdown: {
        breakdown: [],
        summary: {
            total_previous_balance: 0,
            total_previous_credit: 0,
            total_current_fees: 0,
            total_balance_before: 0,
            amount_paid: 0,
            new_balance: 0
        }
    }
});

const school = {
    name: 'School Management System',
    address: '123 School Street, Nairobi',
    phone: '+254 700 000000',
    email: 'info@school.com'
};

// Computed properties
const allFeesSelected = computed(() => {
    return outstandingFees.value.length > 0 && selectedFeeIds.value.length === outstandingFees.value.length;
});

const totalSelectedBalance = computed(() => {
    return outstandingFees.value
        .filter(fee => selectedFeeIds.value.includes(fee.id))
        .reduce((total, fee) => total + (fee.balance || 0), 0);
});

const selectedFeesForPreview = computed(() => {
    return outstandingFees.value.filter(fee => selectedFeeIds.value.includes(fee.id));
});

const canConfirmBulkPayment = computed(() => {
    return selectedFeeIds.value.length > 0 && autoPayment.value && distributionType.value;
});

const totalOutstandingBalance = computed(() => {
    return outstandingFees.value.reduce((total, fee) => total + (fee.balance || 0), 0);
});

const selectedFeeBalance = computed(() => {
    if (!selectedFee.value) return 0;
    return selectedFee.value.balance || 0;
});

const selectedFeeType = computed(() => {
    if (!selectedFee.value) return '';
    return formatFeeType(selectedFee.value.fee_type);
});

const showPaymentOptions = computed(() => {
    return autoPayment.value && selectedFeeBalance.value > 0 && 
           autoPayment.value.amount > selectedFeeBalance.value;
});

const totalOtherOutstandingBalance = computed(() => {
    return otherOutstandingFees.value.reduce((total, fee) => total + (fee.balance || 0), 0);
});

const creditAmount = computed(() => {
    if (!showPaymentOptions.value) return 0;
    
    let excess = autoPayment.value.amount - selectedFeeBalance.value;
    
    if (applyToOtherFees.value) {
        excess -= Math.min(excess, totalOtherOutstandingBalance.value);
    }
    
    return Math.max(0, excess);
});

const showCreditAmount = computed(() => {
    return creditAmount.value > 0 && acceptOverpayment.value;
});

const paymentImpactClass = computed(() => {
    if (!autoPayment.value) return 'alert-light';
    
    if (showPaymentOptions.value) {
        if (showCreditAmount.value) return 'alert-info';
        if (applyToOtherFees.value) return 'alert-warning';
        return 'alert-warning';
    }
    
    if (autoPayment.value.amount === selectedFeeBalance.value) return 'alert-success';
    if (autoPayment.value.amount > selectedFeeBalance.value * 0.5) return 'alert-warning';
    return 'alert-info';
});

const paymentImpactText = computed(() => {
    if (!autoPayment.value) return 'No payment amount set';
    
    if (showPaymentOptions.value) {
        if (showCreditAmount.value) return 'Payment with Credit Creation';
        if (applyToOtherFees.value) return 'Distributed Payment';
        return 'Overpayment Detected';
    }
    
    if (autoPayment.value.amount === selectedFeeBalance.value) return 'Full Payment';
    if (autoPayment.value.amount > selectedFeeBalance.value * 0.5) return 'Substantial Payment';
    return 'Partial Payment';
});

const paymentImpactDescription = computed(() => {
    if (!autoPayment.value) return 'Set payment amount to see impact';
    
    if (showPaymentOptions.value) {
        const appliedToSelected = Math.min(autoPayment.value.amount, selectedFeeBalance.value);
        const appliedToOther = applyToOtherFees.value ? 
            Math.min(autoPayment.value.amount - selectedFeeBalance.value, totalOtherOutstandingBalance.value) : 0;
        
        let description = `KSh ${appliedToSelected.toLocaleString()} applied to selected fee`;
        
        if (appliedToOther > 0) {
            description += `, KSh ${appliedToOther.toLocaleString()} to other fees`;
        }
        
        if (showCreditAmount.value) {
            description += `, KSh ${creditAmount.value.toLocaleString()} as credit`;
        }
        
        return description;
    }
    
    const remaining = selectedFeeBalance.value - autoPayment.value.amount;
    if (remaining <= 0) return 'Fee will be fully paid';
    return `KSh ${remaining.toLocaleString()} remaining after payment`;
});

const canConfirmPayment = computed(() => {
    return selectedFee.value && autoPayment.value;
});

// Helper methods
const formatFeeType = (feeType) => {
    if (!feeType) return 'Fee';
    return feeType.charAt(0).toUpperCase() + feeType.slice(1).replace('_', ' ') + ' Fee';
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    try {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch (error) {
        return 'Invalid Date';
    }
};

const formatCurrency = (amount) => {
    if (amount === null || amount === undefined) return 'KSh 0.00';
    return 'KSh ' + (amount || 0).toLocaleString('en-KE', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const isOverdue = (dueDate) => {
    if (!dueDate) return false;
    try {
        return new Date(dueDate) < new Date();
    } catch (error) {
        return false;
    }
};

const getFeeStatusBadge = (status) => {
    const statusMap = {
        'paid': 'success',
        'partial': 'warning',
        'pending': 'secondary',
        'overdue': 'danger',
        'carried_over': 'info',
        'credit_carried': 'success',
        'credit': 'info'
    };
    return statusMap[status] || 'secondary';
};

// Action methods
const checkPayment = async () => {
    if (!form.reference_number.trim()) {
        errorMessage.value = 'Please enter a reference number';
        return;
    }
    
    if (!form.admission_number.trim()) {
        errorMessage.value = 'Please enter an admission number';
        return;
    }

    loading.value = true;
    errorMessage.value = '';
    successMessage.value = '';
    errors.value = {};
    autoPayment.value = null;
    selectedFeeIds.value = [];
    
    try {
        const response = await axios.post(route('admin.fees.payments.check'), form);
        
        if (response.data.success) {
            student.value = response.data.student;
            autoPayment.value = response.data.auto_payment;
            outstandingFees.value = response.data.outstanding_fees || [];
            selectedFeeIds.value = []; // Reset selection
            
            // Auto-select all fees if payment amount is sufficient
            if (autoPayment.value.amount >= response.data.total_outstanding_balance) {
                selectedFeeIds.value = outstandingFees.value.map(fee => fee.id);
            }
        } else {
            errorMessage.value = response.data.message;
            resetStudentData();
        }
    } catch (error) {
        const message = error.response?.data?.message || error.message;
        errorMessage.value = 'Error checking payment: ' + message;
        resetStudentData();
    } finally {
        loading.value = false;
    }
};

const toggleAllFees = () => {
    if (allFeesSelected.value) {
        selectedFeeIds.value = outstandingFees.value.map(fee => fee.id);
    } else {
        selectedFeeIds.value = [];
    }
};

const selectAllFees = () => {
    selectedFeeIds.value = outstandingFees.value.map(fee => fee.id);
};

const deselectAllFees = () => {
    selectedFeeIds.value = [];
};

const openVerificationModal = async (fee) => {
    selectedFee.value = fee;
    selectedFeeIds.value = [fee.id];
    
    // Reset payment options
    applyToOtherFees.value = false;
    acceptOverpayment.value = false;
    
    // Load other outstanding fees
    if (showPaymentOptions.value) {
        await loadOtherOutstandingFees();
        
        // Auto-select apply to other fees if there are other outstanding fees
        if (otherOutstandingFees.value.length > 0) {
            applyToOtherFees.value = true;
            
            // Auto-select accept overpayment if payment still exceeds total available
            const totalAvailable = selectedFeeBalance.value + totalOtherOutstandingBalance.value;
            if (autoPayment.value.amount > totalAvailable) {
                acceptOverpayment.value = true;
            }
        } else {
            // If no other fees, suggest accepting overpayment
            acceptOverpayment.value = true;
        }
    }
    
    verificationModal.value.show();
};

const loadOtherOutstandingFees = async () => {
    try {
        const response = await axios.get(route('admin.fees.payments.students.outstanding-fees', { student: student.value.id }));
        otherOutstandingFees.value = response.data.fees.filter(f => f.id !== selectedFee.value.id);
    } catch (error) {
        console.error('Error loading other fees:', error);
        otherOutstandingFees.value = [];
    }
};

const openBulkVerificationModal = () => {
    // Reset distribution type
    distributionType.value = 'auto_distribute';
    bulkPaymentForm.notes = '';
    bulkVerificationModal.value.show();
};

const confirmSinglePayment = async () => {
    if (!canConfirmPayment.value) return;

    confirmingPayment.value = true;
    errorMessage.value = '';
    errors.value = {};

    try {
        const response = await axios.post(route('admin.fees.payments.confirm.single'), {
            auto_payment_id: autoPayment.value.id,
            student_id: student.value.id,
            fee_id: selectedFee.value.id,
            apply_to_other_fees: applyToOtherFees.value,
            accept_overpayment: acceptOverpayment.value,
            notes: paymentForm.notes
        });
        
        if (response.data.success) {
            verificationModal.value.hide();
            successMessage.value = response.data.message;
            
            // Fetch detailed receipt data
            const receiptResponse = await axios.get(route('admin.fees.payments.receipt.data', { payment: response.data.payment_id }));
            
            if (receiptResponse.data.success) {
                // Prepare receipt data
                receiptData.receipt_number = 'RCPT-' + response.data.payment_id.toString().padStart(6, '0');
                receiptData.student_name = student.value.full_name;
                receiptData.admission_number = student.value.admission_number;
                receiptData.class = student.value.current_rank?.name;
                receiptData.amount_paid = autoPayment.value.amount;
                receiptData.payment_method = autoPayment.value.payment_method.toUpperCase();
                receiptData.reference_number = autoPayment.value.reference_number;
                receiptData.payment_date = formatDate(autoPayment.value.payment_date);
                receiptData.fee_breakdown = receiptResponse.data.fee_breakdown || {};
                receiptData.credit_balance = Math.abs(Math.min(0, receiptData.fee_breakdown.summary.new_balance));
                
                // Show receipt modal
                setTimeout(() => {
                    receiptModal.value.show();
                }, 500);
            }
            
            // Reset form
            resetForm();
            
        } else {
            errorMessage.value = response.data.message;
        }
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            const message = error.response?.data?.message || error.message;
            errorMessage.value = 'Error confirming payment: ' + message;
        }
    } finally {
        confirmingPayment.value = false;
    }
};

const confirmBulkPayment = async () => {
    if (!canConfirmBulkPayment.value) return;

    confirmingPayment.value = true;
    errorMessage.value = '';
    errors.value = {};

    try {
        const response = await axios.post(route('admin.fees.payments.confirm'), {
            auto_payment_id: autoPayment.value.id,
            student_id: student.value.id,
            fee_ids: selectedFeeIds.value,
            distribution_type: distributionType.value,
            notes: bulkPaymentForm.notes
        });
        
        if (response.data.success) {
            bulkVerificationModal.value.hide();
            successMessage.value = response.data.message;
            
            // Fetch detailed receipt data
            const receiptResponse = await axios.get(route('admin.fees.payments.receipt.data', { payment: response.data.payment_id }));
            
            if (receiptResponse.data.success) {
                // Prepare receipt data
                receiptData.receipt_number = 'RCPT-' + response.data.payment_id.toString().padStart(6, '0');
                receiptData.student_name = student.value.full_name;
                receiptData.admission_number = student.value.admission_number;
                receiptData.class = student.value.current_rank?.name;
                receiptData.amount_paid = autoPayment.value.amount;
                receiptData.payment_method = autoPayment.value.payment_method.toUpperCase();
                receiptData.reference_number = autoPayment.value.reference_number;
                receiptData.payment_date = formatDate(autoPayment.value.payment_date);
                receiptData.fee_breakdown = receiptResponse.data.fee_breakdown || {};
                receiptData.credit_balance = Math.abs(Math.min(0, receiptData.fee_breakdown.summary.new_balance));
                
                // Show receipt modal
                setTimeout(() => {
                    receiptModal.value.show();
                }, 500);
            }
            
            // Reset form
            resetForm();
            
        } else {
            errorMessage.value = response.data.message;
        }
    } catch (error) {
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            const message = error.response?.data?.message || error.message;
            errorMessage.value = 'Error confirming payment: ' + message;
        }
    } finally {
        confirmingPayment.value = false;
    }
};

// Helper methods for distribution preview
const calculateAmountToApply = (fee) => {
    if (distributionType.value === 'accept_overpayment') {
        return Math.min(autoPayment.value.amount, fee.balance);
    }
    
    // For auto distribute, we need to simulate the distribution
    const totalBalance = totalSelectedBalance.value;
    const paymentAmount = autoPayment.value.amount;
    
    if (paymentAmount >= totalBalance) {
        return fee.balance;
    }
    
    // Simple proportional distribution for preview
    const ratio = fee.balance / totalBalance;
    return Math.min(fee.balance, paymentAmount * ratio);
};

const calculateNewBalance = (fee) => {
    const amountToApply = calculateAmountToApply(fee);
    return fee.balance - amountToApply;
};

const calculateTotalNewBalance = () => {
    return selectedFeesForPreview.value.reduce((total, fee) => {
        return total + calculateNewBalance(fee);
    }, 0);
};

const getNewBalanceClass = (fee) => {
    const newBalance = calculateNewBalance(fee);
    if (newBalance < 0) return 'text-info';
    if (newBalance === 0) return 'text-success';
    return 'text-danger';
};

const getTotalNewBalanceClass = () => {
    const totalNewBalance = calculateTotalNewBalance();
    if (totalNewBalance < 0) return 'text-info';
    if (totalNewBalance === 0) return 'text-success';
    return 'text-danger';
};

const getNewStatus = (fee) => {
    const newBalance = calculateNewBalance(fee);
    if (newBalance < 0) return 'credit';
    if (newBalance === 0) return 'paid';
    if (newBalance > 0) return 'partial';
    return fee.status;
};

const getNewStatusBadge = (fee) => {
    const status = getNewStatus(fee);
    const statusMap = {
        'paid': 'bg-success',
        'partial': 'bg-warning',
        'credit': 'bg-info',
        'pending': 'bg-secondary'
    };
    return statusMap[status] || 'bg-secondary';
};

const downloadReceipt = async () => {
    try {
        const paymentId = receiptData.receipt_number.replace('RCPT-', '');
        const response = await axios.get(route('admin.fees.payments.receipt.download', { payment: paymentId }), {
            responseType: 'blob'
        });
        
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `${receiptData.receipt_number}.pdf`);
        document.body.appendChild(link);
        link.click();
        link.remove();
    } catch (error) {
        console.error('Error downloading receipt:', error);
        errorMessage.value = 'Error downloading receipt. Please try again.';
    }
};

const printReceipt = () => {
    const receiptContent = document.querySelector('#receiptModal .modal-body').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print Receipt</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                .text-center { text-align: center; }
                .text-end { text-align: right; }
                .fw-bold { font-weight: bold; }
                .table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                .table-bordered { border: 1px solid #ddd; }
                .table-bordered th, .table-bordered td { border: 1px solid #ddd; padding: 8px; }
                .table-dark { background: #333; color: white; }
                .table-secondary { background: #e9ecef; }
                .table-light { background: #f8f9fa; }
                .table-success { background: #d1e7dd; }
                .table-warning { background: #fff3cd; }
                .table-info { background: #cff4fc; }
                .text-success { color: #198754; }
                .text-danger { color: #dc3545; }
                .text-info { color: #0dcaf0; }
                .text-muted { color: #6c757d; }
            </style>
        </head>
        <body>
            ${receiptContent}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
};

const resetStudentData = () => {
    student.value = null;
    autoPayment.value = null;
    outstandingFees.value = [];
    selectedFeeIds.value = [];
    selectedFee.value = null;
    otherOutstandingFees.value = [];
    applyToOtherFees.value = false;
    acceptOverpayment.value = false;
    distributionType.value = 'auto_distribute';
};

const resetForm = () => {
    form.reference_number = '';
    form.admission_number = '';
    resetStudentData();
    paymentForm.notes = '';
    bulkPaymentForm.notes = '';
    errorMessage.value = '';
};

watch([applyToOtherFees, acceptOverpayment], () => {
    if (applyToOtherFees.value && !acceptOverpayment.value) {
        const totalAvailable = selectedFeeBalance.value + totalOtherOutstandingBalance.value;
        if (autoPayment.value && autoPayment.value.amount > totalAvailable) {
            acceptOverpayment.value = true;
        }
    }
});

onMounted(() => {
    verificationModal.value = new Modal(document.getElementById('verificationModal'));
    bulkVerificationModal.value = new Modal(document.getElementById('bulkVerificationModal'));
    receiptModal.value = new Modal(document.getElementById('receiptModal'));
});
</script>

<style scoped>
.card {
    border-radius: 8px;
}

.card-header {
    border-radius: 8px 8px 0 0 !important;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn {
    border-radius: 6px;
    font-weight: 500;
}

.table th {
    font-weight: 600;
    font-size: 0.8rem;
    text-transform: uppercase;
}

.badge {
    font-size: 0.75em;
    font-weight: 500;
}

.modal-content {
    border-radius: 12px;
}

.alert {
    border: 1px solid;
    border-radius: 6px;
}

.payment-distribution-summary {
    border-left: 4px solid #0d6efd;
}

.distribution-breakdown {
    font-size: 0.9rem;
}

.other-fees-list {
    max-height: 200px;
    overflow-y: auto;
}

.fee-checkbox:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.distribution-preview {
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 15px;
    background: #f8f9fa;
}
</style>