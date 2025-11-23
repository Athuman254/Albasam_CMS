<template>
    <DefaultLayout>
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Fee Report</h4>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="generateReport">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label for="class_id" class="form-label">Class *</label>
                                        <select id="class_id" v-model="form.class_id" class="form-select" required>
                                            <option value="">Select Class</option>
                                            <option v-for="classItem in classes" :key="classItem.id" :value="classItem.id">
                                                {{ getClassDisplayName(classItem) }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="term" class="form-label">Term</label>
                                        <select id="term" v-model="form.term" class="form-select">
                                            <option value="">All Terms</option>
                                            <option v-for="term in terms" :key="term" :value="term">
                                                {{ formatTermName(term) }}
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mt-4">
                                            <label class="form-label d-block">Include Zero Balance</label>
                                            <div class="toggle-switch">
                                                <input 
                                                    type="checkbox" 
                                                    id="include_zero_balance" 
                                                    v-model="form.include_zero_balance"
                                                    class="toggle-switch-checkbox"
                                                >
                                                <label class="toggle-switch-label" for="include_zero_balance">
                                                    <span class="toggle-switch-inner"></span>
                                                    <span class="toggle-switch-switch"></span>
                                                </label>
                                                <span class="toggle-switch-text">
                                                    {{ form.include_zero_balance ? 'Included' : 'Excluded' }}
                                                </span>
                                            </div>
                                            <small class="form-text text-muted">
                                                Toggle to include students with zero or negative balance
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary" :disabled="loading">
                                            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                                            Generate Report
                                        </button>
                                        <button type="button" @click="downloadPDF" class="btn btn-success ms-2" :disabled="!reportData.length || loading">
                                            <i class="fas fa-download me-1"></i> Download PDF
                                        </button>
                                        <button type="button" @click="printReport" class="btn btn-info ms-2" :disabled="!reportData.length || loading">
                                            <i class="fas fa-print me-1"></i> Print Report
                                        </button>
                                    </div>
                                </div>
                            </form>

                            <!-- Report Results -->
                            <div v-if="reportData.length" class="mt-4">
                                <div class="table-responsive" id="report-table">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Admission Number</th>
                                                <th>Student Name</th>
                                                <th>Class</th>
                                                <th>Total Amount</th>
                                                <th>Paid Amount</th>
                                                <th>Balance</th>
                                                <th>Collection Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="item in reportData" :key="item.admission_number">
                                                <td>{{ item.admission_number }}</td>
                                                <td>{{ item.student_name }}</td>
                                                <td>{{ item.class_name }}</td>
                                                <td>{{ formatCurrency(item.total_amount) }}</td>
                                                <td>{{ formatCurrency(item.paid_amount) }}</td>
                                                <td>
                                                    {{ formatCurrency(item.balance) }}
                                                </td>
                                                <td>
                                                    <span>
                                                        {{ item.collection_rate }}%
                                                    </span>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <!-- Totals Row -->
                                        <tfoot>
                                            <tr class="fw-bold totals-row">
                                                <td colspan="3" class="text-end">TOTALS:</td>
                                                <td>{{ formatCurrency(summary.total_amount) }}</td>
                                                <td>{{ formatCurrency(summary.total_paid) }}</td>
                                                <td>
                                                    {{ formatCurrency(summary.total_balance) }}
                                                </td>
                                                <td>
                                                    <span>
                                                        {{ summary.collection_rate }}% Collected
                                                    </span>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>

                            <div v-else-if="searched" class="mt-4">
                                <div class="alert alert-info">
                                    No records found for the selected criteria.
                                </div>
                            </div>

                            <div v-if="error" class="mt-4">
                                <div class="alert alert-danger">
                                    {{ error }}
                                </div>
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

export default {
    components: {
        DefaultLayout
    },
    props: {
        classes: Array,
        terms: Array
    },
    data() {
        return {
            form: {
                class_id: '',
                term: '',
                include_zero_balance: false
            },
            reportData: [],
            summary: {
                total_amount: 0,
                total_paid: 0,
                total_balance: 0,
                collection_rate: 0
            },
            loading: false,
            searched: false,
            error: null
        }
    },
    methods: {
        getClassDisplayName(classItem) {
            if (classItem.full_name) {
                return classItem.full_name;
            }
            if (classItem.name && classItem.stream_name) {
                return `${classItem.name} ${classItem.stream_name}`;
            }
            return classItem.name || 'Unknown Class';
        },

        formatTermName(term) {
            if (term.startsWith('Term')) {
                return term;
            }
            return `Term ${term}`;
        },

        async generateReport() {
            this.loading = true;
            this.searched = true;
            this.error = null;
            
            try {
                const response = await fetch(route('admin.fees.reports.generate-fee-report'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();
                
                if (data.success) {
                    this.reportData = data.reportData || [];
                    this.summary = data.summary || {
                        total_amount: 0,
                        total_paid: 0,
                        total_balance: 0,
                        collection_rate: 0
                    };
                } else {
                    this.error = data.message || 'Error generating report';
                    this.reportData = [];
                    this.summary = {
                        total_amount: 0,
                        total_paid: 0,
                        total_balance: 0,
                        collection_rate: 0
                    };
                }
            } catch (error) {
                console.error('Error generating report:', error);
                this.error = 'Error generating report. Please try again.';
                this.reportData = [];
                this.summary = {
                    total_amount: 0,
                    total_paid: 0,
                    total_balance: 0,
                    collection_rate: 0
                };
            } finally {
                this.loading = false;
            }
        },
        
        async downloadPDF() {
            this.loading = true;
            try {
                const formData = new FormData();
                formData.append('class_id', this.form.class_id);
                formData.append('term', this.form.term);
                formData.append('include_zero_balance', this.form.include_zero_balance ? 1 : 0);
                formData.append('download', true);
                
                const response = await fetch(route('admin.fees.reports.generate-fee-report'), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.message || `Server error: ${response.status}`);
                }

                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.style.display = 'none';
                a.href = url;
                a.download = `fee-report-${new Date().toISOString().split('T')[0]}.pdf`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                
            } catch (error) {
                console.error('Error downloading PDF:', error);
                this.error = `Error downloading PDF: ${error.message}`;
                alert('Error downloading PDF. Please try again.');
            } finally {
                this.loading = false;
            }
        },

        async printReport() {
            if (!this.reportData.length) {
                alert('Please generate a report first.');
                return;
            }

            this.loading = true;
            try {
                const formData = new FormData();
                formData.append('class_id', this.form.class_id);
                formData.append('term', this.form.term);
                formData.append('include_zero_balance', this.form.include_zero_balance ? 1 : 0);
                formData.append('print', true);
                
                const response = await fetch(route('admin.fees.reports.generate-fee-report'), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (!response.ok) {
                    throw new Error(`Server error: ${response.status}`);
                }

                // Open the print view in a new window
                const printWindow = window.open('', '_blank');
                const html = await response.text();
                printWindow.document.write(html);
                printWindow.document.close();
                
                // Auto-print after content loads
                printWindow.onload = function() {
                    printWindow.print();
                    // Optional: Close window after printing
                    setTimeout(() => {
                        printWindow.close();
                    }, 500);
                };
                
            } catch (error) {
                console.error('Error printing report:', error);
                this.error = `Error printing report: ${error.message}`;
                alert('Error printing report. Please try again.');
            } finally {
                this.loading = false;
            }
        },
        
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-KE', {
                style: 'currency',
                currency: 'KES',
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(amount);
        }
    }
}
</script>

<style scoped>
.totals-row {
    border-top: 2px solid #dee2e6;
}

.text-end {
    text-align: right !important;
}

.btn {
    display: inline-flex;
    align-items: center;
}

/* Toggle Switch Styles */
.toggle-switch {
    display: flex;
    align-items: center;
    gap: 10px;
}

.toggle-switch-checkbox {
    display: none;
}

.toggle-switch-label {
    display: inline-block;
    width: 60px;
    height: 30px;
    background-color: #ccc;
    border-radius: 15px;
    position: relative;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-bottom: 0;
}

.toggle-switch-label .toggle-switch-inner {
    display: block;
    width: 100%;
    height: 100%;
    border-radius: 15px;
    position: relative;
    transition: background-color 0.3s ease;
}

.toggle-switch-label .toggle-switch-inner::before,
.toggle-switch-label .toggle-switch-inner::after {
    content: '';
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    font-size: 12px;
    font-weight: bold;
    color: #fff;
    transition: opacity 0.3s ease;
}

.toggle-switch-label .toggle-switch-inner::before {
    content: 'ON';
    left: 8px;
    opacity: 0;
}

.toggle-switch-label .toggle-switch-inner::after {
    content: 'OFF';
    right: 8px;
    opacity: 1;
}

.toggle-switch-label .toggle-switch-switch {
    position: absolute;
    top: 3px;
    left: 3px;
    width: 24px;
    height: 24px;
    background-color: #fff;
    border-radius: 50%;
    transition: transform 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.toggle-switch-checkbox:checked + .toggle-switch-label {
    background-color: #198754;
}

.toggle-switch-checkbox:checked + .toggle-switch-label .toggle-switch-inner {
    background-color: #198754;
}

.toggle-switch-checkbox:checked + .toggle-switch-label .toggle-switch-inner::before {
    opacity: 1;
}

.toggle-switch-checkbox:checked + .toggle-switch-label .toggle-switch-inner::after {
    opacity: 0;
}

.toggle-switch-checkbox:checked + .toggle-switch-label .toggle-switch-switch {
    transform: translateX(30px);
}

.toggle-switch-text {
    font-weight: 500;
    color: #495057;
    min-width: 70px;
}

.toggle-switch-checkbox:checked + .toggle-switch-label + .toggle-switch-text {
    color: #198754;
}

/* Hover effects */
.toggle-switch-label:hover {
    background-color: #b3b3b3;
}

.toggle-switch-checkbox:checked + .toggle-switch-label:hover {
    background-color: #157347;
}

/* Focus styles for accessibility */
.toggle-switch-checkbox:focus + .toggle-switch-label {
    box-shadow: 0 0 0 2px rgba(25, 135, 84, 0.25);
}

/* Disabled state */
.toggle-switch-checkbox:disabled + .toggle-switch-label {
    opacity: 0.6;
    cursor: not-allowed;
}

.toggle-switch-checkbox:disabled + .toggle-switch-label:hover {
    background-color: #ccc;
}

.toggle-switch-checkbox:checked:disabled + .toggle-switch-label:hover {
    background-color: #198754;
}

@media print {
    .card-header, .card-body form, .btn {
        display: none !important;
    }
    
    .table-responsive {
        overflow: visible !important;
    }
}

@media (max-width: 768px) {
    .toggle-switch {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .toggle-switch-text {
        min-width: auto;
    }
}
</style>

