## Redesign user portals and enforce plan/role access

### What changed
**User portal (Free / Basic / Premium / Business)**
- New portal shell in the admin's Ink & Brass design: ink sidebar grouped into Workspace / Team / Account, lock state + reason for features the plan lacks, plan chip, breadcrumb top bar, mobile drawer, confirm dialog for sign-out.
- `composables/usePortalAccess.js` — one frontend rule set mirroring `EntitlementService` and the Business role middleware. Drives the sidebar, the dashboard and a new `middleware/portal-access.global.js` route guard, so restricted pages can't be opened by URL (e.g. a Basic user on the Premium builder, a Business member on Team Members).
- Dashboard rebuilt on real data (card statuses, taps, plan features from `/plan-prices/public`, team size, actionable alerts) — removed fabricated progress values and invented upgrade copy.
- Legacy `/UserDashboard/ProfileBuilder` (duplicate builder) now forwards to the user's plan builder; invoices page renders inside the portal layout.

**Fixes**
- Business analytics returned 500 on SQLite (`HOUR()` is MySQL-only) — now driver-aware; MySQL query unchanged.
- Basic/Free Analytics crashed (local `ref` variable shadowed the Vue auto-import) and read the wrong card field.
- Business owner's own card missing from Business Cards / Analytics (`/nfc-cards` now includes `user_id = owner`).
- Broken "View analytics" route on business cards; hardcoded quota of 10 and "Premium" label on Team Members.
- Settings: wrong settings endpoint (saved notification prefs never loaded); new `POST /settings/export-data` (read-only, excludes credentials/2FA); non-functional privacy toggles and delete-account button replaced with honest alternatives; removed "/mo · charged monthly" copy; 2FA QR code rendered locally (`uqr`) instead of sending the secret to a third-party QR service.
- Card status badge classes use theme tokens (safelisted in Tailwind since they come from the API).

**Also included** — this branch carries previously unpushed commits and earlier uncommitted work: admin panel refinement, onboarding flow enforcement / account reactivation, payment redirect fix, guest checkout and shared admin components.

### Testing
- Production build (`nuxi build`) passes; backend Pest suite 11/11 passing; changed PHP files linted.
- Browser-verified as seeded Basic and Business-owner accounts: dashboard, nav/active states, URL guards, Analytics, Business Cards → analytics deep link, Team Members, Activity Log, Settings, Invoices, sign-out, mobile drawer.
- Business member / Free access checked at API level only (no seeded passwords for those accounts).

### Reviewer notes
- Profile builders (~5.5k lines each) were not redesigned.
- `GET /settings` no longer returns the placeholder `security_settings` / `privacy_settings` keys (no consumers).
- Open question: `free-plans:deactivate-expired` compares `subscription_end_date` (already start + trial_days) against `now − trial_days`, so free accounts stay active ~2× the trial window. Left unchanged in case it's an intended grace period.
- No self-serve account deletion exists; the Settings page now routes closure requests to support.

🤖 Generated with [Claude Code](https://claude.com/claude-code)
