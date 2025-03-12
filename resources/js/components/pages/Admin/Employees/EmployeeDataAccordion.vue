<template>
   <div class="card accordion-item">
      <h2 class="accordion-header border-bottom">
         <button
            type="button"
            class="accordion-button"
            :class="{ collapsed: openAccordion !== sectionName }"
            @click="$emit('toggle', 'contact')">
            Employee Details
         </button>
      </h2>
      <div class="accordion-collapse" :class="{ show: openAccordion === sectionName }">
         <div class="accordion-body px-0 py-4 d-flex align-items-baseline flex-wrap flex-xl-nowrap flex-sm-nowrap flex-md-wrap">
            <table class="table table-sm table-borderless text-nowrap small">
               <tbody>
               <tr>
                  <td class="w-50">Employee Number</td>
                  <td class="fw-medium text-heading">{{ employee.staff_number ?? '-' }}</td>
               </tr>
               <tr>
                  <td>Passport/ID Number</td>
                  <td class="fw-medium text-heading">{{ employee.identification_number ?? '-'}}</td>
               </tr>
               <tr>
                  <td>Tax Identification Number (KRA)</td>
                  <td class="fw-medium text-heading">{{ employee.tax_identification_pin ?? '-' }}</td>
               </tr>
               <tr>
                  <td>TSC Number</td>
                  <td class="fw-medium text-heading">{{ teacher.tsc_number ?? '-'}}</td>
               </tr>
               <tr>
                  <td>Specialization</td>
                  <td class="fw-medium text-heading">{{ teacher.specialization?.name ?? '-'}}</td>
               </tr>
               </tbody>
            </table>
            <table class="table table-sm table-borderless text-nowrap small">
               <tbody>
               <tr>
                  <td class="w-50">Date of hire</td>
                  <td class="fw-medium text-heading">{{ employee.date_of_hire ?? '-' }}</td>
               </tr>
               <tr>
                  <td>Employment Type</td>
                  <td class="fw-medium text-heading">{{ employee.employment_type?.name ?? '-'}}</td>
               </tr>
               <tr>
                  <td>Employment Status</td>
                  <td class="fw-medium text-heading">{{ employee.employment_status?.name ?? '-' }}</td>
               </tr>
               <tr>
                  <td>Job Title</td>
                  <td class="fw-medium text-heading">{{ employee.job_title?.name ?? '-'}}</td>
               </tr>
               <tr>
                  <td>Year of experience</td>
                  <td class="fw-medium text-heading">{{ teacher.years_of_experience + ' yrs' ?? '-'}}</td>
               </tr>
               </tbody>
            </table>
         </div>
      </div>
   </div>
</template>

<script>
export default {
   props: ['employee', 'teacher', 'openAccordion', 'sectionName'],
}
</script>
