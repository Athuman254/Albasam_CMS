<template>

   <Head title="Exam Results Report" />
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Exam Results Report</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Exam Results Report
               </li>
            </ol>
         </nav>

         <div class="col-lg-12">
            <div class="card">
               <div class="card-header flex-column flex-md-row">
                  <div class="row row-gap-1">
                     <div class="col-md-8">
                        <h5>Select Exam & Class</h5>
                        <div class="d-flex gap-3">
                           <v-select class="flex-1" v-model="selectedExam" :options="exams" label="name"
                              :reduce="option => option.id" placeholder="Select Exam"></v-select>

                           <v-select class="flex-1" v-model="selectedClass" :options="classes" label="name"
                              :reduce="option => option.id" placeholder="Select Class">
                             <template #option="{ name, stream }">
                              <strong>{{ name }}</strong> - <small>{{ stream.name }}</small>
                           </template>


                           <template #selected-option="props">
                              {{ props.name }} - {{ props.stream?.name }}
                           </template>
                           </v-select>

                           <button class="btn btn-primary" @click="loadResults">
                              Load Results
                           </button>
                        </div>
                     </div>
                     <div class="col-md-4 text-end">
                        <!-- <button class="btn btn-outline-secondary" @click="printReport" v-if="students.length">
                           <i class="bi bi-printer"></i> Print Report
                        </button> -->
                     </div>
                  </div>
               </div>

               <div class="card-body" id="results-section">
                  <div v-if="students.length">
                     <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                           <thead>
                              <tr>
                                 <th>Adm No</th>
                                 <th>Student Name</th>
                                 <th v-for="subject in subjects" :key="subject.id">
                                    {{ subject.subject?.name }} <br />
                                    <small class="text-muted">/{{ subject.max_marks }}</small>
                                 </th>
                                 <th>Total</th>
                                 <th>Actions</th>
                              </tr>
                           </thead>
                           <tbody>
                              <tr v-for="student in students" :key="student.id">
                                 <td>{{ student.adm_no }}</td>
                                 <td>{{ student.name }}</td>
                                 <td v-for="subject in subjects" :key="subject.id">
                                    {{ marks[student.id]?.[subject.id] ?? '-' }}
                                 </td>
                                 <td><strong>{{ getTotal(student.id) }}</strong></td>
                                 <td>
                                    <div class="dropdown">
                                       <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                                          data-bs-toggle="dropdown">
                                          Actions
                                       </button>
                                       <ul class="dropdown-menu">
                                          <li>
                                             <a class="dropdown-item"
                                                :href="`/admin/exams/reports/student/${student.id}?exam_id=${selectedExam}&class_id=${selectedClass}`"
                                                target="_blank">
                                                <i class="bi bi-file-earmark-pdf"></i> Generate Report
                                             </a>
                                          </li>
                                       </ul>
                                    </div>
                                 </td>
                              </tr>
                           </tbody>
                        </table>

                     </div>
                  </div>
                  <div v-else class="text-muted text-center p-3">
                     Select an exam and class to view results.
                  </div>
               </div>
            </div>
         </div>
      </div>
   </DefaultLayout>
</template>

<script setup>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import { ref, onMounted } from "vue";
import axios from "axios";

const exams = ref([]);
const classes = ref([]);
const students = ref([]);
const subjects = ref([]);
const marks = ref({});

const selectedExam = ref(null);
const selectedClass = ref(null);
const fetchExams = async () => {

   try {
      const { data } = await axios.get("/datatable/exams");
      exams.value = data.data;
   } catch (e) {
         toast.error("Failed to load exams")
      console.error("Failed to load exams");
   }
};

const fetchClasses = async () => {
   try {
      const { data } = await axios.get("/datatable/ranks");
      classes.value = data.data;
   } catch (e) {
      toast.error("Failed to load classes")
      console.error("Failed to load classes");
   }
};

const loadResults = async () => {
   if (!selectedExam.value || !selectedClass.value) return;

   try {

      const { data } = await axios.get("/datatable/enrolled-students", {
         params: { exam_id: selectedExam.value, class_id: selectedClass.value },
      });
      students.value = data.enrolled_students;

      const subResp = await axios.get(`/datatable/exam-subjects`, {
         params: { filter: { class_id: selectedClass.value, exam_id: selectedExam.value } },
      });
      subjects.value = subResp.data.data;

      marks.value = {};
      students.value.forEach((student) => {
         marks.value[student.id] = {};
      });


      const marksResp = await axios.get(`/datatable/exam-marks`, {
         params: { exam_id: selectedExam.value, class_id: selectedClass.value },
      });

      marksResp.data.data.forEach((row) => {
         if (!marks.value[row.student_id]) marks.value[row.student_id] = {};
         marks.value[row.student_id][row.exam_subject_id] = row.marks_obtained;
      });
   } catch (error) {
      toast.error("Failed to load results")
      console.error("Error loading results", error);
   }
};

const getTotal = (studentId) => {
   if (!marks.value[studentId]) return 0;
   return Object.values(marks.value[studentId]).reduce(
      (sum, mark) => sum + (parseFloat(mark) || 0),
      0
   );
};


onMounted(() => {
   fetchExams();
   fetchClasses();
});
</script>
