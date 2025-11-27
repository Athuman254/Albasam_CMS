<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import WorkloadMeter from '@/Components/Timetable/WorkloadMeter.vue';

const props = defineProps({
    allocations: Object, 
    teachers: Array,
    subjects: Array,
    classes: Array,
    academicYears: Array,
    currentAcademicYearId: Number,
    teacherWorkloads: Array,
    workloadLimits: Object,
});

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const deletingAllocation = ref(null);
const editingAllocation = ref(null);
const selectedTeacherId = ref('');
const teacherSearch = ref('');
const teacherSubjects = ref([]);
const teacherWorkloadStatus = ref(null);
const teacherSuggestions = ref([]);
const showWorkloadWarning = ref(false);
const successMessage = ref('');
const showSuccessMessage = ref(false);

const form = useForm({
    academic_year_id: props.currentAcademicYearId,
    teacher_id: null,
    allocations: [], 
});

const editForm = useForm({
    hours_per_week: 5,
    priority: 'medium',
});

// Computed
const filteredTeachers = computed(() => {
    if (!teacherSearch.value) return props.teachers;
    return props.teachers.filter(t => t.full_name.toLowerCase().includes(teacherSearch.value.toLowerCase()));
});

const currentTeacherWorkload = computed(() => {
    if (!form.teacher_id) return null;
    return props.teacherWorkloads.find(w => w.teacher_id === form.teacher_id) || {
        total_hours_per_week: 0,
        total_classes: 0,
        total_subjects: 0
    };
});

const selectedTeacher = computed(() => {
    return props.teachers.find(t => t.id === selectedTeacherId.value);
});

const projectedWorkload = computed(() => {
    if (!currentTeacherWorkload.value) return null;
    
    // Calculate total hours from all allocations
    const totalNewHours = form.allocations.reduce((sum, alloc) => {
        return sum + (alloc.class_ids.length * alloc.hours_per_week);
    }, 0);
    
    const uniqueSubjects = new Set(form.allocations.map(a => a.subject_id));
    const uniqueClasses = new Set(form.allocations.flatMap(a => a.class_ids));

    return {
        total_hours_per_week: currentTeacherWorkload.value.total_hours_per_week + totalNewHours,
        total_classes: currentTeacherWorkload.value.total_classes + uniqueClasses.size,
        total_subjects: currentTeacherWorkload.value.total_subjects + uniqueSubjects.size,
    };
});

const isOverloaded = computed(() => {
    if (!projectedWorkload.value) return false;
    return projectedWorkload.value.total_hours_per_week > props.workloadLimits.max_hours_per_week ||
           projectedWorkload.value.total_classes > props.workloadLimits.max_classes_per_teacher ||
           projectedWorkload.value.total_subjects > props.workloadLimits.max_subjects_per_teacher;
});

// Methods
const getTeacherEmployeeId = (teacherId) => {
    const teacher = props.teachers.find(t => t.id === teacherId);
    return teacher ? teacher.employee_id : null;
};

const openCreateModal = (teacherId = null) => {
    form.reset();
    form.clearErrors(); // Clear any previous errors
    form.academic_year_id = props.currentAcademicYearId;
    if (teacherId) {
        form.teacher_id = teacherId;
    }
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
    // Clear errors after a short delay to allow modal animation
    setTimeout(() => {
        form.clearErrors();
    }, 300);
};

// Initialize allocations when teacher is selected and subjects are loaded
const initializeAllocations = () => {
    if (teacherSubjects.value.length > 0) {
        form.allocations = teacherSubjects.value.map(subject => ({
            subject_id: subject.id,
            subject_name: subject.name,
            class_ids: [],
            hours_per_week: 5,
            priority: 'medium'
        }));
    }
};

// Add method to calculate workload for a specific allocation
const calculateAllocationWorkload = (allocation) => {
    if (!currentTeacherWorkload.value) return 0;
    return allocation.class_ids.length * allocation.hours_per_week;
};

// Add method to get total projected hours
const getTotalProjectedHours = () => {
    return form.allocations.reduce((sum, alloc) => {
        return sum + calculateAllocationWorkload(alloc);
    }, 0);
};

const createAllocation = () => {
    // Filter out allocations with no classes selected
    const validAllocations = form.allocations.filter(a => a.class_ids.length > 0);
    
    if (validAllocations.length === 0) {
        alert('Please select at least one class for a subject');
        return;
    }
    
    // Transform to backend format
    const payload = {
        academic_year_id: form.academic_year_id,
        teacher_id: form.teacher_id,
        allocations: validAllocations
    };
    
    form.post(route('timetable.allocations.store'), {
        data: payload,
        onSuccess: () => {
            closeCreateModal();
            form.reset();
            form.allocations = [];
            
            // Show success message
            successMessage.value = 'Subject allocation created successfully!';
            showSuccessMessage.value = true;
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                showSuccessMessage.value = false;
            }, 5000);
        },
        onError: () => {
            // Auto-dismiss errors after 5 seconds
            setTimeout(() => {
                form.clearErrors();
            }, 5000);
        },
    });
};

const confirmDelete = (allocation) => {
    deletingAllocation.value = allocation;
    showDeleteModal.value = true;
};

const deleteAllocation = () => {
    router.delete(route('timetable.allocations.destroy', deletingAllocation.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingAllocation.value = null;
        },
    });
};

const selectTeacher = (teacher) => {
    selectedTeacherId.value = teacher.id;
    teacherSearch.value = ''; // Clear search to hide dropdown
};

const onAcademicYearChange = (e) => {
    router.get(route('timetable.allocations.index'), { academic_year_id: e.target.value });
};

const getTeacherWorkload = (teacherId) => {
    return props.teacherWorkloads.find(w => w.teacher_id === teacherId) || {
        total_hours_per_week: 0,
        total_classes: 0,
        total_subjects: 0
    };
};

// Watch for teacher selection to load their subjects
watch(() => form.teacher_id, async (newTeacherId) => {
    if (newTeacherId) {
        try {
            // Fetch teacher's subjects
            const subjectsResponse = await axios.get(route('timetable.api.teacher-subjects', newTeacherId));
            teacherSubjects.value = subjectsResponse.data.subjects;
            
            // Initialize allocations for each subject
            initializeAllocations();
            
            // Fetch teacher's workload status
            const statusResponse = await axios.get(route('timetable.api.teacher-workload-status', newTeacherId), {
                params: { academic_year_id: props.currentAcademicYearId }
            });
            teacherWorkloadStatus.value = statusResponse.data;
            
            // Show warning if teacher is at/near limit
            showWorkloadWarning.value = statusResponse.data.status !== 'available';
            
        } catch (error) {
            console.error('Error fetching teacher data:', error);
        }
    } else {
        teacherSubjects.value = [];
        form.allocations = [];
        teacherWorkloadStatus.value = null;
        showWorkloadWarning.value = false;
        teacherSuggestions.value = [];
    }
});

// Computed filtered subjects based on teacher's qualifications
const availableSubjects = computed(() => {
    if (!form.teacher_id || teacherSubjects.value.length === 0) {
        return props.subjects;
    }
    return teacherSubjects.value;
});

// Accordion state for subject cards
const expandedSubjectIndex = ref(0); // First subject expanded by default

// Toggle subject card expansion
const toggleSubjectCard = (index) => {
    expandedSubjectIndex.value = expandedSubjectIndex.value === index ? -1 : index;
};

// Open edit modal
const openEditModal = (allocation) => {
    editingAllocation.value = allocation;
    editForm.hours_per_week = allocation.hours_per_week;
    editForm.priority = allocation.priority;
    showEditModal.value = true;
};

// Auto-dismiss errors after 5 seconds
const autoDismissErrors = () => {
    setTimeout(() => {
        editForm.clearErrors();
    }, 5000); // 5 seconds
};

// Update allocation
const updateAllocation = () => {
    editForm.put(route('timetable.allocations.update', editingAllocation.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        },
        onError: () => {
            autoDismissErrors();
        }
    });
};

// Navigate to teacher edit page
const editTeacher = () => {
    if (!form.teacher_id) return;
    
    // Find the selected teacher to get their teacher_hashid
    const selectedTeacher = props.teachers.find(t => t.id === form.teacher_id);
    if (selectedTeacher && selectedTeacher.teacher_hashid) {
        // Use Inertia to navigate to the edit page without full page reload
        router.visit(route('admin.teachers.edit', selectedTeacher.teacher_hashid));
    }
};
</script>

<template>
    <Head title="Subject Allocation" />

    <DefaultLayout>
        <div class="container-fluid py-4">
            <!-- Success Message -->
            <transition name="fade">
                <div v-if="showSuccessMessage" class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Success!</strong> {{ successMessage }}
                    <button type="button" class="btn-close" @click="showSuccessMessage = false" aria-label="Close"></button>
                </div>
            </transition>

            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">Timetable /</span> Subject Allocation
                </h4>
                <div class="d-flex gap-3">
                    <select 
                        :value="currentAcademicYearId" 
                        @change="onAcademicYearChange"
                        class="form-select"
                        style="width: 200px;"
                    >
                        <option v-for="year in academicYears" :key="year.id" :value="year.id">
                            {{ year.name }}
                        </option>
                    </select>
                    <button @click="openCreateModal" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i> New Allocation
                    </button>
                </div>
            </div>
        </div>

        <!-- Teacher List & Allocations -->
        <div class="row g-4">
            
            <!-- Sidebar: Teachers -->
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3 position-relative">
                            <label class="form-label small text-muted">Find Teacher</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input 
                                    type="text" 
                                    v-model="teacherSearch" 
                                    placeholder="Search by name..." 
                                    class="form-control border-start-0 ps-0"
                                />
                            </div>
                            
                            <!-- Search Results Dropdown -->
                            <div v-if="teacherSearch" class="position-absolute w-100 bg-white shadow rounded mt-1 border" style="z-index: 1000; max-height: 300px; overflow-y: auto;">
                                <div v-if="filteredTeachers.length === 0" class="p-3 text-center text-muted small">
                                    No teachers found
                                </div>
                                <div 
                                    v-else
                                    v-for="teacher in filteredTeachers" 
                                    :key="teacher.id"
                                    @click="selectTeacher(teacher)"
                                    class="p-2 border-bottom cursor-pointer hover-bg-light"
                                    style="cursor: pointer;"
                                >
                                    <div class="fw-medium text-dark">{{ teacher.full_name }}</div>
                                    <small class="text-muted">{{ teacher.email }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Teacher Display -->
                        <div v-if="selectedTeacher" class="mt-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0 text-dark">Selected Teacher</h6>
                                <button @click="selectedTeacherId = ''; form.teacher_id = ''" class="btn btn-sm btn-outline-secondary py-0 px-2" title="Clear Selection">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            
                            <div class="p-3 bg-white rounded border shadow-sm">
                                <div class="fw-bold text-dark mb-1">{{ selectedTeacher.full_name }}</div>
                                <div class="small text-muted mb-3">{{ selectedTeacher.email }}</div>
                                
                                <div class="border-top pt-2 mb-3">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Current Workload</span>
                                        <span class="fw-bold">{{ getTeacherWorkload(selectedTeacher.id).total_hours_per_week }} / {{ workloadLimits.max_hours_per_week }} hrs</span>
                                    </div>
                                    <WorkloadMeter :workload="getTeacherWorkload(selectedTeacher.id)" :limits="workloadLimits" />
                                </div>

                                <button @click="openCreateModal(selectedTeacher.id)" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-plus me-1"></i> Add Allocation
                                </button>
                            </div>
                        </div>
                        
                        <div v-else-if="!teacherSearch" class="text-center py-4 text-muted small">
                            <i class="fas fa-user-check fa-2x mb-2 opacity-25"></i>
                            <p class="mb-0">Search and select a teacher to manage allocations</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main: Allocations -->
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-body">
                        <div v-if="!selectedTeacherId" class="text-center py-5 text-muted">
                            <i class="fas fa-chalkboard-teacher fa-4x mb-3 opacity-50"></i>
                            <p>Select a teacher to view and manage allocations</p>
                        </div>

                        <div v-else>
                            <div class="d-flex justify-content-between align-items-start mb-4 pb-3 border-bottom">
                                <div>
                                    <h3 class="h4 fw-bold text-dark mb-1">
                                        {{ teachers.find(t => t.id === selectedTeacherId)?.full_name }}
                                    </h3>
                                    <p class="text-muted small mb-0">Manage subject and class assignments</p>
                                </div>
                                <div style="width: 250px;">
                                    <WorkloadMeter :workload="getTeacherWorkload(selectedTeacherId)" :limits="workloadLimits" />
                                </div>
                            </div>

                            <div v-if="!allocations[teachers.find(t => t.id === selectedTeacherId)?.full_name]" class="text-center py-5 bg-light rounded border border-dashed">
                                <p class="text-muted mb-3">No allocations found for this teacher.</p>
                                <button 
                                    @click="() => { form.teacher_id = selectedTeacherId; openCreateModal(); }" 
                                    class="btn btn-primary"
                                >
                                    <i class="fas fa-plus me-2"></i> Assign Subjects
                                </button>
                            </div>

                            <div v-else class="row g-3">
                                <div 
                                    v-for="allocation in allocations[teachers.find(t => t.id === selectedTeacherId)?.full_name]" 
                                    :key="allocation.id"
                                    class="col-md-6"
                                >
                                    <div class="card border h-100 allocation-card position-relative">
                                        <div class="card-body">
                                            <!-- 3-Dot Menu -->
                                            <div class="dropdown position-absolute top-0 end-0 m-2">
                                                <button class="btn align-text-top py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="icon-base bx bx-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                                    <li>
                                                        <a class="dropdown-item py-2 px-3" href="javascript:void(0);" @click.prevent="openEditModal(allocation)">
                                                            <i class="fas fa-edit me-2 text-primary"></i> Edit Allocation
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider my-1">
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item py-2 px-3 text-danger" href="javascript:void(0);" @click.prevent="confirmDelete(allocation)">
                                                            <i class="fas fa-trash me-2"></i> Delete Allocation
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            
                                            <div class="d-flex align-items-center gap-3 mb-3">
                                                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded">
                                                    <i class="fas fa-book"></i>
                                                </div>
                                                <div>
                                                    <h5 class="fw-bold mb-0">{{ allocation.class.name }} - {{ allocation.subject.name }}</h5>
                                                    <div class="text-muted small">
                                                        <i class="fas fa-user me-1"></i> {{ teachers.find(t => t.id === selectedTeacherId)?.full_name }}
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                                <span class="badge bg-light text-dark">
                                                    <i class="far fa-clock me-1"></i> {{ allocation.hours_per_week }} hrs/week
                                                </span>
                                                <span 
                                                    class="badge text-uppercase"
                                                    :class="{
                                                        'bg-danger': allocation.priority === 'high',
                                                        'bg-warning': allocation.priority === 'medium',
                                                        'bg-success': allocation.priority === 'low'
                                                    }"
                                                >
                                                    {{ allocation.priority }} Priority
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false" maxWidth="2xl">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                    <h4 class="mb-0 fw-bold">New Allocation</h4>
                    <button @click="showCreateModal = false" class="btn-close" aria-label="Close"></button>
                </div>
                
                <div class="modal-content-wrapper" style="max-width: 900px; margin: 0 auto;">
                    <!-- Teacher Selection -->
                    <div class="mb-4">
                        <InputLabel for="teacher_id" value="Teacher" class="mb-2" />
                        <select id="teacher_id" v-model="form.teacher_id" class="form-select">
                            <option :value="null" disabled selected style="color: #6c757d;">Select Teacher</option>
                            <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                {{ teacher.full_name }}
                            </option>
                        </select>
                        <div v-if="form.errors.teacher_id" class="text-danger small mt-1">{{ form.errors.teacher_id }}</div>
                    </div>

                    <!-- Projected Workload Display -->
                    <div v-if="form.teacher_id && teacherWorkload" class="mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title mb-3 fw-bold text-dark">
                                    <i class="fas fa-chart-line me-2 text-primary"></i>
                                    Projected Workload
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="d-flex flex-column">
                                            <small class="text-muted mb-1">Hours/Week</small>
                                            <div class="d-flex align-items-center">
                                                <strong class="fs-5 me-2">{{ teacherWorkload.total_hours_per_week }}</strong>
                                                <span class="text-muted small">/ {{ teacherWorkload.max_hours }}</span>
                                            </div>
                                            <div class="progress mt-2" style="height: 6px;">
                                                <div class="progress-bar" :class="{
                                                    'bg-success': (teacherWorkload.total_hours_per_week / teacherWorkload.max_hours * 100) < 70,
                                                    'bg-warning': (teacherWorkload.total_hours_per_week / teacherWorkload.max_hours * 100) >= 70 && (teacherWorkload.total_hours_per_week / teacherWorkload.max_hours * 100) < 90,
                                                    'bg-danger': (teacherWorkload.total_hours_per_week / teacherWorkload.max_hours * 100) >= 90
                                                }" :style="{ width: (teacherWorkload.total_hours_per_week / teacherWorkload.max_hours * 100) + '%' }"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex flex-column">
                                            <small class="text-muted mb-1">Total Classes</small>
                                            <div class="d-flex align-items-center">
                                                <strong class="fs-5 me-2">{{ teacherWorkload.total_classes }}</strong>
                                                <span class="text-muted small">/ {{ teacherWorkload.max_classes }}</span>
                                            </div>
                                            <div class="progress mt-2" style="height: 6px;">
                                                <div class="progress-bar" :class="{
                                                    'bg-success': (teacherWorkload.total_classes / teacherWorkload.max_classes * 100) < 70,
                                                    'bg-warning': (teacherWorkload.total_classes / teacherWorkload.max_classes * 100) >= 70 && (teacherWorkload.total_classes / teacherWorkload.max_classes * 100) < 90,
                                                    'bg-danger': (teacherWorkload.total_classes / teacherWorkload.max_classes * 100) >= 90
                                                }" :style="{ width: (teacherWorkload.total_classes / teacherWorkload.max_classes * 100) + '%' }"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex flex-column">
                                            <small class="text-muted mb-1">Total Subjects</small>
                                            <div class="d-flex align-items-center">
                                                <strong class="fs-5 me-2">{{ teacherWorkload.total_subjects }}</strong>
                                                <span class="text-muted small">/ {{ teacherWorkload.max_subjects }}</span>
                                            </div>
                                            <div class="progress mt-2" style="height: 6px;">
                                                <div class="progress-bar" :class="{
                                                    'bg-success': (teacherWorkload.total_subjects / teacherWorkload.max_subjects * 100) < 70,
                                                    'bg-warning': (teacherWorkload.total_subjects / teacherWorkload.max_subjects * 100) >= 70 && (teacherWorkload.total_subjects / teacherWorkload.max_subjects * 100) < 90,
                                                    'bg-danger': (teacherWorkload.total_subjects / teacherWorkload.max_subjects * 100) >= 90
                                                }" :style="{ width: (teacherWorkload.total_subjects / teacherWorkload.max_subjects * 100) + '%' }"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="(teacherWorkload.total_hours_per_week / teacherWorkload.max_hours * 100) >= 90" class="alert alert-danger mt-3 mb-0 py-2">
                                    <small><i class="fas fa-exclamation-triangle me-2"></i><strong>OVERLOAD WARNING</strong></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Workload Status Warning -->
                    <div v-if="showWorkloadWarning && teacherWorkloadStatus" class="alert mb-4" :class="{
                        'alert-danger': teacherWorkloadStatus.status === 'at_limit',
                        'alert-warning': teacherWorkloadStatus.status === 'near_limit'
                    }">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <div>
                                <strong>{{ teacherWorkloadStatus.status === 'at_limit' ? 'Allocation Limit Reached!' : 'Near Allocation Limit' }}</strong>
                                <p class="mb-0 small">{{ teacherWorkloadStatus.warnings.join(', ') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Teacher Suggestions (when at limit) -->
                    <div v-if="teacherSuggestions.length > 0" class="alert alert-info mb-4">
                        <strong>Alternative Teachers Available:</strong>
                        <div class="mt-2">
                            <div v-for="suggestion in teacherSuggestions" :key="suggestion.id" class="d-flex justify-content-between align-items-center mb-1">
                                <span>{{ suggestion.name }}</span>
                                <span class="badge bg-success">{{ suggestion.workload.total_hours_per_week }} hrs/week</span>
                            </div>
                        </div>
                    </div>

                    <!-- Subject Allocations - Show teacher's subjects with class selection -->
                    <div v-if="form.teacher_id">
                        <div v-if="teacherSubjects.length === 0" class="alert alert-warning">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center flex-grow-1">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <div>
                                        <strong>No Subject Qualifications Found</strong>
                                        <p class="mb-0 small">This teacher has no subject qualifications configured. Please add subjects to enable allocation.</p>
                                    </div>
                                </div>
                                <button 
                                    type="button"
                                    class="btn btn-sm btn-warning ms-3"
                                    @click="editTeacher"
                                >
                                    <i class="fas fa-edit me-1"></i>
                                    Edit Teacher
                                </button>
                            </div>
                        </div>
                        
                        <div v-else>
                            <h6 class="mb-3 fw-bold text-dark">
                                <i class="fas fa-chalkboard-teacher me-2 text-primary"></i>
                                Allocate Subjects to Classes
                            </h6>
                            <p class="small text-muted mb-3">Select classes for each subject this teacher will teach:</p>
                            
                            <!-- Loop through each subject the teacher teaches -->
                            <div v-for="(allocation, index) in form.allocations" :key="allocation.subject_id" class="card mb-3 border shadow-sm">
                                <div class="card-body bg-white p-0">
                                    <!-- Clickable Header -->
                                    <div class="d-flex justify-content-between align-items-center p-3">
                                        <div class="d-flex align-items-center">
                                            <h6 class="mb-0 fw-bold text-dark">
                                                <i class="fas fa-book text-primary me-2"></i>
                                                {{ allocation.subject_name }}
                                            </h6>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge" :class="{
                                                'bg-success': calculateAllocationWorkload(allocation) === 0,
                                                'bg-info': calculateAllocationWorkload(allocation) > 0 && calculateAllocationWorkload(allocation) <= 10,
                                                'bg-warning': calculateAllocationWorkload(allocation) > 10 && calculateAllocationWorkload(allocation) <= 20,
                                                'bg-danger': calculateAllocationWorkload(allocation) > 20
                                            }">
                                                {{ calculateAllocationWorkload(allocation) }} hrs/week
                                            </span>
                                            <button 
                                                type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                @click="toggleSubjectCard(index)"
                                            >
                                                <i class="fas" :class="expandedSubjectIndex === index ? 'fa-eye-slash' : 'fa-eye'"></i>
                                                {{ expandedSubjectIndex === index ? 'Hide Classes' : 'Show Classes' }}
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Collapsible Class Selection -->
                                    <div v-show="expandedSubjectIndex === index" class="border-top">
                                        <div class="p-3">
                                            <label class="form-label small fw-semibold text-dark mb-2">Select Classes:</label>
                                        <div class="border rounded p-3 bg-white row g-2" style="max-height: 150px; overflow-y: auto;">
                                            <div v-for="cls in classes" :key="cls.id" class="col-md-4 col-sm-6">
                                                <div class="form-check">
                                                    <input 
                                                        type="checkbox" 
                                                        :id="`alloc_${index}_cls_${cls.id}`" 
                                                        :value="cls.id" 
                                                        v-model="allocation.class_ids" 
                                                        class="form-check-input"
                                                    >
                                                    <label :for="`alloc_${index}_cls_${cls.id}`" class="form-check-label small">
                                                        {{ cls.name }}
                                                    </label>
                                                </div>
                                            </div>
                                            </div>
                                            
                                            <!-- Hours per week and Priority for this subject -->
                                            <div class="row g-3 mt-3">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Hours/Week per Class:</label>
                                                    <input 
                                                        type="number" 
                                                        class="form-control form-control-sm" 
                                                        v-model.number="allocation.hours_per_week" 
                                                        min="1" 
                                                        max="10"
                                                        placeholder="e.g., 5"
                                                    />
                                                    <small class="text-muted">Typical: 3-5 hours</small>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Priority:</label>
                                                    <select class="form-select form-select-sm" v-model="allocation.priority">
                                                        <option value="low">Low</option>
                                                        <option value="medium" selected>Medium</option>
                                                        <option value="high">High</option>
                                                    </select>
                                                    <small class="text-muted">Affects scheduling order</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Total Workload Summary -->
                            <div class="alert alert-secondary mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">Total New Workload:</span>
                                    <span class="badge bg-primary fs-6">{{ getTotalProjectedHours() }} hours/week</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Please select a teacher to view their subjects and create allocations.
                    </div>
                    
                    <div v-if="form.errors.workload" class="alert alert-danger mt-3" role="alert">
                        {{ form.errors.workload }}
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <button @click="showCreateModal = false" class="btn btn-secondary">
                        Cancel
                    </button>
                    <button 
                        @click="createAllocation" 
                        class="btn btn-primary" 
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">
                            <i class="fas fa-spinner fa-spin me-1"></i> Creating...
                        </span>
                        <span v-else>
                            <i class="fas fa-plus me-1"></i> Create Allocation
                        </span>
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Edit Modal -->
        <Modal :show="showEditModal" @close="showEditModal = false">
            <div class="modal-body">
                <h3 class="mb-4">Edit Allocation</h3>
                
                <div v-if="editingAllocation" class="alert alert-secondary mb-3">
                    <div class="small">
                        <strong>Teacher:</strong> {{ editingAllocation.teacher?.name }}<br>
                        <strong>Subject:</strong> {{ editingAllocation.subject?.subject_name }}<br>
                        <strong>Class:</strong> {{ editingAllocation.class?.name }}
                    </div>
                </div>
                
                <div>
                    <div class="mb-3">
                        <InputLabel for="edit_hours" value="Hours Per Week" />
                        <input id="edit_hours" v-model="editForm.hours_per_week" type="number" min="1" max="10" class="form-control mt-2" />
                        <div v-if="editForm.errors.hours_per_week" class="text-danger small mt-1">{{ editForm.errors.hours_per_week }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <InputLabel for="edit_priority" value="Priority" />
                        <select id="edit_priority" v-model="editForm.priority" class="form-select mt-2">
                            <option value="high">High</option>
                            <option value="medium">Medium</option>
                            <option value="low">Low</option>
                        </select>
                    </div>
                    
                    <div v-if="editForm.errors.workload" class="alert alert-danger" role="alert">
                        {{ editForm.errors.workload }}
                    </div>
                </div>

                <div class="modal-footer">
                    <SecondaryButton @click="showEditModal = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="updateAllocation" :disabled="editForm.processing">Update Allocation</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="modal-body">
                <h3 class="mb-4">Remove Allocation</h3>
                <p class="mb-4">
                    Are you sure you want to remove <strong>{{ deletingAllocation?.subject?.subject_name }}</strong> for <strong>{{ deletingAllocation?.class?.name }}</strong>?
                </p>
                <div class="modal-footer">
                    <SecondaryButton @click="showDeleteModal = false">Cancel</SecondaryButton>
                    <DangerButton @click="deleteAllocation">Remove</DangerButton>
                </div>
            </div>
        </Modal>

    </DefaultLayout>
</template>

<style scoped>
.allocation-card:hover .delete-btn {
    opacity: 1 !important;
}

.teacher-item:hover {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
}
</style>
