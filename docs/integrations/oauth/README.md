# OAuth Integration Guide

This project supports Social Login via **Google** and **Apple** using Laravel Socialite and Sanctum.

---

## 1. Overview

Users can sign up or log in using their existing social accounts. This removes the need for a password and speeds up onboarding.

**Supported Providers:**

- **Google**: Web and Mobile supported.
- **Apple**: Required for iOS App Compliance.

---

## 2. Configuration

### Backend (`.env`)

You must configure the Client IDs and Secrets obtained from the developer consoles.

```env
# Google
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI=http://localhost:8000/api/auth/google/callback

# Apple
APPLE_CLIENT_ID=your-apple-service-id
APPLE_CLIENT_SECRET=your-apple-private-key-jwt
APPLE_REDIRECT_URI=http://localhost:8000/api/auth/apple/callback
```

### Frontend (`.env`)

Used for determining which buttons to show.

```env
NUXT_PUBLIC_GOOGLE_CLIENT_ID=your-google-client-id
NUXT_PUBLIC_APPLE_CLIENT_ID=your-apple-client-id
```

---

## 3. Authentication Flow

1.  **User Clicks Button**: Frontend redirects user to `/api/auth/{provider}/redirect`.
2.  **Laravel Redirects**: Backend uses Socialite to send user to Google/Apple.
3.  **User Approves**: User logs in at provider and grants permission.
4.  **Callback**: Provider redirects back to `/api/auth/{provider}/callback`.
5.  **User Creation/Lookup**:
    - Backend checks `social_identities` table.
    - If found: Logs user in.
    - If not found: checks `users` table for matching email.
      - If email exists: Links account to social identity.
      - If new: Creates new User and links identity.
6.  **Token Issue**: Backend generates a Sanctum Token.
7.  **Final Redirect**: Backend redirects to Frontend Dashboard with token in URL (or cookie).

---

## 4. Database Schema

We use a separate table `social_identities` to allow one user to have multiple login methods (e.g., Password + Google).

**Table: `social_identities`**

- `id`: Primary Key
- `user_id`: Foreign Key to `users`
- `provider`: `google` or `apple`
- `provider_id`: Unique ID from the provider (e.g., Google Sub ID)
- `created_at`: Component timestamps

---

## 5. Troubleshooting

**"Redirect URL Mismatch"**

- Ensure the `REDIRECT_URI` in `.env` matches exactly what is whitelisted in Google Cloud Console or Apple Developer Portal.

**"Stateless Error"**

- Ensure session domains are configured correctly in `config/session.php` if using cookies, or ensure the state parameter is being passed back correctly.
