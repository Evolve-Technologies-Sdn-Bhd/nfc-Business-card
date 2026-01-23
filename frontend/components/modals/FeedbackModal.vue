<template>
  <Teleport to="body">
    <div 
      v-if="show"
      class="feedback-modal-overlay" 
      :class="{ active: show }"
      @click.self="closeModal"
    >
      <div class="feedback-modal">
        <!-- Close Button -->
        <button class="modal-close-btn" @click="closeModal" aria-label="Close">
            <Icon name="heroicons:x-mark" class="h-6 w-6" />
        </button>
        
        <!-- SUCCESS STATE -->
        <div v-if="success" class="modal-body success-state">
            <div class="success-icon-wrapper">
                <div class="checkmark-circle">
                    <div class="checkmark draw"></div>
                </div>
            </div>
            <h3 class="success-title">Thank you!</h3>
            <p class="success-subtitle">
                Your feedback has been received and sent to our team.
            </p>
        </div>

        <!-- FORM STATE -->
        <template v-else>
            <div class="modal-header">
            <h2>We'd love your feedback!</h2>
            <p>Help us improve your experience</p>
            </div>
            
            <div class="modal-body">
            <!-- Feedback form content -->
            <div class="rating-section">
                <label>How would you rate us?</label>
                <div class="star-rating">
                    <button
                        v-for="star in 5"
                        :key="star"
                        @click="form.rating = star"
                        @mouseenter="hoverRating = star"
                        @mouseleave="hoverRating = 0"
                        class="star-btn"
                        :class="{ 'active': (hoverRating || form.rating) >= star }"
                        type="button"
                    >
                        ★
                    </button>
                </div>
            </div>
            
            <div class="category-section">
                <label>Category</label>
                <div class="custom-select-wrapper">
                    <select v-model="form.category">
                        <option value="suggestion">Suggestion</option>
                        <option value="bug">Bug</option>
                        <option value="praise">Praise</option>
                        <option value="complaint">Complaint</option>
                        <option value="other">Other</option>
                    </select>
                    <Icon name="heroicons:chevron-down" class="select-arrow" />
                </div>
            </div>
            
            <div class="message-section">
                <label>Your Feedback *</label>
                <textarea 
                    v-model="form.message" 
                    placeholder="Tell us what's on your mind..."
                    maxlength="500"
                ></textarea>
                <span class="char-count">{{ form.message.length }}/500 characters</span>
            </div>
            
            <div class="optional-fields-toggle">
                <a href="#" @click.prevent="showContactInfo = !showContactInfo">
                    {{ showContactInfo ? '- Hide contact info' : '+ Add contact info (optional)' }}
                </a>
            </div>
            
            <div v-show="showContactInfo" class="contact-info">
                <input v-model="form.name" type="text" placeholder="Your name (optional)" />
                <input v-model="form.email" type="email" placeholder="Your email (optional)" />
            </div>
            </div>
            
            <div class="modal-footer">
            <button class="btn-cancel" @click="closeModal">
                Cancel
            </button>
            <button class="btn-submit" @click="submitFeedback" :disabled="submitting || !form.message.trim()">
                <span v-if="submitting">Sending...</span>
                <span v-else>Send Feedback</span>
            </button>
            </div>
        </template>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  initialMessage: {
    type: String,
    default: ''
  },
  initialCategory: {
    type: String,
    default: 'suggestion'
  },
  contextData: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['update:show']);

// State
const submitting = ref(false);
const success = ref(false);
const hoverRating = ref(0);
const showContactInfo = ref(false);

const form = reactive({
  rating: 0,
  category: 'suggestion',
  message: '',
  name: '',
  email: ''
});

// Watch for modal opening to reset/init
watch(() => props.show, (newVal) => {
    if (newVal) {
        resetForm();
    }
});

function resetForm() {
    success.value = false;
    submitting.value = false;
    hoverRating.value = 0;
    showContactInfo.value = false;
    
    form.rating = 0;
    form.category = props.initialCategory || 'suggestion';
    form.message = props.initialMessage || '';
    form.name = '';
    form.email = '';
}

function closeModal() {
  emit('update:show', false);
}

// Close with ESC key
if (process.client) {
    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && props.show) {
            closeModal();
        }
    });
}

async function submitFeedback() {
    if (!form.message.trim()) return;
    
    submitting.value = true;
    const { $api } = useNuxtApp();
    
    try {
        const payload = {
            category: form.category,
            message: form.message,
            rating: form.rating > 0 ? form.rating : null,
            user_name: form.name || null,
            user_email: form.email || null,
            timestamp: new Date().toISOString(),
            ...props.contextData
        };

        const response = await $api.post('/chatbot/feedback', payload);
        
        if (response.success) {
            success.value = true;
            setTimeout(() => {
                closeModal();
            }, 2500);
        } else {
            console.error('Feedback submission failed');
        }
    } catch (e) {
        console.error('Error submitting feedback', e);
    } finally {
        submitting.value = false;
    }
}
</script>

<style>
/* Dark overlay that covers entire screen */
.feedback-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  z-index: 99999; /* Higher than chatbot */
  display: flex;
  align-items: center;
  justify-content: center;
  animation: fadeIn 0.3s ease-out;
  backdrop-filter: blur(2px);
}

/* The actual modal box */
.feedback-modal {
  background: white;
  border-radius: 20px;
  max-width: 520px;
  width: 90%;
  max-height: 85vh;
  overflow-y: auto;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
  position: relative;
  animation: slideUp 0.3s ease-out;
  font-family: 'Inter', sans-serif;
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Close button (X) */
.modal-close-btn {
  position: absolute;
  top: 20px;
  right: 20px;
  background: #f0f0f0;
  border: none;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  color: #666;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s;
  z-index: 10;
}

.modal-close-btn:hover {
  background: #e0e0e0;
  color: #333;
  transform: rotate(90deg);
}

/* Modal sections */
.modal-header {
  padding: 40px 30px 20px;
  border-bottom: 1px solid #f0f0f0;
  text-align: center;
}

.modal-header h2 {
  font-size: 26px;
  font-weight: 700;
  color: #1a1a1a;
  margin-bottom: 8px;
  margin-top: 0;
}

.modal-header p {
  font-size: 15px;
  color: #666;
  margin: 0;
}

.modal-body {
  padding: 30px;
}

.modal-footer {
  padding: 20px 30px 30px;
  display: flex;
  gap: 12px;
  background: #f9fafb;
  border-radius: 0 0 20px 20px;
}

/* Form sections */
.rating-section,
.category-section,
.message-section {
  margin-bottom: 24px;
}

.rating-section label,
.category-section label,
.message-section label {
  display: block;
  font-size: 14px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 10px;
}

.star-rating {
  display: flex;
  gap: 8px;
  justify-content: center;
}

.star-btn {
  background: none;
  border: none;
  font-size: 32px;
  color: #d1d5db;
  cursor: pointer;
  transition: all 0.2s;
  line-height: 1;
  padding: 0 4px;
}

.star-btn.active {
  color: #fbbf24; /* Amber-400 */
  transform: scale(1.1);
}

.custom-select-wrapper {
    position: relative;
}

select {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  font-size: 15px;
  background: white;
  cursor: pointer;
  transition: all 0.2s;
  appearance: none;
  color: #374151;
}

.select-arrow {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #6b7280;
    width: 20px;
    height: 20px;
}

select:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}

textarea {
  width: 100%;
  min-height: 120px;
  padding: 14px 16px;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  font-size: 15px;
  font-family: inherit;
  resize: vertical;
  transition: all 0.2s;
  color: #374151;
}

textarea:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
}

.char-count {
  font-size: 12px;
  color: #9ca3af;
  margin-top: 6px;
  display: block;
  text-align: right;
}

.optional-fields-toggle a {
    color: #6366f1;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
}

.contact-info {
    margin-top: 15px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    animation: fadeIn 0.3s ease;
}

.contact-info input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    font-size: 14px;
}

.contact-info input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

/* Buttons */
.btn-cancel,
.btn-submit {
  flex: 1;
  padding: 14px 24px;
  border: none;
  border-radius: 12px;
  font-size: 15px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-cancel {
  background: white;
  color: #6b7280;
  border: 1px solid #e5e7eb;
}

.btn-cancel:hover {
  background: #f3f4f6;
  color: #374151;
}

.btn-submit {
  background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
  color: white;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(99, 102, 241, 0.3);
}

.btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Success State */
.success-state {
    text-align: center;
    padding: 60px 30px;
}

.success-icon-wrapper {
    margin-bottom: 20px;
}

.checkmark-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #ecfdf5;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
}

.checkmark.draw:after {
    content: "✓";
    font-size: 40px;
    color: #10b981;
}

.success-title {
    color: #10b981;
    font-size: 24px;
    margin-bottom: 10px;
    font-weight: 700;
}

.success-subtitle {
    color: #6b7280;
    font-size: 16px;
}

/* Mobile responsive */
@media (max-width: 768px) {
  .feedback-modal {
    max-width: 95%;
    margin: 20px;
    height: auto;
    max-height: 90vh;
  }
  
  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 20px;
  }
}
</style>
