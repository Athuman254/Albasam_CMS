<template>
   <div class="card mb-3">
      <div class="card-header py-3">
         <span class="d-none d-sm-block">
            <i class="bx bx-play-circle bx-md me-2"></i>
            <span class="h5 mb-0">Buttons</span>
         </span>
      </div>
   </div>
   
   <div class="card">
      <div class="card-body">
         <p class="fs-6">Choose a button style</p>
         <div class="row">
            <div class="col-12">
               <div class="row mb-4">
                  <div class="col-sm-2 align-content-center justify-content-center">
                     <div class="form-check">
                        <input v-model="form.button_style" name="default-radio-1" class="form-check-input" type="radio" value="btn-outline" id="buttonStyle1">
                     </div>
                  </div>
                  <label class="form-check-label col-sm-10" for="buttonStyle1">
                     <button type="button" class="primary-btn btn-outline w-100">Outlined</button>
                  </label>
               </div>
               <div class="row mb-4">
                  <div class="col-sm-2 align-content-center justify-content-center">
                     <div class="form-check">
                        <input v-model="form.button_style" name="default-radio-1" class="form-check-input" type="radio" value="default"  id="buttonStyle2">
                     </div>
                  </div>
                  <label
                     class="form-check-label col-sm-10" for="buttonStyle2">
                     <button type="button" class="primary-btn w-100">Contained</button>
                  </label>
               </div>
            </div>
            <div class="col-12 mt-8">
               <button type="button" class="btn btn-outline-secondary w-100" @click.prevent="updateButtonStyles">
                  Save
               </button>
            </div>
         </div>
      </div>
   </div>
</template>

<script>
import { useForm } from "@inertiajs/vue3"
export default {
   name: "Buttons",
   inheritAttrs: true,
   props: ['currentCustomisation', 'defaultCustomisation'],
   data() {
      return {
         form: useForm({
            id: '',
            button_style: '',
         })
      }
   },
   watch: {
      currentCustomisation: {
         handler(val) {
            if (val) {
               this.form.id = val.hashid;
               this.form.button_style = val.button_style || '';
            }
         },
         immediate: true,
      },
   },
   methods: {
      updateButtonStyles() {
         this.form.patch(route('admin.customisations.button-styles', this.currentCustomisation.hashid), {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               this.$toast.success('Button style updated!', 'Success');
            },
            onError: (error) => {
               console.log(error);
               this.$toast.error('Something went wrong! Please try again', 'Error')
            }
         })
      },
   }
}
</script>

<style scoped>
.primary-btn {
   background-color: var(--ecbz-primary);
   color: #ffffff;
   font-weight: 700;
   padding: 12px 25px;
   border: none;
   border-radius: 5px;
   cursor: pointer;
   font-size: 1rem;
   text-align: center;
   transition: all 0.8s ease;
   position: relative;
}
.primary-btn.btn-outline {
   border: 2px solid var(--ecbz-primary);
   border-radius: 30px;
   background-color: transparent;
   color: var(--ecbz-primary);
   transition: all 0.6s ease;
}
.primary-btn.btn-outline:hover {
   background-color: var(--ecbz-primary);
   color: #ffffff;
}
.form-check {
   margin-bottom: 0;
}
</style>
