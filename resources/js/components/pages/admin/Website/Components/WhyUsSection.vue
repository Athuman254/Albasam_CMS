<template>
    <div class="min-h-screen bg-gray-50 p-6">
        <!-- Top Bar -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-2xl font-bold">Why Choose Us</h1>
            <button @click="showAddForm = !showAddForm"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                {{ showAddForm ? 'View reasons' : 'Add New reason' }}
            </button>
        </div>

        <!-- Existing reasons List -->
        <div v-if="!showAddForm" class="space-y-6">
            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-semibold">Existing Reason</h2>
                </div>
                <div class="p-4">
                    <!-- reasons Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="reason in reasons_ui" :key="reason.id"
                            class="bg-white border rounded-lg overflow-hidden">
                            <div class="p-4">
                                <!-- Preview -->
                                <div class="flex items-start space-x-4">
                                    <div class="w-16 h-16 flex-shrink-0">
                                        <img :src="'/storage/'+reason.image_src" alt="reason icon"
                                            class="w-full h-full object-cover rounded">
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-lg">{{ reason.title }}</h3>
                                        <p class="text-gray-600 text-sm mt-1">{{ reason.description }}</p>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="mt-4 flex justify-end space-x-2">
                                    <!-- <button @click="editreason(reason)"
                                        class="px-3 py-1 bg-gray-100 text-gray-600 rounded hover:bg-gray-200">
                                        Edit
                                    </button> -->
                                    <button @click="deletereason(reason.id)"
                                        class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200">
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Form -->
        <div v-else class="bg-white rounded-lg shadow-sm">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold">{{ editingreason ? 'Edit reason' : 'Add New reason' }}</h2>
            </div>
            <form @submit.prevent="savereason" class="p-6 space-y-6">
                <!-- Form Fields -->
                <div class="space-y-4">
                    <!-- Icon Upload -->
                    <div class="space-y-2">
              <label class="block text-sm font-medium">Quote Icon</label>
              <div class="flex items-center space-x-4">
                <div class="relative w-24 h-24 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
                  <img
                    v-if="previewImage"
                    :src="previewImage"
                    class="w-full h-full object-cover rounded-lg"
                    alt="Quote icon"
                  />
                  <span v-else class="material-icons text-gray-400"> image <br/> 65 x 63</span>
                  <input
                    type="file"
                    @change="handleImageUpload"
                    accept="image/*"
                    class="absolute inset-0 opacity-0 cursor-pointer"
                  />
                </div>
                <div v-if="previewImage" class="flex-1">
                  <button
                    type="button"
                    @click="clearImage"
                    class="text-red-600 hover:text-red-700 text-sm flex items-center"
                  >
                  <i class='bx bx-trash' ></i> del
                  </button>
                </div>
              </div>
            </div>

                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">reason Title</label>
                        <input v-model="form.title" type="text" required class="w-full p-2 border rounded"
                            placeholder="Enter reason title">
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">reason Description</label>
                        <textarea v-model="form.description" required rows="3" class="w-full p-2 border rounded"
                            placeholder="Enter reason description"></textarea>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="cancelEdit"
                        class="px-4 py-2 bg-gray-100 text-gray-600 rounded hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                        {{ editingreason ? 'Update reason' : 'Add reason' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import { useForm } from "@inertiajs/inertia-vue3"
export default {
    props: {
        reasons: {
            type: Array
        }
    },
    data() {

        return {
            showAddForm: false,
            editingreason: null,
            previewImage: null,
            reasons_ui: [],
            form: new useForm({
                icon: '',
                title: '',
                description: ''
            })
        }
    },
    mounted() {
        this.reasons_ui = this.reasons
    },
    methods: {
        handleImageUpload(event) {
        const file = event.target.files[0];
        this.img = file
        if (file) {
          const reader = new FileReader();
          reader.onload = (e) => {
            this.previewImage = e.target.result;
            this.form.icon = event.target.files[0];
          };
          reader.readAsDataURL(file);
        }
      },
      clearImage() {
        this.previewImage = null;
        this.form.icon = null;
      },
        editreason(reason) {
            this.editingreason = reason
            this.form = { ...reason }
            this.showAddForm = true
        },
        cancelEdit() {
            this.editingreason = null
            this.form.icon = ''
            this.form.title = ''
            this.form.description = ''
            // this.form = {
            //     icon: '',
            //     title: '',
            //     description: ''
            // }
            this.showAddForm = false
        },
        async savereason() {
            try {
                if (this.editingreason) {
                    // Update existing reason
                    const index = this.reasons.findIndex(p => p.id === this.editingreason.id)
                    this.reasons[index] = { ...this.editingreason, ...this.form }
                } else {
                    const formData = new FormData()
                    formData.append('title', this.form.title)
                    formData.append('description', this.form.description)
                    if (this.form.icon) {
                        formData.append('icon', this.form.icon)
                    }
                    // this.form.post("/website/pages/homepage/whyus", {
                    //     onError: (errors) => {
                    //         console.error("Validation Errors:", errors);
                    //     },
                    //     onSuccess: () => {
                    //         this.form.reset();
                    //         alert('ds')
                    //     },
                    // });
                    let response = await axios.post('/website/pages/homepage/whyus', formData, {
                        headers: { 'Content-Type': 'multipart/form-data' }
                    })
                    this.reasons_ui.unshift(response.data)
                    // Add new reason
                    //   this.reasons.push({
                    //     id: Date.now(),
                    //     ...this.form
                    //   })
                }

                // Reset form and state
                this.cancelEdit()
            } catch (error) {
                console.error('Error saving reason:', error)
                // Add error handling as needed
            }
        },
        async deletereason(id) {
            if (confirm('Are you sure you want to delete this reason?')) {
                try {
                    await axios.delete(`/website/pages/homepage/whyus/${id}`)
                    this.reasons_ui = this.reasons_ui.filter(p => p.id !== id)
                    // Add API call to delete from database
                } catch (error) {
                    console.error('Error deleting reason:', error)
                    // Add error handling as needed
                }
            }
        }
    }
}
</script>
