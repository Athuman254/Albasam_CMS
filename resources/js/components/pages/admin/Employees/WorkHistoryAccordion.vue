<template>
   <div class="card accordion-item">
      <h2 class="accordion-header border-bottom">
         <button
            type="button"
            class="accordion-button"
            :class="{ collapsed: openAccordion !== sectionName }"
            @click="$emit('toggle', 'history')">
            Work History Details
         </button>
      </h2>
      <div class="accordion-collapse" :class="{ show: openAccordion === sectionName }">
         <div class="accordion-body py-4 d-flex align-items-baseline flex-wrap flex-xl-nowrap flex-sm-nowrap flex-md-wrap">
            <table class="table table-sm table-bordered text-nowrap" style="max-width: inherit;">
               <thead>
               <tr>
                  <th class="p-2 fw-medium text-heading" style="width: 35%;">Name</th>
                  <th class="p-2 fw-medium text-heading" style="width: 25%;">Start Date</th>
                  <th class="p-2 fw-medium text-heading" style="width: 25%;">End Date</th>
                  <th class="p-2 fw-medium text-heading" style="width: 10%;"></th>
               </tr>
               </thead>
               <tbody>
               <tr v-for="(history, index) in workHistories" :key="index">
                  <td class="p-2">{{ history.institution_name ?? '-' }}</td>
                  <td class="p-2">{{ history.start_date ?? '-' }}</td>
                  <td class="p-2">{{ history.end_date ?? '-' }}</td>
                  <td class="p-2"></td>
               </tr>
               </tbody>
            </table>
         </div>
      </div>
   </div>
</template>

<script>
export default {
   props: ['workHistories', 'openAccordion', 'sectionName'],
}
</script>
