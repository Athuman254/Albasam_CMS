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
            <div class="card shadow-sm">
               <div class="card-header bg-light border-bottom">
                  <div class="row row-gap-3 w-100">
                     <div class="col-md-8">
                        <h5 class="mb-3 fw-semibold">Select Exam & Class</h5>
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

                           <button class="btn btn-primary" @click="loadResults" :disabled="loading">
                              {{ loading ? 'Loading...' : 'Load Results' }}
                           </button>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <h5 class="mb-3 fw-semibold">Generate Reports</h5>
                        <div class="d-flex gap-2">
                           <v-select v-model="reportType" :options="reportTypes" label="label"
                              :reduce="option => option.value" placeholder="Report Type" 
                              style="min-width: 150px;"></v-select>
                           <button class="btn btn-success" @click="generateBulkReport" 
                                   :disabled="!selectedExam || !selectedClass || !reportType || loading || !hasMarks || (!hasSelectedStudents && reportType === 'student')">
                              <i class="bi bi-file-earmark-pdf me-1"></i> 
                              {{ loading ? 'Generating...' : 'Generate PDF' }}
                           </button>
                        </div>
                     </div>
                  </div>

                  <!-- Bulk Report Options -->
                  <div class="row mt-4" v-if="students.length > 0 && reportType">
                     <div class="col-12">
                        <div class="border rounded p-4 bg-light">
                           <h6 class="mb-4 fw-semibold">Bulk Report Options</h6>
                           
                           <!-- Report Dates Section -->
                           <div class="row mb-3">
                              <div class="col-md-6">
                                 <label class="form-label fw-semibold">Closing Date</label>
                                 <input type="date" class="form-control" v-model="closingDate">
                                 <div class="form-text">Term closing date for the report</div>
                              </div>
                              <div class="col-md-6">
                                 <label class="form-label fw-semibold">Opening Date</label>
                                 <input type="date" class="form-control" v-model="openingDate">
                                 <div class="form-text">Next term opening date for the report</div>
                              </div>
                           </div>
                           
                           <!-- Student Selection for Individual Reports -->
                           <div v-if="reportType === 'student'" class="mb-3">
                              <label class="form-label fw-semibold">Select Students:</label>
                              
                              <div class="d-flex align-items-center gap-2">
                                 <button class="btn btn-outline-primary" @click="showStudentModal = true" type="button">
                                    <i class="bi bi-person-check me-2"></i>
                                    Select Students
                                 </button>
                                 <span class="text-muted">
                                    {{ selectedStudents.length }} student(s) selected
                                 </span>
                                 <span v-if="!hasSelectedStudents && reportType === 'student'" class="text-danger">
                                    * Please select at least one student
                                 </span>
                              </div>
                           </div>

                           <!-- Report Options for all report types -->
                           <div class="row">
                              <div class="col-md-6">
                                 <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" v-model="includeAnalysis" id="includeAnalysis">
                                    <label class="form-check-label" for="includeAnalysis">
                                       Include Academic Analysis
                                    </label>
                                 </div>
                                 <div class="form-check">
                                    <input class="form-check-input" type="checkbox" v-model="includeRankings" id="includeRankings">
                                    <label class="form-check-label" for="includeRankings">
                                       Include Subject Rankings
                                    </label>
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="form-check">
                                    <input class="form-check-input" type="checkbox" v-model="onlyPublished" id="onlyPublished">
                                    <label class="form-check-label" for="onlyPublished">
                                       Only Published Results
                                    </label>
                                    <div class="form-text text-muted">
                                       Uncheck to include approved marks
                                    </div>
                                 </div>
                                 <!-- Show info for class/stream reports -->
                                 <div v-if="reportType !== 'student'" class="mt-2">
                                    <span class="badge bg-light text-dark border">
                                       {{ reportType === 'class' ? 'Class Report' : 'Stream Report' }}: All students with marks will be included
                                    </span>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               <div class="card-body p-4" id="results-section">
                  <!-- Statistics Cards -->
                  <div v-if="students.length" class="row mb-4 g-3">
                     <div class="col-md-3">
                        <div class="card bg-white border shadow-sm h-100">
                           <div class="card-body text-center p-4">
                              <h6 class="card-title mb-3 text-muted text-uppercase small">Total Students</h6>
                              <h2 class="mb-0 text-dark fw-bold">{{ students.length }}</h2>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="card bg-white border shadow-sm h-100">
                           <div class="card-body text-center p-4">
                              <h6 class="card-title mb-3 text-muted text-uppercase small">With Marks</h6>
                              <h2 class="mb-0 text-dark fw-bold">{{ statistics.studentsWithMarks }}</h2>
                           </div>
                        </div>
                     </div>
                  </div>

                  <div v-if="students.length">
                     <div class="alert alert-info" v-if="!hasMarks">
                        <i class="bi bi-info-circle me-2"></i>
                        No marks found for the selected criteria. 
                        <span v-if="onlyPublished">Try unchecking "Only Published Results" to include approved marks.</span>
                        <span v-else>Please ensure marks have been entered and approved for this exam.</span>
                     </div>
                     
                     <div class="table-responsive" v-else>
                        <table class="table table-bordered table-hover align-middle mb-0">
                           <thead class="table-light">
                              <tr>
                                 <th class="fw-semibold">Adm No</th>
                                 <th class="fw-semibold">Student Name</th>
                                 <th v-for="subject in subjects" :key="subject.id" class="fw-semibold">
                                    {{ subject.subject?.name || subject.name }} <br />
                                    <small class="text-muted fw-normal">/{{ subject.max_marks || 100 }}</small>
                                 </th>
                                 <th class="fw-semibold">Status</th>
                              </tr>
                           </thead>
                           <tbody>
                              <tr v-for="student in sortedStudents" :key="student.id">
                                 <td>{{ student.adm_no }}</td>
                                 <td>{{ student.name }}</td>
                                 <td v-for="subject in subjects" :key="subject.id">
                                    <span :class="getMarkClass(student.id, subject.id)">
                                       {{ getStudentMark(student.id, subject.id) }}
                                    </span>
                                 </td>
                                 <td>
                                    <span class="badge" :class="getStatusBadgeClass(student.id)">
                                       {{ getStatus(student.id) }}
                                    </span>
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

      <!-- Student Selection Modal -->
      <div v-if="showStudentModal" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
         <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title">
                     <i class="bi bi-person-check me-2"></i>
                     Select Students for Report
                  </h5>
                  <button type="button" class="btn-close" @click="showStudentModal = false"></button>
               </div>
               <div class="modal-body">
                  <!-- Search Input -->
                  <div class="mb-3">
                     <input 
                        type="text" 
                        class="form-control" 
                        v-model="studentSearchQuery"
                        placeholder="🔍 Search by name or admission number..."
                     >
                     <small class="text-muted">
                        Showing {{ filteredStudents.length }} of {{ students.length }} students
                     </small>
                  </div>

                  <!-- Action Buttons -->
                  <div class="d-flex align-items-center gap-2 mb-3">
                     <button class="btn btn-sm btn-outline-primary" @click="selectAllStudents" :disabled="!hasMarks">
                        <i class="bi bi-check-all me-1"></i>
                        Select All
                     </button>
                     <button class="btn btn-sm btn-outline-secondary" @click="clearSelection">
                        <i class="bi bi-x-circle me-1"></i>
                        Clear All
                     </button>
                     <span class="badge bg-primary ms-auto">
                        {{ selectedStudents.length }} selected
                     </span>
                  </div>

                  <!-- Student List -->
                  <div class="student-list">
                     <div v-if="filteredStudents.length === 0" class="text-muted text-center p-4">
                        <i class="bi bi-search fs-2 d-block mb-2"></i>
                        <p>No students found matching "{{ studentSearchQuery }}"</p>
                     </div>
                     <div v-for="student in filteredStudents" :key="student.id" class="form-check student-item">
                        <input 
                           class="form-check-input" 
                           type="checkbox" 
                           :id="`modal-student-${student.id}`" 
                           :value="student.id" 
                           v-model="selectedStudents"
                           :disabled="!hasStudentMarks(student.id)"
                        >
                        <label 
                           class="form-check-label w-100" 
                           :for="`modal-student-${student.id}`" 
                           :class="{ 'text-muted': !hasStudentMarks(student.id) }"
                        >
                           <div class="d-flex align-items-center justify-content-between">
                              <div>
                                 <strong>{{ student.adm_no }}</strong> - {{ student.name }}
                              </div>
                              <div>
                                 <span v-if="!hasStudentMarks(student.id)" class="badge bg-secondary">No Marks</span>
                                 <span v-else class="badge bg-success">{{ getTotal(student.id) }} marks</span>
                              </div>
                           </div>
                        </label>
                     </div>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" @click="showStudentModal = false">
                     Close
                  </button>
                  <button type="button" class="btn btn-primary" @click="showStudentModal = false" :disabled="selectedStudents.length === 0">
                     <i class="bi bi-check-lg me-1"></i>
                     Confirm Selection ({{ selectedStudents.length }})
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
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import { ref, onMounted, computed, watch } from "vue";
import axios from "axios";

// Define props to receive initial data from controller
const props = defineProps({
   initialExams: {
      type: Array,
      default: () => []
   },
   initialClasses: {
      type: Array,
      default: () => []
   },
   initialAcademicYears: {
      type: Array,
      default: () => []
   }
});

// Initialize with data from props
const exams = ref(props.initialExams || []);
const classes = ref(props.initialClasses || []);
const students = ref([]);
const subjects = ref([]);
const marks = ref({});
const studentStatuses = ref({});
const selectedStudents = ref([]);
const loading = ref(false);
const studentSearchQuery = ref('');
const showStudentModal = ref(false);

const selectedExam = ref(null);
const selectedClass = ref(null);
const reportType = ref('');
const includeAnalysis = ref(true);
const includeRankings = ref(true);
const onlyPublished = ref(false);
const closingDate = ref('');
const openingDate = ref('');

const reportTypes = ref([
   { label: 'Individual Students', value: 'student' },
   { label: 'Class Report', value: 'class' },
   { label: 'Stream Report', value: 'stream' }
]);

// Computed properties
const showBulkOptions = computed(() => {
   return students.value.length > 0 && reportType.value;
});

const hasSelectedStudents = computed(() => {
   return selectedStudents.value.length > 0;
});

const sortedStudents = computed(() => {
   return [...students.value].sort((a, b) => {
      return getTotal(b.id) - getTotal(a.id);
   });
});

const statistics = computed(() => {
   const studentsWithMarks = students.value.filter(student => hasStudentMarks(student.id)).length;

   const totals = students.value.map(student => getTotal(student.id)).filter(total => total > 0);
   const averageScore = totals.length > 0 ? Math.round(totals.reduce((a, b) => a + b, 0) / totals.length) : 0;
   const topScore = totals.length > 0 ? Math.max(...totals) : 0;

   return {
      studentsWithMarks,
      averageScore,
      topScore
   };
});

// Filter students based on search query
const filteredStudents = computed(() => {
   if (!studentSearchQuery.value || studentSearchQuery.value.trim() === '') {
      return students.value;
   }
   
   const query = studentSearchQuery.value.toLowerCase().trim();
   return students.value.filter(student => {
      const name = (student.name || '').toLowerCase();
      const admNo = (student.adm_no || student.admission_number || '').toLowerCase();
      return name.includes(query) || admNo.includes(query);
   });
});


const hasAnalysis = computed(() => {
   return students.value.length > 0 && subjects.value.length > 0;
});

const hasMarks = computed(() => {
   return students.value.some(student => hasStudentMarks(student.id));
});

// Helper function to get CSRF token
const getCsrfToken = () => {
   return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
};

const fetchExams = async () => {
   try {
      const { data } = await axios.get("/datatable/exams");
      exams.value = data.data || data;
   } catch (e) {
      toast.error("Failed to load exams")
      console.error("Failed to load exams", e);
   }
};

const fetchClasses = async () => {
   try {
      const { data } = await axios.get("/datatable/ranks");
      classes.value = data.data || data;
   } catch (e) {
      toast.error("Failed to load classes")
      console.error("Failed to load classes", e);
   }
};

const loadResults = async () => {
   if (!selectedExam.value || !selectedClass.value) {
      toast.error("Please select both exam and class");
      return;
   }

   try {
      loading.value = true;
      
      // Fetch enrolled students
      const { data } = await axios.get("/datatable/enrolled-students", {
         params: { exam_id: selectedExam.value, class_id: selectedClass.value },
      });
      students.value = data.enrolled_students || data.students || data.data || [];

      // Fetch subjects for the exam and class
      const subResp = await axios.get(`/datatable/exam-subjects`, {
         params: { 
            filter: { 
               class_id: selectedClass.value, 
               exam_id: selectedExam.value 
            } 
         },
      });
      subjects.value = subResp.data.data || subResp.data.subjects || [];

      // Initialize marks and statuses
      marks.value = {};
      studentStatuses.value = {};
      students.value.forEach((student) => {
         marks.value[student.id] = {};
         studentStatuses.value[student.id] = 'no_marks';
      });

      // Fetch all marks without only_published filter
      const marksResp = await axios.get(`/datatable/exam-marks`, {
         params: { 
            filter: {
               exam_id: selectedExam.value, 
               class_id: selectedClass.value
            }
         },
      });

      let marksData = marksResp.data.data || marksResp.data.marks || [];
      
      // Manual filtering based on onlyPublished
      if (onlyPublished.value) {
         marksData = marksData.filter(mark => mark.status === 'published');
      } else {
         // Include both approved and published marks
         marksData = marksData.filter(mark => ['approved', 'published'].includes(mark.status));
      }
      
      marksData.forEach((row) => {
         if (!marks.value[row.student_id]) marks.value[row.student_id] = {};
         marks.value[row.student_id][row.exam_subject_id] = row.marks_obtained;
         studentStatuses.value[row.student_id] = row.status || 'draft';
      });

      // Reset selection when results are loaded
      selectedStudents.value = [];
      
      if (students.value.length > 0) {
         toast.success(`Loaded ${students.value.length} students with ${subjects.value.length} subjects`);
      } else {
         toast.warning("No students found for the selected criteria");
      }
      
   } catch (error) {
      toast.error("Failed to load results")
      console.error("Error loading results", error);
   } finally {
      loading.value = false;
   }
};

const getStudentMark = (studentId, subjectId) => {
   return marks.value[studentId]?.[subjectId] ?? '-';
};

const hasStudentMarks = (studentId) => {
   if (!marks.value[studentId]) return false;
   return Object.values(marks.value[studentId]).some(mark => mark && mark !== '-');
};

const getTotal = (studentId) => {
   if (!marks.value[studentId]) return 0;
   const studentMarks = Object.values(marks.value[studentId])
      .filter(mark => mark && mark !== '-')
      .map(mark => parseFloat(mark) || 0);
   return studentMarks.reduce((sum, mark) => sum + mark, 0);
};

const getAverage = (studentId) => {
   const total = getTotal(studentId);
   const subjectCount = subjects.value.length;
   return subjectCount > 0 ? Math.round((total / (subjectCount * 100)) * 100) : 0;
};

const getGrade = (studentId) => {
   const average = getAverage(studentId);
   if (average >= 80) return 'A';
   if (average >= 70) return 'B';
   if (average >= 60) return 'C';
   if (average >= 50) return 'D';
   return 'E';
};

const getStatus = (studentId) => {
   const status = studentStatuses.value[studentId];
   const statusMap = {
      'draft': 'Draft',
      'submitted': 'Submitted',
      'approved': 'Approved',
      'published': 'Published',
      'no_marks': 'No Marks'
   };
   return statusMap[status] || 'Unknown';
};

// Get student report URL
const getStudentReportUrl = (student) => {
   return `/admin/exams/reports/student/${student.id}?exam_id=${selectedExam.value}&class_id=${selectedClass.value}&include_analysis=${includeAnalysis.value}&only_published=${onlyPublished.value}`;
};

// Styling functions
const getMarkClass = (studentId, subjectId) => {
   const mark = marks.value[studentId]?.[subjectId];
   if (!mark || mark === '-') return 'text-muted';
   const subject = subjects.value.find(s => s.id === subjectId);
   const maxMarks = subject?.max_marks || 100;
   const percentage = maxMarks > 0 ? (parseFloat(mark) / maxMarks) * 100 : 0;
   if (percentage >= 80) return 'text-success fw-bold';
   if (percentage >= 50) return 'text-primary';
   return 'text-danger';
};

const getStatusBadgeClass = (studentId) => {
   const status = studentStatuses.value[studentId];
   switch (status) {
      case 'published': return 'bg-success text-white';
      case 'approved': return 'bg-primary text-white';
      case 'submitted': return 'bg-warning text-dark';
      case 'draft': return 'bg-secondary text-white';
      default: return 'bg-light text-dark border';
   }
};

// Bulk reporting functions
const generateBulkReport = async () => {
   if (!selectedExam.value || !selectedClass.value || !reportType.value) {
      toast.error("Please select exam, class and report type");
      return;
   }

   if (!hasMarks.value) {
      toast.error("No marks available to generate reports");
      return;
   }

   // For student reports, ensure at least one student is selected
   if (reportType.value === 'student' && !hasSelectedStudents.value) {
      toast.error("Please select at least one student");
      return;
   }

   // Validate dates if provided
   if (closingDate.value && openingDate.value) {
      const closing = new Date(closingDate.value);
      const opening = new Date(openingDate.value);
      if (opening <= closing) {
         toast.error("Opening date must be after closing date");
         return;
      }
   }

   try {
      loading.value = true;

      const params = {
         exam_id: selectedExam.value,
         class_id: selectedClass.value,
         report_type: reportType.value,
         include_analysis: includeAnalysis.value,
         include_rankings: includeRankings.value,
         only_published: onlyPublished.value,
         closing_date: closingDate.value,
         opening_date: openingDate.value
      };

      if (reportType.value === 'student' && hasSelectedStudents.value) {
         params.student_ids = selectedStudents.value;
      }

      console.log('Generating bulk report with params:', params);

      const response = await axios.post('/admin/exams/generate-bulk-report', params, {
         responseType: 'blob',
         headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
         }
      });

      // Download PDF
      const blob = new Blob([response.data], { type: 'application/pdf' });
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      
      // Get filename from response headers or use default
      const contentDisposition = response.headers['content-disposition'];
      let filename = `exam-report-${selectedExam.value}-${selectedClass.value}-${reportType.value}.pdf`;
      
      if (contentDisposition) {
         const filenameMatch = contentDisposition.match(/filename="(.+)"/);
         if (filenameMatch) filename = filenameMatch[1];
      }
      
      link.setAttribute('download', filename);
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);

      toast.success("Report generated successfully!");

   } catch (error) {
      console.error("Full error details:", error);
      
      if (error.response?.status === 404) {
         toast.error("Report generation service not found");
      } else if (error.response?.status === 422) {
         toast.error("Validation error. Please check your selections.");
      } else if (error.response?.data?.error) {
         toast.error(`Error: ${error.response.data.error}`);
      } else if (error.code === 'ERR_NETWORK') {
         toast.error("Network error. Please check your connection.");
      } else if (error.response?.status === 500) {
         toast.error("Server error. Please try again later.");
      } else {
         toast.error("Failed to generate report. Please try again.");
      }
   } finally {
      loading.value = false;
   }
};

// Individual student report download
const downloadStudentReport = async (student) => {
   if (!hasStudentMarks(student.id)) {
      toast.error("No marks available for this student");
      return;
   }

   try {
      loading.value = true;

      const response = await axios.get(`/admin/exams/reports/student/${student.id}`, {
         params: {
            exam_id: selectedExam.value,
            class_id: selectedClass.value,
            include_analysis: includeAnalysis.value,
            only_published: onlyPublished.value,
            closing_date: closingDate.value,
            opening_date: openingDate.value
         },
         responseType: 'blob',
         headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest'
         }
      });

      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `exam-report-${student.adm_no}.pdf`);
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);

      toast.success("Student report downloaded successfully!");

   } catch (error) {
      console.error("Error generating student report:", error);
      
      if (error.response?.status === 404) {
         toast.error("Student report not found");
      } else if (error.response?.data?.error) {
         toast.error(`Error: ${error.response.data.error}`);
      } else {
         toast.error("Failed to generate student report");
      }
   } finally {
      loading.value = false;
   }
};

const selectAllStudents = () => {
   selectedStudents.value = students.value
      .filter(student => hasStudentMarks(student.id))
      .map(student => student.id);
};

const clearSelection = () => {
   selectedStudents.value = [];
};

// Watchers
watch([selectedExam, selectedClass], () => {
   students.value = [];
   subjects.value = [];
   marks.value = {};
   selectedStudents.value = [];
   reportType.value = '';
   closingDate.value = '';
   openingDate.value = '';
});

// Watch for onlyPublished changes and reload results
watch(onlyPublished, () => {
   if (selectedExam.value && selectedClass.value) {
      loadResults();
   }
});

// Watch for report type changes and clear student selection
watch(reportType, (newType) => {
   if (newType !== 'student') {
      selectedStudents.value = [];
   }
});

onMounted(() => {

   if (exams.value.length > 0) {
      console.log(`Loaded ${exams.value.length} exams from controller`);
   }
   if (classes.value.length > 0) {
      console.log(`Loaded ${classes.value.length} classes from controller`);
   }
});
</script>

<style scoped>
/* Layout and Spacing Improvements */

/* Page Header */
h3 {
   font-size: 1.75rem;
   line-height: 1.2;
}

/* Card Improvements */
.card {
   border-radius: 0.5rem;
   margin-bottom: 1.5rem;
}

.card-header {
   padding: 1.5rem;
}

.card-header h5 {
   font-size: 1.125rem;
   line-height: 1.5;
   margin-bottom: 0;
}

.card-body {
   padding: 1.5rem;
}

/* Statistics Cards */
.card.bg-white.border {
   border-radius: 0.5rem;
}

.card.bg-white.border .card-body {
   padding: 1.5rem;
}

.card.bg-white.border .card-title {
   font-size: 0.75rem;
   letter-spacing: 0.05em;
   margin-bottom: 0.75rem;
}

.card.bg-white.border h2 {
   font-size: 2.5rem;
   line-height: 1;
}

/* Form Controls */
.form-control {
   padding: 0.625rem 0.875rem;
   font-size: 0.9375rem;
   line-height: 1.5;
}

.form-label {
   margin-bottom: 0.5rem;
   font-size: 0.9375rem;
}

.form-text {
   font-size: 0.8125rem;
   margin-top: 0.375rem;
   line-height: 1.4;
}

/* Buttons */
.btn {
   padding: 0.625rem 1.25rem;
   font-size: 0.9375rem;
   font-weight: 500;
   line-height: 1.5;
}

.btn-sm {
   padding: 0.375rem 0.875rem;
   font-size: 0.875rem;
}

.btn:disabled {
   cursor: not-allowed;
   opacity: 0.6;
}

/* Checkboxes */
.form-check {
   padding-left: 1.75rem;
   margin-bottom: 0.625rem;
}

.form-check-input {
   margin-top: 0.25rem;
}

.form-check-label {
   font-size: 0.9375rem;
   line-height: 1.5;
}

.student-checkboxes {
   border: 1px solid #dee2e6;
   border-radius: 0.5rem;
   padding: 1rem;
   background-color: #fff;
}

.student-checkboxes .form-check {
   padding: 0.5rem;
   margin-bottom: 0.5rem;
}

.student-checkboxes .form-check:hover {
   background-color: #f8f9fa;
   border-radius: 0.25rem;
}

/* Table Styling */
.table-responsive {
   max-height: 70vh;
   overflow-y: auto;
   border-radius: 0.5rem;
}

.table {
   font-size: 0.9375rem;
}

.table thead {
   position: sticky;
   top: 0;
   z-index: 10;
}

.table thead th {
   padding: 1rem 0.875rem;
   font-size: 0.875rem;
   line-height: 1.4;
   white-space: nowrap;
   vertical-align: middle;
}

.table tbody td {
   padding: 0.875rem;
   line-height: 1.5;
   vertical-align: middle;
}

.table tbody tr {
   transition: background-color 0.15s ease-in-out;
}

.table-hover tbody tr:hover {
   background-color: #f8f9fa;
}

/* Badges */
.badge {
   font-size: 0.8125rem;
   padding: 0.375rem 0.625rem;
   font-weight: 500;
   line-height: 1;
}

/* Alerts */
.alert {
   padding: 1rem 1.25rem;
   font-size: 0.9375rem;
   line-height: 1.5;
}

/* Spacing Utilities */
.gap-2 {
   gap: 0.5rem;
}

.gap-3 {
   gap: 1rem;
}

.row-gap-3 {
   row-gap: 1rem;
}

.g-3 {
   gap: 1rem;
}

/* Flex Utilities */
.flex-1 {
   flex: 1;
}

/* Background Utilities */
.bg-white {
   background-color: white !important;
}

.bg-light {
   background-color: #f8f9fa !important;
}

/* Border Utilities */
.border-bottom {
   border-bottom: 1px solid #dee2e6 !important;
}

/* Shadow Utilities */
.shadow-sm {
   box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
}

/* Height Utilities */
.h-100 {
   height: 100% !important;
}

/* Text Utilities */
.text-uppercase {
   text-transform: uppercase !important;
}

.small {
   font-size: 0.875rem !important;
}

.fw-bold {
   font-weight: 700 !important;
}

.fw-semibold {
   font-weight: 600 !important;
}

.fw-normal {
   font-weight: 400 !important;
}

/* Remove colored backgrounds from table */
.table-success, .table-warning {
   background-color: white !important;
}

/* Scrollbar Styling */
.table-responsive::-webkit-scrollbar {
   width: 8px;
   height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
   background: #f1f1f1;
   border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
   background: #888;
   border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
   background: #555;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
   h3 {
      font-size: 1.5rem;
   }
   
   .card-header {
      padding: 1rem;
   }
   
   .card-body {
      padding: 1rem;
   }
   
   .table thead th,
   .table tbody td {
      padding: 0.625rem 0.5rem;
      font-size: 0.875rem;
   }
}

/* Student Selection Modal Styling */
.modal.show {
   display: block;
}

.modal-dialog-scrollable {
   max-height: calc(100vh - 3.5rem);
}

.modal-dialog-scrollable .modal-body {
   overflow-y: auto;
   max-height: calc(100vh - 250px);
}

.modal-header {
   background-color: #f8f9fa;
   border-bottom: 2px solid #dee2e6;
   padding: 1.25rem 1.5rem;
}

.modal-title {
   font-weight: 600;
   font-size: 1.25rem;
}

.modal-body {
   padding: 1.5rem;
}

.modal-footer {
   background-color: #f8f9fa;
   border-top: 2px solid #dee2e6;
   padding: 1rem 1.5rem;
}

/* Student List Styling */
.student-list {
   max-height: 500px;
   overflow-y: auto;
   border: 1px solid #dee2e6;
   border-radius: 0.5rem;
   background-color: #fff;
   padding: 0.5rem;
}

.student-item {
   padding: 1rem 1.25rem;
   border-bottom: 1px solid #f1f1f1;
   transition: all 0.2s ease;
   cursor: pointer;
   margin-bottom: 0;
   display: flex;
   align-items: center;
   gap: 1rem;
}

.student-item:last-child {
   border-bottom: none;
}

.student-item:hover {
   background-color: #f8f9fa;
}

.student-item input[type="checkbox"] {
   width: 1.25rem;
   height: 1.25rem;
   cursor: pointer;
   flex-shrink: 0;
   margin-top: 0;
}

.student-item input[type="checkbox"]:checked ~ label {
   background-color: #e7f3ff;
}

.student-item label {
   cursor: pointer;
   margin-bottom: 0;
   padding: 0.5rem;
   border-radius: 0.25rem;
   transition: all 0.2s ease;
}

.student-item label:hover {
   background-color: #f8f9fa;
}

.student-item strong {
   color: #495057;
   font-weight: 600;
}

/* Custom Scrollbar for Student List */
.student-list::-webkit-scrollbar {
   width: 10px;
}

.student-list::-webkit-scrollbar-track {
   background: #f1f1f1;
   border-radius: 0 0.5rem 0.5rem 0;
}

.student-list::-webkit-scrollbar-thumb {
   background: #888;
   border-radius: 5px;
}

.student-list::-webkit-scrollbar-thumb:hover {
   background: #555;
}

/* Badge in Modal */
.badge.bg-primary {
   font-size: 0.875rem;
   padding: 0.5rem 0.875rem;
}

/* Empty State */
.student-list .text-center {
   color: #6c757d;
}

.student-list .bi-search {
   color: #adb5bd;
}

/* Modal Backdrop */
.modal {
   backdrop-filter: blur(2px);
}

/* Responsive Modal */
@media (max-width: 768px) {
   .modal-dialog {
      margin: 0.5rem;
   }
   
   .modal-body {
      padding: 1rem;
   }
   
   .student-item {
      padding: 0.75rem 1rem;
   }
   
   .student-list {
      max-height: 400px;
   }
}
</style>