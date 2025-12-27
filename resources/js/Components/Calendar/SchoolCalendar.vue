<template>
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <button v-if="canManage" @click="openCreateModal" class="btn btn-primary btn-block w-100 mb-3">
                        <i class="bx bx-plus me-1"></i> Add Event
                    </button>

                    <div class="mb-3">
                        <label class="form-label">Filter by Type</label>
                        <div class="form-check mb-2" v-for="type in eventTypes" :key="type.value">
                            <input class="form-check-input" type="checkbox" :value="type.value" v-model="selectedTypes" @change="refetchEvents">
                            <label class="form-check-label d-flex align-items-center">
                                <span class="badge me-2" :style="{ backgroundColor: type.color }">&nbsp;</span>
                                {{ type.label }}
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-body">
                    <FullCalendar ref="fullCalendar" :options="calendarOptions" />
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Event Modal -->
    <div class="modal fade" id="eventModal" tabindex="-1" aria-hidden="true" ref="eventModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ isEditing ? 'Edit Event' : 'Add New Event' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form @submit.prevent="submitEvent">
                    <div class="modal-body">
                        <!-- Read-only view for non-managers -->
                        <div v-if="!canManage && isEditing">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Event Title</label>
                                <p>{{ form.title }}</p>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">From</label>
                                    <p>{{ formatDisplayDate(form.start_date) }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">To</label>
                                    <p>{{ formatDisplayDate(form.end_date) }}</p>
                                </div>
                            </div>
                             <div class="mb-3">
                                <label class="form-label fw-bold">Type</label>
                                <p>
                                    <span class="badge me-2" :style="{ backgroundColor: getEventTypeColor(form.event_type) }">&nbsp;</span>
                                    {{ getEventTypeLabel(form.event_type) }}
                                </p>
                            </div>
                            <div class="mb-3" v-if="form.location">
                                <label class="form-label fw-bold">Location</label>
                                <p>{{ form.location }}</p>
                            </div>
                            <div class="mb-3" v-if="form.description">
                                <label class="form-label fw-bold">Description</label>
                                <p>{{ form.description }}</p>
                            </div>
                        </div>

                        <!-- Editable Form for Managers -->
                        <div v-else>
                            <div class="mb-3">
                                <label class="form-label required">Event Title</label>
                                <input type="text" class="form-control" v-model="form.title" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">From</label>
                                    <input type="datetime-local" class="form-control" v-model="form.start_date" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">To</label>
                                    <input type="datetime-local" class="form-control" v-model="form.end_date" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" v-model="form.all_day" id="allDayCheck">
                                    <label class="form-check-label" for="allDayCheck">All Day Event</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Event Type</label>
                                <select class="form-select" v-model="form.event_type" required>
                                    <option v-for="type in eventTypes" :key="type.value" :value="type.value">{{ type.label }}</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label required">Target Audience</label>
                                <select class="form-select" v-model="form.target_audience" required>
                                    <option value="all">All (Everyone)</option>
                                    <option value="students">Students Only</option>
                                    <option value="teachers">Teachers Only</option>
                                    <option value="parents">Parents Only</option>
                                    <option value="specific_class">Specific Class</option>
                                </select>
                            </div>

                            <div class="mb-3" v-if="form.target_audience === 'specific_class'">
                                <label class="form-label required">Class</label>
                                <select class="form-select" v-model="form.rank_id" required>
                                    <option v-for="cls in classes" :key="cls.id" :value="cls.id">{{ cls.name }}</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" v-model="form.location" placeholder="e.g. Main Hall">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" v-model="form.description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                        <button v-if="canManage && isEditing" type="button" class="btn btn-danger me-auto" @click="deleteEvent">Delete</button>
                        <button v-if="canManage" type="submit" class="btn btn-primary">Save Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import { Modal } from 'bootstrap';

const props = defineProps({
    classes: {
        type: Array,
        default: () => []
    },
    fetchUrl: {
        type: String,
        required: true
    },
    canManage: {
        type: Boolean,
        default: false
    },
    // Route names for management actions
    routes: {
        type: Object,
        default: () => ({
            store: 'admin.calendar.events.store',
            update: 'admin.calendar.events.update',
            destroy: 'admin.calendar.events.destroy'
        })
    }
});

const fullCalendar = ref(null);
const eventModal = ref(null);
let modalInstance = null;
const isEditing = ref(false);
const selectedEventId = ref(null);

const eventTypes = [
    { value: 'holiday', label: 'Holiday', color: '#FF5B5C' },
    { value: 'exam', label: 'Exam', color: '#5A8DEE' },
    { value: 'meeting', label: 'Meeting', color: '#39DA8A' },
    { value: 'sports', label: 'Sports', color: '#FDAC41' },
    { value: 'academic', label: 'Academic', color: '#696CFF' },
    { value: 'other', label: 'Other', color: '#82868B' },
];

const selectedTypes = ref(eventTypes.map(t => t.value));

const form = useForm({
    title: '',
    description: '',
    event_type: 'other',
    start_date: '',
    end_date: '',
    all_day: false,
    location: '',
    target_audience: 'all',
    rank_id: null,
});

const calendarOptions = reactive({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    editable: props.canManage, // Only editable if canManage is true
    selectable: props.canManage, // Only selectable if canManage is true (for creating)
    selectMirror: true,
    dayMaxEvents: true,
    weekends: true,
    events: {
        url: props.fetchUrl,
        method: 'GET',
        failure: function() {
            alert('there was an error while fetching events!');
        },
    },
    select: handleDateSelect,
    eventClick: handleEventClick,
    eventDrop: handleEventDrop,
    eventResize: handleEventDrop,
});

onMounted(() => {
    modalInstance = new Modal(eventModal.value);
    eventModal.value.addEventListener('hidden.bs.modal', resetForm);
});

function refetchEvents() {
    fullCalendar.value.getApi().refetchEvents();
}

function openCreateModal() {
    if (!props.canManage) return;
    resetForm();
    modalInstance.show();
}

function handleDateSelect(selectInfo) {
    if (!props.canManage) return;
    resetForm();
    form.start_date = selectInfo.startStr + (selectInfo.allDay ? 'T09:00' : '');
    form.end_date = selectInfo.endStr + (selectInfo.allDay ? 'T10:00' : '');
    form.all_day = selectInfo.allDay;
    
    isEditing.value = false;
    modalInstance.show();
}

function handleEventClick(clickInfo) {
    const event = clickInfo.event;
    
    selectedEventId.value = event.id;
    isEditing.value = true;
    
    form.title = event.title;
    form.start_date = formatDateForInput(event.start);
    form.end_date = event.end ? formatDateForInput(event.end) : formatDateForInput(event.start);
    form.all_day = event.allDay;
    
    // Extended props
    const propsData = event.extendedProps;
    form.description = propsData.description;
    form.event_type = propsData.event_type;
    form.location = propsData.location;
    form.target_audience = propsData.target_audience;
    form.rank_id = propsData.rank_id;
    
    modalInstance.show();
}

function handleEventDrop(info) {
    if (!props.canManage) {
        info.revert();
        return;
    }

    if (!confirm("Are you sure about this change?")) {
        info.revert();
        return;
    }

    const event = info.event;
    
    router.put(route(props.routes.update, event.id), {
        title: event.title,
        start_date: formatDateForInput(event.start),
        end_date: event.end ? formatDateForInput(event.end) : formatDateForInput(event.start),
        all_day: event.allDay,
        event_type: event.extendedProps.event_type,
        target_audience: event.extendedProps.target_audience,
        description: event.extendedProps.description,
        location: event.extendedProps.location,
        rank_id: event.extendedProps.rank_id,
        color: event.backgroundColor
    }, {
        preserveScroll: true,
        onSuccess: () => {
             // success
        },
        onError: () => {
            info.revert();
        }
    }); 
}

function submitEvent() {
    if (!props.canManage) return;

    if (isEditing.value) {
        form.put(route(props.routes.update, selectedEventId.value), {
            onSuccess: () => {
                modalInstance.hide();
                refetchEvents();
            }
        });
    } else {
        form.post(route(props.routes.store), {
            onSuccess: () => {
                modalInstance.hide();
                refetchEvents();
                resetForm();
            }
        });
    }
}

function deleteEvent() {
    if (!props.canManage) return;

    if (confirm('Are you sure you want to delete this event?')) {
        router.delete(route(props.routes.destroy, selectedEventId.value), {
            onSuccess: () => {
                modalInstance.hide();
                refetchEvents();
            }
        });
    }
}

function resetForm() {
    form.reset();
    form.clearErrors();
    isEditing.value = false;
    selectedEventId.value = null;
}

function formatDateForInput(date) {
    if (!date) return '';
    const d = new Date(date);
    const offset = d.getTimezoneOffset() * 60000;
    const localISOTime = (new Date(d - offset)).toISOString().slice(0, 16);
    return localISOTime;
}

function formatDisplayDate(date) {
    if (!date) return '';
    return new Date(date).toLocaleString();
}

function getEventTypeColor(type) {
    const found = eventTypes.find(t => t.value === type);
    return found ? found.color : '#82868B';
}

function getEventTypeLabel(type) {
    const found = eventTypes.find(t => t.value === type);
    return found ? found.label : type;
}
</script>

<style scoped>
.fc-event {
    cursor: pointer;
}
.badge {
    display: inline-block;
    width: 12px;
    height: 12px;
    border-radius: 50%;
}
</style>
