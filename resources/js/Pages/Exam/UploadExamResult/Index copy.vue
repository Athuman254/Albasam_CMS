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
              <div class="col-md-6">
                <h5>Select Exam & Class</h5>
                <div class="d-flex gap-3">
                  <v-select class="flex-1" v-model="selectedExam" :options="exams" label="name"
                    :reduce="option => option.id" placeholder="Select Exam"></v-select>

                  <v-select class="flex-1" v-model="selectedClass" :options="classes" label="name"
                    :reduce="option => option.id" placeholder="Select Class"></v-select>

                  <button class="btn btn-primary" @click="loadStudents">Load</button>
                </div>
              </div>
            </div>
          </div>

          <div class="card-body">
            <div v-if="students.length">
              <VueTable :fields="fields" :data="students" ref="marksTable">
                <template v-slot:actions="props">
                  <button class="btn btn-sm btn-outline-primary" @click="openMarksModal(props.rowData)">
                    <i class="bx bx-pencil"></i> Enter Marks
                  </button>
                </template>
              </VueTable>
            </div>
            <div v-else class="text-muted text-center p-3">
              Select an exam and class to load students.
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Marks Modal -->
    <div class="modal fade" id="marks-modal" data-bs-backdrop="static" tabindex="-1"
      aria-labelledby="marks-modal-label" aria-hidden="true" ref="marksModal">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Enter Marks for {{ selectedStudent?.name }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
              @click="clearMarks"></button>
          </div>
          <div class="modal-body">
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Subject</th>
                  <th>Max Marks</th>
                  <th>Marks Obtained</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="subject in subjects" :key="subject.id">
                  <td>{{ subject.name }}</td>
                  <td>{{ subject.max_marks }}</td>
                  <td>
                    <input type="number" class="form-control" v-model="marks[subject.id]"
                      :max="subject.max_marks" min="0">
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button class="btn btn-secondary" data-bs-dismiss="modal" @click="clearMarks">
              Cancel
            </button>
            <button class="btn btn-primary" @click="submitMarks">
              Save Marks
            </button>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { Modal } from "bootstrap";
import { ref, onMounted } from "vue";
import axios from "axios";

const exams = ref([]);
const classes = ref([]);
const students = ref([]);
const subjects = ref([]); 
const selectedExam = ref(null);
const selectedClass = ref(null);
const selectedStudent = ref(null);
const marks = ref({});

const marksModal = ref(null);

const fields = [
  { name: "adm_no", title: "ADM NO" },
  { name: "name", title: "STUDENT NAME" },
  { name: "__slot:actions", title: "ACTIONS", titleClass: "10%", dataClass: "10%" }
];

// Fetch mock data for now
const fetchExams = () => {
  axios.get("/datatable/exams")
    .then(({ data }) => exams.value = data.data)
    .catch(() => console.error("Error loading exams"));
};

const fetchClasses = () => {
  axios.get("/datatable/streams")
    .then(({ data }) => classes.value = data.data)
    .catch(() => console.error("Error loading classes"));
};

const loadStudents = () => {
  if (!selectedExam.value || !selectedClass.value) return;

  students.value = [
    { id: 1, adm_no: "ADM001", name: "John Doe" },
    { id: 2, adm_no: "ADM002", name: "Jane Smith" },
    { id: 3, adm_no: "ADM003", name: "Mike Johnson" }
  ];

  // Fetch subjects for this exam/class
  axios.get(`/datatable/exam-subjects`, {
    params: { class_id: selectedClass.value , exam_id:selectedExam.value}
  })
    .then(({ data }) => subjects.value = data.data)
    .catch(() => console.error("Error loading subjects"));
};

const openMarksModal = (student) => {
  selectedStudent.value = student;
  marks.value = {};
  const modalInstance = Modal.getOrCreateInstance(marksModal.value);
  modalInstance.show();
};

const submitMarks = () => {
  axios.post("/admin/exams/marks", {
    student_id: selectedStudent.value.id,
    exam_id: selectedExam.value,
    class_id: selectedClass.value,
    marks: marks.value
  })
    .then(() => {
      window.$toast.success("Marks saved successfully!");
      clearMarks();
      Modal.getInstance(marksModal.value).hide();
    })
    .catch(() => window.$toast.error("Failed to save marks."));
};

const clearMarks = () => {
  selectedStudent.value = null;
  marks.value = {};
};

onMounted(() => {
  fetchExams();
  fetchClasses();
});
</script>
