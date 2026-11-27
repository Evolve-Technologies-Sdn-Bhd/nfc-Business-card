# Deploying NFCGo over SSH (backend + frontend)

Server: aaPanel · project root `/www/wwwroot/nfcgo.clbgroups.com`
Backend = Laravel (`backend/`, SQLite) · Frontend = Nuxt SPA, built on the server into `spa/`

The server deploys **whatever is on `origin/main`**. So the flow is:
**commit → push → merge to main → SSH → back up DB → run the deploy script → purge Cloudflare.**

---

## 1. On your PC: commit and push

Run in PowerShell from `C:\Users\USER\Desktop\nfc-Business-card`.

Check what will go in (do **not** commit `.claude/`, `frontend/.verify-avatar.mjs`, any `.env`, or `*.sqlite*`):

```powershell
git status
```

Stage the app code and commit:

```powershell
git add backend frontend DEPLOY_SSH.md
git reset frontend/.verify-avatar.mjs
git commit -m "feat: business company settings, intro video, fixes"
git push -u origin feat/user-portal-ink-brass
```

Open a PR from `feat/user-portal-ink-brass` into `main` on GitHub and merge it.
(The server script does `git reset --hard origin/main`, so unmerged branch code will **not** be deployed.)

## 2. SSH into the server

```powershell
ssh root@159.138.233.253
```

(Use `-p <port>` if SSH isn't on 22. Confirm the IP; older scripts mention a different one.)

## 3. Back up the database (always — this release adds 6 migrations)

```bash
cd /www/wwwroot/nfcgo.clbgroups.com/backend
cp database/database.sqlite database/database.sqlite.bak-$(date +%Y%m%d-%H%M)
```

New migrations in this release (`2026_11_27_*`): card template layout, card design snapshot, `is_digital` on cards, `business_settings` table, grouped profile action fields, intro video on landing pages.

## 4. Run the deploy script

Use `deploy.txt` (v2 — it backs up/restores `.env`, verifies env, runs composer, migrates, builds the SPA and swaps it in atomically with rollback).

Copy it **outside the repo first**, because the script itself runs `git reset --hard` and bash misbehaves if the file it's executing changes mid-run:

```bash
cd /www/wwwroot/nfcgo.clbgroups.com
git fetch origin && git show origin/main:deploy.txt > /root/nfcgo-deploy.sh
chmod +x /root/nfcgo-deploy.sh
bash /root/nfcgo-deploy.sh 2>&1 | tee /root/deploy-$(date +%Y%m%d-%H%M).log
```

What it does, in order:

| Step | Backend | Frontend |
|---|---|---|
| 1 | Back up `backend/.env`, `frontend/.env`; `git reset --hard origin/main`; restore `.env`s | |
| 1b | Check `DB_DATABASE` is the server SQLite path | Check `NUXT_PUBLIC_API_BASE_URL` is set and clean |
| 2–5 | `composer install --no-dev`, permissions, `optimize:clear`, `migrate --force`, `optimize` | |
| 6 | | `npm install`, `nuxt generate` (4 GB heap) |
| 7 | | Move old `spa/` to `.spa-live-<ts>`, swap new build in |
| 8–9 | `queue:restart`, keep last 3 backups | |

It stops on the first error (`set -e`). Read the tail of the log if it does.

## 5. Fix ownership (aaPanel runs PHP as `www`)

```bash
cd /www/wwwroot/nfcgo.clbgroups.com
chown -R www:www spa backend/storage backend/bootstrap/cache backend/database
chmod -R 775 backend/storage backend/bootstrap/cache
chmod 664 backend/database/database.sqlite
```

## 6. After deploy

1. Cloudflare → Caching → Configuration → **Purge Everything**.
2. Hard-refresh (Ctrl+Shift+R) https://nfcgo.clbgroups.com/
3. Smoke test: login → dashboard → profile builder → a public profile → admin dashboard.
4. Watch for errors:

```bash
tail -f /www/wwwroot/nfcgo.clbgroups.com/backend/storage/logs/laravel.log
```

## Rollback

Frontend only (bad build):

```bash
cd /www/wwwroot/nfcgo.clbgroups.com
mv spa spa.broken && mv "$(ls -1dt .spa-live-* | head -1)" spa
```

Backend (bad migration / code):

```bash
cd /www/wwwroot/nfcgo.clbgroups.com
git reset --hard <previous-good-commit>
cp backend/database/database.sqlite.bak-<timestamp> backend/database/database.sqlite
cd backend && php artisan optimize:clear && php artisan optimize
```

## Troubleshooting

| Symptom | Fix |
|---|---|
| `dubious ownership` from git | `git config --global --add safe.directory /www/wwwroot/nfcgo.clbgroups.com` |
| `nuxt generate` killed / out of memory | Add swap, or build locally (`npx nuxt generate` in `frontend/`) and `scp -r frontend/.output/public root@server:/www/wwwroot/nfcgo.clbgroups.com/spa-new` then swap |
| 500 on every API call | `php artisan optimize:clear`, then check `laravel.log`; usually `.env` or permissions |
| `attempt to write a readonly database` | Step 5 ownership on `backend/database/` (the folder, not just the file) |
| Old UI still showing | Cloudflare purge + delete `spa/index.html.gz` / `.br` if present |
