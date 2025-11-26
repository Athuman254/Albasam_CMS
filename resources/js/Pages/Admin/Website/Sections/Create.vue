<template>
   <Head title="Create Page Section"/>
   
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
                     Add {{ page.title }} Page Sections
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
                                    <div v-if="section.section_has_image" class="col-lg-4 col-md-4 col-12">
                                       <div class="mb-3">
                                          <label for="sectionSubTitle" class="form-label">Section Image</label>
                                          <input id="serviceMedia" @change="handleSectionMedia($event, index)" type="file" accept="image/*" class="form-control">
                                          <div class="form-text">
                                             <div class="form-check form-switch">
                                                <input v-model="section.section_image_first" class="form-check-input" type="checkbox" id="hasCtaButtons">
                                                <label class="form-check-label" for="hasCtaButtons"> Show Image First </label>
                                             </div>
                                          </div>
                                          <div v-if="getSectionError(index, 'media')" class="text-danger">
                                             {{ getSectionError(index, 'media') }}
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
                                                      <th colspan="4">
                                                         <button type="button" class="btn btn-primary btn-sm" @click.prevent="openAddCtaButton(index)">
                                                            Add CTA Button
                                                         </button>
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
                                                      <td>{{ cta.cta_button_type.name }}</td>
                                                      <td>
                                                         <button type="button" class="btn btn-sm btn-icon btn-danger" @click.prevent="removeCtaButton(index, ctaIndex)">
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
            id="add-cta-button-modal"
            data-bs-backdrop="static"
            tabindex="-1"
            aria-labelledby="add-cta-button-modal-label"
            aria-hidden="true"
            ref="addCtaButtonModal"
         >
            <div class="modal-dialog">
               <div class="modal-content">
                  <div class="modal-header">
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
                              v-model="ctaButtonForm.page"
                              :options="pages"
                              label="title"
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
                           />
                        </div>
                     </form>
                  </div>
                  <div class="modal-footer">
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
                  type: '',
                  sub_title: '',
                  title: '',
                  details: '',
                  section_image_first: false,
                  has_cta_buttons: false,
                  cta_buttons: [],
                  section_has_image: false,
                  media: null,
               }
            ],
         }),
         ctaButtonForm: useForm({
            index: null,
            page: null,
            cta_button_text: '',
            cta_button_type: '',
         }),
         // sectionCardsForm: useForm({}),
         pages: [],
         sectionTypes: [
            {value: 'hero-section', name: 'Hero Section'},
            {value: 'section-with-image', name: 'Section With Image'},
            {value: 'section-without-image', name: 'Section Without Image'},
            {value: 'section-with-contact-form', name: 'Section With Contact Form (mainly for contact page)'},
            // {value: 'section-with-cards', name: 'Section With Cards'},
            // {value: 'section-with-services', name: 'Section With Services'},
            // {value: 'section-with-faqs', name: 'Section With FAQs'},
            // {value: 'section-with-skills', name: 'Section With Skills'},
         ],
         ctaButtonTypes: [
            {value: 'primary-btn', name: 'Primary Button'},
            // {value: 'secondary-btn', name: 'Secondary Button'},
            // {value: 'default-btn', name: 'Default Button'},
         ],
         // cardTypes: [
         //    {value: 'component', name: 'Component'},
         //    {value: 'custom', name: 'Custom Cards'},
         // ],
         // componentModels: [
         //    {value: 'App\\Models\\Services', name: 'Services'},
         // ],
      }
   },
   watch: {
      'form.sections': {
         handler(sections) {
            sections.forEach((section, index) => {
               this.$watch(() => section.type, (newVal) => {
                  section.section_has_image = ['hero-section', 'section-with-image'].includes(newVal);
                  if (!section.section_has_image) section.media = null;
               });
               
               this.$watch(() => section.has_cta_buttons, (newVal) => {
                  if (!newVal) section.cta_buttons = [];
               });
            });
         },
         immediate: true,
         deep: true
      }
   },
   created() {
      this.fetchPages();
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
      addSection() {
         this.form.sections.push({
            type: '',
            sub_title: '',
            title: '',
            details: '',
            section_image_first: false,
            has_cta_buttons: false,
            cta_buttons: [],
            section_has_image: false,
            media: null,
         })
      },
      removeSection(index) {
         this.form.sections.splice(index, 1);
      },
      // handleSectionTypeChange(section) {
      //    console.log('Type changed:', section.type);
      //    section.section_has_image = ['hero-section', 'section-with-image'].includes(section.type);
      // },
      openAddCtaButton(index) {
         this.ctaButtonForm.index = index;
         const modalElement = this.$refs.addCtaButtonModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      addCtaButton() {
         if (!this.ctaButtonForm.page) {
            this.$toast.info('Select a page first', 'Info');
            return;
         }
         if (!this.ctaButtonForm.cta_button_text) {
            this.$toast.info('Label the CTA button first', 'Info');
            return;
         }
         this.form.sections[this.ctaButtonForm.index].cta_buttons.push({
            page: this.ctaButtonForm.page,
            cta_button_text: this.ctaButtonForm.cta_button_text,
            cta_button_type: this.ctaButtonForm.cta_button_type,
         });
         
         this.ctaButtonForm.reset();
         this.ctaButtonForm.clearErrors();
         this.$toast.success('CTA button added!', 'Success');
         const modalElement = this.$refs.addCtaButtonModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.hide();
      },
      formCleanUp() {
         this.ctaButtonForm.reset();
         this.ctaButtonForm.clearErrors();
      },
      removeCtaButton(sectionIndex, ctaIndex) {
         this.form.sections[sectionIndex].cta_buttons.splice(ctaIndex, 1);
      },
      handleSectionMedia(event, index) {
         const file = event.target.files?.[0];
         
         if (!file) {
            this.form.sections[index].media = null;
            return;
         }
         
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
         
         this.form.sections[index].media = file;
      },
      submitForm() {
         this.form.post(route('pages.sections.store'), {
            forceFormData: true,
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               // this.$toast.success('Page sections created successfully', 'Success');
               setTimeout(() => {
                  this.$toast.success('Page sections created successfully', 'Success');
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
