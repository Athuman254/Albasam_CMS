<template>
   <div class="row">
      <div class="mb-4">
         <h5 class="mb-0">Student Details</h5>
         <small>Enter Student Details</small>
      </div>
      <div class="col-md-4">
         <div class="form-group mb-3">
            <label class="form-label" for="firstName">Registration Date</label>
            <input type="text" id="firstName" class="form-control"/>
         </div>
      </div>
      <div class="col-md-4">
         <div class="form-group mb-3">
            <label class="form-label" for="firstName">First Name</label>
            <input type="text" id="firstName" class="form-control"/>
         </div>
      </div>
      <div class="col-md-4">
         <div class="form-group mb-3">
            <label class="form-label" for="middleName">Middle Name</label>
            <input type="text" id="middleName" class="form-control"/>
         </div>
      </div>
      <div class="col-md-4">
         <div class="form-group mb-3">
            <label class="form-label" for="lastName">Last Name</label>
            <input type="text" id="lastName" class="form-control"/>
         </div>
      </div>
      <div class="col-md-4">
         <div class="form-group mb-3">
            <label class="form-label" for="genderId">Gender</label>
            <v-select
               id="genderId"
               v-model="studentForm.gender_id"
               :options="genders"
               label="name"
               :reduce="(option) => option.id"
            ></v-select>
         </div>
      </div>
      <div class="col-md-4">
         <div class="form-group mb-3">
            <label class="form-label" for="religionId">Religion</label>
            <v-select
               id="religionId"
               v-model="studentForm.religion_id"
               :options="religions"
               label="name"
               :reduce="(option) => option.id"
            ></v-select>
         </div>
      </div>
      <div class="col-md-4">
         <div class="form-group mb-3">
            <label class="form-label" for="dateOfBirth">Date Of Birth</label>
            <input type="text" id="dateOfBirth" class="form-control"/>
         </div>
      </div>
      <div class="col-md-4">
         <div class="form-group mb-3">
            <label class="form-label" for="birthCertificateNumber">Birth Certificate Number</label>
            <input type="text" id="birthCertificateNumber" class="form-control"/>
         </div>
      </div>
      <div class="col-md-4">
         <div class="form-group mb-3">
            <label class="form-label" for="citizenship">Citizenship</label>
            <input type="text" id="citizenship" class="form-control"/>
         </div>
      </div>
      <div class="col-md-4">
         <div class="form-group mb-3">
            <label class="form-label" for="county">County</label>
            <input type="text" id="county" class="form-control"/>
         </div>
      </div>
      <div class="col-md-4">
         <div class="form-group mb-3">
            <label class="form-label" for="ward">Ward</label>
            <input type="text" id="ward" class="form-control"/>
         </div>
      </div>
   </div>
</template>

<script>
import {useForm} from "@inertiajs/vue3";

export default {
   props: {
      studentDetails: {
         type: Object,
         default: () => ({}),
      },
   },
   data() {
      return {
         studentForm: useForm({
            first_name: '',
            middle_name: '',
            last_name: '',
            date_of_birth: '',
            birth_certificate_number: '',
            gender_id: '',
            religion_id: '',
            citizenship: '',
            county: '',
            ward: '',
            permanent_address: '',
            kpsea_score: '',
            kjsea_score: '',
            kcpe_score: '',
            upi_number: '',
            nemis: '',
            assessment_number: '',
            previous_school: '',
            specialization: '',
         }),
         genders: [],
         religions: [],
      }
   },
   watch: {
      studentDetails: {
         immediate: true, // Populate form immediately when prop changes
         handler(newDetails) {
            if (newDetails) {
               this.studentForm = {...this.studentForm, ...newDetails};
            }
         },
      },
   },
   methods: {
      emitStudentDetails() {
         // Emit studentForm data to the parent
         this.$emit('updateStudentDetails', {...this.studentForm});
      },
   },
   mounted() {
      // Emit initial data in case pre-filled data exists
      this.emitStudentDetails();
   },
}
</script>
