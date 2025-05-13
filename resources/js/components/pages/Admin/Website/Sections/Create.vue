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
            <li class="breadcrumb-item">
               <Link href="/admin/website/pages">Website Pages</Link>
            </li>
            <li class="breadcrumb-item text-primary">
               Page Sections
            </li>
         </ol>
      </nav>
      
      <div class="col-xl-12 col-md-12">
         <div class="card">
            <div class="card-header border-bottom">
               <h5 class="card-title mb-0">
                  {{ page.title }} - Page Contents
               </h5>
            </div>
            <div v-for="(section, index) in form.sections" :key="index" class="card-body pt-6 border-bottom">
               <div class="mb-3">
                  <div class="mb-4 d-flex align-items-center justify-content-between">
                     <div>
                        <h5 class="mb-0">Section {{ index + 1 }}</h5>
                        <small class="me-2">Enter Section Details</small>
                     </div>
                     <div>
                        <button type="button" class="btn btn-sm btn-danger ms-auto" @click="removeSection(index)">
                           <i class="bx bx-trash"></i>
                        </button>
                     </div>
                  </div>
                  <div class="mb-3">
                     <div class="row">
                        <div class="col-md-4 col-12">
                           <div class="mb-3">
                              <label for="sectionTitle" class="form-label">Title</label>
                              <input id="sectionTitle" type="text" class="form-control" v-model="section.title">
                              <div v-if="getSectionError(index, 'title')" class="text-danger">
                                 {{ getSectionError(index, 'title') }}
                              </div>
                           </div>
                        </div>
                        <div class="col-md-4 col-12">
                           <div class="mb-3">
                              <label for="sectionSubTitle" class="form-label">Sub-Title</label>
                              <input id="sectionSubTitle" type="text" class="form-control" v-model="section.sub_title">
                              <div v-if="getSectionError(index, 'sub_title')" class="text-danger">
                                 {{ getSectionError(index, 'sub_title') }}
                              </div>
                           </div>
                        </div>
                        <div class="col-md-4 col-12">
                           <div class="mb-3">
                              <label for="sectionOrder" class="form-label">Order</label>
                              <input id="sectionOrder" type="number" class="form-control" v-model="section.order">
                              <div v-if="getSectionError(index, 'order')" class="text-danger">
                                 {{ getSectionError(index, 'order') }}
                              </div>
                           </div>
                        </div>
                        <div class="col-md-4 col-12">
                           <div class="mb-3">
                              <label for="sectionBgStyle" class="form-label">Section Background</label>
                              <v-select
                                 id="sectionBgStyle"
                                 v-model="section.bg_style"
                                 :options="sectionBgStyles"
                                 label="label"
                                 :reduce="(option) => option.value"
                              ></v-select>
                              <div v-if="getSectionError(index, 'bg_style')" class="text-danger">
                                 {{ getSectionError(index, 'bg_style') }}
                              </div>
                           </div>
                        </div>
                        <div :class="section.bg_style === 'image' ? 'col-12' : 'col-12 col-md-4'" v-if="section.bg_style">
                           <div class="mb-3">
                              <div v-if="section.bg_style === 'color'">
                                 <label for="sectionBgColor" class="form-label">Background Color</label>
                                 <v-select
                                    id="sectionBgColor"
                                    v-model="section.bg_color"
                                    :options="sectionBgColors"
                                    label="label"
                                    :reduce="(option) => option.value"
                                 ></v-select>
                                 <div v-if="getSectionError(index, 'bg_color')" class="text-danger">
                                    {{ getSectionError(index, 'bg_color') }}
                                 </div>
                              </div>
                              <div v-if="section.bg_style === 'image'">
                                 <label for="" class="form-label">Background Image</label>
                                 <drag-and-drop :multiple="false"  @update:files="(files) => bgImageUpload(files, index)"/>
                                 <div v-if="getSectionError(index, 'bg_image')" class="text-danger">
                                    {{ getSectionError(index, 'bg_image') }}
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="col-md-4 col-12">
                           <div class="mb-3">
                              <label for="sectionType" class="form-label">Section Type</label>
                              <v-select
                                 id="sectionType"
                                 v-model="section.type"
                                 :options="sectionTypes"
                                 label="label"
                                 :reduce="(option) => option.value"
                              ></v-select>
                              <div v-if="getSectionError(index, 'type')" class="text-danger">
                                 {{ getSectionError(index, 'type') }}
                              </div>
                           </div>
                        </div>
                        <div v-if="section.type" class="col-md-12 col-12 ">
                           <div class="mb-3">
                              <div v-if="section.type === 1">
                                 <label for="sectionContent" class="form-label">Section Content</label>
                                 <quill-editor toolbar="full" contentType="html" theme="snow"
                                               v-model:content="section.content"
                                               :options="{ placeholder: 'Write something...' }" />
                                 <div v-if="getSectionError(index, 'content')" class="text-danger">
                                    {{ getSectionError(index, 'content') }}
                                 </div>
                              </div>
                              <div v-if="section.type === 2">
                                 <label for="sectionImage" class="form-label">Section Image</label>
                                 <!-- <input type="file" @change="typeImageUpload($event, section)" class="form-control"> -->
                                 <drag-and-drop :multiple="false"  @update:files="(files) => typeImageUpload(files, index)"/>
                                 <div v-if="getSectionError(index, 'type_image')" class="text-danger">
                                    {{ getSectionError(index, 'type_image') }}
                                 </div>
                              </div>
                           </div>
                        </div>
                        <!-- Sub-Sections -->
                        <div v-if="section.subSections && section.subSections.length" class="col-12 mb-3">
                           <div v-for="(sub, subIndex) in section.subSections" :key="subIndex" class="sub-section sub-md-section sub-sm-section">
                              <div class="mb-4 d-flex align-items-center justify-content-between">
                                 <div>
                                    <h5 class="mb-0">Sub Section {{ subIndex + 1}}</h5>
                                    <small class="me-2">Enter Sub-Section Details</small>
                                 </div>
                                 <div>
                                    <button type="button" class="btn btn-sm btn-danger ms-auto" @click="removeSubSection(index, subIndex)">
                                       <i class="bx bx-trash"></i>
                                    </button>
                                 </div>
                              </div>
                              <div class="row">
                                 <div class="col-md-4 col-12">
                                    <div class="mb-3">
                                       <label for="subSectionTitle" class="form-label">Title</label>
                                       <input id="subSectionTitle" type="text" class="form-control" v-model="sub.title">
                                       <div v-if="getSubSectionError(index, subIndex, 'title')" class="text-danger">
                                          {{ getSubSectionError(index, subIndex, 'title') }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4 col-12">
                                    <div class="mb-3">
                                       <label for="subSectionSubTitle" class="form-label">Sub-Title</label>
                                       <input id="subSectionSubTitle" type="text" class="form-control" v-model="sub.sub_title">
                                       <div v-if="getSubSectionError(index, subIndex, 'sub_title')" class="text-danger">
                                          {{ getSubSectionError(index, subIndex, 'sub_title') }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4 col-12">
                                    <div class="mb-3">
                                       <label for="subSectionOrder" class="form-label">Order</label>
                                       <input id="subSectionOrder" type="number" class="form-control" v-model="sub.order">
                                       <div v-if="getSubSectionError(index, subIndex, 'order')" class="text-danger">
                                          {{ getSubSectionError(index, subIndex, 'order') }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4 col-12">
                                    <div class="mb-3">
                                       <label for="subSectionType" class="form-label">Section Type</label>
                                       <v-select
                                          id="subSectionType"
                                          v-model="sub.type"
                                          :options="sectionTypes"
                                          label="label"
                                          :reduce="(option) => option.value"
                                       ></v-select>
                                       <div v-if="getSubSectionError(index, subIndex, 'type')" class="text-danger">
                                          {{ getSubSectionError(index, subIndex, 'type') }}
                                       </div>
                                    </div>
                                 </div>
                                 <div v-if="sub.type" class="col-md-12 col-12">
                                    <div class="mb-3">
                                       <div v-if="sub.type === 1">
                                          <label for="subSectionContent" class="form-label">Content</label>
                                          <textarea rows="3" class="form-control" v-model="sub.content"></textarea>
                                          <quill-editor toolbar="full" contentType="html" theme="snow"
                                                        v-model:content="sub.content"
                                                        :options="{ placeholder: 'Write something...' }" />
                                          <div v-if="getSubSectionError(index, subIndex, 'content')" class="text-danger">
                                             {{ getSubSectionError(index, subIndex, 'content') }}
                                          </div>
                                       </div>
                                       <div v-if="sub.type === 2">
                                          <label for="subSectionImage" class="form-label">Section Image</label>
                                          <!-- <input type="file" id="subSectionImage" @change="subSectionImageUpload($event, sub)" class="form-control"> -->
                                          <drag-and-drop :multiple="false"  @update:files="(files) => subSectionImageUpload(files, index, subIndex)"/>
                                          <div v-if="getSubSectionError(index, subIndex, 'type_image')" class="text-danger">
                                             {{ getSubSectionError(index, subIndex, 'type_image') }}
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     <button @click="addSubSection(index)" class="btn btn-light mt-3">
                        <i class="bx bx-plus-circle me-2"></i>Add Sub-Section
                     </button>
                  </div>
               </div>
            </div>
            <div class="card-footer pt-6">
               <button @click="addSection" type="button" class="btn btn-light me-3">
                  <i class="bx bx-plus-circle me-2"></i>Add Section
               </button>
               <button v-if="form.sections.length" @click="saveSections" type="button" class="btn btn-success float-end">
                  Submit
               </button>
            </div>
         </div>
      </div>
   </div>
</template>

<script>
import { useForm } from "@inertiajs/vue3";
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css';
// import DragAndDrop from "../../../../global/DragAndDrop.vue";
import DragAndDrop from "../../../../global/FileUploader.vue";

export default {
   props: ['page'],
   components:{QuillEditor, DragAndDrop},
   data() {
      return {
         form: useForm({
            page_id: this.page.id,
            sections: [],
         }),
         sectionTypes: [
            { label: "Text", value: 1 },
            { label: "Image", value: 2 },
         ],
         sectionBgStyles: [
            { label: "Color", value: 'color' },
            { label: "Image", value: 'image' },
         ],
         sectionBgColors: [
            { label: "Default", value: "default" },
            { label: "Theme", value: "bg-theme" },
            { label: "Gray", value: "bg-gray" },
         ],
         errors: {},
      };
   },
   watch: {
      'form.sections.type': function (index,) {
         if(this.form.sections[index].type === null || this.form.sections[index].type === '') {
            return [
               this.form.sections[index].content = null,
               this.form.sections[index].type_image = null,
            ];
         }
      },
      'form.sections.content': function (index) {
         this.form.sections[index].image = null
      },
      'form.sections.type_image': function (index) {
         this.form.sections[index].content = null
      }
   },
   methods: {
      saveSections() {
         // Submit form if validation passes
         this.form.post("/admin/website/sections", {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               this.$toast.success('Page sections added successfully', 'Success');
               setTimeout(() => {
                  this.$inertia.visit('/admin/website/pages');
               }, 1000)
            },
            onError: (errors) => {
               console.log(errors)
               this.$toast.error('An error occurred. Please try again', 'Error');
            },
         });
      },
      addSection() {
         this.form.sections.push({
            id: Date.now(),
            title: "",
            sub_title: "",
            order: "",
            bg_style: "",
            bg_color: "",
            bg_image: "",
            type: "",
            content: "",
            type_image: null,
            subSections: []
         });
      },
      removeSection(index) {
         this.form.sections.splice(index, 1);
      },
      addSubSection(sectionIndex) {
         if (this.form.sections[sectionIndex].subSections.length >= 2) {
            alert('You can only add up to 2 subsections.');
            return;
         }
         this.form.sections[sectionIndex].subSections.push({
            id: Date.now(),
            title: "",
            sub_title: "",
            order: "",
            type: "",
            content: "",
            type_image: "",
         });
      },
      removeSubSection(sectionIndex, subIndex) {
         this.form.sections[sectionIndex].subSections.splice(subIndex, 1);
      },
      bgImageUpload(files,sectionIndex) {
         this.form.sections[sectionIndex].bg_image = files[0];
      },
      typeImageUpload(files, sectionIndex) {
         this.form.sections[sectionIndex].type_image = files[0];
      },
      // typeImageUpload(event, section, subSection) {
      //     let files = event.target.files;
      //     section.type_image = URL.createObjectURL(files[0]);
      // },
      subSectionImageUpload(files, sectionIndex, subIndex) {
         this.form.sections[sectionIndex].subSections[subIndex].type_image = files[0];
         // let files = event.target.files;
      },
      getSectionError(index, field) {
         return this.form.errors[`sections.${index}.${field}`];
      },
      getSubSectionError(index, subIndex, field) {
         return this.form.errors[`sections.${index}.subSections.${subIndex}.${field}`];
      },
   },
};
</script>

<style scoped>
.sub-section {
   margin-left: 3rem;
}
@media (max-width: 768px) {
   .sub-md-section {
      margin-left: 1.5rem !important;
   }
}
@media (max-width: 576px) {
   .sub-sm-section {
      margin-left: 0 !important;
   }
}
</style>
