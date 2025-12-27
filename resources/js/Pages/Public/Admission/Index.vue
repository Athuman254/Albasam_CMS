<template>
  <Head title="Online Admission" />
  
  <div class="admission-portal bg-light min-vh-100" :class="{ 'modal-mode': is_modal }">
    <!-- Hero Section -->
    <header v-if="!is_modal" class="admission-hero py-5 text-white bg-primary text-center">
      <div class="container py-4">
        <h1 class="display-4 fw-bold mb-3">Online Admission Portal</h1>
        <p class="lead mb-4">Start your journey with us. Fill out the application form below.</p>
        <div class="d-flex justify-content-center gap-3">
          <button @click="scrollToForm" class="btn btn-outline-light btn-lg px-4 rounded-pill">Apply Now</button>
          <Link :href="route('public.admission.track.form')" class="btn btn-light btn-lg px-4 rounded-pill text-primary">Track Application</Link>
        </div>
      </div>
    </header>

    <main class="container py-5" :class="{ 'py-2': is_modal }" id="application-form">
      <div class="row justify-content-center">
        <div class="col-lg-10" :class="{ 'col-12 w-100': is_modal }">
          
          <!-- Success Message -->
          <div v-if="flash.success && flash.application" class="success-container">
            <div class="card shadow-lg border-0 rounded-4 text-center p-5 animate__animated animate__fadeIn">
              <div class="mb-4">
                <div class="success-icon bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px;">
                  <i class="fas fa-check fa-3x"></i>
                </div>
              </div>
              <h1 class="display-6 fw-bold text-success mb-3">Application Submitted!</h1>
              <p class="lead text-muted mb-4">Your application has been received and is currently under review. Please save your application number for tracking.</p>
              
              <div class="bg-light p-4 rounded-3 mb-4 border">
                <span class="d-block text-muted small text-uppercase fw-bold mb-2">Application Number</span>
                <h2 class="fw-bold text-primary mb-0">{{ flash.application.application_number }}</h2>
              </div>
              
              <div class="row g-3 mb-4">
                <div class="col-6 text-start">
                  <span class="d-block text-muted small">Student Name</span>
                  <span class="fw-bold">{{ flash.application.full_name }}</span>
                </div>
                <div class="col-6 text-start">
                  <span class="d-block text-muted small">Applied For</span>
                  <span class="fw-bold">{{ flash.application.rank }}</span>
                </div>
              </div>

              <div class="d-grid gap-3 col-md-8 mx-auto">
                <button @click="resetForm" class="btn btn-outline-primary rounded-pill py-2">Apply Another Student</button>
                <button v-if="is_modal" @click="closeParentModal" class="btn btn-primary rounded-pill py-2">Finish</button>
                <Link v-else :href="route('welcome')" class="btn btn-primary rounded-pill py-2">Return to Homepage</Link>
              </div>
            </div>
          </div>

          <!-- Application Form -->
          <div v-else class="card shadow-lg border-0 rounded-4 overflow-hidden" :class="{ 'shadow-none': is_modal }">
            <div class="card-header bg-white border-bottom p-0">
              <div class="d-flex text-center">
                <div v-for="step in 3" :key="step" 
                     class="flex-fill p-3 border-end" 
                     :class="{ 'bg-primary text-white': currentStep === step, 'text-muted bg-light': currentStep !== step }">
                  <span class="d-block fw-bold small text-uppercase">Step {{ step }}</span>
                  <span class="d-none d-md-inline">{{ stepTitles[step-1] }}</span>
                </div>
              </div>
            </div>

            <div class="card-body p-4 p-md-5">
              <!-- Track Application Link for Modal Mode -->
              <div v-if="is_modal" class="text-end mb-4">
                <Link :href="route('public.admission.track.form', { modal: 1 })" class="text-decoration-none small fw-bold">
                  <i class="fas fa-search me-1"></i> Already applied? Track Status
                </Link>
              </div>

              <!-- Error Alert -->
              <div v-if="flash.error || Object.keys(form.errors).length > 0" class="alert alert-danger mb-4 rounded-3 border-0 shadow-sm animate__animated animate__shakeX">
                <div class="d-flex align-items-center">
                  <i class="fas fa-exclamation-circle fs-4 me-3"></i>
                  <div>
                    <div v-if="flash.error" class="fw-bold">{{ flash.error }}</div>
                    <div v-else class="fw-bold">Please correct the errors below to continue.</div>
                    <ul v-if="Object.keys(form.errors).length > 0" class="mb-0 mt-1 ps-3 small">
                      <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                    </ul>
                  </div>
                </div>
              </div>

              <form @submit.prevent="submit">
                <!-- Step 1: Student Information -->
                <div v-show="currentStep === 1">
                  <h3 class="mb-4">Student Information</h3>
                  <div class="row g-3">
                    <div class="col-md-4">
                      <label class="form-label fw-bold">First Name <span class="text-danger">*</span></label>
                      <input v-model="form.first_name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.first_name }" placeholder="Enter first name" required>
                      <div v-if="form.errors.first_name" class="invalid-feedback">{{ form.errors.first_name }}</div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label fw-bold">Middle Name</label>
                      <input v-model="form.middle_name" type="text" class="form-control" placeholder="Enter middle name">
                    </div>
                    <div class="col-md-4">
                      <label class="form-label fw-bold">Last Name <span class="text-danger">*</span></label>
                      <input v-model="form.last_name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.last_name }" placeholder="Enter last name" required>
                      <div v-if="form.errors.last_name" class="invalid-feedback">{{ form.errors.last_name }}</div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label fw-bold">Date of Birth <span class="text-danger">*</span></label>
                      <input v-model="form.date_of_birth" type="date" class="form-control" :class="{ 'is-invalid': form.errors.date_of_birth }" required>
                      <div v-if="form.errors.date_of_birth" class="invalid-feedback">{{ form.errors.date_of_birth }}</div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label fw-bold">Gender <span class="text-danger">*</span></label>
                      <select v-model="form.gender_id" class="form-select" :class="{ 'is-invalid': form.errors.gender_id }" required>
                        <option value="">Select Gender</option>
                        <option v-for="gender in genders" :key="gender.id" :value="gender.id">{{ gender.name }}</option>
                      </select>
                      <div v-if="form.errors.gender_id" class="invalid-feedback">{{ form.errors.gender_id }}</div>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label fw-bold">Birth Certificate No.</label>
                      <input v-model="form.birth_certificate_number" type="text" class="form-control" placeholder="BC No.">
                    </div>
                    <div class="col-md-6">
                      <label class="form-label fw-bold">Religion</label>
                      <select v-model="form.religion_id" class="form-select">
                        <option value="">Select Religion</option>
                        <option v-for="religion in religions" :key="religion.id" :value="religion.id">{{ religion.name }}</option>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Step 2: Class & Academic Details -->
                <div v-show="currentStep === 2">
                  <h3 class="mb-4">Class & Academic Details</h3>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label fw-bold">Applying For Class <span class="text-danger">*</span></label>
                      <select v-model="form.rank_id" class="form-select" :class="{ 'is-invalid': form.errors.rank_id }" required>
                        <option value="">Select Class</option>
                        <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                      </select>
                      <div v-if="form.errors.rank_id" class="invalid-feedback">{{ form.errors.rank_id }}</div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label fw-bold">Division/Stream (Optional)</label>
                      <select v-model="form.division_id" class="form-select">
                        <option value="">Select Stream</option>
                        <option v-for="div in divisions" :key="div.id" :value="div.id">{{ div.name }}</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label fw-bold">Academic Year <span class="text-danger">*</span></label>
                      <input v-model="form.academic_year" type="text" class="form-control" :class="{ 'is-invalid': form.errors.academic_year }" placeholder="e.g. 2024" required>
                      <div v-if="form.errors.academic_year" class="invalid-feedback">{{ form.errors.academic_year }}</div>
                    </div>
                    <div class="col-12 mt-4">
                      <h5 class="mb-3 border-bottom pb-2">Previous Schooling</h5>
                      <div class="row g-3">
                        <div class="col-md-6">
                          <label class="form-label fw-bold">Last School Attended</label>
                          <input v-model="form.previous_school" type="text" class="form-control" placeholder="Previous school name">
                        </div>
                        <div class="col-md-6">
                          <label class="form-label fw-bold">Last Class Attended</label>
                          <input v-model="form.previous_class" type="text" class="form-control" placeholder="Previous class">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Step 3: Guardian Information -->
                <div v-show="currentStep === 3">
                  <h3 class="mb-4">Guardian Information</h3>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label fw-bold">Guardian Full Name <span class="text-danger">*</span></label>
                      <input v-model="form.guardian_name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.guardian_name }" placeholder="Enter name" required>
                      <div v-if="form.errors.guardian_name" class="invalid-feedback">{{ form.errors.guardian_name }}</div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label fw-bold">Relationship <span class="text-danger">*</span></label>
                      <select v-model="form.relationship_id" class="form-select" :class="{ 'is-invalid': form.errors.relationship_id }" required>
                        <option value="">Select Relationship</option>
                        <option v-for="rel in relationships" :key="rel.id" :value="rel.id">{{ rel.name }}</option>
                      </select>
                      <div v-if="form.errors.relationship_id" class="invalid-feedback">{{ form.errors.relationship_id }}</div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label fw-bold">Guardian Email <span class="text-danger">*</span></label>
                      <input v-model="form.guardian_email" type="email" class="form-control" :class="{ 'is-invalid': form.errors.guardian_email }" placeholder="Enter email" required>
                      <div v-if="form.errors.guardian_email" class="invalid-feedback">{{ form.errors.guardian_email }}</div>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label fw-bold">Guardian Phone <span class="text-danger">*</span></label>
                      <input v-model="form.guardian_phone" type="tel" class="form-control" :class="{ 'is-invalid': form.errors.guardian_phone }" placeholder="Enter phone number" required>
                      <div v-if="form.errors.guardian_phone" class="invalid-feedback">{{ form.errors.guardian_phone }}</div>
                    </div>
                    <div class="col-12">
                      <label class="form-label fw-bold">Guardian Address</label>
                      <textarea v-model="form.guardian_address" class="form-control" rows="3" placeholder="Residential address"></textarea>
                    </div>
                  </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="d-flex justify-content-between mt-5">
                  <button v-if="currentStep > 1" @click.prevent="currentStep--" type="button" class="btn btn-outline-secondary px-5 py-2">Previous</button>
                  <div v-else></div>
                  
                  <button v-if="currentStep < 3" @click.prevent="currentStep++" type="button" class="btn btn-primary px-5 py-2">Next Step</button>
                  <button v-else :disabled="form.processing" type="submit" class="btn btn-success px-5 py-2">
                    <span v-if="form.processing">Submitting...</span>
                    <span v-else>Submit Application</span>
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
  genders: Array,
  religions: Array,
  classes: Array,
  divisions: Array,
  relationships: Array,
  is_modal: Boolean,
  flash: Object,
});

const currentStep = ref(1);
const stepTitles = ['Student Info', 'Class Details', 'Guardian Info'];

const form = useForm({
  first_name: '',
  middle_name: '',
  last_name: '',
  date_of_birth: '',
  gender_id: '',
  birth_certificate_number: '',
  religion_id: '',
  rank_id: '',
  division_id: '',
  academic_year: new Date().getFullYear().toString(),
  guardian_name: '',
  guardian_email: '',
  guardian_phone: '',
  relationship_id: '',
  guardian_address: '',
  previous_school: '',
  previous_class: '',
});

const submit = () => {
  form.post(route('public.admission.submit', props.is_modal ? { modal: 1 } : {}), {
    onSuccess: () => {
       // Everything handled via props.flash now
    },
  });
};

const resetForm = () => {
  form.reset();
  currentStep.value = 1;
  // Clear flash success manually if needed or just let Inertia handle it on next visit
  // If we stay on the same page, we might need to tell Inertia to clear flash
  window.location.reload(); // Simplest way to clear flash and reset everything
};

const closeParentModal = () => {
  window.parent.postMessage('closeAdmissionModal', '*');
};

const scrollToForm = () => {
  document.getElementById('application-form').scrollIntoView({ behavior: 'smooth' });
};
</script>

<style scoped>
.admission-portal {
  font-family: 'Inter', sans-serif;
  color: #334155;
}

.admission-hero {
  background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
}

.card {
  transition: transform 0.3s ease;
}

.form-control, .form-select {
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  border-color: #e2e8f0;
}

.form-control:focus, .form-select:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.btn-primary {
  background-color: #3b82f6;
  border-color: #3b82f6;
}

.btn-success {
  background-color: #10b981;
  border-color: #10b981;
}
</style>
