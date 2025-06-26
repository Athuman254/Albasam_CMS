<template>
   <Head title="Admission Form"/>
   
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
                                       <label class="form-label-md mb-1" for="genderId">Gender</label>
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
                                          :min-date="new Date(new Date().getFullYear() - 19, 0, 1)"
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
                                 <!-- KPSEA Score -->
<!--                                 <div v-if="['Junior Secondary', 'Junior High'].includes(selectedDivision)"-->
<!--                                      class="col-md-4">-->
<!--                                    <div class="form-group mb-3">-->
<!--                                       <label class="form-label-md mb-1" for="kpseaScore">KPSEA Score</label>-->
<!--                                       <input type="text" id="kpseaScore" class="form-control"-->
<!--                                              v-model="form.student.kpsea_score"/>-->
<!--                                       <div v-if="form.errors['student.kpsea_score']" class="text-danger">-->
<!--                                          {{ form.errors['student.kpsea_score'] }}-->
<!--                                       </div>-->
<!--                                    </div>-->
<!--                                 </div>-->
                                 <!-- KJSEA Score -->
<!--                                 <div v-if="['Senior Secondary', 'Senior High'].includes(selectedDivision)"-->
<!--                                      class="col-md-4">-->
<!--                                    <div class="form-group mb-3">-->
<!--                                       <label class="form-label-md mb-1" for="kjseaScore">KJSEA Score</label>-->
<!--                                       <input type="text" id="kjseaScore" class="form-control"-->
<!--                                              v-model="form.student.kjsea_score"/>-->
<!--                                       <div v-if="form.errors['student.kjsea_score']" class="text-danger">-->
<!--                                          {{ form.errors['student.kjsea_score'] }}-->
<!--                                       </div>-->
<!--                                    </div>-->
<!--                                 </div>-->
                                 <!-- KCPE Score -->
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
                                 <!-- Common Fields -->
<!--                                 <template v-if="!['Primary', 'Pre-Primary'].includes(selectedDivision)">-->
<!--                                    <div class="col-md-4">-->
<!--                                       <div class="form-group mb-3">-->
<!--                                          <label class="form-label-md mb-1" for="indexNumber">Index Number</label>-->
<!--                                          <input type="text" id="indexNumber" class="form-control"-->
<!--                                                 v-model="form.student.index_number"/>-->
<!--                                          <div v-if="form.errors['student.index_number']" class="text-danger">-->
<!--                                             {{ form.errors['student.index_number'] }}-->
<!--                                          </div>-->
<!--                                       </div>-->
<!--                                    </div>-->
<!--                                    <div class="col-md-4">-->
<!--                                       <div class="form-group mb-3">-->
<!--                                          <label class="form-label-md mb-1" for="assessmentNumber">Assessment Number</label>-->
<!--                                          <input type="text" id="assessmentNumber" class="form-control"-->
<!--                                                 v-model="form.student.assessment_number"/>-->
<!--                                          <div v-if="form.errors['student.assessment_number']" class="text-danger">-->
<!--                                             {{ form.errors['student.assessment_number'] }}-->
<!--                                          </div>-->
<!--                                       </div>-->
<!--                                    </div>-->
<!--                                    <div class="col-md-4">-->
<!--                                       <div class="form-group mb-3">-->
<!--                                          <label class="form-label-md mb-1" for="upiNumber">NEMIS/UPI Number</label>-->
<!--                                          <input type="text" id="upiNumber" class="form-control"-->
<!--                                                 v-model="form.student.upi_number"/>-->
<!--                                          <div v-if="form.errors['student.upi_number']" class="text-danger">-->
<!--                                             {{ form.errors['student.upi_number'] }}-->
<!--                                          </div>-->
<!--                                       </div>-->
<!--                                    </div>-->
<!--                                 </template>-->
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
                              <div v-for="(guardian, index) in form.guardians" :key="index" class="row">
                                 <div class="mb-4 d-flex align-items-center justify-content-between">
                                    <div>
                                       <h5 class="mb-0">Guardian {{ index + 1 }}</h5>
                                       <small class="me-2">Enter Guardian's Details</small>
                                    </div>
                                    <div>
                                       <button type="button" v-if="index > 0" class="btn btn-sm btn-danger ms-auto"
                                               @click="removeGuardian(index)">
                                          <i class="bx bx-trash"></i>
                                       </button>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="firstName">First Name</label>
                                       <input type="text" id="firstName" class="form-control"
                                              v-model="guardian.first_name"/>
                                       <div v-if="getGuardianError(index, 'first_name')" class="text-danger">
                                          {{ getGuardianError(index, 'first_name') }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="middleName">Middle Name</label>
                                       <input type="text" id="middleName" class="form-control"
                                              v-model="guardian.middle_name"/>
                                       <div v-if="getGuardianError(index, 'middle_name')" class="text-danger">
                                          {{ getGuardianError(index, 'middle_name') }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="lastName">Last Name</label>
                                       <input type="text" id="lastName" class="form-control" v-model="guardian.last_name"/>
                                       <!--                                                <div v-if="form.errors['guardians.${index}.last_name']" class="text-danger">{{ form.errors['guardians.${index}.last_name'] }}</div>-->
                                       <div v-if="getGuardianError(index, 'last_name')" class="text-danger">
                                          {{ getGuardianError(index, 'last_name') }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="relationshipId">Relationship</label>
                                       <v-select
                                          id="relationshipId"
                                          v-model="guardian.relationship_id"
                                          :options="relationships"
                                          label="name"
                                          :reduce="(option) => option.id"
                                       ></v-select>
                                       <div v-if="getGuardianError(index, 'relationship_id')" class="text-danger">
                                          {{ getGuardianError(index, 'relationship_id') }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="">Email</label>
                                       <input type="email" id="" class="form-control" v-model="guardian.email"/>
                                       <div v-if="getGuardianError(index, 'email')" class="text-danger">
                                          {{ getGuardianError(index, 'email') }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="">Phone</label>
                                       <input type="text" id="" class="form-control" v-model="guardian.phone"/>
                                       <div v-if="getGuardianError(index, 'phone')" class="text-danger">
                                          {{ getGuardianError(index, 'phone') }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="identificationNumber">Identification Number</label>
                                       <input type="text" id="identificationNumber" class="form-control"
                                              v-model="guardian.identification_number"/>
                                       <div v-if="getGuardianError(index, 'identification_number')" class="text-danger">
                                          {{ getGuardianError(index, 'identification_number') }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="occupation">Profession/Occupation</label>
                                       <input type="text" id="occupation" class="form-control"
                                              v-model="guardian.profession"/>
                                       <div v-if="getGuardianError(index, 'occupation')" class="text-danger">
                                          {{ getGuardianError(index, 'occupation') }}
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="row">
                                 <div class="col-md-12 mt-3">
                                    <button type="button" class="btn rounded-pill btn-light w-100" @click="addGuardian">
                                       <i class="bx bx-plus-circle me-3"></i>
                                       Add Guardian
                                    </button>
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
                                                <label class="form-label-md mb-1" for="siblingGender">Gender</label>
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
                                 Submit
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
   </DefaultLayout>
</template>

<script>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import {Head, Link, useForm} from "@inertiajs/vue3"
import axios from "axios";
import {Inertia} from "@inertiajs/inertia";

export default {
   components: {DefaultLayout, Head, Link},
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
               date_of_birth: '',
               birth_certificate_number: '',
               gender_id: null,
               religion_id: null,
               citizenship: null,
               county: null,
               ward: null,
               permanent_address: null,
               kcpe_score: '',
               // kpsea_score: '',
               // kjsea_score: '',
               // upi_number: '',
               // nemis: '',
               // assessment_number: '',
               previous_school: null,
               specialization: null,
            },
            guardians: [
               {
                  relationship_id: null,
                  first_name: null,
                  middle_name: null,
                  last_name: null,
                  email: null,
                  phone: null,
                  identification_number: null,
                  profession: null,
               },
            ],
            other_details: {
               siblings: [
                  {
                     name: null,
                     age: null,
                     gender_id: null,
                     current_school: null,
                     current_class: null,
                  },
               ],
               physical_disability: null,
               medical_details: null,
               character_book: null,
               hobby: null,
            },
         }),
         selectedDivision: '',
         divisions: [],
         ranks: [],
         genders: [],
         religions: [],
         relationships: [],
         
         currentStep: 1,
         routes: {
            1: "/admin/student-admissions/first-step",
            2: "/admin/student-admissions/second-step",
            3: "/admin/student-admissions/third-step",
            4: "/admin/student-admissions/fourth-step",
         },
      }
   },
   // watch: {
   //    'form.registration_details.division_id': function () {
   //       const formDivision = this.divisions.find(division => division.id === this.form.registration_details.division_id);
   //       if (formDivision) {
   //          this.selectedDivision = formDivision.name;
   //
   //          // Reset fields based on the division
   //          this.form.student.kpsea_score = '';
   //          this.form.student.kjsea_score = '';
   //          this.form.student.kcpe_score = '';
   //
   //          // Reset rank_id when division changes
   //          this.form.registration_details.rank_id = '';
   //       } else {
   //          this.selectedDivision = null; // Handle invalid or missing division ID
   //       }
   //    },
   // },
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
      Inertia.on('navigate', (event) => {
         if (event.detail.page.url === '/admin/student-admissions/admission-form') {
            this.fetchedDivision();
            this.fetchedRanks();
            this.fetchedGenders();
            this.fetchedReligions();
            this.fetchedRelationships();
         }
      });
   },
   mounted() {
      this.fetchedDivision();
      this.fetchedRanks();
      this.fetchedGenders();
      this.fetchedReligions();
      this.fetchedRelationships();
   },
   methods: {
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
      submitForm() {
         this.form.post(route('admin.admissions.store'), {
            onSuccess: () => {
               this.form.reset();
               (this.currentStep = 1);
               this.form.clearErrors();
               this.$toast.success('Student registered successfully', 'Success');
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
         this.form.guardians.push({
            relationship_id: '',
            first_name: '',
            middle_name: '',
            last_name: '',
            email: '',
            phone: '',
            identification_number: '',
            profession: '',
         });
      },
      removeGuardian(index) {
         this.form.guardians.splice(index, 1);
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
      getGuardianError(index, field) {
         return this.form.errors[`guardians.${index}.${field}`];
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
