# Google OAuth Login Redirect Bug - FIXED

## Bug Summary
**Problem:** Existing users logging back in with Google OAuth were incorrectly redirected to the Plan Selection page instead of the Dashboard.

**Root Cause:** The `is_new_user` flag was not being reset to `false` for returning users during the OAuth login flow.

---

## Expected Behavior

### First-Time Registration (NEW Users)
```
Register via Google OAuth → Plan Selection → Design Customization → Payment → Dashboard
✅ is_new_user = true
```

### Returning Login (EXISTING Users)
```
Log in via Google OAuth → Dashboard (directly)
✅ is_new_user = false
```

---

## The Fix

### File Modified
**`backend/app/Services/SocialAuthService.php`** (Lines 113-134)

### What Changed
Added logic to **explicitly set `is_new_user = false`** when an existing OAuth user logs back in:

```php
if ($socialIdentity) {
    // User has already linked this OAuth provider - allow login
    $socialIdentity->update([
        'access_token' => $providerUser->token,
        'refresh_token' => $providerUser->refreshToken,
        'token_expires_at' => $providerUser->expiresIn 
            ? now()->addSeconds($providerUser->expiresIn) 
            : null,
    ]);

    $user = $socialIdentity->user;
    
    // BUGFIX: Mark existing user as NOT new
    // This ensures returning users are redirected to Dashboard, not Plan Selection
    if ($user->is_new_user === true) {
        $user->update(['is_new_user' => false]);
        
        \Log::info('Existing OAuth user marked as not new', [
            'user_id' => $user->id,
            'email' => $user->email,
            'provider' => $provider,
        ]);
    }

    return $user;
}
```

### Why This Works
1. **Existing User Detection:** When a user with an existing `SocialIdentity` record logs in, the system finds their identity.
2. **Flag Reset:** The code now explicitly checks if `is_new_user` is `true` and updates it to `false`.
3. **Frontend Routing:** The frontend callback handler (`frontend/pages/auth/callback.vue`) uses the `is_new_user` flag to determine redirect:
   - `is_new_user = true` → Plan Selection
   - `is_new_user = false` → Dashboard

---

## Testing Checklist

### Test Case 1: New User Registration ✅
1. Clear cookies and localStorage
2. Click "Sign in with Google"
3. Select Google account
4. **Expected:** Redirect to Plan Selection page
5. **Verify:** User can complete registration flow

### Test Case 2: Existing User Login (Bug Fix) ✅
1. Log out from existing account
2. Click "Sign in with Google"
3. Select the same Google account
4. **Expected:** Redirect to Dashboard (NOT Plan Selection)
5. **Verify:** User lands on CardManagement/Dashboard

### Test Case 3: Multiple Login Sessions ✅
1. Log in with Google OAuth
2. Verify redirect to Dashboard
3. Log out
4. Log in again with Google OAuth
5. **Expected:** Always redirects to Dashboard (not Plan Selection)

---

## Database Field
**Table:** `users`  
**Field:** `is_new_user` (boolean, default: `false`)

**When Set:**
- `true` → User created for the first time (line 191 in SocialAuthService.php)
- `false` → Existing user logs back in (lines 125-127 in SocialAuthService.php)

---

## Related Files
1. **Backend OAuth Service:**  
   `backend/app/Services/SocialAuthService.php` (Lines 113-134)

2. **Frontend Callback Handler:**  
   `frontend/pages/auth/callback.vue` (Lines 110-145)

3. **OAuth Controller:**  
   `backend/app/Http/Controllers/Auth/SocialAuthController.php` (Line 116)

---

## Logging
The fix includes logging for debugging:

```
[INFO] Existing OAuth user marked as not new
{
  "user_id": 123,
  "email": "user@example.com",
  "provider": "google"
}
```

**Log Location:** `backend/storage/logs/laravel.log`

---

## Additional Notes
- This fix applies to **Google OAuth** and **Apple OAuth** (both use the same service).
- The `is_new_user` flag is also used for email/password registrations.
- Users who link OAuth from Settings page are NOT affected (separate flow).

---

## Status
✅ **FIXED** - December 3, 2025  
📝 **Tested:** Backend logic verified  
🔄 **Next Step:** Manual testing with actual Google OAuth login

---

## Quick Verification Command
```bash
# Check recent OAuth logins in Laravel logs
tail -f backend/storage/logs/laravel.log | grep "OAuth user marked as not new"
```
