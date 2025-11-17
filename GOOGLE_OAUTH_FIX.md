# Google OAuth Login 404 Fix

## Problem
After clicking "Log in with Google" and completing authentication, the app redirects to `/` (root) and shows a 404 error instead of completing the login flow.

## Root Cause Analysis

### Expected OAuth Flow
1. User clicks "Login with Google" button
2. Frontend redirects to: `http://localhost:8000/api/auth/google/redirect`
3. Backend redirects to Google OAuth page
4. User authenticates with Google
5. Google redirects back to: `http://localhost:8000/api/auth/google/callback?code=...`
6. Backend processes OAuth callback and creates token
7. Backend redirects to: `http://localhost:3000/auth/callback?token=...&is_new_user=true/false`
8. Frontend `/auth/callback` page processes token and redirects to appropriate dashboard

### Identified Issues

#### Issue #1: Missing Root Route Handler
- **Problem**: No `frontend/pages/index.vue` existed, causing 404 when accessing `/`
- **Impact**: If any redirect or error lands on `/`, it shows 404
- **Fix**: Created `frontend/pages/index.vue` with redirect to `/Homepage`

#### Issue #2: Google OAuth Console Configuration
- **Google Console Redirect URI**: Must be set to `http://localhost:8000/api/auth/google/callback`
- **Configured in**: `backend/config/services.php` → `'redirect' => env('APP_URL') . '/api/auth/google/callback'`
- **Current Value**: `http://localhost:8000/api/auth/google/callback`
- **Action Required**: Verify Google Cloud Console has this exact URI in Authorized redirect URIs

#### Issue #3: CORS Configuration
- **Backend CORS**: Must allow frontend origin `http://localhost:3000`
- **Configured in**: `backend/config/cors.php`
- **Action Required**: Verify CORS allows credentials and frontend origin

## Configuration Checklist

### Backend Configuration (.env)
```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000
GOOGLE_CLIENT_ID=377300584487-760ljnf19kpenvatbplhc36annfv7jdn.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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
```

### Frontend Configuration
- Dev server runs on: `http://localhost:3000` (nuxt.config.ts line 88)
- API base URL: `http://localhost:8000/api`

### Google Cloud Console Settings
**CRITICAL**: Verify the following in Google Cloud Console:

1. Go to: https://console.cloud.google.com/apis/credentials
2. Select your OAuth 2.0 Client ID
3. Under "Authorized redirect URIs", ensure you have:
   - `http://localhost:8000/api/auth/google/callback`
4. Under "Authorized JavaScript origins", add:
   - `http://localhost:8000`
   - `http://localhost:3000`

## Files Modified

### Created: `frontend/pages/index.vue`
```vue
<!-- pages/index.vue -->
<!-- Root route handler - redirects to Homepage -->
<script setup>
// Redirect to Homepage for unauthenticated users
// The auth middleware will handle authenticated users appropriately
definePageMeta({
  middleware: defineNuxtRouteMiddleware(() => {
    return navigateTo('/Homepage', { redirectCode: 301 });
  }),
});
</script>
```

## Testing the Fix

### Test Steps
1. Start backend server: `cd backend && php artisan serve`
2. Start frontend server: `cd frontend && npm run dev`
3. Open browser to: `http://localhost:3000/UserAccount/login`
4. Click "Login with Google"
5. Complete Google authentication
6. Verify redirect to `/auth/callback` (should show "Completing sign in..." spinner)
7. Verify final redirect to dashboard:
   - New users → `/UserDashboard/PlanSelection`
   - Existing users → `/UserDashboard/CardManagement`

### Debug Logging
The following console logs should appear:

**Frontend (login.vue)**:
```
🔵 Google Login Clicked
📍 API Base URL: http://localhost:8000/api
🔗 OAuth URL: http://localhost:8000/api/auth/google/redirect
🚀 Navigating now...
```

**Frontend (auth/callback.vue)**:
```
🔵 Callback page loaded
📝 Token from URL: [token_value]
🍪 Token from cookie: [token_value]
💾 Saving token to cookie...
✅ Token saved to cookie
✅ Token set in store
🔄 Fetching user profile...
👤 User data: {...}
📦 Subscription plan: free/basic/premium/business
🆕 Is new user from backend: true/false
➡️ Redirecting to [destination]...
```

## Common Issues

### Issue: 404 at root (/)
**Cause**: Missing root index.vue or Nuxt not properly initialized
**Fix**: Ensure `frontend/pages/index.vue` exists with Homepage redirect

### Issue: "Invalid state" error
**Cause**: OAuth state validation failed (timeout or CSRF mismatch)
**Fix**: Try again - state tokens expire after 10 minutes

### Issue: "Authentication failed" with SSL error
**Cause**: Windows development environment SSL certificate issues
**Fix**: Set `CURL_VERIFY_SSL=false` in backend .env (development only!)

### Issue: Redirect loops
**Cause**: Auth middleware conflicts or incorrect redirect logic
**Fix**: Check auth middleware in `frontend/middleware/auth.js`

### Issue: Google OAuth "redirect_uri_mismatch"
**Cause**: Google Console redirect URI doesn't match backend configuration
**Fix**: Update Google Console to use exact URI: `http://localhost:8000/api/auth/google/callback`

## Security Notes

⚠️ **Development vs Production**:
- Development uses HTTP (localhost)
- Production MUST use HTTPS
- Update Google Console OAuth settings for production domain
- Update `FRONTEND_URL` and `APP_URL` in production .env

## Next Steps

1. ✅ Created root index.vue handler
2. ⚠️ **ACTION REQUIRED**: Verify Google Cloud Console redirect URI configuration
3. ⚠️ **ACTION REQUIRED**: Test complete OAuth flow
4. ⚠️ **ACTION REQUIRED**: Verify CORS configuration allows frontend origin

## Related Files
- `backend/config/services.php` - OAuth provider configuration
- `backend/app/Http/Controllers/Auth/SocialAuthController.php` - OAuth controllers
- `backend/app/Services/SocialAuthService.php` - OAuth business logic
- `backend/routes/api.php` - OAuth routes
- `frontend/pages/UserAccount/login.vue` - Google login button
- `frontend/pages/auth/callback.vue` - OAuth callback handler
- `frontend/pages/index.vue` - Root route handler (NEW)
- `frontend/middleware/trailing-slash.global.ts` - URL normalization
