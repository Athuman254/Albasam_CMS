<template>
    <div class="row">
        <div class="col-xxl-12">
            <h3 class="mb-0">Registration Form</h3>
            <nav class="">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <Link href="/admin/dashboard">Home</Link>
                    </li>
                    <li class="breadcrumb-item">
                        <Link href="/admin/student-registrations">Student Admissions</Link>
                    </li>
                    <li class="breadcrumb-item">
                        Registration Form
                    </li>
                </ol>
            </nav>

            <form id="registrationForm">
                <!-- Step Start-->
<!--                <div class="form-header">-->
<!--                    <div v-for="(step, index) in steps" :key="index" class="stepIndicator"-->
<!--                         :class="{ 'active': currentTab === index, 'finish': currentTab > index }">-->
<!--                        <div class="square">{{ index + 1 }}</div>-->
<!--                        <span>{{ step }}</span>-->
<!--                    </div>-->
<!--                </div>-->
<!--                <div class="progress-bar">-->
<!--                    <div class="progress" :style="{ width: progressPercentage + '%' }"></div>-->
<!--                </div>-->
                <!-- Steps End -->

                <div class="col-lg-12">
                    <transition name="fade">
                        <div v-if="currentStep === 1" class="step">
                            <div class="row">
                                <div class="mb-4">
                                    <h5 class="mb-0">Student Details</h5>
                                    <small>Enter Student Details</small>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="firstName">Registration Date</label>
                                        <input type="text" id="firstName" class="form-control" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="firstName">First Name</label>
                                        <input type="text" id="firstName" class="form-control" v-model="form.student.first_name" />
                                        <div v-if="form.errors['student.first_name']" class="text-danger">{{ form.errors['student.first_name'] }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="middleName">Middle Name</label>
                                        <input type="text" id="middleName" class="form-control" v-model="form.student.middle_name" />
                                        <div v-if="form.errors['student.middle_name']" class="text-danger">{{ form.errors['student.middle_name'] }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="lastName">Last Name</label>
                                        <input type="text" id="lastName" class="form-control" v-model="form.student.last_name" />
                                        <div v-if="form.errors['student.last_name']" class="text-danger">{{ form.errors['student.last_name'] }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="genderId">Gender</label>
                                        <v-select
                                            id="genderId"
                                            v-model="form.student.gender_id"
                                            :options="genders"
                                            label="name"
                                            :reduce="(option) => option.id"
                                        ></v-select>
                                        <div v-if="form.errors['student.gender_id']" class="text-danger">{{ form.errors['student.gender_id'] }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="religionId">Religion</label>
                                        <v-select
                                            id="religionId"
                                            v-model="form.student.religion_id"
                                            :options="religions"
                                            label="name"
                                            :reduce="(option) => option.id"
                                        ></v-select>
                                        <div v-if="form.errors['student.religion_id']" class="text-danger">{{ form.errors['student.religion_id'] }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="dateOfBirth">Date Of Birth</label>
                                        <input type="text" id="dateOfBirth" class="form-control" v-model="form.student.date_of_birth" />
                                        <div v-if="form.errors['student.date_of_birth']" class="text-danger">{{ form.errors['student.date_of_birth'] }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="birthCertificateNumber">Birth Certificate Number</label>
                                        <input type="text" id="birthCertificateNumber" class="form-control" v-model="form.student.birth_certificate_number" />
                                        <div v-if="form.errors['student.birth_certificate_number']" class="text-danger">{{ form.errors['student.birth_certificate_number'] }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="citizenship">Citizenship</label>
                                        <input type="text" id="citizenship" class="form-control" v-model="form.student.citizenship"/>
                                        <div v-if="form.errors['student.citizenship']" class="text-danger">{{ form.errors['student.citizenship'] }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="county">County</label>
                                        <input type="text" id="county" class="form-control" v-model="form.student.county"/>
                                        <div v-if="form.errors['student.county']" class="text-danger">{{ form.errors['student.county'] }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="ward">Ward</label>
                                        <input type="text" id="ward" class="form-control" v-model="form.student.ward"/>
                                        <div v-if="form.errors['student.ward']" class="text-danger">{{ form.errors['student.ward'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </transition>

                    <transition name="fade">
                        <div v-if="currentStep === 2" class="step">
                            <div v-for="(guardian, index) in form.guardians" :key="index" class="row">
                                <div class="mb-4 d-flex align-items-center justify-content-between">
                                    <div>
                                        <h5 class="mb-0">Guardian {{ index + 1 }}</h5>
                                        <small class="me-2">Enter Guardian's Details</small>
                                    </div>
                                    <div>
                                        <button type="button" v-if="index > 0" class="btn btn-sm btn-danger ms-auto" @click="removeGuardian(index)">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="firstName">First Name</label>
                                        <input type="text" id="firstName" class="form-control" v-model="guardian.first_name" />
                                        <div v-if="getGuardianError(index, 'first_name')" class="text-danger">
                                            {{ getGuardianError(index, 'first_name') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="middleName">Middle Name</label>
                                        <input type="text" id="middleName" class="form-control" v-model="guardian.middle_name" />
                                        <div v-if="getGuardianError(index, 'middle_name')" class="text-danger">
                                            {{ getGuardianError(index, 'middle_name') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="lastName">Last Name</label>
                                        <input type="text" id="lastName" class="form-control" v-model="guardian.last_name" />
<!--                                        <div v-if="form.errors['guardians.${index}.last_name']" class="text-danger">{{ form.errors['guardians.${index}.last_name'] }}</div>-->
                                        <div v-if="getGuardianError(index, 'last_name')" class="text-danger">
                                            {{ getGuardianError(index, 'last_name') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="relationshipId">Relationship</label>
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
                                        <label class="form-label" for="">Email</label>
                                        <input type="text" id="" class="form-control" v-model="guardian.email" />
                                        <div v-if="getGuardianError(index, 'email')" class="text-danger">
                                            {{ getGuardianError(index, 'email') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="">Phone</label>
                                        <input type="text" id="" class="form-control" v-model="guardian.phone" />
                                        <div v-if="getGuardianError(index, 'phone')" class="text-danger">
                                            {{ getGuardianError(index, 'phone') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="identificationNumber">Identification Number</label>
                                        <input type="text" id="identificationNumber" class="form-control" v-model="guardian.identification_number" />
                                        <div v-if="getGuardianError(index, 'identification_number')" class="text-danger">
                                            {{ getGuardianError(index, 'identification_number') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="occupation">Profession/Occupation</label>
                                        <input type="text" id="occupation" class="form-control" v-model="guardian.profession" />
                                        <div v-if="getGuardianError(index, 'occupation')" class="text-danger">
                                            {{ getGuardianError(index, 'occupation') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mt-3">
                                    <button type="button" class="btn rounded-pill btn-outline-success w-100" @click="addGuardian">
                                        <i class="bx bx-plus-circle me-3"></i>
                                        Add Guardian
                                    </button>
                                </div>
                            </div>
                        </div>
                    </transition>

                    <transition name="fade">
                        <div v-if="currentStep === 3" class="step">
                            <div class="row">
                            </div>
                        </div>
                    </transition>

                    <!-- Navigation Start -->
                    <div class="form-footer px-0">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-secondary" :class="{ 'disabled': currentStep === 1}" @click="prevStep">
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
                        <div  v-else class="col-auto">
                            <button
                                type="button"
                                class="btn btn-primary"
                                @click.prevent="submitForm"
                            >
                                Submit
                            </button>
                        </div>
                    </div>
                    <!-- Navigation End -->
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { useForm } from "@inertiajs/vue3"
import { Inertia } from "@inertiajs/inertia"
import {router} from "@inertiajs/vue3";

export default{
    data() {
        return {
            form: useForm({
                student: {
                    first_name: '',
                    middle_name: '',
                    last_name: '',
                    date_of_birth: '',
                    birth_certificate_number: '',
                    gender_id: '',
                    religion_id: '',
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
                guardians: [
                    {
                        relationship_id: '',
                        first_name: '',
                        middle_name: '',
                        last_name: '',
                        email: '',
                        phone: '',
                        identification_number: '',
                        profession: '',
                    },
                ],
                generalDetails: {
                    detail_1: '',
                    detail_2: '',
                },
            }),
            genders: [],
            religions: [],
            relationships: [],

            currentStep: 1,
            routes: {
                1: "/admin/student-registrations/first-step",
                2: "/admin/student-registrations/second-step",
                3: "/admin/student-registrations/last-step",
            },
        }
    },
    methods: {
        submitForm() {
            this.form.post("/admin/student-registrations", {
                onSuccess: () => {
                    this.form.reset();
                    (this.currentStep.id = 1);
                },
                onError: (errors) => {
                    this.$toast.error('An error occurred. Please try again', 'Error')
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
                // {
                //     'student.first_name': this.form.student.first_name,
                // },
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
        getGuardianError(index, field) {
            return this.form.errors[`guardians.${index}.${field}`];
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
