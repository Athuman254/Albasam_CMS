<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    attendance: Array,
    stats: Object,
});
</script>

<template>
    <Head title="My Attendance" />

    <StudentLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                My Attendance
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500 truncate">Attendance Rate</div>
                            <div class="mt-1 text-3xl font-semibold" :class="{
                                'text-green-600': stats.percentage >= 90,
                                'text-yellow-600': stats.percentage >= 75 && stats.percentage < 90,
                                'text-red-600': stats.percentage < 75
                            }">
                                {{ stats.percentage }}%
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500 truncate">Present Days</div>
                            <div class="mt-1 text-3xl font-semibold text-green-600">
                                {{ stats.present }}
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500 truncate">Absent Days</div>
                            <div class="mt-1 text-3xl font-semibold text-red-600">
                                {{ stats.absent }}
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500 truncate">Late Days</div>
                            <div class="mt-1 text-3xl font-semibold text-yellow-600">
                                {{ stats.late }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Attendance History</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="attendance.length === 0">
                                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No attendance records found.
                                        </td>
                                    </tr>
                                    <tr v-for="record in attendance" :key="record.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ record.formatted_date }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ record.day }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="{
                                                    'bg-green-100 text-green-800': record.status === 'present',
                                                    'bg-red-100 text-red-800': record.status === 'absent',
                                                    'bg-yellow-100 text-yellow-800': record.status === 'late',
                                                    'bg-gray-100 text-gray-800': record.status === 'excused'
                                                }">
                                                {{ record.status.charAt(0).toUpperCase() + record.status.slice(1) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ record.remarks || '-' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>
