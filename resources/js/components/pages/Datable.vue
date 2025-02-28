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
         default: 2,
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
         },
      };
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
                  size: this.per_page ?? 60,
                  number: page
               },
               filter: this.appendParams.filter,
            };
            const baseUrl = this.apiUrl.startsWith("http")
               ? this.apiUrl
               : `${window.location.origin}/${this.apiUrl}`;
            const urls = this.buildUrl(baseUrl, params);
            
            
            const response = await axios.get(urls);
            this.rows = Array.isArray(response.data.data) ? response.data.data : [];
            this.pagination = response.data.meta || {
               current_page: response.data.current_page || 1,
               total: response.data.total || this.rows.length,
               last_page: response.data.last_page || 1,
            };
         } catch (err) {
            this.error = err.response?.data?.message || err.message || "An error occurred";
            console.error("Error fetching data:", err);
         } finally {
            this.loading = false;
         }
      },
      goToPage(page) {
         if (page >= 1 && page <= this.pagination.last_page) {
            this.fetchData(page);
         }
      },
      reloadTable() {
         this.fetchData();
      },
   },
   
   watch: {
      appendParams: {
         handler: "fetchData",
      },
   },
   mounted() {
      this.fetchData(1);
   },
   template: `
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
                  </div>
               </td>
            </tr>
            
            <tr v-else-if="!rows.length">
               <td :colspan="fields.length" class="text-center py-3">No data available.</td>
            </tr>
            <tr v-else-if="error">
               <td>
                  <div class="text-center py-3 text-danger">Error: {{ error }}</div>
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
      
      <!-- Pagination Controls -->
      <div class="custom-pagination p-5">
         <nav aria-label="Pagination">
            <ul class="pagination  justify-content-center justify-content-lg-end mb-0">
               <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                  <button class="page-link" style="border-radius: 5px;" @click="goToPage(1)"><i
                     class='bx bx-chevrons-left'></i></button>
               </li>
               <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                  <button class="page-link" style="border-radius: 5px;" @click="goToPage(pagination.current_page - 1)">
                     <i class='bx bx-chevron-left'></i></button>
               </li>
               <li
                  v-for="page in pagination.last_page"
                  :key="page"
                  :class="{ active: pagination.current_page === page }"
                  class="page-item"
               >
                  <button class="page-link" style="border-radius: 5px;" @click="goToPage(page)">{{ page }}</button>
               </li>
               <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                  <button class="page-link" style="border-radius: 5px;" @click="goToPage(pagination.current_page + 1)">
                     <i class='bx bx-chevron-right'></i></button>
               </li>
               <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                  <button class="page-link" style="border-radius: 5px;" @click="goToPage(pagination.last_page)"><i
                     class='bx bx-chevrons-right'></i></button>
               </li>
            </ul>
         </nav>
      </div>
   
   
   `,
};
</script>
<style scoped>
.custom-pagination > .pagination {
   display: flex;
   list-style: none;
   padding: 0 !important;
   margin: 0 !important;
}

.pagination {
   padding: 0 !important;
   margin: 0 !important;
}

.custom-pagination .page-item {
   margin: 0 5px;
}

.custom-pagination .page-item.disabled .page-link {
   background-color: #f8f9fa !important;
   color: #d6d8db !important;
   border: 1px solid #d6d8db !important;
   cursor: not-allowed !important;
}

.custom-pagination .page-item.active .page-link {
   background-color: #ff0062 !important;
   color: white;
   border-color: #007bff;
   font-weight: bold;
}

.custom-pagination .page-link {
   display: block;
   padding: 8px 12px;
   color: #007bff;
   background-color: white;
   border: 1px solid #dee2e6;
   border-radius: 1px !important;
   text-decoration: none;
   transition: all 0.3s ease;
   cursor: pointer;
}

.custom-pagination .page-link:hover {
   background-color: #f0f8ff;
   border-color: #007bff;
   color: #0056b3;
}

.custom-pagination .page-item.disabled .page-link:hover {
   background-color: #f8f9fa;
   border-color: #d6d8db;
   color: #d6d8db;
}

.custom-pagination .pagination .page-item:not(.disabled):hover .page-link {
   box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
}

</style>
