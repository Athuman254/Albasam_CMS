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
              <div>
                <button @click="printAllBalances" class="btn btn-success me-2" :disabled="!hasStudents">
                  <i class="fas fa-print me-1"></i> Print All Statements
                </button>
                <Link :href="route('admin.fees.index')" class="btn btn-secondary">
                  <i class="fas fa-arrow-left me-1"></i> Back to Fees
                </Link>
              </div>
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
                        <i class="fas fa-search"></i>
                      </button>
                    </div>
                    <small class="text-muted">Enter admission number or student name</small>
                  </div>
                </div>
                
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label">Filter by Class</label>
                    <select v-model="selectedClass" class="form-control" @change="loadClassStudents" :disabled="loading">
                      <option value="">All Classes</option>
                      <option v-for="rank in ranks" :key="rank.id" :value="rank.id">
                        {{ rank.name }}
                      </option>
                    </select>
                  </div>
                </div>
                
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label">Academic Year</label>
                    <select v-model="academicYear" class="form-control" @change="loadFeeData" :disabled="loading">
                      <option value="">All Years</option>
                      <option v-for="year in academicYears" :key="year" :value="year">
                        {{ year }}
                      </option>
                    </select>
                  </div>
                </div>
                
                <div class="col-md-2">
                  <div class="form-group">
                    <label class="form-label">Term</label>
                    <select v-model="term" class="form-control" @change="loadFeeData" :disabled="loading">
                      <option value="">All Terms</option>
                      <option value="1">Term 1</option>
                      <option value="2">Term 2</option>
                      <option value="3">Term 3</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Debug Information (Remove in production) -->
              <div class="row mb-3" v-if="debugInfo && false"> <!-- Set to false to hide debug info -->
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

              <!-- Quick Stats -->
              <div class="row mb-4" v-if="feeStats && !selectedStudent">
                <div class="col-md-3">
                  <div class="card border-primary">
                    <div class="card-body text-center py-3">
                      <h6 class="card-title text-primary">Total Students</h6>
                      <h3 class="mb-0 text-primary">{{ feeStats.total_students || 0 }}</h3>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card border-success">
                    <div class="card-body text-center py-3">
                      <h6 class="card-title text-success">Total Fees Due</h6>
                      <h3 class="mb-0 text-success">{{ formatCurrency(feeStats.total_fees_due || 0) }}</h3>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card border-warning">
                    <div class="card-body text-center py-3">
                      <h6 class="card-title text-warning">Total Paid</h6>
                      <h3 class="mb-0 text-warning">{{ formatCurrency(feeStats.total_paid || 0) }}</h3>
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="card border-danger">
                    <div class="card-body text-center py-3">
                      <h6 class="card-title text-danger">Total Balance</h6>
                      <h3 class="mb-0 text-danger">{{ formatCurrency(feeStats.total_balance || 0) }}</h3>
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
                        <div class="col-md-4">
                          <p class="mb-1"><strong>Fee Records:</strong> {{ (selectedStudent.fees || []).length }}</p>
                        </div>
                        <div class="col-md-4">
                          <p class="mb-1"><strong>Payment Records:</strong> {{ (selectedStudent.payments || []).length }}</p>
                        </div>
                      </div>
                      <div class="row mt-3">
                        <div class="col-12">
                          <button @click="printStudentStatement(selectedStudent)" class="btn btn-primary btn-sm me-2" :disabled="loading">
                            <i class="fas fa-print me-1"></i> Print Statement
                          </button>
                          <button @click="viewStudentDetails(selectedStudent)" class="btn btn-info btn-sm me-2" :disabled="loading">
                            <i class="fas fa-eye me-1"></i> View Details
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

              <!-- Class Students Table -->
              <div class="row" v-if="classStudents.length > 0 && !selectedStudent">
                <div class="col-12">
                  <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                      <h6 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Students in {{ selectedClassName }} 
                        <span class="badge bg-primary ms-2">{{ classStudents.length }} students</span>
                      </h6>
                      <div>
                        <button @click="printAllClassStatements" class="btn btn-success btn-sm me-2" :disabled="loading">
                          <i class="fas fa-print me-1"></i> Print All Statements
                        </button>
                        <button @click="exportClassBalances" class="btn btn-info btn-sm" :disabled="loading">
                          <i class="fas fa-download me-1"></i> Export
                        </button>
                      </div>
                    </div>
                    <div class="card-body p-0">
                      <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                          <thead class="table-light">
                            <tr>
                              <th>Admission No.</th>
                              <th>Student Name</th>
                              <th>Class</th>
                              <th>Total Fees</th>
                              <th>Paid Amount</th>
                              <th>Balance</th>
                              <th>Status</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr v-for="student in classStudents" :key="student.id">
                              <td class="fw-bold">{{ student.admission_number }}</td>
                              <td>{{ student.full_name }}</td>
                              <td>{{ student.current_rank?.name || 'N/A' }}</td>
                              <td class="text-primary">{{ formatCurrency(student.total_fees || 0) }}</td>
                              <td class="text-success">{{ formatCurrency(student.total_paid || 0) }}</td>
                              <td :class="getBalanceClass(student.balance || 0)">
                                {{ formatCurrency(student.balance || 0) }}
                              </td>
                              <td>
                                <span :class="getStatusBadgeClass(student.balance || 0, student.total_fees || 0)" class="badge">
                                  {{ getFeeStatus(student.balance || 0, student.total_fees || 0) }}
                                </span>
                              </td>
                              <td>
                                <button @click="viewStudentDetails(student)" class="btn btn-sm btn-outline-primary me-1" :disabled="loading">
                                  <i class="fas fa-eye"></i>
                                </button>
                                <button @click="printStudentStatement(student)" class="btn btn-sm btn-outline-success" :disabled="loading">
                                  <i class="fas fa-print"></i>
                                </button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                      
                      <!-- Class Summary -->
                      <div class="card-footer bg-light">
                        <div class="row">
                          <div class="col-md-3">
                            <strong>Class Total Fees:</strong> {{ formatCurrency(classSummary.total_fees) }}
                          </div>
                          <div class="col-md-3">
                            <strong>Class Total Paid:</strong> {{ formatCurrency(classSummary.total_paid) }}
                          </div>
                          <div class="col-md-3">
                            <strong>Class Total Balance:</strong> 
                            <span :class="getBalanceClass(classSummary.total_balance)">
                              {{ formatCurrency(classSummary.total_balance) }}
                            </span>
                          </div>
                          <div class="col-md-3">
                            <strong>Students with Balance:</strong> 
                            <span class="badge bg-warning">{{ classSummary.students_with_balance }}</span>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- No Students Message -->
              <div class="row" v-else-if="selectedClass && !loading && !selectedStudent">
                <div class="col-12">
                  <div class="card">
                    <div class="card-body text-center py-5">
                      <i class="fas fa-users fa-3x text-muted mb-3"></i>
                      <h5 class="text-muted">No students found in this class</h5>
                      <p class="text-muted">Select a different class or check if students are assigned to this class.</p>
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
                        Search for a student by admission number or name, or select a class to view all students' fee balances and print statements.
                      </p>
                      <div class="row justify-content-center">
                        <div class="col-md-8">
                          <div class="row text-start">
                            <div class="col-md-6">
                              <h6><i class="fas fa-search text-primary me-2"></i>Quick Search</h6>
                              <p class="small text-muted">Enter admission number or student name in the search box above</p>
                            </div>
                            <div class="col-md-6">
                              <h6><i class="fas fa-users text-success me-2"></i>Class View</h6>
                              <p class="small text-muted">Select a class to view all students and print bulk statements</p>
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
                      </tr>
                      <tr v-if="!detailedStudent.fees || detailedStudent.fees.length === 0">
                        <td colspan="8" class="text-center text-muted py-3">
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
            <button @click="printStudentStatement(detailedStudent)" class="btn btn-primary" :disabled="loading">
              <i class="fas fa-print me-1"></i> Print Statement
            </button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
      await nextTick(); // Wait for DOM update
      const modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
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