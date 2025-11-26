<template>
  <DefaultLayout>
    <Head title="Fee Reports" />
    
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h4 class="card-title">KSh {{ overview?.total_collected?.toLocaleString() || 0 }}</h4>
                        <p class="card-text">Total Collected</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h4 class="card-title">KSh {{ overview?.total_expected?.toLocaleString() || 0 }}</h4>
                        <p class="card-text">Total Expected</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h4 class="card-title">KSh {{ overview?.total_balance?.toLocaleString() || 0 }}</h4>
                        <p class="card-text">Outstanding Balance</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h4 class="card-title">{{ overview?.collection_rate?.toFixed(1) || 0 }}%</h4>
                        <p class="card-text">Collection Rate</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods Chart -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Payments by Method</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="paymentMethodsChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Monthly Collection</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyCollectionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Payments -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Recent Payments</h5>
                    </div>
                    <div class="card-body">
                        <div v-if="recentPayments?.length === 0" class="text-center py-4">
                            <p class="text-muted">No recent payments found.</p>
                        </div>
                        
                        <div v-else class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Student</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Reference</th>
                                        <th>Verified By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="payment in recentPayments" :key="payment.id">
                                        <td>{{ payment.payment_date }}</td>
                                        <td>{{ payment.student?.full_name }}</td>
                                        <td>KSh {{ payment.amount?.toLocaleString() }}</td>
                                        <td>{{ payment.payment_method?.toUpperCase() }}</td>
                                        <td>{{ payment.reference_number }}</td>
                                        <td>{{ payment.verified_by?.name || 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </DefaultLayout>
</template>

<script>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

export default {
    layout: DefaultLayout,
    components: { Head },
    props: {
        overview: Object,
        recentPayments: Array,
        paymentMethods: Object,
        monthlyData: Object
    },
    mounted() {
        this.renderCharts();
    },
    methods: {
        renderCharts() {
            // Payment Methods Chart
            const methodsCtx = document.getElementById('paymentMethodsChart')?.getContext('2d');
            if (methodsCtx && this.paymentMethods) {
                new Chart(methodsCtx, {
                    type: 'pie',
                    data: {
                        labels: Object.keys(this.paymentMethods),
                        datasets: [{
                            data: Object.values(this.paymentMethods),
                            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40']
                        }]
                    }
                });
            }

            // Monthly Collection Chart
            const monthlyCtx = document.getElementById('monthlyCollectionChart')?.getContext('2d');
            if (monthlyCtx && this.monthlyData) {
                new Chart(monthlyCtx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys(this.monthlyData),
                        datasets: [{
                            label: 'Collections',
                            data: Object.values(this.monthlyData),
                            backgroundColor: '#4CAF50'
                        }]
                    }
                });
            }
        }
    }
}
</script>