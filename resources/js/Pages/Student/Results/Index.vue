<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    exams: Array,
});
</script>

<template>
    <Head title="My Results" />

    <StudentLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                My Results
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div v-if="exams.length === 0" class="text-center py-12">
                            <div class="bg-gray-50 rounded-full h-20 w-20 flex items-center justify-center mx-auto mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">No Results Available</h3>
                            <p class="text-gray-500 mt-1">Your exam results will appear here once they are published.</p>
                        </div>

                        <div v-else class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                            <div v-for="exam in exams" :key="exam.id" class="border rounded-lg overflow-hidden hover:shadow-md transition-shadow duration-200">
                                <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100">
                                    <h3 class="text-lg font-semibold text-indigo-900">{{ exam.name }}</h3>
                                    <p class="text-sm text-indigo-600">{{ exam.academic_year }} - {{ exam.term }}</p>
                                </div>
                                <div class="p-6">
                                    <div class="flex justify-between items-center mb-4">
                                        <div>
                                            <p class="text-xs text-gray-500 uppercase tracking-wide">Average Grade</p>
                                            <p class="text-2xl font-bold text-gray-900">{{ exam.average_grade }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs text-gray-500 uppercase tracking-wide">Percentage</p>
                                            <p class="text-2xl font-bold text-gray-900">{{ exam.percentage }}%</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-between text-sm text-gray-500 mb-6">
                                        <span>{{ exam.subjects_count }} Subjects</span>
                                        <span>{{ exam.date }}</span>
                                    </div>

                                    <Link :href="route('student.results.show', exam.id)" class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded transition-colors duration-200">
                                        View Details
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>
