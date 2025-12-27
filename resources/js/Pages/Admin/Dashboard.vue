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
      </div> <!-- Closing div for the first row -->

      <div class="row mt-4">
         <div class="col-12">
            <div class="card h-100">
               <div class="card-header d-flex align-items-center justify-content-between">
                  <h5 class="card-title m-0 text-primary"><i class="bx bx-table me-2"></i>Student Distribution per Class</h5>
                  <small class="text-muted">Breakdown of students across active classes</small>
               </div>
               <div class="card-body">
                  <div class="table-responsive text-nowrap">
                     <table class="table table-hover border-top">
                        <thead class="bg-light">
                           <tr>
                              <th>Class Name</th>
                              <th>Number of Students</th>
                              <th>Capacity Visualization</th>
                           </tr>
                        </thead>
                        <tbody class="table-border-bottom-0">
                           <tr v-for="(item, index) in studentDistribution" :key="index">
                              <td>
                                 <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-3">
                                       <span class="avatar-initial rounded-circle bg-label-primary">{{ item.class_name.charAt(0) }}</span>
                                    </div>
                                    <span class="fw-bold text-heading">{{ item.class_name }}</span>
                                 </div>
                              </td>
                              <td>
                                 <span class="badge bg-label-primary px-3">{{ item.student_count }} Students</span>
                              </td>
                              <td style="min-width: 200px;">
                                 <div class="progress" style="height: 10px;">
                                    <div 
                                       class="progress-bar bg-primary shadow-none" 
                                       role="progressbar" 
                                       :style="{ width: (item.student_count > 0 ? (item.student_count / Math.max(...studentDistribution.map(i => i.student_count), 50)) * 100 : 0) + '%' }"
                                    ></div>
                                 </div>
                              </td>
                           </tr>
                           <tr v-if="!studentDistribution?.length">
                              <td colspan="3" class="text-center py-4">
                                 <div class="text-muted">No active classes found</div>
                              </td>
                           </tr>
                        </tbody>
                     </table>
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
   classesCount: Number,
   studentDistribution: Array
});
</script>