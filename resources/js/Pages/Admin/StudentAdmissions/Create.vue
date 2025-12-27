<template>
   <Head title="New Student Admission"/>

   <DefaultLayout>
      <div class="row">
         <div class="col-xxl-12">
            <h3 class="mb-0">New Student Admission</h3>
            <nav class="">
               <ol class="breadcrumb">
                  <li class="breadcrumb-item">
                     <Link :href="route('admin.dashboard')">Home</Link>
                  </li>
                  <li class="breadcrumb-item">
                     <Link :href="route('admin.admissions.index')">Student Admissions</Link>
                  </li>
                  <li class="breadcrumb-item text-primary">
                     New Admission
                  </li>
               </ol>
            </nav>

            <div class="card">
               <div class="card-header border-bottom py-3">
                  <div class="form-header d-flex flex-wrap justify-content-between align-items-center gap-2">
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
                                    <small class="text-muted">Select the division for student admission</small>
                                 </div>
                                 
                                 <div class="col-md-6">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="divisionId">Division <span class="text-danger ms-1">*</span></label>
                                       <v-select
                                          id="divisionId"
                                          v-model="form.registration_details.division_id"
                                          :options="divisions"
                                          label="name"
                                          :reduce="(option) => option.id"
                                          placeholder="Select division"
                                       ></v-select>
                                       <div v-if="form.errors['registration_details.division_id']" class="text-danger">
                                          {{ form.errors['registration_details.division_id'] }}
                                       </div>
                                       <small class="text-muted">Select the appropriate division for the student</small>
                                    </div>
                                 </div>

                                 <div class="col-md-6">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="registeredAt">Registration Date <span class="text-danger ms-1">*</span></label>
                                       <date-picker
                                          id="registeredAt"
                                          form-class="shadow-sm"
                                          v-model="form.registration_details.registered_at"
                                          :max-date="new Date()"
                                          placeholder="Select registration date"
                                          @on-change="function(dateObj, dateStr) { form.registration_details.registered_at = dateStr }"
                                       ></date-picker>
                                       <div v-if="form.errors['registration_details.registered_at']" class="text-danger">
                                          {{ form.errors['registration_details.registered_at'] }}
                                       </div>
                                       <small class="text-muted">You can adjust this date if necessary</small>
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
                                    <small class="text-muted">Enter student personal and academic information</small>
                                 </div>
                                 
                                 <!-- Student Photo Section -->
                                 <div class="col-md-12 mb-4">
                                    <div class="card bg-light border-dashed">
                                       <div class="card-body">
                                          <div class="row align-items-center">
                                             <div class="col-md-3 text-center">
                                                <div class="avatar-upload mb-3">
                                                   <div class="avatar-preview mb-2">
                                                      <img :src="photoPreview || '/img/default-avatar.png'" class="rounded border shadow-sm" style="width: 150px; height: 150px; object-fit: cover;" alt="Student Photo">
                                                   </div>
                                                   <div class="btn-group btn-group-sm">
                                                      <button type="button" class="btn btn-outline-primary" @click="$refs.photoInput.click()">
                                                         <i class="bx bx-upload me-1"></i> Upload
                                                      </button>
                                                      <button type="button" class="btn btn-outline-info" @click="startCamera">
                                                         <i class="bx bx-camera me-1"></i> Take Photo
                                                      </button>
                                                   </div>
                                                   <input type="file" ref="photoInput" class="d-none" @change="handlePhotoUpload" accept="image/*">
                                                </div>
                                             </div>
                                             <div class="col-md-9" v-if="cameraActive">
                                                <div class="camera-container text-center">
                                                   <video ref="video" width="320" height="240" autoplay class="rounded border mb-2"></video>
                                                   <canvas ref="canvas" style="display:none;" width="320" height="240"></canvas>
                                                   <div class="camera-controls">
                                                      <button type="button" class="btn btn-sm btn-success me-2" @click="capturePhoto">
                                                         <i class="bx bx-camera me-1"></i> Capture
                                                      </button>
                                                      <button type="button" class="btn btn-sm btn-danger" @click="stopCamera">
                                                         <i class="bx bx-x me-1"></i> Stop
                                                      </button>
                                                   </div>
                                                </div>
                                             </div>
                                             <div class="col-md-9" v-else>
                                                <div class="p-3">
                                                   <h6>Student Photo</h6>
                                                   <p class="text-muted small">Upload a passport-size photo or capture one directly using your webcam. High-quality images (PNG/JPG) are recommended.</p>
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>

                                 <!-- Scholarship Information -->
                                 <div class="col-md-12 mb-4">
                                    <div class="card border-info">
                                       <div class="card-body">
                                          <h6 class="text-info mb-3"><i class="bx bx-award me-1"></i> Scholarship & Financial Aid</h6>
                                          <div class="row">
                                             <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                   <label class="form-label-md mb-1">Scholarship Type</label>
                                                   <v-select
                                                      v-model="form.student.scholarship_type"
                                                      :options="['none', 'full', 'half', 'custom']"
                                                   ></v-select>
                                                </div>
                                             </div>
                                             <div class="col-md-6" v-if="form.student.scholarship_type === 'custom'">
                                                <div class="form-group mb-3">
                                                   <label class="form-label-md mb-1">Scholarship Rate (%)</label>
                                                   <input type="number" class="form-control" v-model="form.student.scholarship_rate" min="0" max="100" />
                                                </div>
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>

                                 <!-- Personal Information -->
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="firstName">First Name <span class="text-danger ms-1">*</span></label>
                                       <input type="text" id="firstName" class="form-control"
                                              v-model="form.student.first_name" placeholder="Enter first name"/>
                                       <div v-if="form.errors['student.first_name']" class="text-danger">
                                          {{ form.errors['student.first_name'] }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="middleName">Middle Name</label>
                                       <input type="text" id="middleName" class="form-control"
                                              v-model="form.student.middle_name" placeholder="Enter middle name"/>
                                       <div v-if="form.errors['student.middle_name']" class="text-danger">
                                          {{ form.errors['student.middle_name'] }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="lastName">Last Name <span class="text-danger ms-1">*</span></label>
                                       <input type="text" id="lastName" class="form-control"
                                              v-model="form.student.last_name" placeholder="Enter last name"/>
                                       <div v-if="form.errors['student.last_name']" class="text-danger">
                                          {{ form.errors['student.last_name'] }}
                                       </div>
                                    </div>
                                 </div>

                                 <!-- Admission Information -->
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="admissionNumber">Admission Number <span class="text-danger ms-1">*</span></label>
                                       <div class="input-group">
                                          <input 
                                             type="text" 
                                             id="admissionNumber" 
                                             class="form-control bg-light" 
                                             v-model="form.student.admission_number" 
                                             readonly
                                             placeholder="Click generate to create"
                                          />
                                          <button 
                                             type="button" 
                                             class="btn btn-outline-primary" 
                                             @click="generateAdmissionNumber"
                                             :disabled="generatingAdmissionNumber"
                                             title="Generate admission number"
                                          >
                                             <i v-if="generatingAdmissionNumber" class="bx bx-loader bx-spin"></i>
                                             <i v-else class="bx bx-refresh"></i>
                                          </button>
                                       </div>
                                       <small class="text-muted">Admission number is auto-generated</small>
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
                                          placeholder="Select class"
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
                                          placeholder="Select gender"
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
                                          placeholder="Select religion"
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
                                          placeholder="Select date of birth"
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
                                              v-model="form.student.birth_certificate_number" placeholder="Enter birth certificate number"/>
                                       <div v-if="form.errors['student.birth_certificate_number']" class="text-danger">
                                          {{ form.errors['student.birth_certificate_number'] }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="assessmentNumber">Assessment Number</label>
                                       <input type="text" id="assessmentNumber" class="form-control"
                                              v-model="form.student.assessment_number" placeholder="Enter assessment number"/>
                                       <div v-if="form.errors['student.assessment_number']" class="text-danger">
                                          {{ form.errors['student.assessment_number'] }}
                                       </div>
                                       <small class="text-muted">Optional: National assessment number</small>
                                    </div>
                                 </div>

                                 <!-- Address Information -->
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="citizenship">Citizenship</label>
                                       <input type="text" id="citizenship" class="form-control"
                                              v-model="form.student.citizenship" placeholder="Enter citizenship"/>
                                       <div v-if="form.errors['student.citizenship']" class="text-danger">
                                          {{ form.errors['student.citizenship'] }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="county">County</label>
                                       <input type="text" id="county" class="form-control" v-model="form.student.county" placeholder="Enter county"/>
                                       <div v-if="form.errors['student.county']" class="text-danger">
                                          {{ form.errors['student.county'] }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-4">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="ward">Ward</label>
                                       <input type="text" id="ward" class="form-control" v-model="form.student.ward" placeholder="Enter ward"/>
                                       <div v-if="form.errors['student.ward']" class="text-danger">
                                          {{ form.errors['student.ward'] }}
                                       </div>
                                    </div>
                                 </div>

                                 <div class="col-md-6">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="permanentAddress">Permanent Address</label>
                                       <textarea id="permanentAddress" class="form-control" rows="3"
                                                 v-model="form.student.permanent_address" placeholder="Enter permanent address"></textarea>
                                       <div v-if="form.errors['student.permanent_address']" class="text-danger">
                                          {{ form.errors['student.permanent_address'] }}
                                       </div>
                                    </div>
                                 </div>

                                 <!-- Academic Information -->
                                 <div class="col-md-6">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="previousSchool">Previous School</label>
                                       <input type="text" id="previousSchool" class="form-control"
                                              v-model="form.student.previous_school" placeholder="Enter previous school"/>
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
                                       <small class="text-muted">Add at least one guardian for the student</small>
                                    </div>
                                    <div class="col-md-3">
                                       <button type="button" class="btn btn-primary float-end" @click="addGuardian">
                                          <i class="bx bx-plus me-1"></i> Add Guardian
                                       </button>
                                    </div>
                                 </div>
                              </div>

                              <div v-if="form.guardians.length === 0" class="text-center py-5">
                                 <i class="bx bx-group text-muted mb-3" style="font-size: 3rem;"></i>
                                 <h5 class="text-muted">No Guardians Added</h5>
                                 <p class="text-muted">Click "Add Guardian" to add guardian information.</p>
                              </div>

                              <div v-for="(guardian, index) in form.guardians" :key="index" class="card mb-4">
                                 <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                       <h6 class="mb-0">Guardian {{ index + 1 }}</h6>
                                       <button type="button" v-if="index > 0" class="btn btn-sm btn-outline-danger" @click="removeGuardian(index)">
                                          <i class="bx bx-trash"></i> Remove
                                       </button>
                                    </div>
                                 </div>
                                 <div class="card-body">
                                    <div class="row">
                                       <div class="col-md-4">
                                          <div class="form-group mb-3">
                                             <label class="form-label-md mb-1">First Name <span class="text-danger">*</span></label>
                                             <input type="text" class="form-control" v-model="guardian.first_name" placeholder="Enter first name"/>
                                             <div v-if="getGuardianError(index, 'first_name')" class="text-danger">{{ getGuardianError(index, 'first_name') }}</div>
                                          </div>
                                       </div>
                                       <div class="col-md-4">
                                          <div class="form-group mb-3">
                                             <label class="form-label-md mb-1">Middle Name</label>
                                             <input type="text" class="form-control" v-model="guardian.middle_name" placeholder="Enter middle name"/>
                                             <div v-if="getGuardianError(index, 'middle_name')" class="text-danger">{{ getGuardianError(index, 'middle_name') }}</div>
                                          </div>
                                       </div>
                                       <div class="col-md-4">
                                          <div class="form-group mb-3">
                                             <label class="form-label-md mb-1">Last Name <span class="text-danger">*</span></label>
                                             <input type="text" class="form-control" v-model="guardian.last_name" placeholder="Enter last name"/>
                                             <div v-if="getGuardianError(index, 'last_name')" class="text-danger">{{ getGuardianError(index, 'last_name') }}</div>
                                          </div>
                                       </div>
                                       <div class="col-md-4">
                                          <div class="form-group mb-3">
                                             <label class="form-label-md mb-1">Relationship <span class="text-danger">*</span></label>
                                             <v-select
                                                v-model="guardian.relationship_id"
                                                :options="relationships"
                                                label="name"
                                                :reduce="(option) => option.id"
                                                placeholder="Select relationship"
                                             ></v-select>
                                             <div v-if="getGuardianError(index, 'relationship_id')" class="text-danger">{{ getGuardianError(index, 'relationship_id') }}</div>
                                          </div>
                                       </div>
                                       <div class="col-md-4">
                                          <div class="form-group mb-3">
                                             <label class="form-label-md mb-1">Email</label>
                                             <input type="email" class="form-control" v-model="guardian.email" placeholder="Enter email address"/>
                                             <div v-if="getGuardianError(index, 'email')" class="text-danger">{{ getGuardianError(index, 'email') }}</div>
                                          </div>
                                       </div>
                                       <div class="col-md-4">
                                          <div class="form-group mb-3">
                                             <label class="form-label-md mb-1">Phone</label>
                                             <input type="text" class="form-control" v-model="guardian.phone" placeholder="Enter phone number"/>
                                             <div v-if="getGuardianError(index, 'phone')" class="text-danger">{{ getGuardianError(index, 'phone') }}</div>
                                          </div>
                                       </div>
                                       <div class="col-md-4">
                                          <div class="form-group mb-3">
                                             <label class="form-label-md mb-1">ID Number</label>
                                             <input type="text" class="form-control" v-model="guardian.identification_number" placeholder="Enter ID/passport number"/>
                                             <div v-if="getGuardianError(index, 'identification_number')" class="text-danger">{{ getGuardianError(index, 'identification_number') }}</div>
                                          </div>
                                       </div>
                                       <div class="col-md-4">
                                          <div class="form-group mb-3">
                                             <label class="form-label-md mb-1">Profession</label>
                                             <input type="text" class="form-control" v-model="guardian.profession" placeholder="Enter profession"/>
                                             <div v-if="getGuardianError(index, 'profession')" class="text-danger">{{ getGuardianError(index, 'profession') }}</div>
                                          </div>
                                       </div>
                                       <div class="col-md-4">
                                          <div class="form-group mb-3">
                                             <label class="form-label-md mb-1">Place of Work</label>
                                             <input type="text" class="form-control" v-model="guardian.place_of_work" placeholder="Enter place of work"/>
                                             <div v-if="getGuardianError(index, 'place_of_work')" class="text-danger">{{ getGuardianError(index, 'place_of_work') }}</div>
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
                                                 v-model="form.other_details.medical_details" placeholder="Any medical conditions or allergies"></textarea>
                                       <div v-if="form.errors['other_details.medical_details']" class="text-danger">
                                          {{ form.errors['other_details.medical_details'] }}
                                       </div>
                                    </div>
                                 </div>
                                 <div class="col-md-6">
                                    <div class="form-group mb-3">
                                       <label class="form-label-md mb-1" for="characterBook">Character Book</label>
                                       <textarea rows="4" id="characterBook" class="form-control"
                                                 v-model="form.other_details.character_book" placeholder="Character assessment or notes"></textarea>
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
                                 <button v-if="currentStep !== 4" type="button" class="btn btn-primary" @click="nextStep" :disabled="!canProceedToNextStep">
                                    Next <i class="bx bx-chevron-right ms-1"></i>
                                 </button>
                                 <button v-else type="button" class="btn btn-success" @click.prevent="submitForm" :disabled="form.processing || !canProceedToNextStep">
                                    <i v-if="form.processing" class="bx bx-loader bx-spin me-1"></i>
                                    {{ form.processing ? 'Submitting...' : 'Submit Admission' }}
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
               division_id: null,
               registered_at: new Date().toISOString().split('T')[0],
            },
            student: {
               first_name: '',
               middle_name: '',
               last_name: '',
               admission_number: '',
               assessment_number: '',
               rank_id: null,
               date_of_birth: '',
               birth_certificate_number: '',
               gender_id: null,
               religion_id: null,
               citizenship: '',
               county: '',
               ward: '',
               permanent_address: '',
               previous_school: '',
               photo: null,
               scholarship_type: 'none',
               scholarship_rate: 0,
            },
            guardians: [
               {
                  relationship_id: null,
                  first_name: '',
                  middle_name: '',
                  last_name: '',
                  email: '',
                  phone: '',
                  identification_number: '',
                  profession: '',
                  place_of_work: '',
               },
            ],
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
               medical_details: '',
               character_book: '',
            },
         }),
         divisions: [],
         ranks: [],
         genders: [],
         religions: [],
         relationships: [],
         generatingAdmissionNumber: false,
         currentStep: 1,
         photoPreview: null,
         cameraActive: false,
         videoStream: null,
         routes: {
            1: "/admin/student-admissions/first-step",
            2: "/admin/student-admissions/second-step",
            3: "/admin/student-admissions/third-step",
         },
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
      canProceedToNextStep() {
         switch (this.currentStep) {
            case 1:
               return this.form.registration_details.division_id;
            case 2:
               return this.form.student.first_name && 
                      this.form.student.last_name && 
                      this.form.student.admission_number && 
                      this.form.student.rank_id &&
                      this.form.student.gender_id &&
                      this.form.student.religion_id;
            case 3:
               return this.form.guardians.some(guardian => 
                  guardian.first_name && guardian.last_name && guardian.relationship_id
               );
            case 4:
               return true; // Other details are optional
            default:
               return true;
         }
      },
      currentDate() {
         return new Date().toLocaleDateString('en-GB', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
         });
      }
   },
   mounted() {
      this.fetchAllData();
      this.generateAdmissionNumber();
   },
   methods: {
      async generateAdmissionNumber() {
         this.generatingAdmissionNumber = true;
         try {
            const response = await axios.get('/admin/student-admissions/generate-admission-number');
            if (response.data.success) {
               this.form.student.admission_number = response.data.admission_number;
               this.$toast.success('Admission number generated successfully');
            } else {
               this.$toast.error('Failed to generate admission number');
            }
         } catch (error) {
            console.error('Error generating admission number:', error);
            this.$toast.error('Error generating admission number');
         } finally {
            this.generatingAdmissionNumber = false;
         }
      },
      fetchAllData() {
         this.fetchedDivision();
         this.fetchedRanks();
         this.fetchedGenders();
         this.fetchedReligions();
         this.fetchedRelationships();
      },
      fetchedDivision() {
         axios.get('/datatable/divisions', {
            params: { filter: { activated: true } },
         }).then(({data}) => {
            this.divisions = data.data || [];
            const defaultDivision = this.divisions.find(division => division.name === 'High School');
            if (defaultDivision) {
               this.form.registration_details.division_id = defaultDivision.id;
            }
         }).catch((error) => {
            console.error('Error fetching divisions:', error);
            this.$toast.error('An error occurred while fetching divisions');
         })
      },
      fetchedRanks() {
         axios.get('/datatable/ranks', {
            params: { filter: { activated: true } },
         }).then(({data}) => {
            this.ranks = data.data || [];
         }).catch((error) => {
            console.error('Error fetching ranks:', error);
            this.$toast.error('An error occurred while fetching classes');
         })
      },
      fetchedGenders() {
         axios.get('/datatable/genders', {
            params: { filter: { activated: true } },
         }).then(({data}) => {
            this.genders = data.data || [];
            const femaleGender = this.genders.find(gender => gender.name === 'Female');
            if (femaleGender) {
               this.form.student.gender_id = femaleGender.id;
            }
         }).catch((error) => {
            console.error('Error fetching genders:', error);
            this.$toast.error('An error occurred while fetching genders');
         })
      },
      fetchedReligions() {
         axios.get('/datatable/religions', {
            params: { filter: { activated: true } },
         }).then(({data}) => {
            this.religions = data.data || [];
         }).catch((error) => {
            console.error('Error fetching religions:', error);
            this.$toast.error('An error occurred while fetching religions');
         })
      },
      fetchedRelationships() {
         axios.get('/datatable/relationships', {
            params: { filter: { activated: true } },
         }).then(({data}) => {
            this.relationships = data.data || [];
         }).catch((error) => {
            console.error('Error fetching relationships:', error);
            this.$toast.error('An error occurred while fetching relationships');
         })
      },
      handlePhotoUpload(event) {
         const file = event.target.files[0];
         if (file) {
            this.form.student.photo = file;
            const reader = new FileReader();
            reader.onload = (e) => {
               this.photoPreview = e.target.result;
            };
            reader.readAsDataURL(file);
         }
      },
      async startCamera() {
         this.cameraActive = true;
         try {
            this.videoStream = await navigator.mediaDevices.getUserMedia({ video: true });
            this.$refs.video.srcObject = this.videoStream;
         } catch (err) {
            console.error("Error accessing camera: ", err);
            this.$toast.error("Could not access camera.");
            this.cameraActive = false;
         }
      },
      stopCamera() {
         if (this.videoStream) {
            this.videoStream.getTracks().forEach(track => track.stop());
            this.videoStream = null;
         }
         this.cameraActive = false;
      },
      capturePhoto() {
         const video = this.$refs.video;
         const canvas = this.$refs.canvas;
         const context = canvas.getContext('2d');
         context.drawImage(video, 0, 0, 320, 240);
         const dataUrl = canvas.toDataURL('image/png');
         this.photoPreview = dataUrl;
         this.form.student.photo = dataUrl;
         this.stopCamera();
      },
      submitForm() {
         if (!this.form.student.admission_number) {
            this.$toast.error('Please generate an admission number first');
            this.generateAdmissionNumber();
            return;
         }
         
         this.form.post(route('admin.admissions.store'), {
            onSuccess: () => {
               this.form.reset();
               this.currentStep = 1;
               this.form.clearErrors();
               this.$toast.success('Student registered successfully', 'Success');
               setTimeout(() => {
                  Inertia.visit('/admin/student-admissions');
               }, 1000)
            },
            onError: (errors) => {
               console.log('Form errors:', errors);
               this.$toast.error('Please check the form for errors and try again', 'Error');
            },
         });
      },
      nextStep() {
         if (!this.canProceedToNextStep) {
            this.$toast.error('Please fill in all required fields before proceeding');
            return;
         }

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
               onError: (errors) => {
                  console.log('Step validation errors:', errors);
                  this.$toast.error('Please check the form for errors before proceeding');
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
            relationship_id: null,
            first_name: '',
            middle_name: '',
            last_name: '',
            email: '',
            phone: '',
            identification_number: '',
            profession: '',
            place_of_work: '',
         });
      },
      removeGuardian(index) {
         if (this.form.guardians.length > 1) {
            this.form.guardians.splice(index, 1);
         } else {
            this.$toast.warning('At least one guardian is required');
         }
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
.form-header {
   display: flex;
   justify-content: space-between;
   margin-bottom: 15px;
   overflow-x: auto;
}

.stepIndicator {
   text-align: center;
   flex: 1 1 120px;
   position: relative;
   padding: 10px 5px;
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

.text-muted {
   color: #6c757d !important;
}

.badge {
   font-size: 0.75em;
   padding: 0.35em 0.65em;
}

.form-control:read-only {
   background-color: #f8f9fa;
   opacity: 1;
}

.bx-loader {
   animation: spin 1s linear infinite;
}

@keyframes spin {
   from { transform: rotate(0deg); }
   to { transform: rotate(360deg); }
}

.btn-outline-primary:hover {
   background-color: #696cff;
   color: white;
}
</style>