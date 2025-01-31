<!-- EventManagement.vue -->
<template>
    <div class="max-w-4xl mx-auto p-4">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Event Management</h1>
            <button @click="openFormDialog()"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                <i class="fas fa-plus text-sm"></i> Add Event
            </button>
        </div>

        <!-- Events List -->
        <div class="space-y-4">
            <div v-for="event in events_ui" :key="event.id" class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="content-event">
                                <div class="entry-info">
                                    <div class="entry-title mb-4">
                                        <span class="text-[#7ecc88] text-xl font-semibold">
                                            {{ event.name }}
                                        </span>
                                    </div>
                                    <div class="entry-meta">
                                        <ul class="space-y-2">
                                            <li class="flex items-center gap-2">
                                                <i class="fas fa-calendar text-gray-600"></i>
                                                <span>{{ formatDate(event.date) }}</span>
                                            </li>
                                            <li class="flex items-center gap-2">
                                                <i class="fas fa-clock text-gray-600"></i>
                                                <span>{{ formatTime(event.start_time) }} - {{ formatTime(event.end_time)
                                                    }}</span>
                                            </li>
                                            <li class="flex items-center gap-2">
                                                <i class="fas fa-map-marker-alt text-gray-600"></i>
                                                <span>{{ event.venue }}</span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <!-- <button @click="editEvent(event)" class="p-2 rounded-lg hover:bg-gray-100">
                                <i class="fas fa-edit text-gray-600">edit</i>
                            </button> -->
                            <button @click="confirmDelete(event)" class="p-2 rounded-lg hover:bg-gray-100">
                                <i class="fas fa-trash text-red-500">delete</i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Modal -->
        <div v-if="showFormModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">{{ selectedEvent ? 'Edit Event' : 'Add New Event' }}</h2>
                    <button @click="closeFormDialog" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <div class="space-y-2">
                        <label for="name" class="block text-sm font-medium text-gray-700">Event Title</label>
                        <input id="name" v-model="form.name" type="text" class="w-full p-2 border rounded-lg" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="date" class="block text-sm font-medium text-gray-700">Date</label>
                            <input id="date" v-model="form.date" type="date" class="w-full p-2 border rounded-lg"
                                required>
                        </div>

                        <div class="space-y-2">
                            <label for="venue" class="block text-sm font-medium text-gray-700">venue</label>
                            <input id="venue" v-model="form.venue" type="text" class="w-full p-2 border rounded-lg"
                                required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label for="start_time" class="block text-sm font-medium text-gray-700">Start Time</label>
                            <input id="start_time" v-model="form.start_time" type="time"
                                class="w-full p-2 border rounded-lg" required>
                        </div>

                        <div class="space-y-2">
                            <label for="end_time" class="block text-sm font-medium text-gray-700">End Time</label>
                            <input id="end_time" v-model="form.end_time" type="time"
                                class="w-full p-2 border rounded-lg" required>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4">
                        <button type="button" @click="closeFormDialog"
                            class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            {{ selectedEvent ? 'Update' : 'Create' }} Event
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h2 class="text-xl font-semibold mb-4">Confirm Delete</h2>
                <p class="text-gray-600 mb-6">Are you sure you want to delete this event? This action cannot be undone.
                </p>
                <div class="flex justify-end gap-4">
                    <button @click="showDeleteModal = false" class="px-4 py-2 border rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button @click="deleteEvent" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { useForm } from '@inertiajs/inertia-vue3';
export default {
    name: 'EventManagement',
    props: {
        events: {
            default: () => []
        }
    },
    data() {
        return {
            events_ui: [
            ],
            showFormModal: false,
            showDeleteModal: false,
            selectedEvent: null,
            form: new useForm({
                name: '',
                date: '',
                start_time: '',
                end_time: '',
                venue: ''
            })
        }
    },
    mounted() {
        this.events_ui = this.events
    },
    methods: {
        formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            })
        },

        formatTime(time24) {
            return new Date(`2000-01-01T${time24}`).toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: 'numeric',
                hour12: true
            }).toLowerCase()
        },

        openFormDialog() {
            this.showFormModal = true
        },

        closeFormDialog() {
            this.showFormModal = false
            this.selectedEvent = null
            this.resetForm()
        },

        resetForm() {
            this.formatDate.name = ''
            this.formatDate.date = ''
            this.formatDate.start_time = ''
            this.formatDate.end_time = ''
            this.formatDate.venue = ''
        },

        editEvent(event) {
            this.selectedEvent = event
            this.form = { ...event }
            this.showFormModal = true
        },

        confirmDelete(event) {
            this.selectedEvent = event
            this.showDeleteModal = true
        },

        async deleteEvent() {
            try {
                await axios.delete(`/website/pages/homepage/events/${this.selectedEvent.id}`)
                this.events_ui = this.events_ui.filter(event => event.id !== this.selectedEvent.id)
                this.showDeleteModal = false
                this.selectedEvent = null

            } catch (error) {
                console.error(error)
            }
        },
        async handleSubmit() {
            if (this.selectedEvent) {

                this.events = this.events.map(event =>
                    event.id === this.selectedEvent.id
                        ? { ...this.form, id: event.id }
                        : event
                )
            } else {
                const formData = new FormData()
                formData.append('date', this.form.date)
                formData.append('end_time', this.form.end_time)
                formData.append('start_time', this.form.start_time)
                formData.append('venue', this.form.venue)
                formData.append('name', this.form.name)

                let response = await axios.post('/website/pages/homepage/events', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
                this.form.reset();
                // Add new quote
                //   const newQuote = {
                //     ...this.formData,
                //     id: this.quotes.length + 1,
                //     createdAt: new Date(),
                //     showActions: false
                //   };
                this.events_ui.unshift(response.data);
            }

            this.closeFormDialog()
        }
    }
}
</script>

<style scoped>
.content-event {
    @apply relative;
}

.entry-meta ul {
    @apply list-none p-0 m-0;
}

.entry-meta li {
    @apply text-gray-600;
}
</style>
