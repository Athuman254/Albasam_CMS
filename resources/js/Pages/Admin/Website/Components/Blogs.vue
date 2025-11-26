<template>
   <div class="card">
      <div class="card-header flex-column flex-md-row">
         <div class="row row-gap-1">
            <div class="col-md-3 col-9">
               <input type="search" id="search" class="form-control bg-muted-lt rounded-2" placeholder="Search Blog"
                      @input="applyFilter" v-model="appendParams.filter.title">
            </div>
            <div class="col-md-6 col-3 ms-auto">
               <div class="flex-wrap text-end">
                  <div class="card-action">
                     <button type="button" class="btn btn-primary d-none d-sm-inline-block"
                             @click="showCreateBlogModal">
                        <i class="icon-base bx bx-plus-circle me-2"></i>
                        Add Blog
                     </button>
                     <button type="button" class="btn btn-primary btn-icon d-sm-none"
                             @click="showCreateBlogModal">
                        <i class="icon-base bx bx-plus"></i>
                     </button>
                  </div>
               </div>
            </div>
         </div>
      </div>
      
      <VueTable
         :fields="fields"
         api-url="datatable/website/blogs"
         :append-params="appendParams"
         ref="blogsTable"
      >
         <template #status="props">
            <span v-if="props.rowData.active" class="badge bg-success">
               Active
            </span>
            <span v-else-if="!props.rowData.active" class="badge bg-danger">
               Deactivated
            </span>
            <span v-else class="badge bg-secondary">
               Unknown
            </span>
         </template>
         <template v-slot:actions="props">
            <div class="dropdown">
               <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                  <i class="icon-base bx bx-dots-vertical"></i>
               </button>
               <div class="dropdown-menu dropdown-menu-end">
                  <a class="dropdown-item" href="#" @click.prevent="editBlog(props.rowData)">
                     <i class="icon-base bx bx-edit-alt me-2"></i>Edit
                  </a>
                  <a class="dropdown-item" href="#" @click.prevent="openBlogMediaModal(props.rowData)">
                     <i class="icon-base bx bx-image-add me-2"></i>Change Image
                  </a>
                  <a class="dropdown-item text-danger" href="#" @click.prevent="deleteBlog(props.rowData)">
                     <i class="icon-base bx bx-trash me-2"></i>Delete
                  </a>
               </div>
            </div>
         </template>
      </VueTable>
      
      <!-- Modal -->
      <div
         class="modal fade"
         id="create-blog-modal"
         data-bs-backdrop="static"
         tabindex="-1"
         aria-labelledby="create-blog-modal-label"
         aria-hidden="true"
         ref="createBlogModal"
      >
         <div class="modal-dialog modal-xl modal-body-simple">
            <div class="modal-content">
               <div class="modal-header pb-5">
                  <h5 class="modal-title" id="create-blog-modal-label">Add Blog</h5>
                  <button
                     type="button"
                     class="btn-close"
                     data-bs-dismiss="modal"
                     aria-label="Close"
                     @click="formCleanUp"
                     :disabled="form.processing"
                  ></button>
               </div>
               <div class="modal-body">
                  <div id="createForm" class="row">
                     <div class="col-lg-6 col-12">
                        <div class="mb-3">
                           <label for="title" class="form-label">Title</label>
                           <input id="title" type="text" v-model="form.title" class="form-control">
                           <div v-if="form.errors.title" class="text-danger">{{ form.errors.title }}</div>
                        </div>
                     </div>
                     <div class="col-lg-6 col-12">
                        <div class="mb-3">
                           <label for="title" class="form-label">Category</label>
                           <v-select
                              id="pageId"
                              v-model="form.blog_category_id"
                              :options="categories"
                              label="name"
                              :reduce="option => option.id"
                           />
                           <div v-if="form.errors.blog_category_id" class="text-danger">{{ form.errors.blog_category_id }}</div>
                        </div>
                     </div>
                     <div class="col-lg-12 col-12">
                        <div class="mb-3">
                           <label for="BlogMedia" class="form-label">Blog Image</label>
                           <input id="BlogMedia" @change="handleMediaUpload" type="file" accept="image/*"
                                  class="form-control">
                        </div>
                     </div>
                     <div class="col-lg-12 col-12">
                        <div class="mb-3">
                           <label for="details" class="form-label">Details</label>
                           <editor v-model="form.details" id="editor" class="editor-control"/>
                           <div v-if="form.errors.details" class="text-danger">{{ form.errors.details }}</div>
                        </div>
                     </div>
                     <div class="col-lg-6 col-12">
                        <div class="mb-3">
                           <label class="row d-flex">
                                 <span class="col">
                                    <span class="fw-bold me-3">Active</span>
                                 </span>
                              <span class="col-auto">
                                    <label class="form-check form-switch">
                                       <input v-model="form.active" class="form-check-input" type="checkbox">
                                    </label>
                                 </span>
                              <span class="form-check-description">When enabled, the blog will be displayed in the system.</span>
                           </label>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="modal-footer pt-5">
                  <button
                     type="button"
                     class="btn btn-secondary me-2"
                     data-bs-dismiss="modal"
                     @click="formCleanUp"
                     :disabled="form.processing"
                  >
                     Close
                  </button>
                  <button
                     type="button"
                     class="btn btn-primary"
                     @click.prevent="createBlog"
                     :disabled="form.processing"
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
         id="edit-blog-modal"
         data-bs-backdrop="static"
         tabindex="-1"
         aria-labelledby="edit-blog-modal-label"
         aria-hidden="true"
         ref="editBlogModal"
      >
         <div class="modal-dialog modal-xl modal-body-simple">
            <div class="modal-content">
               <div class="modal-header pb-5">
                  <h5 class="modal-title" id="create-blog-modal-label">Edit Blog</h5>
                  <button
                     type="button"
                     class="btn-close"
                     data-bs-dismiss="modal"
                     aria-label="Close"
                     @click="formCleanUp"
                     :disabled="form.processing"
                  ></button>
               </div>
               <div class="modal-body">
                  <div id="createForm" class="row">
                     <div class="col-lg-12 col-12">
                        <div class="mb-3">
                           <label for="title" class="form-label">Title</label>
                           <input id="title" type="text" v-model="form.title" class="form-control">
                           <div v-if="form.errors.title" class="text-danger">{{ form.errors.title }}</div>
                        </div>
                     </div>
                     <div class="col-lg-6 col-12">
                        <div class="mb-3">
                           <label for="categoryId" class="form-label">Category</label>
                           <v-select
                              id="pageId"
                              v-model="form.blog_category_id"
                              :options="categories"
                              label="name"
                              :reduce="option => option.id"
                           />
                           <div v-if="form.errors.blog_category_id" class="text-danger">{{ form.errors.blog_category_id }}</div>
                        </div>
                     </div>
                     <div class="col-lg-6 col-12">
                        <div class="mb-3">
                           <label for="BlogMedia" class="form-label">Blog Slug</label>
                           <input id="slug" type="text" v-model="form.slug" class="form-control">
                           <div v-if="form.errors.slug" class="text-danger">{{ form.errors.slug }}</div>
                        </div>
                     </div>
                     <div class="col-lg-12 col-12">
                        <div class="mb-3">
                           <label for="details" class="form-label">Details</label>
                           <editor v-model="form.details" id="editor" class="editor-control"/>
                           <div v-if="form.errors.details" class="text-danger">{{ form.errors.details }}</div>
                        </div>
                     </div>
                     <div class="col-lg-6 col-12">
                        <div class="mb-3">
                           <label class="row d-flex">
                                 <span class="col">
                                    <span class="fw-bold me-3">Active</span>
                                 </span>
                              <span class="col-auto">
                                    <label class="form-check form-switch">
                                       <input v-model="form.active" class="form-check-input" type="checkbox">
                                    </label>
                                 </span>
                              <span class="form-check-description">When enabled, the blog will be displayed in the system.</span>
                           </label>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="modal-footer pt-5">
                  <button
                     type="button"
                     class="btn btn-secondary me-2"
                     data-bs-dismiss="modal"
                     @click="formCleanUp"
                     :disabled="form.processing"
                  >
                     Close
                  </button>
                  <button
                     type="button"
                     class="btn btn-primary"
                     @click.prevent="updateBlog"
                     :disabled="form.processing"
                  >
                     Submit
                  </button>
               </div>
            </div>
         </div>
      </div>
      
      <!-- Blog Media Upload Modal -->
      <div
         class="modal fade"
         id="blog-media-upload-modal"
         data-bs-backdrop="static"
         tabindex="-1"
         aria-labelledby="blog-media-upload-modal-label"
         aria-hidden="true"
         ref="blogMediaUploadModal"
      >
         <div class="modal-dialog  modal-body-simple">
            <div class="modal-content">
               <div class="modal-header pb-5">
                  <h5 class="modal-title" id="logo-upload-modal-label">Upload New Image</h5>
                  <button
                     type="button"
                     class="btn-close"
                     data-bs-dismiss="modal"
                     aria-label="Close"
                     @click="mediaModalCleanUp"
                     :disabled="mediaForm.processing"
                  ></button>
               </div>
               
               <div class="modal-body">
                  <form id="createForm" @submit.prevent="uploadMedia">
                     <div class="mb-3">
                        <h6>Current Image</h6>
                        <div v-if="selectedMedia.id" class="card card-img p-5">
                           <img :src="selectedMedia.original_url" alt="image" style="width:250px; height:auto;">
                        </div>
                        <p v-else class="text-muted">
                           No Image found for this blog
                        </p>
                     </div>
                     <div class="mb-3">
                        <label for="title" class="form-label">New Image</label>
                        <input
                           @change="handleMediaChange"
                           id="blogImage"
                           type="file"
                           accept="image/*"
                           required
                           class="form-control mb-3"
                        />
                        <div v-if="mediaForm.errors.file" class="text-danger">{{ mediaForm.errors.file }}</div>
                     </div>
                     <button type="button" class="btn btn-success" @click.prevent="uploadMedia"
                             :disabled="mediaForm.processing">
                        Upload Image
                     </button>
                  </form>
               </div>
            </div>
         </div>
      </div>
   </div>
</template>

<script>
import {useForm} from "@inertiajs/vue3";
import {Modal} from 'bootstrap';
import _debounce from "lodash/debounce.js";
import Editor from '@/Components/global/Editor.vue'
import axios from "axios";

export default {
   components: {Editor},
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
               titleClass: '5%',
               dataClass: '5%',
            },
         ],
         appendParams: {
            filter: {
               title: '',
            }
         },
         form: useForm({
            id: '',
            title: '',
            blog_category_id: null,
            slug: '',
            details: '',
            media: null,
            active: true,
         }),
         mediaForm: useForm({
            blog_id: '',
            file: null,
         }),
         categories: [],
         selectedMedia: [],
      }
   },
   created() {
      this.fetchCategories();
   },
   methods: {
      fetchCategories() {
         axios.get('/datatable/blog-categories', {
            params: {
               filter: {
                  'activated': true,
               }
            }
         })
            .then(({data}) => {
               this.categories = data.data;
            }).catch((error) => {
            console.log(error);
            // this.$toast.error("An error occurred while fetching the pages!", "Error");
         })
      },
      handleMediaUpload(event) {
         const file = event.target.files[0];
         if (file) {
            this.form.media = file;
         }
      },
      handleMediaChange(event) {
         const file = event.target.files[0];
         if (file) {
            this.mediaForm.file = file;
         }
      },
      showCreateBlogModal() {
         const modalElement = this.$refs.createBlogModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      createBlog() {
         this.form.post(route('admin.components.blogs.store'), {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               this.form.media = null;
               this.$refs.blogsTable.reloadTable();
               const modalElement = this.$refs.createBlogModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Blog Created Successfully', 'Success')
            },
            onError: (error) => {
               console.log(error)
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      editBlog(rowData) {
         this.form.id = rowData.hashid;
         this.form.blog_category_id = rowData.blog_category_id;
         this.form.title = rowData.title;
         this.form.slug = rowData.slug;
         this.form.details = rowData.details;
         this.form.active = rowData.active;
         
         const modalElement = this.$refs.editBlogModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      updateBlog() {
         this.form.patch(route('admin.components.blogs.update', this.form.id), {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               this.form.media = null;
               this.$refs.blogsTable.reloadTable();
               const modalElement = this.$refs.editBlogModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Blog details Updated Successfully', 'Success')
            },
            onError: (errors) => {
               console.log(errors);
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         })
      },
      openBlogMediaModal(blog) {
         const image = blog.media.find(m => m.collection_name === 'blog-image')
         if (image) {
            this.selectedMedia = image;
         }
         this.mediaForm.blog_id = blog.id;
         const modalElement = this.$refs.blogMediaUploadModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      uploadMedia() {
         this.mediaForm.post(route('admin.medias.upload.blog-image'), {
            headers: {
               "Content-Type": "multipart/form-data",
            },
            onSuccess: () => {
               this.mediaForm.reset();
               this.mediaForm.clearErrors();
               this.$refs.blogsTable.reloadTable();
               this.$toast.success('Image changed', 'Updated');
               const modalElement = this.$refs.blogMediaUploadModal;
               const modalInstance = Modal.getOrCreateInstance(modalElement);
               modalInstance.hide();
            },
            onError: (errors) => {
               console.log(errors);
               this.$toast.error('An error occurred. Please try again', 'Error');
            },
         })
      },
      deleteBlog(blog) {
         this.$toast.question('Are you sure?', `Deleting ${blog.title}`).then(() => {
            this.$inertia.delete(route('admin.components.blogs.destroy', blog.id), {
               onSuccess: () => {
                  this.$toast.success('Blog deleted', 'Success');
                  this.$refs.blogsTable.reloadTable();
               },
               onError: (error) => {
                  console.log(error)
                  this.$toast.error('An error occurred while deleting the blog!', 'Error');
               }
            })
         })
      },
      applyFilter: _debounce(function () {
         this.$refs.blogsTable.reloadTable();
      }, 800),
      formCleanUp() {
         this.form.reset();
         this.form.clearErrors();
      },
      mediaModalCleanUp() {
         this.mediaForm.reset();
         this.mediaForm.clearErrors();
         this.selectedMedia = [];
      },
   },
}
</script>

<style scoped></style>
