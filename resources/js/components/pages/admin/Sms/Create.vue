<template>
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between bg-white rounded-3 p-4 shadow-sm">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-opacity-10  rounded-circle">
                        <i class='bx h bx-message-rounded-detail fs-24'></i>
                    </div>
                    <div>
                        <h4 class="h5 mb-1">Bulk Message Composer</h4>
                        <p class="text-muted mb-0">Send messages to multiple recipients</p>
                    </div>
                </div>
                <Link href="/admin/sms/outbox" class="btn btn-outline-secondary">
                    <i class="bi bi-clock-history me-2"></i>View History
                </Link>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row g-4">
        <!-- Left Sidebar -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Contact Groups -->
                    <div class="mb-4">
                        <label class="form-label d-flex align-items-center">
                            <i class="bi bi-people me-2"></i>Contact Group
                        </label>
                        <select v-model="selectedGroup" class="form-select">
                            <option value="">Select a group</option>
                            <option v-for="group in groups" :key="group.id" :value="group.name">
                                {{ group.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Templates -->
                    <!-- <div class="mb-4">
                        <label class="form-label d-flex align-items-center">
                            <i class="bi bi-file-text me-2"></i>Message Template
                        </label>
                        <select v-model="selectedTemplate" class="form-select">
                            <option value="">Select a template</option>
                            <option v-for="template in templates" :key="template.id" :value="template.id">
                                {{ template.name }}
                            </option>
                        </select>
                    </div> -->

                    <!-- Schedule -->
                    <div class="mb-4">
                        <label class="form-label d-flex align-items-center">
                            <i class="bi bi-calendar me-2"></i>Schedule Send
                        </label>
                        <input type="datetime-local" v-model="scheduleDate" class="form-control">
                    </div>

                    <!-- Options -->
                    <div class="border-top pt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">Include Salutation</span>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" v-model="includeSalutation">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Include Signature</span>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" v-model="includeSignature">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Recipients -->
                    <div class="mb-4">
                        <label class="form-label d-flex align-items-center">
                            <i class="bi bi-people me-2"></i>Recipients
                        </label>
                        <textarea  v-model="recipients" class="form-control" rows="2"
                            placeholder="Enter phone numbers separated by commas"></textarea>
                    </div>

                    <!-- Message Content -->
                    <div class="mb-4">
                        <label class="form-label">Message Content</label>
                        <textarea v-model="messageContent" class="form-control" rows="12"
                            placeholder="Type your message here..."></textarea>
                        <div class="form-text text-end">
                            {{ messageContent.length }} characters
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-2"></i>Includes mandatory opt-out option
                        </small>
                        <div class="d-flex gap-2">
                            <!-- <button @click="saveDraft" class="btn btn-outline-secondary">
                                <i class="bi bi-save me-2"></i>Save Draft
                            </button> -->
                            <button @click="sendMessages" class="btn btn-primary">
                                <i class="bi bi-send me-2"></i>Send Messages
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Link } from '@inertiajs/vue3';
export default {
    name: 'BulkMessageComposer',
    data() {
        return {
            recipients: '',
            messageContent: '',
            selectedGroup: '',
            selectedTemplate: '',
            scheduleDate: '',
            includeSalutation: false,
            includeSignature: false,
            groups: [
                { id: 1, name: 'All Contacts' },
                { id: 2, name: 'Parents' },
                { id: 3, name: 'Teachers' },
            ],
            templates: [
                { id: 1, name: 'Default Template' },
                { id: 2, name: 'Sales Follow-up' },
                { id: 3, name: 'Meeting Reminder' },
                { id: 4, name: 'Custom Template' }
            ]
        }
    },
    methods: {
        loading: false,
        status: {
            message: '',
            type: 'success'
        },
        validateForm() {
            if (!this.recipients.trim() && !this.selectedGroup.trim()) {
                this.status = {
                    message: 'Please enter at least one recipient',
                    type: 'danger'
                };
                console.log(this.selectedGroup.trim())
                return false;
            }

            if (!this.messageContent.trim()) {
                this.status = {
                    message: 'Please enter a message',
                    type: 'danger'
                };
                return false;
            }

            return true;
        },
        async saveDraft() {
      if (!this.validateForm()) return;

      try {
        this.loading = true;
        const response = await axios.post('/admin/sms/drafts', {
          recipients: this.recipients.split(',').map(r => r.trim()),
          content: this.messageContent,
          group: this.selectedGroup || null,
          template_id: this.selectedTemplate || null,
          schedule_date: this.scheduleDate || null,
          include_salutation: this.includeSalutation,
          include_signature: this.includeSignature
        });

        this.status = {
          message: 'Draft saved successfully',
          type: 'success'
        };
        this.draftId = response.data.id;

      } catch (error) {
        this.status = {
          message: error.response?.data?.message || 'Error saving draft',
          type: 'danger'
        };
      } finally {
        this.loading = false;
      }
    },

    async sendMessages() {
      if (!this.validateForm()) return;

      try {
        this.loading = true;
        const response = await axios.post('/admin/sms/send', {
          recipients: this.recipients.split(',').map(r => r.trim()),
          content: this.messageContent,
          group: this.selectedGroup || null,
          template_id: this.selectedTemplate || null,
          schedule_date: this.scheduleDate || null,
          include_salutation: this.includeSalutation,
          include_signature: this.includeSignature,
          campaign: {
            name: `Campaign ${new Date().toLocaleDateString()}`,
            description: 'Created from Bulk Message Composer'
          }
        });

        const messageCount = response.data.message_count;

        if (this.scheduleDate) {
          this.status = {
            message: `${messageCount} messages scheduled for sending`,
            type: 'success'
          };
        } else {
          this.status = {
            message: `${messageCount} messages queued for sending`,
            type: 'success'
          };
        }

        this.recipients = '';
        this.messageContent = '';
        this.scheduleDate = '';

      } catch (error) {
        const errorMessage = error.response?.data?.message || 'Error sending messages';

        if (error.response?.status === 422) {
          const errors = error.response.data.errors;
          const firstError = Object.values(errors)[0][0];
          this.status = {
            message: firstError,
            type: 'danger'
          };
        } else if (error.response?.status === 429) {
          // Rate limiting
          this.status = {
            message: 'Please wait before sending more messages',
            type: 'warning'
          };
        } else {
          this.status = {
            message: errorMessage,
            type: 'danger'
          };
        }
      } finally {
        this.loading = false;
      }
    }
  },
}
</script>

<style scoped>
.gap-2 {
    gap: 0.5rem;
}

.gap-3 {
    gap: 1rem;
}
</style>
