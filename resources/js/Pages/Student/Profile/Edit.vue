<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import ChangePasswordModal from '@/Components/Student/ChangePasswordModal.vue';

const props = defineProps({
    student: Object,
});

const form = useForm({
    permanent_address: props.student.permanent_address,
    hobby: props.student.hobby,
    medical_details: props.student.medical_details,
});

const showPasswordModal = ref(false);

const submit = () => {
    form.put(route('student.profile.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="My Profile" />

    <StudentLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                My Profile
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <!-- Profile Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center mb-6">
                            <div class="flex-shrink-0 h-24 w-24">
                                <img class="h-24 w-24 rounded-full object-cover border-4 border-gray-200" :src="student.photo_url" alt="Profile Photo">
                            </div>
                            <div class="ml-6">
                                <h3 class="text-2xl font-bold text-gray-900">{{ student.first_name }} {{ student.last_name }}</h3>
                                <p class="text-sm text-gray-500">{{ student.admission_number }} &bull; {{ student.class }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-200 pt-6">
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Personal Details</h4>
                                <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500">Date of Birth</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ student.date_of_birth || 'N/A' }}</dd>
                                    </div>
                                    <div class="sm:col-span-1">
                                        <dt class="text-sm font-medium text-gray-500">Gender</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ student.gender }}</dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <!-- Editable Fields -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Edit Information</h4>
                                <form @submit.prevent="submit">
                                    <div class="space-y-4">
                                        <div>
                                            <label for="permanent_address" class="block text-sm font-medium text-gray-700">Permanent Address</label>
                                            <textarea id="permanent_address" v-model="form.permanent_address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                            <div v-if="form.errors.permanent_address" class="text-red-500 text-xs mt-1">{{ form.errors.permanent_address }}</div>
                                        </div>

                                        <div>
                                            <label for="hobby" class="block text-sm font-medium text-gray-700">Hobbies</label>
                                            <input type="text" id="hobby" v-model="form.hobby" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <div v-if="form.errors.hobby" class="text-red-500 text-xs mt-1">{{ form.errors.hobby }}</div>
                                        </div>

                                        <div>
                                            <label for="medical_details" class="block text-sm font-medium text-gray-700">Medical Details</label>
                                            <textarea id="medical_details" v-model="form.medical_details" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                                            <div v-if="form.errors.medical_details" class="text-red-500 text-xs mt-1">{{ form.errors.medical_details }}</div>
                                        </div>

                                        <div class="flex items-center justify-end">
                                            <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" :disabled="form.processing">
                                                Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Actions -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Account Security</h4>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Update your password to keep your account secure.</p>
                            </div>
                            <button @click="showPasswordModal = true" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Change Password
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Change Modal -->
        <ChangePasswordModal :show="showPasswordModal" @close="showPasswordModal = false" />
    </StudentLayout>
</template>
