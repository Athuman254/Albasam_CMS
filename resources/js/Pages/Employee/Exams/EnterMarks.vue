<template>
  <Head title="Enter Exam Marks" />
  <DefaultLayout>
    <div class="row">
      <h3 class="mb-0">Enter Exam Marks</h3>
      <nav class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <Link :href="route('employee.dashboard')">Home</Link>
          </li>
          <li class="breadcrumb-item">Enter Exam Marks</li>
        </ol>
      </nav>

      <div class="col-lg-12">
        <div class="card border">
          <div class="card-header bg-white border-bottom">
            <h5 class="mb-3">Select Exam & Class</h5>
            
            <div class="d-flex gap-3 align-items-start flex-wrap">
              <!-- Exam Selection -->
              <div class="flex-1 min-w-200">
                <label class="form-label small mb-1">Select Exam</label>
                <v-select 
                  class="flex-1" 
                  v-model="selectedExam" 
                  :options="exams" 
                  label="name"
                  :reduce="option => option?.id" 
                  placeholder="Choose exam..."
                  :disabled="loading.exams"
                  :get-option-key="(option) => option?.id || ''"
                >
                  <template #no-options>
                    <div class="p-2">
                      {{ loading.exams ? 'Loading exams...' : 'No exams available' }}
                    </div>
                  </template>
                </v-select>
              </div>
              
              <!-- Class Selection -->
              <div class="flex-1 min-w-200">
                <label class="form-label small mb-1">Select Class</label>
                <v-select 
                  class="flex-1" 
                  v-model="selectedClass" 
                  :options="employeeClasses" 
                  label="full_name"
                  :reduce="option => option?.id" 
                  placeholder="Choose class..."
                  :disabled="loading.classes"
                  :get-option-key="(option) => option?.id || ''"
                >
                  <template #option="{ name, stream, full_name, subjects }">
                    <div>
                      <strong>{{ name }}</strong> 
                      <span v-if="stream"> - {{ stream }}</span>
                      <div v-if="subjects && subjects.length > 0" class="small">
                        Subjects: {{ subjects.map(s => s.name).join(', ') }}
                      </div>
                    </div>
                  </template>
                  <template #no-options>
                    <div class="p-2">
                      {{ loading.classes ? 'Loading classes...' : 'No classes assigned' }}
                    </div>
                  </template>
                </v-select>
              </div>

              <!-- Subject Selection -->
              <div class="flex-1 min-w-250" v-if="selectedClass">
                <label class="form-label small mb-1">Select Subject</label>
                <v-select 
                  class="flex-1" 
                  v-model="selectedSubject" 
                  :options="availableSubjects" 
                  label="name"
                  :reduce="option => option?.id" 
                  placeholder="Choose subject..."
                  :disabled="loading.subjects || !selectedClass"
                  :get-option-key="(option) => option?.id || ''"
                >
                  <template #option="{ name, code, max_marks }">
                    <div>
                      <strong>{{ name }}</strong>
                      <span v-if="code"> ({{ code }})</span>
                      <span v-if="max_marks" class="float-end">Max: {{ max_marks }}</span>
                    </div>
                  </template>
                  <template #no-options>
                    <div class="p-2">
                      {{ loading.subjects ? 'Loading subjects...' : 'No subjects available for this class' }}
                    </div>
                  </template>
                </v-select>
              </div>
              
              <div class="flex-shrink-0 pt-4">
                <button 
                  class="btn btn-primary" 
                  @click="loadStudents"
                  :disabled="!selectedExam || !selectedClass || !selectedSubject || loading.students"
                >
                  <span v-if="loading.students" class="spinner-border spinner-border-sm me-2"></span>
                  <i v-else class="bi bi-arrow-clockwise me-1"></i>
                  {{ loading.students ? 'Loading...' : 'Load Students' }}
                </button>
              </div>
            </div>

            <!-- Selection Summary -->
            <div v-if="selectedExam && selectedClass" class="mt-3 p-3 bg-light rounded border">
              <div class="row">
                <div class="col-md-4">
                  <strong>Selected Exam:</strong> 
                  {{ exams.find(e => e.id === selectedExam)?.name || 'Unknown' }}
                </div>
                <div class="col-md-4">
                  <strong>Selected Class:</strong> 
                  {{ employeeClasses.find(c => c.id === selectedClass)?.full_name || 'Unknown' }}
                </div>
                <div class="col-md-4" v-if="selectedSubject">
                  <strong>Selected Subject:</strong> 
                  {{ availableSubjects.find(s => s.id === selectedSubject)?.name || 'Unknown' }}
                </div>
              </div>
              <div v-if="availableSubjects.length > 1" class="mt-2">
                <small>
                  <i class="bi bi-info-circle me-1"></i>
                  You teach {{ availableSubjects.length }} subjects in this class. Select a subject to enter marks.
                </small>
              </div>
            </div>
          </div>

          <div class="card-body">
            <!-- Students Table with Skill Breakdown -->
            <div v-if="students.length && selectedSubject && subjectSkills.length">
              <div class="alert alert-success border">
                <i class="bi bi-check-circle me-2"></i>
                Successfully loaded {{ students.length }} students for {{ availableSubjects.find(s => s.id === selectedSubject)?.name }}
                <span class="float-end">
                  <button class="btn btn-sm btn-outline-primary" @click="toggleSkillMode">
                    <i class="bi" :class="skillMode ? 'bi-list-check' : 'bi-input-cursor-text'"></i>
                    {{ skillMode ? 'Skill Breakdown Mode' : 'Overall Marks Mode' }}
                  </button>
                </span>
              </div>

              <!-- Marks Entry Form -->
              <div class="card border">
                <div class="card-header bg-light border-bottom">
                  <h6 class="mb-0">
                    <i class="bi me-2" :class="skillMode ? 'bi-list-check' : 'bi-pencil-square'"></i>
                    {{ skillMode ? 'Enter Skill Breakdown Marks' : 'Enter Overall Marks' }}
                    for {{ availableSubjects.find(s => s.id === selectedSubject)?.name }}
                    <span class="float-end">Total Marks: {{ currentSubject?.max_marks || 'N/A' }}</span>
                  </h6>
                </div>
                <div class="card-body">
                  <!-- Submission Mode Toggle -->
                  <div class="row mb-4">
                    <div class="col-md-6">
                      <div class="form-check form-switch">
                        <input 
                          class="form-check-input" 
                          type="checkbox" 
                          id="submitForApproval" 
                          v-model="submitForApproval"
                          style="transform: scale(1.2);"
                        >
                        <label class="form-check-label fw-bold" for="submitForApproval">
                          <i class="bi bi-send-check me-1"></i>
                          Submit for Admin Approval
                        </label>
                        <div class="form-text">
                          <template v-if="submitForApproval">
                            <i class="bi bi-info-circle me-1"></i>
                            Marks will be submitted for admin review and will appear in reports once approved.
                          </template>
                          <template v-else>
                            <i class="bi bi-pencil me-1"></i>
                            Marks will be saved as draft and can be edited later.
                          </template>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-6 text-end">
                      <div class="small">
                        <i class="bi bi-clock-history me-1"></i>
                        {{ enteredMarksCount }} of {{ students.length }} students have marks entered
                        <span v-if="skillMode" class="ms-2">
                          <i class="bi bi-pie-chart me-1"></i>
                          {{ subjectSkills.length }} skills defined
                        </span>
                      </div>
                    </div>
                  </div>

                  <!-- Overall Marks Table -->
                  <div v-if="!skillMode" class="table-responsive">
                    <table class="table table-bordered">
                      <thead class="bg-light">
                        <tr>
                          <th width="50">#</th>
                          <th width="100">Student ID</th>
                          <th>Student Name</th>
                          <th width="150">Overall Marks</th>
                          <th width="120">Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(student, index) in students" :key="student.id">
                          <td class="text-center">{{ index + 1 }}</td>
                          <td>
                            <strong>{{ student.student_id || student.admission_number || student.id }}</strong>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <div class="avatar-sm bg-light rounded border me-2">
                                <span class="avatar-title">
                                  {{ getInitials(student.name || student.first_name + ' ' + student.last_name) }}
                                </span>
                              </div>
                              <div>
                                <strong>{{ student.name || student.first_name + ' ' + student.last_name }}</strong>
                                <div class="small">{{ student.email || 'No email' }}</div>
                              </div>
                            </div>
                          </td>
                          <td>
                            <input 
                              type="number" 
                              class="form-control form-control-sm border"
                              :placeholder="`0-${currentSubject?.max_marks || 100}`"
                              :value="overallMarks[student.id]"
                              @input="event => updateOverallMark(student.id, event.target.value)"
                              :max="currentSubject?.max_marks"
                              min="0"
                              step="0.01"
                              @blur="validateOverallMark(student.id)"
                            >
                          </td>
                          <td>
                            <span class="badge border" :class="getStatusBadgeClass(student.id)">
                              {{ getStatusText(student.id) }}
                            </span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <!-- Skill Breakdown Table -->
                  <div v-else class="table-responsive">
                    <table class="table table-bordered">
                      <thead class="bg-light">
                        <tr>
                          <th rowspan="2" width="50">#</th>
                          <th rowspan="2" width="100">Student ID</th>
                          <th rowspan="2">Student Name</th>
                          <th :colspan="subjectSkills.length" class="text-center">Skill Breakdown</th>
                          <th rowspan="2" width="100">Total</th>
                          <th rowspan="2" width="120">Status</th>
                        </tr>
                        <tr>
                          <th v-for="skill in subjectSkills" :key="skill.id" class="text-center small">
                            {{ skill.skill_name }}<br>
                            <small class="text-muted">/{{ skill.max_marks }}</small>
                          </th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(student, index) in students" :key="student.id">
                          <td class="text-center">{{ index + 1 }}</td>
                          <td>
                            <strong>{{ student.student_id || student.admission_number || student.id }}</strong>
                          </td>
                          <td>
                            <div class="d-flex align-items-center">
                              <div class="avatar-sm bg-light rounded border me-2">
                                <span class="avatar-title">
                                  {{ getInitials(student.name || student.first_name + ' ' + student.last_name) }}
                                </span>
                              </div>
                              <div>
                                <strong>{{ student.name || student.first_name + ' ' + student.last_name }}</strong>
                              </div>
                            </div>
                          </td>
                          <td v-for="skill in subjectSkills" :key="skill.id">
                            <input 
                              type="number" 
                              class="form-control form-control-sm border text-center"
                              :placeholder="`0-${skill.max_marks}`"
                              :value="skillMarks[student.id]?.[skill.id]"
                              @input="event => updateSkillMark(student.id, skill.id, event.target.value)"
                              :max="skill.max_marks"
                              min="0"
                              step="0.01"
                              @blur="validateSkillMark(student.id, skill.id)"
                            >
                          </td>
                          <td class="text-center fw-bold">
                            {{ calculateStudentTotal(student.id) }}
                          </td>
                          <td>
                            <span class="badge border" :class="getStatusBadgeClass(student.id)">
                              {{ getStatusText(student.id) }}
                            </span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <div class="mt-4 p-3 border rounded bg-light">
                    <div class="row align-items-center">
                      <div class="col-md-8">
                        <h6 class="mb-1">
                          <i class="bi me-2" :class="submitForApproval ? 'bi-send-check' : 'bi-pencil'"></i>
                          {{ submitForApproval ? 'Ready for Submission' : 'Save as Draft' }}
                        </h6>
                        <p class="mb-0 small">
                          <template v-if="submitForApproval">
                            Marks will be submitted for admin approval and will be available for report generation once approved.
                          </template>
                          <template v-else>
                            Marks will be saved as draft. You can edit them later before submission.
                          </template>
                        </p>
                      </div>
                      <div class="col-md-4 text-end">
                        <button 
                          class="btn border" 
                          :class="submitForApproval ? 'btn-primary' : 'btn-outline-primary'"
                          @click="saveAllMarks"
                          :disabled="saving || !hasChanges || enteredMarksCount === 0"
                        >
                          <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                          <i v-else class="bi me-2" :class="submitForApproval ? 'bi-send-check' : 'bi-save'"></i>
                          {{ saving ? 'Saving...' : submitForApproval ? `Submit for Approval (${enteredMarksCount})` : `Save as Draft (${enteredMarksCount})` }}
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center p-5">
              <i class="bi bi-people display-4 mb-3"></i>
              <h5>No Students Loaded</h5>
              <p class="mb-3">Please select an exam, class, and subject to view students.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import { Head, Link, usePage, router } from "@inertiajs/vue3";
import { ref, onMounted, computed, watch } from "vue";
import axios from "axios";
import { toast } from 'vue3-toastify';

// Get page props for authentication
const page = usePage();

// Reactive data
const exams = ref([]);
const employeeClasses = ref([]);
const availableSubjects = ref([]);
const students = ref([]);
const subjectSkills = ref([]);
const overallMarks = ref({});
const skillMarks = ref({});
const originalMarks = ref({});
const studentStatuses = ref({});

// Selection variables
const selectedExam = ref(null);
const selectedClass = ref(null);
const selectedSubject = ref(null);

const skillMode = ref(false);
const authError = ref(false);
const saving = ref(false);
const submitForApproval = ref(true);

// Loading states
const loading = ref({
  exams: false,
  classes: false,
  subjects: false,
  students: false,
  skills: false
});

// Computed properties
const currentSubject = computed(() => {
  return availableSubjects.value.find(s => s.id === selectedSubject.value);
});

const enteredMarksCount = computed(() => {
  if (skillMode.value) {
    return Object.values(skillMarks.value).filter(studentMarks => 
      Object.values(studentMarks).some(mark => mark !== null && mark !== '' && !isNaN(mark))
    ).length;
  } else {
    return Object.values(overallMarks.value).filter(mark => 
      mark !== null && mark !== '' && !isNaN(mark)
    ).length;
  }
});

const hasChanges = computed(() => {
  if (skillMode.value) {
    return Object.keys(skillMarks.value).some(studentId => {
      const currentStudentMarks = skillMarks.value[studentId] || {};
      const originalStudentMarks = originalMarks.value[studentId]?.skills || {};
      
      return Object.keys(currentStudentMarks).some(skillId => {
        const currentMark = currentStudentMarks[skillId];
        const originalMark = originalStudentMarks[skillId];
        
        if (currentMark === '' || currentMark === null) {
          return originalMark !== '' && originalMark !== null;
        }
        
        return Number(currentMark) !== Number(originalMark);
      });
    });
  } else {
    return Object.keys(overallMarks.value).some(studentId => {
      const currentMark = overallMarks.value[studentId];
      const originalMark = originalMarks.value[studentId]?.overall;
      
      if (currentMark === '' || currentMark === null) {
        return originalMark !== '' && originalMark !== null;
      }
      
      return Number(currentMark) !== Number(originalMark);
    });
  }
});

// Configure axios with better error handling
const apiClient = axios.create({
  baseURL: window.location.origin,
  withCredentials: true,
  headers: {
    'X-Requested-With': 'XMLHttpRequest',
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  timeout: 30000, // 30 seconds timeout
});

// Add request interceptor
apiClient.interceptors.request.use((config) => {
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (csrfToken) {
    config.headers['X-CSRF-TOKEN'] = csrfToken;
  }
  return config;
});

// Add response interceptor to handle HTML responses
apiClient.interceptors.response.use(
  (response) => {
    // Check if response is HTML instead of JSON
    if (typeof response.data === 'string' && response.data.trim().startsWith('<!DOCTYPE html>')) {
      console.warn('API returned HTML instead of JSON. Possible authentication issue or wrong endpoint.');
      throw new Error('Invalid response format: Received HTML instead of JSON');
    }
    return response;
  },
  (error) => {
    console.error('API Error:', error);
    return Promise.reject(error);
  }
);

// Methods
const fetchExams = async () => {
  try {
    loading.value.exams = true;
    const { data } = await apiClient.get("/employee/datatable/exams");
    exams.value = data.data || [];
  } catch (error) {
    console.error("Failed to load exams", error);
    toast.error("Failed to load exams. Please try again.");
  } finally {
    loading.value.exams = false;
  }
};

const fetchEmployeeClasses = async () => {
  try {
    loading.value.classes = true;
    const { data } = await apiClient.get("/employee/classes");
    employeeClasses.value = data.data || [];
  } catch (error) {
    console.error("Failed to load classes:", error);
    employeeClasses.value = [];
    toast.error("Failed to load classes. Please try again.");
  } finally {
    loading.value.classes = false;
  }
};

const fetchAvailableSubjects = async () => {
  if (!selectedClass.value) {
    availableSubjects.value = [];
    selectedSubject.value = null;
    return;
  }

  try {
    loading.value.subjects = true;
    const { data } = await apiClient.get("/employee/subjects", {
      params: { class_id: selectedClass.value }
    });
    
    availableSubjects.value = data.data || [];
    
    if (availableSubjects.value.length === 1) {
      selectedSubject.value = availableSubjects.value[0].id;
    }
  } catch (error) {
    console.error("Failed to load subjects:", error);
    availableSubjects.value = [];
    toast.error("Failed to load subjects. Please try again.");
  } finally {
    loading.value.subjects = false;
  }
};

const fetchSubjectSkills = async () => {
  if (!selectedExam.value || !selectedClass.value || !selectedSubject.value) {
    subjectSkills.value = [];
    return;
  }

  try {
    loading.value.skills = true;
    const { data } = await apiClient.get("/employee/exam-subject-skills", {
      params: {
        exam_id: selectedExam.value,
        class_id: selectedClass.value,
        subject_id: selectedSubject.value
      }
    });
    
    subjectSkills.value = data.data || [];
    
    // If no skills defined, create default ones
    if (subjectSkills.value.length === 0) {
      await createDefaultSkills();
    }
  } catch (error) {
    console.error("Failed to load subject skills:", error);
    subjectSkills.value = [];
  } finally {
    loading.value.skills = false;
  }
};

const createDefaultSkills = async () => {
  try {
    const subjectName = availableSubjects.value.find(s => s.id === selectedSubject.value)?.name;
    const defaultSkills = getDefaultSkills(subjectName);
    
    if (defaultSkills.length === 0) return;
    
    const { data } = await apiClient.post("/employee/exam-subject-skills", {
      exam_id: selectedExam.value,
      class_id: selectedClass.value,
      subject_id: selectedSubject.value,
      skills: defaultSkills
    });
    
    if (data.success) {
      subjectSkills.value = data.data || [];
      toast.success("Default skills created for this subject");
    }
  } catch (error) {
    console.error("Failed to create default skills:", error);
    // Don't show error toast for this as it's not critical
  }
};

const getDefaultSkills = (subjectName) => {
  const skillConfig = {
    'Mathematics': [
      { skill_name: 'Numbers, measurement & geometry', max_marks: 60, order: 1 },
      { skill_name: 'Listening and speaking', max_marks: 40, order: 2 }
    ],
    'English': [
      { skill_name: 'Reading aloud', max_marks: 30, order: 1 },
      { skill_name: 'Comprehension', max_marks: 20, order: 2 },
      { skill_name: 'Language structure', max_marks: 40, order: 3 },
      { skill_name: 'Guided Writing', max_marks: 10, order: 4 }
    ],
    'Kiswahili': [
      { skill_name: 'Kuskiliza na kuongea', max_marks: 20, order: 1 },
      { skill_name: 'Kusoma kwa santi', max_marks: 40, order: 2 },
      { skill_name: 'Kusoma ulahanna', max_marks: 20, order: 3 },
      { skill_name: 'Matumizi ya lugha', max_marks: 20, order: 4 }
    ]
  };
  
  return skillConfig[subjectName] || [];
};

const loadStudents = async () => {
  if (!selectedExam.value || !selectedClass.value || !selectedSubject.value) {
    toast.error("Please select exam, class, and subject first");
    return;
  }

  try {
    loading.value.students = true;
    
    // Load students
    const params = {
      exam_id: selectedExam.value,
      class_id: selectedClass.value,
      subject_id: selectedSubject.value
    };
    
    const { data } = await apiClient.get("/employee/exams/enrolled-students", {
      params: params
    });
    
    if (data.success) {
      students.value = data.data || [];
      
      // Initialize marks
      students.value.forEach(student => {
        overallMarks.value[student.id] = null;
        skillMarks.value[student.id] = {};
        originalMarks.value[student.id] = { overall: null, skills: {} };
        studentStatuses.value[student.id] = 'pending';
      });
      
      // Load existing marks
      await loadExistingMarks();
      
      // Load subject skills
      await fetchSubjectSkills();
      
    } else {
      students.value = [];
      toast.error("Failed to load students");
    }
    
  } catch (error) {
    console.error("Failed to load students:", error);
    students.value = [];
    toast.error("Failed to load students. Please check your selections and try again.");
  } finally {
    loading.value.students = false;
  }
};

const loadExistingMarks = async () => {
  try {
    const { data } = await apiClient.get("/employee/exams/existing-marks", {
      params: {
        exam_id: selectedExam.value,
        class_id: selectedClass.value,
        subject_id: selectedSubject.value
      }
    });
    
    if (data.success && data.data) {
      data.data.forEach(mark => {
        if (mark.overall_marks !== null) {
          overallMarks.value[mark.student_id] = mark.overall_marks;
          originalMarks.value[mark.student_id].overall = mark.overall_marks;
        }
        
        if (mark.skill_marks && mark.skill_marks.length > 0) {
          mark.skill_marks.forEach(skillMark => {
            if (!skillMarks.value[mark.student_id]) {
              skillMarks.value[mark.student_id] = {};
            }
            skillMarks.value[mark.student_id][skillMark.exam_subject_skill_id] = skillMark.marks_obtained;
            originalMarks.value[mark.student_id].skills[skillMark.exam_subject_skill_id] = skillMark.marks_obtained;
          });
        }
        
        studentStatuses.value[mark.student_id] = mark.status || 'saved';
      });
    }
  } catch (error) {
    console.error("Failed to load existing marks:", error);
    // Don't show error toast for this as it's not critical
  }
};

const toggleSkillMode = () => {
  skillMode.value = !skillMode.value;
};

const updateOverallMark = (studentId, value) => {
  if (value === '' || value === null || value === undefined) {
    overallMarks.value[studentId] = null;
  } else {
    const numericValue = Number(value);
    if (!isNaN(numericValue)) {
      overallMarks.value[studentId] = numericValue;
    } else {
      overallMarks.value[studentId] = null;
    }
  }
  studentStatuses.value[studentId] = 'modified';
};

const updateSkillMark = (studentId, skillId, value) => {
  if (!skillMarks.value[studentId]) {
    skillMarks.value[studentId] = {};
  }
  
  if (value === '' || value === null || value === undefined) {
    skillMarks.value[studentId][skillId] = null;
  } else {
    const numericValue = Number(value);
    if (!isNaN(numericValue)) {
      skillMarks.value[studentId][skillId] = numericValue;
    } else {
      skillMarks.value[studentId][skillId] = null;
    }
  }
  studentStatuses.value[studentId] = 'modified';
};

const calculateStudentTotal = (studentId) => {
  const studentSkillMarks = skillMarks.value[studentId] || {};
  const total = Object.values(studentSkillMarks).reduce((sum, mark) => {
    return sum + (Number(mark) || 0);
  }, 0);
  
  return total.toFixed(1);
};

const validateOverallMark = (studentId) => {
  const mark = overallMarks.value[studentId];
  const maxMarks = currentSubject.value?.max_marks;
  
  if (mark !== null && maxMarks && mark > maxMarks) {
    overallMarks.value[studentId] = maxMarks;
    toast.warning(`Marks cannot exceed maximum of ${maxMarks}`);
  }
  
  if (mark !== null && mark < 0) {
    overallMarks.value[studentId] = 0;
  }
};

const validateSkillMark = (studentId, skillId) => {
  const mark = skillMarks.value[studentId]?.[skillId];
  const skill = subjectSkills.value.find(s => s.id === skillId);
  
  if (mark !== null && skill && mark > skill.max_marks) {
    skillMarks.value[studentId][skillId] = skill.max_marks;
    toast.warning(`Marks cannot exceed maximum of ${skill.max_marks} for ${skill.skill_name}`);
  }
  
  if (mark !== null && mark < 0) {
    skillMarks.value[studentId][skillId] = 0;
  }
};

const getInitials = (name) => {
  if (!name) return '??';
  return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
};

const getStatusText = (studentId) => {
  const status = studentStatuses.value[studentId];
  const hasMarks = skillMode.value ? 
    Object.values(skillMarks.value[studentId] || {}).some(mark => mark !== null && mark !== '') :
    overallMarks.value[studentId] !== null && overallMarks.value[studentId] !== '';
  
  if (status === 'submitted') return 'Submitted';
  if (status === 'saved') return 'Draft';
  if (hasMarks) return 'Modified';
  return 'Pending';
};

const getStatusBadgeClass = (studentId) => {
  const status = studentStatuses.value[studentId];
  const hasMarks = skillMode.value ? 
    Object.values(skillMarks.value[studentId] || {}).some(mark => mark !== null && mark !== '') :
    overallMarks.value[studentId] !== null && overallMarks.value[studentId] !== '';
  
  if (status === 'submitted') return 'bg-success text-white';
  if (status === 'saved') return 'bg-primary text-white';
  if (hasMarks) return 'bg-warning text-dark';
  return 'bg-light text-dark border';
};

const saveAllMarks = async () => {
  if (!hasChanges.value || enteredMarksCount.value === 0) {
    toast.info("No changes to save");
    return;
  }

  try {
    saving.value = true;
    
    const requestData = {
      exam_id: parseInt(selectedExam.value),
      subject_id: parseInt(selectedSubject.value),
      class_id: parseInt(selectedClass.value),
      submit_for_approval: submitForApproval.value,
      skill_mode: skillMode.value
    };

    if (skillMode.value) {
      // Prepare skill marks data
      const skillMarksData = [];
      students.value.forEach(student => {
        const studentSkillMarks = skillMarks.value[student.id] || {};
        Object.keys(studentSkillMarks).forEach(skillId => {
          const markValue = studentSkillMarks[skillId];
          if (markValue !== null && markValue !== '') {
            skillMarksData.push({
              student_id: parseInt(student.id),
              exam_subject_skill_id: parseInt(skillId),
              marks_obtained: parseFloat(markValue)
            });
          }
        });
      });
      requestData.skill_marks = skillMarksData;
    } else {
      // Prepare overall marks data
      const overallMarksData = students.value.map(student => {
        const markValue = overallMarks.value[student.id];
        return {
          student_id: parseInt(student.id),
          marks: markValue !== null && markValue !== '' ? parseFloat(markValue) : null
        };
      }).filter(item => item.marks !== null);
      requestData.overall_marks = overallMarksData;
    }

    const response = await apiClient.post("/employee/exams/save-marks", requestData);

    if (response.data.status === 'success') {
      // Update original marks
      if (skillMode.value) {
        students.value.forEach(student => {
          const studentSkillMarks = skillMarks.value[student.id] || {};
          Object.keys(studentSkillMarks).forEach(skillId => {
            if (!originalMarks.value[student.id].skills) {
              originalMarks.value[student.id].skills = {};
            }
            originalMarks.value[student.id].skills[skillId] = studentSkillMarks[skillId];
          });
        });
      } else {
        students.value.forEach(student => {
          originalMarks.value[student.id].overall = overallMarks.value[student.id];
        });
      }
      
      // Update statuses
      students.value.forEach(student => {
        studentStatuses.value[student.id] = submitForApproval.value ? 'submitted' : 'saved';
      });
      
      const message = submitForApproval.value ? 
        `Successfully submitted marks for admin approval!` :
        `Successfully saved marks as draft.`;
      
      toast.success(message);
      
    } else {
      toast.error(response.data.message || "Failed to save marks");
    }
  } catch (error) {
    console.error("Failed to save marks", error);
    
    if (error.response?.data?.errors) {
      const errors = error.response.data.errors;
      Object.keys(errors).forEach(key => {
        toast.error(errors[key][0]);
      });
    } else if (error.response?.data?.message) {
      toast.error(error.response.data.message);
    } else {
      toast.error("Failed to save marks. Please try again.");
    }
  } finally {
    saving.value = false;
  }
};

// Watchers
watch(selectedClass, (newClassId) => {
  if (newClassId) {
    selectedSubject.value = null;
    students.value = [];
    overallMarks.value = {};
    skillMarks.value = {};
    studentStatuses.value = {};
    subjectSkills.value = [];
    skillMode.value = false;
    fetchAvailableSubjects();
  }
});

watch(selectedSubject, (newSubjectId) => {
  if (newSubjectId) {
    students.value = [];
    overallMarks.value = {};
    skillMarks.value = {};
    studentStatuses.value = {};
    subjectSkills.value = [];
    skillMode.value = false;
  }
});

// Lifecycle
onMounted(async () => {
  try {
    await Promise.all([fetchExams(), fetchEmployeeClasses()]);
  } catch (error) {
    console.error("Initialization failed:", error);
    authError.value = true;
    toast.error("Failed to initialize page. Please refresh and try again.");
  }
});
</script>

<style scoped>
.min-w-200 { min-width: 200px; }
.min-w-250 { min-width: 250px; }

.avatar-sm {
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: bold;
}

.table td {
  vertical-align: middle;
}

.badge {
  font-size: 0.75em;
}

.card {
  background-color: #ffffff;
  border: 1px solid #dee2e6;
}

.card-header.bg-white {
  background-color: #ffffff !important;
}

.card-header.bg-light {
  background-color: #f8f9fa !important;
}

.bg-light {
  background-color: #f8f9fa !important;
}

.border {
  border-color: #dee2e6 !important;
}

.table thead.bg-light {
  background-color: #f8f9fa !important;
}

.alert {
  background-color: #ffffff;
}

.alert-info {
  border-color: #bee5eb;
}

.alert-danger {
  border-color: #f5c6cb;
}

.alert-success {
  border-color: #c3e6cb;
}

.btn-outline-primary {
  border-color: #0d6efd;
  color: #0d6efd;
}

.btn-outline-primary:hover {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.form-control {
  border-color: #dee2e6;
}

.form-control:focus {
  border-color: #86b7fe;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.spinner-border {
  color: #0d6efd;
}

.breadcrumb-item.active {
  color: #6c757d;
}
</style>