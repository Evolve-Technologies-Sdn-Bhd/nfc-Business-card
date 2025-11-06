# OAuth Login Fix - Summary

## Issues Found

1. **Frontend OAuth handlers were incomplete**: The login and register pages had placeholder code for Google and Apple login that didn't properly redirect to the OAuth providers.

2. **Backend redirect endpoint returned JSON**: The backend's redirect endpoint returned a JSON response with the redirect URL instead of performing an actual HTTP redirect.

3. **Backend callback didn't create API tokens**: The backend's callback method used session-based authentication instead of creating Sanctum API tokens for the frontend.

4. **No frontend callback page**: There was no page to handle the OAuth callback and save the authentication token.

## Changes Made

### Backend Changes

**File: `backend/app/Http/Controllers/Auth/SocialAuthController.php`**

1. **`redirect()` method**: Changed to perform an actual HTTP redirect to the OAuth provider instead of returning JSON.
   - Now directly redirects users to Google/Apple OAuth pages

2. **`callback()` method**: Updated to create Sanctum API tokens and redirect to frontend callback page.
   - Creates a Sanctum token for API authentication
   - Redirects to `/auth/callback` with the token in the URL
   - Handles errors and redirects back to login with error messages

### Frontend Changes

**File: `frontend/pages/login.vue`**

1. **OAuth button handlers**: Fixed to use the correct API base URL configuration.
   - Uses `config.public.apiBaseUrl` instead of non-existent `config.public.apiBase`
   - Properly constructs the backend URL by removing `/api` suffix

2. **Error handling**: Added OAuth error detection in `onMounted()`.
   - Checks for error query parameters
   - Displays user-friendly error messages
   - Cleans up URL after showing error

**File: `frontend/pages/register.vue`**

1. **OAuth button handlers**: Implemented actual OAuth redirect instead of placeholder "coming soon" messages.
   - Google signup now redirects to OAuth flow
   - Apple signup now redirects to OAuth flow

**File: `frontend/pages/auth/callback.vue` (NEW)**

Created a new callback page that:
- Receives the token from OAuth callback
- Saves token to cookie
- Fetches user profile
- Redirects to dashboard or intended page
- Handles errors gracefully

## How the OAuth Flow Works Now

### 1. User Clicks "Sign in with Google/Apple"
```
Frontend (login.vue) → Redirects to Backend (/api/auth/{provider}/redirect)
```

### 2. Backend Redirects to OAuth Provider
```
Backend → Redirects to Google/Apple OAuth page
```

### 3. User Authorizes on Google/Apple
```
Google/Apple → Redirects back to Backend (/api/auth/{provider}/callback)
```

### 4. Backend Creates Token and Redirects
```
Backend:
  - Receives authorization code
  - Exchanges code for user info
  - Creates or finds user
  - Generates Sanctum API token
  - Redirects to Frontend (/auth/callback?token=xxx&redirect=/dashboard)
```

### 5. Frontend Saves Token and Completes Login
```
Frontend (auth/callback.vue):
  - Extracts token from URL
  - Saves to cookie and store
  - Fetches user profile
  - Redirects to dashboard
```

## Configuration Requirements

### Backend (.env)

Make sure these are set in `backend/.env`:

```env
# Frontend URL for OAuth redirects
FRONTEND_URL=http://localhost:3000

# Google OAuth
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret

# Apple OAuth (if using Apple Sign In)
APPLE_CLIENT_ID=com.yourcompany.yourapp
APPLE_TEAM_ID=YOUR10DIGIT
APPLE_KEY_ID=YOUR10CHAR
APPLE_PRIVATE_KEY=keys/AuthKey_XXXXX.p8

# Sanctum - Make sure frontend domain is whitelisted
SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000

# Cache driver (required for OAuth state management)
CACHE_STORE=database
```

### Frontend (.env)

Make sure these are set in `frontend/.env`:

```env
# API Base URL
API_BASE_URL=http://localhost:8000/api

# Google Client ID (for frontend SDK if needed)
GOOGLE_CLIENT_ID=your-google-client-id

# Apple Client ID (for frontend SDK if needed)
APPLE_CLIENT_ID=com.yourcompany.yourapp
```

### Google OAuth Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Create a project or select existing
3. Enable Google+ API
4. Go to "Credentials" → "Create Credentials" → "OAuth client ID"
5. Configure OAuth consent screen
6. Add authorized redirect URI: `http://localhost:8000/api/auth/google/callback`
7. For production: `https://yourdomain.com/api/auth/google/callback`
8. Copy Client ID and Client Secret to `.env`

### Apple Sign In Setup

1. Go to [Apple Developer Portal](https://developer.apple.com)
2. Register a Service ID
3. Configure Sign in with Apple
4. Add return URLs: `http://localhost:8000/api/auth/apple/callback`
5. Generate a private key (.p8 file)
6. Save key to `backend/keys/` directory
7. Update `.env` with Team ID, Key ID, and key path

## Testing Instructions

### 1. Start Both Servers
```bash
# Terminal 1 - Backend
cd backend
php artisan serve

# Terminal 2 - Frontend  
cd frontend
npm run dev
```

### 2. Test Google Login

1. Go to `http://localhost:3000/login`
2. Click "Google" button
3. Should redirect to Google sign-in page
4. Sign in with your Google account
5. Authorize the application
6. Should redirect back to your app
7. Should see "Completing sign in..." page briefly
8. Should land on dashboard, logged in

### 3. Test Apple Login

1. Go to `http://localhost:3000/login`
2. Click "Apple" button
3. Should redirect to Apple sign-in page
4. Sign in with your Apple ID
5. Authorize the application
6. Should redirect back to your app
7. Should see "Completing sign in..." page briefly
8. Should land on dashboard, logged in

### 4. Test Error Handling

- Deny authorization on Google/Apple page → Should show error and redirect to login
- Try with invalid credentials → Should show appropriate error message

## Common Issues and Solutions

### Issue: "Invalid client" error from Google
**Solution**: Make sure the redirect URI in Google Console matches exactly: `http://localhost:8000/api/auth/google/callback`

### Issue: Redirect loop
**Solution**: Check `FRONTEND_URL` in backend `.env` and `API_BASE_URL` in frontend `.env`

### Issue: "Invalid state" error
**Solution**: 
- Make sure cache is working (check `CACHE_STORE=database` in backend `.env`)
- Run `php artisan cache:clear`
- Check that sessions table exists: `php artisan migrate`

### Issue: Token not being saved
**Solution**: 
- Check browser console for errors
- Make sure cookies are enabled
- Check `SANCTUM_STATEFUL_DOMAINS` includes your frontend domain

### Issue: CORS errors
**Solution**: Check `config/cors.php` in backend, should allow your frontend domain

## Database Requirements

Make sure these tables exist (run migrations if needed):

```bash
cd backend
php artisan migrate
```

Required tables:
- `users` - For user accounts
- `social_identities` - For linking OAuth providers to users
- `personal_access_tokens` - For Sanctum API tokens
- `cache` - For storing OAuth state (if using database cache driver)
- `sessions` - For session management (if using session cache driver)

## Security Notes

1. **HTTPS in Production**: Always use HTTPS in production for OAuth
2. **Secure Cookies**: Set `SESSION_SECURE_COOKIE=true` in production
3. **Environment Variables**: Never commit `.env` files with real credentials
4. **State Validation**: The state parameter prevents CSRF attacks - don't disable it
5. **Token Expiry**: Consider implementing token refresh logic for long-lived sessions

## Next Steps

1. Test thoroughly with real Google/Apple accounts
2. Update OAuth consent screens with proper app information
3. Add profile pictures from social providers
4. Implement account linking (allow users to link multiple social accounts)
5. Add "Sign in with Microsoft" or other providers if needed
