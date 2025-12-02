<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(route('student.password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Change Password" />

        <div class="card px-sm-6 px-0">
            <div class="card-body">
                <!-- Logo -->
                <div class="app-brand justify-content-center mb-4">
                    <a href="#" class="app-brand-link gap-2">
                        <span class="app-brand-text demo text-body fw-bold">Change Password</span>
                    </a>
                </div>
                <!-- /Logo -->

                <h4 class="mb-2 text-center">Secure Your Account 🔒</h4>
                <p class="mb-4 text-center">Please update your password to continue.</p>

                <form id="formAuthentication" class="mb-3" @submit.prevent="submit">
                    <div class="mb-3 form-password-toggle">
                        <label class="form-label" for="current_password">Current Password</label>
                        <div class="input-group input-group-merge">
                            <input
                                type="password"
                                id="current_password"
                                class="form-control"
                                v-model="form.current_password"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                aria-describedby="current_password"
                                autofocus
                            />
                            <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                        </div>
                        <div v-if="form.errors.current_password" class="text-danger mt-1 text-sm">{{ form.errors.current_password }}</div>
                    </div>

                    <div class="mb-3 form-password-toggle">
                        <label class="form-label" for="password">New Password</label>
                        <div class="input-group input-group-merge">
                            <input
                                type="password"
                                id="password"
                                class="form-control"
                                v-model="form.password"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                aria-describedby="password"
                            />
                            <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                        </div>
                        <div v-if="form.errors.password" class="text-danger mt-1 text-sm">{{ form.errors.password }}</div>
                    </div>

                    <div class="mb-3 form-password-toggle">
                        <label class="form-label" for="password_confirmation">Confirm New Password</label>
                        <div class="input-group input-group-merge">
                            <input
                                type="password"
                                id="password_confirmation"
                                class="form-control"
                                v-model="form.password_confirmation"
                                placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                aria-describedby="password_confirmation"
                            />
                            <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                        </div>
                        <div v-if="form.errors.password_confirmation" class="text-danger mt-1 text-sm">{{ form.errors.password_confirmation }}</div>
                    </div>

                    <button class="btn btn-primary d-grid w-100" :disabled="form.processing">
                        Change Password
                    </button>
                </form>

                <div class="text-center">
                    <Link :href="route('logout')" method="post" as="button" class="d-flex align-items-center justify-content-center w-100 btn btn-link">
                        <i class="bx bx-chevron-left scaleX-n1-rtl bx-sm"></i>
                        Back to Login
                    </Link>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
