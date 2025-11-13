# AI Chatbot Widget - Documentation

## Overview

The NFC Business Card platform now includes a fully functional AI-powered chatbot widget that provides instant answers to user questions through a knowledge base managed by administrators.

## Features

### User-Facing Features

1. **Floating Chat Icon**
   - Small circular icon in the bottom-right corner
   - Always visible on all pages
   - Animated pulse effect for notifications
   - Click to expand chat panel

2. **Chat Interface**
   - Compact chat panel (350x500px)
   - Welcome message with quick questions
   - Real-time typing indicator
   - Message history within session
   - Smooth animations and transitions

3. **Smart Q&A System**
   - Keyword-based matching for questions
   - Instant answers from knowledge base
   - Fallback message when no answer is found
   - View-only for users (no editing)

4. **Feedback System**
   - Thumbs up/down for each answer
   - Star rating (1-5 stars)
   - Optional text feedback
   - Optional email for follow-up
   - Sends feedback to admin panel

### Admin Features

1. **Question Management**
   - Add, edit, and delete FAQ questions
   - Rich text answers with line breaks
   - Keyword-based matching system
   - Priority ordering (0-100)
   - Active/inactive status toggle

2. **Feedback Dashboard**
   - View all user feedback
   - Filter by read/unread status
   - Filter by feedback type
   - See matched questions
   - Mark feedback as read
   - Star ratings and user messages

3. **Analytics**
   - View count per question
   - Helpful/not helpful counts
   - Helpfulness percentage
   - Identify gaps in knowledge base

## File Structure

```
frontend/
├── components/
│   ├── ChatbotFloatingIcon.vue    # Floating chat button
│   └── ChatbotInterface.vue        # Chat panel UI
├── composables/
│   └── useChatbot.js              # API integration
├── pages/
│   └── AdminManagement/
│       └── chatbot.vue            # Admin management page
├── layouts/
│   └── AdminManagement.vue        # Admin layout (updated)
└── app.vue                        # Global chatbot integration

backend/
├── app/
│   ├── Models/
│   │   ├── ChatbotQuestion.php    # Q&A model
│   │   └── ChatbotFeedback.php    # Feedback model
│   └── Http/
│       └── Controllers/
│           └── Api/
│               ├── ChatbotController.php       # Public API
│               └── AdminChatbotController.php  # Admin API
├── database/
│   ├── migrations/
│   │   └── 2025_01_13_000001_create_chatbot_tables.php
│   └── seeders/
│       └── ChatbotQuestionSeeder.php
└── routes/
    └── api.php                    # API routes
```

## Installation & Setup

### 1. Database Migration

Run the migration to create the chatbot tables:

```bash
cd backend
php artisan migrate
```

### 2. Seed Sample Data (Optional)

Populate the database with sample FAQ questions:

```bash
php artisan db:seed --class=ChatbotQuestionSeeder
```

### 3. Frontend Dependencies

No additional dependencies required - uses existing Nuxt/Vue setup.

## API Endpoints

### Public Endpoints

#### Ask a Question
```
POST /api/chatbot/ask
Body: { "question": "What is NFC?" }
Response: {
  "success": true,
  "found": true,
  "data": {
    "id": 1,
    "question": "What is an NFC business card?",
    "answer": "An NFC business card is..."
  }
}
```

#### Submit Feedback
```
POST /api/chatbot/feedback
Body: {
  "question_id": 1,
  "user_question": "What is NFC?",
  "user_message": "Very helpful!",
  "user_email": "user@example.com",
  "rating": 5,
  "feedback_type": "rating"
}
```

#### Get Public Questions
```
GET /api/chatbot/questions
Response: {
  "success": true,
  "data": [...]
}
```

### Admin Endpoints (Require Authentication)

#### List All Questions
```
GET /api/admin/chatbot/questions
Query: ?search=nfc&is_active=true
```

#### Create Question
```
POST /api/admin/chatbot/questions
Body: {
  "question": "New question?",
  "answer": "Answer here...",
  "keywords": ["keyword1", "keyword2"],
  "priority": 50,
  "is_active": true
}
```

#### Update Question
```
PUT /api/admin/chatbot/questions/{id}
Body: { same as create }
```

#### Delete Question
```
DELETE /api/admin/chatbot/questions/{id}
```

#### Get Feedback
```
GET /api/admin/chatbot/feedback
Query: ?is_read=false&type=not_found
```

#### Mark Feedback as Read
```
PUT /api/admin/chatbot/feedback/{id}/read
```

## Usage Guide

### For End Users

1. **Opening the Chat**
   - Click the circular chat icon in the bottom-right corner
   - The chat panel will expand showing a welcome message

2. **Asking Questions**
   - Type your question in the input field
   - Press Enter or click the send button
   - The bot will search for a matching answer
   - Quick question buttons are available for common queries

3. **Providing Feedback**
   - Click 👍 Yes or 👎 No after receiving an answer
   - If you click No, a feedback form appears
   - Rate your experience (1-5 stars)
   - Optionally add comments and email
   - Submit to help improve the bot

### For Administrators

1. **Accessing the Admin Panel**
   - Log in as an admin
   - Navigate to "Chatbot & FAQ" in the admin sidebar

2. **Managing Questions**
   - Click "Add Question" to create new Q&A pairs
   - Fill in:
     - Question text
     - Answer (supports line breaks)
     - Keywords (for matching)
     - Priority (higher = shown first)
     - Active status
   - Edit or delete existing questions as needed

3. **Keywords Best Practices**
   - Add multiple variations of words
   - Include synonyms and common misspellings
   - Use specific terms and general terms
   - Examples: For NFC → ["nfc", "near field", "technology", "tap", "wireless"]

4. **Reviewing Feedback**
   - Switch to the "Feedback" tab
   - Filter by unread or feedback type
   - Review user questions and comments
   - Identify questions that need new answers
   - Mark feedback as read after reviewing

5. **Analytics**
   - View count shows popularity
   - Helpful/not helpful counts show answer quality
   - Helpfulness percentage helps prioritize improvements
   - Low helpfulness (<50%) indicates answers need updating

## Customization

### Changing Chat Icon Position

Edit `ChatbotFloatingIcon.vue`:

```vue
<style scoped>
.chatbot-floating-icon {
  position: fixed;
  bottom: 2rem;  /* Adjust vertical position */
  right: 2rem;   /* Adjust horizontal position */
  z-index: 9999;
}
</style>
```

### Changing Chat Panel Size

Edit `ChatbotInterface.vue`:

```vue
<style scoped>
.chat-modal {
  width: 350px;  /* Adjust width */
  height: 500px; /* Adjust height */
}
</style>
```

### Changing Colors

Update the gradient colors in both components:

```vue
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
```

### Adding More Quick Questions

Edit `ChatbotInterface.vue`:

```vue
const quickQuestions = ref([
  'What services do you offer?',
  'How much does it cost?',
  'What is NFC technology?',
  'Add your question here'  // Add more
])
```

## Troubleshooting

### Chat Icon Not Showing

1. Check that `app.vue` includes the chatbot components
2. Verify components are wrapped in `<ClientOnly>`
3. Check browser console for errors
4. Ensure z-index is high enough (9999)

### No Answers Being Found

1. Check that questions are marked as active in admin panel
2. Verify keywords are relevant to user questions
3. Test with exact question text from admin panel
4. Check API endpoint is responding correctly

### Feedback Not Submitting

1. Verify API endpoints are accessible
2. Check browser console for errors
3. Ensure Laravel routes are defined
4. Verify CORS settings if using separate domains

### Admin Page Not Loading

1. Check authentication middleware
2. Verify admin role permissions
3. Check API authentication token
4. Review browser console for errors

## Future Enhancements

Potential improvements for future versions:

1. **AI Integration**
   - Connect to GPT/Claude for natural language understanding
   - Semantic search instead of keyword matching
   - Context-aware conversations

2. **Advanced Features**
   - Multi-language support
   - File attachments in feedback
   - Voice input support
   - Chat history persistence
   - Email notifications for new feedback

3. **Analytics**
   - Conversation flow analysis
   - Question clustering
   - Response time metrics
   - User satisfaction trends

4. **Integration**
   - Slack/Teams notifications
   - CRM integration
   - Knowledge base import/export
   - API for third-party integrations

## Support

For issues or questions:
1. Check this documentation first
2. Review the code comments
3. Check browser console for errors
4. Contact development team

## License

Part of the NFC Business Card platform.
