
<template>
    <div class="max-w-6xl mx-auto p-4">
      <!-- Management Interface -->
      <div class="mb-8">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold">Quick Links Management</h1>
          <button
            @click="openFormDialog()"
            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2"
          >
            <i class="fas fa-plus text-sm"></i> Add Quick Links Section
          </button>
        </div>

        <!-- Quick Links List -->
        <div class="space-y-4">
          <div v-for="section in quick_links_ui" :key="section.id" class="bg-white rounded-lg shadow">
            <div class="p-6">
              <div class="flex justify-between items-start mb-4">
                <div>
                  <span class="text-sm text-gray-500">{{ formatDate(section.date) }}</span>
                  <h3 class="text-xl font-semibold">{{ section.title }}</h3>
                </div>
                <div class="flex gap-2">
                  <!-- <button
                    @click="editSection(section)"
                    class="p-2 rounded-lg hover:bg-gray-100"
                  >
                    <i class="fas fa-edit text-gray-600">edit</i>
                  </button> -->
                  <button
                    @click="confirmDelete(section)"
                    class="p-2 rounded-lg hover:bg-gray-100"
                  >
                    <i class="fas fa-trash text-red-500">del</i>
                  </button>
                </div>
              </div>

              <!-- Preview -->
              <div class="row">
                <div class="col-lg-7">
                  <div class="wrap-link-left">
                    <div class="caption lt-sp275">{{ section.welcome_text }}</div>
                    <div class="heading-lf lt-sp03">{{ section.title }}</div>
                    <p>{{ section.description }}</p>
                    <div class="btn-apply-link">
                      <ul class="flex gap-4">
                        <li v-if="section.button_label">
                          <a :href="section.button_url" class="btn btn-apply bg-[#ff5f60] text-white px-6 py-2 rounded">
                            {{ section.button_label }}
                          </a>
                        </li>
                        <li>
                          <a href="#" class="btn btn-request border border-gray-300 px-6 py-2 rounded">
                            Request Service
                          </a>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
                <div class="col-lg-5">
                  <div class="wrap-link-right">
                    <div class="heading-rg">
                      <span class="font-semibold">Quick Link</span>
                    </div>
                    <ul class="info-quick-link space-y-4 mt-4">
                      <li v-if="section.link1_text" class="flex items-center gap-3">
                        <img src="" alt="icon" class="w-6 h-6">
                        <a :href="section.link1_href">{{ section.link1_text }}</a>
                      </li>
                      <li v-if="section.link2_text" class="flex items-center gap-3">
                        <img src="" alt="icon" class="w-6 h-6">
                        <a :href="section.link2_href">{{ section.link2_text }}</a>
                      </li>
                      <li v-if="section.link3_text" class="flex items-center gap-3">
                        <img src="" alt="icon" class="w-6 h-6">
                        <a :href="section.link3_href">{{ section.link3_text }}</a>
                      </li>
                      <li v-if="section.link4_text" class="flex items-center gap-3">
                        <img src="" alt="icon" class="w-6 h-6">
                        <a :href="section.link4_href">{{ section.link4_text }}</a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Form Modal -->
      <div style="padding-top: 20rem;"  v-if="showFormModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center overflow-y-auto">
        <div class="bg-white rounded-lg p-6 max-w-4xl w-full mx-4 my-8">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">{{ selectedSection ? 'Edit Section' : 'Add New Section' }}</h2>
            <button @click="closeFormDialog" class="text-gray-500 hover:text-gray-700">
              <i class="fas fa-times"></i>
            </button>
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Main Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Welcome Text</label>
                <input
                  v-model="form.welcome_text"
                  type="text"
                  class="w-full p-2 border rounded-lg"
                  maxlength="100"
                >
              </div>
              <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <input
                  v-model="form.title"
                  type="text"
                  class="w-full p-2 border rounded-lg"
                  maxlength="100"
                  required
                >
              </div>
            </div>

            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Description</label>
              <textarea
                v-model="form.description"
                class="w-full p-2 border rounded-lg"
                rows="3"
              ></textarea>
            </div>

            <!-- Button Settings -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Button Label</label>
                <input
                  v-model="form.button_label"
                  type="text"
                  class="w-full p-2 border rounded-lg"
                  maxlength="100"
                >
              </div>
              <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Button URL</label>
                <input
                  v-model="form.button_url"
                  type="text"
                  class="w-full p-2 border rounded-lg"
                  maxlength="100"
                >
              </div>
            </div>

            <!-- Quick Links -->
            <div class="space-y-4">
              <h3 class="font-semibold">Quick Links</h3>

              <div v-for="n in 4" :key="n" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-2">
                  <label class="block text-sm font-medium text-gray-700">Link {{ n }} Text</label>
                  <input
                    v-model="form[`link${n}_text`]"
                    type="text"
                    class="w-full p-2 border rounded-lg"
                    maxlength="20"
                  >
                </div>
                <div class="space-y-2">
                  <label class="block text-sm font-medium text-gray-700">Link {{ n }} URL</label>
                  <input
                    v-model="form[`link${n}_href`]"
                    type="text"
                    class="w-full p-2 border rounded-lg"
                    maxlength="255"
                  >
                </div>
              </div>
            </div>

            <div class="flex justify-end gap-4">
              <button
                type="button"
                @click="closeFormDialog"
                class="px-4 py-2 border rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
              >
                {{ selectedSection ? 'Update' : 'Create' }} Section
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Delete Confirmation Modal -->
      <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
          <h2 class="text-xl font-semibold mb-4">Confirm Delete</h2>
          <p class="text-gray-600 mb-6">Are you sure you want to delete this section? This action cannot be undone.</p>
          <div class="flex justify-end gap-4">
            <button
              @click="showDeleteModal = false"
              class="px-4 py-2 border rounded-lg hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="deleteSection"
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
  import {useForm} from '@inertiajs/inertia-vue3'
  export default {
    name: 'quick_linksManager',
    props:{
        quick_links:{
            default: ()=>[]
        }
    },
    data() {
      return {
        // quick_links: [
        //   {
        //     id: 1,
        //     welcome_text: "It's fast, free and very easy!",
        //     title: "Ready to get started?",
        //     date: "2024-01-02",
        //     description: "Education is the process of acquiring the body of knowledge and skills that people are expected have in your society. A education develops a critical thought process in addition to learning.",
        //     button_label: "Apply now",
        //     button_url: "#",
        //     link1_text: "Tution And Fees",
        //     link1_href: "#",
        //     link2_text: "University Facilities",
        //     link2_href: "#",
        //     link3_text: "Review & Rating",
        //     link3_href: "#",
        //     link4_text: "Community Q&A",
        //     link4_href: "#"
        //   }
        // ],
        quick_links_ui:[],
        showFormModal: false,
        showDeleteModal: false,
        selectedSection: null,
        form: new useForm({
          welcome_text: '',
          title: '',
          date: '',
          description: '',
          button_label: '',
          button_url: '',
          link1_text: '',
          link1_href: '',
          link2_text: '',
          link2_href: '',
          link3_text: '',
          link3_href: '',
          link4_text: '',
          link4_href: ''
        })
      }
    },
    mounted(){
        this.quick_links_ui = this.quick_links
    },
    methods: {
      formatDate(dateStr) {
        return new Date(dateStr).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        })
      },

      openFormDialog() {
        this.showFormModal = true
      },

      closeFormDialog() {
        this.showFormModal = false
        this.selectedSection = null
        this.resetForm()
      },

      resetForm() {
        this.form.title
        this.form.welcome_text
        this.form.description
        this.form.button_label
        this.form.button_url
        this.form.link1_text
        this.form.link1_href
        this.form.link2_text
        this.form.link2_href
        this.form.link3_text
        this.form.link3_href
        this.form.link4_text
        this.form.link4_href
      },

      editSection(section) {
        this.selectedSection = section
        this.form = { ...section }
        this.showFormModal = true
      },

      confirmDelete(section) {
        this.selectedSection = section
        this.showDeleteModal = true
      },

      async deleteSection() {
        await axios.delete(`/website/pages/homepage/quick-links/${this.selectedSection.id}`)
        this.quick_links_ui = this.quick_links_ui.filter(section => section.id !== this.selectedSection.id)
        this.showDeleteModal = false
        this.selectedSection = null
      },

      async handleSubmit() {
        if (this.selectedSection) {
          this.quick_links = this.quick_links.map(section =>
            section.id === this.selectedSection.id
              ? { ...this.form, id: section.id }
              : section
          )
        } else {
            const formData = new FormData()
                formData.append('welcome_text', this.form.welcome_text)
                formData.append('title', this.form.title)
                formData.append('date', this.form.date)
                formData.append('description', this.form.description)
                formData.append('button_label', this.form.button_label)
                formData.append('button_url', this.form.button_url)
                formData.append('link1_text', this.form.link1_text)
                formData.append('link1_href', this.form.link1_href)
                formData.append('link2_text', this.form.link2_text)
                formData.append('link2_href', this.form.link2_href)
                formData.append('link3_text', this.form.link3_text)
                formData.append('link3_href', this.form.link3_href)
                formData.append('link4_text', this.form.link4_text)
                formData.append('link4_href', this.form.link4_href)

                let response = await axios.post('/website/pages/homepage/quick-links', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                })
                this.quick_links_ui.unshift(response.data);
        //   this.quick_links.push({
        //     ...this.form,
        //     id: Date.now()
        //   })
        }

        this.closeFormDialog()
      }
    }
  }
  </script>

  <style scoped>
  .row {
    @apply flex flex-wrap -mx-4;
  }

  .col-lg-7 {
    @apply w-full lg:w-7/12 px-4;
  }

  .col-lg-5 {
    @apply w-full lg:w-5/12 px-4;
  }

  .wrap-link-left {
    @apply space-y-4;
  }

  .caption {
    @apply text-gray-600 text-lg;
  }

  .heading-lf {
    @apply text-2xl font-bold;
  }

  .btn-apply-link ul {
    @apply list-none p-0 m-0 flex gap-4;
  }

  .info-quick-link {
    @apply list-none p-0 m-0 space-y-4;
  }
  </style>
