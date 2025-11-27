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
    form.reset();
    form.academic_year_id = props.currentAcademicYearId;
    form.constraint_type = 'teacher_availability'; // Default
    showCreateModal.value = true;
};

const createConstraint = () => {
    form.post(route('timetable.constraints.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        },
    });
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
            desc = `${constraint.teacher?.full_name} is ${constraint.constraint_value.toUpperCase()} on ${constraint.day_of_week || 'Any Day'}`;
            break;
        case 'room_availability':
            desc = `${constraint.room?.room_name} is ${constraint.constraint_value.toUpperCase()} on ${constraint.day_of_week || 'Any Day'}`;
            break;
        default:
            desc = `${props.constraintTypes[constraint.constraint_type]} constraint`;
    }
    return desc;
};
</script>

<template>
    <Head title="Timetable Constraints" />

    <DefaultLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Scheduling Constraints</h2>
                <div class="flex items-center gap-4">
                    <select 
                        :value="currentAcademicYearId" 
                        @change="onAcademicYearChange"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    >
                        <option v-for="year in academicYears" :key="year.id" :value="year.id">
                            {{ year.name }}
                        </option>
                    </select>
                    <PrimaryButton @click="openCreateModal">
                        <i class="fas fa-plus mr-2"></i> Add Constraint
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="Object.keys(constraints).length === 0" class="text-center py-10 bg-white rounded-lg shadow">
                    <p class="text-gray-500">No constraints defined. The generator will assume full availability.</p>
                </div>

                <div v-else class="space-y-6">
                    <div v-for="(typeConstraints, type) in constraints" :key="type" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-bold text-lg mb-4 text-indigo-700 border-b pb-2 flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            {{ constraintTypes[type] }}
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div 
                                v-for="constraint in typeConstraints" 
                                :key="constraint.id"
                                class="border rounded p-3 flex justify-between items-start bg-gray-50 hover:bg-white transition"
                            >
                                <div>
                                    <div class="font-medium text-gray-800">{{ getConstraintDescription(constraint) }}</div>
                                    <div class="text-xs text-gray-500 mt-1" v-if="constraint.notes">
                                        Note: {{ constraint.notes }}
                                    </div>
                                </div>
                                <button @click="confirmDelete(constraint)" class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Create Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Add Constraint</h2>
                
                <div class="space-y-4">
                    <div>
                        <InputLabel for="type" value="Constraint Type" />
                        <select id="type" v-model="form.constraint_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option v-for="(label, key) in constraintTypes" :key="key" :value="key">
                                {{ label }}
                            </option>
                        </select>
                    </div>

                    <!-- Dynamic Fields based on Type -->
                    <div v-if="form.constraint_type === 'teacher_availability'">
                        <InputLabel for="teacher" value="Teacher" />
                        <select id="teacher" v-model="form.teacher_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="" disabled>Select Teacher</option>
                            <option v-for="teacher in teachers" :key="teacher.id" :value="teacher.id">
                                {{ teacher.full_name }}
                            </option>
                        </select>
                    </div>

                    <div v-if="form.constraint_type === 'room_availability'">
                        <InputLabel for="room" value="Room" />
                        <select id="room" v-model="form.room_id" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                            <option value="" disabled>Select Room</option>
                            <option v-for="room in rooms" :key="room.id" :value="room.id">
                                {{ room.room_name }}
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="day" value="Day (Optional)" />
                            <select id="day" v-model="form.day_of_week" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Any Day</option>
                                <option v-for="day in daysOfWeek" :key="day" :value="day">{{ day }}</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="value" value="Status" />
                            <select id="value" v-model="form.constraint_value" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="unavailable">Unavailable</option>
                                <option value="preferred">Preferred</option>
                                <option value="not_preferred">Not Preferred</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <InputLabel for="notes" value="Notes" />
                        <TextInput id="notes" v-model="form.notes" type="text" class="mt-1 block w-full" placeholder="Reason or details..." />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showCreateModal = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="createConstraint" :disabled="form.processing">Add Constraint</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Delete Modal -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Delete Constraint</h2>
                <p class="text-gray-600 mb-6">
                    Are you sure you want to delete this constraint?
                </p>
                <div class="flex justify-end gap-3">
                    <SecondaryButton @click="showDeleteModal = false">Cancel</SecondaryButton>
                    <DangerButton @click="deleteConstraint">Delete</DangerButton>
                </div>
            </div>
        </Modal>

    </DefaultLayout>
</template>
