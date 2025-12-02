<template>
   <Head title="Dashboard"/>
   
   <DefaultLayout>
      <!-- Greeting Card -->
      <div class="mb-4">
         <GreetingCard 
            :user-name="$page.props.auth.user?.name || 'Admin'" 
            user-type="admin" 
         />
      </div>
      <div class="row">
         <div class="col-lg-4 col-sm-6 mb-3">
            <div class="card h-100">
               <div class="card-body">
                  <div class="d-flex align-items-center gap-4 me-6 me-sm-0">
                     <div class="avatar avatar-lg">
                        <div class="avatar-initial rounded bg-label-primary">
                           <i class="bx bxs-graduation bx-lg"></i>
                        </div>
                     </div>
                     <div class="content-right">
                        <p class="mb-0 fw-semibold">Registered Students</p>
                        <h4 class="text-primary mb-0">{{ studentsCount }}</h4>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-lg-4 col-sm-6 mb-3">
            <div class="card h-100">
               <div class="card-body">
                  <div class="d-flex align-items-center gap-4 me-6 me-sm-0">
                     <div class="avatar avatar-lg">
                        <div class="avatar-initial rounded bg-label-success">
                           <i class="bx bxs-user-badge bx-lg"></i>
                        </div>
                     </div>
                     <div class="content-right">
                        <p class="mb-0 fw-semibold">Teachers</p>
                        <h4 class="text-primary mb-0">{{ teachersCount }}</h4>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-lg-4 col-sm-6 mb-3">
            <div class="card h-100">
               <div class="card-body">
                  <div class="d-flex align-items-center gap-4 me-6 me-sm-0">
                     <div class="avatar avatar-lg">
                        <div class="avatar-initial rounded bg-label-warning">
                           <i class="bx bxs-chalkboard bx-lg"></i>
                        </div>
                     </div>
                     <div class="content-right">
                        <p class="mb-0 fw-semibold">Classes</p>
                        <h4 class="text-primary mb-0">{{ classesCount }}</h4>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </DefaultLayout>
</template>

<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue'; 
import GreetingCard from '@/Components/GreetingCard.vue';
import { Head, Link } from "@inertiajs/vue3";

defineProps({
   studentsCount: Number,
   teachersCount: Number,
   classesCount: Number
});
</script>