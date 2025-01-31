<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
      <h1 class="text-2xl font-bold">Testimonial Management</h1>
      <button
        @click="toggleForm"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
      >
        {{ showForm ? 'View Testimonials' : 'Add New Testimonial' }}
      </button>
    </div>

    <!-- Testimonials List View -->
    <div v-if="!showForm" class="bg-white rounded-lg shadow">
      <div class="p-4 border-b">
        <h2 class="text-lg font-semibold">Existing Testimonials</h2>
      </div>

      <div class="divide-y">
        <div v-for="testimonial in testimonials_ui" :key="testimonial.id" class="p-6">
          <!-- Preview -->
          <div class="mb-4">
            <div class="flex items-start">
              <span class="text-3xl text-gray-400 mr-4">❝</span>
              <div class="flex-1">
                <p class="text-gray-700 whitespace-pre-line">{{ testimonial.message }}</p>
                <div class="mt-4 flex items-center">
                  <img
                    :src="'/storage/' + testimonial.image_src"
                    :alt="testimonial.name"
                    class="w-10 h-10 rounded-full object-cover mr-3"
                  >
                  <p class="font-semibold">{{ testimonial.name }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-2">
            <!-- <button
              @click="editTestimonial(testimonial)"
              class="px-3 py-1 bg-gray-100 text-gray-600 rounded hover:bg-gray-200"
            >
              Edit
            </button> -->
            <button
              @click="deleteTestimonial(testimonial.id)"
              class="px-3 py-1 bg-red-100 text-red-600 rounded hover:bg-red-200"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Form -->
    <div v-else class="bg-white rounded-lg shadow">
      <div class="p-4 border-b">
        <h2 class="text-lg font-semibold">
          {{ editingTestimonial ? 'Edit Testimonial' : 'Add New Testimonial' }}
        </h2>
      </div>

      <form @submit.prevent="saveTestimonial" class="p-6">
        <div class="space-y-6">
          <!-- Quote Input -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Testimonial Quote
            </label>
            <div class="relative">
              <span class="absolute top-2 left-2 text-2xl text-gray-400">❝</span>
              <textarea
                v-model="form.message"
                rows="4"
                class="w-full p-4 pl-8 border rounded"
                placeholder="Enter testimonial quote..."
                required
              ></textarea>
            </div>
            <p class="mt-1 text-sm text-gray-500">
              Use \n or press Enter for line breaks
            </p>
          </div>

          <!-- Author Section -->
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Author Name
              </label>
              <input
                v-model="form.name"
                type="text"
                class="w-full p-2 border rounded"
                placeholder="Enter author name"
                required
              >
            </div>

            <!-- Author Photo Upload -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Author Photo
              </label>
              <div class="flex items-center space-x-4">
                <div class="relative w-20 h-20 border rounded-lg overflow-hidden">
                  <img
                    :src="form.image_src || '/api/placeholder/80/80'"
                    alt="Author preview"
                    class="w-full h-full object-cover"
                  >
                  <div
                    v-if="form.image_src"
                    @click="form.image_src = ''"
                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center cursor-pointer"
                  >
                    ×
                  </div>
                </div>
                <div class="flex-1">
                  <input style="visibility: none;"
                    type="file"
                    ref="photoInputx"
                    accept="image/*"
                    @change="handlePhotoUpload"
                  >
                  <div class="space-y-2">
                    <!-- <button
                      type="button"
                      @click="$refs.photoInputx.click()"
                      class="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                    >
                      Choose Photo
                    </button> -->
                    <input
                      v-model="form.image_src"
                      type="text"
                      placeholder="Or enter photo URL"
                      class="w-full p-2 border rounded"
                    >
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Preview -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Preview</h3>
            <div class="flex items-start">
              <span class="text-3xl text-gray-400 mr-4">❝</span>
              <div>
                <p class="text-gray-700 whitespace-pre-line">{{ form.message }}</p>
                <div class="mt-4 flex items-center">
                  <img
                    :src="form.image_src || '/api/placeholder/40/40'"
                    :alt="form.name"
                    class="w-10 h-10 rounded-full object-cover mr-3"
                  >
                  <p class="font-semibold">{{ form.name }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="flex justify-end space-x-3">
            <!-- <button
              type="button"
              @click="cancelEdit"
              class="px-4 py-2 bg-gray-100 text-gray-600 rounded hover:bg-gray-200"
            >
              Cancel
            </button> -->
            <button
              type="submit"
              class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
            >
              {{ editingTestimonial ? 'Update Testimonial' : 'Save Testimonial' }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import {useForm} from '@inertiajs/inertia-vue3'
import axios from 'axios';
export default {
    props:{
        testimonials: {
            default: ()=>[]
        }
    },
  data() {
    return {
      showForm: false,
      editingTestimonial: null,
      testimonials_ui: [

      ],
      form: new useForm({
        message: '',
        name: '',
        image_src: '',
        actualPhoto: null
      })
    }
  },
  mounted(){
    this.testimonials_ui = this.testimonials
  },
  methods: {
    toggleForm() {
      this.showForm = !this.showForm
      if (!this.showForm) {
        this.cancelEdit()
      }
    },
    editTestimonial(testimonial) {
      this.editingTestimonial = testimonial
      this.form = { ...testimonial }
      this.showForm = true
    },
    cancelEdit() {
      this.editingTestimonial = null
      this.form.message = ''
      this.form.name = ''
      this.form.image_src = ''
      this.form.actualPhoto = null
    },
    async handlePhotoUpload(event) {
      const file = event.target.files[0]

      if (!file) return

      try {
        const reader = new FileReader()
        reader.onload = (e) => {
          this.form.image_src = e.target.result
        }
        reader.readAsDataURL(file)
        this.form.actualPhoto = file

      /*
        const formData = new FormData()
        formData.append('photo', file)
        const response = await fetch('/api/upload', {
          method: 'POST',
          body: formData
        })
        const { url } = await response.json()
        this.form.image_src = url
        */
      } catch (error) {
        console.error('Error uploading photo:', error)
      }
    },
    async saveTestimonial() {
      try {
        if (this.editingTestimonial) {
          const index = this.testimonials.findIndex(t => t.id === this.editingTestimonial.id)
          this.testimonials[index] = {
            ...this.editingTestimonial,
            ...this.form
          }
        } else {
            const formData = new FormData()
          formData.append('message', this.form.message)
          formData.append('name', this.form.name)
          if (this.form.actualPhoto) {
            formData.append('image', this.form.actualPhoto)
          }
          formData.append('authorPhoto', this.form.image_src)

           let response = await axios.post('/website/pages/homepage/testimonials', formData, {
              headers: { 'Content-Type': 'multipart/form-data' }
            })
            this.$toast.success('Testimonial added', 'Success');
            this.testimonials_ui.unshift(response.data)
        //   this.testimonials.push({
        //     id: Date.now(),
        //     ...this.form
        //   })
        }

        this.cancelEdit()
        this.showForm = false
      } catch (error) {
        this.$toast.error('Error saving testimonial:', 'Error');
        console.error('Error saving testimonial:', error)
      }
    },
    async deleteTestimonial(id) {
      if (confirm('Are you sure you want to delete this testimonial?')) {
        try {
            await axios.delete(`/website/pages/homepage/testimonials/${id}`)
            this.$toast.success('Deleted', 'Success');
          this.testimonials_ui = this.testimonials_ui.filter(t => t.id !== id)


        } catch (error) {
          console.error('Error deleting testimonial:', error)
          this.$toast.error('Error deleting testimonial:', 'Error');
        }
      }
    }
  }
}
</script>
