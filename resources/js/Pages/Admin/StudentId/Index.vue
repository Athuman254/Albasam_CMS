<template>
  <Head title="Student ID Card Generation" />
  <DefaultLayout>
    <div class="container-xxl flex-grow-1 container-p-y">
      <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
          <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Admin /</span> Student ID Cards
          </h4>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5 class="mb-0">Selection & Filters</h5>
          <div class="d-flex gap-2">
            <button 
              @click="generateIds" 
              class="btn btn-primary"
              :disabled="selectedStudents.length === 0 || loading"
            >
              <i v-if="loading" class="bx bx-loader-alt bx-spin me-1"></i>
              <i v-else class="bx bx-id-card me-1"></i>
              Generate Selected ({{ selectedStudents.length }})
            </button>
          </div>
        </div>
        <div class="card-body">
          <div class="row align-items-end">
            <div class="col-md-4">
              <label class="form-label">Search Student</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="bx bx-search"></i></span>
                <input
                  v-model="form.search"
                  type="text"
                  class="form-control"
                  placeholder="Name or Adm No..."
                  @keyup.enter="filter"
                />
              </div>
            </div>
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
               <button @click="filter" class="btn btn-outline-primary w-100">
                 Apply Filters
               </button>
            </div>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="table-responsive text-nowrap">
          <table class="table table-hover">
            <thead>
              <tr>
                <th style="width: 50px;">
                  <input 
                    type="checkbox" 
                    class="form-check-input" 
                    @change="toggleAll"
                    :checked="isAllSelected"
                  />
                </th>
                <th>Photo</th>
                <th>Adm No.</th>
                <th>Name</th>
                <th>Class</th>
                <th>Gender</th>
              </tr>
            </thead>
            <tbody class="table-border-bottom-0">
              <tr v-for="student in students.data" :key="student.id">
                <td>
                  <input 
                    type="checkbox" 
                    class="form-check-input" 
                    v-model="selectedStudents" 
                    :value="student.id"
                  />
                </td>
                <td>
                  <div class="avatar avatar-sm me-2">
                    <img :src="student.photo_url || '/assets/img/avatars/1.png'" alt="Avatar" class="rounded-circle" />
                  </div>
                </td>
                <td>{{ student.admission_number }}</td>
                <td>{{ student.first_name }} {{ student.last_name }}</td>
                <td>
                  <span v-if="student.current_rank" class="badge bg-label-primary">
                    {{ student.current_rank.name }}
                  </span>
                  <span v-else class="text-muted">-</span>
                </td>
                <td>{{ student.gender ? student.gender.name : '-' }}</td>
              </tr>
              <tr v-if="students.data.length === 0">
                <td colspan="6" class="text-center py-4">No students found.</td>
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
import { ref, computed } from 'vue';
import Pagination from '@/Components/Pagination.vue';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import axios from 'axios';

const props = defineProps({
  students: Object,
  classes: Array,
  filters: Object,
});

const form = ref({
  class_id: props.filters.class_id || '',
  search: props.filters.search || '',
});

const selectedStudents = ref([]);
const loading = ref(false);

const isAllSelected = computed(() => {
  return props.students.data.length > 0 && selectedStudents.value.length === props.students.data.length;
});

const toggleAll = (e) => {
  if (e.target.checked) {
    selectedStudents.value = props.students.data.map(s => s.id);
  } else {
    selectedStudents.value = [];
  }
};

const filter = () => {
  router.get(route('admin.student-ids.index'), form.value, {
    preserveState: true,
    preserveScroll: true,
  });
};

const generateIds = () => {
  loading.value = true;
  axios.post(route('admin.student-ids.generate'), {
    student_ids: selectedStudents.value
  }, {
    responseType: 'blob'
  }).then((response) => {
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'student_id_cards.pdf');
    document.body.appendChild(link);
    link.click();
    
    // Clear selection after generation
    // selectedStudents.value = [];
  })
  .catch((error) => {
    console.error('Generation failed:', error);
    alert('Failed to generate student ID cards.');
  })
  .finally(() => {
    loading.value = false;
  });
};
</script>
