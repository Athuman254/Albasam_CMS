<script>
import { QuillEditor } from '@vueup/vue-quill'
import _debounce from 'lodash/debounce.js';
import axios from 'axios';

import '@vueup/vue-quill/dist/vue-quill.snow.css';
import DefaultLayout from '../../layouts/DefaultLayout.vue';
import { VueTable } from '../Datable.vue';
import { useForm } from "@inertiajs/vue3";
import { Modal } from 'bootstrap';
import { Inertia } from '@inertiajs/inertia';

export default {
    layout: DefaultLayout,

    components: { QuillEditor, VueTable },

    data() {
        return {
            fields: [
                {
                    name: '__slot:title',
                    title: 'Title',
                },
                {
                    name: 'category.name',
                    title: 'Category',
                },
                {
                    name: 'price',
                    title: 'Price',
                },
                {
                    name: 'status',
                    title: 'Status',
                },

                {
                    name: '__slot:created_at',
                    title: 'Start Date',
                },

                {
                    name: '__slot:actions',
                    title: 'ACTIONS',
                },
            ],
            imagePreview: null,
            appendParams: {
                filter: {
                    title: '',
                }
            },

            form: useForm({
                title: '',
                discription: '',
                content: '',
                featured_image: '',
                service_includes: '',
                price: '',
                vedio_src: '',
                category_id: '',
                status: '',
                has_started: false,
                starts_at: '',
            }),
            edit_mode: false,
            editForm: useForm({
                id: null,
                title: '',
                discription: '',
                content: '',
                featured_image: '',
                service_includes: '',
                price: '',
                vedio_src: '',
                category_id: '',
                status: '',
                has_started: false,
                starts_at: '',
            }),
            categoryForm: useForm({
                name: '',
            }),
            categories: [],
        };
    },
    created() {
        this.fetchCategory();


        // Re-fetch data when navigating back to this component
        Inertia.on('navigate', (event) => {
            if (event.detail.page.url === '/ranks') {
                this.fetchDivisions();
            }
        });
    },
    methods: {
        handleFileChange(e) {
            const file = e.target.files[0]
            if (file) {
                if(!this.edit_mode){
                    this.form.featured_image = file
                }else{
                    this.editForm.featured_image = file
                }

                this.imagePreview = URL.createObjectURL(file)
            }
        },
        fetchCategory() {
            axios.get('/admin/datatable/blog-categories')
                .then(({ data }) => {
                    this.categories = data.data;
                }).catch((error) => {
                    console.error(error)
                    this.$toast.error('An error occurred when fetching the blog posts.')
                })
        },

        createCategoryModal() {
            const modal = this.$refs.addCategoryModal
            const instance = Modal.getOrCreateInstance(modal)
            instance.show()
        },
        createServiceModal() {
            const modalElement = this.$refs.createServiceModal;
            const modalInstance = Modal.getOrCreateInstance(modalElement);
            modalInstance.show();
        },
        addCategory() {
            const formData = new FormData()
            formData.append('name', this.categoryForm.name)
            axios.post('/website/pages/blogs/category', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
                .then(({ data }) => {
                    // let {id,name} = data
                    this.categories.push(data);
                    this.categoryForm.reset();
                    this.$toast.success('added')
                    const modal = this.$refs.addCategoryModal
                    const instance = Modal.getOrCreateInstance(modal)
                    instance.hide()
                }).catch((error) => {
                    let er = error.response?.data?.message || error.message || "An error occurred";
                    console.error(error)
                    this.$toast.error(er)
                })
        },
        createService() {
            this.form.post('/website/pages/services', {
                onSuccess: () => {
                    this.form.reset(); // Reset the form on success
                    this.form.clearErrors();
                    this.imagePreview = null
                    this.$refs.classTable.reloadTable();
                    const modalElement = this.$refs.createServiceModal;
                    const modalInstance = Modal.getInstance(modalElement);
                    modalInstance.hide();
                    this.$toast.success('Service Created Successfully', 'Success')
                },
                onError: (errors) => {
                    this.$toast.error('An error occurred. Please try again', 'Error')
                },
            });
        },
        deleteServiceM(rowData){
            this.editForm.id = rowData.id;
            const modalElement = this.$refs.deleteServiceModal;
            const modalInstance = Modal.getOrCreateInstance(modalElement);
            modalInstance.show();
        },
        editService(rowData) {
            console.log(rowData)
            this.editForm.id = rowData.id;
            this.editForm.title = rowData.title;
            this.editForm.discription = rowData.discription;
            this.editForm.content = rowData.content;
            this.editForm.featured_image = rowData.featured_image;
            this.editForm.service_includes = rowData.service_includes;
            this.editForm.price = rowData.price;
            this.editForm.vedio_src = rowData.vedio_src;
            this.editForm.category_id = rowData.category_id;
            this.editForm.status = rowData.status;
            this.editForm.has_started = rowData.has_started;
            this.editForm.starts_at = rowData.starts_at;
            this.edit_mode = !this.edit_mode
            this.imagePreview = '/storage/'+ rowData.featured_image
            const modalElement = this.$refs.editServiceModal;
            const modalInstance = Modal.getOrCreateInstance(modalElement);
            modalInstance.show();
        },
        copyObject(sourceObj, targetObj) {

const sourceKeys = Object.keys(sourceObj);
const targetKeys = Object.keys(targetObj);
const matchingKeys = sourceKeys.filter(key => targetKeys.includes(key));
const result = { ...targetObj };
matchingKeys.forEach(key => {
    result[key] = sourceObj[key];
});

return result;
},
        updateService() {
            this.editForm.put('/website/pages/services/' + this.editForm.id, {
                onSuccess: () => {
                    this.editForm.reset();
                    this.editForm.clearErrors();
                    this.$refs.classTable.reloadTable();
                    this.edit_mode = !this.edit_mode
                    this.imagePreview = null
                    const modalElement = this.$refs.editServiceModal;
            const modalInstance = Modal.getOrCreateInstance(modalElement);
            modalInstance.hide();
                    this.$toast.success('Class Updated Successfully', 'Success')
                },
                onError: (errors) => {
                    this.$toast.error('An error occurred. Please try again', 'Error')
                },
            })
        },
        deleteService(){
            this.editForm.delete('/website/pages/services/' + this.editForm.id, {
                onSuccess: () => {

                    this.$refs.classTable.reloadTable();
                    const modalElement = this.$refs.deleteServiceModal;
                    const modalInstance = Modal.getOrCreateInstance(modalElement);
                    this.imagePreview = null
            modalInstance.hide();
                    this.$toast.success('Deleted', 'Success')
                    this.editForm.reset();
                    this.editForm.clearErrors();
                },
                onError: (errors) => {
                    this.$toast.error('An error occurred. Please try again', 'Error')
                },
            })
        },
        formatDate(dateStr) {

            const date = new Date(dateStr);
            const options = { day: '2-digit', month: 'short', year: 'numeric' };
            const formatter = new Intl.DateTimeFormat('en-GB', options);
            const formattedDate = formatter.format(date);
            const finalFormattedDate = formattedDate.replace(/\//g, '-');
            return finalFormattedDate;

        },

        applyFilter: _debounce(function () {
            this.$refs.classTable.reloadTable()
        }, 800),
    },
}
</script>

<template>
    <div class="row">
        <div class="col-xxl-12">
            <h3>Services</h3>

            <div class="card">
                <div class="card-header flex-column flex-md-row">
                    <div class="row row-gap-1">
                        <div class="col-md-3 col-6">
                            <input type="search" id="search" class="form-control bg-muted-lt rounded-2"
                                placeholder="Search..." @input="applyFilter" v-model="appendParams.filter.title">
                        </div>
                        <div class="col-md-6 col-6 ms-lg-auto">
                            <div class="flex-wrap text-end">
                                <div class="card-action">
                                    <button type="button" class="btn btn-primary" @click="createServiceModal">
                                        new Service
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <VueTable api-url="datatable/services" :fields="fields" ref="classTable" :append-params="appendParams">

                    <template v-slot:title="props">
                        <div class="media">
                            <div class="align-self-center">
                                <span class="text-sm">{{ props.rowData.title.substr(0, 15) }}</span>
                            </div>
                        </div>
                    </template>
                    <template v-slot:created_at="props">
                        <div class="media">
                            <div class="align-self-center">
                                <span v-if="has_started" class="text-sm">{{ formatDate(props.rowData.created_at) }}</span>
                                <span v-else class="text-sm text-warning">{{ formatDate(props.rowData.starts_at) }}</span>
                            </div>
                        </div>
                    </template>
                    <template v-slot:actions="props">
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" @click="editService(props.rowData)" href="javascript:void(0);"><i class="bx bx-edit-alt me-1"></i>
                                    Edit</a>
                                <a @click="deleteServiceM(props.rowData)" class="dropdown-item text-red-500"  href="javascript:void(0);"><i
                                        class="bx bx-trash me-1"></i> Delete</a>
                            </div>
                        </div>
                    </template>
                </VueTable>
            </div>

            <!-- Create Modal -->
            <div class="modal fade" id="create-service-modal" tabindex="-1" aria-labelledby="create-modal-label"
                aria-hidden="true" ref="createServiceModal">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="create-rank-modal-label">Add new Service</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="createForm" @submit.prevent="createService">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Title</label>
                                    <input id="name" type="text" v-model="form.title" class="form-control">
                                    <div v-if="form.errors.title" class="text-danger">{{ form.errors.title }}</div>
                                </div>

                                <div class="mb-3">
                                    <label for="categoryId" class="form-label">Category</label>
                                    <v-select id="categoryId" v-model="form.category_id" :options="categories"
                                        label="name" :reduce="option => option.id"></v-select>
                                    <div v-if="form.errors.category_id" class="text-danger">{{ form.errors.category_id
                                        }}</div>

                                    <div class="mt-2 text-end text-blue-500">
                                        <a @click="createCategoryModal" href="#">
                                            add Category
                                        </a>

                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label">Short description</label>
                                    <textarea name="" class="form-control" id="" v-model="form.discription"></textarea>

                                    <div v-if="form.errors.discription" class="text-danger">{{ form.errors.discription }}</div>
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Service Includes</label>
                                    <textarea name="" placeholder="eg. include 1, include 2, ..." class="form-control" id="" v-model="form.service_includes"></textarea>

                                    <div v-if="form.errors.discription" class="text-danger">{{ form.errors.discription }}</div>
                                </div>
                                <div class="mb-3">
                                    <label for="streamId" class="form-label">Content</label>

                                    <quill-editor toolbar="full" contentType="html" theme="snow"
                                        v-model:content="form.content"
                                        :options="{ placeholder: 'Write down your blog post...' }" />
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Youtube video ID</label>
                                    <input id="name" type="text" v-model="form.vedio_src" class="form-control">
                                    <div v-if="form.errors.vedio_src" class="text-danger">{{ form.errors.vedio_src }}</div>
                                </div>
                                <div class="mb-3">
                                    <label for="teacherId" class="form-label">Featured Image</label>
                                    <div class="relative">
                                        <input type="file" id="image" @change="handleFileChange" class="hidden"
                                            accept="image/*" :required="!isEditing" />
                                        <label for="image"
                                            class="flex items-center justify-center w-full p-4 border-2 border-dashed rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                                            <div class="space-y-2 text-center">
                                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400"></i>
                                                <div class="text-sm text-gray-600">
                                                    <span class="text-blue-500">Click to upload</span>
                                                </div>
                                                <div class="text-xs text-gray-500">PNG, JPG up to 10MB</div>
                                            </div>
                                        </label>
                                        <div v-if="imagePreview" class="mt-2">
                                            <img :src="imagePreview" class="h-64 w-72 object-cover rounded-lg" />
                                        </div>
                                    </div>
                                    <div v-if="form.errors.featured_image" class="text-danger">{{ form.errors.featured_image }}
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center pt-4">
                                <span>Has started</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" v-model="form.has_started">
                                </div>
                            </div></div>
                                    </div>
                                    <div v-if="!form.has_started" class="col-lg-4">
                                        <div class="mb-3">
                                    <label for="status" class="form-label">start date</label>
                                    <input v-model="form.starts_at" class="form-control" type="datetime-local">

                                </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                    <label for="status" class="form-label">status</label>
                                    <v-select id="status" v-model="form.status" :options="['published', 'draft','archived']"
                                        label="name" :reduce="option => option"></v-select>

                                </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="mb-3">
                                    <label class="form-check-label" for="defaultCheck3"> Price</label>
                                    <input class="form-control" type="number" id="defaultCheck3"
                                            v-model="form.price" checked="">

                                </div>
                                </div>

                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="button" class="btn btn-primary" @click.prevent="createService">
                                Submit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Edit Modal -->
            <div class="modal fade" id="edit-blog-modal" tabindex="-1" aria-labelledby="create-rank-modal-label"
                aria-hidden="true" ref="editServiceModal">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="create-rank-modal-label">Edit Service</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editForm" @submit.prevent="updateService">
                                <div class="mb-3">
                                    <label for="name" class="form-label"> Title</label>
                                    <input id="name" type="text" v-model="editForm.title" class="form-control">
                                    <div v-if="editForm.errors.title" class="text-danger">{{ editForm.errors.title }}</div>
                                </div>

                                <div class="mb-3">
                                    <label for="categoryId" class="form-label">Category</label>
                                    <v-select id="categoryId" v-model="editForm.category_id" :options="categories"
                                        label="name" :reduce="option => option.id"></v-select>
                                    <div v-if="editForm.errors.category_id" class="text-danger">{{ editForm.errors.category_id
                                        }}</div>

                                    <div class="mt-2 text-end text-blue-500">
                                        <a @click="createCategoryModal" href="#">
                                            add Category
                                        </a>

                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Short description</label>
                                    <textarea name="" class="form-control" id="" v-model="editForm.discription"></textarea>

                                    <div v-if="editForm.errors.discription" class="text-danger">{{ editForm.errors.discription }}</div>
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Service Includes</label>
                                    <textarea name="" placeholder="eg. include 1, include 2, ..." class="form-control" id="" v-model="editForm.service_includes"></textarea>

                                    <div v-if="editForm.errors.discription" class="text-danger">{{ editForm.errors.discription }}</div>
                                </div>
                                <div class="mb-3">
                                    <label for="streamId" class="form-label">Content</label>

                                    <quill-editor toolbar="full" contentType="html" theme="snow"
                                        v-model:content="editForm.content"
                                        :options="{ placeholder: 'Write down your blog post...' }" />
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Youtube video ID</label>
                                    <input id="name" type="text" v-model="editForm.vedio_src" class="form-control">
                                    <div v-if="editForm.errors.vedio_src" class="text-danger">{{ editForm.errors.vedio_src }}</div>
                                </div>
                                <div class="mb-3">
                                    <label for="teacherId" class="form-label">Featured Image</label>
                                    <div class="relative">
                                        <input type="file" id="image" @change="handleFileChange" class="hidden"
                                            accept="image/*" :required="!isEditing" />
                                        <label for="image"
                                            class="flex items-center justify-center w-full p-4 border-2 border-dashed rounded-lg cursor-pointer hover:border-blue-500 transition-colors">
                                            <div class="space-y-2 text-center">
                                                <i class="fas fa-cloud-upload-alt text-3xl text-gray-400"></i>
                                                <div class="text-sm text-gray-600">
                                                    <span class="text-blue-500">Click to upload</span>
                                                </div>
                                                <div class="text-xs text-gray-500">PNG, JPG up to 10MB</div>
                                            </div>
                                        </label>
                                        <div v-if="imagePreview" class="mt-2">
                                            <img :src="imagePreview" class="h-64 w-72 object-cover rounded-lg" />
                                        </div>
                                    </div>
                                    <div v-if="editForm.errors.teacher_id" class="text-danger">{{ editForm.errors.teacher_id }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="status" class="form-label">status</label>
                                    <v-select id="status" v-model="editForm.status" :options="['published', 'draft']"
                                        label="name" :reduce="option => option"></v-select>

                                </div>
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center pt-4">
                                <span>Has started</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" v-model="editForm.has_started">
                                </div>
                            </div></div>
                                    </div>
                                    <div v-if="!editForm.has_started" class="col-lg-4">
                                        <div class="mb-3">
                                    <label for="status" class="form-label">start date</label>
                                    <input v-model="editForm.starts_at" class="form-control" type="datetime-local">

                                </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="mb-3">
                                    <label for="status" class="form-label">status</label>
                                    <v-select id="status" v-model="editForm.status" :options="['published', 'draft','archived']"
                                        label="name" :reduce="option => option"></v-select>

                                </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button type="button" class="btn btn-primary" @click.prevent="updateService">
                                Update
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div id="add-category-modal" ref="addCategoryModal" class="modal fade" tabindex="-1" style="display: none;"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalCenterTitle">Add Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col mb-6">
                                    <label for="nameWithTitle" class="form-label">Category Name</label>
                                    <input type="text" v-model="categoryForm.name" id="nameWithTitle"
                                        class="form-control" placeholder="Enter Name">
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button @click="addCategory" type="button" class="btn btn-primary">Save changes</button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="delete-modal" ref="deleteServiceModal" class="modal fade" tabindex="-1" style="display: none;"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalCenterTitle">Deletion</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <p>This can not be undone</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                Close
                            </button>
                            <button @click="deleteService" type="button" class="btn btn-primary">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
