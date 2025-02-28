<template>
   <div class="row">
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
                  <button
                     type="button"
                     class="nav-link"
                     :class="{ active: activeTab === 'account' }"
                     @click="activeTab = 'account'"
                  >
                     <i class="bx bx-user me-2"></i> Account
                  </button>
               </li>
   <!--            <li class="nav-item" role="presentation">-->
   <!--               <button-->
   <!--                   type="button"-->
   <!--                   class="nav-link"-->
   <!--                   :class="{ active: activeTab === 'security' }"-->
   <!--                   @click="activeTab = 'security'"-->
   <!--               >-->
   <!--                   <i class="bx bx-lock me-2"></i> Security-->
   <!--               </button>-->
   <!--            </li>-->
            </ul>
         </div>
         <div v-show="activeTab === 'account'" class="card">
            <h5 class="card-header">
               Account Settings
            </h5>
            <div class="card-body">
               <div class="row gx-6">
                  <div class="col-12 col-lg-6">
                     <div class="mb-4">
                        <label for="name" class="form-label">Name</label>
                        <input id="name" type="text" v-model="form.name" class="form-control">
                        <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
                     </div>
                  </div>
                  <div class="col-12 col-lg-6">
                     <div class="mb-4">
                        <label for="username" class="form-label">Username</label>
                        <input id="username" type="text" v-model="form.username" class="form-control">
                        <div v-if="form.errors.username" class="text-danger">{{ form.errors.username }}</div>
                     </div>
                  </div>
                  <div class="col-12 col-lg-6">
                     <div class="mb-4">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" v-model="form.email" class="form-control">
                        <div v-if="form.errors.email" class="text-danger">{{ form.errors.email }}</div>
                     </div>
                  </div>
                  <div class="col-12 col-lg-6">
                     <div class="mb-4">
                        <label for="phone" class="form-label">Phone</label>
                        <input id="phone" type="text" v-model="form.phone" class="form-control">
                        <div v-if="form.errors.phone" class="text-danger">{{ form.errors.phone }}</div>
                     </div>
                  </div>
                  <div class="col-12 col-lg-6">
                     <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password" v-model="form.password" class="form-control">
                        <div v-if="form.errors.password" class="text-danger">{{ form.errors.password }}</div>
                     </div>
                  </div>
               </div>
            </div>
            <div class="card-footer">
               <button type="button" class="btn btn-primary" @click.prevent="submitForm">Update</button>
            </div>
         </div>
<!--         <div v-show="activeTab === 'security'" class="card">-->
<!--            <h5 class="card-header">-->
<!--               Security Settings-->
<!--            </h5>-->
<!--            <div class="card-body">-->
<!--               <p>Security settings go here.</p>-->
<!--            </div>-->
<!--         </div>-->
      </div>
   </div>
</template>

<script>
import {useForm} from "@inertiajs/vue3";

export default {
   props: ['user'],
   
   data() {
      return {
         form: useForm({
            name: '',
            username: '',
            email: '',
            phone: '',
            password: '',
         }),
         activeTab: "account",
      }
   },
   created() {
      if (this.user) {
         this.form.name = this.user.name;
         this.form.username = this.user.username;
         this.form.email = this.user.email;
         this.form.phone = this.user.phone;
      }
   },
   methods: {
      submitForm() {
         this.form.post('/profile/update', {
            onSuccess: () => {
               this.form.clearErrors();
               this.$toast.success('Details Updated Successfully', 'Success')
            },
            onError: (errors) => {
               console.log(errors);
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         })
      }
   }
}
</script>

<style></style>
