# Chatbot Migration Verification Checklist

## Quick Verification Commands

### 1. Check Deprecated Files Exist
```powershell
# Frontend
Test-Path "frontend\pages\AdminManagement\chatbot.vue.deprecated"

# Backend Controllers
Test-Path "backend\app\Http\Controllers\Api\ChatbotController.php.deprecated"
Test-Path "backend\app\Http\Controllers\Api\AdminChatbotController.php.deprecated"

# Backend Models
Test-Path "backend\app\Models\ChatbotQuestion.php.deprecated"
Test-Path "backend\app\Models\ChatbotFeedback.php.deprecated"
```

### 2. Verify Active Files Removed
```powershell
# These should return False
Test-Path "frontend\pages\AdminManagement\chatbot.vue"
Test-Path "backend\app\Http\Controllers\Api\ChatbotController.php"
Test-Path "backend\app\Http\Controllers\Api\AdminChatbotController.php"
Test-Path "backend\app\Models\ChatbotQuestion.php"
Test-Path "backend\app\Models\ChatbotFeedback.php"
```

### 3. Search for Active References
```powershell
# Should return only commented references
Select-String -Path "backend\routes\api.php" -Pattern "chatbot" -CaseSensitive
```

### 4. Check Frontend Changes
```powershell
# Verify n8n webhook URL is present
Select-String -Path "frontend\components\ChatbotWidget.vue" -Pattern "n8n.jiosgroup.com"
Select-String -Path "frontend\pages\Homepage\index.vue" -Pattern "n8n.jiosgroup.com"
```

---

## Manual Testing Steps

### Frontend Testing

1. **Homepage Chat Widget**
   - [ ] Navigate to homepage
   - [ ] Click on chat button (bottom right)
   - [ ] Chat modal opens successfully
   - [ ] Type a test message and send
   - [ ] Verify message is sent to n8n webhook (check network tab)
   - [ ] Response appears in chat
   - [ ] No console errors

2. **Admin Panel**
   - [ ] Login as admin
   - [ ] Navigate to `/AdminManagement`
   - [ ] Verify "Chatbot & FAQ" is NOT in sidebar menu
   - [ ] Try to directly navigate to `/AdminManagement/chatbot`
   - [ ] Should show 404 or error page

### Backend Testing

3. **Deprecated Endpoints**
   Test these endpoints should return 404:
   ```bash
   # Public endpoints
   POST /api/chatbot/ask
   POST /api/chatbot/feedback
   GET /api/chatbot/questions
   
   # Admin endpoints (with auth token)
   GET /api/admin/chatbot/questions
   POST /api/admin/chatbot/questions
   PUT /api/admin/chatbot/questions/{id}
   DELETE /api/admin/chatbot/questions/{id}
   GET /api/admin/chatbot/feedback
   ```

4. **Database Integrity**
   ```sql
   -- Verify tables still exist
   SHOW TABLES LIKE 'chatbot%';
   
   -- Check data is preserved
   SELECT COUNT(*) FROM chatbot_questions;
   SELECT COUNT(*) FROM chatbot_feedback;
   ```

### Network Testing

5. **n8n Webhook Integration**
   - [ ] Open browser developer tools (F12)
   - [ ] Go to Network tab
   - [ ] Send a chat message from homepage
   - [ ] Verify POST request to: `https://n8n.jiosgroup.com/webhook/e529b3a4-d09d-45d6-8de5-01cc6885bcb7/chat`
   - [ ] Check request payload contains `question` and `chatId`
   - [ ] Check response status is 200
   - [ ] Verify response contains `answer`, `message`, `output`, or `data.answer` field

---

## Expected Results

### ✅ Success Indicators
- Chat widget works and sends messages to n8n
- No errors in browser console
- Admin panel does not show chatbot menu
- Backend routes return 404 for chatbot endpoints
- Database tables still exist with data intact
- All deprecated files have `.deprecated` extension

### ❌ Failure Indicators
- Chat widget shows errors or doesn't send messages
- Console shows 404 errors for n8n webhook
- Admin panel still shows "Chatbot & FAQ" menu
- Backend routes are still accessible
- Database tables were deleted
- Any active references to old chatbot code

---

## Troubleshooting

### Issue: Chat not working on homepage
**Check:**
1. Browser console for errors
2. Network tab shows request to n8n webhook
3. n8n webhook URL is correct
4. CORS is configured on n8n webhook

**Fix:**
- Verify n8n webhook is active and accessible
- Check webhook URL in `ChatbotWidget.vue` and `Homepage/index.vue`

### Issue: Admin panel shows errors
**Check:**
1. Navigation array in `AdminManagement.vue`
2. Route file doesn't have syntax errors

**Fix:**
- Verify chatbot menu item is commented out
- Clear browser cache and hard refresh

### Issue: Backend returns 500 errors
**Check:**
1. `api.php` routes file syntax
2. Controller import statements are commented

**Fix:**
- Ensure all ChatbotController imports are commented
- Run `php artisan route:clear`
- Run `php artisan config:clear`

---

## Post-Deployment Commands

```bash
# Backend
cd backend
php artisan route:clear
php artisan config:clear
php artisan cache:clear
composer dump-autoload

# Frontend
cd frontend
npm run build  # For production
```

---

## Rollback Commands (If Needed)

```powershell
# Restore all deprecated files
cd backend
Move-Item "app\Http\Controllers\Api\ChatbotController.php.deprecated" "app\Http\Controllers\Api\ChatbotController.php"
Move-Item "app\Http\Controllers\Api\AdminChatbotController.php.deprecated" "app\Http\Controllers\Api\AdminChatbotController.php"
Move-Item "app\Models\ChatbotQuestion.php.deprecated" "app\Models\ChatbotQuestion.php"
Move-Item "app\Models\ChatbotFeedback.php.deprecated" "app\Models\ChatbotFeedback.php"

cd ..\frontend
Move-Item "pages\AdminManagement\chatbot.vue.deprecated" "pages\AdminManagement\chatbot.vue"
```

Then manually uncomment the routes in `backend/routes/api.php` and restore the admin menu item.

---

## Contact & Support

If you encounter issues during verification:
1. Check the `CHATBOT_MIGRATION_SUMMARY.md` for detailed information
2. Review console and network logs
3. Verify n8n webhook is responding correctly
4. Test with different browsers to rule out caching issues
