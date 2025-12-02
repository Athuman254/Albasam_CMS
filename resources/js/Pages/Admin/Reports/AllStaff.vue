<template>
  <Head title="All Staff Report" />
  <DefaultLayout>
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
          <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Reports /</span> All Staff
          </h4>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Filter Options</h5>
          <div class="d-flex gap-2">
             <span class="badge bg-label-primary d-flex align-items-center">
                Total Staff: {{ employees.total }}
             </span>
             <button @click="exportReport" class="btn btn-primary">
               <i class="bx bx-export me-1"></i> Export PDF
             </button>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-4">
              <label class="form-label">Role</label>
              <select v-model="form.role_id" class="form-select" @change="filter">
                <option value="">All Roles</option>
                <option v-for="role in roles" :key="role.id" :value="role.id">
                  {{ role.display_name }}
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
                <th>Staff No.</th>
                <th>Name</th>
                <th>Role</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Joined Date</th>
              </tr>
            </thead>
            <tbody class="table-border-bottom-0">
              <tr v-for="employee in employees.data" :key="employee.id">
                <td>{{ employee.staff_number || '-' }}</td>
                <td>{{ employee.first_name }} {{ employee.last_name }}</td>
                <td>
                  <div v-if="employee.user && employee.user.roles">
                    <span v-for="role in employee.user.roles" :key="role.id" class="badge bg-label-info me-1">
                      {{ role.display_name }}
                    </span>
                  </div>
                  <span v-else class="text-muted">-</span>
                </td>
                <td>{{ employee.email }}</td>
                <td>{{ employee.primary_phone || '-' }}</td>
                <td>{{ formatDate(employee.date_of_hire) }}</td>
              </tr>
              <tr v-if="employees.data.length === 0">
                <td colspan="6" class="text-center py-4">No staff found.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="card-footer d-flex justify-content-center">
          <Pagination :links="employees.links" />
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
  employees: Object,
  roles: Array,
  filters: Object,
});

const form = ref({
  role_id: props.filters.role_id || '',
  per_page: props.filters.per_page || 10,
});

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString();
};

const filter = () => {
  router.get(route('admin.reports.all-staff'), form.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

const exportReport = () => {
  axios.post(route('admin.reports.all-staff.export'), form.value, {
    responseType: 'blob'
  }).then((response) => {
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'all_staff_report.pdf');
    document.body.appendChild(link);
    link.click();
  }).catch((error) => {
    console.error('Export failed:', error);
    alert('Failed to export report.');
  });
};
</script>
