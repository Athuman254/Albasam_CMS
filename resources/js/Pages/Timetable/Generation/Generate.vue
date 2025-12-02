<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    versions: Array,
    academicYears: Array,
    currentAcademicYearId: Number,
    classes: Array,
    teachers: Array,
});

const showGenerateModal = ref(false);
const showDeleteModal = ref(false);
const showValidationModal = ref(false);
const showResultModal = ref(false);
const showClassViewModal = ref(false);
const showTeacherViewModal = ref(false);
const deletingVersion = ref(null);
const viewingVersion = ref(null);
const selectedClass = ref(null);
const selectedTeacher = ref(null);
const validationIssues = ref([]);
const generationResult = ref(null);
const isValidating = ref(false);
const timetableData = ref([]);

const form = useForm({
    academic_year_id: props.currentAcademicYearId,
    version_name: '',
    description: '',
});

const hasErrors = computed(() => {
    return validationIssues.value.some(issue => issue.type === 'error');
});

const openGenerateModal = () => {
    form.reset();
    form.academic_year_id = props.currentAcademicYearId;
    
    // Get current academic year name
    const currentYear = props.academicYears.find(y => y.id === props.currentAcademicYearId);
    const yearName = currentYear ? currentYear.name : 'Academic Year';
    
    // Suggest term-based naming
    form.version_name = `${yearName} - Term 1`;
    showGenerateModal.value = true;
};

const validateData = async () => {
    isValidating.value = true;
    try {
        const response = await axios.post(route('timetable.generate.validate'), {
            academic_year_id: props.currentAcademicYearId
        });
        validationIssues.value = response.data.issues;
        showValidationModal.value = true;
    } catch (error) {
        console.error('Validation failed:', error);
        alert('Failed to run validation checks.');
    } finally {
        isValidating.value = false;
    }
};

const proceedToGenerate = () => {
    showValidationModal.value = false;
    openGenerateModal();
};

const generateTimetable = () => {
    form.post(route('timetable.generate.store'), {
        onSuccess: (page) => {
            showGenerateModal.value = false;
            form.reset();
            // Check for generation result in flash
            if (page.props?.flash?.generation_result) {
                generationResult.value = page.props.flash.generation_result;
                showResultModal.value = true;
            }
        },
    });
};

const publishVersion = (version) => {
    if (confirm('Are you sure you want to publish this version? It will become visible to all users.')) {
        router.post(route('timetable.versions.publish', version.id));
    }
};

const confirmDelete = (version) => {
    deletingVersion.value = version;
    showDeleteModal.value = true;
};

const deleteVersion = () => {
    router.delete(route('timetable.versions.destroy', deletingVersion.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

const openClassView = (version) => {
    viewingVersion.value = version;
    selectedClass.value = props.classes && props.classes.length > 0 ? props.classes[0].id : null;
    showClassViewModal.value = true;
    // TODO: Fetch timetable data for the selected class
};

const openTeacherView = (version) => {
    viewingVersion.value = version;
    selectedTeacher.value = props.teachers && props.teachers.length > 0 ? props.teachers[0].id : null;
    showTeacherViewModal.value = true;
    // TODO: Fetch timetable data for the selected teacher
};

const openAllClassesView = (version) => {
    // Navigate to the Class View page to see all classes
    router.visit(route('timetable.view.class'));
};

const downloadClassPdf = () => {
    if (viewingVersion.value && selectedClass.value) {
        window.open(route('timetable.versions.export.class', {
            version: viewingVersion.value.id,
            class: selectedClass.value
        }), '_blank');
    }
};

const downloadTeacherPdf = () => {
    if (viewingVersion.value && selectedTeacher.value) {
        window.open(route('timetable.versions.export.teacher', {
            version: viewingVersion.value.id,
            teacher: selectedTeacher.value
        }), '_blank');
    }
};

const downloadAllClassesPdf = (version) => {
    window.open(route('timetable.versions.export.all', {
        version: version.id
    }), '_blank');
};

const onAcademicYearChange = (e) => {
    router.get(route('timetable.generate.index'), { academic_year_id: e.target.value });
};

const getCoveragePercentage = (stats) => {
    if (!stats || !stats.total_allocations) return 0;
    return Math.round((stats.scheduled / stats.total_allocations) * 100);
};
</script>

<template>
    <Head title="Generate Timetable" />

    <DefaultLayout>
        <div class="container-fluid py-4">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">Timetable /</span> Generation
                </h4>
                <div class="d-flex gap-3">
                    <select 
                        :value="currentAcademicYearId" 
                        @change="onAcademicYearChange"
                        class="form-select"
                        style="width: 200px;"
                    >
                        <option v-for="year in academicYears" :key="year.id" :value="year.id">
                            {{ year.display_name }}
                        </option>
                    </select>
                    <button @click="validateData" :disabled="isValidating" class="btn btn-outline-primary">
                        <i class="fas fa-check-double me-2"></i> 
                        <span v-if="isValidating">Checking...</span>
                        <span v-else>Validate Data</span>
                    </button>
                    <button @click="openGenerateModal" class="btn btn-primary">
                        <i class="fas fa-magic me-2"></i> Generate New
                    </button>
                </div>
            </div>

            <!-- No Timetables State -->
            <div v-if="versions.length === 0" class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-calendar-alt fa-4x text-muted mb-3"></i>
                    <h5 class="card-title">No Timetables Generated</h5>
                    <p class="card-text text-muted">Click "Generate New" to create your first timetable.</p>
                </div>
            </div>

            <!-- Timetable Versions Table -->
            <div v-else class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Version Name</th>
                                    <th>Created</th>
                                    <th>Coverage</th>
                                    <th>Conflicts</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="version in versions" :key="version.id">
                                    <td>
                                        <div class="fw-bold">{{ version.version_name }}</div>
                                        <small class="text-muted">{{ version.description || 'No description' }}</small>
                                    </td>
                                    <td>
                                        <small>{{ new Date(version.created_at).toLocaleDateString() }}</small>
                                        <br>
                                        <small class="text-muted">by {{ version.creator?.name }}</small>
                                    </td>
                                    <td>
                                        <span class="badge" :class="getCoveragePercentage(version.generation_stats) === 100 ? 'bg-success' : 'bg-warning'">
                                            {{ getCoveragePercentage(version.generation_stats) }}%
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ version.generation_stats?.scheduled }} / {{ version.generation_stats?.total_allocations }}</small>
                                    </td>
                                    <td>
                                        <span class="badge" :class="(version.generation_stats?.failed || 0) === 0 ? 'bg-success' : 'bg-danger'">
                                            {{ version.generation_stats?.failed || 0 }}
                                        </span>
                                    </td>
                                    <td>
                                        <span v-if="version.is_active" class="badge bg-success">Active</span>
                                        <span v-else-if="version.is_published" class="badge bg-primary">Published</span>
                                        <span v-else class="badge bg-secondary">Draft</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a class="dropdown-item" href="#" @click.prevent="openClassView(version)">
                                                        <i class="bx bx-chalkboard me-2"></i> View by Class
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" @click.prevent="openTeacherView(version)">
                                                        <i class="bx bx-user me-2"></i> View by Teacher
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="#" @click.prevent="openAllClassesView(version)">
                                                        <i class="bx bx-grid-alt me-2"></i> View All Classes
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li v-if="!version.is_published">
                                                    <a class="dropdown-item" href="#" @click.prevent="publishVersion(version)">
                                                        <i class="bx bx-check me-2"></i> Publish
                                                    </a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item text-danger" href="#" @click.prevent="confirmDelete(version)">
                                                        <i class="bx bx-trash me-2"></i> Delete
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
            </div>
        </div>

        <!-- Generate Modal -->
        <Modal :show="showGenerateModal" @close="showGenerateModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Generate Timetable</h2>
                <p class="text-sm text-gray-500 mb-4">
                    This will run the automated scheduling algorithm based on your configured periods, allocations, and constraints.
                </p>
                
                <div class="space-y-4">
                    <div>
                        <InputLabel for="version_name" value="Version Name" />
                        <TextInput id="version_name" v-model="form.version_name" type="text" class="mt-1 block w-full" />
                        <InputError :message="form.errors.version_name" class="mt-2" />
                    </div>

                    <div>
                        <InputLabel for="description" value="Description (Optional)" />
                        <TextInput id="description" v-model="form.description" type="text" class="mt-1 block w-full" placeholder="e.g. Draft for Term 1" />
                        <InputError :message="form.errors.description" class="mt-2" />
                    </div>
                    
                    <div v-if="form.errors.academic_year_id">
                         <InputError :message="form.errors.academic_year_id" class="mt-2" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showGenerateModal = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="generateTimetable" :disabled="form.processing">
                        <i class="fas fa-cogs mr-2"></i> Run Generator
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Validation Modal -->
        <Modal :show="showValidationModal" @close="showValidationModal = false" maxWidth="3xl">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-medium text-gray-900">
                        <i class="fas fa-clipboard-check mr-2 text-indigo-600"></i> Data Validation Report
                    </h2>
                    <button @click="showValidationModal = false" class="text-gray-400 hover:text-gray-600 p-1">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                
                <div v-if="validationIssues.length === 0" class="text-center py-8">
                    <i class="fas fa-check-circle text-5xl text-green-500 mb-3"></i>
                    <p class="text-lg font-medium text-gray-900">All Checks Passed!</p>
                    <p class="text-gray-500">Your data looks good for generation.</p>
                </div>

                <!-- Scrollable Issues List -->
                <div v-else class="border rounded-lg" style="max-height: 500px; overflow-y: auto;">
                    <div class="space-y-3 p-4">
                        <div v-for="(issue, index) in validationIssues" :key="index" 
                            class="p-4 rounded-lg border-l-4"
                            :class="issue.type === 'error' ? 'bg-red-50 border-red-500' : 'bg-yellow-50 border-yellow-500'"
                        >
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start flex-1">
                                    <i class="fas mt-1 mr-3 flex-shrink-0" :class="issue.type === 'error' ? 'fa-times-circle text-red-500' : 'fa-exclamation-triangle text-yellow-500'"></i>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900" v-html="issue.message"></p>
                                        <p v-if="issue.action" class="text-xs mt-1 text-gray-600">
                                            <i class="fas fa-arrow-right mr-1"></i> {{ issue.action }}
                                        </p>
                                    </div>
                                </div>
                                
                                <!-- Actionable Button with Teacher ID or Class ID -->
                                <div v-if="issue.link_type" class="flex-shrink-0">
                                    <a v-if="issue.link_type === 'teacher_allocation' && issue.teacher_id" 
                                       :href="route('timetable.allocations.index', { teacher_id: issue.teacher_id })"
                                       class="btn btn-sm btn-outline-primary whitespace-nowrap">
                                        <i class="fas fa-external-link-alt mr-1"></i> Fix Now
                                    </a>
                                    <a v-else-if="issue.link_type === 'class_allocation' && issue.class_id" 
                                       :href="route('timetable.allocations.index', { class_id: issue.class_id })"
                                       class="btn btn-sm btn-outline-primary whitespace-nowrap">
                                        <i class="fas fa-external-link-alt mr-1"></i> Fix Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button @click="showValidationModal = false" class="btn btn-secondary">
                        Close
                    </button>
                    <button @click="proceedToGenerate" v-if="!hasErrors" class="btn btn-primary">
                        Proceed to Generate
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Generation Result Modal -->
        <Modal :show="showResultModal" @close="showResultModal = false" maxWidth="lg">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-chart-pie mr-2 text-indigo-600"></i> Generation Report
                </h2>

                <div v-if="generationResult" class="space-y-4">
                    <!-- Summary Stats -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="bg-green-50 p-3 rounded border border-green-200 text-center">
                            <div class="text-2xl font-bold text-green-600">{{ generationResult.scheduled }}</div>
                            <div class="text-xs text-green-800 uppercase font-bold">Scheduled</div>
                        </div>
                        <div class="bg-red-50 p-3 rounded border border-red-200 text-center">
                            <div class="text-2xl font-bold text-red-600">{{ generationResult.failed }}</div>
                            <div class="text-xs text-red-800 uppercase font-bold">Conflicts</div>
                        </div>
                    </div>

                    <!-- Conflicts List -->
                    <div v-if="generationResult.conflicts && generationResult.conflicts.length > 0">
                        <h3 class="text-sm font-bold text-gray-700 mb-2">Conflict Details</h3>
                        <div class="max-h-[40vh] overflow-y-auto pr-2 space-y-2">
                            <div v-for="(conflict, idx) in generationResult.conflicts" :key="idx" 
                                class="p-3 bg-red-50 border-l-4 border-red-500 text-sm text-gray-800 rounded">
                                <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                                {{ conflict }}
                            </div>
                        </div>
                        <div class="mt-3 p-3 bg-blue-50 text-blue-800 text-sm rounded">
                            <i class="fas fa-info-circle mr-1"></i>
                            <strong>Suggestion:</strong> Check teacher availability constraints or reduce class workload to resolve these conflicts.
                        </div>
                    </div>

                    <div v-else class="text-center py-4 text-green-600">
                        <i class="fas fa-check-circle text-4xl mb-2"></i>
                        <p class="font-medium">Timetable generated successfully with no conflicts!</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <PrimaryButton @click="showResultModal = false">Close</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Delete Version</h2>
                <p class="text-gray-600 mb-6">
                    Are you sure you want to delete <strong>{{ deletingVersion?.version_name }}</strong>? This will remove all generated schedules for this version.
                </p>
                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="showDeleteModal = false">Cancel</SecondaryButton>
                    <DangerButton @click="deleteVersion">Delete Version</DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Class View Modal -->
        <Modal :show="showClassViewModal" @close="showClassViewModal = false" maxWidth="4xl">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-chalkboard me-2 text-primary"></i> View Timetable by Class
                </h2>
                
                <div v-if="viewingVersion" class="mb-4">
                    <p class="text-sm text-muted">Version: <strong>{{ viewingVersion.version_name }}</strong></p>
                </div>

                <!-- Class Selector -->
                <div class="mb-4">
                    <label class="form-label">Select Class:</label>
                    <select v-model="selectedClass" class="form-select">
                        <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                            {{ cls.name }}
                        </option>
                    </select>
                </div>

                <!-- Timetable Grid Placeholder -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Timetable Grid Coming Soon!</strong>
                    <p class="mb-0 mt-2">The timetable grid for <strong>{{ classes?.find(c => c.id === selectedClass)?.name }}</strong> will be displayed here.</p>
                    <p class="mb-0 mt-1 small">This will show all periods across the week with subjects and teachers assigned.</p>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button @click="showClassViewModal = false" class="btn btn-secondary">Close</button>
                    <button @click="downloadClassPdf" class="btn btn-primary" :disabled="!selectedClass">
                        <i class="fas fa-file-pdf me-2"></i> Export PDF
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Teacher View Modal -->
        <Modal :show="showTeacherViewModal" @close="showTeacherViewModal = false" maxWidth="4xl">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    <i class="fas fa-user me-2 text-primary"></i> View Timetable by Teacher
                </h2>
                
                <div v-if="viewingVersion" class="mb-4">
                    <p class="text-sm text-muted">Version: <strong>{{ viewingVersion.version_name }}</strong></p>
                </div>

                <!-- Teacher Selector -->
                <div class="mb-4">
                    <label class="form-label">Select Teacher:</label>
                    <select v-model="selectedTeacher" class="form-select">
                        <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                            {{ teacher.name }}
                        </option>
                    </select>
                </div>

                <!-- Timetable Grid Placeholder -->
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Timetable Grid Coming Soon!</strong>
                    <p class="mb-0 mt-2">The timetable grid for <strong>{{ teachers?.find(t => t.id === selectedTeacher)?.name }}</strong> will be displayed here.</p>
                    <p class="mb-0 mt-1 small">This will show all periods across the week with subjects and classes assigned.</p>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button @click="showTeacherViewModal = false" class="btn btn-secondary">Close</button>
                    <button @click="downloadTeacherPdf" class="btn btn-primary" :disabled="!selectedTeacher">
                        <i class="fas fa-file-pdf me-2"></i> Export PDF
                    </button>
                </div>
            </div>
        </Modal>

    </DefaultLayout>
</template>
