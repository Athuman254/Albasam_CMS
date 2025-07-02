<template>
   <Head title="Customisation"/>
   
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Website Customisation</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Customisations
               </li>
            </ol>
         </nav>
         
         <div class="col-12">
            <div class="mb-3">
               <ul class="nav nav-pills mb-5" role="tablist">
                  <li class="nav-item me-2" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'colors' }" @click="activeTab = 'colors'">
                        Colors
                     </button>
                  </li>
               </ul>
            </div>
         </div>
         <div class="col-lg-9 col-md-8 col-12 d-md-block d-none">
            <div class="mb-5">
               <iframe :key="iframeKey" :src="iframeRoute" class="iframe border"></iframe>
            </div>
         </div>
         <div class="col-lg-3 col-md-4 col-12">
            <div class="tab-content">
               <div v-show="activeTab === 'colors'">
                  <div class="card mb-3">
                     <div class="card-header py-3">
                        <h5 class="card-title mb-0">
                           <i class="icon-base bx bxs-brush bx-md"></i>
                           Colors
                        </h5>
                     </div>
                  </div>
                  <div class="card">
                     <div class="card-body">
                        <div class="row">
                           <div class="col-12">
                              <div class="mb-3 ">
                                 <label class="form-label mb-3">Primary Color <span class="small text-muted text-wrap">(default color)</span></label>
                                 <input v-model="form.primary_color" type="color" class="form-control form-control-color">
                                 <!--                           <chrome v-model="form.primary_color" />-->
                              </div>
                           </div>
                           <div class="col-12 mt-3 mb-2">
                              <button v-if="!currentCustomisation" type="button" class="btn btn-primary w-100" @click.prevent="submitCustomisations">Save</button>
                              <button v-else type="button" class="btn btn-primary w-100" @click.prevent="updateCustomisations">Save</button>
                           </div>
                           <div class="col-12 my-3">
                              <button v-if="isNotDefault()" type="button" class="btn btn-outline-secondary w-100" @click.prevent="setDefaultCustomisation">
                                 Use Defaults
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </DefaultLayout>
</template>

<script>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import axios from "axios";
// import { Chrome } from "@ckpack/vue-color";

export default {
   components: {DefaultLayout, Head, Link},
   data() {
      return {
         form: useForm({
            id: '',
            primary_color: '',
            // primary_color_light: '',
            // secondary_color: '',
            // secondary_color_light: '',
         }),
         // buttonForm: useForm({
         //    id: '',
         //    button_style: '',
         // }),
         currentCustomisation: [],
         iframeRoute: route('homepage'),
         iframeKey: 0,
         defaultCustomisation: {
            primary_color: '#25615a',
            // primary_color_light: '#a1b6ff',
            // secondary_color: '#fff200',
            // secondary_color_light: '#f7ffc1',
         },
         activeTab: 'colors',
      }
   },
   mounted() {
      this.fetchCustomisations();
   },
   methods: {
      fetchCustomisations() {
         axios.get('/datatable/website/customisations')
            .then(({data}) => {
               this.currentCustomisation = data;
               if(this.currentCustomisation) {
                  this.populateForm();
               }
            })
      },
      populateForm() {
         this.form.id = this.currentCustomisation.hashid;
         this.form.primary_color = this.currentCustomisation.primary_color;
         // this.form.primary_color_light = this.currentCustomisation.primary_color_light;
         // this.form.secondary_color = this.currentCustomisation.secondary_color;
         // this.form.secondary_color_light = this.currentCustomisation.secondary_color_light;
         // this.buttonForm.button_style = this.currentCustomisation.button_style;
      },
      submitCustomisations() {
         this.form.post(route('admin.customisations.store'), {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               this.fetchCustomisations();
               setTimeout( () => {
                  this.iframeKey++;
               }, 2000)
               this.$toast.success('Website customisations saved successfully!', 'Success');
            },
            onError: (errors) => {
               console.log(errors);
               this.$toast.error('Something went wrong! Try again!');
            }
         })
      },
      updateCustomisations() {
         this.form.patch(route('admin.customisations.update', this.currentCustomisation.hashid), {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               this.fetchCustomisations();
               setTimeout( () => {
                  this.iframeKey++;
               }, 1000)
               this.$toast.success('Website customisations updated successfully!', 'Success');
            },
            onError: (errors) => {
               console.log(errors);
               this.$toast.error('Something went wrong! Try again!');
            }
         })
      },
      isNotDefault() {
         const d = this.defaultCustomisation;
         const c = this.currentCustomisation;
         return (
            c?.primary_color !== d.primary_color
            // c?.primary_color_light !== d.primary_color_light ||
            // c?.secondary_color !== d.secondary_color ||
            // c?.secondary_color_light !== d.secondary_color_light
         );
      },
      setDefaultCustomisation() {
         this.form.primary_color = this.defaultCustomisation.primary_color;
         // this.form.primary_color_light = this.defaultCustomisation.primary_color_light;
         // this.form.secondary_color = this.defaultCustomisation.secondary_color;
         // this.form.secondary_color_light = this.defaultCustomisation.secondary_color_light;
      },
      // updateButtonStyles() {
      //    this.buttonForm.patch(route('admin.customisations.button-styles', this.currentCustomisation.hashid), {
      //       onSuccess: () => {
      //          this.form.reset();
      //          this.form.clearErrors();
      //          this.$toast.success('Button style updated!', 'Success');
      //          this.fetchCustomisations();
      //          setTimeout( () => {
      //             this.iframeKey++;
      //          }, 1000)
      //       },
      //       onError: (error) => {
      //          console.log(error);
      //          this.$toast.error('Something went wrong! Please try again', 'Error')
      //       }
      //    })
      // },
   },
}
</script>

<style scoped>
.iframe {
   width: 100%;
   height: 520px;
   border-radius: 5px;
   border: none;
   overflow: hidden;
}
.tab-content {
   padding: 0 !important;
   background-color: transparent;
   border: none;
   box-shadow: none;
}
.primary-btn {
   background-color: var(--ecbz-primary);
   color: #ffffff;
   font-weight: 700;
   padding: 12px 25px;
   border: none;
   border-radius: 5px;
   cursor: pointer;
   font-size: 1rem;
   text-align: center;
   transition: all 0.8s ease;
   position: relative;
}
.primary-btn.btn-outline {
   border: 2px solid var(--ecbz-primary);
   border-radius: 30px;
   background-color: transparent;
   color: var(--ecbz-primary);
   transition: all 0.6s ease;
}
.primary-btn.btn-outline:hover {
   background-color: var(--ecbz-primary);
   color: #ffffff;
}
.form-check {
   margin-bottom: 0;
}
</style>
