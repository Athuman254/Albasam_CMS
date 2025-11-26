<template>

   <Head title="Manage Exams" />
   <DefaultLayout>
      <div class="row">
         <h3 class="mb-0">Manage Exams </h3>
         <nav class="mb-3">
            <ol class="breadcrumb">
               <li class="breadcrumb-item">
                  <Link :href="route('admin.dashboard')">Home</Link>
               </li>
               <li class="breadcrumb-item text-primary">
                  Manage Exams
               </li>
            </ol>
         </nav>
         <div class="col-lg-12">
            <div class="card">
               <div class="card-header flex-column flex-md-row">
                  <div class="row row-gap-1">
                     <div class="col-md-3 col-9">
                        <!-- <input type="search" id="search" class="form-control bg-muted-lt rounded-2"
                           placeholder="Search..." v-model="appendParams.filter.search" @input="applyFilter"> -->
                     </div>
                     <div class="col-md-6 col-3 ms-lg-auto">
                        <div class="flex-wrap text-end">
                           <div class="card-action">
                              <button type="button" class="btn btn-primary d-none d-sm-inline-block"
                                 @click="openCreateExamModal">
                                 <i class="bx bx-plus-circle me-2"></i>
                                 Create Exams
                              </button>
                              <button type="button" class="btn btn-primary btn-icon d-sm-none"
                                 @click="openCreateExamModal">
                                 <i class="bx bx-plus"></i>
                              </button>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

               <!-- Table Section -->
               <div class="card-body">
                  <VueTable :fields="fields" api-url="datatable/exams" :append-params="appendParams" ref="examsTable">
                     <!-- <template v-slot:totalNetPay="props">
                        <div>{{ formatCurrency(props.rowData.totalNetPay / 100) }}</div>
                     </template> -->
                     <template v-slot:actions="props">
                        <div class="dropdown">
                           <button class="btn align-text-top py-1" data-bs-toggle="dropdown">
                              <i class="icon-base bx bx-dots-vertical"></i>
                           </button>
                           <div class="dropdown-menu dropdown-menu-end">

                              <Link class="dropdown-item" @click="view(props.rowData)">
                              <i class="icon-base bx bxs-eye me-2"></i>view
                              </Link>
                              <a class="dropdown-item text-danger" href="#" @click="deleteExam(props.rowData)">
                                 <i class="icon-base bx bx-trash me-2"></i>Delete
                              </a>
                           </div>
                        </div>
                     </template>
                  </VueTable>
               </div>
            </div>
         </div>
      </div>

      <!-- Create Modal -->
      <div class="modal fade" id="create-rank-modal" data-bs-backdrop="static" tabindex="-1"
         aria-labelledby="create-rank-modal-label" aria-hidden="true" ref="createExamModal">
         <div class="modal-dialog">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="create-rank-modal-label">Add Exam</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                     @click="formCleanUp"></button>
               </div>
               <div class="modal-body">
                  <form id="createForm" @submit.prevent="createRank">
                     <div class="mb-3">
                        <label for="name" class="form-label">Exam Name</label>
                        <input id="name" type="text" v-model="form.name" class="form-control">
                        <div v-if="form.errors.name" class="text-danger">{{ form.errors.name }}</div>
                     </div>

                     <div class="mb-3">
                        <label for="divisionId" class="form-label">Session</label>
                        <v-select id="divisionId" v-model="form.academic_year_id" :options="academicYears" label="name"
                           :reduce="option => option.id"></v-select>
                        <div v-if="form.errors.academic_year_id" class="text-danger">{{ form.errors.academic_year_id }}
                        </div>
                     </div>

                     <div class="mb-3">
                        <label for="streamId" class="form-label">Clascses</label>
                        <v-select id="streamId" multiple v-model="form.classes" :options="classes" label="name"
                           :reduce="option => option.id">

                           <template #option="{ name, stream }">
                              <strong>{{ name }}</strong> - <small>{{ stream.name }}</small>
                           </template>


                           <template #selected-option="props">
                              {{ props.name }} - {{ props.stream?.name }}
                           </template>
                        </v-select>
                        <div v-if="form.errors.classes" class="text-danger">{{ form.errors.classes }}</div>
                     </div>

                     <div class="mb-3">
                        <label for="teacherId" class="form-label">Description</label>
                        <textarea class="form-control" name="" id="" v-model="form.description"></textarea>
                        <div v-if="form.errors.description" class="text-danger">{{ form.errors.description }}</div>
                     </div>
                     <div class="mb-3" v-if="form.classes.length">
                        <h5>Assign Subjects for Each Class</h5>

                        <div v-for="classId in form.classes" :key="classId" class="border rounded p-3 mb-3">
                           <h6>{{ getClassName(classId) }}</h6>

                           <v-select multiple :options="subjects" label="name" :reduce="option => option.id"
                              v-model="form.classSubjects[classId]">
                           </v-select>
                        </div>
                     </div>
                  </form>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" @click="formCleanUp">
                     Close
                  </button>
                  <button v-if="!editMode" type="button" class="btn btn-primary" @click.prevent="createExam">
                     {{ editMode ? 'Update' : 'Submit' }}
                  </button>

               </div>
            </div>
         </div>
      </div>
   </DefaultLayout>
</template>

<script setup>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { Modal } from 'bootstrap';
import _debounce from 'lodash/debounce';
import { ref, computed, watch, onMounted } from 'vue'
import axios from "axios";
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
// import { error } from "jquery";

const createExamModal = ref(null)
const editMode = ref(false)
const selectedExamId = ref(null)
const examsTable = ref(null)
const fields = [

   {
      name: 'name',
      title: 'NAME',
   },
   {
      name: 'academic_year.name',
      title: 'ACADEMIC YEAR',
   },
   {
      name: '__slot:actions',
      title: 'ACTIONS',
      titleClass: '5%',
      dataClass: '5%',
   },
]
const appendParams = ref({
   filter: {

   }
})
const subjects = ref([])
const classes = ref([])
const academicYears = ref([])
const form = useForm({
   name: '',
   academic_year_id: '',
   classSubjects: [],
   classes: [],
   description: ''
})
const openCreateExamModal = () => {
   const modalInstance = Modal.getOrCreateInstance(createExamModal.value);
   modalInstance.show();
}

const fetchStreams = () => {
   axios.get('/datatable/ranks', {
      params: {
         filter: {
            activated: true,
         }
      }
   })
      .then(({ data }) => {
         classes.value = data.data;
      }).catch((error) => {
         console.error(error)
         this.$toast.error('An error occurred when fetching the streams.')
      })
}
const fetchSubjects = () => {
   axios.get('/datatable/subjects', {
      params: {
         filter: {
            activated: true,
         }
      }
   })
      .then(({ data }) => {
         subjects.value = data.data;
      }).catch((error) => {
         console.error(error)
         this.$toast.error('An error occurred when fetching the streams.')
      })
}
const fetchAcademicYears = () => {
   axios.get('/datatable/academic-years', {
      params: {
         filter: {
            is_active: true,
         }
      }
   })
      .then(({ data }) => {
         academicYears.value = data.data
      }).catch((error) => {
         console.error(error)
         this.$toast.error('An error occurred when fetching the streams.')
      })
}
const getClassName = (id) => {
   const cls = classes.value.find(c => c.id === id);
   return cls ? cls.name + '-' + cls.stream?.name : '';
}
const view = (rowData) => {
   editMode.value = true
   selectedExamId.value = rowData.id
   form.name = rowData.name
   form.academic_year_id = rowData.academic_year_id
   form.description = rowData.description ?? ''

   const uniqueClassIds = [...new Set(rowData.subjects.map(s => s.class_id))]
   form.classes = uniqueClassIds

   form.classSubjects = {}
   uniqueClassIds.forEach(classId => {
      form.classSubjects[classId] = rowData.subjects
         .filter(sub => sub.class_id === classId)
         .map(sub => sub.subject_id)
   })

   const modalInstance = Modal.getOrCreateInstance(createExamModal.value);
   modalInstance.show();
}
const deleteExam = (exam) => {
   axios.delete(route('admin.exams.manage.destroy', exam.id))
      .then((res) => {
         examsTable.value.reloadTable()
          toast.success('Deleted')
      }).catch((error) => {
          toast.error("Failed to delete")
      })
}
const createExam = () => {
   form.post(route('admin.exams.manage.store'), {
      onSuccess: () => {
         form.reset();
         form.clearErrors();
         examsTable.value.reloadTable()
         const modalInstance = Modal.getOrCreateInstance(createExamModal.value);
         modalInstance.hide();
      },
      onError: (errors) => {

      },
   });
}
const formCleanUp = () => {
   form.reset();
   form.clearErrors();
   const modalInstance = Modal.getOrCreateInstance(createExamModal.value);
}
function formatCurrency(amount) {
   return new Intl.NumberFormat('KES').format(amount)
}
onMounted(() => {
   fetchStreams()
   fetchAcademicYears()
   fetchSubjects()
})
</script>
