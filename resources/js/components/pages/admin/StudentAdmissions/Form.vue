<template>
    <div class="row">
        <div class="col-xxl-12">
            <h3 class="mb-0">Registration Form</h3>
            <nav class="mb-md-12">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <Link href="/admin/dashboard">Home</Link>
                    </li>
                    <li class="breadcrumb-item">
                        <Link href="/admin/student-admissions">Student Admissions</Link>
                    </li>
                    <li class="breadcrumb-item">
                        Registration Form
<!--                        <a href="#" disabled="disabled">Form</a>-->
                    </li>
                </ol>
            </nav>

            <form id="registrationForm">
                <!-- Step Start-->
                <div class="form-header">
                    <div v-for="(step, index) in steps" :key="index" class="stepIndicator"
                         :class="{ 'active': currentTab === index, 'finish': currentTab > index }">
                        <div class="square">{{ index + 1 }}</div>
                        <span>{{ step }}</span>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress" :style="{ width: progressPercentage + '%' }"></div>
                </div>
                <!-- Steps End -->

                <div class="col-lg-12">
                    <transition name="fade">
                        <div v-if="currentTab === 0" class="step my-5 py-5">
                            <student-form
                                ref="studentForm"
                                @updateStudentDetails="addStudentDetails">
                            </student-form>
                        </div>
                    </transition>

                    <transition name="fade">
                        <div v-if="currentTab === 2" class="step">
                            <p class="my-3 fw-bold h3">Parent Details</p>
                            <div class="row">
                            </div>
                        </div>
                    </transition>

                    <transition name="fade">
                        <div v-if="currentTab === 3" class="step">
                            <p class="my-3 fw-bold h3">Additional Details</p>
                            <div class="row">
                            </div>
                        </div>
                    </transition>

                    <transition name="fade">
                        <div v-if="currentTab === 4" class="step">
                            <p class="my-3 fw-bold h3">Personal Information</p>
                            <div class="row">
                            </div>
                        </div>
                    </transition>

                    <!-- Navigation Start -->
                    <div class="form-footer">
                        <div class="col-md-6">
                            <button type="button" id="prevBtn" @click="nextPrev(-1)" v-show="currentTab > 0">
                                Previous
                            </button>
                        </div>
                        <div class="col-auto">
                            <button
                                type="button"
                                id="nextBtn"
                                @click="nextPrev(1)"
                            >
                                {{ isLastStep ? 'Submit' : 'Next' }}
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
import { useForm } from "@inertiajs/vue3";
import StudentForm from "./StudentForm.vue";
import GuardianForm from "./GuardianForm.vue";

export default {
    components: {StudentForm, GuardianForm},
    data() {
        return {
            currentTab: 0,
            showErrors: false,
            steps: ['General Details', 'Guardian Details', 'Additional Details', 'Account Setup'],

            form: useForm({
                registration_date: '',
                admission_number: '',
                student_details: [],
                guardian_details: [],
            }),
            guardianForm: useForm({
                relationship_id: '',
                first_name: '',
                middle_name: '',
                last_name: '',
                email: '',
                phone: '',
                identification_number: '',
                profession: '',
            }),
        }
    },
    computed: {
        isLastStep() {
            return this.currentTab === this.steps.length - 1;
        },
        progressPercentage() {
            return ((this.currentTab + 1) / this.steps.length) * 100;
        }
    },
    methods: {
        nextPrev(n) {
            // Validate current step before moving forward
            // if (n === 1 && !this.validateCurrentStep()) {
            //     this.showErrors = true; // Indicate validation errors
            //     return;
            // }

            const newTab = this.currentTab + n;

            // Before moving to the next tab, ensure child emits updated data
            if (this.currentTab === 0) {
                this.$refs.studentForm.emitStudentDetails();
            }

            if (newTab >= this.steps.length) {
                this.submitForm();
                return;
            }
            if (newTab < 0) return;

            this.currentTab = newTab;
            this.showErrors = false;
        },
        addStudentDetails(studentData) {
            // Replace any existing student details (if needed)
            this.form.student_details = [studentData];
        },
        // validateCurrentStep() {
        //     // Add specific validation for each step based on currentTab
        //     if (this.currentTab === 0 && this.form.student_details.length === 0) {
        //         this.$toast.error('Please add student details before proceeding.');
        //         return false;
        //     }
        //     if (this.currentTab === 1 && Object.values(this.guardianForm).some((field) => !field)) {
        //         this.$toast.error('Please complete all guardian details.');
        //         return false;
        //     }
        //     return true;
        // },
        // updateStudentDetails(studentData) {
        //     // Ensure the array has at least one entry for the first student
        //     if (!this.form.student_details[0]) {
        //         this.form.student_details.push({});
        //     }
        //
        //     // Update the first student's details
        //     this.form.student_details[0] = { ...studentData };
        // },
        submitForm() {
            console.log('Form submitted:', this.form);
            this.$emit('form-submitted', this.form);
        }
    },
}
</script>

<style scoped>
#registrationForm {
    background: #fff;
    padding: 20px 30px;
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
