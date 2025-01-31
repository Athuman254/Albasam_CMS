<template>
    <div class="row">
        <div class="col-xl-4 col-lg-5 col-md-4 order-1 order-md-0">
            <div class="card mb-6">
                <div class="card-body pt-12">
                    <div class=" d-flex align-items-center flex-column">
                        <div class="avatar avatar-lg rounded bg-label-secondary mb-2 text-center"
                             style="width: 120px; height: 120px; display: flex; align-items: center; justify-content: center;">
                            <i class="bx bxs-user" style="font-size: 120px;"></i>
                        </div>
                        <div class="user-info text-center text-capitalize">
                            <h5>{{ user.name }}</h5>
                            <span class="badge bg-label-secondary">{{ user.username }}</span>
                        </div>
                    </div>
                    <h5 class="pb-4 border-bottom mb-4">Details</h5>
                    <div class="info-container">
                        <ul class="list-unstyled mb-6">
                            <li class="mb-2">
                                <span class="h6 me-2">Username:</span>
                                <span>{{ user.username }}</span>
                            </li>
                            <li class="mb-2">
                                <span class="h6 me-2">Email:</span>
                                <span>{{ user.email }}</span>
                            </li>
                            <li class="mb-2">
                                <span class="h6 me-2">Tax id:</span>
                                <span>{{ user.phone }}</span>
                            </li>
                            <li class="mb-2">
                                <span class="h6 me-2">Role:</span>
                                <span>{{ user.roles[0].display_name }}</span>
                            </li>
                            <li class="mb-2">
                                <span class="h6 me-2">Status:</span>
                                <span v-if="user.activated" class="badge bg-success">Active</span>
                                <span v-else class="badge bg-danger">Deactivated</span>
                            </li>
                        </ul>
<!--                        <div class="d-flex justify-content-center">-->
<!--                            <a href="javascript:;" class="btn btn-primary me-4" data-bs-target="#editUser" data-bs-toggle="modal">Edit</a>-->
<!--                            <a href="javascript:;" class="btn btn-outline-danger suspend-user">Deactivate</a>-->
<!--                        </div>-->
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8 col-lg-7 col-md-8 order-2 order-md-0">
            <div class="nav-align-top">
                <ul class="nav nav-pills mb-4" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button
                            type="button"
                            class="nav-link"
                            :class="{ active: activeTab === 'account' }"
                            @click="activeTab = 'account'"
                        >
                            <i class="bx bx-user me-2"></i> Account
                        </button>
                    </li>
<!--                    <li class="nav-item" role="presentation">-->
<!--                        <button-->
<!--                            type="button"-->
<!--                            class="nav-link"-->
<!--                            :class="{ active: activeTab === 'security' }"-->
<!--                            @click="activeTab = 'security'"-->
<!--                        >-->
<!--                            <i class="bx bx-lock me-2"></i> Security-->
<!--                        </button>-->
<!--                    </li>-->
                </ul>
<!--                <div class="tab-content">-->
<!--                    <div v-show="activeTab === 'account'" class="tab-pane fade show active">-->
<!--                        <h5>Account Tab</h5>-->
<!--                        <p>User account details go here.</p>-->
<!--                    </div>-->
<!--                    <div v-show="activeTab === 'security'" class="tab-pane fade show active">-->
<!--                        <h5>Security Tab</h5>-->
<!--                        <p>Security settings go here.</p>-->
<!--                    </div>-->
<!--                </div>-->
            </div>
            <div v-show="activeTab === 'account'" class="card">
                <h5 class="card-header">
                    Account Settings
                </h5>
                <div class="card-body">
                    <div class="row gx-6">
                        <div class="col-12 col-lg-6">
                            <div class="mb-4">
                                <label for="name" class="form-label">Name</label>
                                <input id="name" type="text" v-model="form.name" class="form-control">
                                <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="mb-4">
                                <label for="username" class="form-label">Username</label>
                                <input id="username" type="text" v-model="form.username" class="form-control">
                                <div v-if="form.errors.username" class="text-danger">{{ form.errors.username }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="mb-4">
                                <label for="email" class="form-label">Email</label>
                                <input id="email" type="email" v-model="form.email" class="form-control">
                                <div v-if="form.errors.email" class="text-danger">{{ form.errors.email }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="mb-4">
                                <label for="phone" class="form-label">Phone</label>
                                <input id="phone" type="text" v-model="form.phone" class="form-control">
                                <div v-if="form.errors.phone" class="text-danger">{{ form.errors.phone }}</div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-6">
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <input id="password" type="password" v-model="form.password" class="form-control">
                                <div v-if="form.errors.password" class="text-danger">{{ form.errors.password }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-primary" @click.prevent="submitForm">Update</button>
                </div>
            </div>
<!--            <div v-show="activeTab === 'security'" class="card">-->
<!--                <h5 class="card-header">-->
<!--                    Security Settings-->
<!--                </h5>-->
<!--                <div class="card-body">-->
<!--                    <p>Security settings go here.</p>-->
<!--                </div>-->
<!--            </div>-->
        </div>
    </div>
</template>

<script>
import {useForm} from "@inertiajs/vue3";

export default {
    props: ['user'],

    data() {
        return {
            form: useForm({
                name: '',
                username: '',
                email: '',
                phone: '',
                password: '',
            }),
            activeTab: "account",
        }
    },
    created() {
        if(this.user) {
            this.form.name = this.user.name;
            this.form.username = this.user.username;
            this.form.email = this.user.email;
            this.form.phone = this.user.phone;
        }
    },
    methods: {
        submitForm() {
            this.form.post('/profile/update', {
                onSuccess: () => {
                    this.form.clearErrors();
                    this.$toast.success('Details Updated Successfully', 'Success')
                },
                onError: (errors) => {
                    console.log(errors);
                    this.$toast.error('An error occurred. Please try again', 'Error')
                },
            })
        }
    }
}
</script>

<style></style>
