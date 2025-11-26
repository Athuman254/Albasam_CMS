<template>
   <Head title="Edit Admission Details"/>
   
   <DefaultLayout>
      <div class="row">
         <div class="col-xxl-12">
            <h3 class="mb-0">Edit Student Admission</h3>
            <nav class="">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                     <Link :href="route('admin.dashboard')">Home</Link>
                  </li>
                  <li class="breadcrumb-item">
                     <Link :href="route('admin.admissions.index')">Student Admissions</Link>
                  </li>
                  <li class="breadcrumb-item text-primary">
                     Edit Admission
                  </li>
               </ol>
            </nav>
            
            <!-- Error State -->
            <div class="col-xxl-12" v-if="error">
               <div class="card">
                  <div class="card-body text-center py-5">
                     <i class="bx bx-error-circle text-danger mb-3" style="font-size: 3rem;"></i>
                     <h4 class="text-danger">Error Loading Admission</h4>
                     <p class="text-muted">{{ error }}</p>
                     <Link :href="route('admin.admissions.index')" class="btn btn-primary">
                        Back to Admissions
                     </Link>
                  </div>
               </div>
            </div>

            <!-- No Student Data -->
            <div class="col-xxl-12" v-else-if="!studentAdmission || !studentAdmission.student">
               <div class="card">
                  <div class="card-body text-center py-5">
                     <i class="bx bx-user-x text-muted mb-3" style="font-size: 3rem;"></i>
                     <h4 class="text-muted">No Student Data Found</h4>
                     <p class="text-muted">This admission record doesn't have an associated student.</p>
                     <Link :href="route('admin.admissions.index')" class="btn btn-primary">
                        Back to Admissions
                     </Link>
                  </div>
               </div>
            </div>

            <!-- Edit Form -->
            <div class="col-xxl-12" v-else>
               <div class="card">
                  <div class="card-header border-bottom">
                     <div class="form-header">
                        <div class="stepIndicator"
                             :class="{ 'active': currentStep === 1, 'finish': currentStep > 1 }">
                           <div class="square">1</div>
                           <span :class="{ 'text-primary': currentStep > 1 }">Admission Details</span>
                        </div>
                        <div class="stepIndicator"
                             :class="{ 'active': currentStep === 2, 'finish': currentStep > 2 }">
                           <div class="square">2</div>
                           <span :class="{ 'text-primary': currentStep > 2 }">Student Details</span>
                        </div>
                        <div class="stepIndicator"
                             :class="{ 'active': currentStep === 3, 'finish': currentStep > 3 }">
                           <div class="square">3</div>
                           <span :class="{ 'text-primary': currentStep > 3 }">Guardian Details</span>
                        </div>
                        <div class="stepIndicator"
                             :class="{ 'active': currentStep === 4, 'finish': currentStep > 4 }">
                           <div class="square">4</div>
                           <span :class="{ 'text-primary': currentStep > 4 }">Other Details</span>
                        </div>
                     </div>
                     <div class="progress-bar">
                        <div class="progress" :style="{ width: progressPercentage + '%' }"></div>
                     </div>
                  </div>
                  
                  <div class="card-body">
                     <div class="row">
                        <div class="col-md-12">
                           <!-- Step 1: Admission Details -->
                           <transition name="fade">
                              <div v-if="currentStep === 1" class="step">
                                 <div class="row">
                                    <div class="mb-4">
                                       <h5 class="mb-0">Admission Details</h5>
                                       <small class="text-muted">Review and update admission information</small>
                                    </div>
                                    
                                    <!-- Admission Information -->
                                    <div class="col-md-6">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="divisionId">Division <span class="text-danger ms-1">*</span></label>
                                          <v-select
                                             id="divisionId"
                                             v-model="form.registration_details.division_id"
                                             :options="divisions"
                                             label="name"
                                             :reduce="(option) => option.id"
                                          ></v-select>
                                          <div v-if="form.errors['registration_details.division_id']" class="text-danger">
                                             {{ form.errors['registration_details.division_id'] }}
                                          </div>
                                       </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1">Admission Date</label>
                                          <div class="form-control bg-light">
                                             {{ studentAdmission?.formatted_date || 'N/A' }}
                                          </div>
                                          <small class="text-muted">Admission date cannot be changed</small>
                                       </div>
                                    </div>

                                    <!-- Admission Status -->
                                    <div class="col-md-12">
                                       <div class="card bg-light">
                                          <div class="card-body">
                                             <h6 class="card-title">Admission Status</h6>
                                             <div class="d-flex align-items-center">
                                                <span class="badge" :class="admissionStatusClass">
                                                   {{ admissionStatusText }}
                                                </span>
                                                <span class="ms-2 text-muted">{{ admissionStatusDescription }}</span>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </transition>

                           <!-- Step 2: Student Details -->
                           <transition name="fade">
                              <div v-if="currentStep === 2" class="step">
                                 <div class="row">
                                    <div class="mb-4">
                                       <h5 class="mb-0">Student Details</h5>
                                       <small class="text-muted">Update student personal information</small>
                                    </div>
                                    
                                    <!-- Personal Information -->
                                    <div class="col-md-4">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="firstName">First Name <span class="text-danger ms-1">*</span></label>
                                          <input type="text" id="firstName" class="form-control"
                                                 v-model="form.student.first_name"/>
                                          <div v-if="form.errors['student.first_name']" class="text-danger">
                                             {{ form.errors['student.first_name'] }}
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="middleName">Middle Name</label>
                                          <input type="text" id="middleName" class="form-control"
                                                 v-model="form.student.middle_name"/>
                                          <div v-if="form.errors['student.middle_name']" class="text-danger">
                                             {{ form.errors['student.middle_name'] }}
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="lastName">Last Name <span class="text-danger ms-1">*</span></label>
                                          <input type="text" id="lastName" class="form-control"
                                                 v-model="form.student.last_name"/>
                                          <div v-if="form.errors['student.last_name']" class="text-danger">
                                             {{ form.errors['student.last_name'] }}
                                          </div>
                                       </div>
                                    </div>

                                    <!-- Admission Information -->
                                    <div class="col-md-4">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="admissionNumber">Admission Number <span class="text-danger ms-1">*</span></label>
                                          <input type="text" id="admissionNumber" class="form-control bg-light" v-model="form.student.admission_number" readonly />
                                          <small class="text-muted">Admission number cannot be changed</small>
                                          <div v-if="form.errors['student.admission_number']" class="text-danger">{{ form.errors['student.admission_number'] }}</div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="classId">Current Class <span class="text-danger ms-1">*</span></label>
                                          <v-select
                                             id="classId"
                                             v-model="form.student.rank_id"
                                             :options="filteredRanks"
                                             label="name"
                                             :reduce="(option) => option.id"
                                          >
                                             <template #option="option">
                                                {{ option.name }} {{ option.stream?.name || '' }}
                                             </template>
                                             <template #selected-option="option">
                                                {{ option.name }} {{ option.stream?.name || '' }}
                                             </template>
                                          </v-select>
                                          <div v-if="form.errors['student.rank_id']" class="text-danger">{{ form.errors['student.rank_id'] }}</div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="genderId">Gender <span class="text-danger ms-1">*</span></label>
                                          <v-select
                                             id="genderId"
                                             v-model="form.student.gender_id"
                                             :options="genders"
                                             label="name"
                                             :reduce="(option) => option.id"
                                          ></v-select>
                                          <div v-if="form.errors['student.gender_id']" class="text-danger">
                                             {{ form.errors['student.gender_id'] }}
                                          </div>
                                       </div>
                                    </div>

                                    <!-- Personal Details -->
                                    <div class="col-md-4">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="religionId">Religion <span class="text-danger ms-1">*</span></label>
                                          <v-select
                                             id="religionId"
                                             v-model="form.student.religion_id"
                                             :options="religions"
                                             label="name"
                                             :reduce="(option) => option.id"
                                          ></v-select>
                                          <div v-if="form.errors['student.religion_id']" class="text-danger">
                                             {{ form.errors['student.religion_id'] }}
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="dateOfBirth">Date Of Birth</label>
                                          <date-picker
                                             id="dateOfBirth"
                                             form-class="shadow-sm"
                                             :value="form.student.date_of_birth"
                                             :max-date="new Date()"
                                             @on-change="function(dateObj, dateStr) { form.student.date_of_birth = dateStr }"
                                          ></date-picker>
                                          <div v-if="form.errors['student.date_of_birth']" class="text-danger">
                                             {{ form.errors['student.date_of_birth'] }}
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="birthCertificateNumber">Birth Certificate Number</label>
                                          <input type="text" id="birthCertificateNumber" class="form-control"
                                                 v-model="form.student.birth_certificate_number"/>
                                          <div v-if="form.errors['student.birth_certificate_number']" class="text-danger">
                                             {{ form.errors['student.birth_certificate_number'] }}
                                          </div>
                                       </div>
                                    </div>

                                    <!-- Address Information -->
                                    <div class="col-md-4">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="citizenship">Citizenship</label>
                                          <input type="text" id="citizenship" class="form-control"
                                                 v-model="form.student.citizenship"/>
                                          <div v-if="form.errors['student.citizenship']" class="text-danger">
                                             {{ form.errors['student.citizenship'] }}
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="county">County</label>
                                          <input type="text" id="county" class="form-control" v-model="form.student.county"/>
                                          <div v-if="form.errors['student.county']" class="text-danger">
                                             {{ form.errors['student.county'] }}
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-4">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="ward">Ward</label>
                                          <input type="text" id="ward" class="form-control" v-model="form.student.ward"/>
                                          <div v-if="form.errors['student.ward']" class="text-danger">
                                             {{ form.errors['student.ward'] }}
                                          </div>
                                       </div>
                                    </div>

                                    <div class="col-md-6">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="permanentAddress">Permanent Address</label>
                                          <textarea id="permanentAddress" class="form-control" rows="3"
                                                 v-model="form.student.permanent_address"></textarea>
                                          <div v-if="form.errors['student.permanent_address']" class="text-danger">
                                             {{ form.errors['student.permanent_address'] }}
                                          </div>
                                       </div>
                                    </div>

                                    <!-- Academic Information -->
                                    <div class="col-md-3">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="kcpeScore">KCPE Score</label>
                                          <input type="text" id="kcpeScore" class="form-control"
                                                 v-model="form.student.kcpe_score"/>
                                          <div v-if="form.errors['student.kcpe_score']" class="text-danger">
                                             {{ form.errors['student.kcpe_score'] }}
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="previousSchool">Previous School</label>
                                          <input type="text" id="previousSchool" class="form-control"
                                                 v-model="form.student.previous_school"/>
                                          <div v-if="form.errors['student.previous_school']" class="text-danger">
                                             {{ form.errors['student.previous_school'] }}
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </transition>

                           <!-- Step 3: Guardian Details -->
                           <transition name="fade">
                              <div v-if="currentStep === 3" class="step">
                                 <div class="mb-4">
                                    <div class="row">
                                       <div class="col-md-9">
                                          <h5 class="mb-0">Guardian Details</h5>
                                          <small class="text-muted">Manage student guardians and their information</small>
                                       </div>
                                       <div class="col-md-3">
                                          <button type="button" class="btn btn-primary float-end" @click="addGuardian">
                                             <i class="bx bx-plus me-1"></i> Add Guardian
                                          </button>
                                       </div>
                                    </div>
                                 </div>

                                 <div v-if="guardianDetails.length === 0" class="text-center py-5">
                                    <i class="bx bx-group text-muted mb-3" style="font-size: 3rem;"></i>
                                    <h5 class="text-muted">No Guardians Added</h5>
                                    <p class="text-muted">Click "Add Guardian" to add guardian information.</p>
                                 </div>

                                 <div v-for="(guardian, index) in guardianDetails" :key="index" class="card mb-4">
                                    <div class="card-header bg-light">
                                       <div class="d-flex justify-content-between align-items-center">
                                          <h6 class="mb-0">Guardian {{ index + 1 }}</h6>
                                          <div class="btn-group">
                                             <button v-if="editingGuardianIndex !== index" type="button" class="btn btn-sm btn-outline-secondary" @click="editGuardian(guardian, index)">
                                                <i class="bx bx-edit me-1"></i> Edit
                                             </button>
                                             <button v-if="editingGuardianIndex === index" type="button" class="btn btn-sm btn-outline-secondary me-2" @click="cancelGuardianEdit">
                                                Cancel
                                             </button>
                                             <button v-if="editingGuardianIndex === index" type="button" class="btn btn-sm btn-success me-2" @click="updateGuardianDetails(guardian, index)">
                                                <i class="bx bx-check me-1"></i> Save
                                             </button>
                                             <button type="button" class="btn btn-sm btn-outline-danger" @click="deleteGuardian(guardian)">
                                                <i class="bx bx-trash"></i>
                                             </button>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="card-body">
                                       <div class="row">
                                          <div class="col-md-4">
                                             <div class="form-group mb-3">
                                                <label class="form-label-md mb-1">First Name</label>
                                                <input type="text" class="form-control" :disabled="editingGuardianIndex !== index" v-model="guardian.first_name"/>
                                                <div v-if="guardianErrors[index]?.first_name" class="text-danger">{{ guardianErrors[index].first_name }}</div>
                                             </div>
                                          </div>
                                          <div class="col-md-4">
                                             <div class="form-group mb-3">
                                                <label class="form-label-md mb-1">Middle Name</label>
                                                <input type="text" class="form-control" :disabled="editingGuardianIndex !== index" v-model="guardian.middle_name"/>
                                                <div v-if="guardianErrors[index]?.middle_name" class="text-danger">{{ guardianErrors[index].middle_name }}</div>
                                             </div>
                                          </div>
                                          <div class="col-md-4">
                                             <div class="form-group mb-3">
                                                <label class="form-label-md mb-1">Last Name</label>
                                                <input type="text" class="form-control" :disabled="editingGuardianIndex !== index" v-model="guardian.last_name"/>
                                                <div v-if="guardianErrors[index]?.last_name" class="text-danger">{{ guardianErrors[index].last_name }}</div>
                                             </div>
                                          </div>
                                          <div class="col-md-4">
                                             <div class="form-group mb-3">
                                                <label class="form-label-md mb-1">Relationship</label>
                                                <v-select
                                                   :disabled="editingGuardianIndex !== index"
                                                   v-model="guardian.relationship_id"
                                                   :options="relationships"
                                                   label="name"
                                                   :reduce="(option) => option.id"
                                                ></v-select>
                                                <div v-if="guardianErrors[index]?.relationship_id" class="text-danger">{{ guardianErrors[index].relationship_id }}</div>
                                             </div>
                                          </div>
                                          <div class="col-md-4">
                                             <div class="form-group mb-3">
                                                <label class="form-label-md mb-1">Email</label>
                                                <input type="email" class="form-control" :disabled="editingGuardianIndex !== index" v-model="guardian.email"/>
                                                <div v-if="guardianErrors[index]?.email" class="text-danger">{{ guardianErrors[index].email }}</div>
                                             </div>
                                          </div>
                                          <div class="col-md-4">
                                             <div class="form-group mb-3">
                                                <label class="form-label-md mb-1">Phone</label>
                                                <input type="text" class="form-control" :disabled="editingGuardianIndex !== index" v-model="guardian.phone"/>
                                                <div v-if="guardianErrors[index]?.phone" class="text-danger">{{ guardianErrors[index].phone }}</div>
                                             </div>
                                          </div>
                                          <div class="col-md-4">
                                             <div class="form-group mb-3">
                                                <label class="form-label-md mb-1">ID Number</label>
                                                <input type="text" class="form-control" :disabled="editingGuardianIndex !== index" v-model="guardian.identification_number"/>
                                                <div v-if="guardianErrors[index]?.identification_number" class="text-danger">{{ guardianErrors[index].identification_number }}</div>
                                             </div>
                                          </div>
                                          <div class="col-md-4">
                                             <div class="form-group mb-3">
                                                <label class="form-label-md mb-1">Profession</label>
                                                <input type="text" class="form-control" :disabled="editingGuardianIndex !== index" v-model="guardian.profession"/>
                                                <div v-if="guardianErrors[index]?.profession" class="text-danger">{{ guardianErrors[index].profession }}</div>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </transition>

                           <!-- Step 4: Other Details -->
                           <transition name="fade">
                              <div v-if="currentStep === 4" class="step">
                                 <div class="row">
                                    <div class="mb-4">
                                       <h5 class="mb-0">Other Details</h5>
                                       <small class="text-muted">Additional student information and siblings</small>
                                    </div>

                                    <!-- Medical & Personal Details -->
                                    <div class="col-md-6">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="physicalDisability">Physical Disability</label>
                                          <input type="text" id="physicalDisability" class="form-control"
                                                 v-model="form.other_details.physical_disability" placeholder="None"/>
                                          <div v-if="form.errors['other_details.physical_disability']" class="text-danger">
                                             {{ form.errors['other_details.physical_disability'] }}
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-6">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="hobby">Special Interest/Hobby</label>
                                          <input type="text" id="hobby" class="form-control"
                                                 v-model="form.other_details.hobby" placeholder="e.g., Sports, Music, Art"/>
                                          <div v-if="form.errors['other_details.hobby']" class="text-danger">
                                             {{ form.errors['other_details.hobby'] }}
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-6">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="medicalDetails">Medical Details</label>
                                          <textarea rows="4" id="medicalDetails" class="form-control"
                                                    v-model="form.other_details.medical_details" placeholder="Any medical conditions or allergies"/>
                                          <div v-if="form.errors['other_details.medical_details']" class="text-danger">
                                             {{ form.errors['other_details.medical_details'] }}
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-6">
                                       <div class="form-group mb-3">
                                          <label class="form-label-md mb-1" for="characterBook">Character Book</label>
                                          <textarea rows="4" id="characterBook" class="form-control"
                                                    v-model="form.other_details.character_book" placeholder="Character assessment or notes"/>
                                          <div v-if="form.errors['other_details.character_book']" class="text-danger">
                                             {{ form.errors['other_details.character_book'] }}
                                          </div>
                                       </div>
                                    </div>

                                    <!-- Siblings Section -->
                                    <div class="col-12">
                                       <div class="card">
                                          <div class="card-header bg-light">
                                             <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">Siblings Information</h6>
                                                <button type="button" class="btn btn-sm btn-primary" @click="addSibling">
                                                   <i class="bx bx-plus me-1"></i> Add Sibling
                                                </button>
                                             </div>
                                          </div>
                                          <div class="card-body">
                                             <div v-if="form.other_details.siblings.length === 0" class="text-center py-4">
                                                <p class="text-muted">No siblings added. Click "Add Sibling" to add sibling information.</p>
                                             </div>
                                             
                                             <div v-for="(sibling, index) in form.other_details.siblings" :key="index" class="border rounded p-3 mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                   <h6 class="mb-0">Sibling {{ index + 1 }}</h6>
                                                   <button type="button" v-if="index > 0" class="btn btn-sm btn-outline-danger" @click="removeSibling(index)">
                                                      <i class="bx bx-trash"></i>
                                                   </button>
                                                </div>
                                                <div class="row g-3">
                                                   <div class="col-md-3">
                                                      <div class="form-group mb-0">
                                                         <label class="form-label-sm mb-1">Name</label>
                                                         <input type="text" class="form-control form-control-sm" v-model="sibling.name" placeholder="Full name"/>
                                                         <div v-if="getSiblingErrors(index, 'name')" class="text-danger small">{{ getSiblingErrors(index, 'name') }}</div>
                                                      </div>
                                                   </div>
                                                   <div class="col-md-2">
                                                      <div class="form-group mb-0">
                                                         <label class="form-label-sm mb-1">Age</label>
                                                         <input type="number" class="form-control form-control-sm" v-model="sibling.age" placeholder="Age"/>
                                                         <div v-if="getSiblingErrors(index, 'age')" class="text-danger small">{{ getSiblingErrors(index, 'age') }}</div>
                                                      </div>
                                                   </div>
                                                   <div class="col-md-2">
                                                      <div class="form-group mb-0">
                                                         <label class="form-label-sm mb-1">Gender</label>
                                                         <select class="form-select form-select-sm" v-model="sibling.gender_id">
                                                            <option value="">Select</option>
                                                            <option v-for="gender in genders" :key="gender.id" :value="gender.id">
                                                               {{ gender.name }}
                                                            </option>
                                                         </select>
                                                         <div v-if="getSiblingErrors(index, 'gender_id')" class="text-danger small">{{ getSiblingErrors(index, 'gender_id') }}</div>
                                                      </div>
                                                   </div>
                                                   <div class="col-md-3">
                                                      <div class="form-group mb-0">
                                                         <label class="form-label-sm mb-1">Current School</label>
                                                         <input type="text" class="form-control form-control-sm" v-model="sibling.current_school" placeholder="School name"/>
                                                         <div v-if="getSiblingErrors(index, 'current_school')" class="text-danger small">{{ getSiblingErrors(index, 'current_school') }}</div>
                                                      </div>
                                                   </div>
                                                   <div class="col-md-2">
                                                      <div class="form-group mb-0">
                                                         <label class="form-label-sm mb-1">Current Class</label>
                                                         <input type="text" class="form-control form-control-sm" v-model="sibling.current_class" placeholder="Class/Grade"/>
                                                         <div v-if="getSiblingErrors(index, 'current_class')" class="text-danger small">{{ getSiblingErrors(index, 'current_class') }}</div>
                                                      </div>
                                                   </div>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </transition>

                           <!-- Navigation -->
                           <div class="form-footer px-0 mt-4">
                              <div class="row">
                                 <div class="col-md-6">
                                    <button type="button" class="btn btn-secondary" :class="{ 'disabled': currentStep === 1}" @click="prevStep">
                                       <i class="bx bx-chevron-left me-1"></i> Previous
                                    </button>
                                 </div>
                                 <div class="col-md-6 text-end">
                                    <button v-if="currentStep !== 4" type="button" class="btn btn-primary" @click="nextStep">
                                       Next <i class="bx bx-chevron-right ms-1"></i>
                                    </button>
                                    <button v-else type="button" class="btn btn-success" @click.prevent="submitForm" :disabled="form.processing">
                                       <i v-if="form.processing" class="bx bx-loader bx-spin me-1"></i>
                                       {{ form.processing ? 'Updating...' : 'Update Admission' }}
                                    </button>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Guardian Modal -->
      <div class="modal fade" id="add-guardian-modal" data-bs-backdrop="static" tabindex="-1" ref="addGuardianModal">
         <div class="modal-dialog modal-lg">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title">Add New Guardian</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" @click="guardianFormCleanUp"></button>
               </div>
               <div class="modal-body">
                  <div class="row">
                     <div class="col-md-6">
                        <div class="mb-3">
                           <label class="form-label">First Name <span class="text-danger">*</span></label>
                           <input v-model="guardianForm.first_name" type="text" class="form-control" />
                           <div v-if="guardianForm.errors.first_name" class="text-danger small">{{ guardianForm.errors.first_name }}</div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="mb-3">
                           <label class="form-label">Last Name <span class="text-danger">*</span></label>
                           <input v-model="guardianForm.last_name" type="text" class="form-control" />
                           <div v-if="guardianForm.errors.last_name" class="text-danger small">{{ guardianForm.errors.last_name }}</div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="mb-3">
                           <label class="form-label">Middle Name</label>
                           <input v-model="guardianForm.middle_name" type="text" class="form-control" />
                           <div v-if="guardianForm.errors.middle_name" class="text-danger small">{{ guardianForm.errors.middle_name }}</div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="mb-3">
                           <label class="form-label">Relationship</label>
                           <v-select v-model="guardianForm.relationship_id" :options="relationships" label="name" :reduce="option => option.id" />
                           <div v-if="guardianForm.errors.relationship_id" class="text-danger small">{{ guardianForm.errors.relationship_id }}</div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="mb-3">
                           <label class="form-label">Email</label>
                           <input v-model="guardianForm.email" type="email" class="form-control" />
                           <div v-if="guardianForm.errors.email" class="text-danger small">{{ guardianForm.errors.email }}</div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="mb-3">
                           <label class="form-label">Phone</label>
                           <input v-model="guardianForm.phone" type="text" class="form-control" />
                           <div v-if="guardianForm.errors.phone" class="text-danger small">{{ guardianForm.errors.phone }}</div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="mb-3">
                           <label class="form-label">ID Number</label>
                           <input v-model="guardianForm.identification_number" type="text" class="form-control" />
                           <div v-if="guardianForm.errors.identification_number" class="text-danger small">{{ guardianForm.errors.identification_number }}</div>
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="mb-3">
                           <label class="form-label">Profession</label>
                           <input v-model="guardianForm.profession" type="text" class="form-control" />
                           <div v-if="guardianForm.errors.profession" class="text-danger small">{{ guardianForm.errors.profession }}</div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" @click="guardianFormCleanUp">Cancel</button>
                  <button type="button" class="btn btn-primary" @click.prevent="uploadNewGuardian">Save Guardian</button>
               </div>
            </div>
         </div>
      </div>
   </DefaultLayout>
</template>

<script>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import {Head, Link, useForm} from "@inertiajs/vue3"
import axios from "axios";
import {Inertia} from "@inertiajs/inertia";
import {Modal} from "bootstrap";

export default {
   components: {DefaultLayout, Head, Link},
   props: {
      studentAdmission: {
         type: Object,
         default: null
      },
      student: {
         type: Object,
         default: null
      },
      guardians: {
         type: Array,
         default: () => []
      },
      siblings: {
         type: Array,
         default: () => []
      },
      error: {
         type: String,
         default: null
      }
   },
   data() {
      return {
         form: useForm({
            registration_details: {
               division_id: null,
            },
            student: {
               first_name: '',
               middle_name: '',
               last_name: '',
               admission_number: '',
               rank_id: null,
               gender_id: null,
               religion_id: null,
               date_of_birth: null,
               birth_certificate_number: null,
               citizenship: '',
               county: '',
               ward: '',
               permanent_address: '',
               kcpe_score: '',
               previous_school: '',
            },
            other_details: {
               siblings: [],
               physical_disability: '',
               hobby: '',
               medical_details: '',
               character_book: '',
            },
         }),
         guardianForm: useForm({
            student_id: null,
            relationship_id: null,
            first_name: null,
            middle_name: null,
            last_name: null,
            email: null,
            phone: null,
            identification_number: null,
            profession: null,
         }),
         
         divisions: [],
         ranks: [],
         genders: [],
         religions: [],
         relationships: [],
         
         guardianDetails: [],
         guardianErrors: {},
         
         currentStep: 1,
         routes: {},
         
         studentDataFetched: false,
         editingGuardianIndex: null,
      }
   },
   computed: {
      progressPercentage() {
         return (this.currentStep / 4) * 100;
      },
      filteredRanks() {
         if (!this.form.registration_details.division_id) {
            return [];
         }
         return this.ranks.filter(rank => rank.division_id === this.form.registration_details.division_id);
      },
      admissionStatusClass() {
         if (this.studentAdmission?.has_exit_school) {
            return 'bg-danger';
         }
         return this.studentAdmission?.is_active ? 'bg-success' : 'bg-warning';
      },
      admissionStatusText() {
         if (this.studentAdmission?.has_exit_school) {
            return 'Exited';
         }
         return this.studentAdmission?.is_active ? 'Active' : 'Inactive';
      },
      admissionStatusDescription() {
         if (this.studentAdmission?.has_exit_school) {
            return 'Student has exited the school';
         }
         return this.studentAdmission?.is_active ? 'Student is currently active' : 'Student admission is inactive';
      }
   },
  created() {
  // Initialize routes without ID for step submissions
  if (this.studentAdmission?.id) {
    this.routes = {
      1: "/admin/student-admissions/first-step",
      2: "/admin/student-admissions/second-step", 
      3: "/admin/student-admissions/third-step",
    };
  }

  // Set guardian form student_id
  if (this.studentAdmission?.student?.id) {
    this.guardianForm.student_id = this.studentAdmission.student.id;
  }

  // Populate form data
  if (this.studentAdmission) {
    this.form.id = this.studentAdmission.id;
    
    // Populate registration details
    this.form.registration_details.division_id = this.studentAdmission.division_id || null;
    
    // Populate student data
    if (this.student) {
      this.form.student.first_name = this.student.first_name || '';
      this.form.student.middle_name = this.student.middle_name || '';
      this.form.student.last_name = this.student.last_name || '';
      this.form.student.admission_number = this.student.admission_number || '';
      this.form.student.rank_id = this.student.rank_id || null;
      this.form.student.gender_id = this.student.gender_id || null;
      this.form.student.religion_id = this.student.religion_id || null;
      this.form.student.date_of_birth = this.student.date_of_birth || null;
      this.form.student.birth_certificate_number = this.student.birth_certificate_number || null;
      this.form.student.citizenship = this.student.citizenship || '';
      this.form.student.county = this.student.county || '';
      this.form.student.ward = this.student.ward || '';
      this.form.student.permanent_address = this.student.permanent_address || '';
      this.form.student.kcpe_score = this.student.kcpe_score || '';
      this.form.student.previous_school = this.student.previous_school || '';
      this.form.other_details.physical_disability = this.student.physical_disability || '';
      this.form.other_details.hobby = this.student.hobby || '';
      this.form.other_details.medical_details = this.student.medical_details || '';
      this.form.other_details.character_book = this.student.character_book || '';
    }

    // Populate guardians
    if (this.guardians && this.guardians.length > 0) {
      this.guardianDetails = [...this.guardians];
    }

    // Populate siblings
    if (this.siblings && this.siblings.length > 0) {
      this.form.other_details.siblings = this.siblings.map(sibling => ({
        name: sibling.name || '',
        age: sibling.age || null,
        gender_id: sibling.gender_id || null,
        current_school: sibling.current_school || '',
        current_class: sibling.current_class || '',
      }));
    } else {
      this.form.other_details.siblings = [{
        name: '',
        age: null,
        gender_id: null,
        current_school: '',
        current_class: '',
      }];
    }
  }
},
   mounted() {
      this.fetchAllStudentData();
   },
   methods: {
      fetchAllStudentData() {
         this.fetchedDivision();
         this.fetchedRanks();
         this.fetchedGenders();
         this.fetchedReligions();
         this.fetchedRelationships();
         
         if (!this.guardians || this.guardians.length === 0) {
            this.fetchedGuardianDetails();
         }
         
         this.studentDataFetched = true;
      },
      fetchedDivision() {
         axios.get('/datatable/divisions', {
            params: { filter: { activated: true } },
         }).then(({data}) => {
            this.divisions = data.data || [];
         }).catch((error) => {
            console.error('Error fetching divisions:', error)
            this.$toast?.error('An error occurred while fetching divisions.')
         })
      },
      fetchedRanks() {
         axios.get('/datatable/ranks', {
            params: { filter: { activated: true } },
         }).then(({data}) => {
            this.ranks = data.data || [];
         }).catch((error) => {
            console.error('Error fetching ranks:', error)
            this.$toast?.error('An error occurred while fetching classes.')
         })
      },
      fetchedGenders() {
         axios.get('/datatable/genders', {
            params: { filter: { activated: true } },
         }).then(({data}) => {
            this.genders = data.data || [];
         }).catch((error) => {
            console.error('Error fetching genders:', error)
            this.$toast?.error('An error occurred while fetching genders.')
         })
      },
      fetchedReligions() {
         axios.get('/datatable/religions', {
            params: { filter: { activated: true } },
         }).then(({data}) => {
            this.religions = data.data || [];
         }).catch((error) => {
            console.error('Error fetching religions:', error)
            this.$toast?.error('An error occurred while fetching religions.')
         })
      },
      fetchedRelationships() {
         axios.get('/datatable/relationships', {
            params: { filter: { activated: true } },
         }).then(({data}) => {
            this.relationships = data.data || [];
         }).catch((error) => {
            console.error('Error fetching relationships:', error)
            this.$toast?.error('An error occurred while fetching relationships.')
         })
      },
      fetchedGuardianDetails() {
         if (!this.studentAdmission?.student?.id) {
            console.warn('No student ID available for fetching guardian details');
            return;
         }
         axios.get('/datatable/guardians', {
            params: { filter: { student_id: this.studentAdmission.student.id } },
         }).then(({data}) => {
            this.guardianDetails = data.data || [];
         }).catch((error) => {
            console.error('Error fetching guardian details:', error)
            this.$toast?.error('An error occurred while fetching guardian details.')
         });
      },
     submitForm() {
  if (!this.form.id) {
    this.$toast?.error('Invalid admission ID');
    return;
  }
  
  // Prepare the data in the correct structure
  const formData = {
    registration_details: {
      division_id: this.form.registration_details.division_id
    },
    student: {
      first_name: this.form.student.first_name,
      middle_name: this.form.student.middle_name || '',
      last_name: this.form.student.last_name,
      admission_number: this.form.student.admission_number,
      rank_id: this.form.student.rank_id,
      gender_id: this.form.student.gender_id,
      religion_id: this.form.student.religion_id,
      date_of_birth: this.form.student.date_of_birth,
      birth_certificate_number: this.form.student.birth_certificate_number || '',
      citizenship: this.form.student.citizenship || '',
      county: this.form.student.county || '',
      ward: this.form.student.ward || '',
      permanent_address: this.form.student.permanent_address || '',
      kcpe_score: this.form.student.kcpe_score || '',
      previous_school: this.form.student.previous_school || '',
    },
    other_details: {
      physical_disability: this.form.other_details.physical_disability || '',
      hobby: this.form.other_details.hobby || '',
      medical_details: this.form.other_details.medical_details || '',
      character_book: this.form.other_details.character_book || '',
      siblings: this.form.other_details.siblings || []
    },
    guardians: this.guardianDetails || []
  };

  console.log('Submitting form data for admission ID:', this.form.id);
  
  // Use the form to submit the data
  this.form.patch("/admin/student-admissions/" + this.form.id, {
    data: formData,
    preserveScroll: true,
    onSuccess: () => {
      this.$toast?.success('Student admission updated successfully', 'Success');
      setTimeout(() => {
        Inertia.visit('/admin/student-admissions');
      }, 1500);
    },
    onError: (errors) => {
      console.log('Form errors:', errors);
      if (errors.message) {
        this.$toast?.error(errors.message, 'Error');
      } else {
        this.$toast?.error('Please check the form for errors and try again', 'Error');
      }
    },
  });
},
      nextStep() {
         const currentRoute = this.routes[this.currentStep];
         if (!currentRoute) {
            console.error("Invalid step");
            return;
         }
         
         // Prepare the data with student_admission_id for edit routes
         const formData = {
            ...this.form.data(),
            student_admission_id: this.studentAdmission?.id
         };
         
         this.form.post(
            currentRoute,
            {
               data: formData,
               onSuccess: () => {
                  this.currentStep++;
               },
               onError: (errors) => {
                  console.log('Step validation errors:', errors);
                  this.$toast?.error('Please check the form for errors before proceeding');
               },
            }
         );
      },
      prevStep() {
         if (this.currentStep > 1) {
            this.currentStep--;
         }
      },
      addGuardian() {
         const modalElement = this.$refs.addGuardianModal;
         const modalInstance = Modal.getOrCreateInstance(modalElement);
         modalInstance.show();
      },
      uploadNewGuardian() {
         if (!this.guardianForm.student_id) {
            this.$toast?.error('No student ID available');
            return;
         }
         
         this.guardianForm.post(route('admin.guardians.store'), {
            onSuccess: () => {
               this.guardianForm.reset();
               this.guardianForm.clearErrors();
               const modalElement = this.$refs.addGuardianModal;
               const modalInstance = Modal.getOrCreateInstance(modalElement);
               modalInstance.hide();
               this.$toast?.success('Guardian added successfully', 'Success');
               this.fetchedGuardianDetails();
            },
            onError: (error) => {
               console.log('Guardian form errors:', error);
               this.$toast?.error('Please check the guardian form for errors', 'Error');
            },
         })
      },
      guardianFormCleanUp() {
         this.guardianForm.reset();
         this.guardianForm.clearErrors();
      },
      editGuardian(guardian, index) {
         this.editingGuardianIndex = index;
      },
      cancelGuardianEdit() {
         this.editingGuardianIndex = null;
         this.guardianErrors = {};
         this.fetchedGuardianDetails(); // Reload to reset any changes
      },
      updateGuardianDetails(guardian, index) {
         if (!guardian.hashid) {
            this.$toast?.error('Invalid guardian ID');
            return;
         }
         
         Inertia.patch(route('admin.guardians.update', guardian.hashid), {
            first_name: guardian.first_name,
            middle_name: guardian.middle_name,
            last_name: guardian.last_name,
            email: guardian.email,
            phone: guardian.phone,
            identification_number: guardian.identification_number,
            profession: guardian.profession,
            relationship_id: guardian.relationship_id,
            student_id: guardian.student_id,
         }, {
            onSuccess: () => {
               this.guardianErrors = {};
               this.editingGuardianIndex = null;
               this.$toast?.success('Guardian updated successfully', 'Success');
               this.fetchedGuardianDetails();
            },
            onError: (errors) => {
               console.log("Guardian Errors:", errors);
               this.guardianErrors[index] = errors;
               this.$toast?.error('Please check the guardian form for errors', 'Error');
            },
         })
      },
      deleteGuardian(guardian) {
         if (!guardian.hashid) {
            this.$toast?.error('Invalid guardian ID');
            return;
         }
         
         this.$toast?.question('Are you sure you want to delete this guardian?', 'Delete Guardian').then(() => {
            Inertia.delete(route('admin.guardians.destroy', guardian.hashid), {
               onSuccess: () => {
                  this.editingGuardianIndex = null;
                  this.$toast?.success('Guardian deleted successfully', 'Success');
                  this.fetchedGuardianDetails();
               },
               onError: (error) => {
                  console.log('Delete error:', error);
                  this.$toast?.error('An error occurred while deleting the guardian', 'Error');
               },
            })
         })
      },
      addSibling() {
         this.form.other_details.siblings.push({
            name: '',
            age: null,
            gender_id: null,
            current_school: '',
            current_class: '',
         });
      },
      removeSibling(index) {
         this.form.other_details.siblings.splice(index, 1);
      },
      getSiblingErrors(index, field) {
         return this.form.errors[`other_details.siblings.${index}.${field}`];
      },
   },
}
</script>

<style scoped>
.form-header {
   display: flex;
   justify-content: space-between;
   margin-bottom: 15px;
   overflow-x: auto;
}

.stepIndicator {
   text-align: center;
   flex: 1;
   position: relative;
}

.stepIndicator .square {
   width: 30px;
   height: 30px;
   background: #ddd;
   border-radius: 25%;
   margin: 0 auto;
   line-height: 30px;
   font-weight: bold;
}

.stepIndicator.active .square {
   background: #696cff;
   color: #ffffff;
}

.stepIndicator.finish .square {
   background: #e1e2ff;
   border: 2px solid #696cff;
   color: #696cff;
}

.progress-bar {
   width: 100%;
   background: #ddd;
   height: 5px;
   border-radius: 10px;
   overflow: hidden;
   margin-bottom: 20px;
}

.progress-bar .progress {
   height: 100%;
   background: #696cff;
   transition: width 0.5s ease;
}

.step {
   animation: fadeIn 0.5s ease-in;
}

.form-footer {
   margin-top: 20px;
   padding: 20px 0;
   border-top: 1px solid #e9ecef;
}

@keyframes fadeIn {
   from { opacity: 0; }
   to { opacity: 1; }
}

.card {
   box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
   border: 1px solid rgba(0, 0, 0, 0.125);
}

.bg-light {
   background-color: #f8f9fa !important;
}

.btn {
   border-radius: 0.375rem;
}

.form-control:read-only {
   background-color: #f8f9fa;
   opacity: 1;
}

.text-muted {
   color: #6c757d !important;
}

.badge {
   font-size: 0.75em;
   padding: 0.35em 0.65em;
}
</style>