<template>
   <div class="row">
      <h3 class="mb-0">Website Pages</h3>
      <nav class="mb-3">
         <ol class="breadcrumb">
            <li class="breadcrumb-item">
               <Link href="/admin/dashboard">Home</Link>
            </li>
            <li class="breadcrumb-item">
               Website Settings
            </li>
            <li class="breadcrumb-item text-primary">
               Website Pages
            </li>
         </ol>
      </nav>
      
      <div class="col-xxl-12">
         <div class="card">
            <div class="card-header flex-column flex-md-row">
               <div class="row row-gap-1">
                  <div class="col-md-3 col-9">
                     <input type="search" id="search" class="form-control bg-muted-lt rounded-2"
                            placeholder="Search Pages"
                            @input="applyFilter" v-model="appendParams.filter.title">
                  </div>
                  <div class="col-md-6 col-3 ms-lg-auto">
                     <div class="flex-wrap text-end">
                        <div class="card-action">
                           <button type="button" class="btn btn-primary d-none d-sm-inline-block" @click="createPage">
                              <i class="bx bx-plus-circle me-2"></i>
                              Add Page
                           </button>
                           
                           <button type="button" class="btn btn-primary btn-icon d-sm-none" @click="createPage">
                              <i class="bx bx-plus"></i>
                           </button>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
            <VueTable
               :fields="fields"
               api-url="datatable/website/pages"
               :append-params="appendParams"
               ref="pagesTable"
            >
               <template #status="props">
                        <span v-if="props.rowData.is_published" class="badge bg-success">
                            Published
                        </span>
                  <span v-else-if="!props.rowData.is_published" class="badge bg-danger">
                            Draft
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
                        <a class="dropdown-item" href="#" @click="editPage(props.rowData)">
                           <i class="bx bx-edit-alt me-2"></i>Edit
                        </a>
                        <Link class="dropdown-item"
                              :href="'/admin/website/pages/' + props.rowData.hashid + (props.rowData.sections.length ? '/edit-sections' : '/create-sections')">
                           <i class="bx bx-detail me-2"></i> Page Sections
                        </Link>
                        <a class="dropdown-item text-danger" href="#">
                           <i class="bx bx-trash me-2"></i>Delete
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
         id="create-page-modal"
         data-bs-backdrop="static"
         tabindex="-1"
         aria-labelledby="create-page-modal-label"
         aria-hidden="true"
         ref="createPageModal"
      >
         <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="create-page-modal-label">Add Page</h5>
                  <button
                     type="button"
                     class="btn-close"
                     data-bs-dismiss="modal"
                     aria-label="Close"
                     @click.prevent="formCleanUp"
                  ></button>
               </div>
               <div class="modal-body">
                  <form id="createForm" @submit.prevent="storePage">
                     <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input id="title" type="text" v-model="form.title" class="form-control">
                        <div v-if="form.errors.title" class="text-danger">{{ form.errors.title }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="content" class="form-label">Description</label>
                        <textarea id="content" rows="3" v-model="form.content" class="form-control"></textarea>
                        <div v-if="form.errors.content" class="text-danger">{{ form.errors.content }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label class="row d-flex">
                                    <span class="col">
                                        <span class="fw-bold me-3">Publish</span>
                                    </span>
                           <span class="col-auto">
                                        <label class="form-check form-switch">
                                            <input v-model="form.is_published" class="form-check-input" type="checkbox">
                                        </label>
                                    </span>
                           <span class="form-check-description">When enabled, the page will appear on the website</span>
                        </label>
                        <div v-if="form.errors.is_published" class="text-danger">{{ form.errors.is_published }}</div>
                     </div>
                  </form>
               </div>
               <div class="modal-footer">
                  <button
                     type="button"
                     class="btn btn-secondary me-2"
                     data-bs-dismiss="modal"
                     @click="formCleanUp"
                  >
                     Close
                  </button>
                  <button
                     type="button"
                     class="btn btn-primary"
                     @click.prevent="storePage"
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
         id="edit-page-modal"
         data-bs-backdrop="static"
         tabindex="-1"
         aria-labelledby="edit-page-modal-label"
         aria-hidden="true"
         ref="editPageModal"
      >
         <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="edit-page-modal-label">Edit Page</h5>
                  <button
                     type="button"
                     class="btn-close"
                     data-bs-dismiss="modal"
                     aria-label="Close"
                     @click="editFormCleanUp"
                  ></button>
               </div>
               <div class="modal-body">
                  <form id="createForm" @submit.prevent="updatePage">
                     <div class="mb-3">
                        <label for="name" class="form-label">Title</label>
                        <input id="name" type="text" v-model="editForm.title" class="form-control">
                        <div v-if="editForm.errors.title" class="text-danger">{{ editForm.errors.title }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="slug" class="form-label">Page Slug</label>
                        <input id="slug" type="text" v-model="editForm.slug" class="form-control">
                        <div v-if="editForm.errors.slug" class="text-danger">{{ editForm.errors.slug }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label for="content" class="form-label">Description</label>
                        <textarea id="content" rows="3" v-model="editForm.content" class="form-control"></textarea>
                        <div v-if="editForm.errors.content" class="text-danger">{{ editForm.errors.content }}</div>
                     </div>
                     
                     <div class="mb-3">
                        <label class="row d-flex">
                                    <span class="col">
                                        <span class="fw-bold me-3">Publish</span>
                                    </span>
                           <span class="col-auto">
                                        <label class="form-check form-switch">
                                            <input v-model="editForm.is_published" class="form-check-input"
                                                   type="checkbox">
                                        </label>
                                    </span>
                           <span
                              class="form-check-description">When enabled, the page will appear on the website.</span>
                        </label>
                        <div v-if="editForm.errors.is_published" class="text-danger">{{
                              editForm.errors.is_published
                           }}
                        </div>
                     </div>
                  </form>
               </div>
               <div class="modal-footer">
                  <button
                     type="button"
                     class="btn btn-secondary me-2"
                     data-bs-dismiss="modal"
                     @click="editFormCleanUp"
                  >
                     Close
                  </button>
                  <button
                     type="button"
                     class="btn btn-primary"
                     @click.prevent="updatePage"
                  >
                     Submit
                  </button>
               </div>
            </div>
         </div>
      </div>
   </div>
</template>

<script>
import {useForm} from "@inertiajs/vue3";
import {Modal} from "bootstrap";
import _debounce from "lodash/debounce.js";

export default {
   data() {
      return {
         fields: [
            {
               name: 'title',
               title: 'TITLE',
            },
            {
               name: 'slug',
               title: 'SLUG',
            },
            {
               name: '__slot:status',
               title: 'STATUS',
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
               title: '',
            }
         },
         form: useForm({
            title: '',
            content: '',
            is_published: false,
         }),
         editForm: useForm({
            id: '',
            title: '',
            slug: '',
            content: '',
            is_published: false,
         }),
      }
   },
   methods: {
      createPage() {
         const modalElement = this.$refs.createPageModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      storePage() {
         this.form.post('/admin/website/pages', {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               this.$refs.pagesTable.reloadTable();
               const modalElement = this.$refs.createPageModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Page Created Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         })
      },
      editPage(rowData) {
         this.editForm.id = rowData.hashid;
         this.editForm.title = rowData.title;
         this.editForm.slug = rowData.slug;
         this.editForm.content = rowData.content;
         this.editForm.is_published = rowData.is_published;
         
         const modalElement = this.$refs.editPageModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      updatePage() {
         this.editForm.patch('/admin/website/pages/' + this.editForm.id, {
            onSuccess: () => {
               this.editForm.reset();
               this.editForm.clearErrors();
               this.$refs.pagesTable.reloadTable();
               const modalElement = this.$refs.editPageModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Page Updated Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         })
      },
      deletePage() {
         //
      },
      applyFilter: _debounce(function () {
         this.$refs.pagesTable.reloadTable();
      }, 800),
      formCleanUp() {
         this.form.reset()
      },
      editFormCleanUp() {
         this.editForm.reset()
      },
   },
}
</script>

<style></style>
