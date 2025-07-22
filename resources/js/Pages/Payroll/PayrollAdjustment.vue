<template>
   <Head title="Classes"/>

   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Payroll Adjustments</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Payroll adjustments
               </li>
            </ol>
         </nav>

          <div class="col-12">
            <div class="mb-3">
               <ul class="nav nav-pills mb-5" role="tablist">
                  <li class="nav-item me-1" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'allowance' }" @click="activeTab = 'allowance'">
                        Allowance
                     </button>
                  </li>

                  <li class="nav-item me-1" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'deductions' }" @click="activeTab = 'deductions'">
                        Deductions
                     </button>
                  </li>
               </ul>

               <div v-if="activeTab === 'allowance'">
                  <PayrollAdjustmentAllowance :employees="employees" :allowances="allowances"/>
               </div>
               <div v-if="activeTab === 'deductions'">
                 'deductions'
               </div>
            </div>
         </div>

      </div>
   </DefaultLayout>
</template>

<script setup>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import PayrollAdjustmentAllowance from "./PayrollAdjustmentAllowance.vue";
import {Head, Link, useForm} from "@inertiajs/vue3";
import axios from 'axios';
import {Inertia} from '@inertiajs/inertia';
import {Modal} from 'bootstrap';
import _debounce from 'lodash/debounce';
import { ref  } from 'vue'
const props = defineProps(['employees','allowances'])
const activeTab = ref('allowance')



</script>

<style scoped>
</style>
