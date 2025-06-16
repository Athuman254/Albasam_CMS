<template>
   <Head title="System Settings"/>
   
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">System Settings</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item">
                  Settings
               </li>
            </ol>
         </nav>
         
         <div class="col-12">
            <div class="mb-3">
               <ul class="nav nav-pills mb-5" role="tablist">
                  <li class="nav-item me-2" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'divisions' }" @click="activeTab = 'divisions'">
                        Divisions
                     </button>
                  </li>
                  <li class="nav-item me-2" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'streams' }" @click="activeTab = 'streams'">
                        Streams
                     </button>
                  </li>
                  <li class="nav-item me-2" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'subjects' }" @click="activeTab = 'subjects'">
                        Subjects
                     </button>
                  </li>
               </ul>
               
               <div v-if="activeTab === 'divisions'">
                  <divisions></divisions>
               </div>
               <div v-if="activeTab === 'streams'">
                  <streams></streams>
               </div>
               <div v-if="activeTab === 'subjects'">
                  <subjects></subjects>
               </div>
            </div>
         </div>
      </div>
   </DefaultLayout>
</template>

<script>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import {Link, Head, useForm} from "@inertiajs/vue3";
import Divisions from "./Divisions.vue";
import Streams from "./Streams.vue";
import Subjects from "./Subjects.vue";

export default {
   components: {DefaultLayout, Head, Link, Divisions, Streams, Subjects},
   data() {
      return {
         activeTab: 'divisions'
      }
   }
}
</script>
