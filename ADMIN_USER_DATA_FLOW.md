# Admin/User Data Flow Documentation

## 📋 Table of Contents

1. [User Authentication Flow](#1-user-authentication-flow)
2. [Admin Permission Check Flow](#2-admin-permission-check-flow)
3. [Subscription & NFC Card Management Flow](#3-subscription--nfc-card-management-flow)
4. [Profile & Analytics Data Flow](#4-profile--analytics-data-flow)
5. [Complete System Architecture](#5-complete-system-architecture)

---

## 1. User Authentication Flow

### 1.1 Traditional Password Registration/Login

```
Frontend (Nuxt)                 Backend (Laravel)              Database
│                                │                              │
├─ POST /api/register            │                              │
│  {email, password, ...}        │                              │
│                                ├─> Validate input             │
│                                ├─> Hash password              │
│                                ├─> Create user ──────────────>│ INSERT users
│                                │   - email (unique)           │ - first_name, last_name
│                                │   - password (hashed)        │ - email, password
│                                │   - plan: 'free'             │ - plan: 'free'
│                                │   - is_new_user: true        │ - is_new_user: true
│                                │                              │
│<── 201 Created                 │<─ Return user + token        │
│    {user, token}               │                              │
│                                │                              │
├─ Store in Pinia auth.js        │                              │
│  - user data                   │                              │
│  - auth token                  │                              │
│                                │                              │
├─ POST /api/login               │                              │
│  {email, password}             │                              │
│                                ├─> Find user by email ───────>│ SELECT * FROM users
│                                │                              │   WHERE email = ?
│                                ├─> Verify password (Hash)     │
│                                ├─> Generate Sanctum token ───>│ INSERT personal_access_tokens
│                                ├─> Update last_login_at ─────>│ UPDATE users
│                                │                              │   SET last_login_at = NOW()
│<── 200 OK                      │<─ Return user + token        │
│    {user, token}               │                              │
│                                │                              │
└─ api.client.js sets            │                              │
   Authorization: Bearer {token} │                              │
```

### 1.2 OAuth Social Login (Google/Apple)

```
Frontend                         Backend                         Database              OAuth Provider
│                                │                               │                     │
├─ Click "Google Login"          │                               │                     │
│                                │                               │                     │
├─ Redirect to OAuth ────────────────────────────────────────────────────────────────>│
│                                │                               │                     │ Google Auth
│<─────────────────────────────────────────────────────────────────────────────────────┤ Consent
│  Callback with code            │                               │                     │
│                                │                               │                     │
├─ POST /api/auth/google/callback│                               │                     │
│  {code, ...}                   │                               │                     │
│                                ├─> Exchange code for token ────────────────────────>│
│                                │                               │                     │
│                                │<─ {access_token, id_token, ...}──────────────────────┤
│                                │                               │                     │
│                                ├─> Decode ID token             │                     │
│                                │   Extract: sub, email, name   │                     │
│                                │                               │                     │
│                                ├─> Check existing user ───────>│ SELECT * FROM users
│                                │   WHERE provider='google'     │   WHERE provider='google'
│                                │   AND provider_id=sub         │   AND provider_id=?
│                                │                               │
│                                │   [IF NOT FOUND]              │
│                                ├─> Create new user ───────────>│ INSERT users
│                                │   - email, first_name         │   - email (from OAuth)
│                                │   - provider: 'google'        │   - provider: 'google'
│                                │   - provider_id: sub          │   - provider_id: sub
│                                │   - password: null/random     │   - password: nullable
│                                │   - is_new_user: true         │   - is_new_user: true
│                                │                               │
│                                ├─> Create/Update social_identity>│ INSERT/UPDATE
│                                │   - user_id                   │   social_identities
│                                │   - provider: 'google'        │   - access_token
│                                │   - provider_id, email        │   - refresh_token
│                                │   - access_token, name, avatar│   - name, avatar
│                                │                               │
│                                ├─> Generate Sanctum token ────>│ INSERT personal_access_tokens
│                                │                               │
│<── 200 OK                      │<─ Return user + token         │
│    {user, token}               │                               │
│                                │                               │
└─ Store in Pinia & set header   │                               │
```

### 1.3 Authentication State Maintenance

```
Frontend Request Flow            Backend Middleware             Database
│                                │                              │
├─ API Request with token        │                              │
│  Authorization: Bearer xxx     │                              │
│                                │                              │
│                                ├─ Sanctum Middleware          │
│                                ├─> Validate token ───────────>│ SELECT * FROM
│                                │                              │   personal_access_tokens
│                                │                              │   WHERE token=hash(xxx)
│                                │<─ Get tokenable (User) ──────┤
│                                │                              │
│                                ├─> Set auth()->user()         │
│                                │                              │
│                                ├─ Admin Middleware (optional) │
│                                ├─> Check is_admin = true      │
│                                │   If false → 403 Forbidden   │
│                                │                              │
│<── Response / 401 / 403        │                              │
│                                │                              │
│  [IF 401 Unauthorized]         │                              │
├─ api.client.js interceptor     │                              │
│  - Clear auth state            │                              │
│  - Redirect to /auth/login     │                              │
```

---

## 2. Admin Permission Check Flow

### 2.1 Admin Field Structure (users table)

```
users table columns:
- is_admin (boolean, default: false)
- admin_role (string, nullable)  // 'super_admin', 'admin', 'moderator'
- admin_permissions (json, nullable)  // ['manage_users', 'view_analytics', 'manage_content']
- last_admin_action_at (timestamp, nullable)
```

### 2.2 Backend Permission Check Flow

```
Request: GET /api/admin/users    Backend Controller              Database
│                                │                               │
├─ Authorization: Bearer token   │                               │
│                                │                               │
│                                ├─ Sanctum auth                 │
│                                ├─> Get auth()->user() ────────>│ FROM personal_access_tokens
│                                │                               │   JOIN users
│                                │                               │
│                                ├─ AdminMiddleware              │
│                                │   Check:                      │
│                                │   - $user->is_admin === true  │
│                                │                               │
│                                │   [IF FALSE]                  │
│                                │   ├─> return 403 Forbidden    │
│                                │   │   "Admin access required" │
│                                │                               │
│                                │   [IF TRUE]                   │
│                                │   ├─> Optional: Check role    │
│                                │   │   - admin_role in         │
│                                │   │     ['super_admin','admin']│
│                                │   │                           │
│                                │   └─> Optional: Check perms   │
│                                │       - admin_permissions     │
│                                │         contains 'manage_users'│
│                                │                               │
│                                ├─> Execute controller logic    │
│                                ├─> Update last_admin_action_at>│ UPDATE users
│                                │                               │
│<── 200 OK / 403 Forbidden      │                               │
```

### 2.3 Frontend Route Guard

```
Frontend Router (middleware/admin.ts)
│
├─ Route: /AdminManagement/*
│
├─ Check auth.js state
│  - user.is_admin === true ?
│
│  [IF FALSE]
│  ├─> Redirect to /UserDashboard
│  └─> Show toast: "Admin access required"
│
│  [IF TRUE]
│  └─> Allow access
```

### 2.4 Admin Account Creation Flow

```
Method 1: Direct Database Modification (Initial super admin)
─────────────────────────────────────────────
UPDATE users
SET is_admin = true,
    admin_role = 'super_admin',
    admin_permissions = '["*"]'
WHERE email = 'admin@example.com';


Method 2: Admin Panel Creation (Requires super_admin permission)
─────────────────────────────────────────────
Frontend                         Backend                        Database
│                                │                              │
├─ POST /api/admin/users         │                              │
│  {user_id, role, permissions}  │                              │
│                                │                              │
│                                ├─ Check requester is          │
│                                │   super_admin                │
│                                │                              │
│                                ├─> Update target user ───────>│ UPDATE users
│                                │   SET is_admin = true        │   SET is_admin = true,
│                                │       admin_role = 'admin'   │       admin_role = ?,
│                                │       admin_permissions      │       admin_permissions = ?
│                                │                              │   WHERE id = ?
│<── 200 OK                      │                              │
```

---

## 3. Subscription & NFC Card Management Flow

### 3.1 Subscription Plan Hierarchy

```
Plan Hierarchy (users.subscription_plan):
┌────────────────────────────────────────────┐
│ free     - Basic features, single profile  │
│ basic    - Multiple profiles, basic analytics │
│ premium  - Advanced analytics, custom themes, NFC cards │
│ business - Team features, API access, priority support │
└────────────────────────────────────────────┘
```

### 3.2 Subscription Status Check Flow (Unlock Card Management)

```
Frontend Request                 Backend Controller             Database
│                                │                              │
├─ GET /api/subscription/status  │                              │
│                                │                              │
│                                ├─> Get auth()->user() ───────>│ SELECT * FROM users
│                                │                              │   WHERE id = ?
│                                │                              │
│                                ├─ Check subscription:         │
│                                │   - hasBasicSubscription()   │
│                                │     → plan IN ['basic',      │
│                                │       'premium', 'business'] │
│                                │                              │
│                                │   - hasPremiumSubscription() │
│                                │     → plan IN ['premium',    │
│                                │       'business']            │
│                                │                              │
│<── {                           │<─ Return status              │
│      hasBasic: true,           │                              │
│      hasPremium: false,        │                              │
│      currentPlan: 'basic',     │                              │
│      canAccessCards: true      │                              │
│    }                           │                              │
│                                │                              │
├─ Frontend checks response      │                              │
│  - If !hasBasic → show upgrade│                              │
│  - If hasBasic → unlock UI    │                              │
```

### 3.3 NFC Card Purchase & Binding Flow

```
Step 1: User Purchases NFC Card (Create Order)
─────────────────────────────────
Frontend                         Backend                        Database
│                                │                              │
├─ POST /api/nfc-cards/purchase  │                              │
│  {plan, quantity, address}     │                              │
│                                │                              │
│                                ├─> Create nfc_cards ─────────>│ INSERT nfc_cards
│                                │   - user_id                  │   - card_id (unique)
│                                │   - card_id (generated)      │   - user_id
│                                │   - nfc_card_id: null        │   - nfc_card_id: NULL
│                                │   - status: 'active'         │   - status: 'inactive'
│                                │   - subscription_plan        │   - purchase_date
│                                │   - billing_address          │   - shipping info
│                                │   - purchase_amount          │
│                                │                              │
│                                ├─> Update user subscription ─>│ UPDATE users
│                                │   SET subscription_plan      │   SET subscription_plan,
│                                │       subscription_active    │       subscription_active
│                                │                              │
│<── 201 Created                 │                              │


Step 2: Admin Encodes NFC Card (Physical card)
─────────────────────────────────
Admin Panel                      Backend (Admin)                Database
│                                │                              │
├─ PUT /api/admin/nfc-cards/{id} │                              │
│  {nfc_card_id: 'ABC123XYZ'}    │                              │
│                                │                              │
│                                ├─> Validate admin role        │
│                                │                              │
│                                ├─> Update card ──────────────>│ UPDATE nfc_cards
│                                │   SET nfc_card_id = 'ABC...' │   SET nfc_card_id = ?,
│                                │       status = 'active'      │       status = 'active'
│                                │       shipped_date = NOW()   │   WHERE id = ?
│                                │                              │
│<── 200 OK                      │                              │


Step 3: User Binds NFC Tag to Card
─────────────────────────────────
User scans NFC tag              Backend                         Database
│                                │                              │
├─ Tap NFC → Read nfc_id         │                              │
│                                │                              │
├─ POST /api/nfc-tags/bind       │                              │
│  {nfc_id, card_id}             │                              │
│                                │                              │
│                                ├─> Find nfc_cards ───────────>│ SELECT * FROM nfc_cards
│                                │   WHERE card_id = ?          │   WHERE card_id = ?
│                                │   AND user_id = auth()->id() │   AND user_id = ?
│                                │                              │
│                                ├─> Create/Update nfc_tags ───>│ INSERT/UPDATE nfc_tags
│                                │   - user_id                  │   - nfc_id
│                                │   - nfc_id (unique)          │   - nfc_card_id
│                                │   - nfc_card_id (FK)         │   - user_id
│                                │   - status: 'active'         │   - status: 'active'
│                                │                              │
│<── 200 OK                      │                              │


Relationship:
users ─┬─> nfc_cards (1:N)
       │   - card_id (unique order identifier)
       │   - nfc_card_id (physical card encoding, nullable initially)
       │
       └─> nfc_tags (1:N)
           - nfc_id (chip UID, unique)
           - nfc_card_id (FK -> nfc_cards.nfc_card_id, nullable)
```

---

## 4. Profile & Analytics Data Flow

### 4.1 Profile Creation & Landing Page

```
Frontend                         Backend                        Database
│                                │                              │
├─ POST /api/profiles            │                              │
│  {name, slug, theme, ...}      │                              │
│                                │                              │
│                                ├─> Validate slug unique ─────>│ SELECT * FROM profiles
│                                │                              │   WHERE slug = ?
│                                │                              │
│                                ├─> Create profile ───────────>│ INSERT profiles
│                                │   - user_id                  │   - user_id
│                                │   - slug (unique)            │   - slug
│                                │   - name, theme, settings    │   - name, theme
│                                │   - is_active: true          │   - is_active: true
│                                │                              │
│<── 201 Created                 │                              │
│    {profile, landing_url}      │                              │
│                                │                              │
├─ Display preview link:         │                              │
│   https://app.com/{slug}       │                              │
```

### 4.2 Visitor Accesses Landing Page (Data Collection)

```
Visitor Browser                  Backend (Public)               Database
│                                │                              │
├─ GET /{slug}                   │                              │
│  (No auth required)            │                              │
│                                │                              │
│                                ├─> Find profile ─────────────>│ SELECT * FROM profiles
│                                │   WHERE slug = ? AND         │   WHERE slug = ?
│                                │   is_active = true           │   AND is_active = true
│                                │                              │
│                                ├─> Render landing page        │
│                                │   + social_links             │
│                                │   + company logo, etc        │
│                                │                              │
│                                ├─> Track analytics ──────────>│ INSERT analytics
│                                │   - trackable: Profile       │   - trackable_type: 'profiles'
│                                │   - action: 'profile_view'   │   - trackable_id: profile.id
│                                │   - session_id (from cookie) │   - action: 'profile_view'
│                                │   - visitor_fingerprint      │   - session_id
│                                │   - ip_address, user_agent   │   - visitor_fingerprint
│                                │   - country, city (from IP)  │   - ip, user_agent
│                                │   - device_type, browser     │   - country, city
│                                │   - referrer                 │   - device_type, browser
│                                │   - user_id (profile.user_id)│   - user_id
│                                │                              │
│                                ├─> Optional: Update profile ─>│ UPDATE profiles
│                                │   total_views++              │   SET total_views = total_views + 1,
│                                │   last_viewed_at = NOW()     │       last_viewed_at = NOW()
│                                │                              │
│<── 200 HTML                    │                              │
│    (Landing page rendered)     │                              │
│                                │                              │
├─ Visitor clicks social link    │                              │
│                                │                              │
├─ Click event tracked           │                              │
│  (via JS or redirect)          │                              │
│                                │                              │
│                                ├─> Track link click ─────────>│ INSERT analytics
│                                │   - action: 'link_click'     │   - action: 'link_click'
│                                │   - link_id: social_link.id  │   - link_id
│                                │   - link_type: 'social_link' │   - link_type
│                                │   - session_id (same)        │   - session_id (same)
│                                │   - user_id (owner)          │   - user_id
│                                │                              │
│                                ├─> Update social_links ──────>│ UPDATE social_links
│                                │   click_count++              │   SET click_count = click_count + 1
│                                │                              │
│                                ├─> Update profile CTR ───────>│ UPDATE profiles
│                                │   total_link_clicks++        │   SET total_link_clicks++,
│                                │   ctr_percentage recalc      │       ctr_percentage =
│                                │                              │       (link_clicks/views)*100
│                                │                              │
│<── 302 Redirect to actual URL  │                              │
```

### 4.3 NFC Card Scan Flow (Similar to Profile Visit)

```
User taps NFC tag                Backend                        Database
│                                │                              │
├─ NFC chip redirects to:        │                              │
│  GET /tap/{nfc_id}             │                              │
│                                │                              │
│                                ├─> Find nfc_tags ────────────>│ SELECT * FROM nfc_tags
│                                │   WHERE nfc_id = ?           │   WHERE nfc_id = ?
│                                │   AND status = 'active'      │   AND status = 'active'
│                                │                              │
│                                ├─> Update tap_count ─────────>│ UPDATE nfc_tags
│                                │   tap_count++                │   SET tap_count++,
│                                │   last_tapped_at = NOW()     │       last_tapped_at = NOW()
│                                │                              │
│                                ├─> Track analytics ──────────>│ INSERT analytics
│                                │   - trackable: NfcTag        │   - trackable_type: 'nfc_tags'
│                                │   - action: 'nfc_tap'        │   - action: 'nfc_tap'
│                                │   - session_id, fingerprint  │   - session_id
│                                │   - user_id (tag owner)      │   - user_id
│                                │   - location, device, etc    │   - country, city, device
│                                │                              │
│                                ├─> Get user's profile ───────>│ SELECT * FROM profiles
│                                │   WHERE user_id = tag.user_id│   WHERE user_id = ?
│                                │   AND is_active = true       │   LIMIT 1
│                                │                              │
│<── 302 Redirect                │                              │
│    Location: /{profile.slug}   │                              │
```

### 4.4 User Views Own Analytics Dashboard

```
Frontend (User Dashboard)        Backend API                    Database
│                                │                              │
├─ GET /api/analytics/summary    │                              │
│  ?date_range=last_7_days       │                              │
│                                │                              │
│                                ├─> Get auth user ────────────>│ auth()->user()
│                                │                              │
│                                ├─ Query 1: Website Views ────>│ SELECT COUNT(*) FROM analytics
│                                │   (profile_view + nfc_tap)   │   WHERE user_id = ?
│                                │                              │   AND action IN ('profile_view','nfc_tap')
│                                │                              │   AND created_at >= ?
│                                │                              │
│                                ├─ Query 2: Card Taps ────────>│ SELECT COUNT(*) FROM analytics
│                                │                              │   WHERE user_id = ?
│                                │                              │   AND action = 'nfc_tap'
│                                │                              │
│                                ├─ Query 3: Unique Visitors ──>│ SELECT COUNT(DISTINCT session_id)
│                                │                              │   FROM analytics
│                                │                              │   WHERE user_id = ?
│                                │                              │   AND action = 'profile_view'
│                                │                              │
│                                ├─ Query 4: CTR ──────────────>│ SELECT
│                                │                              │   COUNT(DISTINCT CASE WHEN action='link_click' THEN session_id END) /
│                                │                              │   COUNT(DISTINCT session_id) * 100
│                                │                              │   FROM analytics
│                                │                              │   WHERE user_id = ?
│                                │                              │
│                                ├─ Query 5: Top Links ────────>│ SELECT link_id, link_type, COUNT(*) as clicks
│                                │                              │   FROM analytics
│                                │                              │   WHERE user_id = ? AND action = 'link_click'
│                                │                              │   GROUP BY link_id, link_type
│                                │                              │   ORDER BY clicks DESC
│                                │                              │   LIMIT 5
│                                │                              │
│                                ├─ Query 6: Top Locations ────>│ SELECT country, city, COUNT(*) as visits
│                                │                              │   FROM analytics
│                                │                              │   WHERE user_id = ?
│                                │                              │   GROUP BY country, city
│                                │                              │   ORDER BY visits DESC
│                                │                              │   LIMIT 10
│                                │                              │
│<── 200 OK                      │<─ Aggregate and return       │
│    {                           │                              │
│      websiteViews: 1234,       │                              │
│      cardTaps: 567,            │                              │
│      uniqueVisitors: 890,      │                              │
│      ctr: 12.5,                │                              │
│      topLinks: [...],          │                              │
│      topLocations: [...]       │                              │
│    }                           │                              │
│                                │                              │
├─ Display charts & stats        │                              │
```

---

## 5. Complete System Architecture

### 5.1 Database Relationship Overview

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         CORE USER SYSTEM                                │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                         │
│  ┌───────────────┐         ┌──────────────────┐                        │
│  │ users         │◄────────│ social_identities│                        │
│  │               │  1:N    │                  │                        │
│  │ - id (PK)     │         │ - user_id (FK)   │                        │
│  │ - email       │         │ - provider       │                        │
│  │ - password    │         │ - provider_id    │                        │
│  │ - is_admin    │         │ - access_token   │                        │
│  │ - admin_role  │         └──────────────────┘                        │
│  │ - plan        │                                                     │
│  │ - subscription│                                                     │
│  └───────┬───────┘                                                     │
│          │                                                             │
│          │ 1:N                                                         │
│          │                                                             │
├──────────┼─────────────────────────────────────────────────────────────┤
│          │           PROFILE & CONTENT SYSTEM                          │
│          │                                                             │
│          ▼                                                             │
│  ┌───────────────┐         ┌──────────────────┐                        │
│  │ profiles      │         │ social_links     │                        │
│  │               │◄────────│                  │                        │
│  │ - id (PK)     │  1:N    │ - profile_id (FK)│                        │
│  │ - user_id (FK)│         │ - platform       │                        │
│  │ - slug (unique│         │ - url            │                        │
│  │ - name, theme │         │ - click_count    │                        │
│  │ - is_active   │         └──────────────────┘                        │
│  │               │                                                     │
│  │ - total_views │         ┌──────────────────┐                        │
│  │ - unique_...  │         │ custom_links     │                        │
│  │ - ctr_%       │         │ (placeholder)    │                        │
│  └───────┬───────┘         └──────────────────┘                        │
│          │                                                             │
├──────────┼─────────────────────────────────────────────────────────────┤
│          │            NFC CARD SYSTEM                                  │
│          │                                                             │
│          ├─ 1:N ──►┌───────────────┐                                  │
│          │         │ nfc_cards     │                                  │
│          │         │               │                                  │
│          │         │ - id (PK)     │                                  │
│          │         │ - user_id (FK)│                                  │
│          │         │ - card_id     │                                  │
│          │         │ - nfc_card_id │◄──┐                              │
│          │         │ - status      │   │ String FK                    │
│          │         │ - subscription│   │ (not numeric FK)             │
│          │         └───────────────┘   │                              │
│          │                             │                              │
│          ├─ 1:N ──►┌───────────────┐   │                              │
│          │         │ nfc_tags      │   │                              │
│          │         │               │───┘                              │
│          │         │ - id (PK)     │                                  │
│          │         │ - user_id (FK)│                                  │
│          │         │ - nfc_id      │                                  │
│          │         │ - nfc_card_id │ (nullable)                       │
│          │         │ - tap_count   │                                  │
│          │         └───────┬───────┘                                  │
│          │                 │                                          │
├──────────┼─────────────────┼──────────────────────────────────────────┤
│          │  ANALYTICS      │  (Polymorphic trackable)                 │
│          │                 │                                          │
│          ▼                 ▼                                          │
│  ┌─────────────────────────────────────┐                             │
│  │ analytics                           │                             │
│  │                                     │                             │
│  │ - id (PK)                           │                             │
│  │ - trackable_type (polymorphic)      │ ─┬─► 'profiles'            │
│  │ - trackable_id   (polymorphic)      │  └─► 'nfc_tags'            │
│  │ - user_id (FK, cached for speed)    │                             │
│  │ - action (profile_view, nfc_tap,    │                             │
│  │          link_click, etc)           │                             │
│  │ - session_id (for UV tracking)      │                             │
│  │ - visitor_fingerprint               │                             │
│  │ - link_id, link_type (for CTR)      │                             │
│  │ - ip_address, user_agent            │                             │
│  │ - country, city (geo-location)      │                             │
│  │ - device_type, browser, platform    │                             │
│  │ - data (json - extra metadata)      │                             │
│  └─────────────────────────────────────┘                             │
│                                                                       │
├───────────────────────────────────────────────────────────────────────┤
│  SECURITY & AUTH                                                      │
│                                                                       │
│  ┌────────────────────┐      ┌──────────────────┐                    │
│  │personal_access_     │      │ sessions         │                    │
│  │       tokens        │      │                  │                    │
│  │ (Sanctum)           │      │ - id (PK)        │                    │
│  │                     │      │ - user_id (FK)   │                    │
│  │ - tokenable_id      │      │ - payload        │                    │
│  │ - token (unique)    │      │ - last_activity  │                    │
│  │ - abilities         │      └──────────────────┘                    │
│  └────────────────────┘                                               │
│                                                                       │
│  ┌────────────────────┐      ┌──────────────────┐                    │
│  │ password_resets    │      │ cache / cache_   │                    │
│  │                     │      │      locks       │                    │
│  │ - user_id (FK)     │      │ (key-value store)│                    │
│  │ - token_hash       │      └──────────────────┘                    │
│  │ - expires_at       │                                               │
│  └────────────────────┘                                               │
│                                                                       │
├───────────────────────────────────────────────────────────────────────┤
│  CONTENT & SUPPORT                                                    │
│                                                                       │
│  ┌────────────────────┐      ┌──────────────────┐                    │
│  │ legal_documents    │      │ chatbot_questions│                    │
│  │                     │      │                  │                    │
│  │ - type (unique)    │      │ - question       │                    │
│  │ - content          │      │ - answer         │                    │
│  │ - version          │      │ - keywords (json)│                    │
│  │ - updated_by (FK)  │      │ - helpful_count  │                    │
│  └────────────────────┘      └─────────┬────────┘                    │
│                                        │ 1:N                         │
│                              ┌─────────▼────────┐                    │
│                              │ chatbot_feedback │                    │
│                              │                  │                    │
│                              │ - question_id(FK)│                    │
│                              │ - user_question  │                    │
│                              │ - rating         │                    │
│                              └──────────────────┘                    │
└───────────────────────────────────────────────────────────────────────┘
```

### 5.2 Key Data Flow Summary

#### Admin Operations Flow

```
1. Admin Login
   → Sanctum token generated
   → is_admin checked in middleware
   → Access /AdminManagement routes

2. Admin manages users
   → Can set is_admin, admin_role, admin_permissions
   → Can view all analytics (WHERE user_id IN ...)
   → Can encode NFC cards (SET nfc_card_id)

3. Admin creates content
   → legal_documents (updated_by = admin.id)
   → chatbot_questions (managed by admin)
```

#### User Operations Flow

```
1. User Registration/Login
   → Create users record
   → Generate Sanctum token
   → Optional: Create social_identities (OAuth)

2. User creates Profile
   → profiles.slug → landing page URL
   → social_links added
   → is_active controls visibility

3. User purchases NFC card
   → nfc_cards record created
   → Admin encodes → nfc_card_id set
   → User binds nfc_tags → links to nfc_card_id

4. Visitor accesses content
   → Landing page/{slug} → track analytics
   → NFC tap → track analytics
   → Link click → track analytics

5. User views dashboard
   → Aggregate analytics WHERE user_id = auth()->id()
   → Show Website Views, Card Taps, UV, CTR, Top Links, Locations
```

#### Analytics Metrics Calculation (Based on Enhanced analytics Table)

```sql
-- Website Views (Profile views + NFC taps)
SELECT COUNT(*) FROM analytics
WHERE user_id = ?
  AND action IN ('profile_view', 'nfc_tap')
  AND created_at >= ?;

-- Card Taps only
SELECT COUNT(*) FROM analytics
WHERE user_id = ?
  AND action = 'nfc_tap';

-- Unique Visitors
SELECT COUNT(DISTINCT session_id) FROM analytics
WHERE user_id = ?
  AND action = 'profile_view';

-- Click-through Rate
SELECT
  COUNT(DISTINCT CASE WHEN action='link_click' THEN session_id END) * 100.0 /
  NULLIF(COUNT(DISTINCT CASE WHEN action='profile_view' THEN session_id END), 0) AS ctr
FROM analytics
WHERE user_id = ?;

-- Top Performing Links
SELECT
  link_id,
  link_type,
  COUNT(*) as click_count,
  sl.title as link_title
FROM analytics a
LEFT JOIN social_links sl ON a.link_id = sl.id AND a.link_type = 'social_link'
WHERE a.user_id = ?
  AND a.action = 'link_click'
GROUP BY link_id, link_type
ORDER BY click_count DESC
LIMIT 5;

-- Top Locations
SELECT
  country,
  city,
  COUNT(*) as visit_count
FROM analytics
WHERE user_id = ?
GROUP BY country, city
ORDER BY visit_count DESC
LIMIT 10;
```

---

## 📝 Implementation Recommendations

### Required Modifications (Based on Your Requirements)

1. **Analytics Table Enhancement** (New migration needed)

   - Add `session_id` (for Unique Visitors)
   - Add `visitor_fingerprint` (for cross-session tracking)
   - Add `link_id`, `link_type` (for CTR & Top Links)
   - Add `user_id` (cached, for faster queries)
   - Add indexes to optimize query performance

2. **User Model Add Subscription Helper Methods**

   ```php
   // backend/app/Models/User.php
   public function hasBasicSubscription() {
       return in_array($this->subscription_plan, ['basic', 'premium', 'business']);
   }

   public function hasPremiumSubscription() {
       return in_array($this->subscription_plan, ['premium', 'business']);
   }
   ```

3. **Frontend Interceptor Fix** (Completed)

   - 401 handling no longer causes infinite loop
   - Clear password fields to prevent leakage

4. **Landing Page Tracking Code**
   - Generate/read session_id on visit
   - Calculate visitor_fingerprint (browser + screen + timezone hash)
   - Send analytics event for each link click (including link_id)

Would you like me to continue implementing these (create migrations, update Model, add Controller logic)?
