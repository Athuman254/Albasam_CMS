<template>
   <Head title="Edit Page Section"/>
   
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">{{ page.title }}</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item">
                  <Link :href="route('admin.pages.index')">Pages</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  {{ page.title }} Page
               </li>
            </ol>
         </nav>
         
         <div class="col-xl-12 col-md-12">
            <div class="card">
               <div class="card-header border-bottom">
                  <h5 class="card-title mb-0">
                     Edit {{ page.title }} Page Sections
                  </h5>
               </div>
               <div class="card-body pt-6 border-bottom">
                  <div class="row">
                     <div class="col-12" v-for="(section, index) in form.sections" :key="index">
                        <div class="mb-8">
                           <div class="card crd-custom">
                              <div class="card-body">
                                 <div class="d-flex align-items-center mb-3">
                                    <h5 >Section {{ index + 1 }}</h5>
                                    
                                    <button v-if="index > 0" type="button" class="btn btn-sm btn-danger ms-5" @click="removeSection(index)">
                                       <i class="bx bx-trash"></i>
                                    </button>
                                 </div>
                                 <div class="row">
                                    <div class="col-lg-4 col-md-4 col-12">
                                       <div class="mb-3">
                                          <label for="sectionType" class="form-label">Section Type</label>
                                          <v-select
                                             id="sectionType"
                                             v-model="section.type"
                                             :options="sectionTypes"
                                             label="name"
                                             :reduce="option => option.value"
                                          />
                                          <div v-if="getSectionError(index, 'type')" class="text-danger">
                                             {{ getSectionError(index, 'type') }}
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-12">
                                       <div class="mb-3">
                                          <label for="sectionTitle" class="form-label">Section Title</label>
                                          <input v-model="section.title" type="text" class="form-control" id="sectionTitle" />
                                          <div v-if="getSectionError(index, 'title')" class="text-danger">
                                             {{ getSectionError(index, 'title') }}
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-lg-4 col-md-4 col-12">
                                       <div class="mb-3">
                                          <label for="sectionSubTitle" class="form-label">Section Sub-Title</label>
                                          <input v-model="section.sub_title" type="text" class="form-control" id="sectionSubTitle" />
                                          <div v-if="getSectionError(index, 'sub_title')" class="text-danger">
                                             {{ getSectionError(index, 'sub_title') }}
                                          </div>
                                       </div>
                                    </div>
                                    <div v-if="section.section_has_image" class="col-lg-6 col-md-6 col-12 my-5">
                                       <div class="mb-3">
                                          <div class="card crd-custom">
                                             <div class="card-body">
                                                <div v-if="section.media[0]">
                                                   <p class="text-muted d-md-flex d-block">
                                                      Section Image
                                                      <span v-if="section.type !== 'hero-section'" class="form-check form-switch ms-auto py-md-0 py-3">
                                                         <input v-model="section.section_image_first" class="form-check-input" type="checkbox" id="hasCtaButtons">
                                                         <label class="form-check-label" for="hasCtaButtons"> Show Image First </label>
                                                      </span>
                                                   </p>
                                                   <div class="card card-img p-5">
                                                      <img :src="section.media[0].original_url" alt="logo" style="width:120px; height:auto;">
                                                   </div>
                                                   <button type="button" class="btn btn-sm btn-outline-danger my-3 ms-3" @click.prevent="deleteMedia(section)">
                                                      Delete Image
                                                   </button>
                                                </div>
                                                <div v-else>
                                                   <div class="mb-3 form-group">
                                                      <label class="form-label-md mb-2 d-md-flex d-block" for="companyLogo">
                                                         Section Image
                                                         <span v-if="section.type !== 'hero-section'" class="form-check form-switch ms-auto py-md-0 py-3">
                                                            <input v-model="section.section_image_first" class="form-check-input" type="checkbox" id="hasCtaButtons">
                                                            <label class="form-check-label" for="hasCtaButtons"> Show Image First </label>
                                                         </span>
                                                      </label>
                                                      <input
                                                         @change="(e) => handleSectionMedia(e, section)"
                                                         id="sectionImage"
                                                         type="file"
                                                         accept="image/*"
                                                         required
                                                         class="form-control mb-3"
                                                      />
                                                      <button type="button" class="btn btn-success"  @click.prevent="uploadMedia" :disabled="mediaForm.processing">
                                                         Upload Image
                                                      </button>
                                                   </div>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-12">
                                       <div class="mb-5">
                                          <label for="sectionDetails" class="form-label">Section Details</label>
                                          <editor v-model="section.details" id="editor" class="editor-control" />
                                          <div v-if="getSectionError(index, 'details')" class="text-danger">
                                             {{ getSectionError(index, 'details') }}
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12">
                                       <div class="form-check form-switch my-5">
                                          <input v-model="section.has_cta_buttons" class="form-check-input" type="checkbox" id="hasCtaButtons">
                                          <label class="form-check-label" for="hasCtaButtons"> Has CTA Buttons </label>
                                          <br>
                                          <span class="text-muted">check this if you want to add Call To Action(CTA) buttons to the section</span>
                                          <div v-if="getSectionError(index, 'has_cta_buttons')" class="text-danger">
                                             {{ getSectionError(index, 'has_cta_buttons') }}
                                          </div>
                                       </div>
                                       <div v-if="section.has_cta_buttons" class="w-100">
                                          <div class="mb-3">
                                             <div class="table-responsive text-nowrap">
                                                <table class="table table-sm table-bordered">
                                                   <thead>
                                                   <tr>
                                                      <th v-if="section.cta_buttons.length <= 1" colspan="4">
                                                         <button type="button" class="btn btn-primary btn-sm" @click.prevent="openAddCtaButton(section)">
                                                            Add CTA Button
                                                         </button>
                                                      </th>
                                                      <th v-else colspan="4">
                                                         <span class="text-muted text-capitalize">You can only add 2 CTA buttons per section</span>
                                                      </th>
                                                   </tr>
                                                   </thead>
                                                   <thead>
                                                   <tr>
                                                      <th class="" style="width: 50%;">Page</th>
                                                      <th class="" style="width: 25%;">Button Text</th>
                                                      <th class="" style="width: 20%;">Button Style</th>
                                                      <th class="" style="width: 5%;"></th>
                                                   </tr>
                                                   </thead>
                                                   <tbody>
                                                   <tr v-for="(cta, ctaIndex) in section.cta_buttons" :key="ctaIndex">
                                                      <td>{{ cta.page.title }}</td>
                                                      <td>{{ cta.cta_button_text }}</td>
                                                      <td>{{ cta.cta_button_type }}</td>
                                                      <td>
                                                         <button type="button" class="btn btn-sm btn-icon btn-danger" @click.prevent="removeCtaButton(cta)">
                                                            <i class="bx bx-trash"></i>
                                                         </button>
                                                      </td>
                                                   </tr>
                                                   </tbody>
                                                </table>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-12 mt-3">
                        <button type="button" class="btn btn-light" @click="addSection">
                           <i class="bx bx-plus-circle me-3"></i>
                           Add Section
                        </button>
                     </div>
                  </div>
               </div>
               <div class="card-footer pt-5">
                  <div class="float-end">
                     <button type="button" class="btn btn-primary" @click.prevent="submitForm">Submit</button>
                  </div>
               </div>
            </div>
         </div>
         
         <!-- Create Modal -->
         <div
            class="modal fade"
            id="add-section-modal"
            data-bs-backdrop="static"
            tabindex="-1"
            aria-labelledby="add-section-modal-label"
            aria-hidden="true"
            ref="addSectionModal"
         >
            <div class="modal-dialog">
               <div class="modal-content">
                  <div class="modal-header">
                     <h5 class="modal-title" id="create-menu-modal-label">Add Section</h5>
                     <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        @click.prevent="sectionFormCleanUp"
                     ></button>
                  </div>
                  <div class="modal-body">
                     <form id="createForm" @submit.prevent="createSection">
                        <div class="mb-3">
                           <label for="sectionType" class="form-label">Section Type</label>
                           <v-select
                              id="sectionType"
                              v-model="sectionForm.type"
                              :options="sectionTypes"
                              label="name"
                              :reduce="option => option.value"
                           />
                        </div>
                        
                        <div class="mb-3">
                           <label for="sectionTitle" class="form-label">Section Title</label>
                           <input v-model="sectionForm.title" type="text" class="form-control" id="sectionTitle" />
                        </div>
                        
                        <div class="mb-3">
                           <label for="sectionSubTitle" class="form-label">Section Sub-Title</label>
                           <input v-model="sectionForm.sub_title" type="text" class="form-control" id="sectionSubTitle" />
                        </div>
                     </form>
                  </div>
                  
                  <div class="modal-footer">
                     <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" @click="sectionFormCleanUp">
                        Close
                     </button>
                     <button type="button" class="btn btn-primary" @click.prevent="createSection">
                        Add Section
                     </button>
                  </div>
               </div>
            </div>
         </div>
         
         <!-- Create Modal -->
         <div
            class="modal fade"
            id="add-cta-button-modal"
            data-bs-backdrop="static"
            tabindex="-1"
            aria-labelledby="add-cta-button-modal-label"
            aria-hidden="true"
            ref="addCtaButtonModal"
         >
            <div class="modal-dialog modal-body-simple">
               <div class="modal-content">
                  <div class="modal-header pb-5">
                     <h5 class="modal-title" id="create-menu-modal-label">Add CTA Button</h5>
                     <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        @click.prevent="formCleanUp"
                     ></button>
                  </div>
                  <div class="modal-body">
                     <form id="createForm" @submit.prevent="addCtaButton">
                        <div class="mb-3">
                           <label for="pageId" class="form-label">Page To Redirect To:</label>
                           <v-select
                              id="pageId"
                              v-model="ctaButtonForm.page_id"
                              :options="pages"
                              label="title"
                              :reduce="option => option.id"
                           />
                        </div>
                        
                        <div class="mb-3">
                           <label for="buttonText" class="form-label">Button Text</label>
                           <input type="text" v-model="ctaButtonForm.cta_button_text" class="form-control" id="buttonText" />
                        </div>
                        
                        <div class="mb-3">
                           <label for="buttonType" class="form-label">Button Type</label>
                           <v-select
                              id="buttonType"
                              v-model="ctaButtonForm.cta_button_type"
                              :options="ctaButtonTypes"
                              label="name"
                              :reduce="option => option.value"
                           />
                        </div>
                     </form>
                  </div>
                  <div class="modal-footer pt-5">
                     <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" @click="formCleanUp">
                        Close
                     </button>
                     <button type="button" class="btn btn-primary" @click.prevent="addCtaButton">
                        Add CTA
                     </button>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </DefaultLayout>
</template>

<script>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import {Head, Link, useForm} from "@inertiajs/vue3";
import axios from "axios";
import {Modal} from "bootstrap";
import Editor from "@/Components/global/Editor.vue";

export default {
   components: {DefaultLayout, Head, Link, Editor},
   props: {
      page: {
         type: Object,
         required: true,
      }
   },
   data() {
      return {
         form: useForm({
            page_id: this.page.id,
            sections: [
               {
                  id: '',
                  type: '',
                  sub_title: '',
                  title: '',
                  details: '',
                  section_image_first: false,
                  has_cta_buttons: false,
                  cta_buttons: {
                     section_id: '',
                     page: '',
                     cta_button_text: '',
                  },
                  section_has_image: false,
                  media: {
                     id: '',
                     section_id: '',
                     url: '',
                  },
               }
            ],
         }),
         sectionForm: useForm({
            page_id: '',
            type: '',
            sub_title: '',
            title: '',
            details: '',
            section_image_first: false,
            has_cta_buttons: false,
            section_has_image: false,
         }),
         mediaForm: useForm({
            section_id: '',
            file: null,
         }),
         ctaButtonForm: useForm({
            section_id: null,
            page_id: null,
            cta_button_text: '',
            cta_button_type: '',
         }),
         // sectionCardsForm: useForm({}),
         pages: [],
         pageSections: [],
         sectionTypes: [
            {value: 'hero-section', name: 'Hero Section'},
            {value: 'section-with-image', name: 'Section With Image'},
            {value: 'section-without-image', name: 'Section Without Image'},
            {value: 'section-with-services', name: 'Section With Services'},
            {value: 'section-with-faqs', name: 'Section With FAQs'},
            {value: 'section-with-skills', name: 'Section With Skills'},
         ],
         ctaButtonTypes: [
            {value: 'primary-btn', name: 'Primary Button'},
            {value: 'secondary-btn', name: 'Secondary Button'},
            {value: 'default-btn', name: 'Default Button'},
         ],
      }
   },
   watch: {
      'form.sections': {
         handler(sections) {
            sections.forEach((section, index) => {
               this.$watch(() => section.type, (newVal) => {
                  section.section_has_image = ['hero-section', 'section-with-image'].includes(newVal);
                  // if (!section.section_has_image) section.media = null;
               });
               
               this.$watch(() => section.has_cta_buttons, (newVal) => {
                  // if (!newVal) section.cta_buttons = [];
               });
            });
         },
         immediate: true,
         deep: true
      }
   },
   mounted() {
      this.fetchPages();
      this.fetchPageSections();
   },
   methods: {
      fetchPages() {
         axios.get(route('datatable.website.pages'), {
            params: {
               filter: {
                  'published': true,
               }
            }
         })
            .then(({data}) => {
               this.pages = data.data;
            }).catch((error) => {
            console.log(error);
            this.$toast.error("An error occurred while fetching the pages!", "Error");
         })
      },
      fetchPageSections() {
         axios.get(route('datatable.sections'), {
            params: {
               filter: {
                  'page_id': this.page.id,
               }
            }
         })
            .then(({data}) => {
               this.pageSections = data.data;
               if(this.pageSections && this.pageSections.length > 0) {
                  this.form.sections = [];
                  
                  this.form.sections = this.pageSections.map(section => ({
                     id: section.id,
                     type: section.type,
                     sub_title: section.sub_title,
                     title: section.title,
                     details: section.details,
                     section_image_first: section.section_image_first,
                     has_cta_buttons: section.has_cta_buttons,
                     cta_buttons: section.cta_buttons ?? [],
                     section_has_image: section.section_has_image,
                     media: section.media ?? null,
                  }))
               }
            }).catch((error) => {
            console.log(error);
            this.$toast.error("An error occurred while fetching the page sections!", "Error");
         })
      },
      // populateForm() {
      //    this.form.sections.splice(0);
      //
      //    this.form.sections = this.pageSections.map(section => ({
      //       id: section.id,
      //       type: section.type,
      //       sub_title: section.sub_title,
      //       title: section.title,
      //       details: section.details,
      //       section_image_first: section.section_image_first,
      //       has_cta_buttons: section.has_cta_buttons,
      //       cta_buttons: section.cta_buttons ?? [],
      //       section_has_image: section.section_has_image,
      //    }))
      //
      //    this.pageSections.forEach(section => {
      //       this.form.sections.push({
      //          type: section.type,
      //          sub_title: section.sub_title,
      //          title: section.title,
      //          details: section.details,
      //          section_image_first: section.section_image_first,
      //          has_cta_buttons: section.has_cta_buttons,
      //          cta_buttons: section.cta_buttons ?? [],
      //          section_has_image: section.section_has_image,
      //          media: null,
      //       });
      //    });
      // },
      addSection() {
         const modalElement = this.$refs.addSectionModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      removeSection(index) {
         this.form.sections.splice(index, 1);
      },
      openAddCtaButton(section) {
         this.ctaButtonForm.section_id = section.id;
         const modalElement = this.$refs.addCtaButtonModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      addCtaButton() {
         if (!this.ctaButtonForm.page_id) {
            this.$toast.info('Select a page first', 'Info');
            return;
         }
         if (!this.ctaButtonForm.cta_button_text) {
            this.$toast.info('Label the CTA button first', 'Info');
            return;
         }
         this.ctaButtonForm.post(route('cta-buttons.store'), {
            onSuccess: () => {
               this.ctaButtonForm.reset();
               this.ctaButtonForm.clearErrors();
               this.$toast.success('CTA button added!', 'Success');
               const modalElement = this.$refs.addCtaButtonModal;
               const modalInstance = Modal.getOrCreateInstance(modalElement);
               modalInstance.hide();
               this.fetchPageSections();
            },
            onError: (error) => {
               console.log(error);
               this.$toast.error('Failed to save CTA button! Please try again', 'Error');
            },
         })
      },
      formCleanUp() {
         this.ctaButtonForm.reset();
         this.ctaButtonForm.clearErrors();
      },
      removeCtaButton(cta) {
         this.$toast.question(`Are you sure?`, 'Delete CTA Button!').then(() => {
            this.$inertia.delete('/admin/website/sections-cta-buttons/' + cta.id, {
               onSuccess: () => {
                  this.$toast.success('CTA button deleted successfully!', 'Success');
                  this.fetchPageSections();
               },
               onError: (error) => {
                  console.log(error)
                  this.$toast.error('An error occurred while deleting the CTA button!', 'Error');
               }
            })
         });
      },
      handleSectionMedia(event, section) {
         const file = event.target.files?.[0];
         
         const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
         const maxSizeMB = 5;
         
         if (!allowedTypes.includes(file.type)) {
            this.$toast.error('Only JPEG, JPG, or PNG files are allowed.', 'Error');
            event.target.value = ''; // reset input
            return;
         }
         
         if (file.size > maxSizeMB * 1024 * 1024) {
            this.$toast.error(`Image must be less than ${maxSizeMB}MB.`, 'Error');
            event.target.value = ''; // reset input
            return;
         }
         
         if (file) {
            this.mediaForm.section_id = section.id
            this.mediaForm.file = file;
         }
      },
      uploadMedia() {
         this.mediaForm.post(route('pages.sections.media'), {
            headers: {
               "Content-Type": "multipart/form-data",
            },
            forceFormData: true,
            onSuccess: () => {
               this.mediaForm.reset();
               this.mediaForm.clearErrors();
               this.$toast.success('Media uploaded', 'Updated');
               this.$inertia.visit(route('pages.sections.edit', this.page.hashid));
               
               this.fetchPageSections();
            },
            onError: (errors) => {
               console.log(errors);
               this.$toast.error('An error occurred. Please try again', 'Error');
            },
         })
      },
      deleteMedia(section) {
         this.$toast.question(`Delete media for ${section.title}?`, 'Deleting section media!').then(() => {
            this.$inertia.delete(route('pages.sections.delete-media', section.id), {
               onSuccess: () => {
                  this.$toast.success('Media deleted successfully!', 'Success');
                  this.fetchPageSections();
               },
               onError: (error) => {
                  console.log(error)
                  this.$toast.error('An error occurred while deleting the media!', 'Error');
               }
            })
         })
      },
      submitForm() {
         this.form.patch(route('pages.sections.update', this.page.hashid), {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               setTimeout(() => {
                  this.$toast.success('Page sections updated successfully', 'Success');
                  this.$inertia.visit(route('pages.index'))
               }, 800)
            },
            onError: (errors) => {
               console.log(errors);
               this.$toast.error('An error occurred. Please try again', 'Error');
            },
         })
      },
      getSectionError(index, field) {
         return this.form.errors[`sections.${index}.${field}`];
      },
   }
}
</script>

<style scoped>
.crd-custom {
   border: 1px solid rgb(228.48, 230.16, 231.84);
   box-shadow:none;
}
.table thead {
   background-color: rgba(228.48, 230.16, 231.84, 0.3);
}
.table thead tr th {
   padding: 0.65rem;
}
.table tbody tr td {
   padding: 0.55rem;
}
</style>
