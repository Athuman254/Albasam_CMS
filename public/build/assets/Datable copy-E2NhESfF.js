const l={props:{apiUrl:{type:String,required:!0},fields:{type:Array,required:!0},appendParams:{type:Object,default:()=>({})}},data(){return{rows:[],loading:!1,error:null}},methods:{async fetchData(){var t,a;this.loading=!0,this.error=null;try{const e=new URLSearchParams(this.appendParams).toString(),s=`${this.apiUrl.startsWith("http")?this.apiUrl:`${window.location.origin}/${this.apiUrl}`}${e?"?"+e:""}`,r=await axios.get(s);this.rows=Array.isArray(r.data.data)?r.data.data:[]}catch(e){this.error=((a=(t=e.response)==null?void 0:t.data)==null?void 0:a.message)||e.message||"An error occurred",console.error("Error fetching data:",e)}finally{this.loading=!1}}},watch:{appendParams:{handler:"fetchData",deep:!0}},mounted(){this.fetchData()},template:`
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

    `},d={};export{l as VueTable,d as default};
