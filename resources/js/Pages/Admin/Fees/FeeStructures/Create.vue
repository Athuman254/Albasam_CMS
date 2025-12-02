<template>
  <DefaultLayout>
    <Head title="Create Fee Structure" />
    
    <!-- Success/Error Messages with Safe Handling -->
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
              <h4 class="card-title mb-0">Create Fee Structure</h4>
              <Link 
                :href="route('admin.fee-structures.index')" 
                class="btn btn-secondary"
              >
                <i class="fas fa-arrow-left me-1"></i> Back to Fee Structures
              </Link>
            </div>
            <div class="card-body">
              <form @submit.prevent="submitForm">
                <div class="row">
                  <!-- Class Selection -->
                  <div class="col-md-4">
                    <div class="form-group mb-3">
                      <label for="class_id" class="form-label">Class *</label>
                      <select 
                        id="class_id"
                        v-model="form.class_id"
                        class="form-control"
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

                  <!-- Academic Year -->
                  <div class="col-md-4">
                    <div class="form-group mb-3">
                      <label for="academic_year" class="form-label">Academic Year *</label>
                      <select 
                        id="academic_year"
                        v-model="form.academic_year"
                        class="form-control"
                        :class="{ 'is-invalid': form.errors.academic_year }"
                      >
                        <option value="">Select Academic Year</option>
                        <option 
                          v-for="year in academic_years" 
                          :key="year.id" 
                          :value="year.name"
                        >
                          {{ year.display_name }}
                        </option>
                      </select>
                      <div v-if="form.errors.academic_year" class="invalid-feedback">
                        {{ form.errors.academic_year }}
                      </div>
                    </div>
                  </div>

                  <!-- Term -->
                  <div class="col-md-4">
                    <div class="form-group mb-3">
                      <label for="term" class="form-label">Term *</label>
                      <select 
                        id="term"
                        v-model="form.term"
                        class="form-control"
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
                </div>

                <div class="row">
                  <!-- Amount -->
                  <div class="col-md-6">
                    <div class="form-group mb-3">
                      <label for="amount" class="form-label">Tuition Fee Amount *</label>
                      <div class="input-group">
                        <span class="input-group-text">Ksh</span>
                        <input 
                          type="number" 
                          id="amount"
                          v-model="form.amount"
                          class="form-control"
                          :class="{ 'is-invalid': form.errors.amount }"
                          placeholder="0.00"
                          step="0.01"
                          min="0"
                        >
                      </div>
                      <div v-if="form.errors.amount" class="invalid-feedback">
                        {{ form.errors.amount }}
                      </div>
                      <small class="text-muted">
                        Base tuition fee amount
                      </small>
                    </div>
                  </div>

                  <!-- Due Date -->
                  <div class="col-md-6">
                    <div class="form-group mb-3">
                      <label for="due_date" class="form-label">Due Date *</label>
                      <input 
                        type="date" 
                        id="due_date"
                        v-model="form.due_date"
                        class="form-control"
                        :class="{ 'is-invalid': form.errors.due_date }"
                        :min="minDueDate"
                      >
                      <div v-if="form.errors.due_date" class="invalid-feedback">
                        {{ form.errors.due_date }}
                      </div>
                      <small class="text-muted">
                        Fee must be paid by this date
                      </small>
                    </div>
                  </div>
                </div>

                <!-- Description -->
                <div class="row">
                  <div class="col-12">
                    <div class="form-group mb-3">
                      <label for="description" class="form-label">Description</label>
                      <textarea 
                        id="description"
                        v-model="form.description"
                        class="form-control"
                        :class="{ 'is-invalid': form.errors.description }"
                        rows="3"
                        placeholder="Optional description about this fee structure..."
                      ></textarea>
                      <div v-if="form.errors.description" class="invalid-feedback">
                        {{ form.errors.description }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Additional Fees Section -->
                <div class="row mt-4">
                  <div class="col-12">
                    <div class="card border-info">
                      <div class="card-header bg-white text-info border-bottom">
                        <div class="d-flex justify-content-between align-items-center">
                          <h6 class="mb-0">Additional Fees</h6>
                          <button 
                            type="button" 
                            class="btn btn-sm btn-outline-info"
                            @click="addAdditionalFee"
                          >
                            <i class="fas fa-plus me-1"></i> Add Fee Item
                          </button>
                        </div>
                      </div>
                      <div class="card-body">
                        <div v-if="form.additional_fees.length === 0" class="text-center py-3 text-muted">
                          <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                          <p>No additional fees added. Click "Add Fee Item" to include additional charges.</p>
                        </div>
                        
                        <div v-else class="table-responsive">
                          <table class="table table-sm table-bordered">
                            <thead class="table-light">
                              <tr>
                                <th width="30%">Fee Name *</th>
                                <th width="25%">Amount *</th>
                                <th width="35%">Description</th>
                                <th width="10%" class="text-center">Actions</th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr v-for="(fee, index) in form.additional_fees" :key="index">
                                <td>
                                  <input 
                                    type="text"
                                    v-model="fee.name"
                                    class="form-control form-control-sm"
                                    placeholder="e.g., Activity Fee"
                                    :class="{ 'is-invalid': form.errors[`additional_fees.${index}.name`] }"
                                  >
                                  <div v-if="form.errors[`additional_fees.${index}.name`]" class="invalid-feedback d-block">
                                    {{ form.errors[`additional_fees.${index}.name`] }}
                                  </div>
                                </td>
                                <td>
                                  <div class="input-group input-group-sm">
                                    <span class="input-group-text">Ksh</span>
                                    <input 
                                      type="number"
                                      v-model="fee.amount"
                                      class="form-control"
                                      placeholder="0.00"
                                      step="0.01"
                                      min="0"
                                      :class="{ 'is-invalid': form.errors[`additional_fees.${index}.amount`] }"
                                    >
                                  </div>
                                  <div v-if="form.errors[`additional_fees.${index}.amount`]" class="invalid-feedback d-block">
                                    {{ form.errors[`additional_fees.${index}.amount`] }}
                                  </div>
                                </td>
                                <td>
                                  <input 
                                    type="text"
                                    v-model="fee.description"
                                    class="form-control form-control-sm"
                                    placeholder="e.g., Term activities and events"
                                  >
                                </td>
                                <td class="text-center">
                                  <button 
                                    type="button"
                                    class="btn btn-sm btn-outline-danger"
                                    @click="removeAdditionalFee(index)"
                                  >
                                    <i class="fas fa-trash"></i>
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

                <!-- Fee Summary -->
                <div class="row mt-4" v-if="form.amount || form.additional_fees.length > 0">
                  <div class="col-12">
                    <div class="card border-primary">
                      <div class="card-header bg-white text-primary border-bottom">
                        <h6 class="mb-0">Fee Structure Summary</h6>
                      </div>
                      <div class="card-body">
                        <div class="row mb-3">
                          <div class="col-md-3">
                            <p class="mb-1"><strong>Class:</strong></p>
                            <p class="mb-0">{{ selectedClassName }}</p>
                          </div>
                          <div class="col-md-3">
                            <p class="mb-1"><strong>Academic Year:</strong></p>
                            <p class="mb-0">{{ form.academic_year || 'Not set' }}</p>
                          </div>
                          <div class="col-md-2">
                            <p class="mb-1"><strong>Term:</strong></p>
                            <p class="mb-0">{{ form.term ? `Term ${form.term}` : 'Not set' }}</p>
                          </div>
                          <div class="col-md-2">
                            <p class="mb-1"><strong>Due Date:</strong></p>
                            <p class="mb-0">{{ formatDate(form.due_date) || 'Not set' }}</p>
                          </div>
                          <div class="col-md-2">
                            <p class="mb-1"><strong>Total Amount:</strong></p>
                            <h5 class="text-success mb-0">{{ formatCurrency(totalAmount) }}</h5>
                          </div>
                        </div>

                        <!-- Fee Breakdown -->
                        <div class="row" v-if="form.amount || form.additional_fees.length > 0">
                          <div class="col-12">
                            <h6 class="mb-2">Fee Breakdown:</h6>
                            <div class="table-responsive">
                              <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                  <tr>
                                    <th>Fee Item</th>
                                    <th class="text-end">Amount</th>
                                  </tr>
                                </thead>
                                <tbody>
                                  <tr v-if="form.amount">
                                    <td>Tuition Fee</td>
                                    <td class="text-end">{{ formatCurrency(form.amount) }}</td>
                                  </tr>
                                  <tr v-for="fee in form.additional_fees" :key="fee.name">
                                    <td>{{ fee.name }}</td>
                                    <td class="text-end">{{ formatCurrency(fee.amount) }}</td>
                                  </tr>
                                </tbody>
                                <tfoot class="table-light">
                                  <tr>
                                    <th>Total</th>
                                    <th class="text-end">{{ formatCurrency(totalAmount) }}</th>
                                  </tr>
                                </tfoot>
                              </table>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mt-4">
                  <div class="col-12">
                    <div class="d-flex gap-2">
                      <button 
                        type="submit" 
                        class="btn btn-primary"
                        :disabled="form.processing || !isFormValid"
                      >
                        <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                        <i v-else class="fas fa-save me-1"></i>
                        {{ form.processing ? 'Creating...' : 'Create Fee Structure' }}
                      </button>
                      
                      <Link 
                        :href="route('admin.fee-structures.index')" 
                        class="btn btn-secondary"
                        :disabled="form.processing"
                      >
                        <i class="fas fa-times me-1"></i>
                        Cancel
                      </Link>

                      <button 
                        type="button" 
                        class="btn btn-outline-info"
                        @click="resetForm"
                        :disabled="form.processing"
                      >
                        <i class="fas fa-redo me-1"></i>
                        Reset Form
                      </button>
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
  classes: Array,
  academic_years: Array,
});

const form = useForm({
  class_id: '',
  academic_year: '',
  term: '',
  amount: '',
  description: '',
  due_date: '',
  additional_fees: [],
});

// Computed properties
const minDueDate = computed(() => {
  return new Date().toISOString().split('T')[0];
});

const selectedClassName = computed(() => {
  if (!form.class_id) return '';
  const classItem = props.classes.find(c => c.id == form.class_id);
  return classItem ? classItem.name : '';
});

const totalAmount = computed(() => {
  let total = parseFloat(form.amount) || 0;
  form.additional_fees.forEach(fee => {
    total += parseFloat(fee.amount) || 0;
  });
  return total;
});

const isFormValid = computed(() => {
  return form.class_id && 
         form.academic_year && 
         form.term && 
         form.amount && 
         form.due_date;
});

// Methods
const addAdditionalFee = () => {
  form.additional_fees.push({
    name: '',
    amount: '',
    description: ''
  });
};

const removeAdditionalFee = (index) => {
  form.additional_fees.splice(index, 1);
};

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
  return new Date(dateString).toLocaleDateString('en-KE', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

const resetForm = () => {
  form.reset();
  form.additional_fees = [];
  // Reset due date to default
  const defaultDueDate = new Date();
  defaultDueDate.setDate(defaultDueDate.getDate() + 30);
  form.due_date = defaultDueDate.toISOString().split('T')[0];
};

const submitForm = () => {
  console.log('Submitting form data:', form.data());
  
  form.post(route('admin.fee-structures.store'), {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Form submitted successfully!');
    },
    onError: (errors) => {
      console.log('Form errors:', errors);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },
    onFinish: () => {
      console.log('Form submission finished');
    }
  });
};

onMounted(() => {
  // Set default due date to 30 days from now
  const defaultDueDate = new Date();
  defaultDueDate.setDate(defaultDueDate.getDate() + 30);
  form.due_date = defaultDueDate.toISOString().split('T')[0];
  
  console.log('Academic Years:', props.academic_years);
  console.log('Classes:', props.classes);
});
</script>

<style scoped>
.card-header {
  border-bottom: none;
}

.form-label {
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.input-group-text {
  background-color: #f8f9fa;
  border-color: #ced4da;
  font-weight: 600;
  color: #2c3e50;
}

.invalid-feedback {
  display: block;
}

.btn {
  border-radius: 0.375rem;
}

.spinner-border-sm {
  width: 1rem;
  height: 1rem;
}

.text-success {
  color: #198754 !important;
}

.alert {
  margin: 1rem;
  border-radius: 0.5rem;
}

.card.border-primary {
  border-color: #0d6efd !important;
}

.card.border-info {
  border-color: #0dcaf0 !important;
}

.table th {
  font-weight: 600;
  font-size: 0.875rem;
}
</style>