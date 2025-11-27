<template>
  <div class="modal fade" :class="{ show: show }" :style="{ display: show ? 'block' : 'none' }" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title">
            <i class="bi bi-exclamation-triangle me-2"></i>
            Edit Mark (Admin Override)
          </h5>
          <button type="button" class="btn-close" @click="close" :disabled="loading"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-warning">
            <strong><i class="bi bi-shield-lock me-1"></i> Admin Action:</strong> 
            You are editing a mark. This action will be logged for audit purposes.
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Student:</label>
            <div>{{ mark?.student?.name }} ({{ mark?.student?.admission_number }})</div>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Subject:</label>
            <div>{{ mark?.examSubject?.subject?.name }}</div>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Status:</label>
            <span class="badge" :class="getStatusBadge(mark?.status)">
              {{ mark?.status || 'Unknown' }}
            </span>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Current Mark:</label>
            <div class="text-primary fs-5">
              {{ mark?.marks_obtained }} / {{ mark?.maximum_marks }}
              <span class="badge bg-secondary ms-2">{{ mark?.grade }}</span>
            </div>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">New Mark: <span class="text-danger">*</span></label>
            <input 
              type="number" 
              class="form-control" 
              v-model.number="newMarks"
              :max="mark?.maximum_marks"
              min="0"
              step="0.5"
              :class="{ 'is-invalid': errors.marks }"
            >
            <small class="text-muted">Maximum: {{ mark?.maximum_marks }}</small>
            <div v-if="errors.marks" class="invalid-feedback">{{ errors.marks }}</div>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Reason for Change: <span class="text-danger">*</span></label>
            <textarea 
              class="form-control" 
              v-model="editReason"
              rows="3"
              placeholder="e.g., Teacher reported marking error, student appealed, calculation mistake..."
              :class="{ 'is-invalid': errors.reason }"
              required
            ></textarea>
            <small :class="editReason.trim().length > 0 && editReason.trim().length < 10 ? 'text-danger' : 'text-muted'">
              Minimum 10 characters ({{ editReason.trim().length }}/10)
            </small>
            <div v-if="errors.reason" class="invalid-feedback">{{ errors.reason }}</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="close" :disabled="loading">
            Cancel
          </button>
          <button 
            type="button" 
            class="btn btn-warning" 
            @click="submitEdit"
            :disabled="!canSubmit || loading"
          >
            <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
            <i v-else class="bi bi-pencil me-1"></i>
            {{ loading ? 'Updating...' : 'Update Mark' }}
          </button>
        </div>
      </div>
    </div>
  </div>
  <div class="modal-backdrop fade" :class="{ show: show }" v-if="show"></div>
</template>

<script setup>
import { ref, computed, watch, inject } from 'vue';
import axios from 'axios';

const toast = inject('toast');

const props = defineProps({
  show: Boolean,
  mark: Object
});

const emit = defineEmits(['close', 'updated']);

const newMarks = ref(null);
const editReason = ref('');
const loading = ref(false);
const errors = ref({});

// Watch for mark changes to reset form
watch(() => props.mark, (newMark) => {
  if (newMark) {
    newMarks.value = newMark.marks_obtained;
    editReason.value = '';
    errors.value = {};
  }
}, { immediate: true });

const canSubmit = computed(() => {
  return newMarks.value !== null && 
         newMarks.value !== '' &&
         editReason.value.trim().length >= 10 &&
         newMarks.value >= 0 &&
         newMarks.value <= (props.mark?.maximum_marks || 0) &&
         newMarks.value !== props.mark?.marks_obtained; // Must be different
});

const getStatusBadge = (status) => {
  const badges = {
    'draft': 'bg-secondary',
    'submitted': 'bg-info',
    'approved': 'bg-success',
    'published': 'bg-primary',
    'rejected': 'bg-danger'
  };
  return badges[status?.toLowerCase()] || 'bg-secondary';
};

const validateForm = () => {
  errors.value = {};
  
  if (newMarks.value === null || newMarks.value === '') {
    errors.value.marks = 'Mark is required';
    return false;
  }
  
  if (newMarks.value < 0) {
    errors.value.marks = 'Mark cannot be negative';
    return false;
  }
  
  if (newMarks.value > props.mark?.maximum_marks) {
    errors.value.marks = `Mark cannot exceed ${props.mark?.maximum_marks}`;
    return false;
  }
  
  if (newMarks.value === props.mark?.marks_obtained) {
    errors.value.marks = 'New mark must be different from current mark';
    return false;
  }
  
  if (editReason.value.trim().length < 10) {
    errors.value.reason = 'Reason must be at least 10 characters';
    return false;
  }
  
  return true;
};

const submitEdit = async () => {
  if (!validateForm()) {
    toast.error('Please fix the errors before submitting');
    return;
  }
  
  loading.value = true;
  try {
    const response = await axios.put(
      route('admin.exams.marks.admin-update', props.mark.id),
      {
        marks_obtained: newMarks.value,
        edit_reason: editReason.value.trim()
      }
    );
    
    toast.success(response.data.message);
    emit('updated', response.data.data);
    close();
  } catch (error) {
    if (error.response?.status === 422) {
      // Validation errors
      const validationErrors = error.response.data.errors || {};
      errors.value = {
        marks: validationErrors.marks_obtained?.[0],
        reason: validationErrors.edit_reason?.[0]
      };
    }
    toast.error(error.response?.data?.message || 'Failed to update mark');
  } finally {
    loading.value = false;
  }
};

const close = () => {
  if (!loading.value) {
    newMarks.value = null;
    editReason.value = '';
    errors.value = {};
    emit('close');
  }
};
</script>

<style scoped>
.modal.show {
  display: block !important;
  z-index: 1060; /* Higher than standard Bootstrap modal (1055) */
}

.modal-backdrop.show {
  opacity: 0.5;
  z-index: 1059; /* Higher than standard Bootstrap backdrop (1050) */
}
</style>
