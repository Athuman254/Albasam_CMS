<template>
    <div class="bg-white p-3 rounded">
        <ul class="nav nav-tabs nav-pills my-3" id="pills-tab" role="tablist">

            <li class="nav-tabs nav-item" role="presentation">

                <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home"
                    type="button" role="tab" aria-controls="pills-home" aria-selected="true">
                    <span><i class="bi bi-person"></i></span> Slider</button>
            </li>

            <li class="nav-tabs nav-item" role="presentation">

                <button class="nav-link" id="pills-location-tab" data-bs-toggle="pill" data-bs-target="#pills-location"
                    type="button" role="tab" aria-controls="pills-location" aria-selected="false">
                    <i class="bi bi-person-circle"></i> Quotes Section</button>
            </li>

            <li class="nav-tabs nav-item" role="presentation">

                <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact"
                    type="button" role="tab" aria-controls="pills-contact" aria-selected="false">
                    <i class="bi bi-globe"></i> About Section</button>
            </li>
            <li class="nav-tabs nav-item" role="presentation">

                <button class="nav-link" id="pills-why-us-tab" data-bs-toggle="pill" data-bs-target="#pills-why-us"
                    type="button" role="tab" aria-controls="pills-why-us" aria-selected="false">
                    <i class="bi bi-globe"></i> Why us Section</button>
            </li>
            <li class="nav-tabs nav-item" role="presentation">

                <button class="nav-link" id="pills-testimonial-tab" data-bs-toggle="pill"
                    data-bs-target="#pills-testimonial" type="button" role="tab" aria-controls="pills-testimonial"
                    aria-selected="false">
                    <i class="bi bi-globe"></i> Testimonial Section</button>
            </li>
            <li class="nav-tabs nav-item" role="presentation">

                <button class="nav-link" id="pills-events-tab" data-bs-toggle="pill" data-bs-target="#pills-events"
                    type="button" role="tab" aria-controls="pills-events" aria-selected="false">
                    <i class="bi bi-globe"></i> Events Section</button>
            </li>
            <li class="nav-tabs nav-item" role="presentation">

<button class="nav-link" id="pills-quicklinks-tab" data-bs-toggle="pill" data-bs-target="#pills-quicklinks"
    type="button" role="tab" aria-controls="pills-quicklinks" aria-selected="false">
    <i class="bi bi-globe"></i> Quick Links Section</button>
</li>
        </ul>
        <div class="tab-content shadow-none" id="pills-tabContent">
            <div class="tab-pane fade show active " id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
                <!-- <div class="row"> -->
                    <!-- <div class="col-lg-8">
                        <div class="col-md">
                            <h5 class="mb-6">Available Slides</h5>
                            <Carousel v-bind="carouselConfig">
                                <Slide v-for="slide in home_slides" :key="slide">
                                    <div class="carousel_item">
                                        <div class="position-absolute">x</div>
                                        <img class="d-block w-100" :src="'/storage/' + slide.image_src"
                                            alt="First slide" />
                                        <div class="carousel-caption d-none d-md-block">
                                            <h3>{{ slide.caption_title }}</h3>
                                            <p>{{ slide.caption }}</p>
                                        </div>

                                    </div>
                                </Slide>

                                <template #addons>
                                    <Navigation />
                                    <Pagination />
                                </template>
                            </Carousel>
                        </div>
                    </div> -->
                    <!-- <div class="col-lg-4"> -->
                        <!-- Form Section -->

                        <!-- <div class="w-full">
                            <h5 class="text-lg font-semibold mb-3">Add Slide</h5>
                            <form @submit.prevent="submit">
                                <div>
                                    <label for="title">Title</label>
                                    <input id="title" type="text" v-model="form.title" class="input"
                                        placeholder="Enter the title" required />
                                    <span v-if="form.errors.title" class="text-red-500">{{ errors.title }}</span>
                                </div>

                                <div>
                                    <label for="description">Description</label>
                                    <textarea id="description" v-model="form.description" class="input"
                                        placeholder="Enter the description" required></textarea>
                                    <span v-if="form.errors.description" class="text-red-500">{{ errors.description
                                        }}</span>
                                </div>

                                <div>
                                    <label for="image">Image</label>
                                    <input class="form-control mb-3 bg-white" id="image" type="file"
                                        @change="handleFileChange" />
                                    <span v-if="form.errors.image" class="text-red-500">{{ errors.image }}</span>
                                </div>

                                <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
                        </div> -->
                    <!-- </div> -->
                <!-- </div> -->
                        <slider-section :home_slides="home_slides"/>
            </div>
            <div class="tab-pane fade" id="pills-location" role="tabpanel" aria-labelledby="pills-location-tab">
                <!-- <div class="row"> -->
                    <!-- <div class="col-lg-8">
                        <h5>Available Quotes</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="quote in quotes" :key="quote.id"
                                class="service-card bg-blue-600 p-6 rounded-lg">
                                <div class="flex flex-col items-center text-center">

                                    <div class="mb-4">
                                        <svg class="w-16 h-16 text-white" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                    </div>
                                    <h5 class="text-white text-xl font-bold mb-2">{{ quote.title }}</h5>
                                    <p class="text-white mb-4">{{ quote.description }}</p>
                                    <button class="px-4 py-2 bg-white text-blue-600 rounded">
                                        Read More
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    <!-- <div class="col-lg-4">
                        <h6>Add a Quote</h6>
                        <form @submit.prevent="addService" class="mb-8 space-y-4">
                            <input v-model="quoteForm.icon" class="w-full p-2 border rounded" placeholder="Icon"
                                required />
                            <input v-model="quoteForm.title" class="w-full p-2 border rounded"
                                placeholder="Service Title" required />
                            <input v-model="quoteForm.link" class="w-full p-2 border rounded"
                                placeholder="read more link" required />
                            <textarea v-model="quoteForm.description" class="w-full p-2 border rounded"
                                placeholder="Service Description" required />
                            <button class="px-4 py-2 bg-blue-600 text-white rounded">
                                Add Service
                            </button>
                        </form>
                    </div> -->
                <!-- </div> -->
                <quote-section :in_quotes="quotes"/>
            </div>
            <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
                <div class="flex flex-col lg:flex-row gap-6 p-6">
                    <!-- Form Section -->
                    <div class="lg:w-1/2">
                        <form @submit.prevent="saveAboutContent" class="space-y-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-semibold mb-4">Video Section</h3>
                                <div class="space-y-3">

                                    <div>
                                        <label class="block text-sm mb-1">YouTube Video ID</label>
                                        <input v-model="aboutForm.videoId" placeholder="e.g. 2Ge1GGitzLw"
                                            class="w-full p-2 border rounded" @input="updateVideoUrl">
                                    </div>
                                    <!-- <input v-model="aboutForm.videoUrl" placeholder="Video URL" -->
                                        <!-- class="w-full p-2 border rounded"> -->
                                    <input v-model="aboutForm.thumbnailImage" placeholder="Thumbnail Image URL"
                                        class="w-full p-2 border rounded">
                                    <!-- <input v-model="aboutForm.decorationImage" placeholder="Decoration Image URL"
                                        class="w-full p-2 border rounded"> -->
                                </div>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-semibold mb-4">Content Section</h3>
                                <div class="space-y-3">
                                    <input v-model="aboutForm.subtitle" placeholder="Subtitle"
                                        class="w-full p-2 border rounded">
                                    <input v-model="aboutForm.title" placeholder="Title"
                                        class="w-full p-2 border rounded">
                                    <textarea v-model="aboutForm.content1" placeholder="Main Content" rows="3"
                                        class="w-full p-2 border rounded"></textarea>
                                    <textarea v-model="aboutForm.content2" placeholder="Secondary Content" rows="3"
                                        class="w-full p-2 border rounded"></textarea>
                                </div>
                            </div>

                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-semibold mb-4">List Items</h3>
                                <div class="space-y-3">
                                    <div v-for="(item, index) in aboutForm.listItems" :key="index" class="flex gap-2">
                                        <input v-model="aboutForm.listItems[index]" class="flex-1 p-2 border rounded">
                                        <button @click="removeListItem(index)"
                                            class="px-3 py-2 bg-red-500 text-white rounded">×</button>
                                    </div>
                                    <button @click="addListItem" type="button"
                                        class="w-full px-4 py-2 bg-blue-500 text-white rounded">
                                        Add Item
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="w-full px-4 py-2 bg-green-500 text-white rounded">
                                Save Changes
                            </button>
                        </form>
                    </div>

                    <!-- Live Preview Section -->
                    <div class="lg:w-1/2 sticky top-6">
                        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                            <!-- Video Preview -->
                            <div class="relative">
                                <div v-if="!isPlaying" class="relative">
                                    <img :src="aboutForm.thumbnailImage || '/api/placeholder/600/400'"
                                        class="w-full h-64 object-cover">
                                    <div @click="playVideo"
                                        class="absolute inset-0 bg-black/50 flex items-center justify-center cursor-pointer hover:bg-black/60 transition-colors">
                                        <svg class="w-16 h-16 text-white" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M8 5v14l11-7z" />
                                        </svg>
                                    </div>
                                    <!-- <img :src="aboutForm.decorationImage || '/api/placeholder/100/100'"
                                        class="absolute -top-4 -left-4 w-16 h-16">
                                    <img :src="aboutForm.decorationImage || '/api/placeholder/100/100'"
                                        class="absolute -bottom-4 -right-4 w-16 h-16"> -->
                                </div>
                                <div v-else class="aspect-video">
                                    <iframe :src="embedUrl" class="w-full h-full" frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen></iframe>
                                </div>
                            </div>

                            <!-- Content Preview -->
                            <div class="p-6">
                                <p class="text-blue-600 font-semibold tracking-wider">{{ aboutForm.subtitle }}</p>
                                <h2 class="text-3xl font-bold my-4">{{ aboutForm.title }}</h2>
                                <div class="space-y-4">
                                    <p>{{ aboutForm.content1 }}</p>
                                    <p>{{ aboutForm.content2 }}</p>
                                    <ul class="space-y-3">
                                        <li v-for="(item, index) in aboutForm.listItems" :key="index"
                                            class="flex items-center">
                                            <svg class="w-5 h-5 text-blue-600 mr-2" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                            </svg>
                                            <span>{{ item }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- <about-section/> -->
            </div>
            <div class="tab-pane fade" id="pills-why-us" role="tabpanel" aria-labelledby="pills-why-us-tab">
                <why-us-section-vue :reasons="why_choose_us" />
            </div>
            <div class="tab-pane fade" id="pills-testimonial" role="tabpanel" aria-labelledby="pills-testimonial-tab">

                <testimonial-section-vue :testimonials="testimonials" />
            </div>
            <div class="tab-pane fade" id="pills-events" role="tabpanel" aria-labelledby="pills-events-tab">
                <event-section :events="events" />
            </div>
            <div class="tab-pane fade" id="pills-quicklinks" role="tabpanel" aria-labelledby="pills-quicklinks-tab">
                <quick-link-section :quick_links="quick_links"/>
            </div>
        </div>
    </div>
</template>

<script>
import 'vue3-carousel/carousel.css'
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel'
import { useForm } from '@inertiajs/inertia-vue3';
import WhyUsSectionVue from './Components/WhyUsSection.vue';
import TestimonialSectionVue from './Components/TestimonialSection.vue';
import EventSection from './Components/EventSection.vue'
import QuickLinkSection from './Components/QuickLinkSection.vue'
import SliderSection from './Components/SliderSection.vue';
import QuoteSection from './Components/QuoteSection.vue'
import AboutSection from './Components/AboutSection.vue';

export default {
    props: {
        home_slides: {
            default: () => []
        },
        quotes: {
            default: () => []
        },
        why_choose_us: {
            default: () => []
        },
        testimonials: {
            default: () => []
        },
        events: {
            default: () => []
        },
        quick_links: {
            default: () => []
        },
        about: {
            default: ()=>{}
        }


    },
    components: { Carousel, Slide, Pagination, Navigation, WhyUsSectionVue, TestimonialSectionVue, EventSection,  QuickLinkSection, SliderSection, QuoteSection, AboutSection},
    data() {
        return {
            carouselConfig: {
                itemsToShow: 2.5,
                wrapAround: true
            },
            form: useForm({
                title: '',
                description: '',
                image: null,
            }),
            quoteForm: useForm({
                title: '',
                description: '',
                link: null,
                icon: null,
            }),
            aboutForm: useForm({
                videoId: '2Ge1GGitzLw',
                videoUrl: '',
                thumbnailImage: '',
                decorationImage: '',
                subtitle: '',
                title: '',
                content1: '',
                content2: '',
                listItems: ['']
            }),
            // about section
            isPlaying: false,
            services: [{
                id: 1,
                title: 'Education Services',
                description: 'Education is the most power which you can use to change the world." Education is the key to.'
            }, {
                id: 1,
                title: 'Education Services',
                description: 'Education is the most power which you can use to change the world." Education is the key to.'
            }, {
                id: 1,
                title: 'Education Services',
                description: 'Education is the most power which you can use to change the world." Education is the key to.'
            }, {
                id: 1,
                title: 'Education Services',
                description: 'Education is the most power which you can use to change the world." Education is the key to.'
            }],
            newService: {
                title: '',
                description: ''
            }

        };
    },
    mounted() {
        if(Object.keys(this.about).length != 0){
            this.aboutForm.videoId = this.about.video_src
            this.aboutForm.subtitle = this.about.title_section
            this.aboutForm.title = this.about.title
            this.aboutForm.content1 = this.about.description.split('|')[0]
            this.aboutForm.content2 = this.about.description.split('|')[1]
            if(this.about.stmt1){
                this.aboutForm.listItems[0] = this.about.stmt1
            }
            this.about.stmt2 && this.aboutForm.listItems.push(this.about.stmt2)
            this.about.stmt3 && this.aboutForm.listItems.push(this.about.stmt3)
            this.about.stmt4 && this.aboutForm.listItems.push(this.about.stmt4)
        }


  },
    computed: {
        embedUrl() {
            return `https://www.youtube.com/embed/${this.aboutForm.videoId}?autoplay=1`
        }
    },
    methods: {
        handleFileChange(event) {
            this.form.image = event.target.files[0];
        },


        // about section
        addListItem() {
            this.aboutForm.listItems.push('')
        },
        removeListItem(index) {
            this.aboutForm.listItems.splice(index, 1)
        },
        saveAboutContent() {
            if(Object.keys(this.about).length == 0){
                this.aboutForm.post('/website/pages/homepage/about', {
                onSuccess: () => {
                    this.$toast.success('Changes saved Successfully', 'Success')
                },
                onError: (errors) => {
                    this.$toast.error('Error occured', 'Error')
                },
            });
            }else{
                this.aboutForm.patch('/website/pages/homepage/about/'+this.about.id, {
                onSuccess: () => {
                    this.$toast.success('Changes saved Successfully', 'Success')
                },
                onError: (errors) => {
                    this.$toast.error('Error occured', 'Error')
                }
            });
            }

        },
        playVideo() {
            this.isPlaying = true
        },
        updateVideoUrl() {
            this.isPlaying = false
        },


    },
};
</script>

<style scoped>
.input {
    width: 100%;
    padding: 0.5rem;
    margin-bottom: 1rem;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.text-red-500 {
    color: #f56565;
}

.btn {
    padding: 0.5rem 1rem;
    background-color: #3182ce;
    color: white;
    border: none;
    border-radius: 4px;
}

.btn-primary {
    background-color: #2b6cb0;
}
</style>
