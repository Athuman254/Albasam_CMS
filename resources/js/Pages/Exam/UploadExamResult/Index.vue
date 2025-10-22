<template>
  <Head title="Enter Exam Results" />
  <DefaultLayout>
    <div class="row">
      <h3 class="mb-0">Enter Exam Results</h3>
      <nav class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <Link :href="route('admin.dashboard')">Home</Link>
          </li>
          <li class="breadcrumb-item text-primary">
            Enter Exam Results
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
                              <strong>{{ name }}</strong> - <small>{{ stream.name }}</small>
                           </template>


                           <template #selected-option="props">
                              {{ props.name }} - {{ props.stream?.name }}
                           </template>
                  </v-select>

                  <button class="btn btn-primary" @click="loadEnrolledStudents">
                    Load
                  </button>
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
                      <th>Adm No</th>
                      <th>Student Name</th>
                      <th v-for="subject in subjects" :key="subject.id">
                        {{ subject.subject?.name }} <br />
                        <small class="text-muted">/{{ subject.max_marks }}</small>
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="student in students" :key="student.id">
                      <td>{{ student.adm_no }}</td>
                      <td>{{ student.name }}</td>
                      <td v-for="subject in subjects" :key="subject.id">
                        <div>
                          <input
                            type="number"
                            class="form-control"
                            :class="{ 'is-invalid': errors[student.id]?.[subject.id] }"
                            min="0"
                            :max="subject.max_marks"
                            :value="marks[student.id]?.[subject.id] ?? ''"
                            @input="onMarkInput(student.id, subject.id, $event.target.value)"
                          />
                          <div v-if="errors[student.id]?.[subject.id]" class="invalid-feedback">
                            {{ errors[student.id][subject.id] }}
                          </div>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="text-end mt-3">
                <button class="btn btn-success" @click="submitAllMarks">
                  Save All Filled
                </button>
              </div>
            </div>

            <div v-else class="text-muted text-center p-3">
              Select an exam and class to load enrolled students.
            </div>

            <!-- <div class="mt-4 p-3 border rounded text-center text-muted">
              <h6>Bulk Upload (CSV/Excel)</h6>
              <p>This feature is coming soon...</p>
            </div> -->
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import axios from "axios";
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const exams = ref([]);
const classes = ref([]);
const students = ref([]);
const subjects = ref([]);
const marks = ref({});
const errors = ref({});

const selectedExam = ref(null);
const selectedClass = ref(null);

const fetchExams = async () => {
  try {
    const { data } = await axios.get("/datatable/exams");
    exams.value = data.data;
  } catch (e) {
    console.error("Failed to load exams");
  }
};

const fetchClasses = async () => {
  try {
    const { data } = await axios.get("/datatable/ranks");
    classes.value = data.data;
  } catch (e) {
    console.error("Failed to load classes");
  }
};

const loadEnrolledStudents = async () => {
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
    errors.value = {};
    students.value.forEach((student) => {
      marks.value[student.id] = {};
      errors.value[student.id] = {};
      subjects.value.forEach((subject) => {
        marks.value[student.id][subject.id] = "";
      });
    });
    if(students.value.length === 0){
      return
    }
    const marksResp = await axios.get(`/datatable/exam-marks`, {
      params: { filter:{exam_id: selectedExam.value, class_id: selectedClass.value }},
    });
    marksResp.data.data.forEach((row) => {

      marks.value[row.student_id][row.exam_subject_id] = row.marks_obtained;
    });
  } catch (error) {
    toast.error("Error loading enrolled students", error);
  }
};

const onMarkInput = (studentId, subjectId, value) => {
  if (!marks.value[studentId]) marks.value[studentId] = {};
  marks.value[studentId][subjectId] = value;
  if (errors.value[studentId]?.[subjectId]) {
    delete errors.value[studentId][subjectId];
  }
};

const submitAllMarks = async () => {
  try {
    errors.value = {};
    await axios.post("/admin/exams/upload-results", {
      exam_id: selectedExam.value,
      class_id: selectedClass.value,
      marks: marks.value,
    });

    toast.success("Marks saved successfully!");
  } catch (error) {
    if (error.response?.status === 422 && error.response.data?.errors) {
      error.response.data.errors.forEach((err) => {
        if (!errors.value[err.student_id]) errors.value[err.student_id] = {};
        errors.value[err.student_id][err.subject_id] = err.message;
      });
    } else {
      console.error("Error saving marks", error);
      toast.error("Failed to save marks.");
    }
  }
};

onMounted(() => {
  fetchExams();
  fetchClasses();
});
</script>
