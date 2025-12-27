<template>
    <Head title="School Calendar" />

    <div class="container-xxl flex-grow-1 container-p-y">
        <SchoolCalendar 
            :fetch-url="fetchUrl" 
            :can-manage="false" 
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

const fetchUrl = route('employee.calendar.events.fetch');
</script>
