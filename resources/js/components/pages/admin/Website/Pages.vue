<script>
import { Link } from '@inertiajs/vue3';

export default {
    data() {
        return {
            pages: [
                {
                    id: 1,
                    title: 'Home',
                    description: 'Manage home page content',
                    url: 'pages/homepage',
                    lastUpdated: '2024-03-28',
                },
                {
                    id: 2,
                    title: 'Services',
                    description: 'Manage service offered',
                    url: 'pages/services',
                    lastUpdated: '2024-03-25',
                },
                {
                    id: 3,
                    title: 'Blog',
                    description: 'Manage school blogs',
                    url: 'pages/blogs',
                    lastUpdated: '2024-03-27',
                },
            ],
        };
    }
    ,
    methods: {
        formatDate(date)
        {
            const options = {year: 'numeric', month: 'long', day: 'numeric'};
            return new Date(date).toLocaleDateString(undefined, options);
        }
        ,
    }
    ,
};

</script>

<template>
    <div class="row">
        <div class="col-xxl-12">
            <!-- <h1>Website Pages</h1> -->

            <div class="max-w-4xl mx-auto p-6 card">
                <h2 class="text-3xl font-bold mb-6">Website Pages</h2>
                <div class="grid gap-4">
                    <div
                        v-for="page in pages"
                        :key="page.id"
                        class="transition-all hover:shadow-lg"
                    >
                        <div class="p-0 border rounded-lg overflow-hidden">
                            <Link
                                :href="page.url"
                                class="block p-6 hover:bg-gray-50"
                            >
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h2 class="text-xl font-semibold text-blue-600 mb-2">
                                            {{ page.title }}
                                        </h2>
                                        <p class="text-gray-600 mb-2">{{ page.description }}</p>
                                        <p class="text-sm text-gray-400">
                                            Last updated: {{ formatDate(page.lastUpdated) }}
                                        </p>
                                    </div>
                                    <ChevronRightIcon class="text-gray-400"/>
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
