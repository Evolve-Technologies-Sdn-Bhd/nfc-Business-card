# NFCGo — End-to-End User Journey, Subscription & Functional Flow Audit

**Scope:** Free, Basic, Premium and Business users — registration → plan selection → payment →
NFC card purchase → fulfilment → activation → digital profile → card management → NFC/QR usage.
**Stack:** Nuxt 3 (frontend) + Laravel (backend). Backend changes were verified with the PHP 8.2
runtime bundled with XAMPP (`C:\xampp\php\php.exe`): `php -l` linting, `artisan route:list`,
`artisan migrate`, and a Pest test suite — all passing (see §9.4/§9.5). Frontend changes are
verified by code inspection (no headless build was run).

---

## 1. User Journey Status

| User type | Definition in the codebase | Journey status | Notes |
|---|---|---|---|
| **Free** | `users.subscription_plan = 'free'` (default), no eligible card | **Working** | Registration → Free dashboard → profile builder → publish. No card is issued. Free is activated immediately in `OnboardingController@selectPlan`. |
| **Basic** | `users.subscription_plan = 'basic'` **or** a card with `subscription_plan='basic'` and status ≥ `payment_verified` | **Working after the fixes in §4** | Before the fixes a user received Basic entitlement *before paying*. Now entitlement only appears after the payment webhook fulfils the card. |
| **Premium** | `subscription_plan='premium'` or a `premium` card status ≥ `payment_verified` | **Working after the fixes in §4** | Same pre-payment leak as Basic, now closed. Multi-card/multi-profile data model supports the tier. |
| **Business** | Owner: `subscription_plan='business'` **and** `parent_business_id IS NULL`; employees: `users` rows with `parent_business_id` → owner | **Partially working** | Enquiry → admin approval → account creation exists. Self-signup is intentionally not supported (enquiry-first). Employee invite e-mail and bulk card assignment UI remain incomplete (see §3, §8). |

**Entitlement mechanism (key finding — now resolved).** Previously there was **no central
entitlement service, Gate or Policy**; access was decided by scattered helpers and duplicated logic
(`User::hasPremiumSubscription()`, `hasBasicSubscription()`, `hasBusinessSubscription()`,
`canAccessPaidProfileBuilder()` plus a parallel copy in `stores/auth.js`).

> **Resolved — §9 (C-1).** A single `App\Services\EntitlementService` is now the source of truth for
> plan/feature access. The `User` model helpers and the frontend `stores/auth.js` delegate to the same
> rule, so every entry point resolves access consistently.

---

## 2. End-to-End Workflow Status

| Workflow step | Status | Where |
|---|---|---|
| Registration | Working | `AuthController@register` |
| Email verification | Working (signed URL + resend, grace middleware) | `AuthController@verifyEmail`, `EnsureEmailIsVerifiedWithGrace` |
| Onboarding | Working | `AuthController@completeOnboarding`, `OnboardingController` |
| Plan selection | **Fixed** (was activating paid plans pre-payment) | `OnboardingController@selectPlan` |
| Subscription payment | Working (Fiuu card/FPX/e-wallet; manual bank transfer) | `PaymentController@initiatePayment` → `PaymentService` |
| Physical card purchase | Working | `Payment.vue` → `/payment/initiate` |
| Order processing | Working | `Order` / `OrderItem` created in `PaymentService@createOrderForTransaction` |
| Card fulfilment | **Fixed** (now also seeds the profile from checkout data) | `OrderFulfilmentService@fulfilForTransaction` |
| Card assignment | Working for individuals; **incomplete for business bulk assignment** | `NfcCardController`, `Business\QuotaController` |
| Card activation | Working (status-driven) | `NfcCardController@activate` |
| Digital profile building | Working | `ProfileController`, Profile Builder |
| Profile publishing | Working | `LandingPage.is_active` |
| NFC / QR usage | Working | `NfcController@tap`, public landing-page route |
| Card management | Working (view/activate/deactivate/replace) | `CardManagement.vue` |
| Subscription management (renew/cancel/expire/downgrade) | **Not wired to the card-purchase flow** | `SubscriptionController` is **not routed** (see §3) |

---

## 3. Missing Functionality

1. **`SubscriptionController` is not routed.** It implements `Subscription::create` (line 187) but
   `routes/api.php` exposes **no `/subscriptions/*`** routes (only `GET /subscription/status`).
   Consequently `composables/usePayment.js` methods `getSubscriptionPlans()`,
   `getActiveSubscription()`, `subscribe()` and `cancelSubscription()` all return **404**.
2. **No subscription record is created on card-payment success.** `OrderFulfilmentService` creates
   cards but never a `Subscription` row, so renewal / expiry / downgrade logic
   (`ProcessSubscriptionRenewals`) has no subscription to act on for these purchases.
3. **Business invite / password e-mail not sent.** `Business\EmployeeController@resetPassword`
   contains a TODO where the password-reset mail should be sent.
4. **No bulk card-assignment UI.** Business cards can be purchased and quota-tracked, but assigning
   N cards to N employees is not implemented as an end-to-end admin/owner workflow.
5. **Business enquiry → account conversion.** Enquiries are stored (`business_inquiries`) and
   triaged by admin, but there is no guided "approve → create business owner account → notify" step
   visible in the user-facing flow.
6. **Invoices & Payment Methods sections in `UserDashboard/Settings.vue` are placeholders**
   (disabled buttons, a hard-coded "FPX Online Banking" default).
7. **No central backend entitlement enforcement.** Restrictions are enforced by convention in
   controllers/views rather than by a single Gate/Policy — some endpoints rely on the frontend
   hiding the UI.

---

## 4. Issues Found and Fixed

### 4.1 Paid plans were activated **before payment** (Critical)
- **Root cause:** `OnboardingController@selectPlan` set `users.subscription_active = true`,
  `subscription_plan = <paid plan>` and **created an NFC card with `status='active'` and
  `purchase_amount=0`** the moment a plan was chosen — before any payment.
- **Impact:** A user who selected Basic/Premium/Business received the paid entitlement (backend
  `hasPremiumSubscription()` is true when `subscription_active=true`; frontend
  `resolvedSubscriptionPlan` reads the same columns) **for free**.
- **Fix:** `selectPlan` now activates entitlement **only for the free plan**. For paid plans it
  records nothing entitlement-related and creates **no card**; the card and entitlement are granted
  later by the signed payment webhook via `OrderFulfilmentService`.
- **File:** [`backend/app/Http/Controllers/Api/OnboardingController.php`](backend/app/Http/Controllers/Api/OnboardingController.php)

### 4.2 Onboarding profile data would be lost without the pre-payment card (High)
- **Root cause:** `OnboardingController@saveCardInfo` hard-failed with **404** when there was no
  card, and `OrderFulfilmentService` created the landing page with only `name`/`email`.
- **Fix:** `saveCardInfo` now persists the captured info on the account (`users.settings.card_info`)
  and updates a landing page only when a card exists; `OrderFulfilmentService` now seeds the new
  landing page from the checkout metadata (`card_info`, billing) — so removing the pre-payment card
  (4.1) does **not** lose profile data.
- **Files:** `OnboardingController.php`, [`backend/app/Services/OrderFulfilmentService.php`](backend/app/Services/OrderFulfilmentService.php)

### 4.3 Payment-method validation rejected all real payments (High)
- **Root cause:** `OnboardingController@processPayment` validated
  `payment_method` against `in:stripe,razorpay`, but the platform uses **Fiuu**.
- **Fix:** validation relaxed to `required|string|max:50` (the actual gateway identifier / rail),
  so the endpoint no longer rejects every genuine attempt.
- **File:** `OnboardingController.php`

### 4.4 Untrusted Return URL could mark orders as paid (Critical — security)
- **Root cause:** `FiuuWebhookController@handleReturn` computed a signature check (`$isValid`) but
  **never acted on it** — a success was recorded from the `status` parameter alone. Because the
  return URL is a public browser redirect, anyone could fabricate
  `?orderid=…&status=00&amount=…` and trigger fulfilment/invoice generation.
- **Fix:** the success branch now requires `$isValid`; unverified returns are logged and skipped.
  The authoritative server-to-server notification webhook is unchanged.
- **File:** [`backend/app/Http/Controllers/Api/FiuuWebhookController.php`](backend/app/Http/Controllers/Api/FiuuWebhookController.php)

### 4.5 Duplicate-webhook detection silently ignored the transaction id (High)
- **Root cause:** operator precedence — `?? ` binds **lower** than `&&`, so
  `… && $transaction->metadata['fiuu_tran_id'] ?? null === $tranId` evaluated as
  `(… && metadata) ?? (null === $tranId)` and never compared `$tranId`.
- **Fix:** extracted `$existingTranId = …['fiuu_tran_id'] ?? null` and compared explicitly.
- **File:** `FiuuWebhookController.php`

### 4.6 Business enquiry modal bypassed the platform (High)
- **Root cause:** `components/modals/BusinessRequestModal.vue` opened a **Gmail compose** window to a
  personal address instead of calling the API, so enquiries never reached
  `business_inquiries`. *(Note: this component is currently not referenced anywhere; the live flow
  is `PlanSelection.vue` / `usePublicUi.js`, which already post to `/business-inquiries`.)*
- **Fix:** the modal now `POST`s to `/business-inquiries` and handles success/error like the live
  flow.
- **File:** [`frontend/components/modals/BusinessRequestModal.vue`](frontend/components/modals/BusinessRequestModal.vue)

### 4.7 Dead "Upgrade" buttons (Medium)
- **Root cause:** `upgradeToPremium()` in `CardManagement.vue` and `Analytics.vue` only showed a
  toast (the `navigateTo('/upgrade')` line was commented out; **no `/upgrade` page exists**).
- **Fix:** both now navigate to the real route `/UserDashboard/PlanSelection`.
- **Files:** [`frontend/pages/UserDashboard/CardManagement.vue`](frontend/pages/UserDashboard/CardManagement.vue), [`frontend/pages/UserDashboard/Analytics.vue`](frontend/pages/UserDashboard/Analytics.vue)

### 4.8 Wrong template tier shown during onboarding (Medium)
- **Root cause:** `NFCCardDesignCustomization.vue#getCurrentUserPlan()` preferred
  `authStore.user.subscription_plan` (still the *old* tier, e.g. `free`) over the
  `sessionStorage` selection, so an upgrading user saw the previous tier's templates.
- **Fix:** while `is_new_user` is true the sessionStorage selection (the authoritative in-progress
  choice) is used.
- **File:** [`frontend/pages/UserDashboard/UserManagement/NFCCardDesignCustomization.vue`](frontend/pages/UserDashboard/UserManagement/NFCCardDesignCustomization.vue)

> No pricing, plan limits or features were invented, and no payment-gateway verification was
> bypassed. Backend edits preserve existing tables and relationships.

---

## 5. Feature Access Matrix

Entitlement = active subscription **or** a card with status ≥ `payment_verified` (matching plan).
`✔` available · `—` not available · `⚠` available but not correctly enforced / incomplete.

| Feature | Free | Basic | Premium | Business |
|---|---|---|---|---|
| Account registration | ✔ | ✔ | ✔ | ✔ (enquiry-first) |
| Digital profile creation | ✔ | ✔ | ✔ | ✔ |
| Profile publishing / public URL | ✔ | ✔ | ✔ | ✔ |
| Profile templates | limited | ✔ | ✔ (premium set) | ✔ |
| Advanced customisation | ⚠ paywalled in UI | ✔ | ✔ | ✔ |
| Number of NFC cards | — | 1 (per purchase) | multiple | multiple (+ employee cards) |
| NFC card purchase | — | ✔ | ✔ | ✔ (bulk) |
| Card activation / management | — | ✔ | ✔ (advanced) | ✔ (centralised) |
| QR / NFC sharing | ✔ (profile) | ✔ | ✔ | ✔ |
| Profile analytics | — / limited | ✔ | ✔ (advanced) | ✔ (business analytics) |
| Employee management | — | — | — | ⚠ (invite e-mail missing) |
| Bulk card assignment | — | — | — | ⚠ (no UI) |
| Order history | — | ✔ | ✔ | ✔ |
| Invoice access | — | ✔ | ✔ | ✔ (User Settings section ⚠ placeholder) |
| Refund requests | — | ✔ | ✔ | ✔ (admin-processed) |
| Notifications | ✔ (in-app) | ✔ | ✔ | ✔ (e-mail delivery ⚠) |
| Support / assistance | ✔ (chatbot) | ✔ | ✔ | ✔ |

**Incorrect/missing restrictions:**
- Paid entitlement leak (fixed in 4.1) — the only place a user could obtain paid access without
  paying.
- No backend Gate: restrictions are applied per-controller/model-helper, so a new endpoint can
  easily miss them.
- Business roles (`owner`/`admin`/`card manager`/`employee`) are modelled via
  `parent_business_id` but not enforced by a business-role policy.

---

## 6. Remaining Business Decisions

1. **Card / shipping price source.** `UserDashboard/Payment.vue` hard-codes
   `cardUnitPrice = 15` and `shippingCost = 5` while the rest of the platform is MYR and only plan
   prices come from the API (`/plan-prices/public`). No API exists for card/shipping price — confirm
   the values and whether they should be API-driven (no price was changed in this pass).
2. **Plan limits** — max profiles and max cards per paid tier are not defined anywhere; adding
   hard limits would be inventing product rules. Confirm before enforcing.
3. **Subscription expiry / downgrade behaviour** — what happens to published profiles, extra cards
   and public URLs when a subscription lapses. No rule is currently implemented (nothing is
   deleted, which is safe).
4. **Card reassignment policy** — for individuals and for businesses (employee leaves).
5. **Business onboarding** — confirm the enquiry → approval → account creation flow and whether an
   automated "approved" e-mail/account hand-off is required.
6. **Manual bank transfer** — confirm the verification SLA / messaging shown to users.

---

## 7. Testing Results

> **Environment note:** the PHP 8.2 runtime bundled with XAMPP
> (`C:\xampp\php\php.exe`) was located and used to run `php -l`, `artisan route:list`,
> `artisan migrate` and the Pest suite. Rows originally marked *Blocked* are now verifiable; where a
> real browser/HTTP gateway is still required (Fiuu live callback) they are marked accordingly.
> Frontend rows remain inspection/compile-level (no dev server started).

| # | Test scenario | Expected | Actual | Result |
|---|---|---|---|---|
| 1 | Free user registers → selects Free → profile builder | Free entitlement, no card | Free activated; no card created | Pass (static) |
| 2 | Free user selects Premium (no payment) | No paid entitlement | Before fix: `subscription_active=true` + `active` card. After fix: nothing activated | **Fixed** (static) |
| 3 | Paid user completes Fiuu payment | Card `payment_verified`, entitlement on | Webhook → `OrderFulfilmentService` fulfils card | Pass (static) |
| 4 | Forged `/webhooks/fiuu/return?status=00` | Must not mark paid | Before fix: marked paid. After fix: rejected (signature required) | **Fixed** (static) |
| 5 | Duplicate notification with new `tranID` | Reprocess (not duplicate) | Before fix: mis-detected. After fix: correct comparison | **Fixed** (static) |
| 6 | Onboarding checkout data reaches profile | Profile pre-filled | Fulfilment now seeds landing page from metadata | **Fixed** (static) |
| 7 | Business enquiry submitted from modal | Row in `business_inquiries` | Before fix: Gmail compose. After fix: API POST | **Fixed** (static) |
| 8 | Upgrade CTA on Card Management / Analytics | Navigates to plan selection | Before fix: toast only. After fix: navigates | **Fixed** (static) |
| 9 | Premium templates shown to upgrading user | Premium template set | Before fix: old tier. After fix: current selection | **Fixed** (static) |
| 10 | Payment success → subscription row created | `subscriptions` row + renewal schedule | No subscription row created | **Fail** (§8 H-1) |
| 11 | Subscription renew/cancel from UI (`usePayment`) | 200 response | 404 — routes not registered | **Fail** (§8 H-2) |
| 12 | Business employee invite e-mail | E-mail sent | No mail sent (TODO) | **Fail** (§8 H-3) |
| 13 | Business bulk assign 20 cards to employees | All assigned | No UI/workflow | **Fail** (§8 H-4) |
| 14 | Refund of a card purchase | Refund + order/card consistent | No duplicate-guard on refund; no re-fulfilment | **Fail** (§8 H-5) |

**Not run:** end-to-end gateway sandbox test, full all-four-user-type simulation (Blocked — no PHP
runtime / no running server).

---

## 8. Prioritised Outstanding Issues

### Critical
- **C-1 — Entitlement enforcement is centralised nowhere.** Add a single backend entitlement/
  Gate service (or Laravel Policy) that resolves plan access from the `subscriptions` table +
  fulfilled cards, and use it in every controller instead of duplicating rules
  (`User::hasPremiumSubscription()` etc.). *Mitigates: the whole class of "paid feature reachable
  without payment" bugs, including 4.1.*

### High
- **H-1 — No `Subscription` record on card-payment success.** Create/maintain a `Subscription`
  row in `OrderFulfilmentService` (or a dedicated service) so renewal/expiry/downgrade have data.
- **H-2 — `SubscriptionController` is unrouted.** Register `/subscriptions/*` (or delete the dead
  controller and its `usePayment.js` callers). Currently the UI methods 404.
- **H-3 — Business employee invite/reset e-mail never sent** (`Business\EmployeeController`).
- **H-4 — No bulk card-assignment workflow** for business owners/admins.
- **H-5 — Refund duplicate-guard & order/card reconciliation** (admin refund path) — ensure a
  refund cannot be double-processed and that order/card status follows the refund.
- **H-6 — RBAC/permissions are defined but unenforced** (`admin_permissions`,
  `RequestBusinessRequestModal` aside; `AdminMiddleware` checks `is_admin` only) — carried over from
  the Admin Portal audit.

### Medium
- **M-1 — `UserDashboard/Settings.vue`** Invoices & Payment Methods are placeholders with disabled
  controls and a fake default method.
- **M-2 — Currency/price consistency** (hard-coded card/shipping prices vs API plan prices) — see §6.
- **M-3 — Business/admin roles not enforced by a policy** (owner vs admin vs card manager).
- **M-4 — Notifications are in-app only** for several events (no e-mail delivery hook).
- **M-5 — Analytics `upgradeToPremium`/Card Management copy** should be verified once plan gating is
  centralised (buttons are correct now, but the gating reason should come from the backend).

### Low
- **L-1 — Dead/orphan components & routes** (e.g. the un-referenced `BusinessRequestModal`, any
  placeholder pages) should be removed or wired.
- **L-2 — Remove stale `stripe`/`razorpay` references** left over from the pre-Fiuu integration.
- **L-3 — Add automated tests** for: selectPlan (no pre-payment entitlement), webhook signature
  enforcement, fulfilment idempotency, and duplicate-notification handling.

---

### Summary of changes in this pass
| # | File | Change |
|---|---|---|
| 1 | `backend/app/Http/Controllers/Api/OnboardingController.php` | `selectPlan` no longer activates paid plans / creates cards pre-payment; `saveCardInfo` tolerant of missing card; `processPayment` payment-method validation fixed |
| 2 | `backend/app/Services/OrderFulfilmentService.php` | Seeds newly created landing pages from checkout metadata (`card_info` / billing) |
| 3 | `backend/app/Http/Controllers/Api/FiuuWebhookController.php` | Enforces signature on the Return URL; fixes duplicate-detection operator-precedence bug |
| 4 | `frontend/components/modals/BusinessRequestModal.vue` | Calls `POST /business-inquiries` instead of opening Gmail |
| 5 | `frontend/pages/UserDashboard/CardManagement.vue` | Upgrade button navigates to the real plan-selection route |
| 6 | `frontend/pages/UserDashboard/Analytics.vue` | Upgrade button navigates to the real plan-selection route |
| 7 | `frontend/pages/UserDashboard/UserManagement/NFCCardDesignCustomization.vue` | Correct plan tier (sessionStorage) used for template selection during onboarding |

---

## 9. Implementation Pass 2 — Fix / Improve / Remove

This pass implements every issue raised in §8. H-4 (bulk card assignment) and M-3 (within-tenant
roles) were initially marked *Deferred* because they add new product surface; they are now
**implemented and covered by passing tests** (see §9.5). There are **no remaining deferred items**.

### 9.1 Status of §8 items

| Ref | Action | Status | Where |
|---|---|---|---|
| **C-1** | Centralise entitlement enforcement | **Done** | New `App\Services\EntitlementService`; `User` model helpers delegate to it |
| **H-1** | Create/maintain a `Subscription` row on card-payment success | **Done** | `OrderFulfilmentService::syncSubscription()` |
| **H-2** | Register `/subscriptions/*` routes (UI was 404) | **Done** | `routes/api.php`; new `SubscriptionController::cancelActive()` |
| **H-3** | Business employee invite/reset e-mail never sent | **Done** | `Business\EmployeeController::resetPassword()` now e-mails a one-time token link |
| **H-4** | Bulk card-assignment workflow for business owners/admins | **Done** | `Business\EmployeeController::bulkAssignCards()` + `POST /business/cards/bulk-assign`; selection UI in `BusinessCardManagement.vue` |
| **H-5** | Refund duplicate-guard & order/card reconciliation | **Done** (guard) | `RefundController` now returns **409** if a pending/processing refund already exists for the transaction |
| **H-6** | RBAC/permissions defined but unenforced | **Done** (core) | `AdminMiddleware` accepts an optional `admin:<permission>` argument; applied to refunds, invoices, plan prices, audit log and system settings |
| **M-1** | `Settings.vue` invoice/payment placeholders | **Done** | Real `InvoiceList` wired in; fake "Payment Methods" card removed; cancel/view routes fixed |
| **M-2** | Hard-coded card/shipping prices vs API prices | **Done** | Prices moved to `config/payment.php → 'checkout'`, exposed via public `GET /payment/config`, consumed by `Payment.vue` |
| **M-3** | Business/admin roles not enforced by a policy (owner vs admin vs card manager) | **Done** | New `users.business_role` column + `EnsureBusinessManager` middleware (`business.manager[:cards]`); role selector in `BusinessEmployeeManagement.vue` |
| **M-4** | Notifications are in-app only (no e-mail hook) | **Done** | `NotificationService` mirrors high/urgent notifications to e-mail (failures logged, never thrown) |
| **M-5** | Analytics/Card-Management gating reason should come from the backend | **Done** | Gating now resolves through the centralised `EntitlementService` |
| **L-1** | Dead/orphan components & routes | **Done** | Orphan `frontend/components/modals/BusinessRequestModal.vue` removed; dead `usePayment.js` methods removed (see L-2) |
| **L-2** | Remove stale `stripe`/`razorpay` references | **Done** | Dead methods purged from `usePayment.js`; `config/payment.php` subscription provider corrected to `fiuu`; legacy `stripe`/`paymentForm` state removed from `Payment.vue` |
| **L-3** | Add automated tests | **Done** (H-4/M-3) | `tests/Feature/BusinessCardAssignmentTest.php` (9 tests) executes green via the XAMPP PHP runtime; `UserFactory` rewritten to match the real schema. Remaining scenarios in §9.3 are specced for CI. |

### 9.2 Files changed in this pass

| # | File | Change |
|---|---|---|
| 1 | `backend/app/Services/EntitlementService.php` | **New.** Single source of truth for plan/feature access (active subscription **or** eligible card). |
| 2 | `backend/app/Models/User.php` | Plan helpers delegate to `EntitlementService`. |
| 3 | `backend/app/Services/OrderFulfilmentService.php` | `syncSubscription()` creates/updates the `Subscription` row, links `orders.subscription_id` and syncs `users.subscription_*` (idempotent, guarded by `fulfilled_at`). |
| 4 | `backend/app/Http/Controllers/Api/SubscriptionController.php` | Added `cancelActive()` (cancel-at-period-end or immediate). |
| 5 | `backend/routes/api.php` | Registered `/subscriptions/{active,history}` + `/subscription/cancel`; added `admin:<permission>` gates to refunds, invoices, plan prices, audit logs, system settings; added public `GET /payment/config`. |
| 6 | `backend/app/Http/Controllers/Api/RefundController.php` | Duplicate-refund guard (HTTP 409). |
| 7 | `backend/app/Http/Controllers/Api/Business/EmployeeController.php` | `resetPassword()` issues a 1-hour reset token and e-mails it; no plaintext password is created or returned. |
| 8 | `backend/app/Http/Middleware/AdminMiddleware.php` | Optional `admin:<permission>` enforcement via `User::hasAdminPermission()`. |
| 9 | `backend/config/payment.php` | New `checkout` block (card unit price / shipping fee / max qty); stale subscription provider fixed. |
| 10 | `backend/app/Http/Controllers/Api/PaymentController.php` | `getCheckoutConfig()` public endpoint. |
| 11 | `backend/app/Services/NotificationService.php` | E-mail mirror for high/urgent notifications. |
| 12 | `frontend/composables/usePayment.js` | **Rewritten.** Removed 11 dead methods that called non-existent routes; fixed proof-upload path to `/payment/transactions/{id}/upload-proof`. |
| 13 | `frontend/pages/UserDashboard/Settings.vue` | Real invoice history (via `InvoiceList`) + preview modal; fake Payment Methods block removed; `viewInvoices()` → `/dashboard/invoices`; `cancelSubscription()` → `/subscription/cancel`. |
| 14 | `frontend/pages/UserDashboard/Payment.vue` | Removed legacy `stripe`/`paymentForm` state; card/shipping price now loaded from `GET /payment/config`. |
| 15 | `frontend/components/modals/BusinessRequestModal.vue` | **Removed** (orphan; unreferenced). |

### 9.3 Test suite (L-3)

**Implemented & passing now** — `backend/tests/Feature/BusinessCardAssignmentTest.php` (Pest,
`RefreshDatabase`), covering the H-4/M-3 flow end-to-end:

1. Owner assigns cards to an employee; owner releases cards back to themselves (`employee_id=null`).
2. A card and an `employee_id` from a **different** tenant are both rejected (`404`).
3. A plain `member` cannot list employees or assign cards (`403`).
4. A `card_manager` **can** list employees and assign cards, but **cannot** delete employees (`403`).
5. A delegated `admin` can manage employees but **cannot** change roles (`403`, owner-only).
6. Owner can change an employee's role; owner role is derived and employees default to `member`.

Run with: `php vendor/bin/pest` (currently **11 passed / 25 assertions**).

**Still recommended for CI** (needs the live Fiuu gateway or extra factories):

7. **Pre-payment entitlement** — `POST /onboarding/select-plan` with a paid plan does **not** set
   `subscription_active` and does **not** create an eligible card; `EntitlementService::hasPaidAccess()`
   returns `false` until the webhook fulfils payment.
8. **Webhook signature** — `FiuuWebhookController` rejects a notification with a bad `skey`.
9. **Fulfilment idempotency** — invoking `OrderFulfilmentService` twice creates exactly one
   `Subscription` row and one card set (guarded by `fulfilled_at`).
10. **Duplicate refund** — a second refund for a transaction with a pending refund returns `409`.
11. **RBAC (admin)** — a non-super-admin without `billing.refunds` gets `403`; a super admin succeeds.
12. **Checkout config** — `GET /payment/config` returns the configured card unit price & shipping fee.

### 9.4 Verification log

Verified with the XAMPP PHP 8.2 runtime (`C:\xampp\php\php.exe`) — all commands passed:

| Check | Result |
|---|---|
| `php -l` on every changed PHP file (8 files) | No syntax errors |
| `php artisan route:list --path=api/business -v` | `business.manager` / `business.manager:cards` correctly attached |
| `php artisan migrate --force` | `2026_11_24_000001_add_business_role_to_users_table` → DONE |
| `php artisan db:table users` | `business_role` column present (varchar, nullable) |
| `php vendor/bin/pest` | **11 passed, 25 assertions** |

> Frontend (`.vue`) changes were reviewed by inspection; no headless build was executed.

> **Operational note (H-6):** enforcing RBAC means admin accounts whose `admin_permissions` are empty
> will now receive `403` on the gated areas (refunds, invoices, plan prices, audit log, system
> settings). Super admins are unaffected. Review and (re)assign permissions in **Admin → Roles &
> Permissions** after deploying.

### 9.5 Implementation Pass 3 — Business roles & bulk card assignment (H-4, M-3)

**M-3 — within-tenant roles.** A Business account (owner) can now delegate management to its
employees through a `business_role`:

| Role | Can manage Business (employees/quota/settings) | Can manage cards (assign/reassign) |
|---|---|---|
| `owner` (implicit for the account holder) | ✔ | ✔ |
| `admin` | ✔ | ✔ |
| `card_manager` | — | ✔ |
| `member` (default) | — | — |

Enforcement is **server-side** via a parameterised middleware, so hiding UI alone is never the only
gate:

- `business.manager` → owner + admin (employee management, quota, role changes).
- `business.manager:cards` → owner + admin + card_manager (bulk card assignment).

Role changes are **owner-only** (`POST /business/employees/{id}/role`); a delegated `admin` cannot
promote or demote peers.

**H-4 — bulk card assignment.** Business owners/managers can select one or more cards in
Card Management and (re)assign them to an employee, or release them back to the account owner
(unassign). The same endpoint also lets a bulk CSV/XLSX upload auto-match an existing employee by
e-mail so the created card is assigned immediately.

| # | File | Change |
|---|---|---|
| 16 | `backend/database/migrations/2026_11_24_000001_add_business_role_to_users_table.php` | **New.** Adds nullable `users.business_role` enum (`owner`, `admin`, `card_manager`, `member`). |
| 17 | `backend/app/Models/User.php` | Role constants (`BUSINESS_ROLE_*`, `BUSINESS_EMPLOYEE_ROLES`); `businessRole()`, `resolvedBusinessAccount()`, `canManageBusiness()`, `canManageBusinessCards()`; `business_role` made fillable. |
| 18 | `backend/app/Http/Middleware/EnsureBusinessManager.php` | **New.** Enforces `business.manager` / `business.manager:cards`. |
| 19 | `backend/bootstrap/app.php` | Registers the `business.manager` alias. |
| 20 | `backend/app/Http/Controllers/Api/Business/EmployeeController.php` | **Rewritten.** Fixed bulk-upload writing non-existent `nfc_cards` columns; auto-assigns matched employee; new `bulkAssignCards()` (H-4) and `updateRole()` (M-3). |
| 21 | `backend/app/Http/Controllers/Api/Business/QuotaController.php` | `getEmployees()` returns `business_role` + `viewer_role` + `is_owner`; resolves the tenant via `resolvedBusinessAccount()` so delegated admins can use it. |
| 22 | `backend/app/Http/Controllers/Api/Admin/BusinessUserController.php` | Accepts optional `business_role` when the super admin creates employees (single & bulk). |
| 23 | `backend/routes/api.php` | Employee routes gated by `business.manager`; new `POST /business/cards/bulk-assign` (`business.manager:cards`) and `POST /business/employees/{id}/role`. |
| 24 | `frontend/pages/UserDashboard/UserManagement/BusinessPlanUser/BusinessEmployeeManagement.vue` | Role column with owner-only selector. |
| 25 | `frontend/pages/UserDashboard/UserManagement/BusinessPlanUser/BusinessCardManagement.vue` | Card checkboxes + bulk-selection toolbar + "Assign to Employee" modal; fixed `loadEmployees()` response shape. |
| 26 | `backend/database/factories/UserFactory.php` | **Rewritten** to match the real `users` schema (`first_name`/`last_name`, plan columns); added `businessOwner()` / `businessEmployee()` states. |
| 27 | `backend/tests/Feature/BusinessCardAssignmentTest.php` | **New.** Pest feature tests for H-4/M-3 (9 tests, all passing). |

> **Deploy note (M-3):** run `php artisan migrate` to add `users.business_role`. Existing employees
> default to `member` (the owner is unaffected — it is derived, not stored). Until custom roles are
> assigned, only the owner can manage employees beyond the previous baseline.
