<script setup>
import { computed } from 'vue';

const props = defineProps({
    workload: Object,
    limits: Object,
});

const percentage = computed(() => {
    if (!props.workload || !props.limits) return 0;
    return Math.min(100, Math.round((props.workload.total_hours_per_week / props.limits.max_hours_per_week) * 100));
});

const colorClass = computed(() => {
    if (percentage.value >= 100) return 'bg-red-500';
    if (percentage.value >= 80) return 'bg-yellow-500';
    return 'bg-green-500';
});

const textClass = computed(() => {
    if (percentage.value >= 100) return 'text-red-600 font-bold';
    if (percentage.value >= 80) return 'text-yellow-600 font-medium';
    return 'text-green-600';
});
</script>

<template>
    <div class="w-full">
        <div class="flex justify-between text-xs mb-1">
            <span>Workload</span>
            <span :class="textClass">{{ workload.total_hours_per_week }} / {{ limits.max_hours_per_week }} hrs</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div class="h-2.5 rounded-full transition-all duration-500" :class="colorClass" :style="{ width: percentage + '%' }"></div>
        </div>
        <div class="flex justify-between text-[10px] text-gray-400 mt-1">
            <span>{{ workload.total_classes }} classes</span>
            <span>{{ workload.total_subjects }} subjects</span>
        </div>
    </div>
</template>
