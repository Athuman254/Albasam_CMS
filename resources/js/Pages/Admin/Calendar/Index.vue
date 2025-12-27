<template>
    <Head title="School Calendar" />

    <div class="container-xxl flex-grow-1 container-p-y">
        <SchoolCalendar 
            :classes="classes" 
            :fetch-url="fetchUrl" 
            :can-manage="true" 
        />
    </div>
</template>

<script setup>
import { Head } from '@inertiajs/vue3';
import SchoolCalendar from '@/Components/Calendar/SchoolCalendar.vue';
import DefaultLayout from '@/Layouts/DefaultLayout.vue';

defineOptions({
    layout: DefaultLayout
});

const props = defineProps({
    classes: Array,
});

const fetchUrl = route('admin.calendar.events.fetch');
</script>
