# Chatbot API Documentation

## Base URL
```
Development: http://localhost:8000/api
Production: https://your-domain.com/api
```

---

## Public Endpoints

### 1. Ask Question

Send a user question and get an AI-generated response.

**Endpoint:** `POST /chatbot/ask`

**Request Body:**
```json
{
  "question": "What is an NFC business card?"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "found": true,
  "data": {
    "id": 1,
    "question": "What is an NFC business card?",
    "answer": "An NFC business card is a modern digital alternative to traditional paper business cards..."
  }
}
```

**No Answer Found Response (200):**
```json
{
  "success": true,
  "found": false,
  "message": "I couldn't find an answer to your question. Would you like to leave feedback?"
}
```

**Validation Error (422):**
```json
{
  "success": false,
  "errors": {
    "question": ["The question field is required."]
  }
}
```

---

### 2. Submit Feedback

Submit user feedback for answered or unanswered questions.

**Endpoint:** `POST /chatbot/feedback`

**Request Body:**
```json
{
  "question_id": 1,                    // Optional: ID of the answered question
  "user_question": "What are your hours?",
  "user_message": "This answer was not helpful because...",  // Optional
  "user_name": "John Doe",             // Optional
  "user_email": "john@example.com",    // Optional
  "rating": 5,                         // Optional: 1-5
  "feedback_type": "rating"            // Required: not_found, rating, general
}
```

**Feedback Types:**
- `not_found` - Question was not answered
- `rating` - Rating an answered question
- `general` - General feedback

**Success Response (200):**
```json
{
  "success": true,
  "message": "Thank you for your feedback!",
  "data": {
    "id": 123,
    "question_id": 1,
    "user_question": "What are your hours?",
    "user_message": "This answer was not helpful because...",
    "user_name": "John Doe",
    "user_email": "john@example.com",
    "rating": 5,
    "feedback_type": "rating",
    "is_read": false,
    "ip_address": "192.168.1.1",
    "created_at": "2025-11-17T10:30:00.000000Z",
    "updated_at": "2025-11-17T10:30:00.000000Z"
  }
}
```

---

### 3. Get All Questions

Retrieve all active Q&A pairs (useful for FAQ pages).

**Endpoint:** `GET /chatbot/questions`

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "question": "What is an NFC business card?",
      "answer": "An NFC business card is a modern digital alternative...",
      "helpfulness": 87.5
    },
    {
      "id": 2,
      "question": "How do I create my digital card?",
      "answer": "Creating your digital NFC card is easy...",
      "helpfulness": 92.3
    }
  ]
}
```

---

## Admin Endpoints

All admin endpoints require authentication via Laravel Sanctum.

**Authentication Header:**
```
Authorization: Bearer YOUR_API_TOKEN
```

---

### 4. List All Q&A Pairs

Get all chatbot questions with optional filtering.

**Endpoint:** `GET /admin/chatbot/questions`

**Query Parameters:**
- `is_active` (boolean) - Filter by active status
- `search` (string) - Search in question or answer text

**Examples:**
```
GET /admin/chatbot/questions
GET /admin/chatbot/questions?is_active=true
GET /admin/chatbot/questions?search=nfc
GET /admin/chatbot/questions?is_active=true&search=pricing
```

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "question": "What is an NFC business card?",
      "answer": "An NFC business card is...",
      "keywords": ["nfc", "business card", "digital card"],
      "priority": 100,
      "is_active": true,
      "view_count": 1234,
      "helpful_count": 108,
      "not_helpful_count": 15,
      "helpfulness": 87.8,
      "created_at": "2025-11-17T10:00:00.000000Z",
      "updated_at": "2025-11-17T10:00:00.000000Z"
    }
  ]
}
```

---

### 5. Create Q&A Pair

Add a new question and answer pair.

**Endpoint:** `POST /admin/chatbot/questions`

**Request Body:**
```json
{
  "question": "What payment methods do you accept?",
  "answer": "We accept credit cards, PayPal, and bank transfers.",
  "keywords": ["payment", "pay", "credit card", "paypal"],
  "priority": 50,
  "is_active": true
}
```

**Field Validations:**
- `question` - Required, string, max 255 characters
- `answer` - Required, string (no max limit)
- `keywords` - Required, array, minimum 1 keyword
- `keywords.*` - Required, string, max 100 characters each
- `priority` - Optional, integer, 0-100 (default: 0)
- `is_active` - Optional, boolean (default: true)

**Success Response (201):**
```json
{
  "success": true,
  "message": "Q&A created successfully",
  "data": {
    "id": 17,
    "question": "What payment methods do you accept?",
    "answer": "We accept credit cards, PayPal, and bank transfers.",
    "keywords": ["payment", "pay", "credit card", "paypal"],
    "priority": 50,
    "is_active": true,
    "view_count": 0,
    "helpful_count": 0,
    "not_helpful_count": 0,
    "created_at": "2025-11-17T11:00:00.000000Z",
    "updated_at": "2025-11-17T11:00:00.000000Z"
  }
}
```

---

### 6. Update Q&A Pair

Modify an existing question and answer.

**Endpoint:** `PUT /admin/chatbot/questions/{id}`

**Request Body (all fields optional):**
```json
{
  "question": "Updated question text",
  "answer": "Updated answer text",
  "keywords": ["new", "keywords", "list"],
  "priority": 75,
  "is_active": false
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Q&A updated successfully",
  "data": {
    // Updated Q&A object
  }
}
```

**Not Found (404):**
```json
{
  "message": "No query results for model [App\\Models\\ChatbotQuestion] {id}"
}
```

---

### 7. Delete Q&A Pair

Remove a question and answer pair.

**Endpoint:** `DELETE /admin/chatbot/questions/{id}`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Q&A deleted successfully"
}
```

**Not Found (404):**
```json
{
  "message": "No query results for model [App\\Models\\ChatbotQuestion] {id}"
}
```

---

### 8. Get All Feedback

Retrieve all user feedback with optional filtering.

**Endpoint:** `GET /admin/chatbot/feedback`

**Query Parameters:**
- `is_read` (boolean) - Filter by read status
- `feedback_type` (string) - Filter by type: not_found, rating, general
- `per_page` (integer) - Items per page (default: 50)

**Examples:**
```
GET /admin/chatbot/feedback
GET /admin/chatbot/feedback?is_read=false
GET /admin/chatbot/feedback?feedback_type=not_found
GET /admin/chatbot/feedback?is_read=false&per_page=20
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 45,
        "question_id": null,
        "user_question": "What are your business hours?",
        "user_message": "I need to know when you're open",
        "user_name": "Jane Smith",
        "user_email": "jane@example.com",
        "rating": null,
        "feedback_type": "not_found",
        "is_read": false,
        "ip_address": "192.168.1.100",
        "created_at": "2025-11-17T09:30:00.000000Z",
        "updated_at": "2025-11-17T09:30:00.000000Z",
        "question": null
      },
      {
        "id": 44,
        "question_id": 5,
        "user_question": "Do I need a physical card?",
        "user_message": "Answer was not clear",
        "user_name": null,
        "user_email": null,
        "rating": 2,
        "feedback_type": "rating",
        "is_read": true,
        "ip_address": "192.168.1.101",
        "created_at": "2025-11-17T08:15:00.000000Z",
        "updated_at": "2025-11-17T10:00:00.000000Z",
        "question": {
          "id": 5,
          "question": "Do I need a physical card?",
          "answer": "Not necessarily! You can use..."
        }
      }
    ],
    "first_page_url": "http://localhost:8000/api/admin/chatbot/feedback?page=1",
    "last_page_url": "http://localhost:8000/api/admin/chatbot/feedback?page=3",
    "next_page_url": "http://localhost:8000/api/admin/chatbot/feedback?page=2",
    "prev_page_url": null,
    "per_page": 50,
    "total": 123,
    "last_page": 3
  }
}
```

---

### 9. Mark Feedback as Read

Mark a feedback entry as read/reviewed.

**Endpoint:** `PUT /admin/chatbot/feedback/{id}/read`

**Request Body:**
```json
{
  "is_read": true
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Feedback marked as read",
  "data": {
    "id": 45,
    "is_read": true,
    // ... other feedback fields
  }
}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "errors": {
    "field_name": [
      "Error message here"
    ]
  }
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated."
}
```

### Not Found (404)
```json
{
  "message": "No query results for model [App\\Models\\ModelName] {id}"
}
```

### Server Error (500)
```json
{
  "message": "Server Error",
  "exception": "Exception message (only in debug mode)"
}
```

---

## Rate Limiting

All API endpoints are rate-limited to prevent abuse:

- **Public endpoints**: 60 requests per minute
- **Admin endpoints**: 120 requests per minute

**Rate Limit Headers:**
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
X-RateLimit-Reset: 1636281600
```

**Rate Limit Exceeded (429):**
```json
{
  "message": "Too Many Requests"
}
```

---

## Testing with cURL

### Ask a Question
```bash
curl -X POST http://localhost:8000/api/chatbot/ask \
  -H "Content-Type: application/json" \
  -d '{"question":"What is an NFC business card?"}'
```

### Submit Feedback
```bash
curl -X POST http://localhost:8000/api/chatbot/feedback \
  -H "Content-Type: application/json" \
  -d '{
    "user_question": "What are your hours?",
    "feedback_type": "not_found",
    "user_name": "John Doe",
    "user_email": "john@example.com"
  }'
```

### Get All Active Questions
```bash
curl -X GET http://localhost:8000/api/chatbot/questions
```

### Admin - Create Q&A (requires auth token)
```bash
curl -X POST http://localhost:8000/api/admin/chatbot/questions \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -d '{
    "question": "What payment methods do you accept?",
    "answer": "We accept credit cards, PayPal, and bank transfers.",
    "keywords": ["payment", "pay", "credit card"],
    "priority": 50
  }'
```

---

## Testing with Postman

Import this collection to test all endpoints:

**Collection Variables:**
- `base_url`: `http://localhost:8000/api`
- `admin_token`: Your Laravel Sanctum token

**Authentication Setup:**
1. Login via your auth endpoint
2. Copy the returned token
3. Add to Postman environment as `admin_token`
4. Use `{{admin_token}}` in Authorization header

---

## WebSocket Support (Future)

Real-time chatbot updates via WebSocket will be added in future versions:
- Live typing indicators
- Instant admin notifications for feedback
- Real-time Q&A updates

---

## Changelog

### Version 1.0 (Current)
- ✅ Public chatbot endpoints
- ✅ Admin Q&A management
- ✅ Feedback collection
- ✅ Smart keyword matching
- ✅ Analytics tracking

### Planned Features
- 🔜 AI integration (OpenAI/ChatGPT)
- 🔜 Multi-language support
- 🔜 WebSocket real-time updates
- 🔜 Advanced analytics dashboard
- 🔜 Conversation history export

---

## Support

For API issues or questions:
- Check Laravel logs: `backend/storage/logs/laravel.log`
- Enable debug mode: Set `APP_DEBUG=true` in `.env`
- Review route list: `php artisan route:list | grep chatbot`
- Test database connection: `php artisan tinker`

---

Last Updated: November 17, 2025
