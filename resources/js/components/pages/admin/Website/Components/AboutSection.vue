<template>
    <div class="container py-5">
      <div class="row g-5">
        <!-- Form Section -->
        <div class="col-lg-6">
          <form @submit.prevent="saveAboutContent" class="needs-validation">
            <!-- Video Section -->
            <div class="card mb-4 shadow-sm">
              <div class="card-header bg-light">
                <h3 class="card-title mb-0">Video Section</h3>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <label for="videoId" class="form-label">YouTube Video ID</label>
                  <input
                    id="videoId"
                    v-model="aboutForm.videoId"
                    @input="updateVideoUrl"
                    class="form-control"
                    placeholder="e.g. 2Ge1GGitzLw"
                  />
                </div>
                <div class="mb-3">
                  <input
                    v-model="aboutForm.videoUrl"
                    class="form-control"
                    placeholder="Video URL"
                  />
                </div>
                <div class="mb-3">
                  <input
                    v-model="aboutForm.thumbnailImage"
                    class="form-control"
                    placeholder="Thumbnail Image URL"
                  />
                </div>
                <!-- <div class="mb-3">
                  <input
                    v-model="aboutForm.decorationImage"
                    class="form-control"
                    placeholder="Decoration Image URL"
                  />
                </div> -->
              </div>
            </div>

            <!-- Content Section -->
            <div class="card mb-4 shadow-sm">
              <div class="card-header bg-light">
                <h3 class="card-title mb-0">Content Section</h3>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <input
                    v-model="aboutForm.subtitle"
                    class="form-control"
                    placeholder="Subtitle"
                  />
                </div>
                <div class="mb-3">
                  <input
                    v-model="aboutForm.title"
                    class="form-control"
                    placeholder="Title"
                  />
                </div>
                <div class="mb-3">
                  <textarea
                    v-model="aboutForm.content1"
                    class="form-control"
                    rows="3"
                    placeholder="Main Content"
                  ></textarea>
                </div>
                <div class="mb-3">
                  <textarea
                    v-model="aboutForm.content2"
                    class="form-control"
                    rows="3"
                    placeholder="Secondary Content"
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- List Items Section -->
            <div class="card mb-4 shadow-sm">
              <div class="card-header bg-light">
                <h3 class="card-title mb-0">List Items</h3>
              </div>
              <div class="card-body">
                <div
                  v-for="(item, index) in aboutForm.listItems"
                  :key="index"
                  class="input-group mb-3"
                >
                  <input
                    v-model="aboutForm.listItems[index]"
                    class="form-control"
                    placeholder="List Item"
                  />
                  <button
                    type="button"
                    @click="removeListItem(index)"
                    class="btn btn-danger"
                  >
                    ×
                  </button>
                </div>
                <button
                  type="button"
                  @click="addListItem"
                  class="btn btn-primary w-100"
                >
                  Add Item
                </button>
              </div>
            </div>

            <button type="submit" class="btn btn-success w-100">Save Changes</button>
          </form>
        </div>

        <!-- Live Preview Section -->
        <div class="col-lg-6">
          <div class="card shadow-sm sticky-top">
            <!-- Video Preview -->
            <div class="position-relative">
              <div v-if="!isPlaying">
                <img
                  :src="aboutForm.thumbnailImage || '/api/placeholder/600/400'"
                  class="card-img-top"
                  alt="Video Thumbnail"
                />
                <button
                  @click="playVideo"
                  class="btn btn-dark position-absolute top-50 start-50 translate-middle"
                >
                  <i class="bi bi-play-fill"></i>
                </button>
                <!-- <img
                  :src="aboutForm.decorationImage || '/api/placeholder/100/100'"
                  class="position-absolute top-0 start-0 rounded-circle"
                  width="50"
                  height="50"
                  alt="Decoration"
                />
                <img
                  :src="aboutForm.decorationImage || '/api/placeholder/100/100'"
                  class="position-absolute bottom-0 end-0 rounded-circle"
                  width="50"
                  height="50"
                  alt="Decoration"
                /> -->
              </div>
              <div v-else class="ratio ratio-16x9">
                <iframe :src="embedUrl" allowfullscreen></iframe>
              </div>
            </div>

            <!-- Content Preview -->
            <div class="card-body">
              <p class="text-primary fw-semibold">{{ aboutForm.subtitle }}</p>
              <h2 class="card-title">{{ aboutForm.title }}</h2>
              <p>{{ aboutForm.content1 }}</p>
              <p>{{ aboutForm.content2 }}</p>
              <ul class="list-unstyled">
                <li
                  v-for="(item, index) in aboutForm.listItems"
                  :key="index"
                  class="d-flex align-items-center mb-2"
                >
                  <i class="bi bi-check-circle text-success me-2"></i>
                  <span>{{ item }}</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>

  <script>
  export default {
    data() {
      return {
        aboutForm: {
          videoId: '',
          videoUrl: '',
          thumbnailImage: '',
          decorationImage: '',
          subtitle: '',
          title: '',
          content1: '',
          content2: '',
          listItems: []
        },
        isPlaying: false
      };
    },
    computed: {
      embedUrl() {
        return this.aboutForm.videoId
          ? `https://www.youtube.com/embed/${this.aboutForm.videoId}`
          : '';
      }
    },
    methods: {
      updateVideoUrl() {
        this.aboutForm.videoUrl = `https://www.youtube.com/watch?v=${this.aboutForm.videoId}`;
      },
      playVideo() {
        this.isPlaying = true;
      },
      addListItem() {
        this.aboutForm.listItems.push('');
      },
      removeListItem(index) {
        this.aboutForm.listItems.splice(index, 1);
      },
      saveAboutContent() {
        // Replace with your API or save logic
        alert('Content saved successfully!');
        console.log(this.aboutForm);
      }
    }
  };
  </script>

  <style>
  /**** Add any custom styles here ****/
  </style>
