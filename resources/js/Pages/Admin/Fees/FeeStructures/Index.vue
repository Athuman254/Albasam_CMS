<template>
  <DefaultLayout>
    <Head title="Fee Structures" />
    
    <!-- Success/Error Messages -->
    <div v-if="$page.props.flash && $page.props.flash.success" class="alert alert-success alert-dismissible fade show m-3" role="alert">
      <i class="fas fa-check-circle me-2"></i>
      {{ $page.props.flash.success }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <div v-if="$page.props.flash && $page.props.flash.error" class="alert alert-danger alert-dismissible fade show m-3" role="alert">
      <i class="fas fa-exclamation-circle me-2"></i>
      {{ $page.props.flash.error }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Fee Structures</h4>
                        <div>
                            <Link 
                                :href="route('admin.fee-structures.create')" 
                                class="btn btn-primary"
                            >
                                <i class="fas fa-plus me-1"></i> Create Fee Structure
                            </Link>
                            <Link 
                                :href="route('admin.fees.index')" 
                                class="btn btn-outline-secondary ms-2"
                            >
                                <i class="fas fa-list me-1"></i> View Fees
                            </Link>
                        </div>
                    </div>
                    <div class="card-body">
                       <div class="card-body">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0 text-primary">{{ fee_structures?.total || 0 }}</h4>
                            <small class="text-muted">Total Structures</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-invoice-dollar fa-2x text-primary opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0 text-success">{{ activeStructuresCount }}</h4>
                            <small class="text-muted">Active Structures</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x text-success opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0 text-warning">{{ inactiveStructuresCount }}</h4>
                            <small class="text-muted">Inactive Structures</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-pause-circle fa-2x text-warning opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
    </div>

                        <div v-if="!fee_structures?.data?.length" class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-file-invoice-dollar fa-4x text-muted mb-3"></i>
                                <h4 class="text-muted">No Fee Structures Found</h4>
                                <p class="text-muted mb-4">Create fee structure templates to easily generate fees for classes</p>
                                <Link 
                                    :href="route('admin.fee-structures.create')" 
                                    class="btn btn-primary btn-lg"
                                >
                                    <i class="fas fa-plus me-2"></i> Create Your First Fee Structure
                                </Link>
                            </div>
                        </div>
                        
                        <div v-else>
                            <!-- Fee Structures Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Class</th>
                                            <th>Academic Year</th>
                                            <th>Term</th>
                                            <th>Amount</th>
                                            <th>Additional Fees</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="structure in fee_structures.data" :key="structure.id" class="align-middle">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                                                    <div>
                                                        <strong>{{ getClassName(structure.rank_id) }}</strong>
                                                        <small class="text-muted d-block" v-if="structure.description">
                                                            {{ structure.description }}
                                                        </small>
                                                        <small class="text-info d-block" v-if="structure.invoices_count > 0">
                                                            <i class="fas fa-receipt me-1"></i>
                                                            {{ structure.invoices_count }} fee(s) generated
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-bold">{{ structure.academic_year }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    Term {{ structure.term }}
                                                </span>
                                            </td>
                                            <td class="fw-bold text-success">
                                                {{ formatCurrency(structure.amount) }}
                                            </td>
                                            <td>
                                                <div v-if="structure.additional_fees && structure.additional_fees.length > 0">
                                                    <small v-for="(fee, index) in structure.additional_fees" :key="index" class="d-block text-muted">
                                                        {{ fee.name }}: {{ formatCurrency(fee.amount) }}
                                                    </small>
                                                </div>
                                                <span v-else class="text-muted">None</span>
                                            </td>
                                            <td>
                                                <span :class="{'text-danger': isOverdue(structure.due_date)}">
                                                    {{ formatDate(structure.due_date) }}
                                                    <span v-if="isOverdue(structure.due_date)" class="badge bg-danger ms-1">Overdue</span>
                                                </span>
                                            </td>
                                            <td>
                                                <span :class="`badge bg-${getStatusBadge(structure.is_active)}`">
                                                    <i :class="`fas fa-${structure.is_active ? 'check' : 'pause'} me-1`"></i>
                                                    {{ getStatusText(structure.is_active) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <!-- Three-dot dropdown menu -->
                                                <div class="dropdown">
                                                    <button 
                                                        class="dots-button dropdown-toggle"
                                                        type="button"
                                                        data-bs-toggle="dropdown"
                                                        aria-expanded="false"
                                                        :disabled="generatingFees"
                                                    >
                                                        <span v-if="generatingFees && currentGeneratingId === structure.id">
                                                            <i class="fas fa-spinner fa-spin"></i>
                                                        </span>
                                                        <span v-else>⋮</span>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                                        <li>
                                                            <Link 
                                                                :href="route('admin.fee-structures.edit', { fee_structure: structure.id })"
                                                                class="dropdown-item d-flex align-items-center"
                                                            >
                                                                <i class="fas fa-edit text-warning me-2" style="width: 16px;"></i>
                                                                Edit Structure
                                                            </Link>
                                                        </li>
                                                        <li>
                                                            <button 
                                                                @click="viewDetails(structure)"
                                                                class="dropdown-item d-flex align-items-center"
                                                            >
                                                                <i class="fas fa-eye text-info me-2" style="width: 16px;"></i>
                                                                View Details
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button 
                                                                @click="generateFees(structure)"
                                                                class="dropdown-item d-flex align-items-center"
                                                                :disabled="!structure.is_active || generatingFees"
                                                            >
                                                                <i class="fas fa-cogs text-success me-2" style="width: 16px;"></i>
                                                                <span>
                                                                    {{ generatingFees && currentGeneratingId === structure.id ? 'Generating...' : 'Generate Fees' }}
                                                                </span>
                                                                <span v-if="structure.invoices_count > 0" class="badge bg-primary ms-2">
                                                                    {{ structure.invoices_count }}
                                                                </span>
                                                            </button>
                                                        </li>
                                                        <li><hr class="dropdown-divider m-0"></li>
                                                        <li>
                                                            <button 
                                                                @click="toggleStatus(structure)"
                                                                class="dropdown-item d-flex align-items-center"
                                                                :disabled="generatingFees"
                                                            >
                                                                <i :class="`fas fa-power-off me-2 ${structure.is_active ? 'text-warning' : 'text-success'}`" style="width: 16px;"></i>
                                                                {{ structure.is_active ? 'Deactivate' : 'Activate' }}
                                                            </button>
                                                        </li>
                                                        <li><hr class="dropdown-divider m-0"></li>
                                                        <li>
                                                            <button 
                                                                @click="deleteStructure(structure)"
                                                                class="dropdown-item d-flex align-items-center text-danger"
                                                                :disabled="generatingFees"
                                                            >
                                                                <i class="fas fa-trash me-2" style="width: 16px;"></i>
                                                                Delete Structure
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div v-if="fee_structures.links && fee_structures.links.length > 1" class="mt-4">
                                <nav aria-label="Fee Structures pagination">
                                    <ul class="pagination justify-content-center mb-0">
                                        <li 
                                            v-for="link in fee_structures.links" 
                                            :key="link.label"
                                            :class="['page-item', { 
                                                active: link.active, 
                                                disabled: !link.url 
                                            }]"
                                        >
                                            <Link 
                                                :href="link.url || '#'" 
                                                class="page-link"
                                                v-html="link.label"
                                                preserve-scroll
                                            />
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="generatingFees" class="loading-overlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
            <p class="mt-3">Generating fees, please wait...</p>
        </div>
    </div>
  </DefaultLayout>
</template>

<script setup>
import DefaultLayout from '@/Layouts/DefaultLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, ref, computed } from 'vue';

const props = defineProps({
    fee_structures: Object,
    classes: Array
});

// Reactive data
const generatingFees = ref(false);
const currentGeneratingId = ref(null);

// Computed properties
const activeStructuresCount = computed(() => {
    return props.fee_structures?.data?.filter(structure => structure.is_active)?.length || 0;
});

const inactiveStructuresCount = computed(() => {
    return props.fee_structures?.data?.filter(structure => !structure.is_active)?.length || 0;
});

const totalStudentsAffected = computed(() => {
    let total = 0;
    props.fee_structures?.data?.forEach(structure => {
        const classItem = props.classes?.find(c => c.id === structure.rank_id);
        if (classItem) {
            total += classItem.available_seats || 0;
        }
    });
    return total;
});

// Initialize Bootstrap dropdowns when component mounts
onMounted(() => {
    initializeBootstrapDropdowns();
});

// Helper methods
const getClassName = (classId) => {
    const classItem = props.classes?.find(c => c.id === classId);
    return classItem ? classItem.full_name || classItem.name : 'N/A';
};

const formatCurrency = (amount) => {
    if (!amount || isNaN(amount)) return 'Ksh 0.00';
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-KE', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getStatusBadge = (isActive) => {
    return isActive ? 'success' : 'secondary';
};

const getStatusText = (isActive) => {
    return isActive ? 'Active' : 'Inactive';
};

const isOverdue = (dueDate) => {
    if (!dueDate) return false;
    return new Date(dueDate) < new Date();
};

const initializeBootstrapDropdowns = () => {
    if (typeof bootstrap !== 'undefined') {
        const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });
        window.bootstrap = bootstrap;
    }
};

const closeAllDropdowns = () => {
    try {
        const dropdownElements = document.querySelectorAll('.dropdown-menu.show');
        dropdownElements.forEach(dropdownElement => {
            if (typeof bootstrap !== 'undefined') {
                const dropdownToggle = dropdownElement.previousElementSibling;
                if (dropdownToggle) {
                    const dropdownInstance = bootstrap.Dropdown.getInstance(dropdownToggle);
                    if (dropdownInstance) {
                        dropdownInstance.hide();
                    } else {
                        dropdownElement.classList.remove('show');
                        dropdownToggle.setAttribute('aria-expanded', 'false');
                    }
                }
            } else {
                dropdownElement.classList.remove('show');
                const dropdownToggle = dropdownElement.previousElementSibling;
                if (dropdownToggle) {
                    dropdownToggle.setAttribute('aria-expanded', 'false');
                    dropdownToggle.classList.remove('show');
                }
            }
        });
    } catch (error) {
        console.warn('Error closing dropdowns:', error);
    }
};

// Action methods
const viewDetails = (structure) => {
    const additionalFees = structure.additional_fees && structure.additional_fees.length > 0 
        ? structure.additional_fees.map(fee => `• ${fee.name}: ${formatCurrency(fee.amount)}`).join('\n')
        : 'None';
    
    alert(`Fee Structure Details:\n\n` +
        `Class: ${getClassName(structure.rank_id)}\n` +
        `Academic Year: ${structure.academic_year}\n` +
        `Term: ${structure.term}\n` +
        `Main Amount: ${formatCurrency(structure.amount)}\n` +
        `Additional Fees:\n${additionalFees}\n` +
        `Due Date: ${formatDate(structure.due_date)}\n` +
        `Status: ${getStatusText(structure.is_active)}\n` +
        `Fees Generated: ${structure.invoices_count || 0}\n` +
        `Description: ${structure.description || 'No description'}`);
};

const generateFees = async (structure) => {
    if (!structure.is_active) {
        alert('Cannot generate fees for an inactive fee structure.');
        return;
    }
    
    const className = getClassName(structure.rank_id);
    const totalAmount = calculateTotalAmount(structure);
    
    if (confirm(`Generate fees for all students in ${className}?\n\n` +
        `Academic Year: ${structure.academic_year}\n` +
        `Term: ${structure.term}\n` +
        `Total Amount: ${formatCurrency(totalAmount)}\n` +
        `Due Date: ${formatDate(structure.due_date)}\n\n` +
        `This will create fee records for all students in this class.`)) {
        
        generatingFees.value = true;
        currentGeneratingId.value = structure.id;
        closeAllDropdowns();
        
        try {
            await router.post(route('admin.fee-structures.generate-fees', { fee_structure: structure.id }), {}, {
                preserveScroll: true,
                onSuccess: (page) => {
                    console.log('✅ Fees generated successfully');
                    // Success message will be shown via flash message
                },
                onError: (errors) => {
                    console.error('❌ Error generating fees:', errors);
                    let errorMessage = 'Failed to generate fees. ';
                    if (errors.message) {
                        errorMessage += errors.message;
                    }
                    alert(errorMessage);
                },
                onFinish: () => {
                    generatingFees.value = false;
                    currentGeneratingId.value = null;
                }
            });
        } catch (error) {
            console.error('Error generating fees:', error);
            alert('Error generating fees: ' + error.message);
            generatingFees.value = false;
            currentGeneratingId.value = null;
        }
    }
};

const calculateTotalAmount = (structure) => {
    let total = parseFloat(structure.amount) || 0;
    if (structure.additional_fees && structure.additional_fees.length > 0) {
        structure.additional_fees.forEach(fee => {
            total += parseFloat(fee.amount) || 0;
        });
    }
    return total;
};

const toggleStatus = (structure) => {
    const newStatus = !structure.is_active;
    const action = newStatus ? 'activate' : 'deactivate';
    const className = getClassName(structure.rank_id);
    
    if (confirm(`Are you sure you want to ${action} the fee structure for ${className} - ${structure.academic_year} Term ${structure.term}?`)) {
        closeAllDropdowns();
        
        router.post(route('admin.fee-structures.update-status', { 
            fee_structure: structure.id 
        }), {
            is_active: newStatus,
            _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }, {
            preserveScroll: true,
            onSuccess: () => {
                console.log('✅ Status updated successfully');
            },
            onError: (errors) => {
                console.error('❌ Error updating status:', errors);
                alert('Failed to update status. Please try again.');
            }
        });
    }
};
const deleteStructure = (structure) => {
    const className = getClassName(structure.rank_id);
    
    if (confirm(`Are you sure you want to delete the fee structure for ${className} - ${structure.academic_year} Term ${structure.term}?\n\n` +
        `This action cannot be undone and will not affect already generated fees.`)) {
        
        closeAllDropdowns();
        
        router.delete(route('admin.fee-structures.destroy', { fee_structure: structure.id }), {
            preserveScroll: true,
            onSuccess: () => {
                console.log('✅ Structure deleted successfully');
            },
            onError: (errors) => {
                console.error('❌ Error deleting structure:', errors);
                alert('Failed to delete structure. Please try again.');
            }
        });
    }
};
</script>

<style scoped>
.empty-state {
    padding: 3rem 0;
}

.card-header {
    border-bottom: none;
}

.card-header .card-title {
    color: white;
}

.table th {
    border-top: none;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}

.badge {
    font-size: 0.75em;
    font-weight: 500;
}

.alert {
    margin: 1rem;
    border-radius: 0.5rem;
}

/* Statistics Cards */
.card.bg-primary,
.card.bg-success,
.card.bg-warning,
.card.bg-info {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card.bg-primary .card-body,
.card.bg-success .card-body,
.card.bg-warning .card-body,
.card.bg-info .card-body {
    padding: 1rem;
}

/* Three-dots dropdown styling */
.dropdown {
    position: relative;
    display: inline-block;
}

.dots-button {
    border: none !important;
    background: none !important;
    padding: 0.25rem 0.5rem !important;
    font-size: 18px;
    font-weight: bold;
    color: #6c757d;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
    line-height: 1;
    min-width: 30px;
    min-height: 30px;
    border-radius: 4px;
}

.dots-button:hover:not(:disabled) {
    color: #495057;
    background-color: #f8f9fa !important;
}

.dots-button:focus {
    box-shadow: none !important;
    outline: none !important;
}

.dots-button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.dots-button::after {
    display: none !important;
}

.dropdown-menu {
    min-width: 220px;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(0, 0, 0, 0.15);
    border-radius: 0.5rem;
    padding: 0.5rem 0;
    z-index: 1000;
}

.dropdown-item {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
    transition: all 0.15s ease;
}

.dropdown-item:focus, 
.dropdown-item:hover {
    background-color: #f8f9fa;
    color: #495057;
}

.dropdown-item:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: transparent;
}

.dropdown-item:disabled:hover {
    background-color: transparent;
}

.text-center .dropdown {
    display: flex;
    justify-content: center;
}

.dots-button {
    position: relative;
    z-index: 1;
}

/* Loading state styling */
.dropdown-item:disabled {
    position: relative;
}

.dropdown-item:disabled::after {
    content: '';
    position: absolute;
    top: 50%;
    right: 1rem;
    transform: translateY(-50%);
    width: 12px;
    height: 12px;
    border: 2px solid #6c757d;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

/* Loading overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-spinner {
    background: white;
    padding: 2rem;
    border-radius: 0.5rem;
    text-align: center;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

/* Additional fee styling */
.text-muted small {
    font-size: 0.75rem;
}

/* Status badges */
.badge.bg-success {
    background-color: #198754 !important;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
}

.badge.bg-danger {
    background-color: #dc3545 !important;
}

/* Table row hover effects */
.table-hover tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.025);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .card-header .btn {
        margin-top: 0.5rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .dots-button {
        padding: 0.2rem 0.4rem !important;
        font-size: 16px;
    }
}

@keyframes spin {
    0% { transform: translateY(-50%) rotate(0deg); }
    100% { transform: translateY(-50%) rotate(360deg); }
}
</style>