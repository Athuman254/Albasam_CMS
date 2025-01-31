<template>
    <div class="profile">
        <h3 class="mb-0">Institution Profile</h3>
        <nav class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <Link href="/admin/dashboard">Home</Link>
                </li>
                <li class="breadcrumb-item">
                    Institution Profile
                </li>
            </ol>
        </nav>

        <div v-if="!institution">
            <div class="d-flex justify-content-center align-content-center">
                <div class="flex flex-column items-center justify-center">
                    <div class="flex flex-column items-center justify-center">
                        <i class="bx bxs-school" style="font-size: 90px;"></i>
                    </div>
                    <div class="mt-2">
                        <label class="font-medium">
                            No institution registered!
                        </label>
                    </div>
                    <div class="mt-2 text-center">
                        <label class="text-gray-500">
                            This page will provide details of your institution once you register it to the system.
                        </label>
                    </div>
                    <div class="mt-6">
                        <Link href="/admin/institutions/create" class="btn btn-outline-primary">
                            <i class="bx bx-plus-circle me-2"></i>
                            Register Institution
                        </Link>
                    </div>
                </div>
            </div>
        </div>
        <div v-else>
            <div class="row">
                <div class="col-xl-10 me-auto">
                    <div class="card">
                        <div class="card-header d-inline-block justify-content-between align-items-center">
                            <h5 class="mb-0">Institution Info</h5>
                            <small class="text-muted">
                                Information about your institute that will be displayed on forms and other documents created by the App.
                            </small>
                        </div>
                        <div class="card-body">
                            <!-- Profile Edit Form -->
                            <div>
                                <div class="row">
                                    <div class="divider">
                                        <div class="divider-text">GENERAL INFORMATION</div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-6 form-group">
                                            <label class="form-label" for="instituteName">Institute Name</label>
                                            <div class="input-group input-group-merge">
                                            <span class="input-group-text">
                                                <i class="bx bx-buildings"></i>
                                            </span>
                                                <input
                                                    v-model="form.name"
                                                    id="instituteName"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="ACME Inc."/>
                                            </div>
                                            <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-6">
                                            <label class="form-label" for="email">Email</label>
                                            <div class="input-group input-group-merge">
                                            <span class="input-group-text">
                                                <i class="bx bx-envelope"></i>
                                            </span>
                                                <input
                                                    v-model="form.email"
                                                    id="email"
                                                    type="email"
                                                    class="form-control"
                                                    placeholder="info@institute.com" />
                                            </div>
                                            <div v-if="form.errors.email" class="text-danger">{{ form.errors.email }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-6">
                                            <label for="phone" class="form-label">Phone</label>
                                            <div class="input-group input-group-merge">
                                            <span class="input-group-text">
                                                <i class="bx bx-phone"></i>
                                            </span>
                                                <input
                                                    v-model="form.phone"
                                                    id="phone"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="2547xxxxxxxx" />
                                            </div>
                                            <div v-if="form.errors.phone" class="text-danger">{{ form.errors.phone }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-6">
                                            <label for="country" class="form-label">Country</label>
                                            <div class="input-group input-group-merge">
                                            <span class="input-group-text">
                                                <i class="bx bx-world"></i>
                                            </span>
                                                <input
                                                    v-model="form.country"
                                                    id="country"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="Kenya" />
                                            </div>
                                            <div v-if="form.errors.country" class="text-danger">{{ form.errors.country }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-6">
                                            <label for="state" class="form-label">State</label>
                                            <div class="input-group input-group-merge">
                                            <span class="input-group-text">
                                                <i class="bx bxs-flag-alt"></i>
                                            </span>
                                                <input
                                                    v-model="form.state"
                                                    id="state"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="Mombasa" />
                                            </div>
                                            <div v-if="form.errors.state" class="text-danger">{{ form.errors.state }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-6 form-group">
                                            <label for="city" class="form-label">City</label>
                                            <div class="input-group input-group-merge">
                                            <span class="input-group-text">
                                                <i class="bx bxs-city"></i>
                                            </span>
                                                <input
                                                    v-model="form.city"
                                                    id="city"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="Mombasa" />
                                            </div>
                                            <div v-if="form.errors.city" class="text-danger">{{ form.errors.city }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-6">
                                            <label for="physical_address" class="form-label">Address</label>
                                            <div class="input-group input-group-merge">
                                            <span class="input-group-text">
                                                <i class="bx bxs-location-plus"></i>
                                            </span>
                                                <input
                                                    v-model="form.physical_address"
                                                    id="physical_address"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="Grandville, Alexandria Street" />
                                            </div>
                                        </div>
                                        <div v-if="form.errors.physical_address" class="text-danger">{{ form.errors.physical_address }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-6">
                                            <label for="postal_code" class="form-label">Postal Code</label>
                                            <div class="input-group input-group-merge">
                                            <span class="input-group-text">
                                                <i class="bx bx-box"></i>
                                            </span>
                                                <input
                                                    v-model="form.postal_address"
                                                    id="postal_address"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="0001-8888" />
                                            </div>
                                            <div v-if="form.errors.postal_address" class="text-danger">{{ form.errors.postal_address }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-6">
                                            <label for="zip" class="form-label">Tax Identification Number</label>
                                            <div class="input-group input-group-merge">
                                            <span class="input-group-text">
                                                <i class="bx bx-file"></i>
                                            </span>
                                                <input
                                                    v-model="form.tax_identification_pin"
                                                    id="tax_identification_number"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="A0000001P" />
                                            </div>
                                            <div v-if="form.errors.tax_identification_pin" class="text-danger">{{ form.errors.tax_identification_pin }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-6">
                                            <label for="mission" class="form-label">Mission</label>
                                            <div class="input-group input-group-merge">
                                                <span class="input-group-text">
                                                    <i class='bx bx-bullseye'></i>
                                                </span>
                                                <textarea
                                                    v-model="form.mission"
                                                    id="mission"
                                                    rows="3"
                                                    class="form-control"
                                                    placeholder="Write something..." />
                                            </div>
                                            <div v-if="form.errors.mission" class="text-danger">{{ form.errors.mission }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-6">
                                            <label for="vision" class="form-label">Vision</label>
                                            <div class="input-group input-group-merge">
                                                <span class="input-group-text">
                                                    <i class='bx bx-bullseye'></i>
                                                </span>
                                                <textarea
                                                    v-model="form.vision"
                                                    id="vision"
                                                    rows="3"
                                                    class="form-control"
                                                    placeholder="Write something..." />
                                            </div>
                                            <div v-if="form.errors.vision" class="text-danger">{{ form.errors.vision }}</div>
                                        </div>
                                    </div>
                                    <div class="divider">
                                        <div class="divider-text">SOCIAL MEDIA HANDLES</div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-6">
                                            <label for="twitter" class="form-label">Twitter</label>
                                            <div class="input-group input-group-merge">
                                            <span class="input-group-text">
                                                <i class='bx bxl-twitter'></i>
                                            </span>
                                                <input
                                                    v-model="form.x_profile"
                                                    id="twitter"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="x.com" />
                                            </div>
                                            <div v-if="form.errors.x_profile" class="text-danger">{{ form.errors.x_profile }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-6">
                                            <label for="facebook" class="form-label">Facebook</label>
                                            <div class="input-group input-group-merge">
                                            <span class="input-group-text">
                                                <i class='bx bxl-facebook'></i>
                                            </span>
                                                <input
                                                    v-model="form.fb_profile"
                                                    id="facebook"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="facebook.com" />
                                            </div>
                                            <div v-if="form.errors.fb_profile" class="text-danger">{{ form.errors.fb_profile }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-6">
                                            <label for="instagram" class="form-label">Instagram</label>
                                            <div class="input-group input-group-merge">
                                            <span class="input-group-text">
                                                <i class='bx bxl-instagram-alt'></i>
                                            </span>
                                                <input
                                                    v-model="form.ig_profile"
                                                    id="instagram"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="instagram.com" />
                                            </div>
                                            <div v-if="form.errors.ig_profile" class="text-danger">{{ form.errors.mission }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-6">
                                            <label for="tiktok" class="form-label">Tiktok</label>
                                            <div class="input-group input-group-merge">
                                            <span class="input-group-text">
                                                <i class='bx bxl-tiktok'></i>
                                            </span>
                                                <input
                                                    v-model="form.tiktok_profile"
                                                    id="tiktok"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="tiktok.com" />
                                            </div>
                                            <div v-if="form.errors.tiktok_profile" class="text-danger">{{ form.errors.tiktok_profile }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-6">
                                            <label for="youtube" class="form-label">Youtube</label>
                                            <div class="input-group input-group-merge">
                                            <span class="input-group-text">
                                                <i class='bx bxl-youtube'></i>
                                            </span>
                                                <input
                                                    v-model="form.youtube_profile"
                                                    id="youtube"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="youtube.com" />
                                            </div>
                                            <div v-if="form.errors.youtube_profile" class="text-danger">{{ form.errors.youtube_profile }}</div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary" @click.prevent="updateDetails">
                                    Update
                                </button>

                                <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="my-3 mx-0">
                                    {{ form.progress.percentage }}%
                                </progress>
                            </div>
                            <!-- End Profile Edit Form -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { useForm } from "@inertiajs/vue3";
import {Inertia} from "@inertiajs/inertia";
import axios from "axios";

export default {
    props: {
        // institution: {
        //     type: Object,
        //     required: true,
        //     default: () => ({})
        // },
        divisions: {
            type: Array,
            required: true,
        },
    },
    data() {
        return {
            form: useForm({
                id: '',
                name: '',
                email: '',
                phone: '',
                country: '',
                state: '',
                city: '',
                physical_address: '',
                postal_address: '',
                tax_identification_pin: '',
                mission: '',
                vision: '',
                x_profile: '',
                fb_profile: '',
                ig_profile: '',
                youtube_profile: '',
                tiktok_profile: '',
            }),
            institution: {},
        };
    },
    created() {
        if(this.institution) {
            this.form.id = this.institution.hashid;
            this.form.name = this.institution.name;
            this.form.email = this.institution.email;
            this.form.phone = this.institution.phone;
            this.form.country = this.institution.country;
            this.form.state = this.institution.state;
            this.form.city = this.institution.city;
            this.form.physical_address = this.institution.physical_address;
            this.form.postal_address = this.institution.postal_address;
            this.form.tax_identification_pin = this.institution.tax_identification_pin;
            this.form.mission = this.institution.mission;
            this.form.vision = this.institution.vision;
            this.form.x_profile = this.institution.x_profile;
            this.form.fb_profile = this.institution.fb_profile;
            this.form.ig_profile = this.institution.ig_profile;
            this.form.tiktok_profile = this.institution.tiktok_profile;
            this.form.youtube_profile = this.institution.youtube_profile;
        }
    },
    mounted() {
        this.institutionDetails();
    },
    methods: {
        institutionDetails() {
            axios.get('/datatable/institution')
                .then(({ data }) => {
                    this.institution = data;
                    if(this.institution) {
                        this.populateForm();
                    }
                }).catch((error) => {
                console.error(error)
                this.$toast.error('An error occurred when fetching the institution details.')
            })
        },
        populateForm() {
            this.form.id = this.institution.hashid || '';
            this.form.name = this.institution.name || '';
            this.form.email = this.institution.email || '';
            this.form.phone = this.institution.phone || '';
            this.form.country = this.institution.country || '';
            this.form.state = this.institution.state || '';
            this.form.city = this.institution.city || '';
            this.form.physical_address = this.institution.physical_address || '';
            this.form.postal_address = this.institution.postal_address || '';
            this.form.tax_identification_pin = this.institution.tax_identification_pin || '';
            this.form.mission = this.institution.mission || '';
            this.form.vision = this.institution.vision || '';
            this.form.x_profile = this.institution.x_profile || '';
            this.form.fb_profile = this.institution.fb_profile || '';
            this.form.ig_profile = this.institution.ig_profile || '';
            this.form.tiktok_profile = this.institution.tiktok_profile || '';
            this.form.youtube_profile = this.institution.youtube_profile || '';
        },
        updateDetails() {
            this.form.patch('/admin/institutions/' + this.institution.hashid, {
                onSuccess: () => {
                    this.form.clearErrors();
                    this.$toast.success('Institution details updated', 'Updated');
                    // this.$inertia.visit('/admin/institutions');
                    this.institutionDetails();
                },
                onError: (errors) => {
                    console.log(errors);
                    this.$toast.error('An error occurred. Please try again', 'Error');
                },
            });
        },
    },
}
</script>

<style scoped>
.profile-con {
    width: 100px;
    height: 100px;
    display: flex;
}

/*--------------------------------------------------------------
# Profie Page
--------------------------------------------------------------*/
.profile .profile-card img {
    max-width: 120px;
}

.profile .profile-card h2 {
    font-size: 24px;
    font-weight: 700;
    color: #2c384e;
    margin: 10px 0 0 0;
}

.profile .profile-card h3 {
    font-size: 18px;
}

.profile .profile-card .social-links a {
    font-size: 20px;
    display: inline-block;
    color: rgba(1, 41, 112, 0.5);
    line-height: 0;
    margin-right: 10px;
    transition: 0.3s;
}

.profile .profile-card .social-links a:hover {
    color: #012970;
}

.profile .profile-overview .row {
    margin-bottom: 20px;
    font-size: 15px;
}

.profile .profile-overview .card-title {
    color: #012970;
}

.profile .profile-overview .label {
    font-weight: 600;
    color: rgba(1, 41, 112, 0.6);
}

.profile .profile-edit label {
    font-weight: 600;
    color: rgba(1, 41, 112, 0.6);
}

.profile .profile-edit img {
    max-width: 120px;
}
</style>
