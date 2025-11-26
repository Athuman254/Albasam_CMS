<template>
  <DefaultLayout>
    <Head title="Fee Reports Overview" />
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Fee Reports Overview</h4>
                    </div>
                    <div class="card-body">
                        <p>Reports Overview page is loading...</p>
                        <p>Total Collected: KSh {{ overview?.total_collected?.toLocaleString() || 0 }}</p>
                        <p>Outstanding Balance: KSh {{ overview?.total_balance?.toLocaleString() || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    overview: Object,
    recentPayments: Array,
    paymentMethods: Object,
    monthlyData: Object
});
</script>