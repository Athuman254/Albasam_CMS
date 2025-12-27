<template>
  <DefaultLayout>
    <Head title="Student Fee Balances & Statements" />
    
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h4 class="card-title mb-0">
                <i class="fas fa-file-invoice-dollar me-2"></i>Student Fee Balances & Statements
              </h4>
            </div>
            <div class="card-body">
              <!-- Search and Filter Section -->
              <div class="row mb-4">
                <div class="col-md-4">
                  <div class="form-group">
                    <label class="form-label">Search Student</label>
                    <div class="input-group">
                      <input 
                        type="text" 
                        class="form-control" 
                        v-model="searchQuery"
                        placeholder="Search by name or admission number..."
                        @keyup.enter="searchStudent"
                        :disabled="loading"
                      >
                      <button @click="searchStudent" class="btn btn-outline-primary" type="button" :disabled="loading">
                        <i class="fas fa-search">search</i>
                      </button>
                    </div>
                    <small class="text-muted">Enter admission number or student name</small>
                  </div>
                </div>
              </div>

              <!-- Debug Information (Remove in production) -->
              <div class="row mb-3" v-if="debugInfo && false"> 
                <div class="col-12">
                  <div class="card border-warning">
                    <div class="card-header bg-warning text-white">
                      <h6 class="mb-0">Debug Info</h6>
                    </div>
                    <div class="card-body">
                      <pre>{{ JSON.stringify(debugInfo, null, 2) }}</pre>
                    </div>
                  </div>
                </div>
              </div>


              <!-- Individual Student Search Result -->
              <div class="row mb-4" v-if="selectedStudent">
                <div class="col-12">
                  <div class="card border-info">
                    <div class="card-header bg-white text-info border-bottom">
                      <h6 class="mb-0">
                        <i class="fas fa-user-graduate me-2"></i>
                        Student Details: {{ selectedStudent.full_name }} ({{ selectedStudent.admission_number }})
                      </h6>
                    </div>
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-3">
                          <p class="mb-1"><strong>Class:</strong> {{ selectedStudent.current_rank?.name || 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                          <p class="mb-1"><strong>Total Fees:</strong> {{ formatCurrency(selectedStudent.total_fees || 0) }}</p>
                        </div>
                        <div class="col-md-3">
                          <p class="mb-1"><strong>Total Paid:</strong> {{ formatCurrency(selectedStudent.total_paid || 0) }}</p>
                        </div>
                        <div class="col-md-3">
                          <p class="mb-1">
                            <strong>Balance:</strong> 
                            <span :class="getBalanceClass(selectedStudent.balance || 0)">
                              {{ formatCurrency(selectedStudent.balance || 0) }}
                            </span>
                          </p>
                        </div>
                      </div>
                      <div class="row mt-3">
                        <div class="col-md-4">
                          <p class="mb-1"><strong>Status:</strong> 
                            <span :class="getStatusBadgeClass(selectedStudent.balance || 0, selectedStudent.total_fees || 0)" class="badge">
                              {{ getFeeStatus(selectedStudent.balance || 0, selectedStudent.total_fees || 0) }}
                            </span>
                          </p>
                        </div>
                      </div>
                      <div class="row mt-3">
                        <div class="col-12">
                          <button @click="openPaymentModal(selectedStudent)" class="btn btn-success btn-sm me-2" :disabled="loading">
                            <i class="fas fa-mobile-alt me-1"></i> M-Pesa Push
                          </button>

                          <button @click="openCashPaymentModal(selectedStudent)" class="btn btn-warning btn-sm me-2" :disabled="loading">
                            <i class="fas fa-money-bill-wave me-1"></i> Record Cash
                          </button>

                          <button @click="printStudentStatement(selectedStudent)" class="btn btn-primary btn-sm me-2" :disabled="loading">
                            <i class="fas fa-print me-1"></i> Print Statement
                          </button>

                          <button @click="openIndividualFeeModal(selectedStudent)" class="btn btn-info btn-sm me-2" :disabled="loading">
                            <i class="fas fa-plus-circle me-1"></i> Add Individual Fee
                          </button>

                          <button @click="clearSelectedStudent" class="btn btn-outline-secondary btn-sm" :disabled="loading">
                            <i class="fas fa-times me-1"></i> Clear
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- No Search Results -->
              <div class="row" v-else-if="searchQuery && !selectedStudent && !loading">
                <div class="col-12">
                  <div class="card">
                    <div class="card-body text-center py-5">
                      <i class="fas fa-search fa-3x text-muted mb-3"></i>
                      <h5 class="text-muted">No student found</h5>
                      <p class="text-muted">No student found with admission number or name "{{ searchQuery }}"</p>
                      <button @click="clearSelectedStudent" class="btn btn-outline-primary">
                        <i class="fas fa-times me-1"></i> Clear Search
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Loading State -->
              <div class="row" v-if="loading">
                <div class="col-12">
                  <div class="card">
                    <div class="card-body text-center py-5">
                      <div class="spinner-border text-primary mb-3" role="status"></div>
                      <p>{{ loadingMessage }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Welcome/Instructions -->
              <div class="row" v-if="!selectedClass && !selectedStudent && !loading && classStudents.length === 0">
                <div class="col-12">
                  <div class="card">
                    <div class="card-body text-center py-5">
                      <i class="fas fa-file-invoice-dollar fa-3x text-primary mb-3"></i>
                      <h4 class="text-primary">Student Fee Statements</h4>
                      <p class="text-muted mb-4">
                        Search for a student by admission number, to view all students' fee balances and print statements.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Student Details Modal -->
    <div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-user-graduate me-2"></i>
              Student Fee Details: {{ detailedStudent?.full_name }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" v-if="detailedStudent">
            <div class="row mb-4">
              <div class="col-md-6">
                <p><strong>Student:</strong> {{ detailedStudent.full_name }}</p>
                <p><strong>Admission No:</strong> {{ detailedStudent.admission_number }}</p>
                <p><strong>Class:</strong> {{ detailedStudent.current_rank?.name || 'N/A' }}</p>
              </div>
              <div class="col-md-6">
                <p><strong>Total Fees:</strong> {{ formatCurrency(detailedStudent.total_fees || 0) }}</p>
                <p><strong>Total Paid:</strong> {{ formatCurrency(detailedStudent.total_paid || 0) }}</p>
                <p><strong>Balance:</strong> 
                  <span :class="getBalanceClass(detailedStudent.balance || 0)">
                    {{ formatCurrency(detailedStudent.balance || 0) }}
                  </span>
                </p>
              </div>
            </div>

            <!-- Fee Breakdown -->
            <div class="card">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-receipt me-2"></i>Fee Breakdown</h6>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-sm mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Fee Type</th>
                        <th>Academic Year</th>
                        <th>Term</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Balance</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th class="text-center">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="fee in detailedStudent.fees || []" :key="fee.id">
                        <td>{{ formatFeeType(fee.fee_type) }}</td>
                        <td>{{ fee.academic_year }}</td>
                        <td>Term {{ fee.term }}</td>
                        <td class="text-end">{{ formatCurrency(fee.amount) }}</td>
                        <td class="text-end">{{ formatCurrency(fee.paid_amount) }}</td>
                        <td class="text-end" :class="getBalanceClass(fee.balance)">
                          {{ formatCurrency(fee.balance) }}
                        </td>
                        <td>
                          <span :class="getFeeStatusClass(fee.status)" class="badge">
                            {{ fee.status }}
                          </span>
                        </td>
                        <td>{{ formatDate(fee.due_date) }}</td>
                        <td class="text-center">
                          <button 
                            v-if="fee.paid_amount === 0" 
                            @click="deleteFee(fee.id)" 
                            class="btn btn-sm btn-danger" 
                            :disabled="loading"
                            title="Delete this fee"
                          >
                            <i class="fas fa-trash"></i>
                          </button>
                          <span v-else class="text-muted small">-</span>
                        </td>
                      </tr>
                      <tr v-if="!detailedStudent.fees || detailedStudent.fees.length === 0">
                        <td colspan="9" class="text-center text-muted py-3">
                          <i class="fas fa-info-circle me-2"></i>No fee records found
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <!-- Payment History -->
            <div class="card mt-3">
              <div class="card-header bg-light">
                <h6 class="mb-0"><i class="fas fa-history me-2"></i>Payment History</h6>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-sm mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Date</th>
                        <th class="text-end">Amount</th>
                        <th>Method</th>
                        <th>Reference</th>
                        <th>Status</th>
                        <th>Verified By</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="payment in detailedStudent.payments || []" :key="payment.id">
                        <td>{{ formatDate(payment.payment_date) }}</td>
                        <td class="text-end text-success fw-bold">{{ formatCurrency(payment.amount) }}</td>
                        <td>{{ formatPaymentMethod(payment.payment_method) }}</td>
                        <td>{{ payment.reference_number || 'N/A' }}</td>
                        <td>
                          <span :class="getPaymentStatusClass(payment.status)" class="badge">
                            {{ payment.status }}
                          </span>
                        </td>
                        <td>{{ payment.verified_by || 'System' }}</td>
                      </tr>
                      <tr v-if="!detailedStudent.payments || detailedStudent.payments.length === 0">
                        <td colspan="6" class="text-center text-muted py-3">
                          <i class="fas fa-info-circle me-2"></i>No payment history found
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button @click="openPaymentModal(detailedStudent)" class="btn btn-success text-white" :disabled="loading">
              <i class="fas fa-mobile-alt me-1"></i> Initiate M-Pesa Payment
            </button>
            <button @click="openCashPaymentModal(detailedStudent)" class="btn btn-warning text-white" :disabled="loading">
              <i class="fas fa-money-bill-wave me-1"></i> Record Cash Payment
            </button>
            <button @click="openIndividualFeeModal(detailedStudent)" class="btn btn-info text-white" :disabled="loading">
              <i class="fas fa-plus-circle me-1"></i> Add Individual Fee
            </button>
            <button @click="printStudentStatement(detailedStudent)" class="btn btn-primary" :disabled="loading">
              <i class="fas fa-print me-1"></i> Print Statement
            </button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>

    <!-- M-Pesa Payment Modal (Admin) -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title">
              <i class="fas fa-mobile-alt me-2"></i>
              Initiate M-Pesa Payment
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="alert alert-info">
              Using Paybill: <strong>{{ paybillNumber || 'Loading...' }}</strong>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Student</label>
              <input type="text" class="form-control" :value="detailedStudent?.full_name" disabled>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Phone Number</label>
              <input type="text" v-model="paymentForm.phone" class="form-control" placeholder="0712345678">
              <small class="text-muted">Enter the phone number to receive the STK Push.</small>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Amount (KSh)</label>
              <input type="number" v-model="paymentForm.amount" class="form-control">
            </div>

            <div v-if="paymentMessage" :class="{'alert-success': paymentSuccess, 'alert-danger': !paymentSuccess}" class="alert mt-3">
              {{ paymentMessage }}
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" @click="initiatePayment" class="btn btn-success text-white" :disabled="processing">
              <span v-if="processing" class="spinner-border spinner-border-sm me-1"></span>
              {{ processing ? 'Processing...' : 'Send STK Push' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Cash Payment Modal -->
    <div class="modal fade" id="cashPaymentModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-warning text-white">
            <h5 class="modal-title">
              <i class="fas fa-money-bill-wave me-2"></i>
              Record Cash Payment
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Student</label>
              <input type="text" class="form-control" :value="detailedStudent?.full_name" disabled>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Amount (KSh)</label>
              <input type="number" v-model="cashPaymentForm.amount" class="form-control" placeholder="0.00">
            </div>

            <div class="mb-3">
              <label class="form-label">Payment Date</label>
              <input type="date" v-model="cashPaymentForm.date" class="form-control">
            </div>

            <div class="mb-3">
              <label class="form-label">Notes</label>
              <textarea v-model="cashPaymentForm.notes" class="form-control" rows="2" placeholder="e.g. Receipt NO: 1234"></textarea>
            </div>

            <div v-if="cashPaymentMessage" :class="{'alert-success': cashPaymentSuccess, 'alert-danger': !cashPaymentSuccess}" class="alert mt-3">
              {{ cashPaymentMessage }}
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" @click="submitCashPayment" class="btn btn-warning text-white" :disabled="processing">
              <span v-if="processing" class="spinner-border spinner-border-sm me-1"></span>
              {{ processing ? 'Recording...' : 'Record Payment' }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <!-- Individual Fee Modal -->
    <div class="modal fade" id="individualFeeModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-info text-white">
            <h5 class="modal-title">
              <i class="fas fa-plus-circle me-2"></i>
              Add Individual Fee
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Student</label>
              <input type="text" class="form-control" :value="detailedStudent?.full_name" disabled>
            </div>

            <div class="row">
              <div class="col-6 mb-3">
                <label class="form-label">Academic Year</label>
                <select v-model="individualFeeForm.academic_year" class="form-control">
                  <option v-for="year in academic_years" :key="year.id" :value="year.name">{{ year.name }}</option>
                </select>
              </div>
              <div class="col-6 mb-3">
                <label class="form-label">Term</label>
                <select v-model="individualFeeForm.term" class="form-control">
                  <option value="1">Term 1</option>
                  <option value="2">Term 2</option>
                  <option value="3">Term 3</option>
                </select>
              </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Fee Type</label>
                <select v-model="individualFeeForm.fee_type" class="form-control">
                  <option value="tuition">Tuition Fee</option>
                  <option value="activity">Activity Fee</option>
                  <option value="exam">Examination Fee</option>
                  <option value="library">Library Fee</option>
                  <option value="sports">Sports Fee</option>
                  <option value="transport">Transport Fee</option>
                  <option value="hostel">Hostel Fee</option>
                  <option value="other">Other Fees</option>
                </select>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Amount (KSh) <span class="text-danger">*</span></label>
              <input type="number" v-model="individualFeeForm.amount" class="form-control" placeholder="0.00">
            </div>

            <div class="mb-3">
              <label class="form-label">Due Date <span class="text-danger">*</span></label>
              <input type="date" v-model="individualFeeForm.due_date" class="form-control">
            </div>

            <div class="mb-3">
              <label class="form-label">Description (Optional)</label>
              <textarea v-model="individualFeeForm.description" class="form-control" rows="2" placeholder="e.g. Uniform charges, etc."></textarea>
            </div>

            <div v-if="individualFeeMessage" :class="{'alert-success': individualFeeSuccess, 'alert-danger': !individualFeeSuccess}" class="alert mt-3">
              {{ individualFeeMessage }}
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" @click="submitIndividualFee" class="btn btn-info text-white" :disabled="processing">
              <span v-if="processing" class="spinner-border spinner-border-sm me-1"></span>
              {{ processing ? 'Adding...' : 'Add Fee' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, reactive, computed, onMounted, nextTick } from 'vue';
import axios from 'axios';

const props = defineProps({
  ranks: Array,
  academic_years: Array,
  initial_stats: Object,
});

// Reactive data
const searchQuery = ref('');
const selectedClass = ref('');
const academicYear = ref('');
const term = ref('');
const selectedStudent = ref(null);
const detailedStudent = ref(null);
const classStudents = ref([]);
const feeStats = ref(props.initial_stats || {});
const loading = ref(false);
const loadingMessage = ref('Loading student fee data...');
const debugInfo = ref(null);

// Payment State
const paymentForm = reactive({
  phone: '',
  amount: ''
});
const cashPaymentForm = reactive({
  amount: '',
  date: new Date().toISOString().split('T')[0],
  notes: ''
});
const processing = ref(false);
const paymentMessage = ref('');
const paymentSuccess = ref(false);
const cashPaymentMessage = ref('');
const cashPaymentSuccess = ref(false);
const individualFeeMessage = ref('');
const individualFeeSuccess = ref(false);
const paybillNumber = ref('Loading...'); // Could fetch from config

const individualFeeForm = reactive({
  fee_type: 'other',
  amount: '',
  academic_year: '',
  term: '',
  due_date: new Date().toISOString().split('T')[0],
  description: ''
});

const openIndividualFeeModal = (student = null) => {
  if (student && student.id) {
    detailedStudent.value = student;
  }
  
  if (!detailedStudent.value) {
    alert('No student selected');
    return;
  }

  // Set defaults from current filters or sensible values
  individualFeeForm.academic_year = academicYear.value || new Date().getFullYear().toString();
  individualFeeForm.term = term.value || '1';
  individualFeeForm.amount = '';
  individualFeeForm.fee_type = 'other';
  individualFeeForm.description = '';
  individualFeeForm.due_date = new Date().toISOString().split('T')[0];
  
  individualFeeMessage.value = '';
  individualFeeSuccess.value = false;
  
  const modalEl = document.getElementById('individualFeeModal');
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  modal.show();
};

const openPaymentModal = (student = null) => {
  // If student is an event (from @click without args), don't use it
  if (student && student.id) {
    detailedStudent.value = student;
  }
  
  if (!detailedStudent.value) {
    alert('No student selected');
    return;
  }

  paymentForm.phone = detailedStudent.value?.phone || '';
  paymentForm.amount = '';
  paymentMessage.value = '';
  paymentSuccess.value = false;
  
  const modalEl = document.getElementById('paymentModal');
  const paymentModal = bootstrap.Modal.getOrCreateInstance(modalEl);
  paymentModal.show();
};

const openCashPaymentModal = (student = null) => {
  // If student is an event, don't use it
  if (student && student.id) {
    detailedStudent.value = student;
  }

  if (!detailedStudent.value) {
    alert('No student selected');
    return;
  }

  cashPaymentForm.amount = '';
  cashPaymentForm.date = new Date().toISOString().split('T')[0];
  cashPaymentForm.notes = '';
  cashPaymentMessage.value = '';
  cashPaymentSuccess.value = false;
  
  const modalEl = document.getElementById('cashPaymentModal');
  const cashModal = bootstrap.Modal.getOrCreateInstance(modalEl);
  cashModal.show();
};

const initiatePayment = async () => {
  if (!paymentForm.phone || !paymentForm.amount) {
    alert('Please enter phone and amount');
    return;
  }
  
  processing.value = true;
  paymentMessage.value = '';
  
  try {
    const response = await axios.post(route('admin.fees.payments.mpesa.initiate-push'), {
      phone_number: paymentForm.phone,
      amount: paymentForm.amount,
      student_id: detailedStudent.value.id,
      account_reference: detailedStudent.value.admission_number
    });

    if (response.data.success) {
      paymentSuccess.value = true;
      paymentMessage.value = 'STK Push sent successfully!';
      setTimeout(() => {
        const modalEl = document.getElementById('paymentModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
      }, 3000);
    } else {
      paymentSuccess.value = false;
      paymentMessage.value = response.data.message;
    }
  } catch (error) {
    console.error(error);
    paymentSuccess.value = false;
    paymentMessage.value = error.response?.data?.message || 'Failed to initiate payment.';
  } finally {
    processing.value = false;
  }
};

const submitCashPayment = async () => {
  if (!cashPaymentForm.amount || !cashPaymentForm.date) {
    alert('Please enter amount and date');
    return;
  }
  
  processing.value = true;
  cashPaymentMessage.value = '';
  
  try {
    const response = await axios.post(route('admin.fees.payments.record-cash'), {
      amount: cashPaymentForm.amount,
      payment_date: cashPaymentForm.date,
      notes: cashPaymentForm.notes,
      student_id: detailedStudent.value.id
    });

    if (response.data.success) {
      cashPaymentSuccess.value = true;
      cashPaymentMessage.value = 'Cash payment recorded successfully!';
      
      // Reload student data silently to show the new payment without re-opening modal
      const detailedResponse = await axios.post(route('admin.fees.student-details'), {
        student_id: detailedStudent.value.id,
        academic_year: academicYear.value,
        term: term.value
      });
      
      if (detailedResponse.data.success) {
        detailedStudent.value = detailedResponse.data.student;
        // Also update selectedStudent if matches
        if (selectedStudent.value && selectedStudent.value.id === detailedStudent.value.id) {
            selectedStudent.value = { ...selectedStudent.value, ...detailedResponse.data.student };
        }
      }
      
      setTimeout(() => {
        const modalEl = document.getElementById('cashPaymentModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
      }, 2000);
    } else {
      cashPaymentSuccess.value = false;
      cashPaymentMessage.value = response.data.message;
    }
  } catch (error) {
    console.error(error);
    cashPaymentSuccess.value = false;
    cashPaymentMessage.value = error.response?.data?.message || 'Failed to record cash payment.';
  } finally {
    processing.value = false;
  }
};

const submitIndividualFee = async () => {
  if (!individualFeeForm.amount || !individualFeeForm.academic_year || !individualFeeForm.term || !individualFeeForm.due_date) {
    alert('Please fill in all required fields (Amount, Year, Term, Due Date)');
    return;
  }
  
  processing.value = true;
  individualFeeMessage.value = '';
  
  try {
    const response = await axios.post(route('admin.fees.store'), {
      rank_id: detailedStudent.value.current_rank?.id,
      student_id: detailedStudent.value.id,
      fee_type: individualFeeForm.fee_type,
      amount: individualFeeForm.amount,
      academic_year: individualFeeForm.academic_year,
      term: individualFeeForm.term,
      due_date: individualFeeForm.due_date,
      description: individualFeeForm.description
    });

    if (response.data.success || response.status === 200) {
      individualFeeSuccess.value = true;
      individualFeeMessage.value = response.data.message || 'Individual fee added successfully!';
      
      // Reload student data
      await searchStudent();
      
      setTimeout(() => {
        const modalEl = document.getElementById('individualFeeModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
      }, 2000);
    }
  } catch (error) {
    console.error(error);
    individualFeeSuccess.value = false;
    individualFeeMessage.value = error.response?.data?.message || 'Failed to add individual fee.';
  } finally {
    processing.value = false;
  }
};

// Computed properties
const selectedClassName = computed(() => {
  if (!selectedClass.value) return '';
  const rank = props.ranks.find(r => r.id == selectedClass.value);
  return rank ? rank.name : '';
});

const hasStudents = computed(() => {
  return classStudents.value.length > 0;
});

const classSummary = computed(() => {
  const total_fees = classStudents.value.reduce((sum, student) => sum + (student.total_fees || 0), 0);
  const total_paid = classStudents.value.reduce((sum, student) => sum + (student.total_paid || 0), 0);
  const total_balance = classStudents.value.reduce((sum, student) => sum + (student.balance || 0), 0);
  const students_with_balance = classStudents.value.filter(student => (student.balance || 0) > 0).length;
  
  return {
    total_fees,
    total_paid,
    total_balance,
    students_with_balance
  };
});

// Methods
const formatCurrency = (amount) => {
  if (!amount || isNaN(amount)) return 'Ksh 0.00';
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount);
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  try {
    return new Date(dateString).toLocaleDateString('en-KE', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  } catch (e) {
    return 'Invalid Date';
  }
};

const formatFeeType = (feeType) => {
  const feeTypes = {
    'tuition': 'Tuition Fee',
    'activity': 'Activity Fee',
    'exam': 'Examination Fee',
    'library': 'Library Fee',
    'sports': 'Sports Fee',
    'transport': 'Transport Fee',
    'hostel': 'Hostel Fee',
    'other': 'Other Fees'
  };
  return feeTypes[feeType] || feeType.charAt(0).toUpperCase() + feeType.slice(1);
};

const formatPaymentMethod = (method) => {
  const methods = {
    'mpesa': 'M-Pesa',
    'bank': 'Bank Transfer',
    'cash': 'Cash',
    'card': 'Card Payment'
  };
  return methods[method] || method.charAt(0).toUpperCase() + method.slice(1);
};

const getBalanceClass = (balance) => {
  if (balance === 0) return 'text-success fw-bold';
  if (balance > 0) return 'text-danger fw-bold';
  return 'text-success'; // Overpaid
};

const getFeeStatus = (balance, totalFees) => {
  if (balance === 0) return 'Paid';
  if (balance === totalFees) return 'Pending';
  if (balance > 0) return 'Partial';
  return 'Overpaid';
};

const getStatusBadgeClass = (balance, totalFees) => {
  if (balance === 0) return 'bg-success';
  if (balance === totalFees) return 'bg-danger';
  if (balance > 0) return 'bg-warning';
  return 'bg-info';
};

const getFeeStatusClass = (status) => {
  const statusClasses = {
    'paid': 'bg-success',
    'pending': 'bg-secondary',
    'partial': 'bg-warning',
    'overdue': 'bg-danger',
    'cancelled': 'bg-dark'
  };
  return statusClasses[status] || 'bg-secondary';
};

const getPaymentStatusClass = (status) => {
  const statusClasses = {
    'completed': 'bg-success',
    'pending': 'bg-warning',
    'failed': 'bg-danger',
    'reversed': 'bg-dark',
    'verified': 'bg-info'
  };
  return statusClasses[status] || 'bg-secondary';
};

const searchStudent = async () => {
  if (!searchQuery.value.trim()) {
    alert('Please enter a student name or admission number');
    return;
  }
  
  loading.value = true;
  loadingMessage.value = 'Searching for student...';
  
  debugInfo.value = {
    searchQuery: searchQuery.value,
    academicYear: academicYear.value,
    term: term.value,
    loading: true
  };
  
  try {
    console.log('Searching for student:', searchQuery.value);
    
    const response = await axios.post(route('admin.fees.search-student-balance'), {
      query: searchQuery.value.trim(),
      academic_year: academicYear.value,
      term: term.value
    });
    
    console.log('Search response:', response.data);
    
    if (response.data.success && response.data.student) {
      selectedStudent.value = response.data.student;
      classStudents.value = [response.data.student];
      
      // Load detailed data for the modal
      loadingMessage.value = 'Loading detailed fee information...';
      const detailedResponse = await axios.post(route('admin.fees.student-details'), {
        student_id: response.data.student.id,
        academic_year: academicYear.value,
        term: term.value
      });
      
      if (detailedResponse.data.success) {
        selectedStudent.value = {
          ...selectedStudent.value,
          ...detailedResponse.data.student
        };
      }
      
      debugInfo.value.studentFound = true;
      debugInfo.value.studentData = selectedStudent.value;
    } else {
      alert('Student not found or has no fee records');
      selectedStudent.value = null;
      classStudents.value = [];
      debugInfo.value.studentFound = false;
    }
  } catch (error) {
    console.error('Search error:', error);
    if (error.response) {
      console.error('Error response:', error.response.data);
      alert('Error searching for student: ' + (error.response.data.message || 'Please try again'));
    } else if (error.request) {
      console.error('Error request:', error.request);
      alert('Network error. Please check your connection and try again.');
    } else {
      alert('Error searching for student. Please try again.');
    }
    debugInfo.value.error = error.message;
  } finally {
    loading.value = false;
    loadingMessage.value = 'Loading student fee data...';
  }
};

const loadClassStudents = async () => {
  if (!selectedClass.value) {
    classStudents.value = [];
    selectedStudent.value = null;
    return;
  }
  
  loading.value = true;
  loadingMessage.value = `Loading students in ${selectedClassName.value}...`;
  
  try {
    const response = await axios.post(route('admin.fees.class-balances'), {
      rank_id: selectedClass.value,
      academic_year: academicYear.value,
      term: term.value
    });
    
    if (response.data.success) {
      classStudents.value = response.data.students;
      feeStats.value = response.data.stats || {};
      selectedStudent.value = null; // Clear individual student when loading class
    }
  } catch (error) {
    console.error('Error loading class students:', error);
    alert('Error loading class fee data: ' + (error.response?.data?.message || 'Please try again'));
  } finally {
    loading.value = false;
    loadingMessage.value = 'Loading student fee data...';
  }
};

const loadFeeData = () => {
  if (selectedClass.value) {
    loadClassStudents();
  } else if (selectedStudent.value) {
    // Reload individual student data if filters change
    searchStudent();
  }
};

const viewStudentDetails = async (student) => {
  loading.value = true;
  loadingMessage.value = 'Loading student details...';
  
  try {
    const response = await axios.post(route('admin.fees.student-details'), {
      student_id: student.id,
      academic_year: academicYear.value,
      term: term.value
    });
    
    if (response.data.success) {
      detailedStudent.value = response.data.student;
      await nextTick();
      const modalEl = document.getElementById('studentDetailsModal');
      const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
    }
  } catch (error) {
    console.error('Error loading student details:', error);
    alert('Error loading student details: ' + (error.response?.data?.message || 'Please try again'));
  } finally {
    loading.value = false;
    loadingMessage.value = 'Loading student fee data...';
  }
};

const clearSelectedStudent = () => {
  selectedStudent.value = null;
  searchQuery.value = '';
  classStudents.value = [];
};

const printStudentStatement = (student) => {
  if (!student) return;
  
  const url = route('admin.fees.print-statement', {
    studentId: student.id,
    academic_year: academicYear.value,
    term: term.value,
    print: true
  });
  
  // Open in new window for printing
  const printWindow = window.open(url, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
  
  if (printWindow) {
    printWindow.focus();
  }
};

const printAllClassStatements = async () => {
  if (!selectedClass.value || classStudents.value.length === 0) {
    alert('Please select a class with students first');
    return;
  }
  
  if (!confirm(`This will print ${classStudents.value.length} student statements. Continue?`)) {
    return;
  }
  
  loading.value = true;
  loadingMessage.value = `Printing ${classStudents.value.length} student statements...`;
  
  try {
    // Print each student statement individually
    for (let i = 0; i < classStudents.value.length; i++) {
      const student = classStudents.value[i];
      loadingMessage.value = `Printing statement ${i + 1} of ${classStudents.value.length}: ${student.full_name}`;
      
      await printIndividualStudentStatement(student);
      
      // Small delay between prints to avoid overwhelming the browser
      if (i < classStudents.value.length - 1) {
        await new Promise(resolve => setTimeout(resolve, 1500));
      }
    }
    
    alert(`Successfully printed ${classStudents.value.length} student statements.`);
  } catch (error) {
    console.error('Error printing class statements:', error);
    alert('Error printing some statements. Please check the print queue.');
  } finally {
    loading.value = false;
    loadingMessage.value = 'Loading student fee data...';
  }
};

const deleteFee = async (feeId) => {
  if (!confirm('Are you sure you want to delete this fee? This action cannot be undone.')) {
    return;
  }
  
  loading.value = true;
  loadingMessage.value = 'Deleting fee...';
  
  try {
    const response = await axios.delete(route('admin.fees.destroy', feeId));
    
    if (response.data.success) {
      alert('Fee deleted successfully!');
      
      // Reload student data to reflect the deletion
      if (selectedStudent.value) {
        await searchStudent();
      }
    } else {
      alert(response.data.message || 'Failed to delete fee.');
    }
  } catch (error) {
    console.error('Error deleting fee:', error);
    alert(error.response?.data?.message || 'Error deleting fee. Please try again.');
  } finally {
    loading.value = false;
    loadingMessage.value = 'Loading student fee data...';
  }
};

const printIndividualStudentStatement = (student) => {
  return new Promise((resolve) => {
    const url = route('admin.fees.print-statement', {
      studentId: student.id,
      academic_year: academicYear.value,
      term: term.value,
      print: true
    });
    
    const printWindow = window.open(url, '_blank', 'width=1200,height=800,scrollbars=yes');
    
    let resolved = false;
    
    const resolvePromise = () => {
      if (!resolved) {
        resolved = true;
        resolve();
      }
    };
    
    printWindow.onload = function() {
      printWindow.print();
      
      // Wait a bit before closing and resolving
      setTimeout(() => {
        if (!printWindow.closed) {
          printWindow.close();
        }
        resolvePromise();
      }, 1000);
    };
    
    // Fallback in case onload doesn't fire
    setTimeout(() => {
      if (!printWindow.closed) {
        printWindow.close();
      }
      resolvePromise();
    }, 5000);
  });
};

const printAllBalances = () => {
  if (selectedClass.value && classStudents.value.length > 0) {
    printAllClassStatements();
  } else if (selectedStudent.value) {
    printStudentStatement(selectedStudent.value);
  } else {
    alert('Please select a class or search for a student first');
  }
};

const exportClassBalances = () => {
  if (!selectedClass.value) {
    alert('Please select a class first');
    return;
  }
  
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = route('admin.fees.export-balances');
  form.target = '_blank';
  
  const rankIdInput = document.createElement('input');
  rankIdInput.type = 'hidden';
  rankIdInput.name = 'rank_id';
  rankIdInput.value = selectedClass.value;
  form.appendChild(rankIdInput);
  
  const academicYearInput = document.createElement('input');
  academicYearInput.type = 'hidden';
  academicYearInput.name = 'academic_year';
  academicYearInput.value = academicYear.value;
  form.appendChild(academicYearInput);
  
  const termInput = document.createElement('input');
  termInput.type = 'hidden';
  termInput.name = 'term';
  termInput.value = term.value;
  form.appendChild(termInput);
  
  // Add CSRF token
  const tokenInput = document.createElement('input');
  tokenInput.type = 'hidden';
  tokenInput.name = '_token';
  tokenInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
  form.appendChild(tokenInput);
  
  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
};

// Initialize
onMounted(() => {
  // Set current academic year and term as default
  const currentYear = new Date().getFullYear();
  academicYear.value = `${currentYear}`;
  
  // Calculate current term based on current month
  const currentMonth = new Date().getMonth() + 1;
  if (currentMonth >= 1 && currentMonth <= 4) term.value = '1';
  else if (currentMonth >= 5 && currentMonth <= 8) term.value = '2';
  else term.value = '3';
  
  // Initialize Bootstrap modal if needed
  if (typeof bootstrap !== 'undefined') {
    window.bootstrap = bootstrap;
  }
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

.table th {
  font-weight: 600;
  background-color: #f8f9fa;
}

.badge {
  font-size: 0.75em;
}

.btn-sm {
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
}

.text-success {
  color: #198754 !important;
}

.text-danger {
  color: #dc3545 !important;
}

.text-warning {
  color: #ffc107 !important;
}

.text-primary {
  color: #0d6efd !important;
}

.border-primary { border-color: #0d6efd !important; }
.border-success { border-color: #198754 !important; }
.border-warning { border-color: #ffc107 !important; }
.border-danger { border-color: #dc3545 !important; }
.border-info { border-color: #0dcaf0 !important; }

.fw-bold {
  font-weight: 600;
}

.spinner-border {
  width: 2rem;
  height: 2rem;
}

.table-responsive {
  max-height: 600px;
}

/* Modal enhancements */
.modal-xl {
  max-width: 1200px;
}

/* Loading state improvements */
.btn:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

/* Responsive improvements */
@media (max-width: 768px) {
  .card-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .card-header > div {
    margin-top: 10px;
    width: 100%;
  }
  
  .table-responsive {
    font-size: 0.875rem;
  }
}
</style>