<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
    periods: Object,
    academicYears: Array,
    currentAcademicYearId: Number,
    daysOfWeek: Array,
    periodDurations: Object,
});

const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const editingPeriod = ref(null);
const deletingPeriod = ref(null);

const form = useForm({
    academic_year_id: props.currentAcademicYearId,
    period_name: '',
    days_of_week: [],
    start_time: '',
    end_time: '',
    is_break: false,
    break_type: '',
    duration_minutes: props.periodDurations.default_lesson_duration,
});

const editForm = useForm({
    period_name: '',
    start_time: '',
    end_time: '',
    is_break: false,
    break_type: '',
    status: 'active',
});

const openCreateModal = () => {
    form.reset();
    form.academic_year_id = props.currentAcademicYearId;
    form.days_of_week = [...props.daysOfWeek]; // Default select all days
    showCreateModal.value = true;
};

const openEditModal = (period) => {
    editingPeriod.value = period;
    editForm.period_name = period.period_name;
    editForm.start_time = period.start_time.substring(0, 5); // HH:mm
    editForm.end_time = period.end_time.substring(0, 5);
    editForm.is_break = !!period.is_break;
    editForm.break_type = period.break_type;
    editForm.status = period.status;
    showEditModal.value = true;
};

const openDeleteModal = (period) => {
    deletingPeriod.value = period;
    showDeleteModal.value = true;
};

const createPeriod = () => {
    form.post(route('timetable.periods.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        },
    });
};

const updatePeriod = () => {
    editForm.put(route('timetable.periods.update', editingPeriod.value.id), {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
        },
    });
};

const deletePeriod = () => {
    router.delete(route('timetable.periods.destroy', deletingPeriod.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
        },
    });
};

const onAcademicYearChange = (e) => {
    router.get(route('timetable.periods.index'), { academic_year_id: e.target.value });
};

const formatTime = (time) => {
    return time.substring(0, 5);
};
</script>

<template>
    <Head title="Timetable Periods" />

    <DefaultLayout>
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light">Timetable /</span> Periods
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
                <button @click="openCreateModal" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> Add Period
                </button>
            </div>
        </div>

        <!-- Periods Content -->
        <div class="card">
            <div class="card-body">
                <div v-if="Object.keys(periods).length === 0" class="text-center py-5 text-muted">
                    <i class="fas fa-clock fa-3x mb-3 opacity-50"></i>
                    <p class="mb-0">No periods configured for this academic year.</p>
                </div>

                <div v-else class="row g-4">
                    <div v-for="day in daysOfWeek" :key="day" class="col-md-6 col-lg-4">
                        <div class="card border">
                            <div class="card-header bg-white border-bottom">
                                <h5 class="mb-0 text-dark">{{ day }}</h5>
                            </div>
                            <div class="card-body">
                                <div v-if="!periods[day]" class="text-muted small fst-italic">
                                    No periods
                                </div>
                                
                                <div v-else class="d-flex flex-column gap-2">
                                    <div 
                                        v-for="period in periods[day]" 
                                        :key="period.id"
                                        class="period-card p-3 rounded position-relative border mb-2 shadow-sm"
                                        :class="period.is_break ? 'border-warning' : 'border-success'"
                                        style="border-left-width: 4px !important;"
                                    >
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <div class="fw-bold text-dark">{{ period.period_name }}</div>
                                                <div class="small text-muted mt-1">
                                                    <i class="far fa-clock me-1"></i>
                                                    {{ formatTime(period.start_time) }} - {{ formatTime(period.end_time) }}
                                                    <span class="ms-1 text-secondary">({{ period.duration_minutes }} min)</span>
                                                </div>
                                                <div v-if="period.is_break" class="badge bg-warning text-dark mt-2">
                                                    {{ period.break_type }}
                                                </div>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn align-text-top py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="icon-base bx bx-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                                    <li>
                                                        <a class="dropdown-item py-2 px-3" href="javascript:void(0);" @click.prevent="openEditModal(period)">
                                                            <i class="fas fa-edit me-2 text-primary"></i> Edit Period
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider my-1">
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item py-2 px-3 text-danger" href="javascript:void(0);" @click.prevent="openDeleteModal(period)">
                                                            <i class="fas fa-trash me-2"></i> Delete Period
                                                        </a>
                                                    </li>
                                                </ul>
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
        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Add New Period</h2>
                
                <div class="space-y-4">
                    <div>
                        <InputLabel for="period_name" value="Period Name" />
                        <TextInput id="period_name" v-model="form.period_name" type="text" class="mt-1 block w-full" placeholder="e.g. Period 1, Morning Break" />
                        <div v-if="form.errors.period_name" class="text-red-500 text-sm mt-1">{{ form.errors.period_name }}</div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="start_time" value="Start Time" />
                            <TextInput id="start_time" v-model="form.start_time" type="time" class="mt-1 block w-full" />
                            <div v-if="form.errors.start_time" class="text-red-500 text-sm mt-1">{{ form.errors.start_time }}</div>
                        </div>
                        <div>
                            <InputLabel for="end_time" value="End Time (Optional)" />
                            <TextInput id="end_time" v-model="form.end_time" type="time" class="mt-1 block w-full" />
                            <p class="text-xs text-gray-500 mt-1">Leave blank to auto-calculate based on duration.</p>
                        </div>
                    </div>

                    <div>
                        <InputLabel for="duration" value="Duration (Minutes)" />
                        <TextInput id="duration" v-model="form.duration_minutes" type="number" class="mt-1 block w-full" />
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex items-center">
                            <Checkbox id="is_break" v-model:checked="form.is_break" />
                            <InputLabel for="is_break" value="Is this a break?" class="ml-2" />
                        </div>
                    </div>

                    <div v-if="form.is_break">
                        <InputLabel for="break_type" value="Break Type" />
                        <select id="break_type" v-model="form.break_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="Short Break">Short Break</option>
                            <option value="Lunch">Lunch</option>
                            <option value="Assembly">Assembly</option>
                            <option value="Games">Games</option>
                        </select>
                    </div>

                    <div>
                        <InputLabel value="Apply to Days" class="mb-2" />
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            <div v-for="day in daysOfWeek" :key="day" class="flex items-center">
                                <input type="checkbox" :id="'day_'+day" :value="day" v-model="form.days_of_week" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <label :for="'day_'+day" class="ml-2 text-sm text-gray-600">{{ day }}</label>
                            </div>
                        </div>
                        <div v-if="form.errors.days_of_week" class="text-red-500 text-sm mt-1">{{ form.errors.days_of_week }}</div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showCreateModal = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="createPeriod" :disabled="form.processing">Create Period</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Edit Modal -->
        <Modal :show="showEditModal" @close="showEditModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Period</h2>
                
                <div class="space-y-4">
                    <div>
                        <InputLabel for="edit_period_name" value="Period Name" />
                        <TextInput id="edit_period_name" v-model="editForm.period_name" type="text" class="mt-1 block w-full" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="edit_start_time" value="Start Time" />
                            <TextInput id="edit_start_time" v-model="editForm.start_time" type="time" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel for="edit_end_time" value="End Time" />
                            <TextInput id="edit_end_time" v-model="editForm.end_time" type="time" class="mt-1 block w-full" />
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="flex items-center">
                            <Checkbox id="edit_is_break" v-model:checked="editForm.is_break" />
                            <InputLabel for="edit_is_break" value="Is this a break?" class="ml-2" />
                        </div>
                    </div>

                    <div v-if="editForm.is_break">
                        <InputLabel for="edit_break_type" value="Break Type" />
                        <select id="edit_break_type" v-model="editForm.break_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="Short Break">Short Break</option>
                            <option value="Lunch">Lunch</option>
                            <option value="Assembly">Assembly</option>
                            <option value="Games">Games</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showEditModal = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="updatePeriod" :disabled="editForm.processing">Update Period</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Delete Period</h2>
                <p class="text-gray-600 mb-6">
                    Are you sure you want to delete <strong>{{ deletingPeriod?.period_name }}</strong>? This action cannot be undone.
                </p>
                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="showDeleteModal = false">Cancel</SecondaryButton>
                    <DangerButton @click="deletePeriod">Delete Period</DangerButton>
                </div>
            </div>
        </Modal>

    </DefaultLayout>
</template>
