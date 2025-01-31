<script>

export const VueTable = {
    props: {
        apiUrl: {
            type: String,
            required: true,
        },
        fields: {
            type: Array,
            required: true,
        },
        appendParams: {
            type: Object,
            default: () => ({}),
        },
    },
    data() {
        return {
            rows: [],
            loading: false,
            error: null,
        };
    },
    methods: {
        async fetchData() {
            this.loading = true;
            this.error = null;

            try {
                const params = new URLSearchParams(this.appendParams).toString();
                const baseUrl = this.apiUrl.startsWith("http")
                    ? this.apiUrl
                    : `${window.location.origin}/${this.apiUrl}`;
                const url = `${baseUrl}${params ? "?" + params : ""}`;

                const response = await axios.get(url);
                this.rows = Array.isArray(response.data.data) ? response.data.data : [];
            } catch (err) {
                this.error = err.response?.data?.message || err.message || "An error occurred";
                console.error("Error fetching data:", err);
            } finally {
                this.loading = false;
            }
        },
    },
    watch: {
        appendParams: {
            handler: "fetchData",
            deep: true,
        },
    },
    mounted() {
        this.fetchData();
    },
    template: `
      <div>
        <div v-if="loading" class="text-center py-3">Loading...</div>
        <div v-else-if="error" class="text-center py-3 text-danger">Error: {{ error }}</div>
        <div v-else-if="!rows.length" class="text-center py-3">No data available.</div>
        <div v-else>
          <div class="table-responsive">
            <table class="table table-hover table-bordered">
              <thead>
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
  <tr v-for="row in rows" :key="row.id">
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
        </div>
      </div>

    `,
};
</script>
