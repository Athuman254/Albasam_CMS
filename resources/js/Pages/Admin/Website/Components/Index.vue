<template>
   <Head title="Components"/>
   
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Website Components</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Components
               </li>
            </ol>
         </nav>
         
         <div class="col-12">
            <div class="nav-align-top mb-6">
               <ul class="nav nav-pills mb-5" role="tablist">
                  <li class="nav-item me-2" role="presentation">
                     <button
                        type="button"
                        class="nav-link"
                        :class="{ active: activeTab === 'blogs' }"
                        @click="activeTab = 'blogs'"
                     >
                        Blogs
                     </button>
                  </li>
<!--                  <li class="nav-item me-2" role="presentation">-->
<!--                     <button-->
<!--                        type="button"-->
<!--                        class="nav-link"-->
<!--                        :class="{ active: activeTab === 'vacancies' }"-->
<!--                        @click="activeTab = 'vacancies'"-->
<!--                     >-->
<!--                        Vacancies-->
<!--                     </button>-->
<!--                  </li>-->
               </ul>
               
               <div class="tab-content">
                  <div v-show="activeTab === 'blogs'">
                     <h5 class="mb-0">Blogs</h5>
                     <h6 class="text-muted">Blogs or news that will be displayed on the website.</h6>
                     <Blogs />
                  </div>
<!--                  <div v-show="activeTab === 'faqs'">-->
<!--                     <h5 class="mb-0">Vacancies</h5>-->
<!--                     <h6 class="text-muted">Vacancies or careers to be displayed on the website.</h6>-->
<!--                     <Vacancies />-->
<!--                  </div>-->
               </div>
            </div>
         </div>
      </div>
   </DefaultLayout>
</template>

<script>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import Blogs from "./Blogs.vue"

export default {
   components: {DefaultLayout, Head, Link, Blogs},
   data() {
      return {
         activeTab: 'blogs'
      }
   },
   methods: {},
}
</script>

<style scoped>
.tab-content {
   padding: 0 !important;
   background-color: transparent;
   border: none;
   box-shadow: none;
}
</style>
