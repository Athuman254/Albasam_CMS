<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    classes: Array,
    currentClassId: Number,
    periods: Array,
    allocations: Object, // Grouped by day -> period_id
    daysOfWeek: Array,
    academicYears: Array,
    currentAcademicYearId: Number,
    hasActiveVersion: Boolean,
});

const onClassChange = (e) => {
    router.get(route('timetable.view.class', e.target.value), { academic_year_id: props.currentAcademicYearId });
};

const onAcademicYearChange = (e) => {
    router.get(route('timetable.view.class', props.currentClassId), { academic_year_id: e.target.value });
};

const getAllocation = (day, periodId) => {
    if (!props.allocations[day]) return null;
    return props.allocations[day][periodId];
};

const printTimetable = () => {
    window.print();
};
</script>

<template>
    <Head title="Class Timetable" />

    <DefaultLayout>
        <template #header>
            <div class="flex justify-between items-center print:hidden">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Class Timetable</h2>
                <div class="flex items-center gap-4">
                    <select 
                        :value="currentAcademicYearId" 
                        @change="onAcademicYearChange"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    >
                        <option v-for="year in academicYears" :key="year.id" :value="year.id">
                            {{ year.display_name }}
                        </option>
                    </select>
                    
                    <select 
                        :value="currentClassId" 
                        @change="onClassChange"
                        class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm min-w-[200px]"
                    >
                        <option v-for="cls in classes" :key="cls.id" :value="cls.id">
                            {{ cls.name }}
                        </option>
                    </select>

                    <button @click="printTimetable" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        <i class="fas fa-print mr-2"></i> Print
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12 print:py-0 print:m-0">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:w-full print:max-w-none">
                
                <div v-if="!hasActiveVersion" class="text-center py-20 bg-white rounded-lg shadow print:hidden">
                    <i class="fas fa-calendar-times text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-medium text-gray-900">No Active Timetable</h3>
                    <p class="text-gray-500 mt-2">The timetable for this academic year has not been published yet.</p>
                </div>

                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 print:shadow-none">
                    <div class="text-center mb-6">
                        <h1 class="text-2xl font-bold text-gray-900">
                            {{ classes.find(c => c.id === currentClassId)?.name }} Timetable
                        </h1>
                        <p class="text-gray-500">{{ academicYears.find(y => y.id === currentAcademicYearId)?.name }}</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse border border-gray-300">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border border-gray-300 p-2 w-20">Time</th>
                                    <th v-for="day in daysOfWeek" :key="day" class="border border-gray-300 p-2 min-w-[150px]">
                                        {{ day }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="period in periods" :key="period.id">
                                    <td class="border border-gray-300 p-2 text-center text-sm font-medium bg-gray-50">
                                        {{ period.start_time.substring(0, 5) }}<br>
                                        -<br>
                                        {{ period.end_time.substring(0, 5) }}
                                    </td>
                                    
                                    <!-- Break Period Row -->
                                    <template v-if="period.is_break">
                                        <td :colspan="daysOfWeek.length" class="border border-gray-300 p-2 text-center bg-yellow-50 text-yellow-800 font-bold uppercase tracking-widest text-sm">
                                            {{ period.period_name }}
                                        </td>
                                    </template>

                                    <!-- Regular Period Row -->
                                    <template v-else>
                                        <td v-for="day in daysOfWeek" :key="day" class="border border-gray-300 p-2 text-center relative hover:bg-gray-50 transition">
                                            <div v-if="getAllocation(day, period.id)" class="h-full w-full">
                                                <div class="font-bold text-indigo-700">
                                                    {{ getAllocation(day, period.id).subject.subject_name }}
                                                </div>
                                                <div class="text-xs text-gray-600 mt-1">
                                                    {{ getAllocation(day, period.id).teacher.full_name }}
                                                </div>
                                                <div v-if="getAllocation(day, period.id).room" class="text-xs text-gray-400 mt-1">
                                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                                    {{ getAllocation(day, period.id).room.room_name }}
                                                </div>
                                            </div>
                                            <div v-else class="text-gray-300 text-xs italic">
                                                -
                                            </div>
                                        </td>
                                    </template>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </DefaultLayout>
</template>

<style>
@media print {
    @page {
        size: landscape;
    }
    body {
        background: white;
    }
    nav, header {
        display: none !important;
    }
}
</style>
