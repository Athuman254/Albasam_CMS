<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3'; // Added usePage
import { ref, reactive } from 'vue';
import axios from 'axios';

const props = defineProps({
    summary: Object,
    fees: Array,
    payments: Array,
});

const showPaymentModal = ref(false);
const processing = ref(false);
const paymentMessage = ref('');
const paymentSuccess = ref(false);

// Use current user phone if available, or empty
const user = usePage().props.auth.user;
const paymentForm = reactive({
    phone: '', // Could pre-fill from user profile
    amount: '',
    account_reference: user ? user.admission_number : 'FeePayment' // Should be dynamic
});

const initiatePayment = async () => {
    if (!paymentForm.phone || !paymentForm.amount) {
        paymentMessage.value = 'Please enter both phone number and amount.';
        paymentSuccess.value = false;
        return;
    }

    processing.value = true;
    paymentMessage.value = '';

    try {
        const response = await axios.post(route('student.mpesa.initiate'), {
            phone_number: paymentForm.phone,
            amount: paymentForm.amount,
            account_reference: paymentForm.account_reference 
        });

        if (response.data.success) {
            paymentSuccess.value = true;
            paymentMessage.value = 'STK Push initiated! Please check your phone to complete the payment.';
            
            // Optionally clear form or close modal after delay
            setTimeout(() => {
                showPaymentModal.value = false;
                paymentMessage.value = '';
                paymentForm.phone = '';
                paymentForm.amount = '';
            }, 5000);
        } else {
            paymentSuccess.value = false;
            paymentMessage.value = response.data.message || 'Failed to initiate payment.';
        }
    } catch (error) {
        console.error(error);
        paymentSuccess.value = false;
        paymentMessage.value = error.response?.data?.message || 'An error occurred. Please try again.';
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <Head title="Fee Statement" />

    <StudentLayout>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Page Header with Buttons -->
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-900">
                        Fee Statement
                    </h2>
                    <div class="flex space-x-2">
                        <button @click="showPaymentModal = true" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Pay Now
                        </button>
                        <a :href="route('student.fees.download')" target="_blank" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Download Statement
                        </a>
                    </div>
                </div>
                
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500 truncate">Total Billed</div>
                            <div class="mt-1 text-3xl font-semibold text-gray-900">
                                KSh {{ summary.total_fees.toLocaleString() }}
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500 truncate">Total Paid</div>
                            <div class="mt-1 text-3xl font-semibold text-gray-900">
                                KSh {{ summary.total_paid.toLocaleString() }}
                            </div>
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="text-sm font-medium text-gray-500 truncate">Outstanding Balance</div>
                            <div class="mt-1 text-3xl font-semibold text-gray-900">
                                KSh {{ summary.balance.toLocaleString() }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fee Breakdown -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Fee Breakdown</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date/Term</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="fees.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No fee records found.
                                        </td>
                                    </tr>
                                    <tr v-for="fee in fees" :key="fee.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div>{{ fee.academic_year }} - Term {{ fee.term }}</div>
                                            <div class="text-xs text-gray-400">Due: {{ fee.due_date }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div class="font-medium">{{ fee.fee_type }}</div>
                                            <div v-if="fee.description" class="text-xs text-gray-500">{{ fee.description }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            {{ fee.amount.toLocaleString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                            {{ fee.paid_amount.toLocaleString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                            {{ fee.balance.toLocaleString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="{
                                                    'bg-green-100 text-green-800': fee.status === 'paid',
                                                    'bg-yellow-100 text-yellow-800': fee.status === 'partial',
                                                    'bg-red-100 text-red-800': fee.status === 'overdue',
                                                    'bg-gray-100 text-gray-800': fee.status === 'pending',
                                                    'bg-blue-100 text-blue-800': fee.status === 'carried_over'
                                                }">
                                                {{ fee.status.charAt(0).toUpperCase() + fee.status.slice(1) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Payment History -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Payment History</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="payments.length === 0">
                                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No payments recorded.
                                        </td>
                                    </tr>
                                    <tr v-for="payment in payments" :key="payment.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ payment.payment_date }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ payment.reference_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ payment.payment_method }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                            KSh {{ payment.amount.toLocaleString() }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- M-Pesa Payment Modal -->
        <div v-show="showPaymentModal" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showPaymentModal = false"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Pay Fees via M-Pesa
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">
                                        Enter your M-Pesa phone number and amount. You will receive a prompt on your phone to complete the payment.
                                    </p>
                                    
                                    <div class="space-y-4">
                                        <div>
                                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                            <input type="text" v-model="paymentForm.phone" id="phone" placeholder="0712345678" class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                        </div>
                                        <div>
                                            <label for="amount" class="block text-sm font-medium text-gray-700">Amount (KSh)</label>
                                            <input type="number" v-model="paymentForm.amount" id="amount" class="mt-1 focus:ring-green-500 focus:border-green-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                            <div class="text-xs text-gray-500 mt-1">Outstanding Balance: KSh {{ summary.balance.toLocaleString() }}</div>
                                        </div>
                                    </div>
                                    
                                    <div v-if="paymentMessage" :class="{'text-green-600': paymentSuccess, 'text-red-600': !paymentSuccess}" class="mt-3 text-sm">
                                        {{ paymentMessage }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="initiatePayment" :disabled="processing" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                            <span v-if="processing">Processing...</span>
                            <span v-else>Pay Now</span>
                        </button>
                        <button type="button" @click="showPaymentModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </StudentLayout>
</template>
