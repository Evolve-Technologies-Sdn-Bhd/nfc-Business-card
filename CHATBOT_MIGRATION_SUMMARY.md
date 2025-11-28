# Chatbot Migration to n8n Webhook

## Summary of Changes

This document outlines the migration from the internal chatbot Q&A system to an external n8n webhook-based chat solution.

---

## Changes Made

### 1. Frontend Changes

#### Homepage Chat Widget (`frontend/pages/Homepage/index.vue`)
- ✅ Already configured to use n8n webhook: `https://n8n.jiosgroup.com/webhook/e529b3a4-d09d-45d6-8de5-01cc6885bcb7/chat`
- **Deprecated Functions:**
  - `loadPopularQuestions()` - No longer loads from backend
  - `submitFeedback()` - No longer sends to backend
  - `submitUserFeedback()` - No longer sends to backend

#### Chatbot Widget Component (`frontend/components/ChatbotWidget.vue`)
- **Updated:** Chat requests now POST directly to n8n webhook
- **Request Format:**
  ```javascript
  {
    question: "user question",
    chatId: Date.now() // Simple session identifier
  }
  ```
- **Removed:** Backend API calls to `/api/chatbot/ask`, `/api/chatbot/feedback`
- **Simplified:** Feedback submission no longer calls backend

#### Admin Navigation (`frontend/layouts/AdminManagement.vue`)
- **Removed:** "Chatbot & FAQ" menu item from admin sidebar
- Navigation no longer shows chatbot management option

#### Admin Chatbot Page
- **Deprecated:** `frontend/pages/AdminManagement/chatbot.vue` → `chatbot.vue.deprecated`
- Page is no longer accessible through routing

---

### 2. Backend Changes

#### API Routes (`backend/routes/api.php`)
- **Commented Out Public Routes:**
  ```php
  // Route::post('/chatbot/ask', [ChatbotController::class, 'ask']);
  // Route::post('/chatbot/feedback', [ChatbotController::class, 'submitFeedback']);
  // Route::get('/chatbot/questions', [ChatbotController::class, 'getQuestions']);
  ```

- **Commented Out Admin Routes:**
  ```php
  // Route::get('/admin/chatbot/questions', [AdminChatbotController::class, 'index']);
  // Route::post('/admin/chatbot/questions', [AdminChatbotController::class, 'store']);
  // Route::put('/admin/chatbot/questions/{id}', [AdminChatbotController::class, 'update']);
  // Route::delete('/admin/chatbot/questions/{id}', [AdminChatbotController::class, 'destroy']);
  // Route::get('/admin/chatbot/feedback', [AdminChatbotController::class, 'getFeedback']);
  // Route::get('/admin/chatbot/feedback/export', [AdminChatbotController::class, 'exportFeedback']);
  // Route::put('/admin/chatbot/feedback/{id}/status', [AdminChatbotController::class, 'updateFeedbackStatus']);
  // Route::put('/admin/chatbot/feedback/{id}/read', [AdminChatbotController::class, 'markFeedbackAsRead']);
  // Route::delete('/admin/chatbot/feedback/{id}', [AdminChatbotController::class, 'deleteFeedback']);
  // Route::get('/admin/chatbot/analytics', [AdminChatbotController::class, 'getAnalytics']);
  ```

- **Commented Out Controller Imports:**
  ```php
  // use App\Http\Controllers\Api\ChatbotController;
  // use App\Http\Controllers\Api\AdminChatbotController;
  ```

#### Controllers (Deprecated)
- `backend/app/Http/Controllers/Api/ChatbotController.php` → `ChatbotController.php.deprecated`
- `backend/app/Http/Controllers/Api/AdminChatbotController.php` → `AdminChatbotController.php.deprecated`

#### Models (Deprecated)
- `backend/app/Models/ChatbotQuestion.php` → `ChatbotQuestion.php.deprecated`
- `backend/app/Models/ChatbotFeedback.php` → `ChatbotFeedback.php.deprecated`

---

## Data Preservation

### Database Tables Retained
The following database tables remain unchanged and contain historical data:
- `chatbot_questions` - Contains Q&A pairs
- `chatbot_feedback` - Contains user feedback and analytics

### Database Migrations Retained
The following migration files are kept for reference:
- `2025_11_11_073651_create_chatbot_questions_table.php`
- `2025_11_11_073658_create_chatbot_feedback_table.php`
- `2025_11_17_000001_add_status_and_category_to_chatbot_feedback.php`

**⚠️ Important:** 
- Do NOT delete these tables until the migration is confirmed stable and historical data is no longer needed
- Do NOT run migrations down/rollback unless specifically instructed
- Database structure remains in place for potential future use or data export

---

## n8n Webhook Integration

### Endpoint
```
POST https://n8n.jiosgroup.com/webhook/e529b3a4-d09d-45d6-8de5-01cc6885bcb7/chat
```

### Request Format
```json
{
  "question": "User's question text",
  "chatId": 1234567890
}
```

### Expected Response Format
The frontend expects one of the following response structures:
```json
{
  "answer": "AI response text"
}
```
or
```json
{
  "message": "AI response text"
}
```
or
```json
{
  "data": {
    "answer": "AI response text"
  }
}
```
or
```json
{
  "output": "AI response text"
}
```

---

## Rollback Instructions

If you need to revert these changes:

### 1. Restore Backend Files
```bash
# From backend directory
mv app/Http/Controllers/Api/ChatbotController.php.deprecated app/Http/Controllers/Api/ChatbotController.php
mv app/Http/Controllers/Api/AdminChatbotController.php.deprecated app/Http/Controllers/Api/AdminChatbotController.php
mv app/Models/ChatbotQuestion.php.deprecated app/Models/ChatbotQuestion.php
mv app/Models/ChatbotFeedback.php.deprecated app/Models/ChatbotFeedback.php
```

### 2. Restore Routes
Edit `backend/routes/api.php`:
- Uncomment the controller imports
- Uncomment the public chatbot routes
- Uncomment the admin chatbot routes

### 3. Restore Frontend Admin Page
```bash
# From frontend directory
mv pages/AdminManagement/chatbot.vue.deprecated pages/AdminManagement/chatbot.vue
```

### 4. Restore Admin Navigation
Edit `frontend/layouts/AdminManagement.vue`:
- Uncomment the "Chatbot & FAQ" menu item

### 5. Revert Frontend Chat Widget
Edit `frontend/components/ChatbotWidget.vue`:
- Change the fetch URL back to `/api/chatbot/ask`
- Restore the feedback submission functions

### 6. Revert Homepage Chat
Edit `frontend/pages/Homepage/index.vue`:
- Restore the `loadPopularQuestions()` function
- Restore the `submitFeedback()` function
- Restore the `submitUserFeedback()` function

---

## Testing Checklist

After deployment, verify:

- [ ] Homepage chat widget opens and displays properly
- [ ] Users can send messages via the chat widget
- [ ] Chat responses are received from n8n webhook
- [ ] No console errors related to chatbot API calls
- [ ] Admin panel no longer shows "Chatbot & FAQ" menu item
- [ ] Direct navigation to `/AdminManagement/chatbot` shows 404 or error
- [ ] Backend routes return 404 for deprecated chatbot endpoints

---

## Migration Date
**November 26, 2025**

## Migration Rationale
- Simplify architecture by removing internal Q&A management
- Leverage n8n's advanced workflow capabilities for chat handling
- Reduce backend maintenance burden
- Improve scalability and flexibility of chat system

---

## Notes

1. **Production Data:** Database tables remain intact for historical reference and potential future use
2. **Backwards Compatibility:** Deprecated files are renamed with `.deprecated` extension rather than deleted
3. **Clean Rollback:** All changes can be easily reverted if issues arise
4. **No Breaking Changes:** The chat interface remains the same for end users
