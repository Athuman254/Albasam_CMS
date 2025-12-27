<template>
  <Head title="Enter Exam Marks" />
  <DefaultLayout>
    <div class="row">
      <h3 class="mb-0">Enter Exam Marks</h3>
      <nav class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <Link :href="route('admin.dashboard')">Home</Link>
          </li>
          <li class="breadcrumb-item text-primary">
            Enter Exam Marks
          </li>
        </ol>
      </nav>

      <div class="col-lg-12">
        <div class="card">
          <div class="card-header flex-column flex-md-row">
            <div class="row row-gap-1">
              <div class="col-md-8">
                <h5>Select Details</h5>
                <div class="d-flex gap-3 flex-wrap">
                  <v-select class="flex-1" v-model="selectedExam" :options="exams" label="name"
                    :reduce="option => option.id" placeholder="Select Exam" style="min-width: 200px;"></v-select>

                  <v-select class="flex-1" v-model="selectedClass" :options="classes" label="name"
                    :reduce="option => option.id" placeholder="Select Class" style="min-width: 200px;" @update:modelValue="fetchSubjects">
                     <template #option="{ name, stream }">
                        <strong>{{ name }}</strong> - <small>{{ stream?.name || 'No Stream' }}</small>
                     </template>
                     <template #selected-option="props">
                        {{ props.name }} - {{ props.stream?.name || 'No Stream' }}
                     </template>
                  </v-select>

                  <v-select class="flex-1" v-model="selectedSubject" :options="subjects" label="name"
                    :reduce="option => option.id" placeholder="Select Subject" style="min-width: 200px;"></v-select>

                  <button class="btn btn-primary" @click="loadData" :disabled="!canLoad">Load</button>
                </div>
              </div>
            </div>
          </div>

          <div class="card-body">
            <div v-if="students.length">
               <div class="d-flex justify-content-between align-items-center mb-3">
                  <h6 class="mb-0">Entering marks for: <span class="text-primary">{{ getSubjectName(selectedSubject) }}</span></h6>
                  <button class="btn btn-success" @click="saveMarks" :disabled="saving">
                     <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                     Save Marks
                  </button>
               </div>
              <div class="table-responsive">
                 <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                       <tr>
                          <th style="width: 50px;">#</th>
                          <th style="width: 150px;">Adm No</th>
                          <th>Student Name</th>
                          <th style="width: 150px;">Marks (Max: {{ maxMarks }})</th>
                       </tr>
                    </thead>
                    <tbody>
                       <tr v-for="(student, index) in students" :key="student.id">
                          <td>{{ index + 1 }}</td>
                          <td>{{ student.admission_number }}</td>
                          <td>
                             {{ student.first_name }} {{ student.middle_name }} {{ student.last_name }}
                          </td>
                          <td>
                             <input 
                              type="number" 
                              class="form-control" 
                              v-model="marks[student.id]" 
                              min="0" 
                              :max="maxMarks"
                              @focus="$event.target.select()"
                              placeholder="Enter mark"
                              :class="{'is-invalid': marks[student.id] > maxMarks || marks[student.id] < 0}"
                             >
                          </td>
                       </tr>
                    </tbody>
                 </table>
              </div>
              <div class="text-end mt-3">
                 <button class="btn btn-success" @click="saveMarks" :disabled="saving">
                     <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                     Save Marks
                  </button>
              </div>
            </div>
            <div v-else-if="searched" class="text-muted text-center p-3">
              No students found for this class.
            </div>
            <div v-else class="text-muted text-center p-3">
              Select Exam, Class and Subject to load students.
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
import { ref, onMounted, computed, watch } from "vue";
import axios from "axios";
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const exams = ref([]);
const classes = ref([]);
const students = ref([]);
const subjects = ref([]); 
const selectedExam = ref(null);
const selectedClass = ref(null);
const selectedSubject = ref(null);
const marks = ref({});
const maxMarks = ref(100);
const saving = ref(false);
const searched = ref(false);

const canLoad = computed(() => selectedExam.value && selectedClass.value && selectedSubject.value);

const fetchExams = () => {
  axios.get("/datatable/exams")
    .then(({ data }) => exams.value = data.data)
    .catch(() => toast.error("Error loading exams"));
};

const fetchClasses = () => {
  axios.get("/datatable/ranks")
    .then(({ data }) => classes.value = data.data)
    .catch(() => toast.error("Error loading classes"));
};

const fetchSubjects = () => {
   subjects.value = [];
   selectedSubject.value = null;
   if(!selectedClass.value) return;

   // Fetch subjects assigned to this class (or generic subjects)
   // We use exam-subjects to correctly get subjects linked to the exam if needed, 
   // but primarily we need subjects available for the class.
   // Using /datatable/subjects is safer to get all subjects, 
   // but /datatable/exam-subjects is better if we only want subjects IN the exam.
   // Let's use exam-subjects if exam is selected, otherwise generic.
   // Actually, the teacher should select from subjects configured for the exam.

   if (selectedExam.value) {
      axios.get(`/datatable/exam-subjects`, {
         params: { 
            filter: {
               class_id: selectedClass.value,
               exam_id: selectedExam.value
            }
         }
      })
      .then(({ data }) => {
         // Data structure is Resource collection of ExamSubject
         subjects.value = data.data.map(es => ({
            id: es.subject.id,
            name: es.subject.name,
            max_marks: es.max_marks
         }));
      })
      .catch(() => toast.error("Error loading subjects"));
   }
};

// Update max marks when subject changes
watch(selectedSubject, (newVal) => {
   if (newVal) {
      const subject = subjects.value.find(s => s.id === newVal);
      maxMarks.value = subject ? subject.max_marks : 100;
   }
});

watch(selectedExam, () => {
   if(selectedClass.value) fetchSubjects();
});

const loadData = async () => {
  if (!canLoad.value) return;
  searched.value = true;
  marks.value = {};

  try {
      // 1. Fetch Students
      const studentsRes = await axios.get(`/datatable/students`, {
         params: { 
            filter: { rank_id: selectedClass.value },
            page: 1,
            per_page: 1000 // Get all
         }
      });
      students.value = studentsRes.data.data;

      // 2. Fetch Existing Marks
      const marksRes = await axios.get(`/datatable/exam-marks`, {
         params: {
            filter: {
               exam_id: selectedExam.value,
               class_id: selectedClass.value,
               subject_id: selectedSubject.value
            },
            page: 1,
            per_page: 1000
         }
      });
      
      // Map existing marks to marks object
      marksRes.data.data.forEach(mark => {
         marks.value[mark.student_id] = mark.marks_obtained;
      });

  } catch (error) {
     console.error(error);
     toast.error("Failed to load data");
  }
};

const saveMarks = () => {
   saving.value = true;
   axios.post(route('exams.upload-results.store'), {
      exam_id: selectedExam.value,
      class_id: selectedClass.value,
      subject_id: selectedSubject.value,
      marks: marks.value
   })
   .then(() => {
      toast.success("Marks saved successfully");
   })
   .catch((error) => {
      console.error(error);
      if(error.response?.data?.errors) {
          // If validation errors on specific marks
          toast.error("Some marks are invalid. Please check.");
      } else {
          toast.error(error.response?.data?.message || "Failed to save marks");
      }
   })
   .finally(() => saving.value = false);
};

const getSubjectName = (id) => {
   return subjects.value.find(s => s.id === id)?.name || '';
}

onMounted(() => {
  fetchExams();
  fetchClasses();
});
</script>