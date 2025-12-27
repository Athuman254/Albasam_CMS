<template>
    <Modal :show="show" @close="$emit('close')" maxWidth="3xl">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                {{ editMode ? 'Edit Employee' : 'Add New Employee' }}
            </h2>

            <form @submit.prevent="submit" class="max-h-[80vh] overflow-y-auto pr-2">
                <!-- Personal Information -->
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3 pb-1 border-b">Personal Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="honorific" class="block text-sm font-medium text-gray-700">Title</label>
                            <select id="honorific" v-model="form.honorific_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select</option>
                                <option v-for="opt in options.honorifics" :key="opt.id" :value="opt.id">{{ opt.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name <span class="text-red-500">*</span></label>
                            <input type="text" id="first_name" v-model="form.first_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" :class="{ 'border-red-300': form.errors.first_name }" required />
                            <p v-if="form.errors.first_name" class="mt-1 text-sm text-red-600">{{ form.errors.first_name }}</p>
                        </div>
                        <div>
                            <label for="middle_name" class="block text-sm font-medium text-gray-700">Middle Name</label>
                            <input type="text" id="middle_name" v-model="form.middle_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" id="last_name" v-model="form.last_name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" :class="{ 'border-red-300': form.errors.last_name }" required />
                            <p v-if="form.errors.last_name" class="mt-1 text-sm text-red-600">{{ form.errors.last_name }}</p>
                        </div>
                        <div>
                            <label for="gender" class="block text-sm font-medium text-gray-700">Gender <span class="text-red-500">*</span></label>
                            <select id="gender" v-model="form.gender_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                                <option value="">Select</option>
                                <option v-for="opt in options.genders" :key="opt.id" :value="opt.id">{{ opt.name }}</option>
                            </select>
                            <p v-if="form.errors.gender_id" class="mt-1 text-sm text-red-600">{{ form.errors.gender_id }}</p>
                        </div>
                        <div>
                            <label for="marital_status" class="block text-sm font-medium text-gray-700">Marital Status</label>
                            <select id="marital_status" v-model="form.marital_status_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select</option>
                                <option v-for="opt in options.maritalStatuses" :key="opt.id" :value="opt.id">{{ opt.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="religion" class="block text-sm font-medium text-gray-700">Religion</label>
                            <select id="religion" v-model="form.religion_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="">Select</option>
                                <option v-for="opt in options.religions" :key="opt.id" :value="opt.id">{{ opt.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3 pb-1 border-b">Contact Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" id="email" v-model="form.email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" :class="{ 'border-red-300': form.errors.email }" />
                            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                        </div>
                        <div>
                            <label for="primary_phone" class="block text-sm font-medium text-gray-700">Primary Phone <span class="text-red-500">*</span></label>
                            <input type="text" id="primary_phone" v-model="form.primary_phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" :class="{ 'border-red-300': form.errors.primary_phone }" required />
                            <p v-if="form.errors.primary_phone" class="mt-1 text-sm text-red-600">{{ form.errors.primary_phone }}</p>
                        </div>
                        <div>
                            <label for="secondary_phone" class="block text-sm font-medium text-gray-700">Secondary Phone</label>
                            <input type="text" id="secondary_phone" v-model="form.secondary_phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                        </div>
                        <div>
                            <label for="postal_address" class="block text-sm font-medium text-gray-700">Postal Address</label>
                            <textarea id="postal_address" v-model="form.postal_address" rows="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label for="permanent_physical_address" class="block text-sm font-medium text-gray-700">Permanent Address</label>
                            <textarea id="permanent_physical_address" v-model="form.permanent_physical_address" rows="1" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Employment Details -->
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3 pb-1 border-b">Employment Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="staff_number" class="block text-sm font-medium text-gray-700">Staff Number (Auto)</label>
                            <input type="text" id="staff_number" v-model="form.staff_number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-100 cursor-not-allowed focus:ring-blue-500 focus:border-blue-500 sm:text-sm" readonly />
                        </div>
                        <div>
                            <label for="date_of_hire" class="block text-sm font-medium text-gray-700">Date of Hire <span class="text-red-500">*</span></label>
                            <input type="date" id="date_of_hire" v-model="form.date_of_hire" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required />
                            <p v-if="form.errors.date_of_hire" class="mt-1 text-sm text-red-600">{{ form.errors.date_of_hire }}</p>
                        </div>
                        <div>
                            <label for="employment_type" class="block text-sm font-medium text-gray-700">Employment Type <span class="text-red-500">*</span></label>
                            <select id="employment_type" v-model="form.employment_type_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                                <option value="">Select</option>
                                <option v-for="opt in options.employmentTypes" :key="opt.id" :value="opt.id">{{ opt.name }}</option>
                            </select>
                            <p v-if="form.errors.employment_type_id" class="mt-1 text-sm text-red-600">{{ form.errors.employment_type_id }}</p>
                        </div>
                        <div>
                            <label for="employment_status" class="block text-sm font-medium text-gray-700">Employment Status <span class="text-red-500">*</span></label>
                            <select id="employment_status" v-model="form.employment_status_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required>
                                <option value="">Select</option>
                                <option v-for="opt in options.employmentStatuses" :key="opt.id" :value="opt.id">{{ opt.name }}</option>
                            </select>
                            <p v-if="form.errors.employment_status_id" class="mt-1 text-sm text-red-600">{{ form.errors.employment_status_id }}</p>
                        </div>
                        <div>
                            <label for="identification_number" class="block text-sm font-medium text-gray-700">ID Number <span class="text-red-500">*</span></label>
                            <input type="text" id="identification_number" v-model="form.identification_number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required />
                            <p v-if="form.errors.identification_number" class="mt-1 text-sm text-red-600">{{ form.errors.identification_number }}</p>
                        </div>
                        <div>
                            <label for="tax_identification_pin" class="block text-sm font-medium text-gray-700">KRA PIN</label>
                            <input type="text" id="tax_identification_pin" v-model="form.tax_identification_pin" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                        </div>
                        <div v-if="isTeacherRole">
                            <label for="tsc_number" class="block text-sm font-medium text-gray-700">TSC Number</label>
                            <input type="text" id="tsc_number" v-model="form.tsc_number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="e.g. 123456" />
                            <p v-if="form.errors.tsc_number" class="mt-1 text-sm text-red-600">{{ form.errors.tsc_number }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <label for="hobbies" class="block text-sm font-medium text-gray-700">Hobbies</label>
                            <textarea id="hobbies" v-model="form.hobbies" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="e.g. Reading, swimming, etc."></textarea>
                            <p v-if="form.errors.hobbies" class="mt-1 text-sm text-red-600">{{ form.errors.hobbies }}</p>
                        </div>
                    </div>
                </div>

                <!-- Media Uploads -->
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3 pb-1 border-b">Media & Documents</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Photo Section -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Employee Photo</label>
                            <div class="flex flex-col items-center p-4 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
                                <div v-if="!cameraActive" class="relative group">
                                    <img v-if="photoPreview || employee?.photo_url" :src="photoPreview || employee?.photo_url" class="w-32 h-32 object-cover rounded-full border-4 border-white shadow-md" alt=""/>
                                    <div v-else class="w-32 h-32 flex items-center justify-center rounded-full bg-blue-100 text-blue-500 border-4 border-white shadow-sm">
                                        <i class='bx bx-user text-5xl'></i>
                                    </div>
                                    <button type="button" @click="$refs.photoInput.click()" class="absolute bottom-0 right-0 bg-blue-600 text-white p-2 rounded-full shadow-lg hover:bg-blue-700 transition">
                                        <i class='bx bx-camera text-xl'></i>
                                    </button>
                                </div>

                                <div v-if="cameraActive" class="w-full">
                                    <video ref="videoElement" autoplay playsinline class="w-full h-48 object-cover rounded-lg bg-black mb-2"></video>
                                    <div class="flex justify-center space-x-2">
                                        <button type="button" @click="capturePhoto" class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700">Capture</button>
                                        <button type="button" @click="stopCamera" class="bg-gray-500 text-white px-3 py-1 rounded text-sm hover:bg-gray-600">Cancel</button>
                                    </div>
                                </div>

                                <div v-if="!cameraActive" class="mt-3 flex space-x-2">
                                    <button type="button" @click="startCamera" class="text-xs text-blue-600 font-medium hover:underline">
                                        <i class="bx bx-camera mr-1"></i> Use Camera
                                    </button>
                                    <span class="text-gray-300">|</span>
                                    <button type="button" @click="$refs.photoInput.click()" class="text-xs text-blue-600 font-medium hover:underline">
                                        Upload File
                                    </button>
                                </div>
                                <input type="file" ref="photoInput" @change="handlePhotoUpload" accept="image/*" class="hidden" />
                                <p v-if="form.errors.photo" class="mt-1 text-xs text-red-600">{{ form.errors.photo }}</p>
                            </div>
                        </div>

                        <!-- Documents Section -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Supportive Documents</label>
                            <div class="flex flex-col p-4 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50 h-full">
                                <div class="flex items-center justify-center h-24 mb-3">
                                    <div class="text-center">
                                        <i class='bx bx-file-blank text-4xl text-gray-400'></i>
                                        <p class="text-xs text-gray-500 mt-1">ID, Certificates, CV, etc.</p>
                                    </div>
                                </div>
                                <input type="file" @change="handleDocumentsUpload" multiple class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
                                <p class="mt-2 text-[10px] text-gray-400">PDF, JPG, PNG (Max 5MB each)</p>
                                <p v-if="form.errors.documents" class="mt-1 text-xs text-red-600">{{ form.errors.documents }}</p>
                                <div v-if="employee?.document_details?.length" class="mt-4 space-y-2">
                                    <p class="text-[10px] font-bold text-gray-500 uppercase">Existing Documents:</p>
                                    <div v-for="doc in employee.document_details" :key="doc.id" class="flex items-center justify-between p-2 bg-white rounded border border-gray-100 shadow-sm">
                                        <div class="flex items-center overflow-hidden">
                                            <i class='bx bx-file text-blue-500 mr-2'></i>
                                            <span class="text-[10px] truncate max-w-[120px]">{{ doc.name }}</span>
                                        </div>
                                        <a :href="doc.url" target="_blank" class="text-blue-600 hover:text-blue-800">
                                            <i class='bx bx-download'></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Role Assignment -->
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3 pb-1 border-b">Role & Designation</h3>
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">Staff Role <span class="text-red-500">*</span></label>
                        <select id="role" v-model="form.role_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" :class="{ 'border-red-300': form.errors.role_id }" required>
                            <option value="">Select Role</option>
                            <option v-for="role in roles" :key="role.id" :value="role.id">
                                {{ role.display_name }}
                            </option>
                        </select>
                        <p v-if="selectedRoleDescription" class="mt-1 text-sm text-blue-600 flex items-center">
                            <i class='bx bx-info-circle mr-1'></i> {{ selectedRoleDescription }}
                        </p>
                        <p v-if="form.errors.role_id" class="mt-1 text-sm text-red-600">{{ form.errors.role_id }}</p>
                    </div>
                </div>

                <!-- Teacher Specific Fields -->
                <div v-if="isTeacherRole" class="mb-6 p-4 bg-blue-50 rounded-md border border-blue-100 animate-fade-in-down">
                    <div class="flex items-center mb-3 text-blue-800">
                        <i class='bx bx-chalkboard mr-2'></i>
                        <span class="text-sm font-medium">Teaching Staff Details</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="subject_specialization" class="block text-sm font-medium text-gray-700">Subject Specialization</label>
                            <select id="subject_specialization" v-model="form.subject_specialization" multiple class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md h-32" :class="{ 'border-red-300': form.errors.subject_specialization }">
                                <option v-for="subject in subjects" :key="subject.id" :value="subject.name">
                                    {{ subject.name }}
                                </option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Hold Ctrl (Windows) or Cmd (Mac) to select multiple subjects.</p>
                            <p v-if="form.errors.subject_specialization" class="mt-1 text-sm text-red-600">{{ form.errors.subject_specialization }}</p>
                        </div>
                        <div>
                            <label for="teaching_qualification" class="block text-sm font-medium text-gray-700">Qualification</label>
                            <select id="teaching_qualification" v-model="form.teaching_qualification" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" :class="{ 'border-red-300': form.errors.teaching_qualification }">
                                <option value="">Select Qualification</option>
                                <option value="Certificate">Certificate</option>
                                <option value="Diploma">Diploma</option>
                                <option value="Bachelor's Degree">Bachelor's Degree</option>
                                <option value="Master's Degree">Master's Degree</option>
                                <option value="PhD">PhD</option>
                            </select>
                            <p v-if="form.errors.teaching_qualification" class="mt-1 text-sm text-red-600">{{ form.errors.teaching_qualification }}</p>
                        </div>
                    </div>
                </div>

                <!-- Payroll Information -->
                <div class="mb-6">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3 pb-1 border-b">Payroll Information</h3>
                    <div class="flex items-center mb-4">
                        <input id="in_payroll" type="checkbox" v-model="form.in_payroll" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                        <label for="in_payroll" class="ml-2 block text-sm text-gray-900">
                            Include in Payroll
                        </label>
                    </div>

                    <div v-if="form.in_payroll" class="grid grid-cols-1 md:grid-cols-2 gap-4 pl-6 border-l-2 border-gray-200">
                        <div class="flex items-center">
                            <input id="pays_paye" type="checkbox" v-model="form.pays_paye" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                            <label for="pays_paye" class="ml-2 block text-sm text-gray-900">Pays PAYE</label>
                        </div>
                        <div class="flex items-center">
                            <input id="pays_housing_levy" type="checkbox" v-model="form.pays_housing_levy" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                            <label for="pays_housing_levy" class="ml-2 block text-sm text-gray-900">Pays Housing Levy</label>
                        </div>

                        <div class="col-span-1">
                            <div class="flex items-center mb-2">
                                <input id="pays_sha" type="checkbox" v-model="form.pays_sha" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                                <label for="pays_sha" class="ml-2 block text-sm text-gray-900">Pays SHA</label>
                            </div>
                            <input v-if="form.pays_sha" type="text" v-model="form.sha_no" placeholder="SHA Number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                        </div>

                        <div class="col-span-1">
                            <div class="flex items-center mb-2">
                                <input id="pays_nssf" type="checkbox" v-model="form.pays_nssf" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                                <label for="pays_nssf" class="ml-2 block text-sm text-gray-900">Pays NSSF</label>
                            </div>
                            <input v-if="form.pays_nssf" type="text" v-model="form.nssf_no" placeholder="NSSF Number" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" />
                        </div>
                    </div>
                </div>

                <!-- System Access -->
                <div class="mb-6 pt-4 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">System Access</h3>

                    <div class="flex items-center mb-4">
                        <input id="has_system_access" type="checkbox" v-model="form.has_system_access" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                        <label for="has_system_access" class="ml-2 block text-sm text-gray-900">
                            Grant System Access (Create User Account)
                        </label>
                    </div>

                    <div v-if="form.has_system_access" class="animate-fade-in-down">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password <span v-if="!editMode" class="text-red-500">*</span></label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input :type="showPassword ? 'text' : 'password'" id="password" v-model="form.password" class="block w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 sm:text-sm pr-10" :class="{ 'border-red-300': form.errors.password }" placeholder="••••••••" :required="!editMode" />
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" @click="showPassword = !showPassword">
                                <i class="bx" :class="showPassword ? 'bx-hide' : 'bx-show'"></i>
                            </div>
                        </div>
                        <p v-if="editMode" class="mt-1 text-xs text-gray-500">Leave blank to keep current password</p>
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button
                        type="button"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        @click="$emit('close')"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="inline-flex justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing" class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>
                        {{ editMode ? 'Update Employee' : 'Create Employee' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: Boolean,
    employee: Object, // If present, edit mode
    roles: Array,
    subjects: Array,
    options: Object,
    nextStaffNumber: String,
});

const emit = defineEmits(['close', 'success']);

const showPassword = ref(false);
const editMode = computed(() => !!props.employee);

const form = useForm({
    // Personal Info
    honorific_id: '',
    first_name: '',
    middle_name: '',
    last_name: '',
    gender_id: '',
    marital_status_id: '',
    religion_id: '',
    date_of_hire: new Date().toISOString().substr(0, 10),

    // Contact Info
    email: '',
    primary_phone: '',
    secondary_phone: '',
    postal_address: '',
    permanent_physical_address: '',
    secondary_physical_address: '',

    // Employment Info
    staff_number: '',
    employment_type_id: '',
    employment_status_id: '',
    identification_number: '',
    tax_identification_pin: '',

    // Role & System
    role_id: '',
    has_system_access: false,
    password: '',

    // Teacher Specific
    subject_specialization: [],
    teaching_qualification: '',

    // Payroll Info
    in_payroll: false,
    pays_paye: false,
    pays_sha: false,
    sha_no: '',
    pays_nssf: false,
    nssf_no: '',
    pays_housing_levy: false,
    tsc_number: '',
    hobbies: '',
    photo: null,
    documents: [],
});

// Watch for changes in the employee prop to populate the form
watch(() => props.employee, (newEmployee) => {
    if (newEmployee) {
        // Edit Mode
        form.honorific_id = newEmployee.honorific_id;
        form.first_name = newEmployee.first_name;
        form.middle_name = newEmployee.middle_name;
        form.last_name = newEmployee.last_name;
        form.gender_id = newEmployee.gender_id;
        form.marital_status_id = newEmployee.marital_status_id;
        form.religion_id = newEmployee.religion_id;
        // Ensure date is in YYYY-MM-DD format for HTML date input
        form.date_of_hire = newEmployee.date_of_hire 
            ? new Date(newEmployee.date_of_hire).toISOString().substr(0, 10) 
            : '';

        form.email = newEmployee.email;
        form.primary_phone = newEmployee.primary_phone;
        form.secondary_phone = newEmployee.secondary_phone;
        form.postal_address = newEmployee.postal_address;
        form.permanent_physical_address = newEmployee.permanent_physical_address;
        form.secondary_physical_address = newEmployee.secondary_physical_address;

        form.staff_number = newEmployee.staff_number;
        form.employment_type_id = newEmployee.employment_type_id;
        form.employment_status_id = newEmployee.employment_status_id;
        form.identification_number = newEmployee.identification_number;
        form.tax_identification_pin = newEmployee.tax_identification_pin;

        // Set role_id from employee.role_id or employee.role.id
        form.role_id = newEmployee.role_id || newEmployee.role?.id || '';
        form.has_system_access = newEmployee.user_id ? true : false;
        form.password = ''; // Password not populated for security

        // Handle subject specialization (string to array)
        form.subject_specialization = newEmployee.subject_specialization
            ? newEmployee.subject_specialization.split(',').map(s => s.trim())
            : [];

        form.teaching_qualification = newEmployee.teaching_qualification;

        form.in_payroll = newEmployee.in_payroll ? true : false;
        form.pays_paye = newEmployee.pays_paye ? true : false;
        form.pays_sha = newEmployee.pays_sha ? true : false;
        form.sha_no = newEmployee.sha_no;
        form.pays_nssf = newEmployee.pays_nssf ? true : false;
        form.nssf_no = newEmployee.nssf_no;
        form.pays_housing_levy = newEmployee.pays_housing_levy ? true : false;
        form.tsc_number = newEmployee.tsc_number || '';
        form.hobbies = newEmployee.hobbies || '';
        form.photo = null;
        form.documents = [];
        photoPreview.value = null;
    } else {
        // Create Mode
        form.reset();
        form.staff_number = props.nextStaffNumber; // Set auto-generated staff number
        form.date_of_hire = new Date().toISOString().substr(0, 10);
    }
}, { immediate: true });

// Computed Properties for Role Logic
const selectedRole = computed(() => {
    return props.roles?.find(r => r.id === form.role_id);
});

const isTeacherRole = computed(() => {
    return selectedRole.value?.name === 'teacher';
});

const selectedRoleDescription = computed(() => {
    return selectedRole.value?.description || '';
});

// Photo & Camera Handling
const photoInput = ref(null);
const photoPreview = ref(null);
const videoElement = ref(null);
const cameraActive = ref(false);
const videoStream = ref(null);

const handlePhotoUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.photo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleDocumentsUpload = (event) => {
    form.documents = Array.from(event.target.files);
};

const startCamera = async () => {
    try {
        videoStream.value = await navigator.mediaDevices.getUserMedia({ video: true });
        cameraActive.value = true;
        setTimeout(() => {
            if (videoElement.value) {
                videoElement.value.srcObject = videoStream.value;
            }
        }, 100);
    } catch (err) {
        console.error("Error accessing camera: ", err);
        alert("Could not access camera. Please ensure permissions are granted.");
    }
};

const stopCamera = () => {
    if (videoStream.value) {
        videoStream.value.getTracks().forEach(track => track.stop());
        videoStream.value = null;
    }
    cameraActive.value = false;
};

const capturePhoto = () => {
    const video = videoElement.value;
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    canvas.toBlob((blob) => {
        const file = new File([blob], 'captured-photo.jpg', { type: 'image/jpeg' });
        form.photo = file;
        photoPreview.value = canvas.toDataURL('image/jpeg');
        stopCamera();
    }, 'image/jpeg');
};

const submit = () => {
    if (editMode.value) {
        form.patch(route('admin.employees.update', props.employee.id), {
            preserveScroll: true,
            onSuccess: (page) => {
                form.reset('password');
                emit('success');
                emit('close');
            },
            onError: (errors) => {
                console.error('Update failed', errors);
            },
        });
    } else {
        form.post(route('admin.employees.store'), {
            preserveScroll: true,
            onSuccess: (page) => {
                form.reset();
                emit('success');
                emit('close');
            },
            onError: (errors) => {
                console.error('Create failed', errors);
            },
        });
    }
};
</script>
