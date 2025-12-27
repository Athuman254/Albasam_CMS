<template>

   <Head title="Enroll Students to Exam" />
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Enroll Students to Exam</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Enroll Students
               </li>
            </ol>
         </nav>

         <div class="col-lg-12">
            <div class="card">
               <div class="card-header flex-column flex-md-row">
                  <div class="row row-gap-1">
                     <div class="col-md-6">
                        <h5>Select Exam & Class</h5>
                        <div class="d-flex gap-3">
                           <v-select class="flex-1" v-model="selectedExam" :options="exams" label="name"
                              :reduce="option => option.id" placeholder="Select Exam"></v-select>

                           <v-select class="flex-1" v-model="selectedClass" :options="classes" label="name"
                              :reduce="option => option.id" placeholder="Select Class">
                               <template #option="{ name, stream }">
                              <strong>{{ name }}</strong> - <small>{{ stream?.name || 'No Stream' }}</small>
                           </template>


                           <template #selected-option="props">
                              {{ props.name }} - {{ props.stream?.name || 'No Stream' }}
                           </template>
                           </v-select>

                           <button class="btn btn-primary" @click="loadStudents">Load</button>
                        </div>
                     </div>
                  </div>
               </div>

               <div class="card-body">
                  <div v-if="students.length">
                     <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                           <thead>
                              <tr>
                                 <th>
                                    <input type="checkbox" v-model="selectAll" @change="toggleSelectAll">
                                 </th>
                                 <th>Adm No</th>
                                 <th>Student Name</th>
                              </tr>
                           </thead>
                           <tbody>
                              <tr v-for="student in students" :key="student.id">
                                 <td>
                                    <input type="checkbox" v-model="selectedStudents" :value="student.id">
                                 </td>
                                 <td>{{ student.admission_number }}</td>
                                 <td>{{ student.first_name	 }} {{ student.middle_name	 }} {{ student.last_name }}</td>
                              </tr>
                           </tbody>
                        </table>
                     </div>

                     <div class="text-end mt-3">
                        <button class="btn btn-success" @click="saveEnrollment">
                           Save Enrollment
                        </button>
                     </div>
                  </div>

                  <div v-else class="text-muted text-center p-3">
                     Select an exam and class to load students.
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
import { ref, onMounted, watch } from "vue";
import axios from "axios";
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';


const exams = ref([]);
const classes = ref([]);
const students = ref([]);
const selectedExam = ref(null);
const selectedClass = ref(null);

const selectedStudents = ref([]);
const selectAll = ref(false);

const fetchExams = () => {
   axios.get("/datatable/exams")
      .then(({ data }) => exams.value = data.data)
      .catch(() =>  toast.error("Error loading exams"));
};

const fetchClasses = () => {
   axios.get("/datatable/ranks")
      .then(({ data }) => classes.value = data.data)
      .catch(() =>  toast.error("Error loading classes"));
};

const loadStudents = async () => {
   if (!selectedExam.value || !selectedClass.value) return;

   const res = await axios.get(`/datatable/students`, {
      params: {
         filter: {
            rank_id: selectedClass.value
         }
         ,size:500
      }
   });
   students.value = res.data.data
   const { data } = await axios.get(`/datatable/enrolled-students`, {
      params: {
         exam_id: selectedExam.value,
         class_id: selectedClass.value
      }
   });

   selectedStudents.value = data.enrolled_student_ids || [];
   selectAll.value = selectedStudents.value.length === students.value.length;
};

const toggleSelectAll = () => {
   if (selectAll.value) {
      selectedStudents.value = students.value.map(s => s.id);
   } else {
      selectedStudents.value = [];
   }
};

const saveEnrollment = () => {
   axios.post("/admin/exams/exam-students", {
      exam_id: selectedExam.value,
      class_id: selectedClass.value,
      student_ids: selectedStudents.value
   })
      .then(() =>  toast.success("Enrollment saved successfully!"))
      .catch(() =>  toast.error("Failed to load results"));
};

watch(selectedStudents, (newVal) => {
   selectAll.value = newVal.length === students.value.length;
});

onMounted(() => {
   fetchExams();
   fetchClasses();
});
</script>
