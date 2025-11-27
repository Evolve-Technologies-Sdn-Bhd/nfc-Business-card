# Chatbot Migration - Quick Reference

## What Changed?

### 🔴 REMOVED (Deprecated)
- Admin "Chatbot & FAQ" menu and management page
- Backend chatbot Q&A management endpoints
- Backend chatbot controllers and models
- Frontend API calls to `/api/chatbot/*` and `/api/admin/chatbot/*`

### 🟢 ADDED / UPDATED
- Frontend chat now uses n8n webhook: `https://n8n.jiosgroup.com/webhook/e529b3a4-d09d-45d6-8de5-01cc6885bcb7/chat`
- All chat requests go directly to n8n (no backend involvement)

### 🟡 PRESERVED
- Database tables: `chatbot_questions`, `chatbot_feedback` (data intact)
- Database migrations (not rolled back)
- All deprecated files renamed with `.deprecated` extension

---

## Files Modified

### Frontend
| File | Change |
|------|--------|
| `frontend/components/ChatbotWidget.vue` | Updated to use n8n webhook |
| `frontend/pages/Homepage/index.vue` | Deprecated backend chatbot calls |
| `frontend/layouts/AdminManagement.vue` | Removed chatbot menu item |
| `frontend/pages/AdminManagement/chatbot.vue` | Renamed to `.deprecated` |

### Backend
| File | Change |
|------|--------|
| `backend/routes/api.php` | Commented out all chatbot routes |
| `backend/app/Http/Controllers/Api/ChatbotController.php` | Renamed to `.deprecated` |
| `backend/app/Http/Controllers/Api/AdminChatbotController.php` | Renamed to `.deprecated` |
| `backend/app/Models/ChatbotQuestion.php` | Renamed to `.deprecated` |
| `backend/app/Models/ChatbotFeedback.php` | Renamed to `.deprecated` |

---

## n8n Webhook Details

**URL:** `https://n8n.jiosgroup.com/webhook/e529b3a4-d09d-45d6-8de5-01cc6885bcb7/chat`

**Method:** `POST`

**Request Body:**
```json
{
  "question": "User's question",
  "chatId": 1234567890
}
```

**Expected Response:**
```json
{
  "answer": "AI response"
}
```

---

## Quick Rollback

If issues arise, run:

```powershell
# Restore backend files
cd backend
Move-Item "app\Http\Controllers\Api\ChatbotController.php.deprecated" "app\Http\Controllers\Api\ChatbotController.php"
Move-Item "app\Http\Controllers\Api\AdminChatbotController.php.deprecated" "app\Http\Controllers\Api\AdminChatbotController.php"
Move-Item "app\Models\ChatbotQuestion.php.deprecated" "app\Models\ChatbotQuestion.php"
Move-Item "app\Models\ChatbotFeedback.php.deprecated" "app\Models\ChatbotFeedback.php"

# Restore frontend
cd ..\frontend
Move-Item "pages\AdminManagement\chatbot.vue.deprecated" "pages\AdminManagement\chatbot.vue"
```

Then uncomment routes in `backend/routes/api.php`

---

## Testing

1. **Homepage:** Chat widget sends to n8n ✓
2. **Admin:** No "Chatbot & FAQ" menu ✓
3. **Backend:** Chatbot routes return 404 ✓
4. **Database:** Tables and data intact ✓

---

## Documentation

- 📋 **Full Details:** `CHATBOT_MIGRATION_SUMMARY.md`
- ✅ **Verification:** `CHATBOT_MIGRATION_VERIFICATION.md`
- 📝 **This File:** Quick reference guide

---

**Migration Date:** November 26, 2025
