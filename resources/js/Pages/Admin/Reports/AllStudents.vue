<template>
  <Head title="All Students Report" />
  <DefaultLayout>
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
          <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Reports /</span> All Students
          </h4>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Filter Options</h5>
          <button @click="exportReport" class="btn btn-primary">
            <i class="bx bx-export me-1"></i> Export PDF
          </button>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4">
              <label class="form-label">Class</label>
              <select v-model="form.class_id" class="form-select" @change="filter">
                <option value="">All Classes</option>
                <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                  {{ cls.name }}
                </option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Gender</label>
              <select v-model="form.gender_id" class="form-select" @change="filter">
                <option value="">All Genders</option>
                <option v-for="gender in genders" :key="gender.id" :value="gender.id">
                  {{ gender.name }}
                </option>
              </select>
            </div>
            <div class="col-md-4">
               <label class="form-label">Per Page</label>
               <select v-model="form.per_page" class="form-select" @change="filter">
                  <option :value="10">10</option>
                  <option :value="20">20</option>
                  <option :value="50">50</option>
                  <option :value="100">100</option>
               </select>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="table-responsive text-nowrap">
          <table class="table table-hover">
            <thead>
              <tr>
                <th>Admission No.</th>
                <th>Name</th>
                <th>Class</th>
                <th>Gender</th>
                <th>Admission Date</th>
              </tr>
            </thead>
            <tbody class="table-border-bottom-0">
              <tr v-for="student in students.data" :key="student.id">
                <td>{{ student.admission_number }}</td>
                <td>{{ student.first_name }} {{ student.last_name }}</td>
                <td>
                  <span v-if="student.current_rank" class="badge bg-label-primary">
                    {{ student.current_rank.name }}
                  </span>
                  <span v-else class="text-muted">-</span>
                </td>
                <td>{{ student.gender ? student.gender.name : '-' }}</td>
                <td>{{ formatDate(student.admission?.registered_at || student.admission?.created_at) }}</td>
              </tr>
              <tr v-if="students.data.length === 0">
                <td colspan="5" class="text-center py-4">No students found.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="card-footer d-flex justify-content-center">
          <Pagination :links="students.links" />
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import axios from 'axios';

const props = defineProps({
  students: Object,
  classes: Array,
  genders: Array,
  filters: Object,
});

const form = ref({
  class_id: props.filters.class_id || '',
  gender_id: props.filters.gender_id || '',
  per_page: props.filters.per_page || 10,
});

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString();
};

const filter = () => {
  router.get(route('admin.reports.all-students'), form.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

const exportReport = () => {
  axios.post(route('admin.reports.all-students.export'), form.value, {
    responseType: 'blob'
  }).then((response) => {
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'all_students_report.pdf');
    document.body.appendChild(link);
    link.click();
  }).catch((error) => {
    console.error('Export failed:', error);
    alert('Failed to export report.');
  });
};
</script>
