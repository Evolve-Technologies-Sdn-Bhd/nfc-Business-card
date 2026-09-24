# NFCGo System Improvement Plan

**Versi**: 1.0  
**Tarikh Audit**: 2026-09-24  
**Skop**: Full system audit (UI/UX, Architecture, Data Flow, Missing Links)

---

## Table of Contents

1. [BAHAGIAN 1: System Overview - Apa Yang Ada Sekarang](#bahagian-1-system-overview---apa-yang-ada-sekarang)
2. [BAHAGIAN 2: Critical Missing Links & Broken Flows](#bahagian-2-critical-missing-links--broken-flows-priority-0---fix-segera)
3. [BAHAGIAN 3: UI Design System & Consistency Improvements](#bahagian-3-ui-design-system--consistency-improvements-priority-1)
4. [BAHAGIAN 4: User Experience (UX) Flow Gaps](#bahagian-4-user-experience-ux-flow-gaps-priority-2)
5. [BAHAGIAN 5: Backend Architecture - Data Consistency & Gaps](#bahagian-5-backend-architecture---data-consistency--gaps-priority-1)
6. [BAHAGIAN 6: User Dashboard - Per-Module Improvements](#bahagian-6-user-dashboard---per-module-improvements-priority-2)
7. [BAHAGIAN 7: Admin Panel - Per-Module Improvements](#bahagian-7-admin-panel---per-module-improvements-priority-3)
8. [BAHAGIAN 8: Landing Page - Content & Conversion Gaps](#bahagian-8-landing-page---content--conversion-gaps-priority-3)
9. [BAHAGIAN 9: Security & Hardening](#bahagian-9-security--hardening-priority-1)
10. [BAHAGIAN 10: Implementation Roadmap & Priority Matrix](#bahagian-10-implementation-roadmap--priority-matrix)

---

# BAHAGIAN 1: System Overview - Apa Yang Ada Sekarang

## 1.1 Technology Stack

| Layer | Technology | Notes |
|-------|-----------|-------|
| Frontend | Nuxt 3 (Static SPA), Vue 3 Composition API, Pinia | SSR disabled (`ssr: false`) |
| Styling | Tailwind CSS 3 + CSS Variables for theming | Ada component layer (`.btn`, `.card`, `.input` etc) |
| Icons | Heroicons via `@iconify/vue` | User prefer SVG icons only (no emoji in UI) |
| Backend | Laravel 12 (PHP >= 8.4), Sanctum Auth | Monolith API architecture |
| Cache/Queue | Redis 7.x | Jobs wujud (GenerateInvoiceJob) tapi worker monitoring tak ada |
| Payment Gateway | Fiuu (Razer) via webhooks | Multiple rails: Card, FPX, E-Wallet, Manual Bank Transfer |
| Storage | Local `storage/app/public` + symlink | FileUploadService handles image uploads |
| Integrations | n8n (card template processing), Google Form (Contact), Apple/Google OAuth | |
| Database | MySQL/MariaDB via migrations | 40+ tables, 46 migration files |

## 1.2 User Personas & Tier Plans

| Tier | Persona | Key Features |
|------|---------|-------------|
| **Free** | Individual trial users | 1 digital profile, 5 social links, Basic analytics |
| **Basic** | Small business / freelancers | Most Popular plan, Physical NFC card support |
| **Premium** | Professionals, Power users | Detailed analytics, Custom themes, Unlimited links |
| **Business** | SME / Corporate teams | Employee management, Quota system, Activity logs, Team analytics |
| **Admin** | Platform Super Admin / Staff | Full CRUD across all entities, Reporting |

## 1.3 Frontend Module Structure

```
frontend/
├── layouts/
│   ├── UserDashboard.vue      ── Sidebar nav plan-aware + notifications dropdown
│   └── AdminManagement.vue    ── Separate admin layout with topbar + sidebar
├── pages/
│   ├── index.vue              ── Landing page (modular components)
│   ├── profile/[id].vue       ── Public NFC profile landing page (glassmorphism UI)
│   ├── Homepage/index.vue     ── Landing page route alias
│   ├── UserAccount/           ── Login, Register, ForgotPass, ResetPass
│   ├── UserDashboard/         ── 14+ pages (CardMgmt, Analytics, PlanSelection,
│   │                             Settings, Notifications, Payment, ProfileBuilder x4 plans)
│   ├── AdminManagement/       ── 12+ pages (Dashboard, Users, BusinessUsers,
│   │                             NFCCards, CardTemplates, Invoices, PriceMgmt,
│   │                             LegalDocs, ProfileBuilderDesign, Stats,
│   │                             Notifications, Feedback)
│   ├── auth/callback.vue      ── OAuth redirect handler
│   ├── payment/               ── result.vue, status.vue (Fiuu callback pages)
│   └── dashboard/invoices/    ── User invoice listing
├── components/ (50+ files)
│   ├── homepage/ (9 modules)  ── Navbar, Hero, Features, HowItWorks, WhoUses,
│   │                             Gallery, Testimonials, Pricing, CTA, Footer
│   ├── modals/ (4)            ── BusinessRequest, Contact, Feedback, LegalDoc
│   └── shared (37+)           ── ChatbotWidget, LayoutDesigner, FiuuPaymentForm,
│                                 PaymentMethodSelector, InvoiceDownload/Preview, etc.
├── stores/ (5)                ── auth, profile, nfc, nfcCard, admin
├── composables/ (10)          ── chatbot, invoices, notifications, payment,
│                                 phoneFormat, planPrices, profileData, theme, toast
├── plugins/ (4)               ── api.client.js (axios + interceptors),
│                                 auth.client.js, router.client.js, toast.client.js
└── middleware/ (5)            ── admin, auth, guest, business-plan-guard, trailing-slash
```

## 1.4 Backend Module Structure

```
backend/app/
├── Models/ (32 files)         ── Core entities + relationships
├── Http/Controllers/
│   └── Api/ (28 controllers)  ── Auth, Profile, NFC, Payment, Admin, Business, etc.
├── Services/ (7)              ── FileUpload, Notification, Payment, SocialAuth,
│                                 Token, Invoice, AppleClientSecret
├── Jobs/ (1)                  ── GenerateInvoiceJob
├── Mail/ (2)                  ── PasswordResetMail, BusinessPlanRequestMail
├── Notifications/ (4)         ── InvoiceGenerated, PassChanged, ResetPass, AdminBulkOrder
├── Middleware/ (7)            ── Admin, Authenticate, HandleCors, LogActivity, etc.
└── Console/Commands/ (2)      ── CleanupExpiredRememberTokens, CreateMissingProfiles
```

---

# BAHAGIAN 2: Critical Missing Links & Broken Flows (Priority 0 - Fix Segera)

**Definisi**: Flow yang functionally broken / tidak jalan langsung / missing endpoint / security risk.

## 2.1 Chatbot Widget: API Endpoint Tidak Wujud [CRITICAL]

**Masalah**: Widget [ChatbotWidget.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/components/ChatbotWidget.vue) dan composable [useChatbot.js](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/composables/useChatbot.js) memanggil endpoint `POST /chatbot/ask` untuk jawab soalan user.

**Bukti Missing Route**: Semakan [api.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/routes/api.php#L271) line 271, hanya **satu** public chatbot route wujud:
```php
Route::post('/chatbot/feedback', [ChatbotController::class, 'submitFeedback']);
```
Route `/chatbot/ask` **TIADA**. Method `askQuestion()` dalam composable selamanya akan return error.

**Selain itu**:
- `GET /chatbot/questions` dipanggil `getPublicQuestions()` (untuk FAQ) juga **TIADA** dalam api.php
- Table `chatbot_questions` wujud (dari migration + seeder), tapi tak ada controller method yang expose
- n8n webhook untuk chatbot (`https://n8n.jiosgroup.com/webhook/e529b3a4-d09d-45d6-8de5-01cc6885bcb7/chat`) telah configured tapi tiada backend proxy call

**Fix Required**:
1. Tambah 2 routes public dalam `api.php`:
   - `POST /chatbot/ask` → call n8n webhook, fetch dari `chatbot_questions` table jika match local, fallback ke AI
   - `GET /chatbot/questions` → return published FAQ questions

## 2.2 Subscription Data: Dual Source of Truth [CRITICAL]

**Masalah**: Sistem menyimpan subscription status di **dua tempat berbeza**:

| Lokasi | Field | Digunakan oleh |
|--------|-------|---------------|
| `users` table | `subscription_plan`, `subscription_start_date`, `subscription_end_date`, `subscription_active` | [User.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Models/User.php#L112-L119) `hasPremiumSubscription()`, UI auth store redirect logic |
| `subscriptions` table | Seluruh table dengan `status`, `next_billing_date`, `provider_subscription_id`, SoftDeletes | [Subscription.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Models/Subscription.php) + PaymentController + DunningLog |

**Risk**: Bila payment webhook update `subscriptions.status`, column `users.subscription_active` tak auto sync. User masih boleh/kehilangan access premium features tanpa sebab.

**Fix Required**:
1. Pilih **single source of truth** — recommendation: `subscriptions` table (lebih lengkap, ada soft deletes, dunning logs, proper lifecycle)
2. Buat Accessor pada User model yang query latest subscription dari subscriptions table
3. Deprecate `users.subscription_plan`, `users.subscription_active` columns (gunakan view layer yang map ke subscriptions table)
4. Tambah job nightly sync untuk consistency check

## 2.3 No Email Verification After Registration [HIGH]

**Masalah**: [AuthController@register](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Controllers/Api/AuthController.php) create user terus dan issue token tanpa verify email. Boleh register email palsu secara spam.

**Dalam User Model**: Column `email_verified_at` wujud (line 56 casting), tapi tak pernah disentuh.

**Laravel Sanctum**: Tak enforce `verified` middleware.

**Fix Required**:
1. Send verification email selepas register (Laravel built-in `MustVerifyEmail` contract)
2. Tambah middleware `verified` pada protected routes
3. Resend verification email button pada halaman "Account Pending Verification"
4. Rate limit resend (max 3 email / jam)

## 2.4 2FA Feature: API Fields Exist But No Frontend UI [HIGH]

**Masalah**: Dalam [User model](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Models/User.php#L35-L36):
```php
'two_factor_enabled',
'two_factor_secret',
```
Dalam [auth store](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/stores/auth.js#L172-L198) method `verify2FA()` wujud.

TAPI:
- Settings page TIADA tab "Security" untuk enable/disable 2FA
- Login page TIADA 2FA step selepas email/password sah
- QR code scan UI tak wujud
- Recovery codes backup flow tak wujud

## 2.5 Analytics Charts Semua Placeholder [CRITICAL]

**Masalah**: Kesemua pages Analytics (`Analytics.vue`, `PremiumAnalytics.vue`, `BusinessAnalytics.vue`) hanya display **empty placeholder div** dengan icon lock atau "chart will be displayed here" tanpa actual data visualisation.

Contoh dalam [Analytics.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/UserDashboard/Analytics.vue#L136-L187):
- "Taps Over Time" → hanya div bg-secondary-50, tiada chart
- "Device Types" → hanya div kosong
- "Top Locations" → placeholder
- "Link Clicks" → placeholder

Backend AnalyticsController ada endpoint:
- `GET /analytics/overview`
- `GET /analytics/profile`
- `GET /analytics/user/overview`
- `GET /analytics/business/overview` (dengan export CSV!)

**Fix Required**: Integrate Chart.js atau ECharts pada semua analytics pages.

## 2.6 Profile Preview Modal Still Placeholder [MEDIUM]

**Masalah**: Dalam [UserDashboard.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/layouts/UserDashboard.vue#L384-L434) line 424-429, butang "Quick Actions → Preview Profile" buka modal dengan text:
```
Profile preview will be displayed here
```
Content modal tak dikaitkan dengan actual profile data user.

**Fix**: Embed iframe ke `/profile/{id}` atau render mini version dalam modal.

---

# BAHAGIAN 3: UI Design System & Consistency Improvements (Priority 1)

## 3.1 Sidebar UserDashboard: Remove Icons (User Preference)

**Pengesahan User Profile**: User secara explicit menyatakan:
> "lebih gemar menyembunyikan ikon pada item menu untuk penampilan yang lebih bersih"

**Current State**: [UserDashboard.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/layouts/UserDashboard.vue#L43-L71) setiap nav item ada Icon component:
```vue
<Icon :name="item.icon" class="mr-3 h-5 w-5 flex-shrink-0" ... />
```

**Required Change**:
- Remove `<Icon>` block dari nav loop dalam UserDashboard.vue
- Notification badges dan active state indicator (border-left) KEKAL
- Profile section avatar + notifications dropdown + logout KEKAL icons (bukan nav menu item)
- **Sama apply pada AdminManagement sidebar** [AdminManagement.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/layouts/AdminManagement.vue#L33-L63)

## 3.2 Profile Page [id].vue: Refactor Inline Styles + Remove Emoji [CRITICAL USER PREFERENCE]

**User Profile Explicit Requirements**:
1. "Dilarang menggunakan emoji dalam antara muka; gunakan ikon SVG sebagai ganti"
2. "Premium, berwibawa, minimalis, bersih, dan profesional"
3. "Warna matte, fon yang lebih besar, dan jarak (spacing) yang lebih luas"
4. "Mengelakkan warna neon yang menyilau atau latar belakang hitam pekat (OLED)"

**Current Violations dalam [id].vue**:
- Line 145, 185: Butang ada emoji `←` dan `✏️` (butang Home & Edit Profile)
- Line 204: Preview banner ada emoji `👁️`
- Line 222, 237, 280: Loading/error states guna emoji `⏳`, `📝`, `😢`
- 90% page styling guna `:style="{ ... }"` inline object dengan hardcoded hex colors (tidak guna Tailwind classes @apply atau design system CSS vars)
- Background color hardcoded `#1a1f3a` (OLED-hitam-ish) — user tak suka black pekat
- Gradient orbs ada `rgba(102, 126, 234, ...)`, `rgba(240, 147, 251, ...)` — purple/pink neon-ish

**Fix Plan (Zora Pro Alignment)**:
1. Replace SEMUA emoji dengan Heroicons SVG yang setara:
   - `←` → `heroicons:arrow-left`
   - `✏️` → `heroicons:pencil-square`
   - `👁️` → `heroicons:eye`
   - `⏳` → `heroicons:clock` (dengan animate-spin)
   - `📝` → `heroicons:document-text`
   - `😢` → `heroicons:x-circle`
2. Buat CSS class untuk semua reusable style blocks (btn-floating, card-glass, gradient-bg) dan move ke component layer dalam main.css
3. Integrate dengan global ThemeSwitcher (guna `--theme-primary-*` CSS vars, bukan hardcode)
4. Tukar palette kepada Matte Navy + Matte Teal sesuai Zora Pro spec dari user profile
5. Refactor responsive inline style (isMobile checks) kepada Tailwind `md:` / `lg:` breakpoints

## 3.3 Design Tokens Inconsistency

**Masalah**: 3 layer UI dengan styling approach berbeza:

| Location | Approach | Consistency |
|----------|----------|-------------|
| Landing page + Auth pages | Guna Tailwind + component classes (.btn, .card, .input) | ✅ Good, follow design system |
| UserDashboard + AdminManagement | Campur Tailwind + sedikit inline | ⚠️ Acceptable |
| profile/[id].vue | 95% inline object styles + hardcoded colors | ❌ Broken |

**Fix**: Semua pages WAJIB guna:
1. Color: `bg-primary-600`, `text-secondary-700` via [tailwind.config.js](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/tailwind.config.js) CSS variables
2. Components: Guna class `.btn-primary`, `.card`, `.input` yang dah define dalam [main.css](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/assets/css/main.css#L74-L188)
3. **HILANGKAN hardcoded hex** kecuali untuk profile custom color user sendiri (yang configurable by user)

## 3.4 Zora Pro Theme Rollout

**User preference confirmed**: Navy + Matte Teal palette untuk theme default.

**Tasks**:
1. Override `:root` CSS variables dalam [main.css](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/assets/css/main.css#L8-L31):
   - Primary palette → Matte Navy (deep blue-gray, bukan ungu hologram)
   - Accent → Matte Teal (#5D8C87 ish)
   - Neutral → Warm grays bukan cool slate
2. Update [useTheme.js](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/composables/useTheme.js) untuk tambah Zora Pro sebagai theme default
3. Pastikan [ThemeSwitcher.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/components/ThemeSwitcher.vue) ada options: Zora Pro (default), Dark, Holographic Purple
4. Landing page hero gradient: tukar daripada `from-primary-50 to-secondary-100` kepada matte gradient navy→teal

---

# BAHAGIAN 4: User Experience (UX) Flow Gaps (Priority 2)

## 4.1 No Cancel / Downgrade Subscription Flow

**Masalah**: Dalam PlanSelection, user boleh upgrade Free→Basic→Premium→Business, tapi TIADA mechanism untuk:
- Cancel subscription (still pay monthly tapi tak guna)
- Downgrade (Premium→Basic, Business→Premium)
- Pause subscription (cuti 1 bulan)

**UI Missing**:
- Settings page TIADA section "Subscription & Billing"
- Button "Cancel Plan" / "Change Plan" (downgrade option)
- Cancellation reason survey / save-win flow
- Prorated refund calculation display

**Backend Missing**:
- `Subscription@cancel` method wujud (line 154-163 Subscription model) tapi TIADA API endpoint yang call
- TIADA `POST /subscription/cancel`, `POST /subscription/change-plan` dalam api.php
- Proration logic tak wujud

## 4.2 Profile Builder: No Autosave

**Masalah**: User edit banyak fields dalam ProfileBuilder (satu page panjang dengan multiple tabs untuk general/design sections, social links, portfolio, gallery, services). Jika browser crash / tab tertutup sebelum click "Save Profile", SEMUA changes hilang.

**Fix Plan**:
1. Add debounced autosave (3 seconds selepas last keystroke)
2. Save indicator di header: "Saving..." → "All changes saved ✓"
3. Warning dialog sebelum navigate away / close tab jika ada unsaved changes (`onBeforeRouteLeave` + `beforeunload`)
4. LocalStorage draft backup sementara network offline

## 4.3 Settings Page: Missing Tabs

**Current [Settings.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/UserDashboard/Settings.vue) tabs**:
- ✅ Personal Information (first/last name, email, phone, avatar)
- ✅ Profile URLs (list of NFC card links)

**Missing tabs**:
1. **Security** tab:
   - Change password form (old password → new password → confirm)
   - 2FA Toggle with QR code scan + recovery codes
   - Active sessions list (devices logged in, IP, last seen, logout option)
   - "Logout Everywhere" button (endpoint API dah wujud: `POST /user/logout-everywhere`)
   - Linked OAuth accounts (Google/Apple) — endpoint dah wujud: `GET /user/linked-accounts`

2. **Preferences** tab:
   - Language selector (BM/BI) — user strict about single language UI no mixing
   - Default theme (Zora Pro / Dark)
   - Email notifications toggle (marketing, product updates, security alerts)
   - Push notifications permission request (browser push API)

3. **Subscription & Billing** tab (dari §4.1):
   - Current plan summary + next billing date
   - Change plan (upgrade/downgrade) matrix
   - Cancel / Pause subscription
   - Saved payment methods (add/remove card)
   - Invoice history shortcut

## 4.4 No Saved Payment Methods Management

**Masalah**: Model `PaymentMethod`, Controller `PaymentMethodController`, route `GET /payment/rails` semua wujud. Tapi user takde tempat save kad kredit untuk auto-renew.

**Settings → Billing**: Add "Payment Methods" section. UI senarai saved cards (last 4 digits, expiry, card brand), button "Add New Card" via Fiuu tokenization.

## 4.5 Notification Preferences Missing

**User punya notification system cukup lengkap (dapat toast, list page, mark all read) tapi user takleh control jenis notification yang dia nak terima.**

**Missing**: Table untuk notification_preferences per user (email vs in-app toggle untuk categories: payment, subscription, security, marketing, system).

---

# BAHAGIAN 5: Backend Architecture - Data Consistency & Gaps (Priority 1)

## 5.1 Scheduled Jobs / Cron Tasks List

**Ada 2 Console Command**:
- `CleanupExpiredRememberTokens` ✅
- `CreateMissingProfiles` ✅

**Missing Critical Scheduled Tasks (perlu buat Command + schedule dalam Kernel)**:

| Task | Frequency | Purpose |
|------|-----------|---------|
| `ProcessSubscriptionRenewals` | Daily | Charge cards for `next_billing_date <= today()` subscriptions, create Transactions & Invoices, trigger dunning jika fail |
| `SendSubscriptionExpiryReminders` | Daily | 7 hari, 3 hari, 1 hari sebelum expiry — email reminder users |
| `SendTrialEndingReminders` | Daily | Trial users last 3 days reminder |
| `DunningRetryProcess` | Twice daily | Failed payment retry schedule (max 3 attempt sebelum suspend) |
| `CleanupOldAnalytics` | Monthly | Archive analytics data >6 bulan ke separate archive table (performance) |
| `CleanupSoftDeletedRecords` | Monthly | Permanently delete records dari soft-deletes yang dah >90 hari |
| `SyncSubscriptionUserColumns` | Hourly | §2.2 interim fix - sync users.subscription_* columns ke subscriptions table sehingga kita migrate fully |

## 5.2 Admin Audit Log Tidak Lengkap

**Masalah**: Table `activity_logs` wujud dan LogActivity middleware wujud, tapi hanya track employee activities untuk Business plan. TIADA audit trail untuk:
- Admin actions: Create user, delete user, approve order, change prices, update legal docs
- Permission changes: Add admin, remove admin, change permissions
- Data export actions: Who exported what and when

**Fix**: Extend LogActivity middleware untuk cover semua Admin routes, log:
- Actor (admin user ID)
- Action (create/update/delete/export/approve/reject)
- Entity type + entity ID
- Old values vs new values JSON (untuk update)
- IP address + user agent
- Tambah Admin page "System Audit Log" untuk view

## 5.3 Database Index Audit

**Potensi slow queries untuk data yang akan berkembang**:
- `analytics` table: Missing composite index `(trackable_type, trackable_id, created_at)` untuk query time-range analytics
- `nfc_cards` table: Index `nfc_card_id` wujud (unique) tapi `user_id + status` composite index tak wujud (untuk query "user dapat semua active cards")
- `notifications` table: Missing `user_id + is_read + created_at DESC` index (list notifications paginated)
- `social_links` table: Missing `landing_page_id + is_active + sort_order` composite

## 5.4 API Response Pagination Inconsistent

**Masalah**: Beberapa endpoints return semua data tanpa pagination, yang akan slow bila data berkembang:
- `GET /notifications` - perlu page per 20
- `GET /analytics/business/overview` - aggregate OK tapi time series breakdown perlu chunked
- `GET /admin/users` - pagination WIP (component AdminPagination.vue wujud, tapi tak semua admin controller integrate)

---

# BAHAGIAN 6: User Dashboard - Per-Module Improvements (Priority 2)

## 6.1 UserDashboard Layout (Global)

| # | Issue | Current | Required |
|---|-------|---------|----------|
| 1 | Sidebar nav icons | Shown | Hidden (§3.1 user preference) |
| 2 | Profile Preview Modal | Placeholder text | Render actual profile (§2.6) |
| 3 | Subscription upgrade banner bottom sidebar | Fixed bottom of sidebar | Move ke Settings / Plan pages only, not every page (too spammy on every navigation) |
| 4 | Breadcrumbs / Page titles | Tak konsisten | Add header component dengan page title + description dinamik dari route meta |
| 5 | Active route highlight for Analytics | Workaround via `.includes('/Analytics')` | Use route meta tags for cleaner logic |

## 6.2 Card Management

| # | Issue | Fix |
|---|-------|-----|
| 1 | Order New Card modal only started (truncated view) | Complete onboarding step 2 (card info) + step 3 (payment) full flow |
| 2 | Shipping status tracking | Tiada UI show tracking number | Bila `tracking_number` populated dari admin, display clickable link ke courier tracking page |
| 3 | Filter / search for user with >5 cards | Tiada search/filter box | Add simple search box by owner name / card ID + status filter chips |
| 4 | Card design preview | Currently text-only | Display actual card template image thumbnail if available |

## 6.3 Analytics (Detailed Breakdown)

Backend endpoints semua dah ready. Frontend tasks:

1. Install `chart.js` + `vue-chartjs`
2. KPI cards (ada) tapi:
   - `tapGrowth`, `visitorGrowth`, `engagementGrowth` hardcoded/dummy → bind dari API actual period comparison (current vs previous period delta)
3. "Taps Over Time" → Line chart (daily series, date filter 7d/30d/90d/1y)
4. "Device Types" → Pie / Donut chart (mobile vs desktop vs tablet)
5. "Top Locations" → Bar chart horizontal (top 10 countries / states dengan tap count)
6. "Top Social Links Clicked" → Bar chart ranked by clicks
7. Export button → dah wujud, pastikan call correct export endpoint per plan (ada `userOverviewExport` dan `businessOverviewExport`)

## 6.4 Profile Builder

| # | Issue | Fix |
|---|-------|-----|
| 1 | No autosave | §4.2 |
| 2 | No keyboard shortcuts | Cmd/Ctrl+S untuk save, Cmd/Ctrl+Z undo (simple history stack 10 steps) |
| 3 | Apply Design modal "disabled" message | Button disabled dengan tooltip explain "You need 2+ cards to apply design across them" daripada hanya disabled senyap |
| 4 | Image upload no compression | Client-side image compression sebelum upload (canvas-based < 1MB) — reduce storage + speed up |
| 5 | Required field indicator tak konsisten | Tanda * pada semua required fields, inline validation error display on blur |
| 6 | "View Saved" opens in same tab | Force open dalam new tab (`window.open`, `target="_blank"`) supaya user tak keluar builder |

## 6.5 Notifications Page

| # | Issue | Fix |
|---|-------|-----|
| 1 | No bulk select actions | Add checkbox column, dropdown action: Mark Selected Read / Delete Selected |
| 2 | Date filter tiada | Add date range picker (From / To) |
| 3 | Push notifications tak support | Add enable push notification button (guna Notification API + service worker) |
| 4 | In-app sounds | Optional toggle: play subtle sound bila new notification masuk |

## 6.6 Plan Selection / Checkout

| # | Issue | Fix |
|---|-------|-----|
| 1 | Step 1-2-3 indicator only top header | Add vertical progress summary sidebar pada checkout dengan semua steps filled/unfilled |
| 2 | No promo code / voucher field | Add "Apply Coupon" text + input box — backend PromoCode model + validation endpoint perlu create (future) |
| 3 | Order confirmation email timing | Send invoice PDF + order confirmation SELEPAS payment verified, bukan sem initiate |

---

# BAHAGIAN 7: Admin Panel - Per-Module Improvements (Priority 3)

## 7.1 AdminManagement Layout (Global)

| # | Issue | Fix |
|---|-------|-----|
| 1 | Sidebar icons still shown | Hidden align dengan UserDashboard (§3.1) |
| 2 | pageTitle lookup exact match route.path | Dynamic route children (e.g. invoices/1/show) tak match → fallback "Admin Panel". Use route meta or startsWith match. |
| 3 | No System Settings page | Create page untuk: Site name, Maintenance mode toggle, Default email sender, Payment gateway test/live mode toggle, Chatbot n8n webhook URL setting, Cloudflare API key for auto purge |
| 4 | No Admin Roles & Permissions page | User model dah ada `admin_role`, `admin_permissions` fields. Create page untuk create sub-admin (finance team, support team) dengan limited access (e.g. Finance boleh tengok Invoices/Payments sahaja, Support boleh manage Users/Cards tapi tak boleh ubah prices). |
| 5 | No Audit Log page | §5.2 — view semua admin + system activity trails |

## 7.2 User Management (/users)

| # | Issue | Fix |
|---|-------|-----|
| 1 | Filter by plan, status, date signup | Chips filter sidebar |
| 2 | Search by email / name only OK | Add search by phone, company, nfc_card_id |
| 3 | User details modal too simple | Show full profile: subscription history, all cards, analytics summary, account impersonate button (Login As User) |
| 4 | Manual subscription adjustment | Button "Extend Subscription", "Mark As Paid", "Suspend Account", "Activate Account" |

## 7.3 NFC Card Management (/nfc-cards)

| # | Issue | Fix |
|---|-------|-----|
| 1 | Shipment tracking fields exist (`tracking_number`, `shipped_date`, `delivered_date`) | Add form untuk update shipping info + button "Mark As Shipped", "Mark As Delivered" yang auto populate dates |
| 2 | Bulk assign cards to users | CSV import of card serial numbers + assign batch ke business account |
| 3 | Card physical inventory tracking | Stock page: Available / Assigned / Returned cards count |

## 7.4 Invoices & Payments

| # | Issue | Fix |
|---|-------|-----|
| 1 | Manual Bank Transfer verification UI dah wujud ✅ | Add "Send Reminder Email" button for pending transfers yang >48h |
| 2 | Refund process endpoint ada | Add partial refund (amount field) bukan sahaja full refund |
| 3 | DunningLog model wujud | Add page view dunning attempts per subscription |

## 7.5 Missing Admin Pages (Tak Wujud Langsung)

1. **Chatbot Question Management** — CRUD soalan FAQ (table `chatbot_questions` wujud), published/unpublished toggle, reorder. Ini feed chatbot widget yang sekarang broken (§2.1).
2. **Feature Toggles** — Table/Seeder FeatureToggleSeeder wujud tapi tak ada UI. Toggle features by plan (e.g. "Enable Portfolio Section", "Enable Blog Section") tanpa deploy code.
3. **Queue Jobs Monitoring** — Failed jobs, pending jobs, retry options (Laravel Horizon basic dashboard atau custom page)
4. **System Backup** — Trigger manual DB backup, list backup files, download (php artisan backup)

---

# BAHAGIAN 8: Landing Page - Content & Conversion Gaps (Priority 3)

## 8.1 Modular Components Status (Audit Homepage)

| Component | Status | Gaps |
|-----------|--------|------|
| HomepageNavbar | ✅ Ada ThemeSwitcher + Chatbot | Mobile menu close on outside click tak ada smooth |
| HomepageHero | ✅ Good headline + CTA | No live demo profile preview link |
| HomepageFeatures | ✅ Carousel OK | Add icon per feature (currently plain text) |
| HomepageHowItWorks | ✅ 3 steps OK | Add actual screenshot / mockup for each step |
| HomepageWhoUses | ✅ Logos OK | Add client testimonial quotes by company |
| HomepageGallery | ✅ OK | Add "Live Preview" link hover pada setiap card |
| HomepageTestimonials | ✅ OK | Add photo avatar pada setiap testimonial |
| HomepagePricing | ✅ 4 tiers OK | §8.2 — missing FAQ, feature comparison table |
| HomepageCTA | ✅ OK | Simplify, satu CTA sahaja (Get Started) |
| HomepageFooter | ✅ Legal links OK | Add sitemap links, social media links brand |

## 8.2 Missing Landing Page Sections

1. **FAQ Section (Penting untuk conversion)**
   - Tarik data dari `chatbot_questions` dengan flag `is_faq = true`
   - Accordion style, categories: Pricing, Technical, Shipping, Account

2. **Live Profile Demo Section**
   - Preview tab-style: Corporate User, Freelancer, Creative, Cafe Owner — 4 profile demo real yang boleh click

3. **Feature Comparison Matrix**
   - Table compare Free vs Basic vs Premium vs Business untuk setiap feature detail
   - Semak [ProfileBuilderSectionsSeeder.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/database/seeders/ProfileBuilderSectionsSeeder.php) — setiap section yang plan-restricted boleh list kat sini

4. **Money-back Guarantee / Trust badges**
   - 14-day free trial, 30-day money back, Secure checkout badges
   - SSM / Suruhanjaya Syarikat Malaysia logo jika applicable

5. **Contact Section**
   - Bukan sahaja modal Google Form iframe, tapi physical address, whatsapp click-to-chat, support hours

---

# BAHAGIAN 9: Security & Hardening (Priority 1)

## 9.1 User-Facing Security

| # | Issue | Fix |
|---|-------|-----|
| 1 | Email unverified users dapat full access | §2.3 Enforce MustVerifyEmail + verified middleware |
| 2 | Rate limiting on auth — 10/min OK | Add lockout: 5 failed login → 15 min ban (track IP + email) |
| 3 | 2FA UI Missing | §2.4 implement full 2FA flow |
| 4 | Password reset token no expiry shown | UI display "token expired, please request new" dengan betul (endpoint verify token ada, tapi frontend tak handle token invalid message friendly) |
| 5 | Password strength meter TIADA | Register + Change password forms add zxcvbn strength indicator (Weak/Medium/Strong) with requirement list visual |
| 6 | Session management | §4.3 Settings Security tab — active sessions list with logout button per device |

## 9.2 Backend Security

| # | Issue | Fix |
|---|-------|-----|
| 1 | Admin routes guna custom `admin` middleware OK | Double check setiap admin endpoint punya policy gate (bukan sekadar is_admin flag, tapi granular permissions check) |
| 2 | File upload only validate extension, tak validate MIME signature | FileUploadService check via `finfo` actual MIME type, block SVG yang mengandungi JS inline |
| 3 | Image upload tak compress server-side | Tambah spatie/image intervention compress + generate thumbnails |
| 4 | Public profile /[id] — no rate limit on taps/clicks tracking | Tambah throttle (max 100 tracking requests per IP per hour) prevent spam inflate analytics |
| 5 | API rate limit on Sanctum generic | Tambah specific rate limits per resource (payment 3/min, profile update 30/min) |
| 6 | CORS policy global HandleCors OK | Restrict origins list explicitly (APP_URL + allowed domains), tak guna wildcard dalam production |
| 7 | APP_KEY in env without quotes solved in past ✅ | Verify semua production env .env file APP_KEY double-quoted |

## 9.3 Production-Only Security Items (Tercatat untuk deployment checklist)

- Force HTTPS redirect (Nginx level)
- HSTS headers (Strict-Transport-Security: max-age=31536000; includeSubDomains)
- X-Content-Type-Options: nosniff
- X-Frame-Options: DENY (elak iframe embedding dari luar untuk dashboard)
- Content Security Policy (CSP) allowlist for n8n, Google OAuth, Fiuu domain sahaja

---

# BAHAGIAN 10: Implementation Roadmap & Priority Matrix

## 10.1 Priority Legend

| Priority | Code | Masa | Syarat |
|----------|------|------|--------|
| **Blocker** | P0 | This week | Broken flow / security |
| **Critical** | P1 | 1-2 weeks | Architecture + UX design system |
| **Important** | P2 | 3-4 weeks | Feature parity + UX polish |
| **Enhancement** | P3 | Month 2+ | Conversion / long term polish |

---

### Phase 1 (P0 — 1 Week): Fix Broken Things + Security ASAP

| Task | Reference Section | Effort Estimate |
|------|-------------------|-----------------|
| 1. Fix ChatbotWidget endpoints (add `/chatbot/ask` + `/chatbot/questions` routes + n8n proxy) | §2.1 | 1 day |
| 2. Enforce Email Verification flow (register → verify email → can login) | §2.3, §9.1.1 | 1 day |
| 3. Sidebar icons REMOVED from UserDashboard + AdminManagement layouts | §3.1 | 0.5 day |
| 4. Remove ALL emoji from profile/[id].vue → Ganti Heroicons SVG | §3.2 | 0.5 day |
| 5. Fix Profile Preview Modal (load actual profile content, bukan placeholder) | §2.6 | 0.5 day |
| 6. Password strength meter + confirm password visual match indicator | §9.1.5 | 0.5 day |

---

### Phase 2 (P1 — 2 Weeks): Design System + Backend Foundation

| Task | Reference Section | Effort Estimate |
|------|-------------------|-----------------|
| 1. Rollout Zora Pro Theme (Matte Navy + Teal palette) | §3.4 | 1 day |
| 2. Refactor profile/[id].vue — inline styles → Tailwind + design system classes, mobile responsive via breakpoint | §3.2, §3.3 | 3 days |
| 3. Subscription Source of Truth consolidation | §2.2 | 2 days |
| 4. Add Settings Security Tab (change password, 2FA QR flow, active sessions, logout everywhere, linked accounts) | §2.4, §4.3 | 3 days |
| 5. Scheduled Jobs + Console Commands (renewal processing, reminders, dunning retry, cleanup) | §5.1 | 2 days |
| 6. Database missing composite indexes add migration | §5.3 | 0.5 day |
| 7. Admin Audit Log middleware coverage + System Audit Log page | §5.2, §7.1.5 | 1.5 days |

---

### Phase 3 (P2 — 4 Weeks): Core UX Features + Analytics

| Task | Reference Section | Effort Estimate |
|------|-------------------|-----------------|
| 1. Integrate Chart.js for ALL analytics pages (Taps, Devices, Locations, Links, Time filter) | §2.5, §6.3 | 4 days |
| 2. Profile Builder Autosave + Unsaved Changes Warning + History undo | §4.2, §6.4.1 | 2 days |
| 3. Add Settings Preferences tab (Language, Theme defaults, Notification Preferences) | §4.3 | 1.5 days |
| 4. Add Settings Billing/Subscription tab (Save payment methods, Change plan, Cancel/Pause) | §4.1, §4.4 | 3 days |
| 5. Notifications page: Bulk actions, date filter, push notifications enable | §6.5 | 2 days |
| 6. Card Management: Complete Order Modal + Shipment tracking UI + search/filter | §6.2 | 2 days |
| 7. Notification Preferences DB table + backend logic | §4.5 | 1 day |
| 8. API Response pagination for all list endpoints | §5.4 | 1.5 days |
| 9. Profile Builder: Image compression client-side, open preview new tab, required field indicators | §6.4 | 1 day |

---

### Phase 4 (P3 — Month 2+): Long-Term Value + Conversion

| Task | Reference Section | Effort Estimate |
|------|-------------------|-----------------|
| 1. Landing Page: FAQ Section (dari chatbot_questions table) | §8.2.1 | 1 day |
| 2. Landing Page: Feature Comparison Matrix table | §8.2.3 | 1 day |
| 3. Landing Page: Live Profile Demo section 4 personas | §8.2.2 | 2 days |
| 4. Admin: Chatbot Question Management page (CRUD FAQ) | §7.5.1, §2.1 | 2 days |
| 5. Admin: System Settings page (site config, payment modes, maintenance) | §7.1.3 | 2 days |
| 6. Admin: Admin Roles & Permissions management | §7.1.4 | 2 days |
| 7. Admin: Feature Toggles UI management | §7.5.2 | 1 day |
| 8. Admin: NFC Card Shipment update forms + stock tracking | §7.3 | 2 days |
| 9. User: Promo code / voucher support pada checkout | §6.6.2 | 2 days |
| 10. Security headers HSTS, CSP, X-Frame-Options production Nginx | §9.3 | 0.5 day |
| 11. Email Templates Design System (branded HTML email untuk semua notifications) | Mail templates blade | 2 days |

---

## 10.2 Quick Wins Checklist (Boleh buat dalam 1 hari secara standalone)

- [x] Remove sidebar icons from both layouts
- [x] Remove emoji from profile/[id].vue → ganti icons
- [x] Fix Profile Preview Modal placeholder
- [x] Password strength meter visual on register
- [x] Chatbot `/chatbot/questions` FAQ route (untuk landing page FAQ section)
- [x] Open "View Saved" Profile Builder dalam new tab
- [x] Zora Pro default palette swap dalam `:root` main.css

---

## 10.3 Files Affected Summary (Untuk Cross-Check During Implementation)

| Domain | Core Files |
|--------|-----------|
| **Layouts** | [UserDashboard.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/layouts/UserDashboard.vue), [AdminManagement.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/layouts/AdminManagement.vue) |
| **Profile Public** | [profile/[id].vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/profile/[id].vue) |
| **Design System** | [main.css](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/assets/css/main.css), [tailwind.config.js](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/tailwind.config.js), [useTheme.js](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/composables/useTheme.js), [ThemeSwitcher.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/components/ThemeSwitcher.vue) |
| **Dashboard Pages** | [Settings.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/UserDashboard/Settings.vue), [Analytics.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/UserDashboard/Analytics.vue), [Notifications.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/UserDashboard/Notifications.vue), [CardManagement.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/UserDashboard/CardManagement.vue) |
| **Profile Builder** | [BusinessProfileBuilder.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/UserDashboard/UserManagement/BusinessPlanUser/BusinessProfileBuilder.vue), [PremiumProfileBuilder.vue], [BasicProfileBuilder.vue], [FreeProfileBuilder.vue] |
| **Auth Backend** | [AuthController.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Controllers/Api/AuthController.php), [User.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Models/User.php) |
| **Chatbot Backend** | [ChatbotController.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Controllers/Api/ChatbotController.php), [api.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/routes/api.php), [useChatbot.js](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/composables/useChatbot.js), [ChatbotWidget.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/components/ChatbotWidget.vue) |
| **Subscription** | [Subscription.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Models/Subscription.php), [SubscriptionController.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Controllers/Api/SubscriptionController.php), [auth.js store](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/stores/auth.js) |
| **Analytics** | [AnalyticsController.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Controllers/Api/AnalyticsController.php) |
| **Console Kernel** | [Kernel.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Console/Kernel.php) — add schedule() commands |
| **Landing Page** | [index.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/Homepage/index.vue) + 9 components dalam `frontend/components/homepage/` |

---

**End of Document**
