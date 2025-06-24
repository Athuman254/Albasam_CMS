<template>
   <div class="card">
      <div class="card-header flex-column flex-md-row">
         <div class="row row-gap-1">
            <div class="col-md-3 col-9">
               <input type="search" id="search" class="form-control bg-muted-lt rounded-2" placeholder="Search..."
                      @input="applyFilter" v-model="appendParams.filter.name">
            </div>
            <div class="col-md-6 col-3 ms-lg-auto">
               <div class="flex-wrap text-end">
                  <div class="card-action">
                     <button type="button" class="btn btn-primary d-none d-sm-inline-block"
                             @click="showCreateBlogCategoryModal">
                        <i class="bx bx-plus-circle me-2"></i>
                        Add Blog Category
                     </button>
                     
                     <button type="button" class="btn btn-primary btn-icon d-sm-none"
                             @click="showCreateBlogCategoryModal">
                        <i class="bx bx-plus"></i>
                     </button>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <VueTable
         :fields="fields"
         api-url="datatable/blog-categories"
         :append-params="appendParams"
         ref="blogCategoriesTable"
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
                  <a class="dropdown-item" href="#" @click="editBlogCategory(props.rowData)">
                     <i class="bx bx-edit-alt me-2"></i>Edit
                  </a>
                  <a class="dropdown-item text-danger" href="#">
                     <i class="bx bx-trash me-2"></i>Delete
                  </a>
               </div>
            </div>
         </template>
      </VueTable>
   </div>
   
   <!-- Create Modal -->
   <div
      class="modal fade"
      id="create-blog-category-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="create-blog-category-modal-label"
      aria-hidden="true"
      ref="createBlogCategoryModal"
   >
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="create-blog-category-modal-label">Add Blog Category</h5>
               <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                  @click.prevent="formCleanUp"
               ></button>
            </div>
            <div class="modal-body">
               <form id="createForm" @submit.prevent="createBlogCategory">
                  <div class="mb-3">
                     <label for="name" class="form-label">Name</label>
                     <input id="name" type="text" v-model="form.name" class="form-control">
                     <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
                  </div>
                  
                  <div class="mb-3">
                     <label class="row d-flex">
                                       <span class="col">
                                           <span class="fw-bold me-3">Activate</span>
                                       </span>
                        <span class="col-auto">
                                           <label class="form-check form-switch">
                                               <input v-model="form.activated" class="form-check-input" type="checkbox">
                                           </label>
                                       </span>
                        <span class="form-check-description">When enabled, the category will be used during students' admission process.</span>
                     </label>
                     <div v-if="form.errors.activated" class="text-danger">{{ form.errors.activated }}</div>
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
                  @click.prevent="createBlogCategory"
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
      id="edit-blog-category-modal"
      data-bs-backdrop="static"
      tabindex="-1"
      aria-labelledby="edit-blog-category-modal-label"
      aria-hidden="true"
      ref="editBlogCategoryModal"
   >
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="edit-blog-category-modal-label">Edit Blog Category</h5>
               <button
                  type="button"
                  class="btn-close"
                  data-bs-dismiss="modal"
                  aria-label="Close"
                  @click="editFormCleanUp"
               ></button>
            </div>
            <div class="modal-body">
               <form id="createForm" @submit.prevent="updateBlogCategory">
                  <div class="mb-3">
                     <label for="name" class="form-label">Name</label>
                     <input id="name" type="text" v-model="editForm.name" class="form-control">
                     <div v-if="editForm.errors.name" class="text-danger">{{ editForm.errors.name }}</div>
                  </div>
                  
                  <div class="mb-3">
                     <label class="row d-flex">
                                       <span class="col">
                                           <span class="fw-bold me-3">Activate</span>
                                       </span>
                        <span class="col-auto">
                                           <label class="form-check form-switch">
                                               <input v-model="editForm.activated" class="form-check-input"
                                                      type="checkbox">
                                           </label>
                                       </span>
                        <span class="form-check-description">When enabled, the category will be used during students' admission process.</span>
                     </label>
                     <div v-if="editForm.errors.activated" class="text-danger">{{ editForm.errors.activated }}</div>
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
                  @click.prevent="updateBlogCategory"
               >
                  Submit
               </button>
            </div>
         </div>
      </div>
   </div>
</template>

<script>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import {Link, Head, useForm} from "@inertiajs/vue3";
import {Modal} from 'bootstrap';
import _debounce from "lodash/debounce.js";

export default {
   components: {DefaultLayout, Head, Link},
   data() {
      return {
         fields: [
            {
               name: 'name',
               title: 'NAME',
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
               name: '',
            }
         },
         form: useForm({
            name: '',
            activated: '',
         }),
         editForm: useForm({
            id: '',
            name: '',
            activated: '',
         }),
      };
   },
   methods: {
      showCreateBlogCategoryModal() {
         const modalElement = this.$refs.createBlogCategoryModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      createBlogCategory() {
         this.form.post(route('admin.blog_categories.store'), {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               this.$refs.blogCategoriesTable.reloadTable();
               const modalElement = this.$refs.createBlogCategoryModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Category Created Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      editBlogCategory(rowData) {
         this.editForm.id = rowData.hashid;
         this.editForm.name = rowData.name;
         this.editForm.activated = rowData.activated;
         
         const modalElement = this.$refs.editBlogCategoryModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      updateBlogCategory() {
         this.editForm.patch(route('admin.blog_categories.update', this.editForm.id), {
            onSuccess: () => {
               this.editForm.reset();
               this.editForm.clearErrors();
               this.$refs.blogCategoriesTable.reloadTable();
               const modalElement = this.$refs.editBlogCategoryModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Category Updated Successfully', 'Success')
            },
            onError: (errors) => {
               console.log(errors);
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         })
      },
      applyFilter: _debounce(function () {
         this.$refs.blogCategoriesTable.reloadTable();
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
