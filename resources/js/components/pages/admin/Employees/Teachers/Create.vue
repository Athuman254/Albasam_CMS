<template>
   <div class="row">
      <div class="col-xxl-12">
         <h3 class="mb-0">Teacher Registration Form</h3>
         <nav class="">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link href="/admin/dashboard">Home</Link>
               </li>
               <li class="breadcrumb-item">
                  <Link href="/admin/employees/teachers">Registered Teachers</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Registration Form
               </li>
            </ol>
         </nav>

         <div class="card">
            <div class="card-header border-bottom">
               <div class="form-header">
                  <div class="stepIndicator"
                       :class="{ 'active': currentStep === 1, 'finish': currentStep > 1 }">
                     <div class="square">1</div>
                     <span :class="{ 'text-primary': currentStep > 1 }">Personal Details</span>
                  </div>
                  <div class="stepIndicator"
                       :class="{ 'active': currentStep === 2, 'finish': currentStep > 2 }">
                     <div class="square">2</div>
                     <span :class="{ 'text-primary': currentStep > 2 }">Employee Data</span>
                  </div>
                  <div class="stepIndicator"
                       :class="{ 'active': currentStep === 3, 'finish': currentStep > 3 }">
                     <div class="square">3</div>
                     <span :class="{ 'text-primary': currentStep > 3 }">Other Details</span>
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
                                 <h5 class="mb-0">Personal Details</h5>
                                 <small>Enter Personal Details</small>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="firstName">First Name <span class="text-danger ms-1">*</span></label>
                                    <input type="text" id="firstName" class="form-control"
                                           v-model="form.personal_details.first_name"/>
                                    <div v-if="form.errors['personal_details.first_name']" class="text-danger">
                                       {{ form.errors['personal_details.first_name'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="middleName">Middle Name</label>
                                    <input type="text" id="middleName" class="form-control"
                                           v-model="form.personal_details.middle_name"/>
                                    <div v-if="form.errors['personal_details.middle_name']" class="text-danger">
                                       {{ form.errors['personal_details.middle_name'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="lastName">Last Name <span
                                       class="text-danger ms-1">*</span></label>
                                    <input type="text" id="lastName" class="form-control"
                                           v-model="form.personal_details.last_name"/>
                                    <div v-if="form.errors['personal_details.last_name']" class="text-danger">
                                       {{ form.errors['personal_details.last_name'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="honorificId">Designation/Honorific</label>
                                    <v-select
                                       id="honorificId"
                                       v-model="form.personal_details.honorific_id"
                                       :options="honorifics"
                                       label="name"
                                       :reduce="(option) => option.id"
                                    ></v-select>
                                    <div v-if="form.errors['personal_details.honorific_id']" class="text-danger">
                                       {{ form.errors['personal_details.honorific_id'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="genderId">Gender <span
                                       class="text-danger ms-1">*</span></label>
                                    <v-select
                                       id="genderId"
                                       v-model="form.personal_details.gender_id"
                                       :options="genders"
                                       label="name"
                                       :reduce="(option) => option.id"
                                    ></v-select>
                                    <div v-if="form.errors['personal_details.gender_id']" class="text-danger">
                                       {{ form.errors['personal_details.gender_id'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="religionId">Religion <span
                                       class="text-danger ms-1">*</span></label>
                                    <v-select
                                       id="religionId"
                                       v-model="form.personal_details.religion_id"
                                       :options="religions"
                                       label="name"
                                       :reduce="(option) => option.id"
                                    ></v-select>
                                    <div v-if="form.errors['personal_details.religion_id']" class="text-danger">
                                       {{ form.errors['personal_details.religion_id'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="maritalStatusId">Marital Status <span
                                       class="text-danger ms-1">*</span></label>
                                    <v-select
                                       id="maritalStatusId"
                                       v-model="form.personal_details.marital_status_id"
                                       :options="maritalStatuses"
                                       label="name"
                                       :reduce="(option) => option.id"
                                    ></v-select>
                                    <div v-if="form.errors['personal_details.marital_status_id']" class="text-danger">
                                       {{ form.errors['personal_details.marital_status_id'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" id="email" class="form-control"
                                           v-model="form.personal_details.email"/>
                                    <div v-if="form.errors['personal_details.email']" class="text-danger">
                                       {{ form.errors['personal_details.email'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="primaryPhone">Primary Phone Number <span
                                       class="text-danger ms-1">*</span></label>
                                    <input type="text" id="primaryPhone" class="form-control"
                                           v-model="form.personal_details.primary_phone"/>
                                    <div v-if="form.errors['personal_details.primary_phone']" class="text-danger">
                                       {{ form.errors['personal_details.primary_phone'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="secondaryPhone">Secondary Phone Number</label>
                                    <input type="text" id="secondaryPhone" class="form-control"
                                           v-model="form.personal_details.secondary_phone"/>
                                    <div v-if="form.errors['personal_details.secondary_phone']" class="text-danger">
                                       {{ form.errors['personal_details.secondary_phone'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="permanentAddress">Permanent Address <span
                                       class="text-danger ms-1">*</span></label>
                                    <input type="text" id="permanentAddress" class="form-control"
                                           v-model="form.personal_details.permanent_physical_address"/>
                                    <div v-if="form.errors['personal_details.permanent_physical_address']"
                                         class="text-danger">
                                       {{ form.errors['personal_details.permanent_physical_address'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="secondaryAddress">Secondary Address</label>
                                    <input type="text" id="secondaryAddress" class="form-control"
                                           v-model="form.personal_details.secondary_physical_address"/>
                                    <div v-if="form.errors['personal_details.secondary_physical_address']"
                                         class="text-danger">
                                       {{ form.errors['personal_details.secondary_physical_address'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="postalAddress">Postal Address</label>
                                    <input type="text" id="postalAddress" class="form-control"
                                           v-model="form.personal_details.postal_address"/>
                                    <div v-if="form.errors['personal_details.postal_address']" class="text-danger">
                                       {{ form.errors['personal_details.postal_address'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="identificationNumber">Passport/ID Number <span
                                       class="text-danger ms-1">*</span></label>
                                    <input type="text" id="identificationNumber" class="form-control"
                                           v-model="form.personal_details.identification_number"/>
                                    <div v-if="form.errors['personal_details.identification_number']"
                                         class="text-danger">{{ form.errors['personal_details.identification_number'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="taxPIN">KRA PIN <span
                                       class="text-danger ms-1">*</span></label>
                                    <input type="text" id="taxPIN" class="form-control"
                                           v-model="form.personal_details.tax_identification_pin"/>
                                    <div v-if="form.errors['personal_details.tax_identification_pin']"
                                         class="text-danger">{{
                                          form.errors['personal_details.tax_identification_pin']
                                       }}
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
                                 <h5 class="mb-0">Employee Details</h5>
                                 <small>Enter Employee Data</small>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="dateHired">Date Hired</label>
                                    <date-picker
                                       id="dateHired"
                                       form-class="shadow-sm"
                                       :value="form.employee_details.date_of_hire"
                                       :max-date="new Date()"
                                       @on-change="function(dateObj, dateStr) {
                                                        form.employee_details.date_of_hire = dateStr
                                                    }"
                                    ></date-picker>
                                    <div v-if="form.errors['employee_details.date_of_hire']" class="text-danger">
                                       {{ form.errors['employee_details.date_of_hire'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="staffNumber">Staff/Employee Number</label>
                                    <input type="text" id="staffNumber" class="form-control"
                                           v-model="form.employee_details.staff_number"/>
                                    <div v-if="form.errors['employee_details.staff_number']" class="text-danger">
                                       {{ form.errors['employee_details.staff_number'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="employmentType">Employment Type <span
                                       class="text-danger ms-1">*</span></label>
                                    <v-select
                                       id="employmentType"
                                       v-model="form.employee_details.employment_type_id"
                                       :options="employmentTypes"
                                       label="name"
                                       :reduce="(option) => option.id"
                                    ></v-select>
                                    <div v-if="form.errors['employee_details.employment_type_id']" class="text-danger">
                                       {{ form.errors['employee_details.employment_type_id'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="employmentStatus">Employment Status <span
                                       class="text-danger ms-1">*</span></label>
                                    <v-select
                                       id="employmentStatus"
                                       v-model="form.employee_details.employment_status_id"
                                       :options="employmentStatuses"
                                       label="name"
                                       :reduce="(option) => option.id"
                                    ></v-select>
                                    <div v-if="form.errors['employee_details.employment_type_id']" class="text-danger">
                                       {{ form.errors['employee_details.employment_type_id'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="jobTitleId">Job Title <span class="text-danger ms-1">*</span></label>
                                    <v-select
                                       id="jobTitleId"
                                       v-model="form.employee_details.job_title_id"
                                       :options="jobTitles"
                                       label="name"
                                       :reduce="(option) => option.id"
                                    ></v-select>
                                    <div v-if="form.errors['employee_details.job_title_id']" class="text-danger">
                                       {{ form.errors['employee_details.job_title_id'] }}
                                    </div>
                                 </div>
                              </div>

                              <div class="mb-3 mt-4">
                                 <h5 class="mb-0">Emergency Contact Details</h5>
                                 <small>Capture The Teacher's Emergency Contact</small>
                              </div>
                              <div v-for="(contact, index) in form.employee_details.emergency_contacts" :key="index"
                                   class="col-md-12">
                                 <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                       <h6 class="mb-0">Emergency Contact {{ index + 1 }}</h6>
                                       <p>Complete the form to capture the particulars of all their emergency
                                          contacts.</p>
                                    </div>
                                    <div class="me-3">
                                       <button type="button" v-if="index > 0"
                                               class="btn btn-sm btn-icon btn-danger ms-auto"
                                               @click="removeEmergencyContact(index)">
                                          <i class="bx bx-trash"></i>
                                       </button>
                                    </div>
                                 </div>
                                 <div class="mb-4">
                                    <div class="row gx-4 mb-4">
                                       <div class="col-md-4">
                                          <div class="form-group mb-3">
                                             <label class="form-label" for="contactName">Name</label>
                                             <input type="text" class="form-control" v-model="contact.name"/>
                                             <div v-if="getEmergencyContactError(index, 'name')" class="text-danger">
                                                {{ getEmergencyContactError(index, 'name') }}
                                             </div>
                                          </div>
                                       </div>
                                       <div class="col-md-3">
                                          <div class="form-group mb-3">
                                             <label class="form-label" for="contactEmail">Email</label>
                                             <input type="email" class="form-control" v-model="contact.email"/>
                                             <div v-if="getEmergencyContactError(index, 'email')" class="text-danger">
                                                {{ getEmergencyContactError(index, 'email') }}
                                             </div>
                                          </div>
                                       </div>
                                       <div class="col-md-3">
                                          <div class="form-group mb-3">
                                             <label class="form-label" for="contactPhone">Phone Number</label>
                                             <input type="text" class="form-control" v-model="contact.phone"/>
                                             <div v-if="getEmergencyContactError(index, 'phone')" class="text-danger">
                                                {{ getEmergencyContactError(index, 'phone') }}
                                             </div>
                                          </div>
                                       </div>
                                       <div class="col-md-2">
                                          <div class="form-group mb-3">
                                             <label class="form-label" for="contactRelationship">Relationship</label>
                                             <v-select
                                                id="contactRelationship"
                                                v-model="contact.relationship_id"
                                                :options="relationships"
                                                label="name"
                                                :reduce="(option) => option.id"
                                             ></v-select>
                                             <div v-if="getEmergencyContactError(index, 'qualification_type_id')"
                                                  class="text-danger">
                                                {{ getEmergencyContactError(index, 'qualification_type_id') }}
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-12 mb-8">
                                 <button type="button" class="btn rounded-pill btn-light" @click="addEmergencyContact">
                                    <i class="bx bx-plus-circle me-3"></i>
                                    Add Emergency Contact
                                 </button>
                              </div>
                           </div>
                        </div>
                     </transition>

                     <transition name="fade">
                        <div v-if="currentStep === 3" class="step">
                           <div class="row">
                              <div class="mb-4">
                                 <h5 class="mb-0">Teacher's Details</h5>
                                 <small>Enter Teacher's Data</small>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="tscNumber">TSC Number <span class="text-danger ms-1">*</span></label>
                                    <input type="text" id="tscNumber" class="form-control"
                                           v-model="form.other_details.tsc_number"/>
                                    <div v-if="form.errors['other_details.tsc_number']" class="text-danger">
                                       {{ form.errors['other_details.tsc_number'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="teacherTitleId">Role/Title</label>
                                    <v-select
                                       id="teacherTitleId"
                                       v-model="form.other_details.teacher_title_id"
                                       :options="teacherTitles"
                                       label="name"
                                       :reduce="(option) => option.id"
                                    ></v-select>
                                    <div v-if="form.errors['other_details.teacher_title_id']" class="text-danger">
                                       {{ form.errors['other_details.teacher_title_id'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="specializationId">Specialization</label>
                                    <v-select
                                       id="specializationId"
                                       v-model="form.other_details.specialization_area_id"
                                       :options="specializationAreas"
                                       label="name"
                                       :reduce="(option) => option.id"
                                    ></v-select>
                                    <div v-if="form.errors['other_details.job_title_id']" class="text-danger">
                                       {{ form.errors['other_details.specialization_area_id'] }}
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group mb-3">
                                    <label class="form-label" for="yearsOfExperience">Years Of Experience</label>
                                    <input type="number" id="yearsOfExperience" class="form-control"
                                           v-model="form.other_details.years_of_experience"/>
                                    <div v-if="form.errors['other_details.years_of_experience']" class="text-danger">
                                       {{ form.errors['other_details.years_of_experience'] }}
                                    </div>
                                 </div>
                              </div>

                              <div class="mb-3 mt-4">
                                 <h5 class="mb-0">Qualification Details</h5>
                                 <small>Enter Teacher's Qualification Details</small>
                              </div>
                              <div v-for="(qualification, index) in form.other_details.qualifications" :key="index"
                                   class="col-md-12">
                                 <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                       <h6 class="mb-0">Qualification {{ index + 1 }}</h6>
                                       <p>Complete the form to capture the particulars of all their qualifications.</p>
                                    </div>
                                    <div class="me-3">
                                       <button type="button" v-if="index > 0"
                                               class="btn btn-sm btn-icon btn-danger ms-auto"
                                               @click="removeQualification(index)">
                                          <i class="bx bx-trash"></i>
                                       </button>
                                    </div>
                                 </div>
                                 <div class="mb-4">
                                    <div class="row gx-4 mb-4">
                                       <div class="col-md-4">
                                          <div class="form-group mb-3">
                                             <label class="form-label" for="collegeName">Institution</label>
                                             <input type="text" class="form-control"
                                                    v-model="qualification.institution_name"/>
                                             <div v-if="getQualificationError(index, 'institution_name')"
                                                  class="text-danger">
                                                {{ getQualificationError(index, 'institution_name') }}
                                             </div>
                                          </div>
                                       </div>
                                       <div class="col-md-4">
                                          <div class="form-group mb-3">
                                             <label class="form-label" for="courseName">Course</label>
                                             <input type="text" class="form-control"
                                                    v-model="qualification.course_name"/>
                                             <div v-if="getQualificationError(index, 'course_name')"
                                                  class="text-danger">{{ getQualificationError(index, 'course_name') }}
                                             </div>
                                          </div>
                                       </div>
                                       <div class="col-md-4">
                                          <div class="form-group mb-3">
                                             <label class="form-label" for="qualificationType">Qualification
                                                Type</label>
                                             <v-select
                                                id="qualificationType"
                                                v-model="qualification.qualification_type_id"
                                                :options="qualificationTypes"
                                                label="name"
                                                :reduce="(option) => option.id"
                                             ></v-select>
                                             <div v-if="getQualificationError(index, 'qualification_type_id')"
                                                  class="text-danger">
                                                {{ getQualificationError(index, 'qualification_type_id') }}
                                             </div>
                                          </div>
                                       </div>
                                       <div class="col-md-4">
                                          <div class="form-group mb-3">
                                             <label class="form-label" for="yearsOfExperience">Year Of
                                                Completion</label>
                                             <input type="text" class="form-control"
                                                    v-model="qualification.year_of_completion"/>
                                             <div v-if="getQualificationError(index, 'year_of_completion')"
                                                  class="text-danger">
                                                {{ getQualificationError(index, 'year_of_completion') }}
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-12 mb-8">
                                 <button type="button" class="btn rounded-pill btn-light" @click="addQualification">
                                    <i class="bx bx-plus-circle me-3"></i>
                                    Add Qualification
                                 </button>
                              </div>

                              <div class="mb-3 mt-4">
                                 <h5 class="mb-0">Work History</h5>
                                 <small>Enter Teacher's Work History</small>
                              </div>
                              <div v-for="(history, index) in form.other_details.work_histories" :key="index"
                                   class="col-md-12">
                                 <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                       <h6 class="mb-0">Work History {{ index + 1 }}</h6>
                                       <p>Complete the form to capture the particulars of all their work history.</p>
                                    </div>
                                    <div class="me-3">
                                       <button type="button" v-if="index > 0"
                                               class="btn btn-sm btn-icon btn-danger ms-auto"
                                               @click="removeWorkHistory(index)">
                                          <i class="bx bx-trash"></i>
                                       </button>
                                    </div>
                                 </div>
                                 <div class="mb-3">
                                    <div class="row gx-4 mb-4">
                                       <div class="col-md-6">
                                          <div class="form-group mb-3">
                                             <label class="form-label" for="institutionName">Institution</label>
                                             <input type="text" class="form-control"
                                                    v-model="history.institution_name"/>
                                             <div v-if="getWorkHistoryError(index, 'institution_name')" class="text-danger">
                                                {{getWorkHistoryError(index, 'institution_name') }}
                                             </div>
                                          </div>
                                       </div>
                                       <div class="col-md-3">
                                          <div class="form-group mb-3">
                                             <label class="form-label" for="startDate">Start Date</label>
                                             <date-picker
                                                id="startDate"
                                                form-class="shadow-sm"
                                                :value="history.start_date"
                                                :max-date="new Date()"
                                                @on-change="function(dateObj, dateStr) {
                                                                    history.start_date = dateStr
                                                                }"
                                             ></date-picker>
                                             <div v-if="getWorkHistoryError(index, 'start_date')" class="text-danger">
                                                {{ getWorkHistoryError(index, 'start_date') }}
                                             </div>
                                          </div>
                                       </div>
                                       <div class="col-md-3">
                                          <div class="form-group mb-3">
                                             <label class="form-label" for="endDate">End Date</label>
                                             <date-picker
                                                id="endDate"
                                                form-class="shadow-sm"
                                                :value="history.end_date"
                                                :max-date="new Date()"
                                                @on-change="function(dateObj, dateStr) {
                                                                    history.end_date = dateStr
                                                                }"
                                             ></date-picker>
                                             <div v-if="getWorkHistoryError(index, 'end_date')" class="text-danger">
                                                {{ getWorkHistoryError(index, 'end_date') }}
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-12">
                                 <div class="mx-auto">
                                    <button type="button" class="btn rounded-pill btn-light" @click="addWorkHistory">
                                       <i class="bx bx-plus-circle me-3"></i>
                                       Add History
                                    </button>
                                 </div>
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
                        <div v-if="currentStep !== 3" class="col-auto">
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
</template>

<script>
import {useForm} from "@inertiajs/vue3"
import axios from "axios";
import {Inertia} from "@inertiajs/inertia";

export default {
   data() {
      return {
         form: useForm({
            personal_details: {
               first_name: null,
               middle_name: null,
               last_name: null,
               honorific_id: null,
               marital_status_id: null,
               gender_id: null,
               religion_id: null,
               email: null,
               primary_phone: null,
               secondary_phone: null,
               permanent_physical_address: null,
               secondary_physical_address: null,
               postal_address: null,
               identification_number: null,
               tax_identification_pin: null,
            },
            employee_details: {
               staff_number: null,
               date_of_hire: new Date().toISOString().slice(0, 10),
               employment_type_id: null,
               employment_status_id: null,
               job_title_id: null,
               emergency_contacts: [
                  // {
                  //    name: null',
                  //    email: null',
                  //    phone: null',
                  //    relationship_id: null',
                  // }
               ],
            },
            other_details: {
               specialization_area_id: null,
               teacher_title_id: null,
               tsc_number: null,
               years_of_experience: null,
               qualifications: [
                  // {
                  //    institution_name: null',
                  //    course_name: null',
                  //    qualification_type_id: null',
                  //    year_of_completion: null',
                  // },
               ],
               work_histories: [
                  // {
                  //    institution_name: null',
                  //    start_date: null',
                  //    end_date: null',
                  // }
               ],
            },
         }),

         employmentTypes: [],
         employmentStatuses: [],
         jobTitles: [],
         honorifics: [],
         maritalStatuses: [],
         genders: [],
         religions: [],
         relationships: [],
         specializationAreas: [],
         teacherTitles: [],
         qualificationTypes: [],

         currentStep: 1,
         routes: {
            1: "/admin/employees/teacher-registration/first-step",
            2: "/admin/employees/teacher-registration/second-step",
            3: "/admin/employees/teachers",
            // 4: "/admin/employees/teacher-registration/fourth-step",
         },

         dataFetched: false,
      }
   },
   computed: {
      progressPercentage() {
         return (this.currentStep / Object.keys(this.routes).length) * 100;
      },
   },
   created() {
      // Re-fetch data when navigating back to this component
      Inertia.on('navigate', this.handleNavigation);
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
         const targetUrl = '/admin/employees/teachers/create';
         if (event.detail.page.url === targetUrl && !this.dataFetched) {
            this.fetchAllData();
         }
      },
      fetchAllData() {
         this.fetchedEmploymentTypes();
         this.fetchedEmploymentStatuses();
         this.fetchedJobTitles();
         this.fetchedHonorifics();
         this.fetchedMaritalStatuses();
         this.fetchedGenders();
         this.fetchedReligions();
         this.fetchedRelationships();
         this.fetchedSpecializationAreas();
         this.fetchedTeacherTitles();
         this.fetchedQualificationTypes();

         this.dataFetched = true;
      },
      fetchedEmploymentTypes() {
         axios.get('/datatable/employment-types', {
            params: {
               filter: {
                  activated: true,
               },
            },
         })
            .then(({data}) => {
               this.employmentTypes = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the employment types.')
         })
      },
      fetchedEmploymentStatuses() {
         axios.get('/datatable/employment-statuses', {
            params: {
               filter: {
                  activated: true,
               },
            },
         })
            .then(({data}) => {
               this.employmentStatuses = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the employment statuses.')
         })
      },
      fetchedJobTitles() {
         axios.get('/datatable/job-titles', {
            params: {
               filter: {
                  activated: true,
               },
            },
         })
            .then(({data}) => {
               this.jobTitles = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the job titles.')
         })
      },
      fetchedHonorifics() {
         axios.get('/datatable/honorifics', {
            params: {
               filter: {
                  activated: true,
               },
            },
         })
            .then(({data}) => {
               this.honorifics = data.data;
            }).catch((error) => {
            console.error(error)
            // this.$toast.error('An error occurred while fetching the honorifics.')
         })
      },
      fetchedMaritalStatuses() {
         axios.get('/datatable/marital-statuses', {
            params: {
               filter: {
                  activated: true,
               },
            },
         })
            .then(({data}) => {
               this.maritalStatuses = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the marital statuses.')
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
            // this.$toast.error('An error occurred while fetching the relationships.')
         })
      },
      fetchedSpecializationAreas() {
         axios.get('/datatable/specializations', {
            params: {
               filter: {
                  activated: true,
               },
            },
         })
            .then(({data}) => {
               this.specializationAreas = data.data;
            }).catch((error) => {
            console.error(error)
         })
      },
      fetchedTeacherTitles() {
         axios.get('/datatable/teacher-titles', {
            params: {
               filter: {
                  activated: true,
               },
            },
         })
            .then(({data}) => {
               this.teacherTitles = data.data;
            }).catch((error) => {
            console.error(error)
            // this.$toast.error('An error occurred while fetching the titles.')
         })
      },
      fetchedQualificationTypes() {
         axios.get('/datatable/qualification-types', {
            params: {
               filter: {
                  activated: true,
               },
            },
         })
            .then(({data}) => {
               this.qualificationTypes = data.data;
            }).catch((error) => {
            console.error(error)
            this.$toast.error('An error occurred while fetching the qualification types.')
         })
      },
      submitForm() {
         this.form.post("/admin/employees/teachers", {
            onSuccess: () => {
               this.form.reset();
               (this.currentStep = 1);
               this.form.clearErrors();
               this.$toast.success('Teacher registered successfully', 'Success');
               setTimeout(() => {
                  this.$inertia.visit('/admin/employees/teachers');
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
      addWorkHistory() {
         this.form.other_details.work_histories.push({
            institution_name: '',
            start_date: '',
            end_date: '',
            year_of_completion: '',
         });
      },
      removeWorkHistory(index) {
         this.form.other_details.work_histories.splice(index, 1);
      },
      addQualification() {
         this.form.other_details.qualifications.push({
            institution_name: '',
            course_name: '',
            qualification_type_id: '',
            year_of_completion: '',
         });
      },
      removeQualification(index) {
         this.form.other_details.qualifications.splice(index, 1);
      },
      addEmergencyContact() {
         this.form.employee_details.emergency_contacts.push({
            name: '',
            email: '',
            phone: '',
            relationship_id: '',
         });
      },
      removeEmergencyContact(index) {
         this.form.employee_details.emergency_contacts.splice(index, 1);
      },
      getEmergencyContactError(index, field) {
         return this.form.errors[`employee_details.emergency_contacts.${index}.${field}`];
      },
      getQualificationError(index, field) {
         return this.form.errors[`other_details.qualifications.${index}.${field}`];
      },
      getWorkHistoryError(index, field) {
         return this.form.errors[`other_details.qualifications.${index}.${field}`];
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
