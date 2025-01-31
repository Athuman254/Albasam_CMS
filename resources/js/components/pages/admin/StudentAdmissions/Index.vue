<template>
    <div class="row">
        <h3 class="mb-0">Student Admissions</h3>
        <nav class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <Link href="/admin/dashboard">Home</Link>
                </li>
                <li class="breadcrumb-item">
                    Student Admissions
                </li>
            </ol>
        </nav>

        <div class="col-xxl-12">
            <div class="card">
                <div class="card-header flex-column flex-md-row">
                    <div class="row row-gap-1">
                        <div class="col-md-3 col-9">
                            <input type="search" id="search" class="form-control bg-muted-lt rounded-2" placeholder="Search..."
                                   @input="applyFilter" v-model="appendParams.filter.admission_number" >
                        </div>
                        <div class="col-md-6 col-3 ms-lg-auto">
                            <div class="flex-wrap text-end">
                                <div class="card-action">
                                    <Link href="/admin/student-admissions/admission-form" class="btn btn-primary d-none d-sm-inline-block">
                                        <i class="bx bx-plus-circle me-2"></i>
                                        New Registration
                                    </Link>

                                    <Link class="btn btn-primary btn-icon d-sm-none" href="/admin/student-admissions/admission-form">
                                        <i class="bx bx-plus"></i>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <VueTable
                    api-url="datatable/student-admissions"
                    :fields="fields"
                    ref="admissionsTable"
                    :append-params="appendParams"
                >
                    <template v-slot:student="props">
                        {{ props.rowData.student.first_name }} {{ props.rowData.student.last_name }}
                    </template>
                    <template v-slot:actions="props">
                        <div class="dropdown">
                            <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">
                                    <i class="bx bx-detail me-2"></i> Details
                                </a>
                                <Link class="dropdown-item" :href="'/admin/student-admissions/' + props.rowData.hashid + '/edit'">
                                    <i class="bx bx-edit-alt me-2"></i> Edit
                                </Link>
<!--                                <a class="dropdown-item text-danger" href="#">-->
<!--                                    <i class="bx bx-trash me-2"></i> Delete-->
<!--                                </a>-->
                            </div>
                        </div>
                    </template>
                </VueTable>
            </div>
        </div>
    </div>
</template>

<script>
import _debounce from "lodash/debounce.js";

export default {
    data() {
        return {
            fields: [
                {
                    name: 'date',
                    title: 'DATE',
                    titleClass: 'font-weight-bold',
                    width: '20%',
                },
                {
                    name: 'admission_number',
                    title: 'ADMISSION NUMBER',
                    width: '20%',
                },
                {
                    name: '__slot:student',
                    title: 'STUDENT',
                    width: 'auto',
                },
                {
                    name: '__slot:actions',
                    title: 'ACTIONS',
                    titleClass: 'text-end',
                    dataClass: 'text-end',
                    width: '10%',
                },
            ],
            appendParams: {
                filter: {
                    admission_number: '',
                }
            },
        };
    },
    methods: {
        applyFilter: _debounce(function () {
            this.$refs.admissionsTable.reloadTable()
        }, 800),
    },
}
</script>

<style scoped>
</style>
