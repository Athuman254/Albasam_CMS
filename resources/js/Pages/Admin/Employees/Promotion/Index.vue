<template>
  <Head title="Student Promotion" />
  <DefaultLayout>
    <div class="row">
      <h3 class="mb-0">Student Promotion</h3>
      <nav class="mb-3">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <Link :href="route('employee.dashboard')">Home</Link>
          </li>
          <li class="breadcrumb-item">Student Promotion</li>
        </ol>
      </nav>

      <div class="col-lg-12">
        <div class="card border">
          <div class="card-header bg-white border-bottom">
            <h5 class="card-title mb-1">Student Promotion Management</h5>
            <p class="mb-0">Promote students to next class and track promotion history</p>
          </div>

          <div class="card-body">
            <!-- Stats Cards -->
            <div class="row mb-4" v-if="isClassTeacher && stats">
              <div class="col-md-3 col-6 mb-3">
                <div class="card border">
                  <div class="card-body text-center p-3">
                    <div class="avatar avatar-lg mb-2">
                      <span class="avatar-initial rounded bg-label-primary">
                        <i class="bx bx-user"></i>
                      </span>
                    </div>
                    <h6 class="card-title mb-1">Total Students</h6>
                    <h4 class="mb-0">{{ stats.total_students }}</h4>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6 mb-3">
                <div class="card border">
                  <div class="card-body text-center p-3">
                    <div class="avatar avatar-lg mb-2">
                      <span class="avatar-initial rounded bg-label-success">
                        <i class="bx bx-up-arrow-alt"></i>
                      </span>
                    </div>
                    <h6 class="card-title mb-1">Promoted This Year</h6>
                    <h4 class="mb-0">{{ stats.promoted_this_year }}</h4>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6 mb-3">
                <div class="card border">
                  <div class="card-body text-center p-3">
                    <div class="avatar avatar-lg mb-2">
                      <span class="avatar-initial rounded bg-label-warning">
                        <i class="bx bx-time"></i>
                      </span>
                    </div>
                    <h6 class="card-title mb-1">Pending Promotion</h6>
                    <h4 class="mb-0">{{ stats.pending_promotion }}</h4>
                  </div>
                </div>
              </div>
              <div class="col-md-3 col-6 mb-3">
                <div class="card border">
                  <div class="card-body text-center p-3">
                    <div class="avatar avatar-lg mb-2">
                      <span class="avatar-initial rounded bg-label-info">
                        <i class="bx bx-star"></i>
                      </span>
                    </div>
                    <h6 class="card-title mb-1">Special Promotions</h6>
                    <h4 class="mb-0">{{ stats.special_promotions }}</h4>
                  </div>
                </div>
              </div>
            </div>

            <!-- Error Message for Non-Class Teachers -->
            <div v-if="!isClassTeacher" class="alert alert-warning">
              <i class="bx bx-info-circle me-2"></i>
              {{ error }}
            </div>

            <!-- Promotion Interface for Class Teachers -->
            <div v-else>
              <!-- Class Selection Section -->
              <div class="row mb-4">
                <div class="col-md-6">
                  <label class="form-label">Select Your Class</label>
                  <select v-model="selectedClass" class="form-select" @change="onClassSelected">
                    <option value="">Choose Class...</option>
                    <option v-for="rank in teacherClasses" :key="rank.id" :value="rank.id">
                      {{ rank.name }} 
                      <span v-if="rank.stream">- {{ rank.stream.name }}</span>
                      <span v-if="rank.division">- {{ rank.division.name }}</span>
                    </option>
                  </select>
                  <div class="form-text">
                    Only classes where you are the class teacher are shown
                  </div>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Promote To Class</label>
                  <select v-model="nextClass" class="form-select" :disabled="!selectedClass">
                    <option value="">Choose Next Class...</option>
                    <option v-for="classItem in availableNextClasses" :key="classItem.id" :value="classItem.id">
                      {{ classItem.name }}
                      <span v-if="classItem.stream">- {{ classItem.stream.name }}</span>
                      <span v-if="classItem.division">- {{ classItem.division.name }}</span>
                    </option>
                  </select>
                  <div class="form-text">
                    Only higher classes are available for promotion
                  </div>
                </div>
              </div>

              <!-- Current Class Info -->
              <div v-if="selectedClass" class="alert alert-info bg-light border mb-4">
                <div class="d-flex align-items-center">
                  <i class="bx bx-info-circle me-2"></i>
                  <div>
                    <strong>Current Class:</strong> {{ getClassName(selectedClass) }}
                    <span v-if="currentClassInfo"> | Form Level: {{ currentClassInfo.formLevel }}</span>
                  </div>
                </div>
              </div>

              <!-- Students List -->
              <div v-if="selectedClass && students.length > 0">
                <div class="card border">
                  <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                    <div>
                      <h5 class="card-title mb-1">Students in {{ getClassName(selectedClass) }}</h5>
                      <span class="badge border bg-primary text-white">{{ students.length }} students</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                      <button @click="toggleSelectAll" class="btn btn-outline-primary btn-sm border">
                        {{ allSelected ? 'Deselect All' : 'Select All Eligible' }}
                      </button>
                      <button @click="loadClassStudents" class="btn btn-outline-secondary btn-sm border">
                        <i class="bx bx-refresh"></i> Refresh
                      </button>
                    </div>
                  </div>

                  <div class="card-body">
                    <!-- Special Promotion Toggle -->
                    <div class="row mb-4">
                      <div class="col-12">
                        <div class="form-check form-switch">
                          <input v-model="specialPromotion" class="form-check-input" type="checkbox" id="specialPromotion">
                          <label class="form-check-label" for="specialPromotion">
                            <strong>Special Promotion</strong> - Allow promotion for students who haven't completed all terms
                          </label>
                        </div>
                      </div>
                    </div>

                    <!-- Reason for Special Promotion -->
                    <div v-if="specialPromotion" class="row mb-4">
                      <div class="col-12">
                        <label class="form-label">Reason for Special Promotion</label>
                        <textarea v-model="promotionReason" class="form-control" rows="3" 
                                  placeholder="Explain why these students need special promotion..."></textarea>
                      </div>
                    </div>

                    <!-- Students Table -->
                    <div class="table-responsive">
                      <table class="table table-bordered align-middle">
                        <thead class="bg-light">
                          <tr>
                            <th width="50">
                              <input type="checkbox" v-model="allSelected" @change="toggleSelectAll">
                            </th>
                            <th>Admission No.</th>
                            <th>Student Name</th>
                            <th>Gender</th>
                            <th>Academic Progress</th>
                            <th>Eligibility</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="student in students" :key="student.id" 
                              :class="{
                                'table-warning': !student.is_eligible_for_promotion && !specialPromotion,
                                'table-success': student.selected
                              }">
                            <td>
                              <input type="checkbox" v-model="student.selected" 
                                     :disabled="!student.is_eligible_for_promotion && !specialPromotion">
                            </td>
                            <td>
                              <strong>{{ student.admission_number }}</strong>
                            </td>
                            <td>
                              <strong>{{ student.full_name }}</strong>
                            </td>
                            <td>{{ student.gender }}</td>
                            <td>
                              <span class="badge border" 
                                    :class="student.has_completed_all_terms ? 'bg-success text-white' : 'bg-warning text-dark'">
                                {{ student.completed_terms.length }}/3 Terms
                              </span>
                              <small class="text-muted d-block mt-1">
                                Terms: {{ student.completed_terms.join(', ') || 'None' }}
                              </small>
                            </td>
                            <td>
                              <span v-if="student.is_eligible_for_promotion" class="badge border bg-success text-white">
                                <i class="bx bx-check me-1"></i> Eligible
                              </span>
                              <span v-else class="badge border bg-danger text-white">
                                <i class="bx bx-x me-1"></i> Not Eligible
                              </span>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>

                    <!-- Summary -->
                    <div class="row mt-3">
                      <div class="col-12">
                        <div class="alert alert-info bg-light border">
                          <div class="d-flex justify-content-between align-items-center">
                            <div>
                              <strong>Summary:</strong> 
                              {{ selectedStudentsCount }} selected • 
                              {{ eligibleStudentsCount }} eligible • 
                              {{ nonEligibleStudentsCount }} not eligible
                            </div>
                            <div v-if="selectedStudentsCount > 0 && nextClass" class="text-success">
                              <i class="bx bx-up-arrow-alt"></i>
                              Promoting to: <strong>{{ getClassName(nextClass) }}</strong>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                      <div class="col-12">
                        <button @click="promoteStudents" 
                                :disabled="!canPromote" 
                                class="btn btn-primary me-2 border">
                          <i class="bx bx-up-arrow-alt me-1"></i> Promote Selected Students
                        </button>
                        <button @click="viewPromotionHistory" 
                                class="btn btn-outline-info me-2 border"
                                :disabled="!selectedClass">
                          <i class="bx bx-history me-1"></i> View Promotion History
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Empty State -->
              <div v-if="selectedClass && students.length === 0" class="text-center py-5">
                <i class="bx bx-user-x display-4 mb-3"></i>
                <h5>No Students Found</h5>
                <p class="mb-4">There are no students in this class to promote, or all students have already been promoted this academic year.</p>
                <button class="btn btn-primary" @click="loadClassStudents">
                  <i class="bx bx-refresh me-1"></i> Refresh
                </button>
              </div>

              <!-- No Class Selected State -->
              <div v-if="!selectedClass" class="text-center py-5">
                <i class="bx bx-select-multiple display-4 mb-3"></i>
                <h5>Select a Class</h5>
                <p class="mb-4">Please select a class from the dropdown above to view students available for promotion.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Promotion Confirmation Modal -->
    <div class="modal fade" id="promotionModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border">
          <div class="modal-header bg-white border-bottom">
            <h5 class="modal-title">Promotion Results</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div v-if="promotionResult" class="text-center">
              <i class="bx bx-check-circle text-success display-1"></i>
              <h4 class="text-success mt-3">Promotion Completed!</h4>
              <p class="mb-2">
                Successfully promoted <strong class="text-success">{{ promotionResult.promoted_count }}</strong> students 
                from <strong>{{ promotionResult.from_class }}</strong> to <strong>{{ promotionResult.to_class }}</strong>.
              </p>
              <div v-if="promotionResult.not_eligible_count > 0 || promotionResult.already_promoted_count > 0 || promotionResult.not_in_class_count > 0" 
                   class="alert alert-warning mt-3 text-start bg-light border">
                <h6 class="alert-heading">Details:</h6>
                <ul class="mb-0">
                  <li v-if="promotionResult.not_eligible_count > 0">
                    <strong>{{ promotionResult.not_eligible_count }}</strong> students were not eligible for promotion
                  </li>
                  <li v-if="promotionResult.already_promoted_count > 0">
                    <strong>{{ promotionResult.already_promoted_count }}</strong> students were already promoted this academic year
                  </li>
                  <li v-if="promotionResult.not_in_class_count > 0">
                    <strong>{{ promotionResult.not_in_class_count }}</strong> students are not in the selected class
                  </li>
                </ul>
              </div>
            </div>
          </div>
          <div class="modal-footer bg-white border-top">
            <button type="button" class="btn btn-primary border" data-bs-dismiss="modal" @click="refreshAfterPromotion">
              OK
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Promotion History Modal -->
    <div class="modal fade" id="promotionHistoryModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl">
        <div class="modal-content border">
          <div class="modal-header bg-white border-bottom">
            <h5 class="modal-title">Promotion History - {{ getClassName(selectedClass) }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div v-if="promotionHistory.length > 0" class="table-responsive">
              <table class="table table-bordered align-middle">
                <thead class="bg-light">
                  <tr>
                    <th>Student Name</th>
                    <th>Admission No.</th>
                    <th>Promoted To</th>
                    <th>Academic Year</th>
                    <th>Promoted By</th>
                    <th>Date</th>
                    <th>Type</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="history in promotionHistory" :key="history.id">
                    <td>{{ history.student_name }}</td>
                    <td>{{ history.admission_number }}</td>
                    <td>{{ history.to_class }}</td>
                    <td>{{ history.academic_year }}</td>
                    <td>{{ history.promoted_by }}</td>
                    <td>{{ history.promoted_at }}</td>
                    <td>
                      <span v-if="history.special_promotion" class="badge border bg-warning text-dark">Special</span>
                      <span v-else class="badge border bg-success text-white">Regular</span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div v-else class="text-center py-4">
              <i class="bx bx-history display-4 mb-3"></i>
              <h4 class="text-muted mt-3">No Promotion History</h4>
              <p class="text-muted">No students have been promoted from this class yet.</p>
            </div>
          </div>
          <div class="modal-footer bg-white border-top">
            <button type="button" class="btn btn-secondary border" data-bs-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </DefaultLayout>
</template>

<script>
import DefaultLayout from "@layouts/DefaultLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { Modal } from 'bootstrap';

export default {
  name: 'StudentPromotion',
  components: { 
    DefaultLayout,
    Head,
    Link
  },
  props: {
    ranks: Array,
    allClasses: Array,
    isClassTeacher: Boolean,
    error: String,
    classTeacherAssignments: Array
  },
  data() {
    return {
      selectedClass: '',
      nextClass: '',
      students: [],
      specialPromotion: false,
      promotionReason: '',
      promotionResult: null,
      promotionHistory: [],
      promotionModal: null,
      promotionHistoryModal: null,
      stats: null,
      currentClassInfo: null
    };
  },
  computed: {
    // Only show classes where teacher is class teacher
    teacherClasses() {
      return this.ranks || [];
    },
    
    // Filter available next classes to only show higher classes
    availableNextClasses() {
      if (!this.selectedClass || !this.currentClassInfo) return [];
      
      const currentFormLevel = this.currentClassInfo.formLevel;
      
      return this.allClasses.filter(classItem => {
        const classFormLevel = this.extractFormLevel(classItem.name);
        // Only show classes with higher form levels
        return classFormLevel > currentFormLevel;
      });
    },
    
    allSelected: {
      get() {
        const eligibleStudents = this.students.filter(student => 
          student.is_eligible_for_promotion || this.specialPromotion
        );
        return eligibleStudents.length > 0 && eligibleStudents.every(student => student.selected);
      },
      set(value) {
        this.students.forEach(student => {
          if (student.is_eligible_for_promotion || this.specialPromotion) {
            student.selected = value;
          }
        });
      }
    },
    selectedStudentsCount() {
      return this.students.filter(student => student.selected).length;
    },
    eligibleStudentsCount() {
      return this.students.filter(student => student.is_eligible_for_promotion).length;
    },
    nonEligibleStudentsCount() {
      return this.students.filter(student => !student.is_eligible_for_promotion).length;
    },
    canPromote() {
      return this.selectedClass && 
             this.nextClass && 
             this.selectedStudentsCount > 0 &&
             this.selectedClass !== this.nextClass;
    }
  },
  mounted() {
    this.promotionModal = new Modal(document.getElementById('promotionModal'));
    this.promotionHistoryModal = new Modal(document.getElementById('promotionHistoryModal'));
    this.loadPromotionStats();
  },
  methods: {
    getClassName(classId) {
      const rank = this.ranks.find(r => r.id == classId) || 
                  this.allClasses.find(c => c.id == classId);
      return rank ? rank.name : 'Unknown Class';
    },
    
    extractFormLevel(className) {
      // Extract form number from class name (e.g., "Form 1" -> 1, "Form 2" -> 2)
      const match = className.match(/Form\s*(\d+)/i);
      return match ? parseInt(match[1]) : 0;
    },
    
    onClassSelected() {
      if (this.selectedClass) {
        const selectedRank = this.ranks.find(r => r.id == this.selectedClass);
        if (selectedRank) {
          this.currentClassInfo = {
            name: selectedRank.name,
            formLevel: this.extractFormLevel(selectedRank.name)
          };
        }
        this.loadClassStudents();
        this.nextClass = ''; // Reset next class selection
      } else {
        this.currentClassInfo = null;
        this.nextClass = '';
        this.students = [];
      }
    },
    
    async loadPromotionStats() {
      if (!this.isClassTeacher) return;
      
      try {
        const response = await axios.get('/employee/promotion/stats');
        this.stats = response.data;
      } catch (error) {
        console.error('Error loading promotion stats:', error);
        // If route not found, set default empty stats
        if (error.response?.status === 404 || error.response?.data?.message?.includes('could not be found')) {
          this.stats = {
            total_students: 0,
            promoted_this_year: 0,
            pending_promotion: 0,
            special_promotions: 0
          };
        } else if (error.response?.status !== 403) { // Don't show error if not class teacher
          this.$toast.error('Failed to load promotion statistics');
        }
      }
    },
    
    async loadClassStudents() {
      if (!this.selectedClass) return;
      
      try {
        const response = await axios.get('/employee/promotion/get-class-students', {
          params: {
            class_id: this.selectedClass
          }
        });
        this.students = response.data.students;
      } catch (error) {
        console.error('Error loading students:', error);
        const errorMessage = error.response?.data?.error || 'Failed to load students';
        this.$toast.error(errorMessage);
      }
    },
    
    toggleSelectAll() {
      const newValue = !this.allSelected;
      this.students.forEach(student => {
        if (student.is_eligible_for_promotion || this.specialPromotion) {
          student.selected = newValue;
        }
      });
    },
    
    async promoteStudents() {
      if (!this.canPromote) return;
      
      const selectedStudentIds = this.students
        .filter(student => student.selected)
        .map(student => student.id);
      
      try {
        const response = await axios.post('/employee/promotion/promote-students', {
          current_class_id: this.selectedClass,
          next_class_id: this.nextClass,
          student_ids: selectedStudentIds,
          special_promotion: this.specialPromotion,
          reason: this.promotionReason
        });
        
        this.promotionResult = response.data;
        this.promotionModal.show();
        
      } catch (error) {
        console.error('Error promoting students:', error);
        const errorMessage = error.response?.data?.error || 'Failed to promote students';
        this.$toast.error(errorMessage);
      }
    },
    
    refreshAfterPromotion() {
      this.loadClassStudents();
      this.loadPromotionStats();
      this.promotionResult = null;
      this.promotionReason = '';
      this.specialPromotion = false;
    },
    
    async viewPromotionHistory() {
      if (!this.selectedClass) return;
      
      try {
        const response = await axios.get('/employee/promotion/promotion-history', {
          params: {
            class_id: this.selectedClass
          }
        });
        this.promotionHistory = response.data.promotions;
        this.promotionHistoryModal.show();
      } catch (error) {
        console.error('Error loading promotion history:', error);
        const errorMessage = error.response?.data?.error || 'Failed to load promotion history';
        this.$toast.error(errorMessage);
      }
    }
  }
};
</script>

<style scoped>
.card {
  background-color: #ffffff;
  border: 1px solid #dee2e6;
}

.card.border {
  border-color: #dee2e6 !important;
}

.badge.border {
  background: white;
  border: 1px solid #dee2e6 !important;
  color: #212529;
}

.table td {
  vertical-align: middle;
}

.badge {
  font-size: 0.75em;
  font-weight: 500;
}

.avatar {
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.avatar-lg {
  width: 48px;
  height: 48px;
}

.avatar-initial {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
}

.bg-label-primary { background-color: #e7f1ff; color: #0d6efd; }
.bg-label-success { background-color: #e7f9f0; color: #198754; }
.bg-label-warning { background-color: #fff3cd; color: #ffc107; }
.bg-label-info { background-color: #e7f6f8; color: #0dcaf0; }

.table-responsive {
  max-height: 500px;
  overflow-y: auto;
}

.modal-body .row {
  margin-bottom: 1rem;
}

.alert.bg-light {
  background-color: #f8f9fa !important;
  border-color: #dee2e6 !important;
}

.bg-light {
  background-color: #f8f9fa !important;
}

.border {
  border-color: #dee2e6 !important;
}

.btn-outline-primary.border:hover {
  background-color: #0d6efd;
  border-color: #0d6efd;
  color: white;
}

.btn-outline-info.border:hover {
  background-color: #0dcaf0;
  border-color: #0dcaf0;
  color: white;
}

.table-success {
  background-color: rgba(25, 135, 84, 0.1) !important;
}

.table-warning {
  background-color: rgba(255, 193, 7, 0.1) !important;
}

.modal-xl {
  max-width: 1200px;
}

.form-check-input:checked {
  background-color: #198754;
  border-color: #198754;
}
</style>