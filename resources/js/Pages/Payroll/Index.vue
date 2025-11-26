<template>

   <Head title="Payroll Run" />
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Payroll Run</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Payroll Run
               </li>
            </ol>
         </nav>
         <div class="col-lg-12">
            <div class="card">
               <div class="card-header flex-column flex-md-row">
                  <div class="row row-gap-1">
                     <div class="col-md-3 col-9">
                        <!-- <input type="search" id="search" class="form-control bg-muted-lt rounded-2"
                           placeholder="Search..." v-model="appendParams.filter.search" @input="applyFilter"> -->
                     </div>
                     <div class="col-md-6 col-3 ms-lg-auto">
                        <div class="flex-wrap text-end">
                           <div class="card-action">
                              <button type="button" class="btn btn-primary d-none d-sm-inline-block"
                                 @click="openRunModal">
                                 <i class="bx bx-plus-circle me-2"></i>
                                 Run
                              </button>
                              <button type="button" class="btn btn-primary btn-icon d-sm-none" @click="openRunModal">
                                 <i class="bx bx-plus"></i>
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- Table Section -->
               <div class="card-body">
                    <VueTable
                  :fields="fields"
                  api-url="datatable/payroll/summary"
                  :append-params="appendParams"
                  ref="usersTable"
               >
               <template v-slot:totalNetPay="props">
                  <div>{{ formatCurrency(props.rowData.totalNetPay /100) }}</div>
               </template>
                  <template v-slot:actions="props">
                     <div class="dropdown">
                        <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                           <i class="icon-base bx bx-dots-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">

                           <Link class="dropdown-item" :href="route('admin.payroll-details',props.rowData.pay_date)" >
                              <i class="icon-base bx bxs-key me-2"></i>Open
                           </Link>
                           <a class="dropdown-item text-danger disabled" href="#">
                              <i class="icon-base bx bx-trash me-2"></i>Delete
                           </a>
                        </div>
                     </div>
                  </template>
               </VueTable>
               </div>
            </div>
         </div>
      </div>


   </DefaultLayout>
</template>

<script setup>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { Modal } from 'bootstrap';
import _debounce from 'lodash/debounce';
import { ref, computed, watch, onMounted } from 'vue'
import axios from "axios";
import { progress } from "izitoast/dist/js/iziToast.min";

const  fields = [

            {
               name: 'month',
               title: 'Month',
            },
            {
               name: '__slot:totalNetPay',
               title: 'Net Pay',
            },
            {
               name: 'processed_by.name',
               title: 'Processed',
            },
            {
               name: '__slot:actions',
               title: 'ACTIONS',
               titleClass: '5%',
               dataClass: '5%',
            },
         ]
const appendParams = ref({
      filter:{

      }
})
function formatCurrency(amount){
   return new Intl.NumberFormat('KES').format(amount)
}
</script>
