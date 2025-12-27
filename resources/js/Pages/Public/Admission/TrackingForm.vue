<template>
  <Head title="Track Application" />
  
  <div class="tracking-page bg-light min-vh-100" :class="{ 'modal-mode': is_modal }">
    <header v-if="!is_modal" class="bg-primary text-white py-5 text-center mb-5">
      <div class="container">
        <h1 class="display-5 fw-bold">Track Application</h1>
        <p class="lead">Check the status of your admission application</p>
      </div>
    </header>

    <div class="container" :class="{ 'py-4': is_modal }">
      <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6" :class="{ 'col-12 w-100': is_modal }">
          <div class="card shadow border-0 rounded-4 p-4 p-md-5 mb-5" :class="{ 'shadow-none': is_modal }">
            <h3 class="fw-bold mb-4">Tracking Information</h3>
            <form @submit.prevent="submit">
              <div class="mb-4">
                <label class="form-label fw-bold">Application Number</label>
                <input v-model="form.application_number" type="text" class="form-control form-control-lg bg-light" placeholder="e.g. APP-202412-0001" required>
                <div v-if="form.errors.application_number" class="text-danger mt-1 small">{{ form.errors.application_number }}</div>
              </div>
              <div class="mb-4">
                <label class="form-label fw-bold">Guardian Email</label>
                <input v-model="form.guardian_email" type="email" class="form-control form-control-lg bg-light" placeholder="Enter the email used during application" required>
                <div v-if="form.errors.guardian_email" class="text-danger mt-1 small">{{ form.errors.guardian_email }}</div>
              </div>
              <div class="d-grid mt-4">
                <button :disabled="form.processing" type="submit" class="btn btn-primary btn-lg rounded-pill">
                  <span v-if="form.processing">Searching...</span>
                  <span v-else>Track Status</span>
                </button>
              </div>
            </form>
          </div>
          
          <div v-if="$page.props.flash.error" class="alert alert-danger rounded-4 p-3 mb-5 border-0">
            {{ $page.props.flash.error }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
  is_modal: Boolean,
});

const form = useForm({
  application_number: '',
  guardian_email: '',
  modal: props.is_modal ? 1 : 0
});

const submit = () => {
  form.post(route('public.admission.track', props.is_modal ? { modal: 1 } : {}));
};
</script>

<style scoped>
.tracking-page {
  font-family: 'Inter', sans-serif;
}
.form-control:focus {
  background-color: #fff !important;
}
</style>
