<script>
import { Head, Link } from '@inertiajs/vue3';
import DefaultLayout from "@layouts/DefaultLayout.vue";
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';

export default {
   components: {DefaultLayout, Head, Link, UpdateProfileInformationForm, UpdatePasswordForm, DeleteUserForm},
   props: {
      mustVerifyEmail: {
         type: Boolean,
      },
      status: {
         type: String,
      },
      user: {
         type: Object,
      },
   },
   data() {
      return {
         activeTab: 'account',
      }
   },
}
</script>

<template>
   <Head title="Profile" />
   
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Profile</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Profile
               </li>
            </ol>
         </nav>
         
         <div class="col-xl-4 col-lg-5 col-md-4 order-1 order-md-0">
            <div class="card mb-6">
               <div class="card-body">
                  <small class="card-text text-uppercase text-body-secondary text-secondary small">ABOUT</small>
                  <ul class="list-unstyled my-3 py-1">
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-user"></i>
                        <span class="fw-medium mx-2">Name :</span>
                        <span>{{ user.name }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-user-circle"></i>
                        <span class="fw-medium mx-2">Username :</span>
                        <span>{{ user.username }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-envelope"></i>
                        <span class="fw-medium mx-2">Email :</span>
                        <span>{{ user.email }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-phone"></i>
                        <span class="fw-medium mx-2">Phone :</span>
                        <span>{{ user.phone }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-check"></i>
                        <span class="fw-medium mx-2">Status :</span>
                        <span v-if="user.activated" class="badge bg-success">Active</span>
                        <span v-else class="badge bg-danger">Deactivated</span>
                     </li>
                  </ul>
               </div>
            </div>
         </div>
         
         <div class="col-xl-8 col-lg-7 col-md-8 order-2 order-md-0">
            <div class="nav-align-top">
               <ul class="nav nav-pills mb-4" role="tablist">
                  <li class="nav-item" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'account' }" @click="activeTab = 'account'">
                        <i class="icon-base bx bx-user me-2"></i> Account Information
                     </button>
                  </li>
                  
                  <li class="nav-item" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'password' }" @click="activeTab = 'password'">
                        <i class="icon-base bx bxs-key me-2"></i> Change Password
                     </button>
                  </li>
                  
<!--                  <li class="nav-item" role="presentation">-->
<!--                     <button type="button" class="nav-link" :class="{ active: activeTab === 'delete' }" @click="activeTab = 'delete'">-->
<!--                        <i class="icon-base bx bx-trash me-2"></i> Delete Account-->
<!--                     </button>-->
<!--                  </li>-->
               </ul>
            </div>
            
            <div v-show="activeTab === 'account'" class="card">
               <div class="card-header">
                  <h5 class="mb-0">Profile Information</h5>
                  <span class="text-muted">Update your account's profile information and email address.</span>
               </div>
               <div class="card-body">
                  <UpdateProfileInformationForm
                     :user="user"
                     :must-verify-email="mustVerifyEmail"
                     :status="status"
                  />
               </div>
            </div>
            
            <div v-show="activeTab === 'password'" class="card">
               <div class="card-header">
                  <h5 class="mb-0">Update Password</h5>
                  <span class="text-muted">Ensure your account is using a long, random password to stay secure.</span>
               </div>
               <div class="card-body">
                  <UpdatePasswordForm />
               </div>
            </div>
            
<!--            <div v-show="activeTab === 'delete'" class="card">-->
<!--               <div class="card-header">-->
<!--                  <h5 class="mb-0">Delete Account</h5>-->
<!--                  <span class="text-muted">Once your account is deleted, all of its resources and data will-->
<!--                     be permanently deleted. Before deleting your account, please-->
<!--                     download any data or information that you wish to retain.-->
<!--                  </span>-->
<!--               </div>-->
<!--               <div class="card-body">-->
<!--                  <DeleteUserForm />-->
<!--               </div>-->
<!--            </div>-->
         </div>
      </div>
   </DefaultLayout>
</template>
