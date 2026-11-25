# NFCGo Admin Portal — Comprehensive Module Audit, Functional Gap Analysis, Information Architecture & System Improvement

Audience: Product Architect / Business System Analyst / UI-UX / Solution Architect / Nuxt + Laravel Engineer
Scope: `frontend/` (Nuxt 3) + `backend/` (Laravel) — **Admin Portal only**
Method: direct source inspection (routes, controllers, middleware, models, migrations, Vue pages, layout). Every claim below is evidence-based; file paths are given.
Status: Audit complete. One safe improvement implemented (see §13). Remaining items are prioritised in §12 with implementation plans.

---

## 0. Executive Summary

The Admin Portal is **far more mature than a typical "audit target"**: 20 functional admin pages back ~40 real, working API endpoints (no stubs/placeholders were found). The data model is sound and the core operational workflows (user management, business accounts, card order lifecycle, templates, designers, invoices, refunds, notifications, feedback, FAQ, audit log, configuration) genuinely work end-to-end.

The real problems are **not missing pages — they are weak governance and a few broken edges**:

| # | Severity | Finding | Evidence |
|---|---|---|---|
| 1 | **Critical** | **RBAC is defined but not enforced.** A ~25-permission catalogue exists, but no `Gate`/`Policy` exists and `User::hasAdminPermission()` is never called by any endpoint. Every `/admin/*` route is gated only by a boolean `is_admin` check. | `app/Http/Middleware/AdminMiddleware.php`, `app/Http/Controllers/Api/AdminRoleController.php`, `app/Models/User.php::hasAdminPermission()` |
| 2 | **Critical/High** | **"Profile Setting" page is broken.** It calls `POST /admin/profile/update` and `POST /admin/profile/change-password`, which are **not registered** in `routes/api.php`. Saves silently 404. | `frontend/pages/AdminManagement/profile-settings.vue:475,512`; absent in `backend/routes/api.php` |
| 3 | **High** | **Payment credentials returned to the browser in plaintext** (Fiuu merchant/verify/secret keys) and settings are stored in the **cache, not a table** (ephemeral). | `app/Http/Controllers/Api/Admin/AdminSystemSettingsController.php` |
| 4 | **High** | **Duplicate/orphan admin pages** (`price-management.vue`, `legal-documents.vue`) and a **duplicated Feedback tab** inside `chatbot-faq.vue`. | Frontend inventory (see §2.4) |
| 5 | **Medium/High** | **Backend capability with no admin UI:** manual bank-transfer verification endpoints exist but there is no page for them. | `routes/api.php` (`/admin/payments/manual-transfers/*`); no matching `.vue` |
| 6 | Medium | Refund duplicate-guard: refundable-amount is validated, but there is **no constraint preventing multiple pending refunds per transaction**. | `app/Http/Controllers/Api/RefundController.php` |
| 7 | Medium | Audit log captures `new_values` but `old_values` is effectively empty; no actor/Action→before/after for updates. | `app/Http/Middleware/LogActivity.php` |
| 8 | Medium | Notifications are **in-app only**; no email delivery channel, no delivery-status/failed tracking. | `app/Services/NotificationService.php` |

**Bottom line:** the portal needs *hardening and rationalisation*, not a rebuild. The highest-value work is (a) enforce the permissions that already exist, (b) fix the broken Profile Setting path, (c) protect integration secrets, and (d) de-duplicate the navigation surface.

---

## 1. PHASE 1 — Understanding the Existing System

### 1.1 Backend admin surface
All admin routes are declared in `backend/routes/api.php:366-586` under one group:

```php
Route::middleware(['auth:sanctum', 'admin', \App\Http\Middleware\LogActivity::class])
     ->prefix('admin')->group(function () { ... });
```

Controllers backing the admin surface (all perform real DB work):

- `Api\AdminController` (~1850 lines) — dashboard, system stats, user CRUD, business employees, NFC card order-flow, audit logs, CSV export.
- `Api\AdminRoleController` — role/permission catalogue + assignment.
- `Api\AdminNotificationController` — notification list, broadcasts, order approve/reject.
- `Api\AdminChatbotController` — FAQ CRUD + feedback moderation.
- `Api\RefundController`, `Admin\InvoiceController`, `Admin\ManualBankTransferController`, `Admin\PlanPriceController`.
- `Admin\BusinessUserController`, `Admin\BusinessUserAssignmentController`, `Admin\BusinessInquiryController`.
- `Admin\ProfileBuilderSectionController`, `Admin\ProfileBuilderFieldController`, `Api\ProfileDesignController`, `Admin\CardDesignController`, `Api\CardTemplateController`.
- `Admin\AdminSystemSettingsController`, `Api\AdminExportController`.

### 1.2 Frontend admin surface
20 pages under `frontend/pages/AdminManagement/` (plus `invoices/index.vue`), all reachable except two orphans (§2.4). Shared chrome: `frontend/layouts/AdminManagement.vue`, guarded by `frontend/middleware/admin.ts`.

### 1.3 Data model (source of truth)
| Entity | Authoritative table | Notes |
|---|---|---|
| User / Admin | `users` | `is_admin`, `admin_role` (`super_admin`/`admin`/`moderator`), `admin_permissions` (JSON). **No RBAC tables.** |
| Business account | `users` | `subscription_plan='business'` + self-FK `parent_business_id` → employees. **No `organisations` table.** |
| NFC card | `nfc_cards` | 11 statuses; order/payment/shipping timestamps denormalised. **No status-history table.** |
| Card template (product) | `card_templates` | Static artwork, `processing_status` (n8n pipeline). |
| Card design (designer) | `card_designs` | Element-based editable designs (`front_elements` JSON, mm coords). Distinct from templates. |
| Digital profile | `landing_pages` | 1:1 with `nfc_cards`; ~150 fields incl. `section_layout`/`field_layout`. |
| Builder catalogue | `profile_builder_sections`, `profile_builder_fields`, `profile_design_options` | Admin-managed choices; user selections stored denormalised on `landing_pages`. |
| Orders | `orders` + `order_items` | Exist (added 2026-11-23). **No admin UI yet.** |
| Payment | `transactions` | Links order/subscription/card; manual-bank fields. |
| Subscription | `subscriptions` | Source of truth (`users.subscription_*` is a write-through cache). |
| Invoice | `invoices` | Snapshot `billing_details`/`company_details`. |
| Refund | `refunds` | Links `transactions`. |
| Enquiry | `business_inquiries` | Triage queue (public form). |
| Notification | `notifications` | In-app. |
| Audit | `activity_logs` | Actor/action/entity/`new_values`/ip/ua. |
| Chatbot | `chatbot_questions`, `chatbot_feedback`. |
| Settings | **none** | Global settings live in **Cache** (`Cache::put('system_settings', ...)`). |

### 1.4 Workflow data flow (as implemented)
Card ordering: user pays → `nfc_cards.status` moves `pending_payment → awaiting_payment_verification → payment_verified → processing → shipped → delivered → active`, driven by admin actions in `AdminController` (`verifyNfcCardPayment`, `markNfcCardProcessing`, `markNfcCardShipped`, …). This is real and validated.

---

## 2. PHASE 2 — Information Architecture Evaluation

### 2.1 Current vs intended navigation
The live sidebar in `frontend/layouts/AdminManagement.vue:448-548` **already matches the intended IA** in the brief exactly (Dashboard / Users / Cards / System / Account). No broken links. This is a strength.

### 2.2 Structure verdict

| Decision | Module | Rationale |
|---|---|---|
| **Keep** | Dashboard, User Management, Business Users, Business Enquiries, Roles & Permissions, NFC Card Management, Card Templates, Card Designer, Profile Builder Design, Analytics, Notifications, User Feedback, Chatbot FAQ, Audit Log, Profile Setting, Configuration | Each has a clear operational purpose and a working backend. |
| **Rename** | "Profile Builder Design" → **"Profile Layouts"** | Reads as a *designer* but is actually the admin catalogue of profile sections/fields/design options + per-business assignments. The current name invites confusion with Card Designer. |
| **Relocate** | Invoices, Refunds → out of **Cards** into a new **"Commercial"** group (with **Orders**) | Invoices/refunds are financial documents, not card artefacts. Grouping them with Orders finally gives `orders` an admin home. |
| **Merge** | `price-management.vue`, `legal-documents.vue` → **Configuration tabs** | Both are already fully implemented as tabs inside `configuration.vue`; the standalone pages are orphan duplicates. |
| **Remove (UI only)** | Duplicated Feedback tab inside `chatbot-faq.vue` | Keep the dedicated `feedback.vue`; chatbot-faq should be FAQ-only. |
| **Separate** | (confirm separation) Card Designer vs Profile Builder Design | Already correctly separated — do **not** merge (matches the brief's warning). |
| **Add (from existing backend)** | **Manual Bank Transfer verification** page | Endpoints `/admin/payments/manual-transfers*` exist with no UI. |
| **Role-restrict** | Roles & Permissions, Configuration | Super-admin only. |

### 2.3 Missing modules (assessed against real need — see §8 for justification)
Orders Management, Manual Bank Transfer verification UI, Card Stock/Inventory, System Health. **Not recommended:** Promotions, Subscriptions admin, Ticket system (feedback covers it), content CMS (no clear need).

### 2.4 Orphan / duplicate pages (evidence)
`price-management.vue` and `legal-documents.vue` exist in `frontend/pages/AdminManagement/` but are **not referenced anywhere** (grep for `price-management|legal-documents` found no links outside themselves) and duplicate `configuration.vue` tabs. `chatbot-faq.vue` re-implements the Feedback moderation UI already provided by `feedback.vue`.

---

## 3. PHASE 3 — Dashboard

**Implemented (good):** `AdminController::dashboard()` returns real month-over-month trends (users/profiles/cards/analytics) with `safePercentChange`, recent users, subscription breakdown, and a **mixed activity feed** (NFC/profile `Analytics` + `ActivityLog` system events). `index.vue` renders stat cards from real API data (comment: *"no hardcoded fakes"*) with a 30s auto-refresh and loader/empty states. `system_stats` surfaces active sessions + last backup.

**Gaps vs the brief:**
- No **date-range filter** (fixed to month-over-month only).
- **Commercial overview absent** on the dashboard: no totals for orders / sales / pending & failed payments / refund requests / outstanding invoices.
- **Card operations absent**: no "awaiting activation / blocked / lost" breakdown, no template-usage or design-activity widgets.
- **System health minimal**: no gateway status, failed jobs, failed notifications, security alerts.
- Only *some* numbers are clickable (stat cards are static); the brief asks stats to deep-link to their management pages.

**Recommendation:** add one backend endpoint returning a commercial+card-ops summary (e.g. `GET /admin/dashboard?range=30d`), keep the dashboard to act-on-able KPIs, and make each card `NuxtLink` to its module with the matching filter pre-applied. *(Priority: Medium — see §12.)*

---

## 4. PHASE 4 — Users Module

### 4.1 User Management — **Improve**
Existing: `users.vue` (list, search, status/plan/date filters, pagination), full CRUD via `AdminController::getUsers/getUser/createUser/updateUser/deleteUser`, super-admin protection, quota sync, CSV export.

Gap: **No rich detail view.** There is a modal, not the brief's tabbed profile (Overview · Account · NFC Cards · Digital Profiles · Orders & Transactions · Invoices · Activity History). Card/order/invoice linkage for a user is not surfaced in one place.
Recommendation: convert the user modal into a **detail page/route** `users/[id].vue` with those tabs, reusing existing per-entity list endpoints filtered by `user_id`. Do not duplicate card/payment mutation logic inside it (link out instead). *(Priority: High.)*

### 4.2 Business Users — **Keep (mostly complete)**
Model confirmed: a business user is a `users` row with `subscription_plan='business'` and `parent_business_id = null`; employees are `users` rows pointing at the parent. Per-business feature overrides live in `business_user_assignments`. `business-users.vue` supports listing, stats, detail, single + bulk employee creation, and quota updates.
Gap: no **verification status** concept; the brief correctly warns against inventing multi-tenant complexity — **do not** add an organisations table. *(Priority: Low.)*

### 4.3 Business Enquiries — **Improve**
Existing: `business-inquiries.vue` + `Admin\BusinessInquiryController` (list, statistics, show, update with `admin_notes`, stamps `handled_by`/`handled_at`).
Gaps vs brief: only 3 statuses (`new`/`in_progress`/`resolved`) instead of the 7-stage pipeline; **no explicit assignee picker**, no follow-up activity timeline, **no "convert to business customer"** action.
Recommendation: extend the status enum (safe additive migration) to New/Under Review/Contacted/In Progress/Converted/Rejected/Closed; add an assignee + notes/timeline; wire a "Convert" action that pre-fills business-account creation. *(Priority: Medium.)*

### 4.4 Roles & Permissions — **Fix (Critical)**
Existing: `roles-permissions.vue`; `AdminRoleController::index` returns admins + assignable roles + a **permission catalogue** (7 domains, ~25 keys: `users.*`, `cards.*`, `billing.*`, `content.*`, `support.*`, `analytics.*`, `system.*`). Assignment enforces super-admin and blocks self/other-super-admin edits.
**Critical gap: none of those permissions are enforced.** No `Gate::define`, no `Policies`, and no controller calls `hasAdminPermission()`. A user with `is_admin=true` and *zero* permissions can call every admin endpoint.
Recommendation (see §11): add a permission middleware + route→permission map and enforce server-side. *(Priority: Critical.)*

---

## 5. PHASE 5 — Cards Module

### 5.1 NFC Card Management — **Keep, extend**
Existing: 11-status lifecycle with real admin transitions (verify/reject payment, mark processing/shipped/delivered, cancel) and CSV export.
Gaps vs brief: **no reassign**, **no block/suspend**, **no record lost/damaged**, and **no status/assignment history table** (timeline is inferred from a few timestamp columns).
Recommendation: add a `nfc_card_status_history` table (additive) written on each transition, plus `reassign`, `block/suspend`, `report lost/damaged` endpoints with valid state-transition guards. *(Priority: High for history; Medium for reassign/block.)*

### 5.2 Card Templates — **Keep (complete)**
`card-templates.vue` + `CardTemplateController` (CRUD, preview, visibility toggle, retry, n8n processing pipeline, `plan_types`, `is_hidden`). Correctly distinct from designs.

### 5.3 Card Designer — **Keep (complete)**
`card-designer.vue` + `Admin\CardDesignController` — canvas, front/back, text/image/QR elements, mm coordinates, background upload, PDF export. Correctly distinct from templates.

### 5.4 Profile Builder Designer Management — **Keep, rename**
`profile-builder-design.vue` + section/field/design-option controllers + business assignments. Handles reusable layouts, section order, plan entitlements and per-business overrides. Distinction from Card Designer is correct. Rename to "Profile Layouts" (§2.2).

### 5.5 Invoices — **Keep, relocate, guard**
`invoices/index.vue` + `Admin\InvoiceController` (list/show/statistics/regenerate/mark-issued/**mark-paid**/cancel/resend/download/preview). Relocate under Commercial. **Risk:** `markAsPaid` allows a manual paid-status override — restrict to a permission and require an audit note (the brief warns against unverified paid-status changes).

### 5.6 Refund — **Keep, harden (financial control)**
`refunds.vue` + `RefundController::getPendingRefunds/processRefund`. Approve path calls `PaymentService::processRefund()` → Fiuu gateway; refundable amount is validated on request.
Gap: **no protection against duplicate pending refunds** for the same transaction.
Recommendation: enforce a single open refund per transaction (conditional index / DB constraint + idempotency key on processing) and a refund ceiling per permission. *(Priority: Critical/High — financial.)*

---

## 6. PHASE 6 — System Module

### 6.1 Analytics — **Improve**
`stats.vue` uses `GET /admin/stats`, `/admin/dashboard`, plus CSV exports (`/admin/export/{users,nfc-cards,analytics}`). Solid baseline. Gaps: thin **commercial** analytics (revenue/payment success-failure/refund trends/product performance) and **engagement** analytics depth. Recommendation: server-side report endpoints with date-range params + drill-down; only surface metrics the system actually records (`Analytics` table). *(Priority: Medium.)*

### 6.2 Notifications — **Improve**
`notifications.vue` + `AdminNotificationController` (list, stats, unread count, announcement, send-to-users, system-message, approve/reject order, cleanup). **In-app only** — `NotificationService` writes DB rows; **no email channel**, no templates/status/failed tracking/retry. Recommendation: keep in-app as the source of truth; if email is required, add it explicitly (template + delivery log + retry) rather than implying it exists. *(Priority: Medium.)*

### 6.3 User Feedback — **Keep, de-duplicate**
`feedback.vue` + `AdminChatbotController` feedback methods (list, stats, read, status, delete) over `chatbot_feedback`. Remove the duplicate Feedback tab from `chatbot-faq.vue`. *(Priority: Low.)*

### 6.4 Chatbot FAQ — **Keep**
`chatbot-faq.vue` + `chatbot/questions` CRUD + toggle-active over `chatbot_questions`. Recommendation: evaluate a single content source shared with any public FAQ to avoid duplicate answers. *(Priority: Low.)*

### 6.5 Audit Log — **Improve (integrity)**
`SystemAuditLog.vue` + `AdminController::auditLogsIndex/auditLogsExportCsv` with filters + CSV. Gaps: `old_values` effectively empty (no before/after), no immutability guarantee, no correlation id. Recommendation: capture `old_values` in `LogActivity`, make the table append-only (no delete route — already absent), strip secrets, add a request id. *(Priority: Medium.)*

---

## 7. PHASE 7 — Account Module

### 7.1 Profile Setting — **Fix (broken)**
`profile-settings.vue` posts to `/admin/profile/update` and `/admin/profile/change-password`; **neither route exists** in `backend/routes/api.php` → both actions 404. Additionally the page/layout reference `user.profile_image`, but `User` only has `account_image` (`app/Models/User.php:53`, `$appends=['full_name']`), so the admin avatar falls back to the default.
Also note `PUT /user/account → ProfileController::updateUserInfo` is declared but that method **does not exist** in `ProfileController`.
Recommendation (see §12 P1): add the two admin profile endpoints (validate unique email excluding self; `Hash::check` + `Hash::make` for password) and align the avatar field. *(Priority: Critical/High.)*

### 7.2 Configurations — **Harden**
`configuration.vue` tabs: General, Brand, Plan Pricing, Legal, Payment, Email. Backed by `AdminSystemSettingsController`, stored in **Cache** (no table → settings can vanish on cache clear). **Security issue:** `GET /admin/system-settings/payment` returns Fiuu `merchant_id`/`verify_key`/`secret_key`; email password stored raw.
Recommendation: persist settings in a `system_settings` table; **never return secret values** (send a masked placeholder + "has value" flag; only write when a new value is supplied); encrypt at rest; keep the public brand endpoint as-is (it leaks nothing). *(Priority: High.)*

---

## 8. PHASE 8 — Missing Modules & Unnecessary Features

For each candidate addition: need → problem solved → relation → duplicate? → new entities? → integrate instead?

| Candidate | Needed? | Rationale |
|---|---|---|
| **Orders Management** | Yes (High) | `orders`/`order_items` exist but have no UI; order history is currently only visible via card rows. Add a read-first Orders list + detail; link invoices/refunds/transactions. No new tables. |
| **Manual Bank Transfer verification** | Yes (High) | Backend already implemented (`ManualBankTransferController`), **no UI** — a pure gap. Add a page under Commercial/Payments. |
| **Card Stock / Inventory** | Marginal | Only if physical stock is tracked today (it is not). Defer until a real stock model exists; do **not** build speculatively. |
| **System Health Monitoring** | Partial | Sessions + last backup exist. Add gateway-status + failed-notification counters only if/when those signals are recorded. |
| **Product / Categories / Promotions / Subscriptions admin / CMS / Tickets** | No | No clear business driver; most duplicate existing modules, or the domain (single card product) does not warrant them. |

**Remove/simplify (safe):** orphan `price-management.vue` + `legal-documents.vue` (merge into Configuration, then delete the pages or redirect); duplicate Feedback tab in `chatbot-faq.vue`. No DB records should be touched — these are UI-only.

---

## 9. PHASE 9 — Cross-Module Workflows & Data Integrity

| Workflow | Status | Broken / missing link |
|---|---|---|
| **A. User Registration → Management → Cards** | OK | User detail lacks unified cards/profile view (§4.1). |
| **B. Business Enquiry → Conversion** | Partial | No convert-to-customer action, no follow-up timeline, 3-status limit (§4.3). |
| **C. Card Lifecycle** | OK (transitions validated) | No status/assignment history table; no reassign/block/lost (§5.1). |
| **D. Digital Profile** | OK | `landing_pages` 1:1 with card; catalogue tables only hold choices (no overwrite of user content). Good. |
| **E. Commercial (Order → Payment → Invoice → Refund)** | **At risk** | `orders` (new) vs `nfc_cards` denormalised order fields create a **dual source of truth**; no admin Orders UI; refund duplicate-guard missing. |
| **F. Notifications** | Partial | No delivery status/failure tracking; in-app only (§6.2). |
| **G. Feedback → Resolution** | OK | Resolved loop present; duplicated entry point in chatbot-faq (§6.3). |
| **H. Admin Login → Permission → Action → Audit** | **Broken** | Permission step is not enforced; audit `old_values` incomplete (§4.4, §6.5). |

**Source-of-truth issues:** (1) card order state duplicated between `orders` and `nfc_cards`; (2) `users.subscription_*` is a documented cache of `subscriptions` — acceptable but must stay write-through; (3) settings in cache rather than DB (§7.2).

---

## 10. PHASE 10 — Admin UI/UX

Strengths: consistent design tokens, grouped collapsible sidebar, notification/feedback drawers, badges, loaders/empty states, responsive layout.

Improvements:
- **Implemented this pass:** nested-route page title + active-nav resolution (previously `/AdminManagement/invoices/1` showed "Admin Panel" and lost its highlight). See §13.
- Orphan pages creating confusing dead-ends (§2.4).
- Dashboard stat cards not clickable (§3).
- Relocate Invoices/Refunds to a Commercial group to match how operators think (§2.2).

---

## 11. PHASE 11 — Roles, Access Control & Security

**Current state:** `AdminMiddleware` does only `if (!$user || !$user->is_admin) return 403;`. `User::hasAdminPermission()` and `isSuperAdmin()` exist but are unused by endpoints (only role *writes* check `isSuperAdmin()`).

**Required (Critical):** enforce server-side, per action, using the **existing** architecture:
1. `PermissionMiddleware` (or middleware parameters) reading a **route→permission map**, calling `$user->hasAdminPermission($perm)`; super-admin already returns `true`.
2. Map sensitive actions: `refunds.process → billing.refunds`, `invoices.mark-paid → billing.invoices`, `users.assign_role → users.assign_role`, `system-settings.* → system.system_settings`, `roles.* → system.manage_admins`, `roles-permissions page → system.manage_admins`, etc.
3. Keep frontend visibility aligned (hide nav/actions without permission) but never rely on it.

**Financial & access protections:** refunds (idempotency + ceiling), invoice manual paid-override, role assignment, global configuration, integration secrets (§5.5–5.6, §7.2).

---

## 12. PHASE 12 — Prioritised Findings & Implementation Plan

| ID | Area | Existing | Required | Gap | Recommendation | Impact | Implementation | Priority |
|---|---|---|---|---|---|---|---|---|
| P1-1 | RBAC | Catalogue defined, unused | Enforced per action | No Gate/Policy; `hasAdminPermission` unused | Add permission middleware + route map | Prevents unauthorised admin ops | Backend (middleware + routes) | **Critical** |
| P1-2 | Profile Setting | Page posts to `/admin/profile/*` | Working endpoints | Routes missing → 404 | Add `updateAdminProfile` + `changeAdminPassword` + routes; fix avatar field | Admin cannot save profile/password | Backend + minor FE | **Critical** |
| P1-3 | Refund | Gateway refund + amount check | No duplicate refunds | No unique open-refund guard | Enforce one open refund/txn + idempotency | Prevents double payouts | Backend (+migration) | **Critical** |
| P2-1 | Config secrets | Fiuu secret returned plaintext | Never expose secrets | Keys/email password sent to browser | Mask on read; write-only when provided; encrypt; persist in table | Stops credential leakage | Backend + FE | **High** |
| P2-2 | Manual transfers | Backend endpoints exist | Admin UI | No page | Add verification page | Unlocks existing capability | Frontend | **High** |
| P2-3 | User detail | List + modal | Tabbed detail (cards/profiles/orders/invoices/activity) | No unified view | Add `users/[id].vue` tabs | Faster ops, fewer errors | Frontend (+existing APIs) | **High** |
| P2-4 | Card lifecycle | Order-flow actions | Reassign/block/lost + history | No history table; no actions | Add history table + guarded endpoints | Traceability + control | Backend + FE | **High** |
| P3-1 | IA cleanup | Orphan/duplicate pages | Single source | 2 orphans + dup Feedback tab | Merge into Configuration; delete/redirect; remove dup tab | Clearer navigation | Frontend | **Medium** |
| P3-2 | Dashboard | Real KPIs/trends | Commercial/card-ops + date range + deep links | Missing KPIs/filters | Add summary endpoint + clickable cards | Better oversight | Backend + FE | **Medium** |
| P3-3 | Enquiries | 3 statuses | 7-stage pipeline + assignee + convert | Limited workflow | Extend enum + assignee/notes/convert | Sales follow-through | Backend + FE | **Medium** |
| P3-4 | Audit | Filter + CSV | before/after + append-only + correlation id | `old_values` empty | Capture old values; strip secrets | Reliable audit | Backend | **Medium** |
| P3-5 | Notifications | In-app | Real channels only | No email/status/retry | Add email channel or stop implying it | Honest comms | Backend | **Medium** |
| P4-1 | Orders | Tables exist | Admin visibility | No UI | Read-only Orders list/detail | Order oversight | Frontend | **High** |
| P4-2 | UI polish | Good baseline | Clickable stats, clearer grouping | Minor | Commercial group + clickable cards | UX | Frontend | **Low** |

---

## 13. Implemented in This Pass

**Admin navigation fix — `frontend/layouts/AdminManagement.vue`**
- Replaced exact-match `pageTitle`/`isActiveRoute` logic with longest-prefix resolution (`resolveNavItem`/`activeNavItem`).
- Effect: nested admin routes (e.g. `/AdminManagement/invoices/1`) now show the correct **page title** ("Invoices") and keep their sidebar item **highlighted**; the auto-expand watcher now expands the correct group for nested routes too.
- Risk: none (frontend-only, no behavioural change for top-level routes).

All other items in §12 require backend changes. **Backend was intentionally not modified**: PHP is not available in this environment (`php -v` → not found), so Laravel changes cannot be verified. Modifying authorization/financial code without the ability to run the suite would violate the brief's "preserve existing business operations" requirement.

---

## 14. Decisions Required

1. **Scope of next batch** — approve implementing P1-1 (RBAC enforcement) and P1-3 (refund guard)? These touch authorization and money paths and should be verified against the running app/tests before release.
2. **Settings persistence** — approve migrating global settings from cache to a `system_settings` table with encrypted secrets (P2-1)?
3. **IA changes** — approve (a) relocating Invoices/Refunds into a new **Commercial** group with Orders, (b) merging `price-management.vue`/`legal-documents.vue` into Configuration and removing them, (c) removing the duplicate Feedback tab from `chatbot-faq.vue`?
4. **Orders UI** — build the read-only Orders admin page (P4-1)?

Once confirmed, the recommended order is: **P1-2 → P1-1 → P1-3 → P2-1 → P2-2 → IO/IA cleanup → P2-3/P2-4 → analytics/audit hardening.**
