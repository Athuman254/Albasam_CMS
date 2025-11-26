<template>
   <div>
      <div class="d-flex align-items-center mb-3 pb-1">
         <h4 class="mb-0">Employee Information</h4>
         <div class="ms-auto">
            <ul class="list-inline mb-0 d-flex align-items-end">
               <li class="list-inline-item">
                  <button type="button" class="btn btn-primary">
                     Edit Details
                  </button>
               </li>
            </ul>
         </div>
      </div>
      
      <div class="row mb-4">
         <div class="col-md-6">
            <div class="info-group mb-3">
               <h6 class="text-muted mb-1">Employee Number</h6>
               <p class="mb-0">{{ employee.staff_number ?? '-' }}</p>
            </div>
            <div class="info-group mb-3">
               <h6 class="text-muted mb-1">Date of Hire</h6>
               <p class="mb-0">{{ employee.date_of_hire ?? '-' }}</p>
            </div>
            <div class="info-group mb-3">
               <h6 class="text-muted mb-1">Employment Type</h6>
               <p class="mb-0">{{ employee.employment_type?.name ?? '-' }}</p>
            </div>
            <div class="info-group mb-3">
               <h6 class="text-muted mb-1">Employment Status</h6>
               <span class="badge bg-warning">{{ employee.employment_status?.name ?? '-' }}</span>
            </div>
         </div>
         <div class="col-md-6">
            <div class="info-group mb-3">
               <h6 class="text-muted mb-1">Passport/ID Number</h6>
               <p class="mb-0">{{ employee.identification_number ?? '-' }}</p>
            </div>
            <div class="info-group mb-3">
               <h6 class="text-muted mb-1">Tax Identification Number (KRA)</h6>
               <p class="mb-0">{{ employee.tax_identification_pin ?? '-' }}</p>
            </div>
            <div class="info-group mb-3">
               <h6 class="text-muted mb-1">TSC Number</h6>
               <p class="mb-0">{{ employee.teacher?.tsc_number ?? '-' }}</p>
            </div>
            <div class="info-group mb-3">
               <h6 class="text-muted mb-1">Job Title</h6>
               <p class="mb-0">{{ employee.teacher?.job.title ?? '-' }}</p>
            </div>
         </div>
      </div>
      
      <div class="row">
         <div class="col-md-6">
            <div class="info-group mb-3">
               <h6 class="text-muted mb-1">Gender</h6>
               <p class="mb-0">{{ employee.gender?.name ?? '-' }}</p>
            </div>
            <div class="info-group mb-3">
               <h6 class="text-muted mb-1">Religion</h6>
               <p class="mb-0">{{ employee.religion?.name ?? '-' }}</p>
            </div>
            <div class="info-group mb-3">
               <h6 class="text-muted mb-1">Marital Status</h6>
               <p class="mb-0">{{ employee.marital_status?.name ?? '-' }}</p>
            </div>
         </div>
         <div class="col-md-6">
            <div class="info-group mb-3">
               <h6 class="text-muted mb-1">Specialization</h6>
               <p class="mb-0">{{ employee.specialization ?? 'To be pulled' }}</p>
            </div>
            <div class="info-group mb-3">
               <h6 class="text-muted mb-1">Years of Experience</h6>
               <p class="mb-0">{{ employee.teacher?.years_of_experience || 'Not specified' }}</p>
            </div>
         </div>
      </div>
      <h4 class="mt-4">Contact Information</h4>
      <div class="row">
         <div class="col-md-6">
            <div class="info-group mb-3">
               <h6 class="text-muted mb-1">Email</h6>
               <p class="mb-0">{{ employee.email ?? '-' }}</p>
            </div>
            <div class="info-group mb-3">
               <h6 class="text-muted mb-1">Primary Phone</h6>
               <p class="mb-0">{{ employee.primary_phone ?? '-' }}</p>
            </div>
            <div class="info-group mb-3">
               <h6 class="text-muted mb-1">Secondary Phone</h6>
               <p class="mb-0">{{ employee.secondary_phone ?? '-' }}</p>
            </div>
         </div>
         <div class="col-md-6">
            <div class="info-group mb-3">
               <h6 class="text-muted mb-1">Permanent Address</h6>
               <p class="mb-0">{{ employee.permanent_physical_address ?? '-' }}</p>
            </div>
            <div class="info-group mb-3">
               <h6 class="text-muted mb-1">Secondary Address</h6>
               <p class="mb-0">{{ employee.secondary_physical_address ?? '-' }}</p>
            </div>
            <div class="info-group mb-3">
               <h6 class="text-muted mb-1">Postal Address</h6>
               <p class="mb-0">{{ employee.postal_address ?? '-' }}</p>
            </div>
         </div>
      </div>
   </div>
</template>

<script setup>
const props = defineProps({
   employee:{
      type:Object,
      required:true
   }
})
</script>
