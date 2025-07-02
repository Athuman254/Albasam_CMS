<template>
   <Head title="Page Sections"/>
   
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
         
         <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-6 row-gap-4">
            <div class="d-flex flex-column justify-content-center">
               <h4 class="mb-1">{{ page.title }} Page Sections</h4>
               <p class="mb-0">Manage the page sections that are being displayed on the website</p>
            </div>
            <div class="d-flex align-content-center flex-wrap gap-4">
               <button type="button" class="btn btn-primary d-none d-sm-inline-block" @click.prevent="openAddSectionModal">
                  Add Section
               </button>
               <button type="button" class="btn btn-primary btn-icon d-sm-none" @click.prevent="openAddSectionModal">
                  <i class="icon-base bx bx-plus"></i>
               </button>
            </div>
         </div>
         
         <div class="col-xl-12 col-md-12">
            <div class="row">
               <div class="accordion">
                  <div v-for="(section, index) in pageSections" :key="index" class="accordion-item mb-5">
                     <h1 class="accordion-header border-bottom">
                        <button
                           type="button"
                           class="accordion-button"
                           :class="{ collapsed: openAccordion !== `${section.id}` }"
                           :key="section.id"
                           @click="toggleAccordion(`${section.id}`, section)"
                        >
                           Section {{ index + 1}}
                        </button>
                     </h1>
                     <div>
                        <div v-show="openAccordion === `${section.id}`" class="accordion-body py-4 transition-all duration-800 ease-in-out">
                           <div class="row">
                              <div class="col-lg-4 col-md-4 col-12">
                                 <div class="mb-3">
                                    <label for="sectionType" class="form-label-md mb-2 d-md-flex d-block">
                                       Section Type
                                       <span v-if="editForm.type === 'section-with-contact-form'" class="form-check form-switch ms-auto py-md-0 py-3">
                                             <input v-model="editForm.section_has_image" class="form-check-input" type="checkbox" id="hasCtaButtons">
                                             <label class="form-check-label" for="hasCtaButtons"> Include an image </label>
                                          </span>
                                    </label>
                                    <v-select
                                       id="sectionType"
                                       v-model="editForm.type"
                                       :options="sectionTypes"
                                       label="name"
                                       :reduce="option => option.value"
                                    />
                                 </div>
                              </div>
                              <div v-if="editForm.type === 'section-with-map'" class="col-md-6 col-12">
                                 <div class="mb-3">
                                    <label for="mapLink" class="form-label-md mb-2">Map URL</label>
                                    <input v-model="editForm.map_link" type="text" class="form-control" id="mapLink" />
                                    <div v-if="form.errors.map_link" class="text-danger">{{ editForm.errors.map_link }}</div>
                                 </div>
                              </div>
                              <div v-if="editForm.type === 'section-with-component' && editForm.type !== 'section-with-map'" class="col-lg-4 col-md-4 col-12">
                                 <div class="mb-3">
                                    <label for="componentType" class="form-label-md mb-2">Component</label>
                                    <v-select
                                       id="componentType"
                                       v-model="editForm.component_type"
                                       :options="componentTypes"
                                       label="name"
                                       :reduce="option => option.value"
                                    />
                                 </div>
                              </div>
                              <div class="col-lg-4 col-md-4 col-12">
                                 <div class="mb-3">
                                    <label for="sectionTitle" class="form-label-md mb-2">Section Title</label>
                                    <input v-model="editForm.title" type="text" class="form-control" id="sectionTitle" />
                                 </div>
                              </div>
                              <div class="col-lg-4 col-md-4 col-12">
                                 <div class="mb-3">
                                    <label for="sectionSubTitle" class="form-label-md mb-2">Section Sub-Title</label>
                                    <input v-model="editForm.sub_title" type="text" class="form-control" id="sectionSubTitle" />
                                 </div>
                              </div>
                              <div v-if="editForm.section_has_image && editForm.type !== 'section-with-map'"  class="col-lg-6 col-md-6 col-12 my-5">
                                 <div class="mb-3">
                                    <div class="card crd-custom">
                                       <div class="card-body">
                                          <div v-if="editForm.media">
                                             <p class="text-muted d-md-flex d-block">
                                                Section Image
                                                <span v-if="editForm.type !== 'hero-section' && editForm.type !== 'section-with-contact-form'" class="form-check form-switch ms-auto py-md-0 py-3">
                                                   <input v-model="editForm.section_image_first" class="form-check-input" type="checkbox" id="hasCtaButtons">
                                                   <label class="form-check-label" for="hasCtaButtons"> Show Image First </label>
                                                </span>
                                             </p>
                                             <div class="card card-img p-5">
                                                <img :src="editForm.media.original_url" alt="logo" style="width:120px; height:auto;">
                                             </div>
                                             <button type="button" class="btn btn-sm btn-outline-danger my-3 ms-3" @click.prevent="deleteMedia(section)">
                                                Delete Image
                                             </button>
                                          </div>
                                          <div v-else>
                                             <div class="mb-3 form-group">
                                                <label class="form-label-md mb-2 d-md-flex d-block" for="sectionImage">
                                                   Section Image
                                                   <span v-if="editForm.type !== 'hero-section' && editForm.type !== 'section-with-contact-form'" class="form-check form-switch ms-auto py-md-0 py-3">
                                                      <input v-model="editForm.section_image_first" class="form-check-input" type="checkbox" id="hasCtaButtons">
                                                      <label class="form-check-label" for="hasCtaButtons"> Show Image First </label>
                                                   </span>
                                                </label>
                                                <input
                                                   @change="(e) => handleEditSectionMedia(e, section)"
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
                              <div v-if="editForm.type !== 'section-with-map'" class="col-12">
                                 <div class="mb-5">
                                    <label for="sectionDetails" class="form-label-md mb-2 d-block">
                                       Section Details
                                       <span v-if="editForm.type === 'section-with-contact-form'" class="form-check form-switch py-3">
                                          <input v-model="editForm.include_contact_cards" class="form-check-input" type="checkbox" id="hasCtaButtons">
                                          <label class="form-check-label" for="hasCtaButtons"> Include Contact Cards </label>
                                       </span>
                                    </label>
                                    <div v-if="!editForm.include_contact_cards">
                                       <editor v-model="editForm.details" id="editor" class="editor-control" />
                                    </div>
                                    <!--                                    <ck-editor v-model="editForm.details" id="editor" class="w-100"></ck-editor>-->
                                 </div>
                              </div>
                              <div v-if="editForm.type !== 'section-with-contact-form' && editForm.type !== 'section-with-map'" class="col-md-7 col-12">
                                 <div class="form-check form-switch mb-5">
                                    <input v-model="editForm.has_cta_buttons" class="form-check-input" type="checkbox" id="hasCtaButtons">
                                    <label class="form-check-label" for="hasCtaButtons"> Has CTA Buttons </label>
                                    <br>
                                    <span class="text-muted">check this if you want to add Call To Action(CTA) buttons to the section</span>
                                 </div>
                                 <div v-if="editForm.has_cta_buttons">
                                    <div class="mb-3">
                                       <div class="table-responsive text-nowrap">
                                          <table class="table table-sm table-bordered">
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
                                                   <button type="button" class="btn btn-sm btn-icon btn-danger" @click.prevent="deleteCtaButton(section, cta)">
                                                      <i class="icon-base bx bx-trash"></i>
                                                   </button>
                                                </td>
                                             </tr>
                                             </tbody>
                                          </table>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div v-if="editForm.type !== 'section-with-map' && editForm.has_cta_buttons" class="col-md-5 col-12">
                                 <form id="createCtaButtonForm" class="card crd-custom">
                                    <div class="card-body">
                                       <label class="form-label-md mb-4 d-md-flex d-block">
                                          <span class="align-content-center">CTA Button Form</span>
                                          <span v-if="newCtaButtonForm.page_id && newCtaButtonForm.cta_button_text" class="ms-auto py-md-0 py-3">
                                             <button type="button" class="btn btn-sm btn-light" @click.prevent="uploadCtaButton(section)">Save</button>
                                          </span>
                                       </label>
                                       
                                       <div v-if="editForm.cta_buttons.length >= 2">
                                          <p class="text-muted">
                                             You can only add 2 CTA buttons per section.
                                          </p>
                                       </div>
                                       <div v-else class="row">
                                          <div class="col-12">
                                             <div class="mb-3">
                                                <label for="pageId" class="form-label">Page To Redirect To:</label>
                                                <v-select
                                                   id="pageId"
                                                   v-model="newCtaButtonForm.page_id"
                                                   :options="pages"
                                                   label="title"
                                                   :reduce="option => option.id"
                                                />
                                             </div>
                                          </div>
                                          <div class="col-12">
                                             <div class="mb-3">
                                                <label for="buttonText" class="form-label">Button Text</label>
                                                <input type="text" v-model="newCtaButtonForm.cta_button_text" class="form-control" id="buttonText" />
                                             </div>
                                          </div>
                                          <div class="col-12">
                                             <div class="mb-3">
                                                <label for="buttonType" class="form-label">Button Type</label>
                                                <v-select
                                                   id="buttonType"
                                                   v-model="newCtaButtonForm.cta_button_type"
                                                   :options="ctaButtonTypes"
                                                   label="name"
                                                   :reduce="option => option.value"
                                                />
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </form>
                              </div>
                              <div class="col-12 mt-5 ms-auto">
                                 <button type="button" class="btn btn-success me-3" @click.prevent="updateSection">
                                    Update Section
                                 </button>
                                 <button type="button" class="btn btn-outline-danger float-end" @click.prevent="deleteSection(section)">
                                    Delete Section
                                 </button>
                              </div>
                              <div class="col-6 mt-5">
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         
         <!-- Section Modal -->
         <div
            class="modal fade"
            id="add-section-modal"
            data-bs-backdrop="static"
            tabindex="-1"
            aria-labelledby="add-section-modal-label"
            aria-hidden="true"
            ref="addSectionModal"
         >
            <div class="modal-dialog modal-xl modal-dialog-scrollable">
               <div class="modal-content">
                  <div class="modal-header p-5 border-bottom">
                     <h5 class="modal-title" id="create-menu-modal-label">New Section</h5>
                     <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                        @click.prevent="formCleanUp"
                     ></button>
                  </div>
                  
                  <div class="modal-body">
                     <form id="createForm" @submit.prevent="uploadNewSection">
                        <div class="row">
                           <div class="col-md-6 col-12">
                              <div class="mb-3">
                                 <label for="sectionType" class="form-label-md mb-2 d-md-flex d-block">
                                    Section Type
                                    <span v-if="form.type === 'section-with-contact-form'" class="form-check form-switch ms-auto py-md-0 py-3">
                                          <input v-model="form.section_has_image" class="form-check-input" type="checkbox" id="hasCtaButtons">
                                          <label class="form-check-label" for="hasCtaButtons"> Include an image </label>
                                       </span>
                                 </label>
                                 <v-select
                                    id="sectionType"
                                    v-model="form.type"
                                    :options="sectionTypes"
                                    label="name"
                                    :reduce="option => option.value"
                                 />
                                 <div v-if="form.errors.type" class="text-danger">{{ form.errors.type }}</div>
                              </div>
                           </div>
                           <div v-if="form.type === 'section-with-map'" class="col-md-6 col-12">
                              <div class="mb-3">
                                 <label for="mapLink" class="form-label-md mb-2">Map URL</label>
                                 <input v-model="form.map_link" type="text" class="form-control" id="mapLink" />
                                 <div v-if="form.errors.map_link" class="text-danger">{{ form.errors.map_link }}</div>
                              </div>
                           </div>
                           <div v-if="form.type === 'section-with-component' && form.type !== 'section-with-map'" class="col-md-6 col-12">
                              <div class="mb-3">
                                 <label for="componentType" class="form-label-md mb-2">Component</label>
                                 <v-select
                                    id="componentType"
                                    v-model="form.component_type"
                                    :options="componentTypes"
                                    label="name"
                                    :reduce="option => option.value"
                                 />
                              </div>
                           </div>
                           <div v-if="form.section_has_image && form.type !== 'section-with-map'" class="col-md-6 col-12">
                              <div class="mb-3">
                                 <label for="sectionImage" class="form-label-md mb-2 d-md-flex d-block">
                                    Section Image
                                    <span v-if="form.type !== 'hero-section' && form.type !== 'section-with-contact-form'" class="form-check form-switch ms-auto mb-0 py-md-0 py-3">
                                          <input v-model="form.section_image_first" class="form-check-input" type="checkbox" id="hasCtaButtons">
                                          <label class="form-check-label" for="hasCtaButtons"> Show Image First </label>
                                       </span>
                                 </label>
                                 <input id="serviceMedia" @change="handleSectionMedia" type="file" accept="image/*" class="form-control">
                              </div>
                           </div>
                           <div v-if="form.type !== 'section-with-map'" class="col-md-6 col-12">
                              <div class="mb-3">
                                 <label for="sectionType" class="form-label-md mb-2">Section Title</label>
                                 <input v-model="form.title" type="text" class="form-control" id="sectionTitle" />
                                 <div v-if="form.errors.title" class="text-danger">{{ form.errors.title }}</div>
                              </div>
                           </div>
                           <div v-if="form.type !== 'section-with-map'" class="col-md-6 col-12">
                              <div class="mb-3">
                                 <label for="sectionSubTitle" class="form-label-md mb-2">Section Sub-Title</label>
                                 <input v-model="form.sub_title" type="text" class="form-control" id="sectionSubTitle" />
                                 <div v-if="form.errors.sub_title" class="text-danger">{{ form.errors.sub_title }}</div>
                              </div>
                           </div>
                           <div v-if="form.type !== 'section-with-map'" class="col-12">
                              <div class="mb-5">
                                 <label for="sectionDetails" class="form-label-md">
                                    Section Details
                                    <span v-if="form.type === 'section-with-contact-form'" class="form-check form-switch py-3">
                                             <input v-model="form.include_contact_cards" class="form-check-input" type="checkbox" id="hasCtaButtons">
                                             <label class="form-check-label" for="hasCtaButtons"> Include Contact Cards </label>
                                          </span>
                                 </label>
                                 <div v-if="!form.include_contact_cards">
                                    <editor v-model="form.details" id="editor" class="editor-control" />
                                 </div>
                                 <div v-if="form.errors.details" class="text-danger">{{ form.errors.details }}</div>
                              </div>
                           </div>
                           <div v-if="form.type !== 'section-with-contact-form' && form.type !== 'section-with-map'" class="col-md-7 col-12">
                              <div class="form-check form-switch mb-5">
                                 <input v-model="form.has_cta_buttons" class="form-check-input" type="checkbox" id="hasCtaButtons">
                                 <label class="form-check-label" for="hasCtaButtons"> Has CTA Buttons </label>
                                 <br>
                                 <span class="text-muted">check this if you want to add Call To Action (CTA) buttons</span>
                              </div>
                              <div v-if="form.has_cta_buttons" class="w-100">
                                 <div class="mb-3">
                                    <div class="table-responsive text-nowrap">
                                       <table class="table table-sm table-bordered">
                                          <thead>
                                          <tr>
                                             <th class="" style="width: 50%;">Page</th>
                                             <th class="" style="width: 25%;">Button Text</th>
                                             <th class="" style="width: 20%;">Button Style</th>
                                             <th class="" style="width: 5%;"></th>
                                          </tr>
                                          </thead>
                                          <tbody>
                                          <tr v-for="(cta, ctaIndex) in form.cta_buttons" :key="ctaIndex">
                                             <td>{{ cta.page.title }}</td>
                                             <td>{{ cta.cta_button_text }}</td>
                                             <td>{{ cta.cta_button_type.name }}</td>
                                             <td>
                                                <button type="button" class="btn btn-sm btn-icon btn-danger" @click.prevent="removeCtaButton()">
                                                   <i class="icon-base bx bx-trash"></i>
                                                </button>
                                             </td>
                                          </tr>
                                          </tbody>
                                       </table>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div v-if="form.has_cta_buttons && form.type !== 'section-with-map'" class="col-md-5 col-12">
                              <form id="createCtaButtonForm" class="card crd-custom">
                                 <div class="card-body">
                                    <label class="form-label-md mb-4 d-md-flex d-block">
                                       <span class="align-content-center">CTA Button Form</span>
                                       <span v-if="ctaButtonForm.page && ctaButtonForm.cta_button_text" class="ms-auto py-md-0 py-3">
                                             <button type="button" class="btn btn-sm btn-light" @click.prevent="addCtaButtonToForm">Save</button>
                                          </span>
                                    </label>
                                    <div v-if="form.cta_buttons.length >= 2">
                                       <p class="text-muted">
                                          You can only add 2 CTA buttons per section.
                                       </p>
                                    </div>
                                    <div v-else class="row">
                                       <div class="col-12">
                                          <div class="mb-3">
                                             <label for="pageId" class="form-label">Page To Redirect To:</label>
                                             <v-select
                                                id="pageId"
                                                v-model="ctaButtonForm.page"
                                                :options="pages"
                                                label="title"
                                             />
                                          </div>
                                       </div>
                                       <div class="col-12">
                                          <div class="mb-3">
                                             <label for="buttonText" class="form-label">Button Text</label>
                                             <input type="text" v-model="ctaButtonForm.cta_button_text" class="form-control" id="buttonText" />
                                          </div>
                                       </div>
                                       <div class="col-12">
                                          <div class="mb-3">
                                             <label for="buttonType" class="form-label">Button Type</label>
                                             <v-select
                                                id="buttonType"
                                                v-model="ctaButtonForm.cta_button_type"
                                                :options="ctaButtonTypes"
                                                label="name"
                                             />
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </form>
                           </div>
                        </div>
                     </form>
                  </div>
                  
                  <div class="modal-footer p-5 border-top">
                     <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" @click="formCleanUp">
                        Close
                     </button>
                     <button type="button" class="btn btn-primary" @click.prevent="uploadNewSection">
                        Save Section
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
            type: '',
            sub_title: '',
            title: '',
            details: '',
            component_type: '',
            section_image_first: false,
            include_contact_cards: false,
            has_cta_buttons: false,
            cta_buttons: [],
            section_has_image: false,
            media: null,
            map_link: '',
         }),
         editForm: useForm({
            index: '',
            page_id: this.page.id,
            section_id: null,
            type: '',
            sub_title: '',
            title: '',
            details: '',
            component_type: '',
            include_contact_cards: false,
            section_image_first: false,
            has_cta_buttons: false,
            cta_buttons: [],
            section_has_image: false,
            media: null,
            map_link: '',
         }),
         mediaForm: useForm({
            section_id: '',
            file: null,
         }),
         ctaButtonForm: useForm({
            page: null,
            cta_button_text: '',
            cta_button_type: '',
         }),
         newCtaButtonForm: useForm({
            section_id: null,
            page_id: null,
            cta_button_text: '',
            cta_button_type: '',
         }),
         pages: [],
         pageSections: [],
         openAccordion: '',
         sectionTypes: [
            {value: 'hero-section', name: 'Hero Section'},
            {value: 'section-with-image', name: 'Section With Image'},
            {value: 'section-without-image', name: 'Section Without Image'},
            {value: 'section-with-component', name: 'Section With Component'},
            {value: 'section-with-blogs', name: 'Section With Blogs'},
            {value: 'section-with-contact-form', name: 'Section With Contact Form'},
            {value: 'section-with-map', name: 'Section With Map'},
         ],
         componentTypes: [
            {value: 'blogs', name: 'Blogs'},
            {value: 'careers', name: 'Vacancies'},
         ],
         ctaButtonTypes: [
            {value: 'primary-btn', name: 'Primary Button'},
            // {value: 'secondary-btn', name: 'Secondary Button'},
            // {value: 'default-btn', name: 'Default Button'},
         ],
      }
   },
   watch: {
      'form.type': function(newVal) {
         this.form.section_has_image = ['hero-section', 'section-with-image'].includes(newVal);
         if (!this.form.section_has_image) this.form.media = null;
      },
      'form.section_has_image': function(val) {
         this.form.include_contact_cards = false;
      },
      'form.include_contact_cards': function(newVal) {
         if (newVal) {
            this.form.section_has_image = false;
         }
      },
      'editForm.type': function(newVal) {
         this.editForm.section_has_image = ['hero-section', 'section-with-image', 'section-with-contact-form'].includes(newVal);
      },
      'editForm.section_has_image': function(val) {
         this.editForm.include_contact_cards = false;
      },
      'editForm.include_contact_cards': function(newVal) {
         if (newVal) {
            this.editForm.section_has_image = false;
         }
      },
   },
   mounted() {
      this.fetchPages();
      this.fetchSections();
   },
   methods: {
      toggleAccordion(sectionKey, section) {
         this.openAccordion = this.openAccordion === sectionKey ? null : sectionKey;
         this.editForm.reset();
         this.editForm.clearErrors();
         this.editForm.media = null;
         if(this.openAccordion === sectionKey && section) {
            this.populateEditForm(section.id);
         } else {
            this.editForm.reset();
            this.editForm.clearErrors();
            this.editForm.media = null;
         }
      },
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
      fetchSections() {
         axios.get(route('datatable.website.sections'), {
            params: {
               filter: {
                  'page_id': this.page.id,
               }
            }
         })
            .then(({data}) => {
               this.pageSections = data.data;
            }).catch((error) => {
            console.log(error);
            this.$toast.error("An error occurred while fetching the page sections!", "Error");
         })
      },
      populateEditForm(sectionId) {
         let section = this.pageSections.find((sec) => sec.id === sectionId);
         if(section) {
            this.editForm.section_id = section.id;
            this.editForm.type = section.type;
            this.editForm.title = section.title;
            this.editForm.sub_title = section.sub_title;
            this.editForm.component_type = section.component_type;
            this.editForm.details = section.details;
            this.editForm.include_contact_cards = section.include_contact_cards;
            this.editForm.section_has_image = section.section_has_image;
            this.editForm.section_image_first = section.section_image_first;
            this.editForm.has_cta_buttons = section.has_cta_buttons;
            this.editForm.media = section.media[0];
            this.editForm.cta_buttons = section.cta_buttons;
            this.editForm.map_link = section.map_link;
         }
      },
      openAddSectionModal() {
         const modalElement = this.$refs.addSectionModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
         this.openAccordion = '';
      },
      addCtaButtonToForm() {
         if (!this.ctaButtonForm.page) {
            this.$toast.info('Select a page first', 'Info');
            return;
         }
         if (!this.ctaButtonForm.cta_button_text) {
            this.$toast.info('Label the CTA button first', 'Info');
            return;
         }
         this.form.cta_buttons.push({
            page: this.ctaButtonForm.page,
            cta_button_text: this.ctaButtonForm.cta_button_text,
            cta_button_type: this.ctaButtonForm.cta_button_type,
         });
         
         this.ctaButtonForm.reset();
         this.ctaButtonForm.clearErrors();
         this.$toast.success('CTA button added!', 'Success');
      },
      removeCtaButton(index) {
         this.form.cta_buttons.splice(index, 1);
      },
      handleSectionMedia(event) {
         const file = event.target.files[0];
         
         if (!file) {
            this.form.media = null;
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
            event.target.value = '';
            return;
         }
         
         this.form.media = file;
      },
      handleEditSectionMedia(event, section) {
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
            this.mediaForm.section_id = section.id;
            this.mediaForm.file = file;
         }
      },
      uploadMedia() {
         const sectionId = this.mediaForm.section_id;
         this.mediaForm.post(route('admin.pages.sections.media'), {
            headers: {
               "Content-Type": "multipart/form-data",
            },
            forceFormData: true,
            onSuccess: () => {
               this.mediaForm.reset();
               this.mediaForm.clearErrors();
               this.fetchSections();
               setTimeout(() => {
                  this.$toast.success('Media uploaded', 'Updated');
                  this.populateEditForm(sectionId)
               }, 400)
            },
            onError: (errors) => {
               console.log(errors);
               this.$toast.error('An error occurred. Please try again', 'Error');
            },
         })
      },
      uploadCtaButton(section) {
         this.newCtaButtonForm.section_id = section.id;
         if (!this.newCtaButtonForm.page_id) {
            this.$toast.info('Select a page first', 'Info');
            return;
         }
         if (!this.newCtaButtonForm.cta_button_text) {
            this.$toast.info('Label the CTA button first', 'Info');
            return;
         }
         this.newCtaButtonForm.post(route('admin.cta-buttons.store'), {
            onSuccess: () => {
               this.newCtaButtonForm.reset();
               this.newCtaButtonForm.clearErrors();
               this.fetchSections();
               setTimeout(() => {
                  this.$toast.success('CTA button added!', 'Success');
                  this.populateEditForm(section.id)
               }, 400)
            },
            onError: (error) => {
               console.log(error);
               this.$toast.error('Failed to save CTA button! Please try again', 'Error');
            },
         })
      },
      uploadNewSection() {
         this.form.post(route('admin.pages.sections.store'), {
            forceFormData: true,
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               const modalElement = this.$refs.addSectionModal;
               const modalInstance = Modal.getOrCreateInstance(modalElement);
               modalInstance.hide();
               setTimeout(() => {
                  this.$toast.success('Page section created successfully', 'Success');
                  this.fetchSections();
               }, 400);
            },
            onError: (errors) => {
               console.log(errors);
               this.$toast.error('An error occurred. Please try again', 'Error');
            },
         })
      },
      updateSection() {
         this.editForm.patch(route('admin.pages.sections.update', this.editForm.section_id), {
            onSuccess: () => {
               this.editForm.clearErrors();
               this.fetchSections();
               setTimeout(() => {
                  this.$toast.success('Page sections updated successfully', 'Success');
                  this.populateEditForm(this.editForm.section_id)
               }, 400)
            },
            onError: (errors) => {
               console.log(errors);
               this.$toast.error('An error occurred. Please try again', 'Error');
            },
         })
      },
      deleteMedia(section) {
         this.$toast.question(`Delete media for ${section.title}?`, 'Deleting section media!').then(() => {
            this.$inertia.delete(route('admin.pages.sections.delete-media', section.id), {
               onSuccess: () => {
                  this.editForm.clearErrors();
                  this.fetchSections();
                  setTimeout(() => {
                     this.$toast.success('Media deleted successfully!', 'Success');
                     this.populateEditForm(section.id)
                  }, 400)
               },
               onError: (error) => {
                  console.log(error)
                  this.$toast.error('An error occurred while deleting the media!', 'Error');
               }
            })
         })
      },
      deleteCtaButton(section, cta) {
         this.$toast.question(`Are you sure?`, 'Delete CTA Button!').then(() => {
            this.$inertia.delete('/admin/website/sections-cta-buttons/' + cta.id, {
               onSuccess: () => {
                  this.editForm.clearErrors();
                  this.fetchSections();
                  setTimeout(() => {
                     this.$toast.success('CTA button deleted successfully!', 'Success');
                     this.populateEditForm(section.id)
                  }, 400)
               },
               onError: (error) => {
                  console.log(error)
                  this.$toast.error('An error occurred while deleting the CTA button!', 'Error');
               }
            })
         });
      },
      deleteSection(section) {
         this.$toast.question(`Are you sure?`, 'Delete Section!').then(() => {
            this.$inertia.delete(route('admin.pages.sections.delete', section.id), {
               onSuccess: () => {
                  this.$toast.success('CTA button deleted successfully!', 'Success');
                  this.openAccordion = '';
                  setTimeout(() => {
                     this.fetchSections();
                  }, 300)
               },
               onError: (error) => {
                  console.log(error)
                  this.$toast.error('An error occurred while deleting the page section!', 'Error');
               }
            })
         });
      },
      formCleanUp() {
         this.form.reset();
         this.form.clearErrors();
         this.form.media = null;
      },
   }
}
</script>

<style scoped>
.accordion-item {
   border-radius: 0.375rem;
   border-bottom: none;
}
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
