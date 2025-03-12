<template>
   <div class="row">
      <h3 class="mb-0">Outbox</h3>
      <nav class="mb-3">
         <ol class="breadcrumb">
            <li class="breadcrumb-item">
               <Link href="/admin/dashboard">Home</Link>
            </li>
            <li class="breadcrumb-item">
               Sms
            </li>
            <li class="breadcrumb-item text-primary">
               Outbox
            </li>
         </ol>
      </nav>
      
      <div class="col-xxl-12">
         <div class="card">
            <div class="card-header flex-column flex-md-row">
               <div class="row row-gap-1">
                  <div class="col-md-3 col-9">
                     <input type="search" id="search" class="form-control bg-muted-lt rounded-2" placeholder="Search..."
                            @input="applyFilter" v-model="appendParams.filter.status">
                  </div>
                  <div class="col-md-6 col-3 ms-lg-auto">
                     <div class="flex-wrap text-end">
                     
                     </div>
                  </div>
               </div>
            </div>
            
            <VueTable
               api-url="datatable/sms/outbox"
               :fields="fields"
               ref="outboxTable"
               :append-params="appendParams"
            >
               <template v-slot:actions="props">
                  <div class="dropdown">
                     <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical"></i>
                     </button>
                     <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="#" @click="editRank(props.rowData)">
                           <i class="bx bx-edit-alt me-2"></i>Edit
                        </a>
                        <a class="dropdown-item text-danger" href="#">
                           <i class="bx bx-trash me-2"></i>Delete
                        </a>
                     </div>
                  </div>
               </template>
            </VueTable>
         </div>
      </div>
   </div>
</template>

<script>
import _debounce from 'lodash/debounce';
import axios from 'axios';
import {useForm} from "@inertiajs/vue3";
import {Modal} from 'bootstrap';
import {Inertia} from '@inertiajs/inertia';

export default {
   
   data() {
      return {
         fields: [
            {
               name: 'campaign.name',
               title: 'Campain',
            },
            {
               name: 'contact.phone_number',
               title: 'Phone Number',
            },
            {
               name: 'content',
               title: 'Message',
            },
            {
               name: 'status',
               title: 'Status',
            },
            {
               name: 'scheduled_at',
               title: 'Date',
            },
         ],
         appendParams: {
            filter: {
               status: '',
            }
         },
         form: useForm({
            name: '',
            division_id: '',
            stream_id: '',
            teacher_id: '',
         }),
         editForm: useForm({
            id: '',
            name: '',
            division_id: '',
            stream_id: '',
            teacher_id: '',
         }),
         ranks: [],
         divisions: [],
         streams: [],
      };
   },
   created() {
      this.fetchDivisions();
      this.fetchStreams();
      // Re-fetch data when navigating back to this component
      Inertia.on('navigate', (event) => {
         if (event.detail.page.url === '/admin/settings/ranks') {
            this.fetchDivisions();
            this.fetchStreams();
         }
      });
   },
   methods: {
      fetchDivisions() {
         axios.get('/datatable/divisions')
            .then(({data}) => {
               this.divisions = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred when fetching the divisions.')
         })
      },
      fetchStreams() {
         axios.get('/datatable/streams')
            .then(({data}) => {
               this.streams = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred when fetching the streams.')
         })
      },
      createRankModal() {
         const modalElement = this.$refs.createRankModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      createRank() {
         this.form.post('/admin/settings/ranks', {
            onSuccess: () => {
               this.form.reset(); // Reset the form on success
               this.form.clearErrors();
               this.$refs.outboxTable.reloadTable();
               const modalElement = this.$refs.createRankModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Class Created Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      editRank(rowData) {
         this.editForm.id = rowData.hashid; // Assign the ID manually
         this.editForm.name = rowData.name;
         const modalElement = this.$refs.editRankModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      updateRank() {
         this.editForm.patch('/admin/settings/ranks/' + this.editForm.id, {
            onSuccess: () => {
               this.editForm.reset(); // Reset the form on success
               this.editForm.clearErrors();
               this.$refs.classTable.reloadTable();
               const modalElement = this.$refs.editRankModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Class Updated Successfully', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         })
      },
      // applyFilter(){
      //     this.$refs.classTable.reloadTable();
      // },
      applyFilter: _debounce(function () {
         this.$refs.outboxTable.reloadTable();
      }, 800),
   },
}
</script>

<style scoped>
</style>
