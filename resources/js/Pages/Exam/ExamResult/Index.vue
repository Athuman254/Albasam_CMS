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
                  <div class="row row-gap-1 w-100">
                     <div class="col-md-8">
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

                           <button class="btn btn-primary" @click="loadResults" :disabled="loading">
                              {{ loading ? 'Loading...' : 'Load Results' }}
                           </button>
                        </div>
                     </div>
                     <div class="col-md-4">
                        <h5>Generate Reports</h5>
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
                  <div class="row mt-3" v-if="students.length > 0 && reportType">
                     <div class="col-12">
                        <div class="border rounded p-3 bg-white">
                           <h6 class="mb-3">Bulk Report Options</h6>
                           
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
                              <div class="d-flex align-items-center gap-2 mb-2">
                                 <button class="btn btn-sm btn-outline-primary" @click="selectAllStudents" :disabled="!hasMarks">
                                    Select All
                                 </button>
                                 <button class="btn btn-sm btn-outline-secondary" @click="clearSelection">
                                    Clear All
                                 </button>
                                 <span class="text-muted ms-2">
                                    {{ selectedStudents.length }} student(s) selected
                                 </span>
                                 <span v-if="!hasSelectedStudents && reportType === 'student'" class="text-danger ms-2">
                                    * Please select at least one student
                                 </span>
                              </div>
                              <div class="student-checkboxes" style="max-height: 150px; overflow-y: auto;">
                                 <div v-for="student in students" :key="student.id" class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                           :id="`student-${student.id}`" 
                                           :value="student.id" 
                                           v-model="selectedStudents"
                                           :disabled="!hasStudentMarks(student.id)">
                                    <label class="form-check-label" :for="`student-${student.id}`" 
                                           :class="{ 'text-muted': !hasStudentMarks(student.id) }">
                                       {{ student.adm_no }} - {{ student.name }}
                                       <span v-if="!hasStudentMarks(student.id)" class="badge bg-secondary ms-1">No Marks</span>
                                       <span v-else class="badge bg-success ms-1">{{ getTotal(student.id) }} marks</span>
                                    </label>
                                 </div>
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

               <div class="card-body" id="results-section">
                  <!-- Statistics Cards -->
                  <div v-if="students.length" class="row mb-4">
                     <div class="col-md-3">
                        <div class="card bg-white border">
                           <div class="card-body text-center p-3">
                              <h6 class="card-title mb-1 text-muted">Total Students</h6>
                              <h4 class="mb-0 text-dark">{{ students.length }}</h4>
                           </div>
                        </div>
                     </div>
                     <div class="col-md-3">
                        <div class="card bg-white border">
                           <div class="card-body text-center p-3">
                              <h6 class="card-title mb-1 text-muted">With Marks</h6>
                              <h4 class="mb-0 text-dark">{{ statistics.studentsWithMarks }}</h4>
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
                        <table class="table table-bordered align-middle">
                           <thead>
                              <tr>
                                 <th>Adm No</th>
                                 <th>Student Name</th>
                                 <th v-for="subject in subjects" :key="subject.id">
                                    {{ subject.subject?.name || subject.name }} <br />
                                    <small class="text-muted">/{{ subject.max_marks || 100 }}</small>
                                 </th>
                                 <th>Status</th>
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
   </DefaultLayout>
</template>

<script setup>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import { ref, onMounted, computed, watch } from "vue";
import axios from "axios";

const exams = ref([]);
const classes = ref([]);
const students = ref([]);
const subjects = ref([]);
const marks = ref({});
const studentStatuses = ref({});
const selectedStudents = ref([]);
const loading = ref(false);

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
   fetchExams();
   fetchClasses();
});
</script>

<style scoped>
.student-checkboxes {
   border: 1px solid #dee2e6;
   border-radius: 0.375rem;
   padding: 10px;
   background-color: white;
}

.table-responsive {
   max-height: 70vh;
   overflow-y: auto;
}

.badge {
   font-size: 0.75em;
}

.card {
   box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
   background-color: white;
}

.card-title {
   font-size: 0.875rem;
}

.btn:disabled {
   cursor: not-allowed;
   opacity: 0.6;
}

.form-text {
   font-size: 0.8rem;
}

.table td, .table th {
   vertical-align: middle;
}

.bg-white {
   background-color: white !important;
}

/* Remove colored backgrounds and use white with borders */
.table-success, .table-warning {
   background-color: white !important;
}
</style>