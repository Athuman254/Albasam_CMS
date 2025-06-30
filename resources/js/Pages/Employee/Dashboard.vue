<template>
   <Head title="Dashboard"/>
   
   <DefaultLayout>
      <div class="row">
         <h3>Dashboard</h3>
         
         <div class="">
            <div class="col-12">
               <div
                  class="overflow-hidden bg-white shadow-sm sm:rounded-lg"
               >
                  <div class="p-6 text-gray-900">
                     You're logged in!
                  </div>
               </div>
            </div>
         </div>
      </div>
   
   </DefaultLayout>
</template>

<script setup>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import {Head} from '@inertiajs/vue3';
</script>
