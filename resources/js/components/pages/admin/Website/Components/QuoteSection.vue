<template>
    <div class="max-w-4xl mx-auto p-6">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <h3 class="text-3xl font-bold">Quotes Collection</h3>
        <button
          @click="showAddForm = true"
          class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
        >
        <i class='bx bx-plus-circle' ></i>
          Add Quote
        </button>
      </div>

      <!-- Add/Edit Modal -->
      <div v-if="showAddForm || editingQuote" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">{{ editingQuote ? 'Edit Quote' : 'Add New Quote' }}</h2>
            <button @click="closeForm" class="text-gray-500 hover:text-gray-700">
                <i class='bx bx-x-circle' ></i>
            </button>
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-4">
            <!-- Image Upload -->
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
                  <span v-else class="material-icons text-gray-400">image</span>
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

            <!-- Title Input -->
            <div>
              <label class="block text-sm font-medium mb-1">Title</label>
              <input
                v-model="form.title"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter quote title"
              />
            </div>

            <!-- Description Input -->
            <div>
              <label class="block text-sm font-medium mb-1">Description</label>
              <textarea
                v-model="form.description"
                required
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Enter quote description"
              ></textarea>
            </div>

            <!-- Category Selection -->
            <!-- <div>
              <label class="block text-sm font-medium mb-1">Category</label>
              <select
                v-model="formData.category"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="inspiration">Inspiration</option>
                <option value="motivation">Motivation</option>
                <option value="success">Success</option>
                <option value="wisdom">Wisdom</option>
              </select>
            </div> -->

            <!-- Submit Button -->
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeForm"
                class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              >
                {{ editingQuote ? 'Update Quote' : 'Add Quote' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Quotes Grid -->
      <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="quote in quotes"
          :key="quote.id"
          class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow"
        >
          <!-- Quote Card -->
          <div class="p-4">
            <!-- Quote Header -->
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center space-x-3">
                <div class="w-12 h-12 rounded-full overflow-hidden bg-gray-100">
                  <img
                    v-if="quote.image_src"
                    :src="'/storage/'+quote.image_src"
                    class="w-full h-full object-cover"
                    alt="Quote icon"
                  />
                  <span v-else class="material-icons text-gray-400 w-full h-full flex items-center justify-center">format_quote</span>
                </div>
                <div>
                  <h3 class="font-bold text-lg">{{ quote.title }}</h3>
                  <span class="text-sm text-gray-500 capitalize">{{ quote.category }}</span>
                </div>
              </div>
              <!-- Actions Dropdown -->
              <div class="relative">
                <button
                  @click="quote.showActions = !quote.showActions"
                  class="p-1 hover:bg-gray-100 rounded-full"
                >
                <i class='bx bx-dots-horizontal-rounded' ></i>
                </button>
                <div
                  v-if="quote.showActions"
                  class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-1 z-10"
                >
                  <!-- <button
                    @click="editQuote(quote)"
                    class="w-full px-4 py-2 text-left hover:bg-gray-100 flex items-center"
                  >
                    <span class="material-icons text-gray-600 mr-2">edit</span>
                    Edit
                  </button> -->
                  <button
                    @click="confirmDelete(quote.id)"
                    class="px-4 py-2 text-left hover:bg-gray-100 flex items-center text-red-600"
                  >
                  <i class='bx bx-trash' ></i> del
                  </button>
                </div>
              </div>
            </div>

            <!-- Quote Content -->
            <p class="text-gray-600 mb-4">{{ quote.description }}</p>

            <!-- Quote Footer -->
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-500">Added {{ formatDate(quote.created_at) }}</span>
              <!-- <button class="text-blue-600 hover:text-blue-700 flex items-center text-sm">
                Read More
                <span class="material-icons text-sm">chevron_right</span>
              </button> -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script>
  export default {
    name: 'QuotesApp',
    props:{
        in_quotes:{
            type: Array,
            default: ()=> []
        }
    },
    data() {
      return {
        quotes: [
          {
            id: 1,
            title: 'Dream Big',
            description: 'All our dreams can come true if we have the courage to pursue them.',
            category: 'inspiration',
            image_src: null,
            created_at: new Date('2024-01-01'),
            showActions: false
          },
        ],
        showAddForm: false,
        editingQuote: null,
        form: {
          title: '',
          description: '',
          category: 'inspiration',
          image: null,
          img: null,
        },
        previewImage: null
      }
    },
    mounted() {
    this.quotes = this.in_quotes
  },
    methods: {
      handleImageUpload(event) {
        const file = event.target.files[0];
        this.img = file
        if (file) {
          const reader = new FileReader();
          reader.onload = (e) => {
            this.previewImage = e.target.result;
            this.form.image = event.target.files[0];
          };
          reader.readAsDataURL(file);
        }
      },
      clearImage() {
        this.previewImage = null;
        this.form.image = null;
      },
      async handleSubmit() {
        this.form.errors = {}
        try{
            const formData = new FormData()
          formData.append('title', this.form.title)
          formData.append('description', this.form.description)
          if (this.form.image) {
            formData.append('image', this.form.image)
          }
           let response = await axios.post('/website/pages/homepage/quotes', formData, {
              headers: { 'Content-Type': 'multipart/form-data' }
            })
            this.$toast.success('added', 'Success');
              // Add new quote
        //   const newQuote = {
        //     ...this.formData,
        //     id: this.quotes.length + 1,
        //     createdAt: new Date(),
        //     showActions: false
        //   };
          this.quotes.unshift(response.data);
          this.closeForm();
        }catch(error){
            this.$toast.error('Failed', 'Error');
            if (error.response?.status === 422) {
            this.form.errors = error.response.data.errors
            this.$toast.error('Fail all required fills', 'Error');
          }
        }

      },
      editQuote(quote) {
        this.editingQuote = quote;
        this.formData = { ...quote };
        this.previewImage = quote.image;
        quote.showActions = false;
      },
      deleteQuote(id) {
        if (confirm('Are you sure you want to delete this quote?')) {
          this.quotes = this.quotes.filter(quote => quote.id !== id);
        }
      },
      async confirmDelete(id) {
        try {
            if (confirm('Are you sure you want to delete this quote?')) {
                await axios.delete(`/website/pages/homepage/quotes/${id}`)
                this.$toast.success('Delete', 'Success');
          this.quotes = this.quotes.filter(quote => quote.id !== id);
        }

        } catch (error) {
            this.$toast.error('Failed to Detele', 'Error');
          console.error(error)
        }
      },
      closeForm() {
        this.showAddForm = false;
        this.editingQuote = null;
        this.form = {
          title: '',
          description: '',
          category: 'inspiration',
          image: null
        };
        this.previewImage = null;
      },
      formatDate(date) {
        return new Date(date).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
      }
    }
  }
  </script>

  <style scoped>

  </style>
