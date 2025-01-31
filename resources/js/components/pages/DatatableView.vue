<script>
import DefaultLayout from '../layouts/DefaultLayout.vue';
import { VueTable } from './Datable.vue';

export default {
    layout: DefaultLayout,
    components: { VueTable },
    data() {
        return {
            fields: [{
                name: "__slot:name",
                title: 'Name',
                titleClass: 'text-end',
                dataClass: 'text-end'
            },
            {
                name: "email",
                title: 'Email'
            },
            {
                name: "role",
                title: 'Role'
            },
            {
                name: "role",
                title: 'Role'
            },
            {
                name: "role",
                title: 'Role'
            },
            {
                name: "role",
                title: 'Role'
            },
            {
                name: "role",
                title: 'Role'
            },
            {
                name: "next_of_kin.name",
                title: 'next_of_kin'
            },
            {
                name: "__slot:actions",
                titleClass: "text-center",
                dataClass: "text-left",
            }
            ],
            appendParams: { filter: {search: '',per_page: 20} }
        };
    },
    methods: {
        editRow(row) {
            alert(`Editing: ${JSON.stringify(row)}`);
        },
        deleteRow(row) {
            alert(`Deleting: ${JSON.stringify(row)}`);
        },
        applyFilter(){
            if (this.$refs.table) {
                console.log(this.$refs.table)
         this.$refs.table.reloadTable();
        }
        }
    }
};
</script>

<template>
    <div class="row">
        <div class="col-xxl-12">
            <h3>Welcome to the Dashboard</h3>
        </div>
        <div class="table-wrapper card border-0 shadow-none">
            <div class="card-header ">

            </div>
            <h3 class="text-center mb-4">DataTable</h3>
            <div class="card-header">
                <div class="table-controls">
                  <div class="col-lg-2 col-6">
                    <div class="d-flex align-items-center">
                    <label for="entriesCount" class="form-label">Show:</label>
                    <select @change="applyFilter" v-model="appendParams.filter.per_page" id="entriesCount" class="form-select">
                        <option value="5">5</option>
                        <option value="30">30</option>
                        <option value="40">40</option>
                        <option value="50">50</option>
                        <option value="60">60</option>
                    </select>
                </div>
                  </div>

                <input type="text" id="searchBox" class="form-control" placeholder="Search...">
            </div>
        <div class="input-icon me-2">
                    <span class="input-icon-addon">
                        <i class="uil uil-search"></i>
                    </span>
          <input type="text" class="form-control bg-muted-lt rounded-2" placeholder="Search"
          @input="applyFilter" v-model="appendParams.filter.search" >
        </div>
      </div>
            <vue-table ref="table" api-url="data-with-kin" :fields="fields" :append-params="appendParams">

                <template v-slot:name="props">
                    <div class="media">
                        <div class="align-self-center">
                            <span class="text-sm text-danger">{{ props.rowData.name }}</span>
                        </div>
                    </div>
                </template>
                <!-- <template v-slot:next_of_kin="props">
                    <div class="media">
                        <div class="align-self-center">
                            <span class="text-sm text-danger">{{ props.rowData.next_of_kin[0].name }}</span>
                        </div>
                    </div>
                </template> -->
                <template v-slot:actions="props">
                    <div class="dropdown">
                        <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                            <i class="uil uil-ellipsis-h"></i> dsdsd
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="#" @click="editRow(props.rowData)">
                                <i class="uil uil-edit me-2"></i>Edit
                            </a>
                            <a class="dropdown-item text-danger" href="#" @click="deleteRow(props.rowData)">
                                <i class="uil uil-trash me-2"></i>Delete
                            </a>
                        </div>
                    </div>
                </template>

            </vue-table>
        </div>
    </div>
</template>
