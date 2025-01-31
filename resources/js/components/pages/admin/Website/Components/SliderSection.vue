<template>
    <div class="max-w-7xl mx-auto p-4">
      <div class="row">
        <!-- Carousel Section -->
        <div class="col-lg-8">
          <div class="rounded-lg p-6">
            <div class="flex justify-between items-center mb-6">
              <h5 class="text-xl font-semibold">Available Slides</h5>
              <span class="text-gray-500 text-sm">{{ slides.length }} slides</span>
            </div>

            <Carousel v-bind="carouselConfig" class="relative">
              <Slide v-for="(slide, index) in slides" :key="slide.id">
                <div class="carousel_item relative rounded-lg overflow-hidden bg-gray-100">
                  <!-- Delete Button -->
                  <button
                    @click="deleteSlide(slide)"
                    class="absolute top-4 right-4 z-10 bg-red-500 hover:bg-red-600 text-white p-1 rounded-full shadow-lg"
                  >
                  <i class='bx bx-trash'></i>
                  </button>

                  <!-- Image -->
                  <img
                    :src="'/storage/' + slide.image_src"
                    :alt="slide.caption_title"
                    class="w-full h-[400px] object-cover"
                  />

                  <!-- Caption -->
                  <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-6">
                    <h3 class="text-white text-2xl font-bold mb-2">{{ slide.caption_title }}</h3>
                    <p class="text-white/90">{{ slide.caption }}</p>
                  </div>

                  <!-- Slide Number -->
                  <div class="absolute top-4 left-4 bg-white/90 px-3 py-1 rounded-full text-sm font-medium">
                    {{ index + 1 }}/{{ slides.length }}
                  </div>
                </div>
              </Slide>

              <template #addons>
                <Navigation />
                <Pagination />
              </template>
            </Carousel>
          </div>
        </div>

        <!-- Form Section -->
        <div class="col-lg-4">
          <div class="bg-white rounded-lg shadow-lg p-6">
            <h5 class="text-xl font-semibold mb-6">{{ isEditing ? 'Edit Slide' : 'Add New Slide' }}</h5>

            <form @submit.prevent="handleSubmit" class="space-y-6">
              <!-- Title Input -->
              <div class="space-y-2">
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input
                  id="title"
                  v-model="form.title"
                  type="text"
                  class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Enter slide title"
                  required
                />
                <span v-if="form.errors.title" class="text-red-500 text-sm">{{ form.errors.title }}</span>
              </div>

              <!-- Description Input -->
              <div class="space-y-2">
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="4"
                  class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Enter slide description"
                  required
                ></textarea>
                <span v-if="form.errors.description" class="text-red-500 text-sm">{{ form.errors.description }}</span>
              </div>

              <!-- Image Upload -->
              <div class="space-y-2">
                <label for="image" class="block text-sm font-medium text-gray-700">Image</label>
                <div class="relative">
                  <input
                    type="file"
                    id="image"
                    @change="handleFileChange"
                    class="hidden"
                    accept="image/*"
                    :required="!isEditing"
                  />
                  <label
                    for="image"
                    class="flex items-center justify-center w-full p-4 border-2 border-dashed rounded-lg cursor-pointer hover:border-blue-500 transition-colors"
                  >
                    <div class="space-y-2 text-center">
                      <i class="fas fa-cloud-upload-alt text-3xl text-gray-400"></i>
                      <div class="text-sm text-gray-600">
                        <span class="text-blue-500">Click to upload</span> or drag and drop
                      </div>
                      <div class="text-xs text-gray-500">PNG, JPG up to 10MB</div>
                    </div>
                  </label>
                  <div v-if="imagePreview" class="mt-2">
                    <img :src="imagePreview" class="h-32 w-full object-cover rounded-lg" />
                  </div>
                </div>
                <span v-if="form.errors.image" class="text-red-500 text-sm">{{ form.errors.image }}</span>
              </div>

              <!-- Submit Buttons -->
              <div class="flex gap-4">
                <button
                  type="submit"
                  class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition-colors"
                  :disabled="processing"
                >
                  <span v-if="processing">
                    <i class="fas fa-spinner fa-spin mr-2"></i>Processing...
                  </span>
                  <span v-else>
                    {{ isEditing ? 'Update Slide' : 'Add Slide' }}
                  </span>
                </button>
                <button
                  v-if="isEditing"
                  type="button"
                  @click="cancelEdit"
                  class="flex-1 border border-gray-300 hover:bg-gray-50 py-2 px-4 rounded-lg transition-colors"
                >
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
          <h2 class="text-xl font-semibold mb-4">Confirm Delete</h2>
          <p class="text-gray-600 mb-6">Are you sure you want to delete this slide? This action cannot be undone.</p>
          <div class="flex justify-end gap-4">
            <button
              @click="showDeleteModal = false"
              class="px-4 py-2 border rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="confirmDelete"
              class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script>
  import { Carousel, Navigation, Pagination, Slide } from 'vue3-carousel'
  import 'vue3-carousel/dist/carousel.css'
  import { useForm } from '@inertiajs/inertia-vue3'
  export default {
    name: 'SlidesManager',
    props:{
        home_slides:{
            default:() =>[]
        }
    },
    components: {
      Carousel,
      Slide,
      Navigation,
      Pagination
    },

    data() {
      return {
        carouselConfig: {
          itemsToShow: 1,
          snapAlign: 'center',
          wrapAround: true
        },
        slides:[],
        form: useForm({
          title: '',
          description: '',
          image: null,
          errors: {}
        }),
        imagePreview: null,
        processing: false,
        isEditing: false,
        editingSlideId: null,
        showDeleteModal: false,
        slideToDelete: null
      }
    },
    mounted() {
    this.slides = this.home_slides
  },
    methods: {
      handleFileChange(e) {
        const file = e.target.files[0]
        if (file) {
          this.form.image = file
          this.imagePreview = URL.createObjectURL(file)
        }
      },

      async handleSubmit() {
        this.processing = true
        this.form.errors = {}

        try {
          const formData = new FormData()
          formData.append('title', this.form.title)
          formData.append('description', this.form.description)
          if (this.form.image) {
            formData.append('image', this.form.image)
          }

          let response
          if (this.isEditing) {
            response = await axios.post(`/api/slides/${this.editingSlideId}`, formData, {
              headers: { 'Content-Type': 'multipart/form-data' }
            })
            // Update the slide in the local array
            const index = this.slides.findIndex(slide => slide.id === this.editingSlideId)
            if (index !== -1) {
              this.slides[index] = response.data.slide
            }
          } else {

            response = await axios.post('/website/pages/homepage/slide', formData, {
              headers: { 'Content-Type': 'multipart/form-data' }
            })
            this.$toast.success('Home slider Saved', 'Success');
            this.slides.push(response.data)
          }
          this.resetForm()
        } catch (error) {
          if (error.response?.status === 422) {
            this.form.errors = error.response.data.errors
            this.$toast.error('Error saving slider:', 'Error');
          }
        } finally {
          this.processing = false
        }
      },

      editSlide(slide) {
        this.isEditing = true
        this.editingSlideId = slide.id
        this.form.title = slide.caption_title
        this.form.description = slide.caption
        this.imagePreview = `/storage/${slide.image_src}`
      },

      cancelEdit() {
        this.resetForm()
      },

      resetForm() {
        this.form.title = ''
        this.form.description = ''
        this.form.image = null
        this.imagePreview = null
        this.isEditing = false
        this.editingSlideId = null
      },

      deleteSlide(slide) {
        this.slideToDelete = slide
        this.showDeleteModal = true
      },

      async confirmDelete() {
        try {
          await axios.delete(`/website/pages/homepage/slide/${this.slideToDelete.id}`)
          this.slides = this.slides.filter(s => s.id !== this.slideToDelete.id)
          this.$toast.success('Deleted:', 'Success');
          this.showDeleteModal = false
          this.slideToDelete = null
        } catch (error) {
            this.$toast.error('Error deleting a slider:', 'Error');

          console.error(error)
        }
      }
    }
  }
  </script>

  <style  scoped>
  .carousel_item {
    @apply relative h-[400px];
  }

  .row {
    @apply flex flex-wrap -mx-4;
  }

  .col-lg-8 {
    @apply w-full lg:w-2/3 px-4;
  }

  .col-lg-4 {
    @apply w-full lg:w-1/3 px-4;
  }

  /* Override carousel navigation styles */
  :deep(.carousel__prev),
  :deep(.carousel__next) {
    @apply bg-white/90 text-gray-800 rounded-full w-10 h-10 flex items-center justify-center;
  }

  :deep(.carousel__pagination) {
    @apply mt-4;
  }

  :deep(.carousel__pagination-button) {
    @apply w-3 h-3 rounded-full bg-gray-300;
  }

  :deep(.carousel__pagination-button--active) {
    @apply bg-blue-500;
  }
  </style>
