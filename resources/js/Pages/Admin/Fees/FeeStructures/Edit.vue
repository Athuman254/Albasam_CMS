<template>
  <DefaultLayout>
    <Head title="Edit Fee Structure" />
    
    <!-- Success/Error Messages -->
    <div v-if="$page.props.flash && $page.props.flash.success" class="alert alert-success alert-dismissible fade show m-3" role="alert">
      <i class="fas fa-check-circle me-2"></i>
      {{ $page.props.flash.success }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div v-if="$page.props.flash && $page.props.flash.error" class="alert alert-danger alert-dismissible fade show m-3" role="alert">
      <i class="fas fa-exclamation-circle me-2"></i>
      {{ $page.props.flash.error }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h4 class="card-title mb-0">Edit Fee Structure</h4>
              <Link 
                :href="route('admin.fee-structures.index')" 
                class="btn btn-outline-secondary"
              >
                <i class="fas fa-arrow-left me-1"></i> Back to List
              </Link>
            </div>
            <div class="card-body">
              <!-- Warning Message for Generated Invoices -->
              <div class="alert alert-warning mb-4" v-if="fee_structure.invoices_count > 0">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Warning:</strong> This fee structure has {{ fee_structure.invoices_count }} generated invoices. 
                Changing any fee amount will automatically update existing student fee records and recalculate balances.
              </div>

              <form @submit.prevent="submit">
                <div class="row">
                  <!-- Basic Information -->
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                      <select 
                        v-model="form.class_id" 
                        id="class_id"
                        class="form-select"
                        :class="{ 'is-invalid': form.errors.class_id }"
                      >
                        <option value="">Select Class</option>
                        <option 
                          v-for="classItem in classes" 
                          :key="classItem.id" 
                          :value="classItem.id"
                        >
                          {{ classItem.name }}
                        </option>
                      </select>
                      <div v-if="form.errors.class_id" class="invalid-feedback">
                        {{ form.errors.class_id }}
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="academic_year" class="form-label">Academic Year <span class="text-danger">*</span></label>
                      <select 
                        v-model="form.academic_year" 
                        id="academic_year"
                        class="form-select"
                        :class="{ 'is-invalid': form.errors.academic_year }"
                      >
                        <option value="">Select Academic Year</option>
                        <option 
                          v-for="year in academic_years" 
                          :key="year.id" 
                          :value="year.name"
                        >
                          {{ year.name }}
                        </option>
                      </select>
                      <div v-if="form.errors.academic_year" class="invalid-feedback">
                        {{ form.errors.academic_year }}
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="term" class="form-label">Term <span class="text-danger">*</span></label>
                      <select 
                        v-model="form.term" 
                        id="term"
                        class="form-select"
                        :class="{ 'is-invalid': form.errors.term }"
                      >
                        <option value="">Select Term</option>
                        <option value="1">Term 1</option>
                        <option value="2">Term 2</option>
                        <option value="3">Term 3</option>
                      </select>
                      <div v-if="form.errors.term" class="invalid-feedback">
                        {{ form.errors.term }}
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="due_date" class="form-label">Due Date <span class="text-danger">*</span></label>
                      <input 
                        type="date" 
                        v-model="form.due_date"
                        id="due_date"
                        class="form-control"
                        :class="{ 'is-invalid': form.errors.due_date }"
                      >
                      <div v-if="form.errors.due_date" class="invalid-feedback">
                        {{ form.errors.due_date }}
                      </div>
                      <div v-if="fee_structure.invoices_count > 0" class="form-text text-warning">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        Changing due date will update {{ fee_structure.invoices_count }} student records
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="status" class="form-label">Status</label>
                      <div class="form-check form-switch mt-2">
                        <input 
                          class="form-check-input" 
                          type="checkbox" 
                          v-model="form.is_active"
                          id="status"
                        >
                        <label class="form-check-label" for="status">
                          {{ form.is_active ? 'Active' : 'Inactive' }}
                        </label>
                      </div>
                      <div v-if="!form.is_active && fee_structure.invoices_count > 0" class="form-text text-warning">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        Deactivating will prevent new fee generation but won't affect existing invoices
                      </div>
                    </div>
                  </div>
                  
                  <div class="col-12">
                    <div class="mb-3">
                      <label for="description" class="form-label">Description</label>
                      <textarea 
                        v-model="form.description"
                        id="description"
                        class="form-control"
                        :class="{ 'is-invalid': form.errors.description }"
                        rows="3"
                        placeholder="Optional description..."
                      ></textarea>
                      <div v-if="form.errors.description" class="invalid-feedback">
                        {{ form.errors.description }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Tuition Fee Section -->
                <div class="row mt-4">
                  <div class="col-12">
                    <div class="card">
                      <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Tuition Fee</h6>
                      </div>
                      <div class="card-body">
                        <div class="row">
                          <div class="col-md-6">
                            <div class="mb-3">
                              <label for="amount" class="form-label">Tuition Fee Amount (KES) <span class="text-danger">*</span></label>
                              <input 
                                type="number" 
                                v-model="form.amount"
                                id="amount"
                                class="form-control"
                                :class="{ 'is-invalid': form.errors.amount }"
                                placeholder="0.00"
                                step="0.01"
                                min="0"
                              >
                              <div v-if="form.errors.amount" class="invalid-feedback">
                                {{ form.errors.amount }}
                              </div>
                              <div v-if="fee_structure.invoices_count > 0" class="form-text text-warning">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                Changing this amount will update {{ fee_structure.invoices_count }} student tuition fee records
                              </div>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="mb-3">
                              <label class="form-label">Current Impact</label>
                              <div class="p-3 bg-light rounded">
                                <div class="d-flex justify-content-between">
                                  <span>Students Affected:</span>
                                  <strong>{{ fee_structure.invoices_count }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mt-1">
                                  <span>Total Impact:</span>
                                  <strong class="text-primary">Ksh {{ formatCurrency(form.amount * fee_structure.invoices_count) }}</strong>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Additional Fees Section -->
                <div class="row mt-4">
                  <div class="col-12">
                    <div class="card">
                      <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Additional Fees</h6>
                        <button 
                          type="button" 
                          class="btn btn-outline-primary btn-sm"
                          @click="openAddFeeModal"
                        >
                          <i class="fas fa-plus me-1"></i> Add Fee
                        </button>
                      </div>
                      <div class="card-body">
                        <div class="alert alert-info mb-3" v-if="fee_structure.invoices_count > 0">
                          <i class="fas fa-info-circle me-2"></i>
                          <strong>Note:</strong> Changes to additional fees will automatically sync to existing student records.
                        </div>

                        <div v-if="form.additional_fees.length === 0" class="text-center py-4 text-muted">
                          <i class="fas fa-money-bill-wave fa-2x mb-3"></i>
                          <p>No additional fees added. Click "Add Fee" to include additional charges.</p>
                        </div>

                        <div v-else class="table-responsive">
                          <table class="table table-bordered">
                            <thead class="table-light">
                              <tr>
                                <th width="25%">Fee Name</th>
                                <th width="20%">Amount (KES)</th>
                                <th width="15%">Fee Type</th>
                                <th width="30%">Description</th>
                                <th width="10%" class="text-center">Actions</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr v-for="(fee, index) in form.additional_fees" :key="index">
                                <td>{{ fee.name }}</td>
                                <td>Ksh {{ formatCurrency(fee.amount) }}</td>
                                <td>
                                  <span class="badge bg-light text-dark text-capitalize">
                                    {{ fee.fee_type }}
                                  </span>
                                </td>
                                <td>{{ fee.description || '-' }}</td>
                                <td class="text-center">
                                  <button 
                                    type="button"
                                    class="btn btn-sm btn-outline-primary me-1"
                                    @click="editAdditionalFee(index)"
                                    title="Edit Fee"
                                  >
                                    <i class="fas fa-edit"></i>
                                  </button>
                                  <button 
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    @click="removeAdditionalFee(index)"
                                    title="Remove Fee"
                                  >
                                    <i class="fas fa-trash"></i>
                                  </button>
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </div>

                        <!-- Additional Fees Summary -->
                        <div v-if="form.additional_fees.length > 0" class="mt-3 p-3 bg-light rounded">
                          <div class="row">
                            <div class="col-md-6">
                              <strong>Total Additional Fees:</strong> Ksh {{ formatCurrency(additionalFeesTotal) }}
                            </div>
                            <div class="col-md-6">
                              <strong>Students Affected:</strong> {{ fee_structure.invoices_count }}
                            </div>
                          </div>
                          <div class="row mt-2">
                            <div class="col-12">
                              <strong>Total Impact:</strong> 
                              <span class="text-success fw-bold">
                                Ksh {{ formatCurrency(additionalFeesTotal * fee_structure.invoices_count) }}
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Total Summary -->
                <div class="row mt-4" v-if="form.amount > 0 || form.additional_fees.length > 0">
                  <div class="col-12">
                    <div class="card">
                      <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Fee Structure Summary</h6>
                      </div>
                      <div class="card-body">
                        <div class="row">
                          <div class="col-md-4">
                            <p class="mb-1"><strong>Tuition Fee:</strong></p>
                            <h5>Ksh {{ formatCurrency(form.amount) }}</h5>
                          </div>
                          <div class="col-md-4">
                            <p class="mb-1"><strong>Additional Fees:</strong></p>
                            <h5>Ksh {{ formatCurrency(additionalFeesTotal) }}</h5>
                          </div>
                          <div class="col-md-4">
                            <p class="mb-1"><strong>Total per Student:</strong></p>
                            <h4 class="text-primary">Ksh {{ formatCurrency(totalPerStudent) }}</h4>
                          </div>
                        </div>
                        <div class="row mt-3" v-if="fee_structure.invoices_count > 0">
                          <div class="col-12">
                            <div class="alert alert-info">
                              <i class="fas fa-users me-2"></i>
                              <strong>Total Impact:</strong> 
                              Ksh {{ formatCurrency(totalPerStudent * fee_structure.invoices_count) }} 
                              across {{ fee_structure.invoices_count }} students
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mt-4">
                  <button 
                    type="button" 
                    @click="deleteStructure"
                    class="btn btn-outline-danger"
                    :disabled="form.processing"
                  >
                    <i class="fas fa-trash me-1"></i> Delete
                  </button>
                  
                  <div>
                    <Link 
                      :href="route('admin.fee-structures.index')" 
                      class="btn btn-outline-secondary me-2"
                      :disabled="form.processing"
                    >
                      Cancel
                    </Link>
                    <button 
                      type="submit" 
                      class="btn btn-primary"
                      :disabled="form.processing"
                    >
                      <i class="fas fa-save me-1"></i> 
                      {{ form.processing ? 'Updating...' : 'Update Fee Structure' }}
                      <span v-if="fee_structure.invoices_count > 0" class="badge bg-warning ms-2">
                        {{ fee_structure.invoices_count }} students
                      </span>
                    </button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Additional Fee Modal -->
    <div class="modal fade" :class="{ 'show d-block': showAddFeeModal }" v-if="showAddFeeModal" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              {{ editingFeeIndex !== null ? 'Edit Additional Fee' : 'Add Additional Fee' }}
            </h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label for="fee_type" class="form-label">Fee Type <span class="text-danger">*</span></label>
              <select 
                v-model="currentFee.fee_type"
                id="fee_type"
                class="form-select"
                @change="updateFeeName"
              >
                <option value="activity">Activity Fee</option>
                <option value="exam">Examination Fee</option>
                <option value="library">Library Fee</option>
                <option value="sports">Sports Fee</option>
                <option value="transport">Transport Fee</option>
                <option value="hostel">Hostel Fee</option>
                <option value="other">Other Fee</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="fee_name" class="form-label">Fee Name <span class="text-danger">*</span></label>
              <input 
                type="text"
                v-model="currentFee.name"
                id="fee_name"
                class="form-control"
                placeholder="e.g., Activity Fee"
              >
            </div>

            <div class="mb-3">
              <label for="fee_amount" class="form-label">Amount (KES) <span class="text-danger">*</span></label>
              <input 
                type="number"
                v-model="currentFee.amount"
                id="fee_amount"
                class="form-control"
                placeholder="0.00"
                step="0.01"
                min="0"
              >
            </div>

            <div class="mb-3">
              <label for="fee_description" class="form-label">Description</label>
              <textarea 
                v-model="currentFee.description"
                id="fee_description"
                class="form-control"
                rows="3"
                placeholder="Optional description..."
              ></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" @click="closeModal">
              Cancel
            </button>
            <button type="button" class="btn btn-primary" @click="saveAdditionalFee">
              {{ editingFeeIndex !== null ? 'Update Fee' : 'Add Fee' }}
            </button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show" v-if="showAddFeeModal"></div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, reactive, onMounted } from 'vue';

// Helper function to get fee type from name - MOVED TO TOP
const getFeeTypeFromName = (name) => {
  if (!name) return 'other';
  const nameLower = name.toLowerCase();
  if (nameLower.includes('activity')) return 'activity';
  if (nameLower.includes('exam') || nameLower.includes('examination')) return 'exam';
  if (nameLower.includes('library')) return 'library';
  if (nameLower.includes('sports')) return 'sports';
  if (nameLower.includes('transport')) return 'transport';
  if (nameLower.includes('hostel')) return 'hostel';
  return 'other';
};

const props = defineProps({
  fee_structure: Object,
  classes: Array,
  academic_years: Array
});

// Reactive state
const showAddFeeModal = ref(false);
const editingFeeIndex = ref(null);

// Current fee being edited/added
const currentFee = reactive({
  name: '',
  amount: '',
  fee_type: 'activity',
  description: ''
});

// Format currency function - MOVED BEFORE form initialization
const formatCurrency = (amount) => {
  if (!amount || isNaN(amount)) return '0.00';
  return parseFloat(amount).toFixed(2);
};

// Form setup - NOW AFTER helper functions
const form = useForm({
  class_id: props.fee_structure.rank_id,
  academic_year: props.fee_structure.academic_year,
  term: props.fee_structure.term.toString(),
  amount: props.fee_structure.amount,
  due_date: props.fee_structure.due_date,
  description: props.fee_structure.description || '',
  is_active: props.fee_structure.is_active,
  additional_fees: props.fee_structure.additional_fees ? 
    props.fee_structure.additional_fees.map(fee => ({
      ...fee,
      fee_type: getFeeTypeFromName(fee.name)
    })) : [],
});

// Computed properties
const additionalFeesTotal = computed(() => {
  return form.additional_fees.reduce((total, fee) => total + (parseFloat(fee.amount) || 0), 0);
});

const totalPerStudent = computed(() => {
  return (parseFloat(form.amount) || 0) + additionalFeesTotal.value;
});

// Modal methods
const openAddFeeModal = () => {
  editingFeeIndex.value = null;
  currentFee.name = '';
  currentFee.amount = '';
  currentFee.fee_type = 'activity';
  currentFee.description = '';
  showAddFeeModal.value = true;
};

const editAdditionalFee = (index) => {
  const fee = form.additional_fees[index];
  editingFeeIndex.value = index;
  currentFee.name = fee.name;
  currentFee.amount = fee.amount;
  currentFee.fee_type = fee.fee_type;
  currentFee.description = fee.description || '';
  showAddFeeModal.value = true;
};

const closeModal = () => {
  showAddFeeModal.value = false;
  editingFeeIndex.value = null;
};

const updateFeeName = () => {
  const feeNameMap = {
    activity: 'Activity Fee',
    exam: 'Examination Fee',
    library: 'Library Fee',
    sports: 'Sports Fee',
    transport: 'Transport Fee',
    hostel: 'Hostel Fee',
    other: 'Other Fee'
  };
  
  currentFee.name = feeNameMap[currentFee.fee_type] || 'Other Fee';
};

const saveAdditionalFee = () => {
  // Validate required fields
  if (!currentFee.name.trim()) {
    alert('Please enter a fee name');
    return;
  }

  if (!currentFee.amount || parseFloat(currentFee.amount) <= 0) {
    alert('Please enter a valid amount');
    return;
  }

  if (editingFeeIndex.value !== null) {
    // Update existing fee
    form.additional_fees[editingFeeIndex.value] = { ...currentFee };
  } else {
    // Add new fee
    form.additional_fees.push({ ...currentFee });
  }

  closeModal();
};

const removeAdditionalFee = (index) => {
  if (confirm('Are you sure you want to remove this additional fee?')) {
    form.additional_fees.splice(index, 1);
  }
};

const submit = () => {
  // Show confirmation for changes affecting invoices
  if (props.fee_structure.invoices_count > 0) {
    const confirmed = confirm(
      `This fee structure has ${props.fee_structure.invoices_count} generated invoices.\n\n` +
      `Changing fee amounts will update all related student fee records and recalculate balances.\n\n` +
      `Total impact: Ksh ${formatCurrency(totalPerStudent.value * props.fee_structure.invoices_count)}\n\n` +
      `Are you sure you want to proceed?`
    );
    
    if (!confirmed) {
      return;
    }
  }

  form.put(route('admin.fee-structures.update', { fee_structure: props.fee_structure.id }), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
    },
    onError: (errors) => {
      console.error('Update failed:', errors);
    }
  });
};

const deleteStructure = () => {
  if (props.fee_structure.invoices_count > 0) {
    alert('Cannot delete fee structure that has generated invoices. Please delete the generated fees first.');
    return;
  }

  if (confirm('Are you sure you want to delete this fee structure? This action cannot be undone.')) {
    form.delete(route('admin.fee-structures.destroy', { fee_structure: props.fee_structure.id }), {
      preserveScroll: true,
      onSuccess: () => {
        // Success handled by Inertia
      },
      onError: (errors) => {
        console.error('Delete failed:', errors);
      },
    });
  }
};

onMounted(() => {
  document.addEventListener('click', (event) => {
    if (showAddFeeModal.value && event.target.classList.contains('modal-backdrop')) {
      closeModal();
    }
  });
});
</script>

<style scoped>
.alert {
  margin: 0 0 1rem 0;
  border-radius: 0.5rem;
}

.card-header {
  border-bottom: 1px solid #dee2e6;
  background-color: #f8f9fa;
}

.form-text.text-warning {
  font-weight: 500;
}

.badge.bg-warning {
  color: #000;
}

.table th {
  font-weight: 600;
  font-size: 0.875rem;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.modal {
  background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
  border: none;
  border-radius: 0.5rem;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-header {
  border-bottom: 1px solid #dee2e6;
  background-color: #f8f9fa;
  border-radius: 0.5rem 0.5rem 0 0;
}

.modal-footer {
  border-top: 1px solid #dee2e6;
  background-color: #f8f9fa;
  border-radius: 0 0 0.5rem 0.5rem;
}

.badge.bg-light {
  border: 1px solid #dee2e6;
}

@media (max-width: 768px) {
  .d-flex.justify-content-between.align-items-center {
    flex-direction: column;
    gap: 1rem;
  }
  
  .d-flex.justify-content-between.align-items-center > div {
    width: 100%;
    display: flex;
    justify-content: space-between;
  }
  
  .modal-dialog {
    margin: 1rem;
  }
}

.modal.show {
  backdrop-filter: blur(2px);
}

.modal-backdrop {
  opacity: 0.5;
}
</style>