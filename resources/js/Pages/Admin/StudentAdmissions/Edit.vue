<template>
   <Head title="Edit Admission Details"/>
   
   <DefaultLayout>
      <div class="row">
         <div class="col-xxl-12">
            <h3 class="mb-0">Admission Form</h3>
            <nav class="">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                     <Link :href="route('admin.dashboard')">Home</Link>
                  </li>
                  <li class="breadcrumb-item">
                     <Link :href="route('admin.admissions.index')">Student Admissions</Link>
                  </li>
                  <li class="breadcrumb-item text-primary">
                     Admission Form
                  </li>
               </ol>
            </nav>
            
            <div class="card">
               <div class="card-header border-bottom">
                  <div class="form-header">
                     <div class="stepIndicator"
                          :class="{ 'active': currentStep === 1, 'finish': currentStep > 1 }">
                        <div class="square">1</div>
                        <span :class="{ 'text-primary': currentStep > 1 }">General Details</span>
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
                  <!-- Steps End -->
               </div>
               
               <div class="card-body">
                  <div class="row">
                     <div class="col-md-12">
                        <transition name="fade">
                           <div v-if="currentStep === 1" class="step">
                              <div class="row">
                                 <div class="mb-4">
                                    <h5 class="mb-0">Admission Details</h5>
                                    <small>Enter Admission Details</small>
                                 </div>
                                 <div class="col-md-6">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="registrationDate">Registration Date <span
                                          class="text-danger ms-1">*</span></label>
                                       <date-picker
                                          id="registrationDate"
                                          form-class="shadow-sm"
                                          :value="form.registration_details.date"
                                          :max-date="new Date()"
                                          @on-change="function(dateObj, dateStr) {
                                                   form.registration_details.date = dateStr
                                                 }"
                                       ></date-picker>
                                       <div v-if="form.errors['registration_details.date']" class="text-danger">
                                          {{ form.errors['registration_details.date'] }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-6">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="divisionId">Division <span
                                          class="text-danger ms-1">*</span></label>
                                       <v-select
                                          disabled
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
                              </div>
                           </div>
                        </transition>
                        <transition name="fade">
                           <div v-if="currentStep === 2" class="step">
                              <div class="row">
                                 <div class="mb-4">
                                    <h5 class="mb-0">Student Details</h5>
                                    <small>Enter Student Details</small>
                                 </div>
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
                                       <label class="form-label-md mb-1" for="lastName">Last Name <span
                                          class="text-danger ms-1">*</span></label>
                                       <input type="text" id="lastName" class="form-control"
                                              v-model="form.student.last_name"/>
                                       <div v-if="form.errors['student.last_name']" class="text-danger">
                                          {{ form.errors['student.last_name'] }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="admissionNumber">Admission Number <span class="text-danger ms-1">*</span></label>
                                       <input type="text" id="admissionNumber" class="form-control" v-model="form.student.admission_number" />
                                       <div v-if="form.errors['student.admission_number']" class="text-danger">{{ form.errors['student.admission_number'] }}</div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="classId">Admitted Class <span class="text-danger ms-1">*</span></label>
                                       <v-select
                                          id="classId"
                                          v-model="form.student.rank_id"
                                          :options="filteredRanks"
                                          label="name"
                                          :reduce="(option) => option.id"
                                       >
                                          <template #option="option">
                                             {{ option.name }} {{ option.stream?.name }}
                                          </template>
                                          <template #selected-option="option">
                                             {{ option.name }} {{ option.stream?.name }}
                                          </template>
                                       </v-select>
                                       <div v-if="form.errors['student.rank_id']" class="text-danger">{{ form.errors['student.rank_id'] }}</div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="genderId">Gender <span
                                          class="text-danger ms-1">*</span></label>
                                       <v-select
                                          disabled
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
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="religionId">Religion <span
                                          class="text-danger ms-1">*</span></label>
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
                                          @on-change="function(dateObj, dateStr) {
                                             form.student.date_of_birth = dateStr
                                          }"
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
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="permanentAddress">Permanent Address</label>
                                       <input type="text" id="permanentAddress" class="form-control"
                                              v-model="form.student.permanent_address"/>
                                       <div v-if="form.errors['student.permanent_address']" class="text-danger">
                                          {{ form.errors['student.permanent_address'] }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="kcpeScore">KCPE Score</label>
                                       <input type="text" id="kcpeScore" class="form-control"
                                              v-model="form.student.kcpe_score"/>
                                       <div v-if="form.errors['student.kcpe_score']" class="text-danger">
                                          {{ form.errors['student.kcpe_score'] }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
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
                        <transition name="fade">
                           <div v-if="currentStep === 3" class="step">
                              <div class="mb-4">
                                 <div class="row">
                                    <div class="col-md-9 mt-3">
                                       <h5 class="mb-0">Student Guardian Details</h5>
                                       <small>A list of all guardians of the student registered to the system</small>
                                    </div>
                                    <div class="col-md-3 mt-3 ">
                                       <button type="button" class="btn btn-light float-end" @click="addGuardian">
                                          Add Guardian
                                       </button>
                                    </div>
                                 </div>
                              </div>
                              <div v-for="(guardian, index) in guardianDetails" :key="index" class="row">
                                 <div class="mb-4 d-flex align-items-center">
                                    <div>
                                       <h5 class="mb-0">Guardian {{ index + 1 }}</h5>
                                       <small class="me-2">Enter Guardian's Details</small>
                                    </div>
                                    <div class="ms-5">
                                       <button v-if="editingGuardianIndex !== index" type="button" class="btn btn-secondary me-2" @click="editGuardian(guardian, index)">
                                          Edit Guardian
                                       </button>
                                       <button v-if="editingGuardianIndex === index" type="button" class="btn btn-light me-2" @click="cancelGuardianEdit">
                                          Cancel
                                       </button>
                                       <button v-if="editingGuardianIndex === index" type="button" class="btn btn-success me-2" @click="updateGuardianDetails(guardian)">
                                          Update
                                       </button>
                                       <button type="button" class="btn btn-icon btn-danger ms-6" @click="deleteGuardian(guardian)">
                                          <i class="icon-base bx bx-trash"></i>
                                       </button>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="firstName">First Name</label>
                                       <input type="text" id="firstName" class="form-control" :disabled="editingGuardianIndex !== index"  v-model="guardian.first_name"/>
                                       <div v-if="guardianErrors[index]?.first_name" class="text-danger">{{ guardianErrors.first_name }}</div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="middleName">Middle Name</label>
                                       <input type="text" id="middleName" class="form-control" :disabled="editingGuardianIndex !== index"
                                              v-model="guardian.middle_name"/>
                                       <div v-if="editingGuardianIndex === index && guardianErrors.middle_name" class="text-danger">{{ guardianErrors.middle_name }}</div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="lastName">Last Name</label>
                                       <input type="text" id="lastName" class="form-control" :disabled="editingGuardianIndex !== index"  v-model="guardian.last_name"/>
                                       <div v-if="editingGuardianIndex === index && guardianErrors.last_name" class="text-danger">{{ guardianErrors.last_name }}</div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="relationshipId">Relationship</label>
                                       <v-select
                                          :disabled="editingGuardianIndex !== index"
                                          id="relationshipId"
                                          v-model="guardian.relationship_id"
                                          :options="relationships"
                                          label="name"
                                          :reduce="(option) => option.id"
                                       ></v-select>
                                       <div v-if="editingGuardianIndex === index && guardianErrors.relationship_id" class="text-danger">{{ guardianErrors.relationship_id }}</div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="">Email</label>
                                       <input type="email" id="" class="form-control" :disabled="editingGuardianIndex !== index"  v-model="guardian.email"/>
                                       <div v-if="editingGuardianIndex === index && guardianErrors.email" class="text-danger">{{ guardianErrors.email }}</div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="">Phone</label>
                                       <input type="text" id="" class="form-control" :disabled="editingGuardianIndex !== index"  v-model="guardian.phone"/>
                                       <div v-if="editingGuardianIndex === index && guardianErrors.phone" class="text-danger">{{ guardianErrors.phone }}</div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="identificationNumber">Identification Number</label>
                                       <input type="text" id="identificationNumber" class="form-control" :disabled="editingGuardianIndex !== index"  v-model="guardian.identification_number"/>
                                       <div v-if="editingGuardianIndex === index && guardianErrors.identification_number" class="text-danger">{{ guardianErrors.identification_number }}</div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="occupation">Profession/Occupation</label>
                                       <input type="text" id="occupation" class="form-control" :disabled="editingGuardianIndex !== index"  v-model="guardian.profession"/>
                                       <div v-if="editingGuardianIndex === index && guardianErrors.profession" class="text-danger">{{ guardianErrors.profession }}</div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </transition>
                        
                        <transition name="fade">
                           <div v-if="currentStep === 4" class="step">
                              <div class="row">
                                 <div class="mb-4">
                                    <h5 class="mb-0">Other Details</h5>
                                    <small>Enter other relevant information</small>
                                 </div>
                                 <div class="col-md-6">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="physicalDisability">Physical Disability</label>
                                       <input type="text" id="physicalDisability" class="form-control"
                                              v-model="form.other_details.physical_disability"/>
                                       <div v-if="form.errors['other_details.physical_disability']" class="text-danger">
                                          {{ form.errors['other_details.physical_disability'] }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-6">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="hobby">Special Interest/Hobby</label>
                                       <input type="text" id="hobby" class="form-control"
                                              v-model="form.other_details.hobby"/>
                                       <div v-if="form.errors['other_details.hobby']" class="text-danger">
                                          {{ form.errors['other_details.hobby'] }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-6">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="medicalDetails">Medical Details</label>
                                       <textarea rows="5" id="medicalDetails" class="form-control"
                                                 v-model="form.other_details.medical_details"/>
                                       <div v-if="form.errors['other_details.medical_details']" class="text-danger">
                                          {{ form.errors['other_details.medical_details'] }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-6">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="characterBook">Character Book</label>
                                       <textarea rows="5" id="characterBook" class="form-control"
                                                 v-model="form.other_details.character_book"/>
                                       <div v-if="form.errors['other_details.character_book']" class="text-danger">
                                          {{ form.errors['other_details.character_book'] }}
                                       </div>
                                    </div>
                                 </div>
                                 <div v-for="(sibling, index) in form.other_details.siblings" :key="index"
                                      class="col-md-12">
                                    <div class="d-flex align-items-center justify-content-between">
                                       <div>
                                          <h6 class="mb-0">Siblings {{ index + 1 }}</h6>
                                          <p>Fill in the table to capture the particulars of all brothers/sisters.</p>
                                       </div>
                                       <div class="me-3">
                                          <button type="button" v-if="index > 0"
                                                  class="btn btn-sm btn-icon btn-danger ms-auto"
                                                  @click="removeSibling(index)">
                                             <i class="bx bx-trash"></i>
                                          </button>
                                       </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                       <div class="row gx-4 mb-4">
                                          <div class="col-md-3">
                                             <div class="form-group mb-3">
                                                <label class="form-label-md mb-1" for="siblingName">Name</label>
                                                <input type="text" class="form-control" v-model="sibling.name"/>
                                                <div v-if="getSiblingErrors(index, 'name')" class="text-danger">
                                                   {{ getSiblingErrors(index, 'name') }}
                                                </div>
                                             </div>
                                          </div>
                                          <div class="col-md-2">
                                             <div class="form-group mb-3">
                                                <label class="form-label-md mb-1" for="siblingAge">Age</label>
                                                <input type="number" class="form-control" v-model="sibling.age"/>
                                                <div v-if="getSiblingErrors(index, 'age')" class="text-danger">
                                                   {{ getSiblingErrors(index, 'age') }}
                                                </div>
                                             </div>
                                          </div>
                                          <div class="col-md-2">
                                             <div class="form-group mb-3">
                                                <label class="form-label-md mb-1" for="siblingGender">Name</label>
                                                <select class="form-select" v-model="sibling.gender_id">
                                                   <option disabled value="">Select Gender</option>
                                                   <option v-for="gender in genders" :key="gender.id" :value="gender.id">
                                                      {{ gender.name }}
                                                   </option>
                                                </select>
                                                <div v-if="getSiblingErrors(index, 'gender_id')" class="text-danger">
                                                   {{ getSiblingErrors(index, 'gender_id') }}
                                                </div>
                                             </div>
                                          </div>
                                          <div class="col-md-3">
                                             <div class="form-group mb-3">
                                                <label class="form-label-md mb-1" for="siblingSchool">Current School</label>
                                                <input type="text" class="form-control" v-model="sibling.current_school"/>
                                                <div v-if="getSiblingErrors(index, 'current_school')" class="text-danger">
                                                   {{ getSiblingErrors(index, 'current_school') }}
                                                </div>
                                             </div>
                                          </div>
                                          <div class="col-md-2">
                                             <div class="form-group mb-3">
                                                <label class="form-label-md mb-1" for="siblingClass">Current Class</label>
                                                <input type="text" class="form-control" v-model="sibling.current_class"/>
                                                <div v-if="getSiblingErrors(index, 'current_class')" class="text-danger">
                                                   {{ getSiblingErrors(index, 'current_class') }}
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-12 mb-8">
                                    <button type="button" class="btn rounded-pill btn-light" @click="addSibling">
                                       <i class="bx bx-plus-circle me-3"></i>
                                       Add Sibling
                                    </button>
                                 </div>
                              </div>
                           </div>
                        </transition>
                        
                        <!-- Navigation Start -->
                        <div class="form-footer px-0">
                           <div class="col-md-6">
                              <button type="button" class="btn btn-secondary" :class="{ 'disabled': currentStep === 1}"
                                      @click="prevStep">
                                 Previous
                              </button>
                           </div>
                           <div v-if="currentStep !== 4" class="col-auto">
                              <button
                                 type="button"
                                 class="btn btn-primary"
                                 @click="nextStep"
                              >
                                 Next
                              </button>
                           </div>
                           <div v-else class="col-auto">
                              <button
                                 type="button"
                                 class="btn btn-success"
                                 @click.prevent="submitForm"
                                 :disabled="form.processing"
                              >
                                 Update
                              </button>
                           </div>
                        </div>
                        <!-- Navigation End -->
                     </div>
                  </div>
                  <progress v-if="form.progress" :value="form.progress.percentage" max="100">
                     {{ form.progress.percentage }}%
                  </progress>
               </div>
            </div>
         </div>
      </div>
      
      <!-- Section Modal -->
      <div
         class="modal fade"
         id="add-guardian-modal"
         data-bs-backdrop="static"
         tabindex="-1"
         aria-labelledby="add-guardian-modal-label"
         aria-hidden="true"
         ref="addGuardianModal"
      >
         <div class="modal-dialog modal-xl">
            <div class="modal-content">
               <div class="modal-header p-5 border-bottom">
                  <h5 class="modal-title" id="create-menu-modal-label">New Guardian</h5>
                  <button
                     type="button"
                     class="btn-close"
                     data-bs-dismiss="modal"
                     aria-label="Close"
                     @click.prevent="guardianFormCleanUp"
                  ></button>
               </div>
               
               <div class="modal-body">
                  <div id="createForm" @submit.prevent="uploadNewGuardian">
                     <div class="row">
                        <div class="col-md-4 col-12">
                           <div class="mb-3">
                              <label for="firstName" class="form-label-md mb-1">First Name <span class="text-danger ms-1">*</span></label>
                              <input v-model="guardianForm.first_name" type="text" class="form-control" id="firstName" />
                              <div v-if="guardianForm.errors.first_name" class="text-danger">{{ guardianForm.errors.first_name }}</div>
                           </div>
                        </div>
                        <div class="col-md-4 col-12">
                           <div class="mb-3">
                              <label for="middleName" class="form-label-md mb-1">Middle Name</label>
                              <input v-model="guardianForm.middle_name" type="text" class="form-control" id="middleName" />
                              <div v-if="guardianForm.errors.middle_name" class="text-danger">{{ guardianForm.errors.middle_name }}</div>
                           </div>
                        </div>
                        <div class="col-md-4 col-12">
                           <div class="mb-3">
                              <label for="lastName" class="form-label-md mb-1">Last Name <span class="text-danger ms-1">*</span></label>
                              <input v-model="guardianForm.last_name" type="text" class="form-control" id="lastName" />
                              <div v-if="guardianForm.errors.last_name" class="text-danger">{{ guardianForm.errors.last_name }}</div>
                           </div>
                        </div>
                        <div class="col-md-4 col-12">
                           <div class="mb-3">
                              <label for="relationshipId" class="form-label-md mb-1">
                                 Relationship
                              </label>
                              <v-select
                                 id="relationshipId"
                                 v-model="guardianForm.relationship_id"
                                 :options="relationships"
                                 label="name"
                                 :reduce="option => option.id"
                              />
                              <div v-if="guardianForm.errors.relationship_id" class="text-danger">{{ guardianForm.errors.relationship_id }}</div>
                           </div>
                        </div>
                        <div class="col-md-4 col-12">
                           <div class="mb-3">
                              <label for="email" class="form-label-md mb-1">Email</label>
                              <input v-model="guardianForm.email" type="text" class="form-control" id="email" />
                              <div v-if="guardianForm.errors.email" class="text-danger">{{ guardianForm.errors.email }}</div>
                           </div>
                        </div>
                        <div class="col-md-4 col-12">
                           <div class="mb-3">
                              <label for="phone" class="form-label-md mb-1">Phone</label>
                              <input v-model="guardianForm.phone" type="text" class="form-control" id="email" />
                              <div v-if="guardianForm.errors.phone" class="text-danger">{{ guardianForm.errors.phone }}</div>
                           </div>
                        </div>
                        <div class="col-md-4 col-12">
                           <div class="mb-3">
                              <label for="identificationNumber" class="form-label-md mb-1">Passport/ID Number</label>
                              <input v-model="guardianForm.identification_number" type="text" class="form-control" id="identificationNumber" />
                              <div v-if="guardianForm.errors.identification_number" class="text-danger">{{ guardianForm.errors.identification_number }}</div>
                           </div>
                        </div>
                        <div class="col-md-4 col-12">
                           <div class="mb-3">
                              <label for="profession" class="form-label-md mb-1">Profession</label>
                              <input v-model="guardianForm.profession" type="text" class="form-control" id="email" />
                              <div v-if="guardianForm.errors.profession" class="text-danger">{{ guardianForm.errors.profession }}</div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="modal-footer p-5 border-top">
                  <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" @click="guardianFormCleanUp">
                     Close
                  </button>
                  <button type="button" class="btn btn-primary" @click.prevent="uploadNewGuardian">
                     Save Section
                  </button>
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
   props: ['studentAdmission'],
   data() {
      return {
         form: useForm({
            registration_details: {
               date: new Date().toISOString().slice(0, 10),
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
               kpsea_score: '',
               kjsea_score: '',
               kcpe_score: '',
               upi_number: '',
               nemis: '',
               assessment_number: '',
               previous_school: '',
               specialization: '',
            },
            guardians: [],
            other_details: {
               siblings: [
                  {
                     name: '',
                     age: null,
                     gender_id: null,
                     current_school: '',
                     current_class: '',
                  },
               ],
               physical_disability: '',
               hobby: '',
               special_interests: '',
            },
         }),
         guardianForm: useForm({
            student_id: this.studentAdmission.student.id,
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
         siblingDetails: [],
         guardianErrors: {},
         
         currentStep: 1,
         routes: {
            1: "/admin/student-admissions/" + this.studentAdmission.hashid + "/first-step/",
            2: "/admin/student-admissions/" + this.studentAdmission.hashid + "/second-step/",
            3: "/admin/student-admissions/" + this.studentAdmission.hashid + "/third-step/",
            4: "/admin/student-admissions/" + this.studentAdmission.hashid + "/fourth-step/",
         },
         
         studentDataFetched: false,
         editingGuardianIndex: null,
      }
   },
   watch: {
      'form.registration_details.division_id': function () {
         const formDivision = this.divisions.find(division => division.id === this.form.registration_details.division_id);
         if (formDivision) {
            this.selectedDivision = formDivision.name;
            
            // Reset fields based on the division
            this.form.student.kpsea_score = '';
            this.form.student.kjsea_score = '';
            this.form.student.kcpe_score = '';
            
            // Reset rank_id when division changes
            this.form.registration_details.rank_id = '';
         } else {
            this.selectedDivision = null; // Handle invalid or missing division ID
         }
      },
   },
   computed: {
      progressPercentage() {
         return (this.currentStep / Object.keys(this.routes).length) * 100;
      },
      filteredRanks() {
         if (!this.form.registration_details.division_id) {
            return [];
         }
         const divisionId = this.form.registration_details.division_id;
         // console.log("selected division:", divisionId)
         
         if (!divisionId) {
            return [];
         }
         
         return this.ranks.filter(rank => rank.division_id === divisionId);
      },
   },
   created() {
      // Re-fetch data when navigating back to this component
      Inertia.on('navigate', this.handleNavigation);
      
      if (this.studentAdmission) {
         this.form.id = this.studentAdmission.hashid;
         this.form.registration_details.date = this.studentAdmission.date;
         this.form.registration_details.division_id = this.studentAdmission.division_id;
         this.form.student.first_name = this.studentAdmission.student?.first_name;
         this.form.student.middle_name = this.studentAdmission.student?.middle_name;
         this.form.student.last_name = this.studentAdmission.student?.last_name;
         this.form.student.admission_number = this.studentAdmission.student?.admission_number;
         this.form.student.rank_id = this.studentAdmission.student?.rank_id;
         this.form.student.gender_id = this.studentAdmission.student?.gender_id;
         this.form.student.religion_id = this.studentAdmission.student?.religion_id;
         this.form.student.date_of_birth = this.studentAdmission.student?.date_of_birth;
         this.form.student.birth_certificate_number = this.studentAdmission.student?.birth_certificate_number;
         this.form.student.citizenship = this.studentAdmission.student?.citizenship;
         this.form.student.county = this.studentAdmission.student?.county;
         this.form.student.ward = this.studentAdmission.student?.ward;
         this.form.student.permanent_address = this.studentAdmission.student?.permanent_address;
         this.form.student.kcpe_score = this.studentAdmission.student?.kcpe_score;
         this.form.student.assessment_number = this.studentAdmission.student?.assessment_number;
         this.form.student.previous_school = this.studentAdmission.student?.previous_school;
         this.form.other_details.physical_disability = this.studentAdmission.student?.physical_disability;
         this.form.other_details.hobby = this.studentAdmission.student?.hobby;
         this.form.other_details.medical_details = this.studentAdmission.student?.medical_details;
         this.form.other_details.character_book = this.studentAdmission.student?.character_book;
      }
   },
   mounted() {
      this.fetchAllStudentData();
   },
   methods: {
      handleNavigation(event) {
         const targetUrl = '/admin/student-admissions/' + this.studentAdmission.hashid + '/edit';
         if (event.detail.page.url === targetUrl && !this.dataFetched) {
            this.fetchAllStudentData();
         }
      },
      fetchAllStudentData() {
         this.fetchedDivision();
         this.fetchedRanks();
         this.fetchedGenders();
         this.fetchedReligions();
         this.fetchedRelationships();
         
         this.fetchedGuardianDetails();
         this.fetchedSiblingDetails();
         
         this.studentDataFetched = true;
      },
      fetchedDivision() {
         axios.get('/datatable/divisions', {
            params: {
               filter: {
                  activated: true,
               },
            },
         })
            .then(({data}) => {
               this.divisions = data.data;
               const defaultDivision = this.divisions.find(division => division.name === 'High School');
               if (defaultDivision) {
                  this.form.registration_details.division_id = defaultDivision.id;
               }
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the divisions.')
         })
      },
      fetchedRanks() {
         axios.get('/datatable/ranks', {
            params: {
               filter: {
                  activated: true,
               },
            },
         })
            .then(({data}) => {
               this.ranks = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the classes.')
         })
      },
      fetchedGenders() {
         axios.get('/datatable/genders', {
            params: {
               filter: {
                  activated: true,
               },
            },
         })
            .then(({data}) => {
               this.genders = data.data;
               const femaleGender = this.genders.find(gender => gender.name === 'Female');
               if (femaleGender) {
                  this.form.student.gender_id = femaleGender.id;
               }
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the genders.')
         })
      },
      fetchedReligions() {
         axios.get('/datatable/religions', {
            params: {
               filter: {
                  activated: true,
               },
            },
         })
            .then(({data}) => {
               this.religions = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the religions.')
         })
      },
      fetchedRelationships() {
         axios.get('/datatable/relationships', {
            params: {
               filter: {
                  activated: true,
               },
            },
         })
            .then(({data}) => {
               this.relationships = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the relationships.')
         })
      },
      fetchedGuardianDetails() {
         if (!this.studentAdmission.student) {
            return;
         }
         axios.get('/datatable/guardians', {
            params: {
               filter: {
                  student_id: this.studentAdmission.student.id,
               },
            },
         }).then(({data}) => {
            this.guardianDetails = data.data;
         }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the guardian details.')
         });
      },
      fetchedSiblingDetails() {
         if (!this.studentAdmission.student) {
            return;
         }
         axios.get('/datatable/siblings', {
            params: {
               filter: {
                  student_id: this.studentAdmission.student.id,
               },
            },
         }).then(({data}) => {
            this.siblingDetails = data.data;
            if (this.siblingDetails && this.siblingDetails.length > 0) {
               this.form.other_details.siblings = this.siblingDetails.map(sibling => ({
                  name: sibling.name,
                  gender_id: sibling.gender_id,
                  age: sibling.age,
                  current_school: sibling.current_school,
                  current_class: sibling.current_class,
               }));
            } else {
               this.form.other_details.siblings = [
                  {
                     name: null,
                     age: null,
                     gender_id: null,
                     current_school: '',
                     current_class: '',
                  }
               ];
            }
         }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the siblings\' details.')
         });
      },
      submitForm() {
         this.form.patch("/admin/student-admissions/" + this.form.id, {
            onSuccess: () => {
               this.form.reset();
               (this.currentStep = 1);
               this.form.clearErrors();
               this.$toast.success('Student\'s registration details updated successfully', 'Success');
               setTimeout(() => {
                  this.$inertia.visit('/admin/student-admissions');
               }, 1000)
            },
            onError: (errors) => {
               console.log(errors)
               this.$toast.error('An error occurred. Please try again', 'Error');
            },
         });
      },
      nextStep() {
         const currentRoute = this.routes[this.currentStep];
         if (!currentRoute) {
            console.error("Invalid step");
            return;
         }
         this.form.post(
            currentRoute,
            {
               onSuccess: () => {
                  this.currentStep++;
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
         this.guardianForm.post(route('admin.guardians.store'), {
            onSuccess: () => {
               this.guardianForm.reset();
               this.guardianForm.clearErrors();
               const modalElement = this.$refs.addGuardianModal;
               const modalInstance = Modal.getOrCreateInstance(modalElement);
               modalInstance.hide();
               setTimeout(() => {
                  this.$toast.success('Guardian details saved successfully', 'Success');
                  this.fetchedGuardianDetails();
               }, 400);
            },
            onError: (error) => {
               console.log(error);
               this.$toast.error('An error occurred. Please try again', 'Error');
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
      },
      updateGuardianDetails(guardian, index) {
         this.$inertia.patch(route('admin.guardians.update', guardian.hashid),
            {
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
               setTimeout(() => {
                  this.$toast.success('Guardian details saved successfully', 'Success');
                  this.fetchedGuardianDetails();
               }, 400);
            },
            onError: (errors) => {
               console.log("Guardian Errors:", errors);
               this.guardianErrors[index] = errors;
               this.$toast.error('An error occurred. Please try again', 'Error');
            },
         })
      },
      deleteGuardian(guardian) {
         this.$toast.question('Are you sure? Delete guardian details?', 'You are deleting a guardian!').then(() => {
            this.$inertia.delete(route('admin.guardians.destroy', guardian.hashid), {
               onSuccess: () => {
                  this.editingGuardianIndex = null;
                  setTimeout(() => {
                     this.$toast.success('Guardian details deleted successfully', 'Success');
                     this.fetchedGuardianDetails();
                  }, 400);
               },
               onError: (error) => {
                  console.log(error);
                  this.$toast.error('An error occurred. Please try again', 'Error');
               },
            })
         })
      },
      addSibling() {
         this.form.other_details.siblings.push({
            name: '',
            age: '',
            gender_id: '',
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
#registrationForm {
   background: #fff;
   padding: 30px 30px;
   border-radius: 10px;
   /* box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1); */
}
.card > .card-body {
   padding-inline: 1.5rem;
   padding-block: 1.5rem;
}
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
.stepIndicator .circle {
   width: 30px;
   height: 30px;
   background: #ddd;
   border-radius: 50%;
   margin: 0 auto;
   line-height: 30px;
   font-weight: bold;
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
.stepIndicator.active .circle, .stepIndicator.active .square {
   background: #696cff;
   color: #ffffff;
}
.stepIndicator.finish .circle, .stepIndicator.finish .square {
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
   display: flex;
   justify-content: space-between;
   margin-top: 10px;
   padding: 15px;
}
button {
   padding: 10px 20px;
   border: none;
   border-radius: 5px;
   font-size: 1em;
   cursor: pointer;
}
#prevBtn {
   background: #eee;
   color: #696cff;
}
#nextBtn {
   background: #696cff;
   color: white;
}

@keyframes fadeIn {
   from {
      opacity: 0;
   }
   to {
      opacity: 1;
   }
}
</style>
