<script setup>
import { computed } from 'vue';

const props = defineProps({
    userName: {
        type: String,
        required: true
    },
    userType: {
        type: String,
        required: true,
        validator: (value) => ['student', 'employee', 'admin'].includes(value)
    }
});

// Get time-based greeting
const greeting = computed(() => {
    const hour = new Date().getHours();
    
    if (hour >= 5 && hour < 12) {
        return 'Good Morning';
    } else if (hour >= 12 && hour < 17) {
        return 'Good Afternoon';
    } else {
        return 'Good Evening';
    }
});

// Get welcome message based on user type
const welcomeMessage = computed(() => {
    const messages = {
        student: "Welcome back! Ready to learn something new today?",
        employee: "Welcome back! Let's make today productive.",
        admin: "Welcome back! Your school management dashboard is ready."
    };
    
    return messages[props.userType] || "Welcome back!";
});

// Get icon based on time
const greetingIcon = computed(() => {
    const hour = new Date().getHours();
    
    if (hour >= 5 && hour < 12) {
        return '☀️'; // Morning
    } else if (hour >= 12 && hour < 17) {
        return '🌤️'; // Afternoon
    } else {
        return '🌙'; // Evening
    }
});
</script>

<template>
    <div class="greeting-card">
        <div class="greeting-content">
            <div class="greeting-icon">{{ greetingIcon }}</div>
            <div class="greeting-text">
                <h2 class="greeting-title">{{ greeting }}, {{ userName }}!</h2>
                <p class="greeting-message">{{ welcomeMessage }}</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.greeting-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    animation: slideIn 0.5s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.greeting-content {
    display: flex;
    align-items: center;
    gap: 16px;
}

.greeting-icon {
    font-size: 48px;
    line-height: 1;
}

.greeting-text {
    flex: 1;
}

.greeting-title {
    color: #1f2937;
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 8px 0;
}

.greeting-message {
    color: #6b7280;
    font-size: 16px;
    margin: 0;
}

@media (max-width: 768px) {
    .greeting-card {
        padding: 16px;
    }
    
    .greeting-icon {
        font-size: 36px;
    }
    
    .greeting-title {
        font-size: 20px;
    }
    
    .greeting-message {
        font-size: 14px;
    }
}
</style>
