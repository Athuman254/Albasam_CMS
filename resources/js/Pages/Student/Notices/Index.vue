<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    notices: Object,
});
</script>

<template>
    <Head title="Announcements" />

    <StudentLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Announcements
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div v-if="notices.data.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center text-gray-500">
                    No announcements at this time.
                </div>

                <div v-for="notice in notices.data" :key="notice.id" class="bg-white overflow-hidden shadow-sm sm:rounded-lg transition hover:shadow-md">
                    <div class="p-6 border-l-4" :class="{
                        'border-indigo-500': notice.type === 'General',
                        'border-green-500': notice.type === 'Student',
                        'border-blue-500': notice.type === 'Teacher',
                        'border-gray-500': notice.type === 'Staff'
                    }">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                                    {{ notice.title }}
                                    <span v-if="notice.is_new" class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        New
                                    </span>
                                </h3>
                                <div class="mt-1 text-sm text-gray-500">
                                    {{ notice.published_at }} &bull; {{ notice.type }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 text-gray-700 whitespace-pre-line">
                            {{ notice.content }}
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="notices.data.length > 0" class="mt-6 flex justify-center">
                     <div class="flex flex-wrap -mb-1">
                        <template v-for="(link, key) in notices.links" :key="key">
                            <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded" v-html="link.label" />
                            <Link v-else class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:bg-white focus:border-indigo-500 focus:text-indigo-500" :class="{ 'bg-blue-700 text-white': link.active }" :href="link.url" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>
