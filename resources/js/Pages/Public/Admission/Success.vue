<template>
  <Head title="Application Submitted" />
  
  <div class="success-page bg-light min-vh-100 d-flex align-items-center" :class="{ 'modal-mode': is_modal }">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
          <div class="card shadow-lg border-0 rounded-4 text-center p-5">
            <div class="mb-4">
              <div class="success-icon bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px;">
                <i class="fas fa-check fa-3x"></i>
              </div>
            </div>
            <h1 class="display-6 fw-bold text-success mb-3">Application Submitted!</h1>
            <p class="lead text-muted mb-5">Your application has been received and is currently under review. Please save your application number for tracking.</p>
            
            <div class="bg-light p-4 rounded-3 mb-5 border">
              <span class="d-block text-muted small text-uppercase fw-bold mb-2">Application Number</span>
              <h2 class="fw-bold text-primary mb-0">{{ application.application_number }}</h2>
            </div>
            
            <div class="row g-3 mb-5">
              <div class="col-6 text-start">
                <span class="d-block text-muted small">Student Name</span>
                <span class="fw-bold">{{ application.full_name }}</span>
              </div>
              <div class="col-6 text-start">
                <span class="d-block text-muted small">Applied For</span>
                <span class="fw-bold">{{ application.rank?.name }}</span>
              </div>
            </div>

            <div class="d-grid gap-3">
              <Link :href="route('public.admission.index', is_modal ? { modal: 1 } : {})" class="btn btn-outline-primary rounded-pill py-2">Apply Another Student</Link>
              <button v-if="is_modal" @click="closeParentModal" class="btn btn-primary rounded-pill py-2">Finish</button>
              <Link v-else :href="route('welcome')" class="btn btn-primary rounded-pill py-2">Return to Homepage</Link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
  application: Object,
  is_modal: Boolean,
});

const closeParentModal = () => {
  window.parent.postMessage('closeAdmissionModal', '*');
};
</script>

<style scoped>
.success-page {
  font-family: 'Inter', sans-serif;
}
.success-icon {
  box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);
}
</style>
