<template>
   <Head title="System Settings"/>
   
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">System Settings</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item">
                  Settings
               </li>
            </ol>
         </nav>
         
         <div class="col-12">
            <div class="mb-3">
               <ul class="nav nav-pills mb-5" role="tablist">
                  <li class="nav-item me-1" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'divisions' }" @click="activeTab = 'divisions'">
                        Divisions
                     </button>
                  </li>
                  <li class="nav-item me-1" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'streams' }" @click="activeTab = 'streams'">
                        Streams
                     </button>
                  </li>
                  <li class="nav-item me-1" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'subjects' }" @click="activeTab = 'subjects'">
                        Subjects
                     </button>
                  </li>
                  <li class="nav-item me-1" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'religions' }" @click="activeTab = 'religions'">
                        Religions
                     </button>
                  </li>
                  <li class="nav-item me-1" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'relationships' }" @click="activeTab = 'relationships'">
                        Relationships
                     </button>
                  </li>
                  <li class="nav-item me-1" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'honorifics' }" @click="activeTab = 'honorifics'">
                        Honorifics
                     </button>
                  </li>
                  <li class="nav-item me-1" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'job-titles' }" @click="activeTab = 'job-titles'">
                        Job Titles
                     </button>
                  </li>
                  <li class="nav-item me-1" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'employment-types' }" @click="activeTab = 'employment-types'">
                        Employment Types
                     </button>
                  </li>
                  <li class="nav-item me-1" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'employment-statuses' }" @click="activeTab = 'employment-statuses'">
                        Employment Statuses
                     </button>
                  </li>
                  <li class="nav-item me-1" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'salary-scales' }" @click="activeTab = 'salary-scales'">
                        Salary Scales
                     </button>
                  </li>
                  <li class="nav-item me-1" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'salary-grades' }" @click="activeTab = 'salary-grades'">
                        Salary Grades
                     </button>
                  </li>
                  <li class="nav-item me-1" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'qualification-types' }" @click="activeTab = 'qualification-types'">
                        Qualification Types
                     </button>
                  </li>
                  <li class="nav-item me-1" role="presentation">
                     <button type="button" class="nav-link" :class="{ active: activeTab === 'blog-categories' }" @click="activeTab = 'blog-categories'">
                        Blog Categories
                     </button>
                  </li>
               </ul>
               
               <div v-if="activeTab === 'divisions'">
                  <divisions></divisions>
               </div>
               <div v-if="activeTab === 'streams'">
                  <streams></streams>
               </div>
               <div v-if="activeTab === 'subjects'">
                  <subjects></subjects>
               </div>
               <div v-if="activeTab === 'honorifics'">
                  <honorifics></honorifics>
               </div>
               <div v-if="activeTab === 'religions'">
                  <religions></religions>
               </div>
               <div v-if="activeTab === 'relationships'">
                  <relationships></relationships>
               </div>
               <div v-if="activeTab === 'job-titles'">
                  <job-titles></job-titles>
               </div>
               <div v-if="activeTab === 'employment-types'">
                  <employment-types></employment-types>
               </div>
               <div v-if="activeTab === 'employment-statuses'">
                  <employment-statuses></employment-statuses>
               </div>
               <div v-if="activeTab === 'salary-scales'">
                  <salary-scales></salary-scales>
               </div>
               <div v-if="activeTab === 'salary-grades'">
                  <salary-grades></salary-grades>
               </div>
               <div v-if="activeTab === 'qualification-types'">
                  <qualification-types></qualification-types>
               </div>
               <div v-if="activeTab === 'blog-categories'">
                  <blog-categories></blog-categories>
               </div>
            </div>
         </div>
      </div>
   </DefaultLayout>
</template>

<script>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import {Link, Head} from "@inertiajs/vue3";
import Divisions from "./Divisions.vue";
import Streams from "./Streams.vue";
import Subjects from "./Subjects.vue";
import Religions from "./Religions.vue";
import Relationships from "./Relationships.vue";
import Honorifics from "./Honorifics.vue";
import JobTitles from "./JobTitles.vue";
import EmploymentTypes from "./EmploymentTypes.vue";
import EmploymentStatuses from "./EmploymentStatuses.vue";
import SalaryScales from "./SalaryScale.vue";
import SalaryGrades from "./SalaryGrade.vue";
import QualificationTypes from "./QualificationTypes.vue";
import BlogCategories from "./BlogCategories.vue";

export default {
   components: {
      DefaultLayout, Head, Link, Divisions, Streams, Subjects, Religions,
      Relationships, Honorifics, JobTitles, EmploymentTypes, EmploymentStatuses,
      SalaryScales, SalaryGrades, QualificationTypes, BlogCategories},
   data() {
      return {
         activeTab: 'divisions'
      }
   }
}
</script>

<style scoped>
.nav-pills .nav-item .nav-link:not(.active) {
   border: 1px solid #e9ecef;
}
</style>
