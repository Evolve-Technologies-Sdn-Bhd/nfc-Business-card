# NFCGo — Codebase Audit & Gap Report

**Date:** 2026-11-23
**Scope:** Full read-through of `frontend/` (Nuxt 3 SPA) and `backend/` (Laravel 12 API)
**Constraint applied:** No code was modified. Every finding below is evidence-based (file + line).
**Purpose:** Map the real current implementation against the "NFCGo — Complete Platform Audit, Code Refinement & Product Experience Transformation" brief, and separate findings into (A) fixable without changing existing logic and (B) requires a logic/schema change.

---

## 0. Method

Inspected: `backend/routes/api.php`, all API controllers, models, migrations, middleware, `Console/Kernel.php`, services, config; `frontend/` pages, layouts, components, stores, composables, middleware, `nuxt.config.ts`, `package.json`.

Not executed: no server was started, no test suite was run. Statements about runtime behaviour are derived from code reading, not from live execution — they are labelled as such.

---

## 1. Executive summary

The platform is **far more complete than the existing [SYSTEM_IMPROVEMENT_PLAN.md](file:///c:/Users/USER/Desktop/nfc-Business-card/SYSTEM_IMPROVEMENT_PLAN.md) (2026-09-24) claims**. Many "P0 critical" items in that document are already implemented and verified:

| Old plan claim | Actual state |
|---|---|
| Chatbot `/chatbot/ask` missing | Route EXISTS — [api.php:L343-L345](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/routes/api.php#L343-L345) |
| Email verification not implemented | Endpoint + `verify-email.vue` + resend endpoint EXIST (not enforced — see S-01) |
| 2FA has no UI | Full TOTP backend ([AuthController.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Controllers/Api/AuthController.php)) + full 2FA UI in Settings |
| Settings has only 2 tabs | Settings has ~7 tabs incl. Security, Billing, Privacy ([Settings.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/UserDashboard/Settings.vue), 3045 lines) |
| No audit log | `SystemAuditLog.vue` + `GET /admin/audit-logs` exist |
| Scheduled jobs missing | 7 scheduled commands EXIST in [Kernel.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Console/Kernel.php) |
| Missing DB indexes | Most composite indexes already added |

**However**, a second class of problems is *not* in either document and is more commercially damaging than anything listed there:

1. **The marketing homepage is a 2903-line monolithic file and the entire `components/homepage/*` component library (9 files) is dead code** — never referenced anywhere.
2. **The primary "Watch Demo" hero CTA opens a modal that says "Demo video coming soon..."** — a dead end on the main purchase-conversion path.
3. **The 6 homepage testimonials are fabricated** (invented people, AI-generated avatars, US companies) on a Malaysian product — directly contrary to §5.3 "avoid misleading marketing claims".
4. **`OnboardingController` still contains a mock payment that returns `status = completed` with `transaction_id = MOCK_xxxx`** — a fake successful payment in production code paths.
5. **The public NFC landing-page endpoint has no publication/ownership check, and writes to the database on a public GET.**
6. **There is no order concept at all** — no `orders`/`order_items` table exists. Purchase state is scattered across `transactions`, `nfc_cards`, `subscriptions`.

---

## 2. Corrections to the previous audit document

Recorded so the old plan is not followed blindly:

- `frontend/pages/index.vue` is an 11-line 301 redirect to `/Homepage` — the real homepage is [Homepage/index.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/Homepage/index.vue) (2903 lines).
- The "modular components" claim in [SYSTEM_IMPROVEMENT_PLAN.md §1.3](file:///c:/Users/USER/Desktop/nfc-Business-card/SYSTEM_IMPROVEMENT_PLAN.md) is wrong for the homepage: the components exist but are unused.
- `profile/[id].vue` and `profile/[...slug].vue` are **near-identical duplicates** (~6600 lines / 279 KB each). `[...slug].vue` is the live implementation; `[id].vue` is legacy.
- `[SystemAuditLog.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/AdminManagement/SystemAuditLog.vue)`, `configuration.vue`, `card-templates.vue`, `price-management.vue`, `business-users.vue` all exist — old plan §7.5 "missing admin pages" is out of date.

---

## 3. Findings — Public website & landing page (brief §5, §8)

This is the area you chose to implement first, so it is detailed most.

| ID | Finding | Status | Evidence | Cat |
|---|---|---|---|---|
| **P-01** | Homepage is one 2903-line monolith; all 9 files in `frontend/components/homepage/` are **dead code** (no `<HomepageX>`/`homepage-x` usage; only import in the file is `vue`) | BROKEN/DUP | [Homepage/index.vue#L2133](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/Homepage/index.vue#L2133) | A |
| **P-02** | Hero secondary CTA "Watch Demo" opens a modal containing the placeholder text "Demo video coming soon..." | PLACEHOLDER | [Homepage/index.vue#L251](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/Homepage/index.vue#L251), [#L1531-L1557](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/Homepage/index.vue#L1531-L1557) | A |
| **P-03** | 6 testimonials are invented (fake names, `ui-avatars.com` generated avatars, US companies "TechCorp"/"GlobalTech") | FABRICATED | [Homepage/index.vue#L2251-L2300](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/Homepage/index.vue#L2251-L2300) | A |
| **P-04** | Hardcoded usage stats ("10K+", etc.) presented as real traction | HARDCODED | [Homepage/index.vue#L1120](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/Homepage/index.vue#L1120) | A |
| **P-05** | Two pricing sources: plan prices come from `GET /plan-prices/public` via `usePlanPrices()`, but physical-card prices are hardcoded `Starting from RM29 / RM89 / RM59` in the markup → drift risk vs. admin Price Management | INCONSISTENT | [Homepage/index.vue#L796](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/Homepage/index.vue#L796), [#L833](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/Homepage/index.vue#L833), [#L868](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/Homepage/index.vue#L868), [#L2164](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/Homepage/index.vue#L2164) | A |
| **P-06** | No FAQ section (brief §5.1, §8.2.1) even though a public FAQ API already exists and is unused by the site | MISSING (easy win) | [api.php#L343](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/routes/api.php#L343) | A |
| **P-07** | 8 footer links point to `href="#"` | DEAD LINK | [Homepage/index.vue#L1400-L1498](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/Homepage/index.vue#L1400-L1498) | A |
| **P-08** | No feature-comparison matrix, no live profile demo, no shipping/activation information, no genuine trust badges (brief §5.3, §8.2) | MISSING | homepage has none | A |
| **P-09** | No public product detail page, no cart, no guest checkout — every CTA goes to `/UserAccount/register` (brief §7 "avoid forcing account creation before exploring") | MISSING | [Homepage/index.vue#L247](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/Homepage/index.vue#L247) | B |
| **P-10** | No per-page SEO/OG metadata → shared links have no preview card | MISSING | [nuxt.config.ts](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/nuxt.config.ts) has only a global `description` | A |

---

## 4. Findings — Public profile page, identity & community (brief §9)

| ID | Finding | Status | Evidence | Cat |
|---|---|---|---|---|
| **D-01** | `[id].vue` and `[...slug].vue` are ~6600-line near-duplicates; both ~279 KB. Massive duplication + ~95% inline `:style` + emoji icons | DUPLICATE | `frontend/pages/profile/[id].vue`, `frontend/pages/profile/[...slug].vue` | A (careful) |
| **D-02** | **Public landing-page endpoint returns any card's page with no publication/ownership check**, and **auto-creates a `LandingPage` row on a public GET** (unauthenticated write) | BROKEN/SECURITY | [NfcCardController.php#L924-L947](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Controllers/Api/NfcCardController.php#L924-L947) | B |
| **D-03** | Public tracking endpoints (`/nfc-cards/{id}/track-tap`, `/analytics/track`, `/nfc/tap/{nfcId}`) are unauthenticated, **unthrottled**, and store raw IP + user agent with no consent (brief §9.4, §11) | PARTIAL | [api.php#L119-L121](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/routes/api.php#L119-L121), [NfcController.php#L97-L104](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Controllers/Api/NfcController.php#L97-L104) | B |
| **D-04** | vCard download, QR modal, WhatsApp/tel/mailto/maps deep links, share modal — all EXIST | EXISTS | `profile/[...slug].vue` | — |
| **D-05** | QR codes are generated via third-party `api.qrserver.com` (external dependency, leaks profile URL to a third party) | EXISTS (risk) | `profile/[...slug].vue` QR logic | A |
| **D-06** | Community/discovery (brief §9.2, §9.3) — no directory, no opt-in discovery, no contact exchange. Confirmed absent, as expected | MISSING | — | B |
| **D-07** | `LandingPage` does have an `is_active` field, but the public read path ignores it | PARTIAL | [LandingPage.php#L146](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Models/LandingPage.php#L146) | B |

---

## 5. Findings — Commerce, orders & payments (brief §7, §8)

| ID | Finding | Status | Evidence | Cat |
|---|---|---|---|---|
| **C-01** | **No `orders` / `order_items` tables or models exist anywhere.** Purchase state is split across `transactions`, `nfc_cards` (holds `purchase_date`, `shipping_address`, `tracking_number`) and `subscriptions` | MISSING | no matches for `orders` in `backend/database/migrations/`; [NfcCard migration](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/database/migrations/2025_07_30_180000_create_nfc_cards_table.php) | B |
| **C-02** | Cart, quantity, shipping form, promo code, guest checkout — none exist. Checkout is a fixed 3-step authenticated flow in PlanSelection → design → Payment | MISSING | `frontend/pages/UserDashboard/PlanSelection.vue`, `Payment.vue` | B |
| **C-03** | "Buy Business plan" does not create an order — it opens a Gmail compose window to `genn.chong@clbgroups.com` | BROKEN | [PlanSelection.vue#L659-L665](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/UserDashboard/PlanSelection.vue#L659-L665) | B |
| **C-04** | **Mock payment still present**: onboarding payment returns `'status' => 'completed'` with `transaction_id = 'MOCK_'…` | FAKE SUCCESS | [OnboardingController.php#L375-L376](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Controllers/Api/OnboardingController.php#L375-L376) | B |
| **C-05** | Only Fiuu (Razer) is genuinely integrated. Signature verification, amount/currency in signature, and duplicate-callback check exist; other gateways are config-only stubs | PARTIAL | [PaymentService.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Services/PaymentService.php), [FiuuWebhookController.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Controllers/Api/FiuuWebhookController.php) | B |
| **C-06** | No idempotency key / unique constraint on webhook processing (duplicate check is a metadata comparison only) | PARTIAL | `FiuuWebhookController.php` | B |
| **C-07** | Card fulfilment is fully manual: no auto card creation on payment success, no inventory/stock, no stock decrement. User must click "I have received my card" | MISSING | `NfcCardController::activate()`, admin `mark-shipped`/`mark-delivered` routes | B |
| **C-08** | Payment UX: success/pending/retry states and post-payment invoice download EXIST | EXISTS | [payment/result.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/payment/result.vue), [payment/status.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/payment/status.vue) | — |
| **C-09** | Production credentials are placeholders (`FIUU_MERCHANT_ID`, `FIUU_VERIFY_KEY`, `FIUU_SECRET_KEY`, bank account numbers); template webhook URLs still point at `localhost` | UNCONFIGURED | [backend/config/payment.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/config/payment.php), [env.production.template](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/env.production.template) | config |

---

## 6. Findings — Registration, onboarding, chatbot, notifications (brief §6, §9)

| ID | Finding | Status | Evidence | Cat |
|---|---|---|---|---|
| **O-01** | Registration: validated fields, strong password regex, duplicate-email prevention, terms checkbox (frontend-only), token issued immediately | EXISTS | [AuthController.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Controllers/Api/AuthController.php), [register.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/UserAccount/register.vue) | — |
| **O-02** | **Chatbot widget bypasses the backend and calls the n8n webhook directly from the browser**, with the webhook URL hardcoded in client JS. The existing `POST /api/chatbot/ask` is therefore unused | PARTIAL | [ChatbotWidget.vue#L290-L292](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/components/ChatbotWidget.vue#L290-L292) vs [api.php#L344](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/routes/api.php#L344) | B (low risk) |
| **O-03** | Onboarding card claiming has **no ownership proof** — a card can be linked/activated by anyone who knows the identifier (brief §6.3 explicitly warns about this) | MISSING | [OnboardingController.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Controllers/Api/OnboardingController.php), `NfcCardController::activate()` | B |
| **O-04** | A hardcoded TOTP secret `JBSWY3DPEHPK3PXP` is rendered in the registration UI — needs confirmation that it is decorative and not a live secret | UNVERIFIED | [register.vue#L570](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/UserAccount/register.vue#L570) | A |
| **O-05** | Notifications: service-driven creation, list page, mark-read/all-read/delete, pinned support, 30s polling (no realtime), **no notification-preferences model/table** | PARTIAL | `NotificationService.php`, `useNotifications.js`, [UserDashboard.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/layouts/UserDashboard.vue) | B |

---

## 7. Findings — Admin, cards, analytics, security, performance (brief §2, §3, §10, §11)

| ID | Finding | Status | Evidence | Cat |
|---|---|---|---|---|
| **S-01** | **`verified` middleware is never applied to any route** → unverified accounts get full access (brief §2.3, §9.1) | PARTIAL | no `verified` in [routes/api.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/routes/api.php) | B |
| **S-02** | Subscription state still has two sources of truth (`users.subscription_*` vs `subscriptions`), mitigated by an hourly sync job + accessors | PARTIAL | [User.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Models/User.php), [Kernel.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Console/Kernel.php) | B |
| **S-03** | Analytics charts are **not implemented** — no charting library is even installed (`package.json` has no chart.js/echarts/apexcharts). Growth deltas are hardcoded; the chart areas are placeholder divs | BROKEN | [Analytics.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/UserDashboard/Analytics.vue), [frontend/package.json](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/package.json) | A/B |
| **S-04** | `FileUploadService` validates extension only (no MIME-signature check); the thumbnail generator is a **copy stub** | PARTIAL | [FileUploadService.php#L81-L108](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Services/FileUploadService.php#L81-L108) | B |
| **S-05** | Rate limiting exists only on 5 auth routes (`register`, `login`, `verify`, `resend`, `check-email`). Public tracking endpoints are unthrottled | PARTIAL | [api.php#L107-L131](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/routes/api.php#L107-L131) | B |
| **S-06** | **Profile builder duplication: 4 near-identical builders at 5534 / 5183 / 5072 lines**, plus `ProfileBuilder.vue` (2088 lines) which appears to be legacy, plus 3 duplicated card-design pages | DUPLICATE | `frontend/pages/UserDashboard/UserManagement/**`, `frontend/pages/UserDashboard/ProfileBuilder.vue` | A (large) |
| **S-07** | Admin panel is complete and API-wired (dashboard KPIs, users, business users, nfc-cards, card templates, invoices, price management, stats, notifications, feedback, profile-builder design, configuration, legal docs, audit log). Missing: roles & permissions UI, chatbot FAQ management UI, refund UI, order review (blocked by C-01), feature toggles, queue monitoring, backup | MOSTLY EXISTS | `frontend/pages/AdminManagement/*.vue` | mixed |
| **S-08** | Audit trail exists (`LogActivity` middleware, `activity_logs`, `/admin/audit-logs`, `SystemAuditLog.vue`); only Admin/Business/Employee actors are logged | EXISTS | [LogActivity.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Middleware/LogActivity.php) | — |
| **S-09** | Missing composite indexes: `transactions (user_id, status, created_at)`, `analytics (trackable_type, action, created_at)` | MINOR | migrations | B |
| **S-10** | Hardcoded third-party/external values: n8n webhook, Google Form embed URL, `genn.chong@clbgroups.com`, `api.qrserver.com`, `support@nfcgo.com` | EXISTS | multiple | A |

---

## 8. Category A vs Category B

**Category A — can be improved without touching business logic, data model, or existing API contracts.** These are the candidates for the first implementation pass:

- P-01 dead homepage components; P-02 dead demo modal; P-03 fabricated testimonials; P-04 hardcoded stats; P-05 hardcoded card prices (display layer); P-06 FAQ section (consumes existing public endpoint); P-07 dead footer links; P-08 missing marketing sections; P-10 SEO/OG meta.
- D-01 profile page duplication + inline styles + emoji (visual refactor only).
- D-05 replace external QR service with a local generator.
- O-04 verify/remove the demo TOTP secret.
- S-03 analytics chart rendering (frontend only — renders data the API already returns; growth deltas need a decision).
- S-06 deduplicate the profile builders (large, risky, should be a separate deliberate pass).
- S-10 hardcoded values moved to config.

**Category B — requires changing logic, schema, or contracts. Needs your explicit approval per item:**

- D-02 enforce publication + ownership on the public landing-page endpoint; stop writing on public GET.
- D-03 rate-limit public tracking endpoints; decide the privacy stance on raw IP storage.
- C-01 introduce an `orders` / `order_items` model.
- C-02 cart / guest checkout / public product pages.
- C-03 replace the Gmail-compose Business purchase path with a real flow.
- C-04 remove the mock payment from onboarding.
- C-06 webhook idempotency key.
- C-07 automated card creation / inventory on payment success.
- O-02 route the chatbot through the backend controller.
- O-03 ownership verification when linking/activating a purchased card.
- O-05 notification-preferences table.
- S-01 enforce email verification (breaks existing unverified users — needs a migration/grace strategy).
- S-02 collapse the subscription dual-source-of-truth.
- S-04 real MIME validation + real thumbnails.
- S-05/S-09 rate limits + indexes.

---

## 9. Recommended first implementation batch — Public website & landing page (all Category A)

Ordered, self-contained, no logic changes:

1. **Remove the dead end on the primary conversion path** — replace the "Watch Demo" placeholder modal ([Homepage/index.vue#L1531-L1557](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/Homepage/index.vue#L1531-L1557)) with a real, working demo of a published profile page (an existing route already exists: `/profile/...`), or remove the button.
2. **Stop publishing invented testimonials** — replace with either genuinely approved customer quotes or a neutral trust section. Same for the "10K+/500+/98%/2M+" stats and "Trusted by Leading Companies" logo row.
3. **Single source for pricing** — drive the "Starting from RM29/89/59" card figures from the same price API the plan cards use ([Homepage/index.vue#L2164](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/Homepage/index.vue#L2164)), or clearly label them as display-only.
4. **Add the FAQ section** using the already-existing public endpoint `GET /api/chatbot/questions` ([api.php#L343](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/routes/api.php#L343)) — content is manageable from the existing admin chatbot controller.
5. **Fix the 8 `href="#"` footer links** ([Homepage/index.vue#L1400-L1498](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/Homepage/index.vue#L1400-L1498)) — route them to real pages or remove.
6. **Add the missing conversion sections** (feature comparison matrix, shipping & activation info, real contact details).
7. **SEO/OG**: add per-page metadata so shared links render previews.
8. **Decide the fate of the dead homepage component library** — either adopt it (refactor the monolith) or delete it. Do not leave both.
9. **Landing-page (public profile) presentation pass**: replace emoji with SVG icons and move inline styles to design tokens on the live `[...slug].vue`; leave `[id].vue` untouched until you decide to delete or redirect it.

Skills/infrastructure needed for later batches: none beyond what exists; the chart work (S-03) requires adding a charting dependency, which is a build-level decision and will be raised separately.

---

## 10. Required configuration (not code)

| Item | Where | Needed for |
|---|---|---|
| `FIUU_MERCHANT_ID`, `FIUU_VERIFY_KEY`, `FIUU_SECRET_KEY` | `backend/.env` | Live payments |
| Production webhook + return URLs (currently `localhost` in the template) | Fiuu merchant portal + `backend/.env` | Callback delivery |
| Bank transfer account numbers | `backend/.env` | Manual bank transfer rail |
| n8n chatbot webhook (currently hardcoded in frontend JS) | `backend/.env` (`services.php` default) | Chatbot/FAQ |

---

## 11. Decisions I need from you

1. **Testimonials & stats** — do you have real customer quotes/metrics to use, or should the section be replaced with neutral, non-fabricated content?
2. **"Watch Demo"** — build a working profile demo, or remove the CTA?
3. **Dead homepage components** — adopt them (refactor the 2903-line monolith) or delete them?
4. **Card prices on the homepage** — should they come from the DB price table (single source) or stay as static marketing figures?
5. **`profile/[id].vue`** — keep, redirect to `[...slug].vue`, or delete?

Once you confirm, I will implement the batch in section 9 directly in the codebase (Category A only — no logic changes), then move to the next area.
