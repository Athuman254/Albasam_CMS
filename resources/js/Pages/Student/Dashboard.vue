<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import GreetingCard from '@/Components/GreetingCard.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    student: Object,
    stats: Object,
    notices: Array,
});
</script>

<template>
    <Head title="Student Dashboard" />

    <StudentLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Greeting Card -->
                <GreetingCard 
                    :user-name="student.first_name" 
                    user-type="student" 
                />

                <!-- Stats Grid -->
                <div class="mb-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Attendance -->
                    <div class="overflow-hidden rounded-lg bg-white p-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="rounded-full bg-blue-100 p-3 text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Attendance</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.attendance_percentage }}%</p>
                            </div>
                        </div>
                    </div>

                    <!-- Current Grade -->
                    <div class="overflow-hidden rounded-lg bg-white p-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="rounded-full bg-green-100 p-3 text-green-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Current Grade</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.current_grade }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Fee Balance -->
                    <div class="overflow-hidden rounded-lg bg-white p-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="rounded-full bg-red-100 p-3 text-red-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Fee Balance</p>
                                <p class="text-2xl font-semibold text-gray-900">Ksh {{ stats.fee_balance.toLocaleString() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Exams -->
                    <div class="overflow-hidden rounded-lg bg-white p-6 shadow-sm">
                        <div class="flex items-center">
                            <div class="rounded-full bg-purple-100 p-3 text-purple-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Upcoming Exams</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ stats.upcoming_exams }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                            <h4 class="text-lg font-medium text-gray-900">Quick Actions</h4>
                        </div>
                        <div class="p-6">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <Link :href="route('student.results.index')" class="flex items-center rounded-lg border border-gray-200 p-4 transition hover:bg-indigo-50 hover:shadow-md hover-shake cursor-pointer">
                                    <div class="rounded-full bg-indigo-100 p-2 text-indigo-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <span class="ml-3 font-medium text-gray-900">View Results</span>
                                </Link>

                                <Link :href="route('student.fees.index')" class="flex items-center rounded-lg border border-gray-200 p-4 transition hover:bg-green-50 hover:shadow-md hover-shake cursor-pointer">
                                    <div class="rounded-full bg-green-100 p-2 text-green-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <span class="ml-3 font-medium text-gray-900">Fee Statement</span>
                                </Link>

                                <Link :href="route('student.attendance.index')" class="flex items-center rounded-lg border border-gray-200 p-4 transition hover:bg-yellow-50 hover:shadow-md hover-shake cursor-pointer">
                                    <div class="rounded-full bg-yellow-100 p-2 text-yellow-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <span class="ml-3 font-medium text-gray-900">My Attendance</span>
                                </Link>

                                <Link :href="route('student.profile.edit')" class="flex items-center rounded-lg border border-gray-200 p-4 transition hover:bg-pink-50 hover:shadow-md hover-shake cursor-pointer">
                                    <div class="rounded-full bg-pink-100 p-2 text-pink-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <span class="ml-3 font-medium text-gray-900">My Profile</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Announcements -->
                    <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
                            <h4 class="text-lg font-medium text-gray-900">Recent Announcements</h4>
                        </div>
                        <div class="p-6">
                            <ul v-if="notices.length > 0" class="space-y-4">
                                <li v-for="notice in notices" :key="notice.id" class="border-b border-gray-100 pb-4 last:border-0 last:pb-0">
                                    <p class="text-sm text-gray-500">{{ notice.published_at }}</p>
                                    <p class="font-medium text-gray-900">{{ notice.title }}</p>
                                </li>
                            </ul>
                            <div v-else class="text-center text-gray-500 py-4">
                                No recent announcements.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>

<style scoped>
@keyframes shake {
  0% { transform: translate(1px, 1px) rotate(0deg); }
  10% { transform: translate(-1px, -2px) rotate(-1deg); }
  20% { transform: translate(-3px, 0px) rotate(1deg); }
  30% { transform: translate(3px, 2px) rotate(0deg); }
  40% { transform: translate(1px, -1px) rotate(1deg); }
  50% { transform: translate(-1px, 2px) rotate(-1deg); }
  60% { transform: translate(-3px, 1px) rotate(0deg); }
  70% { transform: translate(3px, 1px) rotate(-1deg); }
  80% { transform: translate(-1px, -1px) rotate(1deg); }
  90% { transform: translate(1px, 2px) rotate(0deg); }
  100% { transform: translate(1px, -2px) rotate(-1deg); }
}

.hover-shake:hover {
  animation: shake 0.5s;
  animation-iteration-count: 1;
}
</style>
