<template>
   <Head title="Promotion Report" />
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Student Promotion Report</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item">Reports</li>
               <li class="breadcrumb-item active">Promotions</li>
            </ol>
         </nav>

         <div class="col-12">
            <div class="card">
               <div class="card-header">
                  <h5 class="card-title mb-0">Filter Criteria</h5>
               </div>
               <div class="card-body">
                  <div class="row g-3">
                     <div class="col-md-3">
                        <label class="form-label">Academic Year</label>
                        <v-select v-model="filters.academic_year_id" :options="academicYears" label="name"
                           :reduce="option => option.id" placeholder="All Years"></v-select>
                     </div>
                     <div class="col-md-3">
                        <label class="form-label">From Class</label>
                        <v-select v-model="filters.from_class_id" :options="classes" label="name"
                           :reduce="option => option.id" placeholder="All Classes"></v-select>
                     </div>
                     <div class="col-md-3">
                        <label class="form-label">To Class</label>
                        <v-select v-model="filters.to_class_id" :options="classes" label="name"
                           :reduce="option => option.id" placeholder="All Classes"></v-select>
                     </div>
                     <div class="col-md-3">
                        <label class="form-label">Promotion Type</label>
                        <select class="form-select" v-model="filters.special_promotion">
                           <option :value="null">All Types</option>
                           <option :value="false">Regular Promotion</option>
                           <option :value="true">Special Promotion</option>
                        </select>
                     </div>
                     <div class="col-md-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" class="form-control" v-model="filters.start_date">
                     </div>
                     <div class="col-md-3">
                        <label class="form-label">End Date</label>
                        <input type="date" class="form-control" v-model="filters.end_date">
                     </div>
                     <div class="col-md-6 d-flex align-items-end gap-2">
                        <button class="btn btn-primary" @click="fetchData">
                           <i class="bi bi-search me-1"></i> Filter
                        </button>
                        <button class="btn btn-secondary" @click="resetFilters">
                           <i class="bi bi-x-circle me-1"></i> Reset
                        </button>
                        <button class="btn btn-success ms-auto" @click="generatePdf" :disabled="loading || promotions.length === 0">
                           <i class="bi bi-file-earmark-pdf me-1"></i> Generate PDF
                        </button>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <div class="col-12 mt-4">
            <div class="card">
               <div class="card-body">
                  <div v-if="loading" class="text-center py-5">
                     <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                     </div>
                  </div>
                  
                  <div v-else-if="promotions.length === 0" class="text-center py-5 text-muted">
                     <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                     No promotion records found matching the criteria.
                  </div>

                  <div v-else class="table-responsive">
                     <table class="table table-hover align-middle">
                        <thead class="table-light">
                           <tr>
                              <th>Date</th>
                              <th>Student</th>
                              <th>From Class</th>
                              <th>To Class</th>
                              <th>Type</th>
                              <th>Promoted By</th>
                           </tr>
                        </thead>
                        <tbody>
                           <tr v-for="promotion in promotions" :key="promotion.id">
                              <td>{{ formatDate(promotion.promoted_at) }}</td>
                              <td>
                                 <div>{{ promotion.student?.name }}</div>
                                 <small class="text-muted">{{ promotion.student?.admission_number }}</small>
                              </td>
                              <td>{{ promotion.from_class?.name }}</td>
                              <td>{{ promotion.to_class?.name }}</td>
                              <td>
                                 <span v-if="promotion.special_promotion" class="badge bg-warning text-dark">Special</span>
                                 <span v-else class="badge bg-success">Regular</span>
                              </td>
                              <td>{{ promotion.promoted_by?.name }}</td>
                           </tr>
                        </tbody>
                     </table>
                     
                     <!-- Simple Pagination -->
                     <div class="d-flex justify-content-between align-items-center mt-3" v-if="pagination.total > 0">
                        <div class="text-muted small">
                           Showing {{ pagination.from }} to {{ pagination.to }} of {{ pagination.total }} entries
                        </div>
                        <nav>
                           <ul class="pagination pagination-sm mb-0">
                              <li class="page-item" :class="{ disabled: !pagination.prev_page_url }">
                                 <button class="page-link" @click="changePage(pagination.current_page - 1)">Previous</button>
                              </li>
                              <li class="page-item disabled">
                                 <span class="page-link">{{ pagination.current_page }}</span>
                              </li>
                              <li class="page-item" :class="{ disabled: !pagination.next_page_url }">
                                 <button class="page-link" @click="changePage(pagination.current_page + 1)">Next</button>
                              </li>
                           </ul>
                        </nav>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </DefaultLayout>
</template>

<script setup>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref, onMounted, reactive } from "vue";
import axios from "axios";
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const props = defineProps({
   academicYears: Array,
   classes: Array
});

const loading = ref(false);
const promotions = ref([]);
const pagination = ref({});

const filters = reactive({
   academic_year_id: null,
   from_class_id: null,
   to_class_id: null,
   special_promotion: null,
   start_date: '',
   end_date: '',
   page: 1
});

const fetchData = async () => {
   loading.value = true;
   try {
      const response = await axios.get(route('admin.reports.promotions.data'), {
         params: filters
      });
      promotions.value = response.data.data;
      pagination.value = {
         current_page: response.data.current_page,
         last_page: response.data.last_page,
         total: response.data.total,
         from: response.data.from,
         to: response.data.to,
         prev_page_url: response.data.prev_page_url,
         next_page_url: response.data.next_page_url
      };
   } catch (error) {
      console.error(error);
      toast.error("Failed to fetch promotion data");
   } finally {
      loading.value = false;
   }
};

const changePage = (page) => {
   if (page < 1 || page > pagination.value.last_page) return;
   filters.page = page;
   fetchData();
};

const resetFilters = () => {
   filters.academic_year_id = null;
   filters.from_class_id = null;
   filters.to_class_id = null;
   filters.special_promotion = null;
   filters.start_date = '';
   filters.end_date = '';
   filters.page = 1;
   fetchData();
};

const generatePdf = async () => {
   try {
      loading.value = true;
      const response = await axios.post(route('admin.reports.promotions.generate'), filters, {
         responseType: 'blob'
      });
      
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `promotion-report-${new Date().toISOString().split('T')[0]}.pdf`);
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
      
      toast.success("Report generated successfully!");
   } catch (error) {
      console.error(error);
      toast.error("Failed to generate PDF report");
   } finally {
      loading.value = false;
   }
};

const formatDate = (dateString) => {
   if (!dateString) return '-';
   return new Date(dateString).toLocaleDateString();
};

onMounted(() => {
   fetchData();
});
</script>
