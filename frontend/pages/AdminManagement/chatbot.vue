<!-- pages/AdminManagement/chatbot.vue -->
<template>
  <div>
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-secondary-900">Chatbot Management</h1>
      <p class="mt-2 text-secondary-600">
        Manage FAQ questions, answers, and user feedback
      </p>
    </div>

    <!-- Tabs -->
    <div class="mb-6">
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors',
              activeTab === tab.id
                ? 'border-primary-500 text-primary-600'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            {{ tab.name }}
            <span
              v-if="tab.badge"
              :class="[
                'ml-2 py-0.5 px-2 rounded-full text-xs',
                activeTab === tab.id
                  ? 'bg-primary-100 text-primary-600'
                  : 'bg-gray-100 text-gray-600'
              ]"
            >
              {{ tab.badge }}
            </span>
          </button>
        </nav>
      </div>
    </div>

    <!-- Questions & Answers Tab -->
    <div v-show="activeTab === 'questions'">
      <div class="mb-6 flex justify-between items-center">
        <div class="flex-1 max-w-md">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search questions..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
          />
        </div>
        <button
          @click="openQuestionModal()"
          class="btn-primary flex items-center space-x-2"
        >
          <Icon name="heroicons:plus" class="h-5 w-5" />
          <span>Add Question</span>
        </button>
      </div>

      <!-- Questions List -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="spinner"></div>
      </div>

      <div v-else-if="filteredQuestions.length" class="space-y-4">
        <div
          v-for="question in filteredQuestions"
          :key="question.id"
          class="card p-6"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center space-x-3 mb-2">
                <h3 class="text-lg font-semibold text-gray-900">
                  {{ question.question }}
                </h3>
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    question.is_active
                      ? 'bg-green-100 text-green-800'
                      : 'bg-gray-100 text-gray-800'
                  ]"
                >
                  {{ question.is_active ? 'Active' : 'Inactive' }}
                </span>
              </div>

              <p class="text-gray-600 mb-4 whitespace-pre-wrap">{{ question.answer }}</p>

              <div class="flex items-center space-x-4 text-sm text-gray-500">
                <div class="flex items-center space-x-1">
                  <Icon name="heroicons:eye" class="h-4 w-4" />
                  <span>{{ question.view_count }} views</span>
                </div>
                <div class="flex items-center space-x-1">
                  <Icon name="heroicons:hand-thumb-up" class="h-4 w-4 text-green-600" />
                  <span>{{ question.helpful_count }}</span>
                </div>
                <div class="flex items-center space-x-1">
                  <Icon name="heroicons:hand-thumb-down" class="h-4 w-4 text-red-600" />
                  <span>{{ question.not_helpful_count }}</span>
                </div>
                <div
                  v-if="question.helpful_count + question.not_helpful_count > 0"
                  class="flex items-center space-x-1"
                >
                  <Icon name="heroicons:chart-bar" class="h-4 w-4" />
                  <span>{{ question.helpfulness }}% helpful</span>
                </div>
              </div>

              <div class="mt-3 flex flex-wrap gap-2">
                <span
                  v-for="keyword in question.keywords"
                  :key="keyword"
                  class="px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded-md"
                >
                  {{ keyword }}
                </span>
              </div>
            </div>

            <div class="flex items-center space-x-2 ml-4">
              <button
                @click="openQuestionModal(question)"
                class="p-2 text-gray-400 hover:text-primary-600 rounded-lg hover:bg-gray-100"
                title="Edit"
              >
                <Icon name="heroicons:pencil" class="h-5 w-5" />
              </button>
              <button
                @click="deleteQuestion(question.id)"
                class="p-2 text-gray-400 hover:text-red-600 rounded-lg hover:bg-gray-100"
                title="Delete"
              >
                <Icon name="heroicons:trash" class="h-5 w-5" />
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-12">
        <Icon name="heroicons:question-mark-circle" class="h-16 w-16 text-gray-300 mx-auto mb-4" />
        <p class="text-gray-500">No questions found</p>
      </div>
    </div>

    <!-- Feedback Tab -->
    <div v-show="activeTab === 'feedback'">
      <div class="mb-6 flex items-center space-x-4">
        <select
          v-model="feedbackFilter"
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
        >
          <option value="all">All Feedback</option>
          <option value="unread">Unread</option>
          <option value="not_found">No Answer Found</option>
          <option value="rating">Ratings</option>
        </select>

        <button
          v-if="feedbackList.filter(f => !f.is_read).length > 0"
          @click="markAllAsRead"
          class="btn-secondary"
        >
          Mark All as Read
        </button>
      </div>

      <!-- Feedback List -->
      <div v-if="loadingFeedback" class="flex justify-center py-12">
        <div class="spinner"></div>
      </div>

      <div v-else-if="filteredFeedback.length" class="space-y-4">
        <div
          v-for="feedback in filteredFeedback"
          :key="feedback.id"
          :class="[
            'card p-6',
            !feedback.is_read ? 'border-l-4 border-primary-500' : ''
          ]"
        >
          <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
              <div class="flex items-center space-x-2 mb-2">
                <span
                  :class="[
                    'px-2 py-1 text-xs font-medium rounded-full',
                    getFeedbackTypeBadge(feedback.feedback_type)
                  ]"
                >
                  {{ feedback.feedback_type.replace('_', ' ').toUpperCase() }}
                </span>
                <span v-if="!feedback.is_read" class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">
                  NEW
                </span>
                <span v-if="feedback.rating" class="flex items-center space-x-1">
                  <Icon name="heroicons:star-solid" class="h-4 w-4 text-yellow-500" />
                  <span class="text-sm font-medium">{{ feedback.rating }}/5</span>
                </span>
              </div>

              <p class="text-sm text-gray-500 mb-2">
                <strong>User Question:</strong> {{ feedback.user_question }}
              </p>

              <p v-if="feedback.question_text" class="text-sm text-gray-500 mb-2">
                <strong>Matched Question:</strong> {{ feedback.question_text }}
              </p>

              <p v-if="feedback.user_message" class="text-gray-900 mb-3">
                {{ feedback.user_message }}
              </p>

              <div class="flex items-center space-x-4 text-xs text-gray-400">
                <span v-if="feedback.user_email">{{ feedback.user_email }}</span>
                <span>{{ formatDate(feedback.created_at) }}</span>
              </div>
            </div>

            <button
              v-if="!feedback.is_read"
              @click="markFeedbackAsRead(feedback.id)"
              class="btn-secondary-sm"
            >
              Mark as Read
            </button>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-12">
        <Icon name="heroicons:chat-bubble-left-right" class="h-16 w-16 text-gray-300 mx-auto mb-4" />
        <p class="text-gray-500">No feedback found</p>
      </div>
    </div>

    <!-- Question Modal -->
    <div
      v-if="showQuestionModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
      @click.self="closeQuestionModal"
    >
      <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-2xl font-bold text-gray-900">
            {{ editingQuestion ? 'Edit Question' : 'Add New Question' }}
          </h2>
        </div>

        <form @submit.prevent="saveQuestion" class="p-6 space-y-6">
          <!-- Question -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Question <span class="text-red-500">*</span>
            </label>
            <input
              v-model="questionForm.question"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              placeholder="What is your question?"
            />
          </div>

          <!-- Answer -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Answer <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="questionForm.answer"
              rows="6"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              placeholder="Provide a detailed answer..."
            ></textarea>
          </div>

          <!-- Keywords -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Keywords <span class="text-red-500">*</span>
            </label>
            <div class="flex space-x-2 mb-2">
              <input
                v-model="newKeyword"
                type="text"
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                placeholder="Add a keyword..."
                @keyup.enter.prevent="addKeyword"
              />
              <button
                type="button"
                @click="addKeyword"
                class="btn-secondary"
              >
                Add
              </button>
            </div>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="(keyword, index) in questionForm.keywords"
                :key="index"
                class="px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full flex items-center space-x-2"
              >
                <span>{{ keyword }}</span>
                <button
                  type="button"
                  @click="removeKeyword(index)"
                  class="text-blue-600 hover:text-blue-800"
                >
                  <Icon name="heroicons:x-mark" class="h-4 w-4" />
                </button>
              </span>
            </div>
            <p class="text-xs text-gray-500 mt-1">
              Keywords help match user questions to answers. Add relevant terms and synonyms.
            </p>
          </div>

          <!-- Priority & Status -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Priority
              </label>
              <input
                v-model.number="questionForm.priority"
                type="number"
                min="0"
                max="100"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              />
              <p class="text-xs text-gray-500 mt-1">Higher priority = shown first (0-100)</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Status
              </label>
              <label class="flex items-center space-x-2 cursor-pointer">
                <input
                  v-model="questionForm.is_active"
                  type="checkbox"
                  class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                />
                <span class="text-sm text-gray-700">Active (visible to users)</span>
              </label>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
            <button
              type="button"
              @click="closeQuestionModal"
              class="btn-secondary"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="btn-primary"
            >
              {{ saving ? 'Saving...' : (editingQuestion ? 'Update' : 'Create') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

definePageMeta({
  middleware: ['auth', 'admin'],
  layout: 'admin-management'
})

const { $api } = useNuxtApp()

// State
const activeTab = ref('questions')
const loading = ref(false)
const loadingFeedback = ref(false)
const saving = ref(false)
const searchQuery = ref('')
const feedbackFilter = ref('all')

const questions = ref([])
const feedbackList = ref([])

const showQuestionModal = ref(false)
const editingQuestion = ref(null)
const newKeyword = ref('')

const questionForm = ref({
  question: '',
  answer: '',
  keywords: [],
  priority: 50,
  is_active: true
})

// Computed
const tabs = computed(() => [
  {
    id: 'questions',
    name: 'Questions & Answers',
    badge: questions.value.length
  },
  {
    id: 'feedback',
    name: 'User Feedback',
    badge: feedbackList.value.filter(f => !f.is_read).length || null
  }
])

const filteredQuestions = computed(() => {
  if (!searchQuery.value) return questions.value

  const query = searchQuery.value.toLowerCase()
  return questions.value.filter(q =>
    q.question.toLowerCase().includes(query) ||
    q.answer.toLowerCase().includes(query) ||
    q.keywords.some(k => k.toLowerCase().includes(query))
  )
})

const filteredFeedback = computed(() => {
  let filtered = feedbackList.value

  if (feedbackFilter.value === 'unread') {
    filtered = filtered.filter(f => !f.is_read)
  } else if (feedbackFilter.value !== 'all') {
    filtered = filtered.filter(f => f.feedback_type === feedbackFilter.value)
  }

  return filtered
})

// Methods
const fetchQuestions = async () => {
  loading.value = true
  try {
    const response = await $api.get('/admin/chatbot/questions')
    if (response.data.success) {
      questions.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching questions:', error)
  } finally {
    loading.value = false
  }
}

const fetchFeedback = async () => {
  loadingFeedback.value = true
  try {
    const response = await $api.get('/admin/chatbot/feedback')
    if (response.data.success) {
      feedbackList.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching feedback:', error)
  } finally {
    loadingFeedback.value = false
  }
}

const openQuestionModal = (question = null) => {
  if (question) {
    editingQuestion.value = question
    questionForm.value = {
      question: question.question,
      answer: question.answer,
      keywords: [...question.keywords],
      priority: question.priority,
      is_active: question.is_active
    }
  } else {
    editingQuestion.value = null
    questionForm.value = {
      question: '',
      answer: '',
      keywords: [],
      priority: 50,
      is_active: true
    }
  }
  showQuestionModal.value = true
}

const closeQuestionModal = () => {
  showQuestionModal.value = false
  editingQuestion.value = null
  newKeyword.value = ''
}

const addKeyword = () => {
  const keyword = newKeyword.value.trim()
  if (keyword && !questionForm.value.keywords.includes(keyword)) {
    questionForm.value.keywords.push(keyword)
    newKeyword.value = ''
  }
}

const removeKeyword = (index) => {
  questionForm.value.keywords.splice(index, 1)
}

const saveQuestion = async () => {
  if (questionForm.value.keywords.length === 0) {
    alert('Please add at least one keyword')
    return
  }

  saving.value = true
  try {
    const payload = {
      question: questionForm.value.question,
      answer: questionForm.value.answer,
      keywords: questionForm.value.keywords,
      priority: questionForm.value.priority,
      is_active: questionForm.value.is_active
    }

    if (editingQuestion.value) {
      await $api.put(`/admin/chatbot/questions/${editingQuestion.value.id}`, payload)
    } else {
      await $api.post('/admin/chatbot/questions', payload)
    }

    closeQuestionModal()
    await fetchQuestions()
  } catch (error) {
    console.error('Error saving question:', error)
    alert('Failed to save question. Please try again.')
  } finally {
    saving.value = false
  }
}

const deleteQuestion = async (id) => {
  if (!confirm('Are you sure you want to delete this question?')) return

  try {
    await $api.delete(`/admin/chatbot/questions/${id}`)
    await fetchQuestions()
  } catch (error) {
    console.error('Error deleting question:', error)
    alert('Failed to delete question. Please try again.')
  }
}

const markFeedbackAsRead = async (id) => {
  try {
    await $api.put(`/admin/chatbot/feedback/${id}/read`)
    await fetchFeedback()
  } catch (error) {
    console.error('Error marking feedback as read:', error)
  }
}

const markAllAsRead = async () => {
  const unreadIds = feedbackList.value.filter(f => !f.is_read).map(f => f.id)
  for (const id of unreadIds) {
    await markFeedbackAsRead(id)
  }
}

const getFeedbackTypeBadge = (type) => {
  const badges = {
    not_found: 'bg-red-100 text-red-800',
    rating: 'bg-blue-100 text-blue-800',
    general: 'bg-gray-100 text-gray-800'
  }
  return badges[type] || badges.general
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Lifecycle
onMounted(() => {
  fetchQuestions()
  fetchFeedback()
})
</script>

<style scoped>
.spinner {
  border: 3px solid #f3f3f3;
  border-top: 3px solid #667eea;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
</style>
