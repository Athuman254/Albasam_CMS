<script>
import DefaultLayout from '../../layouts/DefaultLayout.vue';
import { useForm } from "@inertiajs/vue3";

export default {
    layout: DefaultLayout,
    props: {
        site_settings: {
            type: Object,
            default: () => { }
        }
    },
    data() {
        return {
            currentTab: 'general',
            previewDevice: 'Desktop',
            tabs: [
                { label: 'General', value: 'general' },
                { label: 'Appearance', value: 'appearance' },
                { label: 'SEO', value: 'seo' }
            ],
            devices: ['Desktop', 'Tablet', 'Mobile'],
            settings: useForm({
                id: null,
                template_name: 'Default Template',
                color: '#4f46e5',
                secondary_color: '#818cf8',
                font_family: 'Arial, sans-serif',
                background_image: '',
                banner_text: 'Welcome to our website!',
                show_banner: true,
                meta_title: 'My Awesome Site',
                meta_description: 'A great website with amazing content',
                meta_keywords: 'awesome, amazing-school, great, school, ',
                is_active: true
            }),
            initialSettings: null

        };
    },
    created() {
        if(Object.keys(this.site_settings).length !== 0){
            this.settings.id = this.site_settings?.id
            this.settings.template_name = this.site_settings?.template_name
            this.settings.color = this.site_settings?.color
            this.settings.secondary_color = this.site_settings?.secondary_color,
            this.settings.font_family = this.site_settings?.font_family
            this.settings.meta_description = this.site_settings?.meta_description
            this.settings.meta_keywords = this.site_settings?.meta_keywords
            this.settings.is_active = this.site_settings?.is_active
        }
    },
    methods: {
        copyObject(sourceObj, targetObj) {
            const sourceKeys = Object.keys(sourceObj);
            const targetKeys = Object.keys(targetObj);
            const matchingKeys = sourceKeys.filter(key => targetKeys.includes(key));
            const result = { ...targetObj };
            matchingKeys.forEach(key => {
                result[key] = sourceObj[key];
            });

            return result;
        },
        saveChange() {
            this.settings.post('/website/settings', {
                onSuccess: () => {
                    this.settings.clearErrors();
                    this.$toast.success('Settings Updated', 'Success')
                },
                onError: (errors) => {
                    this.$toast.error('An error occurred. Please try again', 'Error')
                },
            });
        }
    },
}
</script>

<template>
    <div class="row">
        <div class="col-xxl-12">
            <h3>Site settings</h3>

            <div class="card">
                <div class="rosw p-4">
                    <div class="row py-5">
                        <!-- Left Sidebar -->
                        <div class="col-lg-4 space-y-4">
                            <!-- Quick Actions Card -->
                            <div class="bg-white rounded-lg shadow p-4">
                                <h5 class="font-semibold mb-4">Quick Actions</h5>
                                <div class="space-y-2">
                                    <button
                                        class="flex items-center w-full px-3 py-2 bg-light text-left rounded hover:bg-gray-100">
                                        <i class="fas fa-eye mr-2"></i>
                                        Visit Site
                                    </button>
                                    <button
                                        class="flex items-center w-full px-3 bg-light py-2 text-left rounded hover:bg-gray-100">
                                        <i class="fas fa-cog mr-2"></i>
                                        Clear Cache
                                    </button>
                                </div>
                            </div>

                            <!-- Status Card -->
                            <div class="bg-white rounded-lg shadow p-4">
                                <h5 class="font-semibold mb-4">Status</h5>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-sm text-gray-600">Site Status</label>
                                        <div class="flex items-center mt-1 space-x-2">
                                            <div
                                                :class="['h-3 w-3 rounded-full', settings.is_active ? 'bg-green-500' : 'bg-red-500']">
                                            </div>
                                            <span class="text-sm">{{ settings.is_active ? 'Active' : 'Inactive'
                                                }}</span>
                                        </div>
                                    </div>
                                    <!-- <div>
                                        <label class="text-sm text-gray-600">Banner Status</label>
                                        <div class="flex items-center mt-1 space-x-2">
                                            <div
                                                :class="['h-3 w-3 rounded-full', settings.show_banner ? 'bg-green-500' : 'bg-red-500']">
                                            </div>
                                            <span class="text-sm">{{ settings.show_banner ? 'Visible' : 'Hidden'
                                                }}</span>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                        </div>

                        <!-- Main Content -->
                        <div class="col-lg-4">
                            <!-- Tabs -->
                            <div class="bg-white rounded-lg shadow">
                                <div class="border-b">
                                    <nav class="flex space-x-4 px-4">
                                        <button v-for="tab in tabs" :key="tab.value" @click="currentTab = tab.value"
                                            :class="[
                                                'px-4 py-3 text-sm font-medium',
                                                currentTab === tab.value ? 'border-b-2 border-blue-500 text-blue-600' : 'text-gray-500 hover:text-gray-700'
                                            ]">
                                            {{ tab.label }}
                                        </button>
                                    </nav>
                                </div>

                                <!-- Tab Content -->
                                <div class="p-6">
                                    <!-- General Settings -->
                                    <div v-if="currentTab === 'general'" class="space-y-6">
                                        <div class="space-y-4">
                                            <label class="block">
                                                <span class="text-gray-700">Template Name *</span>
                                                <select v-model="settings.template_name" class="form-select" name=""
                                                    id="">
                                                    <option value="0">default</option>
                                                    <option value="1">Template two</option>
                                                    <option value="2">Template three</option>
                                                </select>
                                            </label>

                                            <div class="flex items-center justify-between">
                                                <span class="text-gray-700">Site Active</span>
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" v-model="settings.is_active"
                                                        class="sr-only peer">
                                                    <div
                                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                                    </div>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- <div class="space-y-4">
                                            <h3 class="text-lg font-medium">Banner Settings</h3>

                                            <div class="flex items-center justify-between">
                                                <span class="text-gray-700">Show Banner</span>
                                                <label class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" v-model="settings.show_banner"
                                                        class="sr-only peer">
                                                    <div
                                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                                                    </div>
                                                </label>
                                            </div>

                                            <label class="block">
                                                <span class="text-gray-700">Banner Text</span>
                                                <textarea v-model="settings.banner_text" rows="3"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"></textarea>
                                            </label>
                                        </div> -->
                                    </div>

                                    <!-- Appearance Settings -->
                                    <div v-if="currentTab === 'appearance'" class="space-y-6">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="block">
                                                    <span class="text-gray-700">Primary Color</span>
                                                    <div class="mt-1 flex space-x-2">
                                                        <input v-model="settings.color" type="color" class="h-10 w-16">
                                                        <input v-model="settings.color" type="text"
                                                            class="form-control">
                                                    </div>
                                                </label>
                                            </div>

                                            <div>
                                                <label class="block">
                                                    <span class="text-gray-700">Secondary Color</span>
                                                    <div class="mt-1 flex space-x-2">
                                                        <input v-model="settings.secondary_color" type="color"
                                                            class="h-10 w-16">
                                                        <input v-model="settings.secondary_color" type="text"
                                                            class="form-control">
                                                    </div>
                                                </label>
                                            </div>
                                        </div>

                                        <label class="block">
                                            <span class="text-gray-700">Font Family</span>
                                            <input v-model="settings.font_family" type="text"
                                                class="form-control focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        </label>

                                        <!-- <label class="block">
                                            <span class="text-gray-700">Background Image URL</span>
                                            <input v-model="settings.background_image" type="text"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        </label> -->
                                    </div>

                                    <!-- SEO Settings -->
                                    <div v-if="currentTab === 'seo'" class="space-y-6">
                                        <label class="block">
                                            <span class="text-gray-700">Meta Title</span>
                                            <input v-model="settings.meta_title" type="text" class="form-control">
                                        </label>

                                        <label class="block">
                                            <span class="text-gray-700">Meta Description</span>
                                            <textarea v-model="settings.meta_description" rows="3"
                                                class="form-control"></textarea>
                                        </label>

                                        <label class="block">
                                            <span class="text-gray-700">Meta Keywords</span>
                                            <textarea v-model="settings.meta_keywords" rows="2"
                                                class="form-control"></textarea>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Sidebar - Preview -->
                        <div class="col-lg-4">
                            <div class="bg-white rounded-lg shadow p-4">
                                <h3 class="font-semibold mb-4">Live Preview</h3>

                                <!-- Preview Device Selector -->
                                <div class="flex space-x-2 mb-4">
                                    <button v-for="device in devices" :key="device" @click="previewDevice = device"
                                        :class="[
                                            'px-3 py-1 text-sm rounded',
                                            previewDevice === device ? 'bg-blue-500 text-white' : 'bg-gray-100 hover:bg-gray-200'
                                        ]">
                                        {{ device }}
                                    </button>
                                </div>

                                <!-- Preview Window -->
                                <div class="border rounded-lg p-4 bg-white mx-auto" :style="{
                                    width: previewDevice === 'Desktop' ? '100%' : previewDevice === 'Tablet' ? '75%' : '50%'
                                }">
                                    <!-- Banner Preview -->
                                    <div v-if="settings.show_banner" class="p-2 mb-4 text-center text-sm rounded"
                                        :style="{
                                            backgroundColor: settings.secondary_color,
                                            color: 'white'
                                        }">
                                        {{ settings.banner_text }}
                                    </div>

                                    <!-- Content Preview -->
                                    <div class="space-y-2" :style="{
                                        fontFamily: settings.font_family,
                                        color: settings.color
                                    }">
                                        <h1 class="text-xl font-bold">{{ settings.meta_title }}</h1>
                                        <p class="text-sm">{{ settings.meta_description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Action Bar -->
                    <div class="fixed bottom-0 left-0 right-0 bg-white border-t p-4">
                        <div class="container mx-auto flex justify-end space-x-4">
                            <button @click="resetSettings" class="px-4 py-2 border rounded-md hover:bg-gray-50">
                                Reset Changes
                            </button>
                            <button @click="saveChange"
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                Save All Changes
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
