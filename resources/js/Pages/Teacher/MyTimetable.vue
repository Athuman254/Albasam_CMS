<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    periods: Array,
    allocations: Object, // Grouped by day -> period_id
    daysOfWeek: Array,
    academicYears: Array,
    currentAcademicYearId: Number,
    hasActiveVersion: Boolean,
    teacherName: String,
    versionId: Number,
});

const onAcademicYearChange = (e) => {
    router.get(route('teacher.timetable'), { academic_year_id: e.target.value });
};

const getAllocation = (day, periodId) => {
    if (!props.allocations[day]) return null;
    return props.allocations[day][periodId];
};

const printTimetable = () => {
    window.print();
};

const downloadPdf = () => {
    if (props.versionId) {
        window.open(route('timetable.generate.export-teacher-pdf', {
            version: props.versionId,
            teacher: props.teacherName
        }), '_blank');
    }
};
</script>

<template>
    <Head title="My Timetable" />

    <DefaultLayout>
        <template #header>
            <div class="flex justify-between items-center print:hidden">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Timetable</h2>
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

                    <button @click="printTimetable" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                        <i class="fas fa-print mr-2"></i> Print
                    </button>
                    
                    <button v-if="hasActiveVersion" @click="downloadPdf" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                        <i class="fas fa-file-pdf mr-2"></i> Download PDF
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
                    <div class="text-center mb-6 border-b pb-4">
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">
                            Albasam Comprehensive School
                        </h1>
                        <h2 class="text-xl font-semibold text-indigo-600 mb-1">
                            Teacher Timetable: {{ teacherName }}
                        </h2>
                        <p class="text-sm text-gray-600">
                            Academic Year: {{ academicYears.find(y => y.id === currentAcademicYearId)?.name }}
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse border border-gray-300">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border border-gray-300 p-2 w-24">Time</th>
                                    <th v-for="day in daysOfWeek" :key="day" class="border border-gray-300 p-2 min-w-[140px]">
                                        {{ day }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(period, index) in periods" :key="period.id">
                                    <!-- Break Period Row -->
                                    <template v-if="period.is_break">
                                        <td class="border border-gray-300 p-2 text-center text-xs font-medium bg-gray-50">
                                            {{ period.start_time.substring(0, 5) }}<br>
                                            -<br>
                                            {{ period.end_time.substring(0, 5) }}
                                        </td>
                                        <td :colspan="daysOfWeek.length" class="border border-gray-300 p-3 text-center bg-yellow-50 text-yellow-800 font-bold uppercase tracking-widest text-sm">
                                            {{ period.period_name }}
                                        </td>
                                    </template>

                                    <!-- Regular Period Row -->
                                    <template v-else>
                                        <td class="border border-gray-300 p-2 text-center text-sm font-medium bg-gray-50">
                                            <div class="font-bold">{{ period.period_name.replace('Period', 'Lesson') }}</div>
                                            <div class="text-xs text-gray-600 mt-1">
                                                {{ period.start_time.substring(0, 5) }} - {{ period.end_time.substring(0, 5) }}
                                            </div>
                                        </td>
                                        <td v-for="day in daysOfWeek" :key="day" class="border border-gray-300 p-3 text-center relative hover:bg-gray-50 transition">
                                            <div v-if="getAllocation(day, period.id)" class="h-full w-full">
                                                <div class="font-bold text-gray-900 text-sm">
                                                    {{ getAllocation(day, period.id).subject?.subject_name || getAllocation(day, period.id).subject?.name || 'N/A' }}
                                                </div>
                                                <div class="text-xs text-gray-600 mt-1">
                                                    {{ getAllocation(day, period.id).class?.name || 'No Class' }}
                                                </div>
                                                <div v-if="getAllocation(day, period.id).room" class="text-xs text-gray-400 mt-1">
                                                    {{ getAllocation(day, period.id).room.room_name }}
                                                </div>
                                            </div>
                                            <div v-else class="text-gray-300 text-sm">
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

