<template>

   <Head title="Teacher Details Page" />

   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Registered Teachers</h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item">
                  <Link :href="route('admin.teachers.index')">Registered Teachers</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Details
               </li>
            </ol>
         </nav>

         <div class="col-xl-4 col-md-6 order-1 order-md-1">
            <div class="card mb-6">
               <div class="card-body">
                  <h3>{{ employee.honorific?.name + ' ' + employee.first_name + ' ' + employee.last_name }}</h3>
                  <small class="card-text text-uppercase text-light small">ABOUT</small>
                  <ul class="list-unstyled my-3 py-1">
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bxs-user-badge"></i>
                        <span class="fw-bold mx-2">Employee Number :</span>
                        <span>{{ employee.staff_number ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-male-female"></i>
                        <span class="fw-bold mx-2">Gender :</span>
                        <span>{{ employee.gender?.name ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-church"></i>
                        <span class="fw-bold mx-2">Religion :</span>
                        <span>{{ employee.religion?.name ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center">
                        <i class="icon-base bx bx-category-alt"></i>
                        <span class="fw-bold mx-2">Marital Status :</span>
                        <span>{{ employee.marital_status?.name ?? '-' }}</span>
                     </li>
                  </ul>

                  <small class="card-text text-uppercase text-light small">CONTACT</small>
                  <ul class="list-unstyled my-3 py-1">
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-envelope"></i>
                        <span class="fw-bold mx-2">Email :</span>
                        <span>{{ employee.email ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-phone"></i>
                        <span class="fw-bold mx-2">Primary Phone :</span>
                        <span>{{ employee.primary_phone ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-phone"></i>
                        <span class="fw-bold mx-2">Secondary Phone :</span>
                        <span>{{ employee.secondary_phone ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-current-location"></i>
                        <span class="fw-bold mx-2">Permanent Address :</span>
                        <span>{{ employee.permanent_physical_address ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center mb-4">
                        <i class="icon-base bx bx-current-location"></i>
                        <span class="fw-bold mx-2">Secondary Address :</span>
                        <span>{{ employee.secondary_physical_address ?? '-' }}</span>
                     </li>
                     <li class="d-flex align-items-center">
                        <i class="icon-base bx bx-box"></i>
                        <span class="fw-bold mx-2">Postal Address :</span>
                        <span>{{ employee.postal_address ?? '-' }}</span>
                     </li>
                  </ul>
               </div>
            </div>
            <div class="card mb-6">
               <div class="card-body">
                  <h3>System Access</h3>
                  <div v-if="!employee.has_system_access" class="d-flex align-items-start row">
                     <div class="col-12">
                        <p>The employee has no system access! <br>
                           Click the button below to give access.
                        </p>
                        <button type="button" class="btn btn-sm btn-outline-primary"
                           @click.prevent="showCreateCredentialsModal">
                           Give Access
                        </button>
                     </div>
                  </div>
                  <div v-else>
                     <div class="d-flex align-items-start row">
                        <div class="col-12">
                           <p>The employee has access to the system! <br>
                              Click the button below to revoke access
                           </p>
                           <button type="button" class="btn btn-outline-danger" @click.prevent="revokeSystemAccess">
                              Suspend
                           </button>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>

         <div class="col-xl-8 col-md-6 order-3 order-md-2">
            <div class="accordion">
               <!-- Employee Data Accordion -->
               <div class="card accordion-item">
                  <h2 class="accordion-header border-bottom">
                     <button type="button" class="accordion-button" :class="{ collapsed: openAccordion !== 'employee' }"
                        @click="toggleAccordion('employee')">
                        Employee Details
                     </button>
                  </h2>
                  <div class="accordion-collapse" :class="{ show: openAccordion === 'employee' }">
                     <div
                        class="accordion-body px-0 py-4 d-flex align-items-baseline flex-wrap flex-xl-nowrap flex-sm-nowrap flex-md-wrap">
                        <table class="table table-sm table-borderless text-nowrap small">
                           <tbody>
                              <tr>
                                 <td class="w-50">Employee Number</td>
                                 <td class="fw-medium text-heading">{{ employee.staff_number ?? '-' }}</td>
                              </tr>
                              <tr>
                                 <td>Passport/ID Number</td>
                                 <td class="fw-medium text-heading">{{ employee.identification_number ?? '-' }}</td>
                              </tr>
                              <tr>
                                 <td>Tax Identification Number (KRA)</td>
                                 <td class="fw-medium text-heading">{{ employee.tax_identification_pin ?? '-' }}</td>
                              </tr>
                              <tr>
                                 <td>TSC Number</td>
                                 <td class="fw-medium text-heading">{{ teacher.tsc_number ?? '-' }}</td>
                              </tr>
                              <tr>
                                 <td>Specialization</td>
                                 <td class="fw-medium text-heading">{{ teacher.specialization?.name ?? '-' }}</td>
                              </tr>
                           </tbody>
                        </table>
                        <table class="table table-sm table-borderless text-nowrap small">
                           <tbody>
                              <tr>
                                 <td class="w-50">Date of hire</td>
                                 <td class="fw-medium text-heading">{{ employee.date_of_hire ?? '-' }}</td>
                              </tr>
                              <tr>
                                 <td>Employment Type</td>
                                 <td class="fw-medium text-heading">{{ employee.employment_type?.name ?? '-' }}</td>
                              </tr>
                              <tr>
                                 <td>Employment Status</td>
                                 <td class="fw-medium text-heading">{{ employee.employment_status?.name ?? '-' }}</td>
                              </tr>
                              <tr>
                                 <td>Job Title</td>
                                 <td class="fw-medium text-heading">{{ teacher.job?.title ?? '-' }}</td>
                              </tr>
                              <tr>
                                 <td>Year of experience</td>
                                 <td class="fw-medium text-heading">{{ teacher.years_of_experience + ' yrs' ?? '-' }}
                                 </td>
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>

               <!-- Emergency Contact Details Accordion -->
               <div class="card accordion-item">
                  <h2 class="accordion-header border-bottom">
                     <button type="button" class="accordion-button" :class="{ collapsed: openAccordion !== 'contact' }"
                        @click="toggleAccordion('contact')">
                        Emergency Contact Details
                     </button>
                  </h2>
                  <div class="accordion-collapse" :class="{ show: openAccordion === 'contact' }">
                     <div
                        class="accordion-body py-4 d-flex align-items-baseline flex-wrap flex-xl-nowrap flex-sm-nowrap flex-md-wrap">
                        <table class="table table-sm table-bordered text-nowrap" style="max-width: inherit;">
                           <thead>
                              <tr>
                                 <th class="p-2 fw-medium text-heading" style="width: 30%;">Name</th>
                                 <th class="p-2 fw-medium text-heading" style="width: 25%;">Email</th>
                                 <th class="p-2 fw-medium text-heading" style="width: 15%;">Phone</th>
                                 <th class="p-2 fw-medium text-heading" style="width: 15%;">Relationship</th>
                                 <!--                              <th class="p-2 fw-medium text-heading" style="width: 10%;"></th>-->
                              </tr>
                           </thead>
                           <tbody>
                              <tr v-for="(contact, index) in emergencyContacts" :key="index">
                                 <td class="p-2">{{ contact.name ?? '-' }}</td>
                                 <td class="p-2">{{ contact.email ?? '-' }}</td>
                                 <td class="p-2">{{ contact.phone ?? '-' }}</td>
                                 <td class="p-2">{{ contact.relationship?.name ?? '-' }}</td>
                                 <!--                              <td class="p-2"></td>-->
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>

               <!-- Qualification Details Accordion -->
               <div class="card accordion-item">
                  <h2 class="accordion-header border-bottom">
                     <button type="button" class="accordion-button"
                        :class="{ collapsed: openAccordion !== 'qualification' }"
                        @click="toggleAccordion('qualification')">
                        Qualification Details
                     </button>
                  </h2>
                  <div class="accordion-collapse" :class="{ show: openAccordion === 'qualification' }">
                     <div
                        class="accordion-body py-4 d-flex align-items-baseline flex-wrap flex-xl-nowrap flex-sm-nowrap flex-md-wrap">
                        <table class="table table-sm table-bordered text-nowrap" style="max-width: inherit;">
                           <thead>
                              <tr>
                                 <th class="p-2 fw-medium text-heading" style="width: 30%;">Institution</th>
                                 <th class="p-2 fw-medium text-heading" style="width: 25%;">Course</th>
                                 <th class="p-2 fw-medium text-heading" style="width: 15%;">Qualification</th>
                                 <th class="p-2 fw-medium text-heading" style="width: 15%;">Completion Year</th>
                                 <!--                              <th class="p-2 fw-medium text-heading" style="width: 10%;"></th>-->
                              </tr>
                           </thead>
                           <tbody>
                              <tr v-for="(qualification, index) in qualificationDetails" :key="index">
                                 <td class="p-2">{{ qualification.institution_name }}</td>
                                 <td class="p-2">{{ qualification.course_name }}</td>
                                 <td class="p-2">{{ qualification.qualification_type?.name }}</td>
                                 <td class="p-2">{{ qualification.year_of_completion }}</td>
                                 <!--                              <td class="p-2"></td>-->
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>

               <!-- Work History Details Accordion -->
               <div class="card accordion-item">
                  <h2 class="accordion-header border-bottom">
                     <button type="button" class="accordion-button" :class="{ collapsed: openAccordion !== 'history' }"
                        @click="toggleAccordion('history')">
                        Work History Details
                     </button>
                  </h2>
                  <div class="accordion-collapse" :class="{ show: openAccordion === 'history' }">
                     <div
                        class="accordion-body py-4 d-flex align-items-baseline flex-wrap flex-xl-nowrap flex-sm-nowrap flex-md-wrap">
                        <table class="table table-sm table-bordered text-nowrap" style="max-width: inherit;">
                           <thead>
                              <tr>
                                 <th class="p-2 fw-medium text-heading" style="width: 35%;">Name</th>
                                 <th class="p-2 fw-medium text-heading" style="width: 25%;">Start Date</th>
                                 <th class="p-2 fw-medium text-heading" style="width: 25%;">End Date</th>
                                 <!--                              <th class="p-2 fw-medium text-heading" style="width: 10%;"></th>-->
                              </tr>
                           </thead>
                           <tbody>
                              <tr v-for="(history, index) in workHistories" :key="index">
                                 <td class="p-2">{{ history.institution_name ?? '-' }}</td>
                                 <td class="p-2">{{ history.start_date ?? '-' }}</td>
                                 <td class="p-2">{{ history.end_date ?? '-' }}</td>
                                 <!--                              <td class="p-2"></td>-->
                              </tr>
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
               <!-- Incomes details -->

               <div class="card accordion-item">
                  <h2 class="accordion-header border-bottom">
                     <button type="button" class="accordion-button" :class="{ collapsed: openAccordion !== 'incomes' }"
                        @click="toggleAccordion('incomes')">
                        Incomes
                     </button>
                  </h2>
                  <div class="accordion-collapse" :class="{ show: openAccordion === 'incomes' }">
                     <div
                        class="accordion-body py-4 d-flex align-items-baseline flex-wrap flex-xl-nowrap flex-sm-nowrap flex-md-wrap">
                        <div class="w-100">
                           <div class="d-flex justify-content-end mb-2">
                              <button @click="openEmployeeIncomeModal" class="btn btn-primary"><i
                                    class="icon-base bx bx-plus-circle me-1"></i> Add</button>
                           </div>
                           <table class="table table-sm table-bordered text-nowrap" style="max-width: inherit;">
                              <thead>
                                 <tr>
                                    <th class="p-2 fw-medium text-heading" style="width: 35%;">Income</th>
                                    <th class="p-2 fw-medium text-heading" style="width: 25%;">Amount</th>
                                    <th class="p-2 fw-medium text-heading" style="width: 15%;">Actions</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 <tr v-for="(income, index) in employeeIncomes" :key="index">
                                    <td class="p-2">{{ income.income.name ?? '-' }}</td>
                                    <td class="p-2 text-end">{{ formatCurrency(income.amount / 100) }}</td>
                                    <td class="text-end">
                                       <div class="dropdown">
                                          <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                                             <i class="icon-base bx bx-dots-vertical"></i>
                                          </button>
                                          <div class="dropdown-menu dropdown-menu-end">
                                             <a href="#" class="dropdown-item"
                                                @click.prevent="openEmployeeIncomeEditModal(income)">
                                                <i class="icon-base bx bx-edit-alt me-2"></i>Edit
                                             </a>
                                             <a title="Delete" href="#" class="dropdown-item text-danger"
                                                @click.prevent="deleteEmployeeIncome(income)">
                                                <i class="icon-base bx bx-trash me-2"></i>Del
                                             </a>
                                          </div>
                                       </div>

                                    </td>
                                 </tr>
                              </tbody>
                           </table>

                        </div>
                     </div>
                  </div>
               </div>

               <!-- Deduction details -->

               <div class="card accordion-item">
                  <h2 class="accordion-header border-bottom">
                     <button type="button" class="accordion-button"
                        :class="{ collapsed: openAccordion !== 'deductions' }" @click="toggleAccordion('deductions')">
                        Deductions
                     </button>
                  </h2>
                  <div class="accordion-collapse" :class="{ show: openAccordion === 'deductions' }">
                     <div
                        class="accordion-body py-4 d-flex align-items-baseline flex-wrap flex-xl-nowrap flex-sm-nowrap flex-md-wrap">
                        <div class="w-100">
                           <div class="d-flex justify-content-end mb-2">
                              <button @click="openEmployeeDeductionModal" class="btn btn-primary"><i
                                    class="icon-base bx bx-plus-circle me-1"></i> Add</button>
                           </div>
                           <table class="table table-sm table-bordered text-nowrap" style="max-width: inherit;">
                              <thead>
                                 <tr>
                                    <th class="p-2 fw-medium text-heading" style="width: 35%;">Deduction</th>
                                    <th class="p-2 fw-medium text-heading" style="width: 25%;">Amount</th>
                                    <th class="p-2 fw-medium text-heading" style="width: 15%;">Actions</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 <tr v-for="(deduction, index) in employeeDeductions" :key="index">
                                    <td class="p-2">{{ deduction.deduction.name ?? '-' }}</td>
                                    <td class="p-2 text-end">{{ formatCurrency(deduction.amount / 100) }}</td>
                                    <td class="text-end">
                                       <div class="dropdown">
                                          <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                                             <i class="icon-base bx bx-dots-vertical"></i>
                                          </button>
                                          <div class="dropdown-menu dropdown-menu-end">
                                             <a href="#" class="dropdown-item"
                                                @click.prevent="openEmployeeDeductionEditModal(deduction)">
                                                <i class="icon-base bx bx-edit-alt me-2"></i>Edit
                                             </a>
                                             <a title="Delete" href="#" class="dropdown-item text-danger"
                                                @click.prevent="deleteEmployeeDeduction(deduction)">
                                                <i class="icon-base bx bx-trash me-2"></i>Del
                                             </a>
                                          </div>
                                       </div>

                                    </td>
                                 </tr>
                              </tbody>
                           </table>

                        </div>
                     </div>
                  </div>
               </div>

            </div>
         </div>
      </div>
   </DefaultLayout>

   <!-- Start Create Modal -->
   <div class="modal fade" id="create-credentials-modal" data-bs-backdrop="static" tabindex="-1"
      aria-labelledby="create-credentials-modal-label" aria-hidden="true" ref="createCredentialsModal">
      <div class="modal-dialog modal-body-simple">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="create-credentials-modal-label">System Access</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                  @click="credentialsFormCleanUp"></button>
            </div>
            <div class="modal-body">
               <form id="createForm" @submit.prevent="storeCredentials">
                  <div class="mb-3">
                     <label for="password" class="form-label">Password</label>
                     <input id="password" type="password" v-model="form.password" class="form-control">
                     <div v-if="form.errors.password" class="text-danger">{{ form.errors.password }}</div>
                  </div>

                  <div class="mb-3">
                     <label class="row d-flex">
                        <span class="col">
                           <span class="fw-bold me-1">Activate Account</span>
                        </span>
                        <span class="col-auto">
                           <label class="form-check form-switch">
                              <input v-model="form.has_system_access" class="form-check-input" type="checkbox">
                           </label>
                        </span>
                        <span class="form-check-description">When enabled, the employee can login to the system.</span>
                     </label>
                  </div>
               </form>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal"
                  @click="credentialsFormCleanUp">
                  Close
               </button>
               <button type="button" class="btn btn-primary" @click.prevent="storeCredentials"
                  :disabled="form.processing">
                  Submit
               </button>
            </div>
         </div>
      </div>
   </div>
   <!-- End Create Modal -->

   <!-- start create employee income modal-->
   <div class="modal fade" id="create-employee-income-modal" data-bs-backdrop="static" tabindex="-1"
      aria-labelledby="create-employee-income-modal-label" aria-hidden="true" ref="createEmployeeIncomeModal">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="create-employee-income-modal-label">Add Income</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                  @click.prevent="formCleanUp"></button>
            </div>
            <div class="modal-body">
               <form id="createForm" @submit.prevent="createEmployeeIncome">
                  <div class="mb-3">
                     <label for="income" class="form-label">Income</label>
                     <select class="form-select" v-model="employeeIncomeForm.income_id" id="income">
                        <option :key="income.id" :value="income.id" v-for="income in incomes">{{ income.name }}</option>
                     </select>
                     <div v-if="employeeIncomeForm.errors.income_id" class="text-danger">{{
                        employeeIncomeForm.errors.income_id }}</div>
                  </div>
                  <div class="mb-3">
                     <label for="amount" class="form-label">Amount</label>
                     <input id="amount" type="text" v-model="employeeIncomeForm.amount" class="form-control">
                     <div v-if="employeeIncomeForm.errors.amount" class="text-danger">{{
                        employeeIncomeForm.errors.amount }}</div>
                  </div>
               </form>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" @click="formCleanUp">
                  Close
               </button>
               <button type="button" class="btn btn-primary" @click.prevent="createEmployeeIncome">
                  Submit
               </button>
            </div>
         </div>
      </div>
   </div>
   <!-- end create employee income modal-->

   <!-- start create employee deduction modal-->
   <div class="modal fade" id="create-employee-dedection-modal" data-bs-backdrop="static" tabindex="-1"
      aria-labelledby="create-employee-dedection-modal-label" aria-hidden="true" ref="createEmployeeDeductionModal">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="create-employee-dedection-modal-label">Add Deduction</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                  @click.prevent="formCleanUp"></button>
            </div>
            <div class="modal-body">
               <form id="createDeductionForm" @submit.prevent="createEmployeeDeduction">
                  <div class="mb-3">
                     <label for="income" class="form-label">Deduction</label>
                     <select class="form-select" v-model="employeeDeductionForm.deduction_id" id="income">
                        <option :key="deduction.id" :value="deduction.id" v-for="deduction in deductions">{{
                           deduction.name }}</option>
                     </select>
                     <div v-if="employeeDeductionForm.errors.deduction_id" class="text-danger">{{
                        employeeDeductionForm.errors.deduction_id }}</div>
                  </div>
                  <div class="mb-3">
                     <label for="amount" class="form-label">Amount</label>
                     <input id="amount" type="text" v-model="employeeDeductionForm.amount" class="form-control">
                     <div v-if="employeeDeductionForm.errors.amount" class="text-danger">{{
                        employeeDeductionForm.errors.amount }}</div>
                  </div>
               </form>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" @click="formCleanUp">
                  Close
               </button>
               <button type="button" class="btn btn-primary" @click.prevent="createEmployeeDeduction">
                  Submit
               </button>
            </div>
         </div>
      </div>
   </div>
   <!-- end create employee income modal-->

   <!-- start edit employee income modal-->
   <div class="modal fade" id="edit-employee-income-modal" data-bs-backdrop="static" tabindex="-1"
      aria-labelledby="edit-employee-income-modal-label" aria-hidden="true" ref="editEmployeeIncomeModal">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="edit-employee-income-modal-label">Edit Income</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                  @click.prevent="formCleanUp"></button>
            </div>
            <form id="createForm" @submit.prevent="updateEmployeeIncome">
               <div class="modal-body">
                  <div class="mb-3">
                     <label for="income" class="form-label">Income</label>
                     <select class="form-select" v-model="employeeIncomeForm.income_id" id="income">
                        <option :key="income.id" :value="income.id" v-for="income in incomes">{{ income.name }}</option>
                     </select>
                     <div v-if="employeeIncomeForm.errors.income_id" class="text-danger">{{
                        employeeIncomeForm.errors.income_id }}</div>
                  </div>
                  <div class="mb-3">
                     <label for="amount" class="form-label">Amount</label>
                     <input id="amount" type="text" v-model="employeeIncomeForm.amount" class="form-control">
                     <div v-if="employeeIncomeForm.errors.amount" class="text-danger">{{
                        employeeIncomeForm.errors.amount }}</div>
                  </div>

               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" @click="formCleanUp">
                     Close
                  </button>
                  <button class="btn btn-primary">
                     Update
                  </button>
               </div>
            </form>
         </div>
      </div>
   </div>
   <!-- end edit employee income modal-->
   <!-- start create employee deduction modal-->
   <div class="modal fade" id="edit-employee-deduction-modal" data-bs-backdrop="static" tabindex="-1"
      aria-labelledby="edit-employee-deduction-modal-label" aria-hidden="true" ref="editEmployeeDeductionModal">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="edit-employee-deduction-modal-label">Edit Deduction</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                  @click.prevent="formCleanUp"></button>
            </div>
            <div class="modal-body">
               <form id="createDeductionForm" @submit.prevent="updateEmployeeDeduction">
                  <div class="mb-3">
                     <label for="income" class="form-label">Deduction</label>
                     <select class="form-select" v-model="employeeDeductionForm.deduction_id" id="income">
                        <option :key="deduction.id" :value="deduction.id" v-for="deduction in deductions">{{
                           deduction.name }}</option>
                     </select>
                     <div v-if="employeeDeductionForm.errors.deduction_id" class="text-danger">{{
                        employeeDeductionForm.errors.deduction_id }}</div>
                  </div>
                  <div class="mb-3">
                     <label for="amount" class="form-label">Amount</label>
                     <input id="amount" type="text" v-model="employeeDeductionForm.amount" class="form-control">
                     <div v-if="employeeDeductionForm.errors.amount" class="text-danger">{{
                        employeeDeductionForm.errors.amount }}</div>
                  </div>
               </form>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" @click="formCleanUp">
                  Close
               </button>
               <button type="button" class="btn btn-primary" @click.prevent="updateEmployeeDeduction">
                  update
               </button>
            </div>
         </div>
      </div>
   </div>
   <!-- end create employee income modal-->
</template>

<script>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { Inertia } from "@inertiajs/inertia";
import axios from "axios";
import { Modal } from "bootstrap";
import { error } from "jquery";

export default {
   components: { DefaultLayout, Head, Link },
   props: ['teacher', 'employee'],

   data() {
      return {
         form: useForm({
            has_system_access: null,
            password: null,
         }),
         employeeIncomeForm: useForm({
            id: null,
            employee_id: this.employee.id,
            income_id: null,
            amount: ''
         }),
         employeeDeductionForm: useForm({
            id: null,
            employee_id: this.employee.id,
            deduction_id: null,
            amount: ''
         }),
         emergencyContacts: [],
         qualificationDetails: [],
         workHistories: [],

         dataFetched: false,
         openAccordion: 'employee',

         incomes: [],
         employeeIncomes: [],
         deductions: [],
         employeeDeductions: []
      };
   },
   beforeDestroy() {
      // Clean up the listener when the component is destroyed
      Inertia.off('navigate', this.handleNavigation);
   },

   mounted() {
      this.fetchAllData();
   },
   methods: {
      handleNavigation(event) {
         const targetUrl = '/admin/employees/teachers/' + this.teacher.hashid;
         if (event.detail?.page.url === targetUrl && !this.dataFetched) {
            this.fetchAllData();
         }
      },
      fetchAllData() {
         this.fetchedEmergencyContactDetails();
         this.fetchedQualificationDetails();
         this.fetchedWorkHistoryDetails();
         this.dataFetched = true;
      },
      fetchedEmergencyContactDetails() {
         if (!this.employee) {
            return;
         }
         axios.get('/datatable/emergency-contacts', {
            params: {
               filter: {
                  employee_id: this.employee.id,
               },
            },
         }).then(({ data }) => {
            this.emergencyContacts = data.data;
         }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the employee emergency contact details.')
         });
      },
      fetchedQualificationDetails() {
         if (!this.employee) {
            return;
         }
         axios.get('/datatable/employee-qualifications', {
            params: {
               filter: {
                  employee_id: this.employee.id,
               },
            },
         }).then(({ data }) => {
            this.qualificationDetails = data.data;
         }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the employee qualifications.')
         });
      },
      fetchedWorkHistoryDetails() {
         if (!this.employee) {
            return;
         }
         axios.get('/datatable/work-histories', {
            params: {
               filter: {
                  employee_id: this.employee.id,
               },
            },
         }).then(({ data }) => {
            this.workHistories = data.data;
         }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the employee work history details.')
         });
      },
      toggleAccordion(section) {
         this.openAccordion = this.openAccordion === section ? null : section;
      },
      showCreateCredentialsModal() {
         this.form.has_system_access = this.employee.has_system_access;
         const modalElement = this.$refs.createCredentialsModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      storeCredentials() {
         this.form.post(route('admin.employees.system-access', this.employee.hashid), {
            onSuccess: () => {
               this.form.reset();
               this.form.clearErrors();
               const modalElement = this.$refs.createCredentialsModal;
               const modalInstance = Modal.getInstance(modalElement);
               modalInstance.hide();
               this.$toast.success('Employee now has system access', 'Success')
            },
            onError: (errors) => {
               this.$toast.error('An error occurred. Please try again', 'Error')
            },
         });
      },
      revokeSystemAccess() {
         this.$toast.question('Revoke system access for ' + this.teacher.first_name + ' ?', 'Caution!').then(() => {
            this.$inertia.patch(route('admin.employees.revoke-system-access', this.employee.hashid), {
               onSuccess: () => {
                  this.$inertia.reload();
                  setTimeout(() => {
                     this.$toast.success('System access revoked', 'Success');
                  }, 400);
               },
               onError: (error) => {
                  console.log(error);
                  this.$toast.error('An error occurred. Please try again', 'Error');
               },
            })
         });
      },
      credentialsFormCleanUp() {
         this.form.reset()
      },
      openEmployeeIncomeModal() {
         Modal.getOrCreateInstance(this.$refs.createEmployeeIncomeModal).show()
      },
      openEmployeeDeductionModal() {
         Modal.getOrCreateInstance(this.$refs.createEmployeeDeductionModal).show()
      },
      loadIncomes() {
         axios.get('/datatable/settings/incomes')
            .then((res) => {
               this.incomes = res.data.data
            })
            .catch((error) => {
               console.error(error)
            })
      },
      loadDeductions() {
         axios.get('/datatable/settings/deductions')
            .then((res) => {
               this.deductions = res.data.data
            })
            .catch((error) => {
               console.error(error)
            })
      },
      loadEmployeeIncomes() {
         axios.get('/datatable/employee/incomes', {
            params: {
               filter: {
                  employee_id: this.employee.id,
               },
            },
         }).then(({ data }) => {
            this.employeeIncomes = data.data;
         }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the employee Incomes.')
         });
      },
      loadEmployeeDeductions() {
         axios.get('/datatable/employee/deductions', {
            params: {
               filter: {
                  employee_id: this.employee.id,
               },
            },
         }).then(({ data }) => {
            this.employeeDeductions = data.data;
         }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the employee deductions.')
         });
      },
      createEmployeeIncome() {
         this.employeeIncomeForm.post(route('admin.employee.income'), {
            onSuccess: () => {
               Modal.getOrCreateInstance(this.$refs.createEmployeeIncomeModal).hide()
               this.$toast.success('Employee Income has been created')
               this.loadEmployeeIncomes()
               this.employeeIncomeForm.reset()
            },
            onError: (error) => {
               console.log(error)
            }
         })
      },
      formatCurrency(amount) {
         return new Intl.NumberFormat('KES').format(amount)
      },
      updateEmployeeIncome() {
         this.employeeIncomeForm.patch(route('admin.employee.income.update', this.employeeIncomeForm.id), {
            onSuccess: () => {
               Modal.getOrCreateInstance(this.$refs.editEmployeeIncomeModal).hide()
               this.$toast.success('Employee Income has been updated')
               this.loadEmployeeIncomes()
            },
            onError: (error) => {
               console.log(error)
            }
         })
      },
      openEmployeeIncomeEditModal(income) {
         this.employeeIncomeForm.id = income.hashid
         this.employeeIncomeForm.income_id = income.income_id
         this.employeeIncomeForm.amount = income.amount / 100
         Modal.getOrCreateInstance(this.$refs.editEmployeeIncomeModal).show()
      },
      openEmployeeDeductionEditModal(deduction) {
         this.employeeDeductionForm.id = deduction.hashid
         this.employeeDeductionForm.amount = deduction.amount / 100
         this.employeeDeductionForm.deduction_id = deduction.deduction_id
         Modal.getOrCreateInstance(this.$refs.editEmployeeDeductionModal).show()
      },
      deleteEmployeeIncome(income) {

         this.$toast.question('Are you sure you want to delete' + ' ?', 'Confirm!')
            .then(() => {
               this.employeeIncomeForm.delete(route('admin.employee.income.delete', income.hashid), {
                  onSuccess: () => {
                     this.$toast.success('Employee Income has been Deleted')
                     this.loadEmployeeIncomes()
                  },
                  onError: () => {
                     this.$toast.success('Error Occured,Failed to Delete')
                  }
               })
            })
      },

      createEmployeeDeduction() {
         this.employeeDeductionForm.post(route('admin.employee.deduction'), {
            onSuccess: () => {
               Modal.getOrCreateInstance(this.$refs.createEmployeeDeductionModal).hide()
               this.$toast.success('Employee Deduction has been created')
               this.loadEmployeeDeductions()
               this.employeeDeductionForm.reset()
            },
            onError: (error) => {
               this.$toast.error('Error Occured')
            }
         })
      },
      updateEmployeeDeduction() {
         this.employeeDeductionForm.patch(route('admin.employee.deductions.update', this.employeeDeductionForm.id), {
            onSuccess: () => {
               Modal.getOrCreateInstance(this.$refs.editEmployeeDeductionModal).hide()
               this.$toast.success('Employee Deduction has been updated')
               this.loadEmployeeDeductions()
            },
            onError: (error) => {
               this.$toast.error('Something went wrong')
               console.log(error)
            }
         })
      },
      deleteEmployeeDeduction(deduction) {

         this.$toast.question('Are you sure you want to delete' + ' ?', 'Confirm!')
            .then(() => {
               this.employeeDeductionForm.delete(route('admin.employee.deductions.delete', deduction.hashid), {
                  onSuccess: () => {
                     this.$toast.success('Employee Deduction has been Deleted')
                     this.loadEmployeeDeductions()
                  },
                  onError: () => {
                     this.$toast.success('Error Occured,Failed to Delete')
                  }
               })
            })
      },
   },
   watch: {
      openAccordion(tab) {
         switch (tab) {
            case 'incomes':
               this.loadIncomes()
               this.loadEmployeeIncomes()
               break
            case 'deductions':
               this.loadDeductions()
               this.loadEmployeeDeductions()
               break
            default:
               break
         }
      }
   },
}
</script>

<style scoped>
.accordion-collapse {
   display: none;
}

.accordion-collapse.show {
   display: block;
}
</style>
