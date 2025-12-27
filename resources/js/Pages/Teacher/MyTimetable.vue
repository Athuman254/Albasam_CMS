<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    periods: Array,
    allocations: Object, // Grouped by day -> period_id
    daysOfWeek: Array,
    academicYears: Array,
    currentAcademicYearId: Number,
    hasActiveVersion: Boolean,
    teacherName: String,
    teacherId: Number,
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
        window.open(route('timetable.versions.export.teacher', {
            version: props.versionId,
            teacher: props.teacherId
        }), '_blank');
    }
};

const isBreak = (periodName) => {
    const breaks = ['SHORT BREAK', 'LUNCH BREAK', 'GAMES/CLUBS', 'GAMES', 'CLUBS', 'BREAK'];
    return breaks.includes(periodName.toUpperCase());
};
</script>

<template>
    <Head title="My Timetable" />

    <DefaultLayout>
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="d-flex justify-content-between align-items-center mb-4 print:hidden">
                <h4 class="fw-bold py-3 mb-0">
                    <span class="text-muted fw-light">Teacher /</span> My Timetable
                </h4>
                
                <div class="d-flex align-items-center gap-3">
                    <select 
                        :value="currentAcademicYearId" 
                        @change="onAcademicYearChange"
                        class="form-select form-select-sm"
                        style="width: auto;"
                    >
                        <option v-for="year in academicYears" :key="year.id" :value="year.id">
                            {{ year.display_name }}
                        </option>
                    </select>

                    <div class="dropdown">
                        <button class="btn btn-primary btn-icon rounded-pill dropdown-toggle hide-arrow" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bx bx-dots-vertical-rounded"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" @click="printTimetable">
                                    <i class="bx bx-printer me-2"></i> Print Timetable
                                </a>
                            </li>
                            <li v-if="hasActiveVersion">
                                <a class="dropdown-item" href="javascript:void(0);" @click="downloadPdf">
                                    <i class="bx bxs-file-pdf me-2"></i> Export PDF
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div v-if="!hasActiveVersion" class="card text-center py-5 print:hidden">
                <div class="card-body">
                    <div class="avatar avatar-xl bg-light-primary mx-auto mb-4">
                        <span class="avatar-initial rounded">
                            <i class="bx bx-calendar-x fs-1 text-primary"></i>
                        </span>
                    </div>
                    <h5 class="card-title">No Active Timetable</h5>
                    <p class="card-text text-muted">The timetable for this academic year has not been published yet.</p>
                </div>
            </div>

            <div v-else class="card shadow-none border-0 overflow-hidden print:m-0">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center bg-lighter">
                    <div>
                        <h5 class="mb-1 fw-bold text-primary">{{ teacherName }}'s Timetable</h5>
                        <small class="text-muted">Academic Year: {{ academicYears.find(y => y.id === currentAcademicYearId)?.name }}</small>
                    </div>
                    <div class="text-end d-none d-md-block">
                        <h4 class="mb-0 fw-bold">SkaasSchools</h4>
                    </div>
                </div>
                
                <div class="table-responsive text-nowrap">
                    <table class="table table-bordered table-sm mb-0 custom-timetable">
                        <thead>
                            <tr class="bg-light text-center">
                                <th style="width: 100px;">Time</th>
                                <th v-for="day in daysOfWeek" :key="day" class="p-2">
                                    {{ day }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="period in periods" :key="period.id">
                                <!-- Full widths Break Row -->
                                <template v-if="period.is_break && isBreak(period.period_name)">
                                    <td class="text-center bg-lighter py-3 border-end">
                                        <div class="fw-bold small">{{ period.start_time.substring(0, 5) }}</div>
                                        <div class="fw-bold small">{{ period.end_time.substring(0, 5) }}</div>
                                    </td>
                                    <td :colspan="daysOfWeek.length" class="text-center bg-light fw-bold py-3 text-uppercase tracking-widest bg-lighter text-dark">
                                        {{ period.period_name }}
                                    </td>
                                </template>

                                <!-- Regular Period Row -->
                                <template v-else>
                                    <td class="text-center bg-lighter border-end">
                                        <div class="fw-bold text-primary">{{ period.period_name.replace('Period', 'Lesson') }}</div>
                                        <div class="small text-muted">{{ period.start_time.substring(0, 5) }} - {{ period.end_time.substring(0, 5) }}</div>
                                    </td>
                                    <td v-for="day in daysOfWeek" :key="day" class="text-center p-3 align-middle position-relative transition-all hover-target">
                                        <div v-if="getAllocation(day, period.id)" class="allocation-content">
                                            <div class="fw-bold text-dark mb-1">{{ getAllocation(day, period.id).subject?.name || 'N/A' }}</div>
                                            <div class="badge bg-label-primary px-2 py-1">{{ getAllocation(day, period.id).class?.name || 'No Class' }}</div>
                                            <div v-if="getAllocation(day, period.id).room" class="text-muted small mt-1">
                                                <i class="bx bx-map-pin me-1"></i>{{ getAllocation(day, period.id).room.room_name }}
                                            </div>
                                        </div>
                                        <div v-else class="text-light">
                                            <i class="bx bx-minus"></i>
                                        </div>
                                    </td>
                                </template>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </DefaultLayout>
</template>

<style scoped>
.custom-timetable th {
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.custom-timetable td {
    border-color: #e6e8eb !important;
}

.bg-lighter {
    background-color: #f8f9fa !important;
}

.allocation-content {
    min-height: 45px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.hover-target:hover {
    background-color: #f1f3f5;
}

.tracking-widest {
    letter-spacing: 0.25em;
}

@media print {
    @page {
        size: landscape;
        margin: 5mm;
    }
    .container-p-y {
        padding: 0 !important;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .table-bordered {
        border-color: #333 !important;
    }
    .table-bordered th, .table-bordered td {
        border-color: #333 !important;
    }
    body {
        font-family: serif;
    }
}
</style>

