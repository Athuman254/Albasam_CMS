<template>
    <div class="row">
        <h3 class="mb-0">User Permissions</h3>
        <nav class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <Link href="/admin/dashboard">Home</Link>
                </li>
                <li class="breadcrumb-item">
                    Registered Permissions
                </li>
            </ol>
        </nav>

        <div class="col-xxl-12">
            <div class="card">
                <div class="card-header flex-column flex-md-row">
                    <div class="row row-gap-1">
                        <div class="col-md-3 col-9">
                            <input type="search" id="search" class="form-control bg-muted-lt rounded-2" placeholder="Search Permissions"
                                   @input="applyFilter" v-model="appendParams.filter.display_name" >
                        </div>
                        <div class="col-md-6 col-3 ms-lg-auto">
                            <div class="flex-wrap text-end">
                                <div class="card-action">
                                    <button type="button" class="btn btn-primary d-none d-sm-inline-block" @click="showCreatePermissionModal">
                                        <i class="bx bx-plus-circle me-2"></i>
                                        Add Permission
                                    </button>

                                    <button type="button" class="btn btn-primary btn-icon d-sm-none" @click="showCreatePermissionModal">
                                        <i class="bx bx-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <VueTable
                    :fields="fields"
                    api-url="datatable/permissions"
                    :append-params="appendParams"
                    ref="permissionsTable"
                >
                    <template #status="props">
                        <span v-if="props.rowData.activated" class="badge bg-success">
                            Active
                        </span>
                        <span v-else-if="!props.rowData.activated" class="badge bg-danger">
                            Deactivated
                        </span>
                        <span v-else class="badge bg-secondary">
                            Unknown
                        </span>
                    </template>

                    <template #actions="props">
                        <div class="dropdown">
                            <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#" @click="editPermission(props.rowData)">
                                    <i class="bx bx-edit-alt me-2"></i>Edit
                                </a>
                            </div>
                        </div>
                    </template>
                </VueTable>
            </div>
        </div>

        <!-- Create Modal -->
        <div
            class="modal fade"
            id="create-permission-modal"
            tabindex="-1"
            aria-labelledby="create-permission-modal-label"
            aria-hidden="true"
            ref="createPermissionModal"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="create-permission-modal-label">Add Permission</h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <form id="createForm" @submit.prevent="createPermission">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input id="name" type="text" v-model="form.name" class="form-control">
                                <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Code</label>
                                <input id="name" type="text" v-model="form.code" class="form-control">
                                <div v-if="form.errors.code" class="text-danger">{{ form.errors.code }}</div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" rows="3" v-model="form.description" class="form-control"></textarea>
                                <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary me-2"
                            data-bs-dismiss="modal"
                        >
                            Close
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary"
                            @click.prevent="createPermission"
                        >
                            Submit
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div
            class="modal fade"
            id="edit-permission-modal"
            tabindex="-1"
            aria-labelledby="edit-permission-modal-label"
            aria-hidden="true"
            ref="editPermissionModal"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="create-permission-modal-label">Edit Permission</h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"
                        ></button>
                    </div>
                    <div class="modal-body">
                        <form id="createForm" @submit.prevent="updatePermission">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input id="name" type="text" v-model="editForm.name" class="form-control">
                                <div v-if="editForm.errors.name" class="text-danger">{{ editForm.errors.name }}</div>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Code</label>
                                <input id="name" type="text" v-model="editForm.code" class="form-control">
                                <div v-if="editForm.errors.code" class="text-danger">{{ editForm.errors.code }}</div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" rows="3" v-model="editForm.description" class="form-control"></textarea>
                                <div v-if="editForm.errors.name" class="text-danger">{{ editForm.errors.name }}</div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary me-2"
                            data-bs-dismiss="modal"
                        >
                            Close
                        </button>
                        <button
                            type="button"
                            class="btn btn-primary"
                            @click.prevent="updatePermission"
                        >
                            Update
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import _debounce from "lodash/debounce.js";
import { Modal } from 'bootstrap';
import { useForm } from "@inertiajs/vue3";

export default {
    data() {
        return {
            fields: [
                {
                    name: 'display_name',
                    title: 'NAME',
                },
                // {
                //     name: 'name',
                //     title: 'DESCRIPTION',
                // },
                {
                    name: 'code',
                    title: 'CODE',
                },
                {
                    name: '__slot:actions',
                    title: 'ACTIONS',
                    titleClass: 'text-end w-5',
                    dataClass: 'text-end w-5',
                },
            ],
            appendParams: {
                filter: {
                    display_name: '',
                }
            },
            form: useForm({
                name: '',
                code: '',
                description: '',
                activated: '',
            }),
            editForm: useForm({
                id: '',
                name: '',
                code: '',
                description: '',
                activated: '',
            }),
        };
    },
    methods: {
        showCreatePermissionModal() {
            const modalElement = this.$refs.createPermissionModal;
            const modalInstance = Modal.getOrCreateInstance(modalElement);
            modalInstance.show();
        },
        createPermission() {
            this.form.post('/admin/permissions', {
                onSuccess: () => {
                    this.form.reset(); // Reset the form on success
                    this.form.clearErrors();
                    this.$refs.permissionsTable.reloadTable();
                    const modalElement = this.$refs.createPermissionModal;
                    const modalInstance = Modal.getInstance(modalElement);
                    modalInstance.hide();
                    this.$toast.success('Permission Created Successfully', 'Success')
                },
                onError: (errors) => {
                    this.$toast.error('An error occurred. Please try again', 'Error')
                },
            });
        },
        editPermission(rowData) {
            this.editForm.id = rowData.id; // Assign the ID manually
            this.editForm.name = rowData.display_name;
            this.editForm.code = rowData.code;
            this.editForm.activated = rowData.activated;

            const modalElement = this.$refs.editPermissionModal;
            const modalInstance = Modal.getOrCreateInstance(modalElement);
            modalInstance.show();
        },
        updatePermission() {
            this.editForm.patch('/admin/permissions/' + this.editForm.id, {
                onSuccess: () => {
                    this.editForm.reset(); // Reset the form on success
                    this.editForm.clearErrors();
                    this.$refs.permissionsTable.reloadTable();
                    const modalElement = this.$refs.editPermissionModal;
                    const modalInstance = Modal.getInstance(modalElement);
                    modalInstance.hide();
                    this.$toast.success('Permission Updated Successfully', 'Success')
                },
                onError: (errors) => {
                    this.$toast.error('An error occurred. Please try again', 'Error')
                },
            })
        },
        applyFilter: _debounce(function () {
            this.$refs.permissionsTable.reloadTable();
        }, 800),
    },
}
</script>

<style scoped></style>
