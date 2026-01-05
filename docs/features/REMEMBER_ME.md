# Remember Me Functionality Implementation

## Overview
This implementation provides a secure "Remember Me" functionality **exclusively for email/password authentication**. Users who log in with Google OAuth are **not** eligible for this feature.

## Security Features

### 1. **Cryptographically Secure Tokens**
- Tokens are generated using Laravel's `Str::random(60)` which provides 60 characters of randomness
- Tokens are hashed with SHA-256 before storage in the database
- Only the hash is stored in the database; plain tokens are never stored

### 2. **HttpOnly Cookies**
- Tokens are sent to the client as `httpOnly` cookies
- This prevents JavaScript access, protecting against XSS attacks
- Cookies are also marked as `secure` (HTTPS only) and use `sameSite=lax` for CSRF protection

### 3. **Token Expiration**
- Tokens expire after 30 days by default
- Expired tokens are automatically rejected
- A cleanup command is available to remove expired tokens from the database

### 4. **Token Revocation**
- Users can logout from all devices, revoking all remember tokens
- Individual logout also revokes the current remember token
- Tokens can be manually revoked by administrators if needed

## Database Schema

### Remember Tokens Table
```sql
CREATE TABLE remember_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash VARCHAR(64) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    last_used_at TIMESTAMP NULL,
    user_agent VARCHAR(500) NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_expires (user_id, expires_at),
    INDEX idx_token_hash (token_hash)
);
```

## Implementation Details

### 1. **Login Flow (AuthController::login)**
When a user logs in with `remember=true`:
1. Validate credentials (email + password)
2. **Check if user is OAuth-only** - if yes, deny password login
3. Create a Sanctum token for the current session
4. If `remember=true`:
   - Generate a 60-character random token
   - Hash it with SHA-256
   - Store hash in `remember_tokens` table with:
     - User ID
     - Token hash
     - Expiration (30 days from now)
     - User agent
     - IP address
   - Create httpOnly cookie with plain token
5. Return response with cookie attached

### 2. **Auto-Authentication (RememberMe Middleware)**
On every API request:
1. Check if user is already authenticated via Sanctum
2. If not, check for `remember_token` cookie
3. If cookie exists:
   - Hash the plain token from cookie
   - Look up hash in `remember_tokens` table
   - Verify token is not expired
   - Verify user exists and has local password (not OAuth-only)
   - Update `last_used_at` timestamp
   - Create new Sanctum token for this session
   - Authenticate user automatically
4. If token is invalid/expired, clear the cookie

### 3. **Logout Flow (AuthController::logout)**
When a user logs out:
1. Log the logout activity
2. Delete the current Sanctum token
3. If `remember_token` cookie exists:
   - Hash the token
   - Delete it from the database
   - Clear the cookie
4. Return success response

### 4. **Logout Everywhere (AuthController::logoutEverywhere)**
When a user logs out from all devices:
1. Log the activity
2. Delete **all** Sanctum tokens for the user
3. Delete **all** remember tokens for the user
4. Clear the remember_token cookie
5. Return success response

## API Endpoints

### Login with Remember Me
```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password123",
  "remember": true
}
```

**Response:**
```json
{
  "success": true,
  "user": { ... },
  "token": "sanctum_token_here",
  "token_type": "Bearer"
}
```
**Cookie Set:** `remember_token` (httpOnly, secure, 30 days)

### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```
**Cookie Cleared:** `remember_token`

### Logout Everywhere
```http
POST /api/user/logout-everywhere
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "message": "You have been logged out from all devices."
}
```
**Effect:** All Sanctum tokens and remember tokens for the user are deleted

## Middleware Configuration

The `RememberMe` middleware is registered in `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->api(append: [
        \App\Http\Middleware\RememberMe::class, // First in chain
        \App\Http\Middleware\LogActivity::class,
        \App\Http\Middleware\HandleCors::class,
    ]);
})
```

## Maintenance

### Cleanup Expired Tokens
Run this command periodically (e.g., via cron):
```bash
php artisan remember-tokens:cleanup
```

You can schedule this in `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('remember-tokens:cleanup')->daily();
}
```

## Security Considerations

### ✅ What This Implementation Protects Against:
1. **Token theft via XSS** - httpOnly cookies prevent JavaScript access
2. **Token theft via CSRF** - sameSite=lax provides protection
3. **Brute force attacks** - 60 characters of randomness = 62^60 possibilities
4. **Database breaches** - only hashed tokens stored, plain tokens never saved
5. **OAuth account hijacking** - OAuth-only users cannot use remember me

### ⚠️ Important Notes:
1. **HTTPS Required** - The `secure` flag means cookies only work over HTTPS
2. **OAuth Exclusion** - Users who signed up via Google cannot use this feature
3. **Token Rotation** - Consider implementing token rotation on use for enhanced security
4. **Rate Limiting** - Consider rate limiting login attempts
5. **Device Tracking** - User agent and IP are logged for security auditing

## User Experience

### For Email/Password Users:
- ✅ Can check "Remember me" during login
- ✅ Stay logged in for 30 days across browser restarts
- ✅ Can logout from all devices if needed
- ✅ Token automatically refreshed on each use

### For OAuth Users (Google):
- ❌ Cannot use "Remember me" feature
- ✅ Must use Google Sign-In button
- ✅ Browser may remember Google session (handled by Google)

## Testing Checklist

- [ ] Email/password user can login with remember=true
- [ ] Remember token cookie is set with correct attributes
- [ ] User stays authenticated after browser restart
- [ ] OAuth-only user cannot use remember me
- [ ] Logout clears remember token and cookie
- [ ] Logout everywhere revokes all tokens
- [ ] Expired tokens are rejected
- [ ] Invalid tokens are rejected and cookie is cleared
- [ ] Token hash is stored, not plain token
- [ ] Last used timestamp is updated on token use

## Files Modified/Created

### Created:
1. `database/migrations/2025_12_02_000001_create_remember_tokens_table.php`
2. `app/Models/RememberToken.php`
3. `app/Http/Middleware/RememberMe.php`
4. `app/Console/Commands/CleanupExpiredRememberTokens.php`

### Modified:
1. `app/Http/Controllers/Api/AuthController.php`
   - Updated `login()` to handle remember flag
   - Updated `logout()` to revoke remember token
   - Added `logoutEverywhere()` method
   - Added `createRememberToken()` helper method
2. `app/Models/User.php`
   - Added `rememberTokens()` relationship
3. `bootstrap/app.php`
   - Registered `RememberMe` middleware
4. `routes/api.php`
   - Added `/user/logout-everywhere` route

## Migration Instructions

1. Run the migration:
```bash
php artisan migrate
```

2. Clear config cache (if needed):
```bash
php artisan config:clear
php artisan cache:clear
```

3. Test the feature:
```bash
# Login with remember me
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password","remember":true}' \
  -c cookies.txt

# Make authenticated request using cookie
curl http://localhost:8000/api/me -b cookies.txt
```

4. Schedule token cleanup (optional but recommended):
Edit `app/Console/Kernel.php` and add:
```php
$schedule->command('remember-tokens:cleanup')->daily();
```
