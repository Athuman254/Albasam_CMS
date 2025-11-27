<script>
export const VueTable = {
   props: {
      apiUrl: {
         type: String,
         required: true,
      },
      apiMode: {
         type: Boolean,
         default: () => false
      },
      data_src: {
         type: Array,
         default: () => []
      },
      fields: {
         type: Array,
         required: true,
      },
      appendParams: {
         type: Object,
         default: () => ({}),
      },
      perPage: {
         type: Number,
         default: 15,
      },
   },
   data() {
      return {
         rows: [],
         loading: false,
         error: null,
         pagination: {
            current_page: 1,
            total: 0,
            last_page: 1,
            per_page: this.perPage,
         },
         pageSizeOptions: [10, 15, 20, 25, 50, 100],
         selectedPageSize: this.perPage,
      };
   },
   computed: {
      visiblePages() {
         const current = this.pagination.current_page;
         const last = this.pagination.last_page;
         const delta = 2;
         const pages = [];
         
         if (last <= 1) return [1];
         
         // Always show first page
         pages.push(1);
         
         // Calculate range around current page
         const rangeStart = Math.max(2, current - delta);
         const rangeEnd = Math.min(last - 1, current + delta);
         
         // Add ellipsis after first page if needed
         if (rangeStart > 2) {
            pages.push('...');
         }
         
         // Add pages in range
         for (let i = rangeStart; i <= rangeEnd; i++) {
            pages.push(i);
         }
         
         // Add ellipsis before last page if needed
         if (rangeEnd < last - 1) {
            pages.push('...');
         }
         
         // Always show last page if there's more than one page
         if (last > 1) {
            pages.push(last);
         }
         
         return pages;
      },
      showingInfo() {
         if (this.pagination.total === 0) return 'No records found';
         
         const start = ((this.pagination.current_page - 1) * this.pagination.per_page) + 1;
         const end = Math.min(this.pagination.current_page * this.pagination.per_page, this.pagination.total);
         
         return `Showing ${start} to ${end} of ${this.pagination.total} entries`;
      },
   },
   methods: {
      buildUrl(baseUrl, params) {
         const url = new URL(baseUrl);
         const appendNestedParams = (prefix, obj) => {
            for (const [key, value] of Object.entries(obj)) {
               if (typeof value === 'object' && value !== null) {
                  appendNestedParams(`${prefix}[${key}]`, value);
               } else {
                  url.searchParams.append(`${prefix}[${key}]`, value);
               }
            }
         };
         for (const [key, value] of Object.entries(params)) {
            if (typeof value === 'object' && value !== null) {
               appendNestedParams(key, value);
            } else {
               url.searchParams.append(key, value);
            }
         }
         return url.toString();
      },
      async fetchData(page = 1) {
         this.loading = true;
         this.error = null;
         try {
            let params = {
               sort: '',
               page: {
                  size: this.selectedPageSize,
                  number: page
               },
               filter: this.appendParams.filter,
            };
            
            const baseUrl = this.apiUrl.startsWith("http")
               ? this.apiUrl
               : `${window.location.origin}/${this.apiUrl}`;
            const url = this.buildUrl(baseUrl, params);
            
            const response = await axios.get(url);
            this.rows = Array.isArray(response.data.data) ? response.data.data : [];
            
            // Handle both Laravel pagination formats
            if (response.data.meta) {
               this.pagination = {
                  current_page: response.data.meta.current_page || 1,
                  total: response.data.meta.total || 0,
                  last_page: response.data.meta.last_page || 1,
                  per_page: response.data.meta.per_page || this.selectedPageSize,
               };
            } else if (response.data.current_page) {
               this.pagination = {
                  current_page: response.data.current_page,
                  total: response.data.total || 0,
                  last_page: response.data.last_page || 1,
                  per_page: response.data.per_page || this.selectedPageSize,
               };
            }
            
         } catch (err) {
            this.error = err.response?.data?.message || err.message || "An error occurred";
            console.error("Error fetching data:", err);
         } finally {
            this.loading = false;
         }
      },
      goToPage(page) {
         if (page >= 1 && page <= this.pagination.last_page && page !== '...') {
            this.fetchData(page);
         }
      },
      changePageSize() {
         this.selectedPageSize = parseInt(this.selectedPageSize);
         this.pagination.per_page = this.selectedPageSize;
         this.pagination.current_page = 1;
         this.fetchData(1);
      },
      reloadTable() {
         this.fetchData(this.pagination.current_page);
      },
   },
   
   watch: {
      appendParams: {
         handler: "fetchData",
         deep: true
      },
   },
   mounted() {
      this.fetchData(1);
   },
   template: `
      <div class="table-container">
         <div class="table-responsive">
            <table class="table table-hover table-sm">
               <thead style="background-color: rgb(34, 48, 62, 0.06);">
               <tr>
                  <th
                     v-for="field in fields"
                     :key="field.name"
                     :class="field.titleClass || ''">
                     {{ field.title || '' }}
                  </th>
               </tr>
               </thead>
               <tbody>
               
               <tr v-if="loading">
                  <td :colspan="fields.length">
                     <div class="text-center py-3">
                        <div class="eas-spinner mx-auto"></div>
                        <div class="mt-2">Loading data...</div>
                     </div>
                  </td>
               </tr>
               
               <tr v-else-if="!rows.length && !error">
                  <td :colspan="fields.length" class="text-center py-3">
                     <i class="bx bx-inbox text-muted" style="font-size: 2rem;"></i>
                     <div class="mt-2">No data available.</div>
                  </td>
               </tr>
               
               <tr v-else-if="error">
                  <td :colspan="fields.length">
                     <div class="text-center py-3 text-danger">
                        <i class="bx bx-error-alt" style="font-size: 2rem;"></i>
                        <div class="mt-2">Error: {{ error }}</div>
                        <button class="btn btn-sm btn-outline-primary mt-2" @click="fetchData(1)">
                           <i class="bx bx-refresh me-1"></i> Try Again
                        </button>
                     </div>
                  </td>
               </tr>
               
               <tr v-else v-for="row in rows" :key="row.id">
                  <td
                     v-for="field in fields"
                     :key="field.name"
                     :class="field.dataClass || ''"
                  >
                     <template v-if="field.name.startsWith('__slot:')">
                        <slot
                           :name="field.name.split(':')[1]"
                           :rowData="row"
                        ></slot>
                     </template>
                     <template v-else>
                        {{ field.name.split('.').reduce((acc, curr) => acc && acc[curr], row) }}
                     </template>
                  </td>
               </tr>
               </tbody>
            </table>
         </div>
         
         <!-- Professional Pagination Controls -->
         <div class="custom-pagination p-3 border-top" v-if="pagination.last_page > 1 || rows.length > 0">
            <div class="row align-items-center">
               <!-- Showing Info -->
               <div class="col-md-6 mb-2 mb-md-0">
                  <div class="text-muted small">
                     {{ showingInfo }}
                  </div>
               </div>
               
               <!-- Page Size Selector -->
               <div class="col-md-6 text-md-end">
                  <div class="d-flex align-items-center justify-content-md-end gap-3">
                     <!-- Items per page -->
                     <div class="d-flex align-items-center">
                        <label class="form-label-sm mb-0 me-2 text-muted">Show:</label>
                        <select 
                           class="form-select form-select-sm" 
                           style="width: auto;"
                           v-model="selectedPageSize"
                           @change="changePageSize"
                        >
                           <option v-for="option in pageSizeOptions" :key="option" :value="option">
                              {{ option }}
                           </option>
                        </select>
                     </div>
                     
                     <!-- Pagination -->
                     <nav aria-label="Pagination">
                        <ul class="pagination pagination-sm mb-0">
                           <!-- First Page -->
                           <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                              <button class="page-link" @click="goToPage(1)" title="First Page">
                                 <i class='bx bx-chevrons-left'></i>
                              </button>
                           </li>
                           
                           <!-- Previous Page -->
                           <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                              <button class="page-link" @click="goToPage(pagination.current_page - 1)" title="Previous Page">
                                 <i class='bx bx-chevron-left'></i>
                              </button>
                           </li>
                           
                           <!-- Page Numbers -->
                           <li
                              v-for="(page, index) in visiblePages"
                              :key="index"
                              :class="{ 
                                 active: pagination.current_page === page,
                                 disabled: page === '...'
                              }"
                              class="page-item"
                           >
                              <button 
                                 class="page-link" 
                                 @click="goToPage(page)"
                                 :disabled="page === '...'"
                              >
                                 {{ page }}
                              </button>
                           </li>
                           
                           <!-- Next Page -->
                           <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                              <button class="page-link" @click="goToPage(pagination.current_page + 1)" title="Next Page">
                                 <i class='bx bx-chevron-right'></i>
                              </button>
                           </li>
                           
                           <!-- Last Page -->
                           <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                              <button class="page-link" @click="goToPage(pagination.last_page)" title="Last Page">
                                 <i class='bx bx-chevrons-right'></i>
                              </button>
                           </li>
                        </ul>
                     </nav>
                  </div>
               </div>
            </div>
         </div>
      </div>
   `,
};
</script>

<style scoped>
.custom-pagination {
   background-color: #f8f9fa;
}

.pagination {
   margin: 0;
}

.page-item.active .page-link {
   background-color: #696cff;
   border-color: #696cff;
   color: white;
}

.page-link {
   color: #696cff;
   border: 1px solid #dee2e6;
   padding: 0.375rem 0.75rem;
   font-size: 0.875rem;
}

.page-link:hover {
   background-color: #e9ecef;
   border-color: #dee2e6;
   color: #0056b3;
}

.page-item.disabled .page-link {
   color: #6c757d;
   background-color: #f8f9fa;
   border-color: #dee2e6;
}

.form-select-sm {
   padding: 0.25rem 2rem 0.25rem 0.5rem;
   font-size: 0.875rem;
}

.eas-spinner {
   width: 2rem;
   height: 2rem;
   border: 2px solid #f3f3f3;
   border-top: 2px solid #696cff;
   border-radius: 50%;
   animation: spin 1s linear infinite;
}

@keyframes spin {
   0% { transform: rotate(0deg); }
   100% { transform: rotate(360deg); }
}

.table-container {
   border-radius: 0.375rem;
   overflow: hidden;
}
</style>