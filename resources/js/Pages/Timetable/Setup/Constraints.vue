<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    constraints: Object,
    teachers: Array,
    classes: Array,
    subjects: Array,
    rooms: Array,
    academicYears: Array,
    currentAcademicYearId: Number,
    constraintTypes: Object,
    daysOfWeek: Array,
});

const showCreateModal = ref(false);
const showDeleteModal = ref(false);
const deletingConstraint = ref(null);
const activeTab = ref('teacher_availability');
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    academic_year_id: props.currentAcademicYearId,
    constraint_type: 'teacher_availability',
    teacher_id: '',
    class_id: '',
    subject_id: '',
    room_id: '',
    day_of_week: '',
    period_number: '',
    constraint_value: 'unavailable',
    notes: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.academic_year_id = props.currentAcademicYearId;
    form.constraint_type = activeTab.value; // Default to current tab
    showCreateModal.value = true;
};

const openEditModal = (constraint) => {
    isEditing.value = true;
    editingId.value = constraint.id;
    form.academic_year_id = constraint.academic_year_id;
    form.constraint_type = constraint.constraint_type;
    form.teacher_id = constraint.teacher_id;
    form.class_id = constraint.class_id;
    form.subject_id = constraint.subject_id;
    form.room_id = constraint.room_id;
    form.day_of_week = constraint.day_of_week || '';
    form.period_number = constraint.period_number || '';
    form.constraint_value = constraint.constraint_value;
    form.notes = constraint.notes || '';
    showCreateModal.value = true;
};

const saveConstraint = () => {
    if (isEditing.value) {
        form.put(route('timetable.constraints.update', editingId.value), {
            onSuccess: () => {
                showCreateModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('timetable.constraints.store'), {
            onSuccess: () => {
                showCreateModal.value = false;
                form.reset();
            },
        });
    }
};

const confirmDelete = (constraint) => {
    deletingConstraint.value = constraint;
    showDeleteModal.value = true;
};

const deleteConstraint = () => {
    router.delete(route('timetable.constraints.destroy', deletingConstraint.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

const onAcademicYearChange = (e) => {
    router.get(route('timetable.constraints.index'), { academic_year_id: e.target.value });
};

const getConstraintDescription = (constraint) => {
    let desc = '';
    switch (constraint.constraint_type) {
        case 'teacher_availability':
            desc = `${constraint.teacher?.name}`;
            break;
        case 'room_availability':
            desc = `${constraint.room?.room_name}`;
            break;
        default:
            desc = props.constraintTypes[constraint.constraint_type] || 'Constraint';
    }
    return desc;
};

const getConstraintDetail = (constraint) => {
     return `${constraint.day_of_week || 'Any Day'} ${constraint.period_number ? '- Period ' + constraint.period_number : ''}`;
}

const getStatusBadgeClass = (value) => {
    switch (value) {
        case 'unavailable': return 'bg-danger';
        case 'preferred': return 'bg-success';
        case 'not_preferred': return 'bg-warning text-dark';
        case 'required': return 'bg-primary';
        default: return 'bg-secondary';
    }
};

const getStatusLabel = (value) => {
    switch (value) {
        case 'unavailable': return 'Unavailable';
        case 'preferred': return 'Preferred';
        case 'not_preferred': return 'Not Preferred';
        case 'required': return 'Required';
        default: return value;
    }
};
</script>

<template>
    <Head title="Timetable Constraints" />

    <DefaultLayout>
        <div class="container-fluid py-4">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">Timetable /</span> Constraints
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
                        <i class="fas fa-plus me-2"></i> Add Constraint
                    </button>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item" v-for="(label, key) in constraintTypes" :key="key">
                    <a 
                        class="nav-link cursor-pointer" 
                        :class="{ 'active fw-bold': activeTab === key, 'text-muted': activeTab !== key }"
                        @click.prevent="activeTab = key"
                        href="#"
                    >
                        {{ label }}
                        <span v-if="constraints[key]" class="badge bg-secondary ms-2 rounded-pill">{{ constraints[key].length }}</span>
                    </a>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content">
                <div class="tab-pane fade show active">
                    
                    <div v-if="!constraints[activeTab] || constraints[activeTab].length === 0" class="text-center py-5 bg-white rounded border border-dashed">
                        <i class="fas fa-clipboard-check fa-3x mb-3 text-muted opacity-50"></i>
                        <p class="text-muted mb-0">No constraints defined for {{ constraintTypes[activeTab] }}.</p>
                        <p class="text-muted small">The generator will assume full availability.</p>
                        <button @click="openCreateModal" class="btn btn-outline-primary btn-sm mt-3">
                            Add {{ constraintTypes[activeTab] }} Constraint
                        </button>
                    </div>

                    <div v-else class="row g-3">
                        <div v-for="constraint in constraints[activeTab]" :key="constraint.id" class="col-md-6 col-lg-4">
                            <div class="card h-100 border shadow-sm hover-shadow transition">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded p-2 me-3 text-primary">
                                                <i class="fas" :class="{
                                                    'fa-user-clock': activeTab === 'teacher_availability',
                                                    'fa-door-open': activeTab === 'room_availability',
                                                    'fa-exclamation-circle': !['teacher_availability', 'room_availability'].includes(activeTab)
                                                }"></i>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold mb-0 text-dark">{{ getConstraintDescription(constraint) }}</h6>
                                                <small class="text-muted">{{ getConstraintDetail(constraint) }}</small>
                                            </div>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn align-text-top py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="icon-base bx bx-dots-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a class="dropdown-item" href="javascript:void(0);" @click.prevent="openEditModal(constraint)">
                                                    <i class="icon-base bx bx-edit-alt me-1"></i> Edit
                                                </a>
                                                <a class="dropdown-item text-danger" href="javascript:void(0);" @click.prevent="confirmDelete(constraint)">
                                                    <i class="icon-base bx bx-trash me-1"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <span class="badge" :class="getStatusBadgeClass(constraint.constraint_value)">
                                            {{ getStatusLabel(constraint.constraint_value) }}
                                        </span>
                                        <small v-if="constraint.notes" class="text-muted fst-italic text-truncate" style="max-width: 150px;" :title="constraint.notes">
                                            <i class="fas fa-info-circle me-1"></i> {{ constraint.notes }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false" maxWidth="md">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                    <h5 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-plus-circle me-2"></i>
                        {{ isEditing ? 'Edit' : 'Add' }} {{ constraintTypes[form.constraint_type] }}
                    </h5>
                    <button @click="showCreateModal = false" class="btn-close" aria-label="Close"></button>
                </div>
                
                <div class="mb-4">
                    <InputLabel for="type" value="Constraint Type" class="form-label text-muted small text-uppercase fw-bold" />
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-filter text-muted"></i></span>
                        <select id="type" v-model="form.constraint_type" class="form-select">
                            <option v-for="(label, key) in constraintTypes" :key="key" :value="key">
                                {{ label }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Dynamic Fields based on Type -->
                <div v-if="form.constraint_type === 'teacher_availability'" class="mb-4">
                    <InputLabel for="teacher" value="Teacher" class="form-label text-muted small text-uppercase fw-bold" />
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-chalkboard-teacher text-muted"></i></span>
                        <select id="teacher" v-model="form.teacher_id" class="form-select">
                            <option value="" disabled>Select Teacher</option>
                            <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                {{ teacher.name }}
                            </option>
                        </select>
                    </div>
                    <div v-if="form.errors.teacher_id" class="text-danger small mt-1">{{ form.errors.teacher_id }}</div>
                </div>

                <div v-if="form.constraint_type === 'room_availability'" class="mb-4">
                    <InputLabel for="room" value="Room" class="form-label text-muted small text-uppercase fw-bold" />
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-door-open text-muted"></i></span>
                        <select id="room" v-model="form.room_id" class="form-select">
                            <option value="" disabled>Select Room</option>
                            <option v-for="room in rooms" :key="room.id" :value="room.id">
                                {{ room.room_name }}
                            </option>
                        </select>
                    </div>
                    <div v-if="form.errors.room_id" class="text-danger small mt-1">{{ form.errors.room_id }}</div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <InputLabel for="day" value="Day" class="form-label text-muted small text-uppercase fw-bold" />
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="far fa-calendar-alt text-muted"></i></span>
                            <select id="day" v-model="form.day_of_week" class="form-select">
                                <option value="">Any Day</option>
                                <option v-for="day in daysOfWeek" :key="day" :value="day">{{ day }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <InputLabel for="value" value="Status" class="form-label text-muted small text-uppercase fw-bold" />
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-thermometer-half text-muted"></i></span>
                            <select id="value" v-model="form.constraint_value" class="form-select">
                                <option value="unavailable">Unavailable</option>
                                <option value="preferred">Preferred</option>
                                <option value="not_preferred">Not Preferred</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Status Explanation -->
                <div class="alert py-2 px-3 mb-4 rounded-3 border-0 shadow-sm" :class="{
                    'alert-danger bg-danger bg-opacity-10': form.constraint_value === 'unavailable',
                    'alert-success bg-success bg-opacity-10': form.constraint_value === 'preferred',
                    'alert-warning bg-warning bg-opacity-10': form.constraint_value === 'not_preferred',
                    'alert-primary bg-primary bg-opacity-10': form.constraint_value === 'required'
                }">
                    <div class="d-flex align-items-start">
                        <i class="fas mt-1 me-2" :class="{
                            'fa-ban text-danger': form.constraint_value === 'unavailable',
                            'fa-check-circle text-success': form.constraint_value === 'preferred',
                            'fa-exclamation-triangle text-warning': form.constraint_value === 'not_preferred',
                            'fa-lock text-primary': form.constraint_value === 'required'
                        }"></i>
                        <small class="mb-0 text-dark">
                            <span v-if="form.constraint_value === 'unavailable'"><strong>Strict Rule:</strong> The generator will <strong>NEVER</strong> schedule here.</span>
                            <span v-if="form.constraint_value === 'preferred'"><strong>High Priority:</strong> The generator will <strong>TRY</strong> to schedule here first.</span>
                            <span v-if="form.constraint_value === 'not_preferred'"><strong>Low Priority:</strong> The generator will <strong>AVOID</strong> scheduling here if possible.</span>
                            <span v-if="form.constraint_value === 'required'"><strong>Mandatory:</strong> The generator <strong>MUST</strong> schedule here.</span>
                        </small>
                    </div>
                </div>

                <div class="mb-3">
                    <InputLabel for="notes" value="Notes (Optional)" class="form-label text-muted small text-uppercase fw-bold" />
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fas fa-sticky-note text-muted"></i></span>
                        <textarea id="notes" v-model="form.notes" class="form-control" rows="2" placeholder="Reason or details..."></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-light border" @click="showCreateModal = false">Cancel</button>
                    <button type="button" class="btn btn-primary px-4" @click="saveConstraint" :disabled="form.processing">
                        <span v-if="form.processing" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        {{ isEditing ? 'Update Constraint' : 'Save Constraint' }}
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-4">
                <div class="text-center mb-4">
                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                    <h4 class="fw-bold">Delete Constraint</h4>
                    <p class="text-muted">Are you sure you want to delete this constraint? This action cannot be undone.</p>
                </div>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-secondary" @click="showDeleteModal = false">Cancel</button>
                    <button type="button" class="btn btn-danger" @click="deleteConstraint">Delete Constraint</button>
                </div>
            </div>
        </Modal>

    </DefaultLayout>
</template>

<style scoped>
.hover-shadow:hover {
    transform: translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
.transition {
    transition: all 0.3s ease;
}
.nav-tabs .nav-link {
    color: #495057;
    border: none;
    border-bottom: 3px solid transparent;
}
.nav-tabs .nav-link.active {
    color: #0d6efd;
    border-bottom: 3px solid #0d6efd;
    background: transparent;
}
.nav-tabs .nav-link:hover:not(.active) {
    border-bottom: 3px solid #e9ecef;
}
.cursor-pointer {
    cursor: pointer;
}
</style>
