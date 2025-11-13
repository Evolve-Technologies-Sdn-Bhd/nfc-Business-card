// composables/useChatbot.js
export const useChatbot = () => {
  const { $api } = useNuxtApp()

  /**
   * Ask the chatbot a question
   */
  const askQuestion = async (question) => {
    try {
      const response = await $api.post('/chatbot/ask', { question })
      
      if (response.data.success && response.data.found) {
        return {
          success: true,
          found: true,
          answer: response.data.data.answer,
          questionId: response.data.data.id,
          question: response.data.data.question
        }
      }
      
      return {
        success: true,
        found: false,
        message: response.data.message
      }
    } catch (error) {
      console.error('Error asking chatbot:', error)
      return {
        success: false,
        error: error.message
      }
    }
  }

  /**
   * Submit feedback
   */
  const submitFeedback = async (feedbackData) => {
    try {
      const payload = {
        question_id: feedbackData.questionId || null,
        user_question: feedbackData.userQuestion || '',
        user_message: feedbackData.userMessage || '',
        user_name: feedbackData.userName || '',
        user_email: feedbackData.userEmail || '',
        rating: feedbackData.rating || 1,
        feedback_type: mapFeedbackType(feedbackData.feedbackType)
      }

      const response = await $api.post('/chatbot/feedback', payload)
      
      return {
        success: response.data.success,
        message: response.data.message
      }
    } catch (error) {
      console.error('Error submitting feedback:', error)
      return {
        success: false,
        error: error.message
      }
    }
  }

  /**
   * Get all public questions (for FAQ page)
   */
  const getPublicQuestions = async () => {
    try {
      const response = await $api.get('/chatbot/questions')
      
      if (response.data.success) {
        return {
          success: true,
          questions: response.data.data
        }
      }
      
      return {
        success: false,
        questions: []
      }
    } catch (error) {
      console.error('Error getting questions:', error)
      return {
        success: false,
        questions: [],
        error: error.message
      }
    }
  }

  /**
   * Map frontend feedback type to backend format
   */
  const mapFeedbackType = (type) => {
    const typeMap = {
      'no-answer': 'not_found',
      'not-helpful': 'rating',
      'helpful': 'rating',
      'general': 'general'
    }
    
    return typeMap[type] || 'general'
  }

  return {
    askQuestion,
    submitFeedback,
    getPublicQuestions
  }
}
