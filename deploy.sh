#!/usr/bin/env bash
# ============================================================
# NFC Business Card — Full Deployment Script (Backend + Frontend)
# Server: aaPanel 159.138.233.253  |  Domain: nfcgo.clbgroups.com
# Run from: /www/wwwroot/nfcgo.clbgroups.com
# Usage:  chmod +x deploy.sh  &&  ./deploy.sh
# ============================================================
set -euo pipefail

# ---------- CONFIG ----------
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACKEND_DIR="${PROJECT_ROOT}/backend"
FRONTEND_DIR="${PROJECT_ROOT}/frontend"
SPA_DIR="${PROJECT_ROOT}/spa"
BRANCH="${1:-main}"
LOG_FILE="${PROJECT_ROOT}/deploy-$(date +%Y%m%d-%H%M%S).log"

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; BLUE='\033[0;34m'; NC='\033[0m'
log()   { echo -e "${BLUE}[$(date '+%H:%M:%S')]${NC} $*" | tee -a "$LOG_FILE"; }
ok()    { echo -e "${GREEN}[$(date '+%H:%M:%S')] ✅${NC} $*" | tee -a "$LOG_FILE"; }
warn()  { echo -e "${YELLOW}[$(date '+%H:%M:%S')] ⚠️${NC} $*" | tee -a "$LOG_FILE"; }
fail()  { echo -e "${RED}[$(date '+%H:%M:%S')] ❌${NC} $*" | tee -a "$LOG_FILE"; exit 1; }

exec > >(tee -a "$LOG_FILE") 2>&1

log "========================================"
log "NFCGo DEPLOY START — $(date)"
log "Project root : ${PROJECT_ROOT}"
log "Branch       : ${BRANCH}"
log "Log file     : ${LOG_FILE}"
log "========================================"

# ============================================================
# STEP 0 — PRE-FLIGHT CHECKS
# ============================================================
log "STEP 0/7  Pre-flight checks..."

[ -d "$BACKEND_DIR" ]   || fail "Tiada folder backend/ di ${PROJECT_ROOT}"
[ -d "$FRONTEND_DIR" ]  || fail "Tiada folder frontend/ di ${PROJECT_ROOT}"
[ -f "${BACKEND_DIR}/artisan" ] || fail "${BACKEND_DIR}/artisan tidak wujud"
command -v git >/dev/null 2>&1   || fail "git tidak dijumpai"
command -v php >/dev/null 2>&1   || fail "php tidak dijumpai"
command -v node >/dev/null 2>&1  || fail "node tidak dijumpai"
command -v npm >/dev/null 2>&1   || fail "npm tidak dijumpai"

# --- Git safe.directory (elak ralat "dubious ownership" aaPanel) ---
git config --global --add safe.directory "${PROJECT_ROOT}" 2>/dev/null || true
ok "Pre-flight checks lulus."

# ============================================================
# STEP 1 — GIT PULL CODE TERBARU
# ============================================================
log "STEP 1/7  git pull origin ${BRANCH} ..."
cd "${PROJECT_ROOT}"

# Simpan apa-apa perubahan tempatan (cth. .env) sebelum pull
if ! git diff-index --quiet HEAD -- 2>/dev/null; then
  warn "Terdapat perubahan tempatan — stash dahulu..."
  git stash push -m "auto-stash sebelum deploy $(date)" || true
fi

git fetch --all --prune
git checkout "${BRANCH}"
git reset --hard "origin/${BRANCH}"
ok "Git pull selesai (branch: ${BRANCH})."

# ============================================================
# STEP 2 — BACKEND: composer install (jika perlu)
# ============================================================
log "STEP 2/7  Backend — composer install --no-dev ..."
cd "${BACKEND_DIR}"

if command -v composer >/dev/null 2>&1; then
  composer install --optimize-autoloader --no-dev --no-interaction --prefer-dist
  ok "Composer install selesai."
else
  warn "Composer binary tidak dijumpai PATH — cuba melalui composer.phar"
  if [ -f "${BACKEND_DIR}/composer.phar" ]; then
    php composer.phar install --optimize-autoloader --no-dev --no-interaction --prefer-dist
    ok "Composer (phar) install selesai."
  else
    warn "Tiada composer.phar — LANGKAU composer install. Pastikan vendor/ sudah terkini."
  fi
fi

# --- Storage permissions (Laravel standard) ---
log "Mengeset permission storage/ dan bootstrap/cache/ ..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chown -R www:www storage bootstrap/cache 2>/dev/null || warn "chown www:www gagal (bukan root?); ignore jika aaPanel sudah handle."

# --- Storage symlink ---
if [ ! -L "public/storage" ] && [ ! -d "public/storage" ]; then
  php artisan storage:link 2>/dev/null || warn "storage:link gagal — buat secara manual jika perlu."
  ok "storage:link dilaksanakan."
fi

# ============================================================
# STEP 3 — MIGRATION (PENTING — sebelum frontend build)
# ============================================================
log "STEP 3/7  php artisan migrate --force ..."
cd "${BACKEND_DIR}"

# --- Known-fallback: migration 2025_12_08_093110 dah di-defensive, tapi backup-on-error ---
set +e
php artisan migrate --force --no-interaction
MIGRATE_EXIT=$?
set -e

if [ $MIGRATE_EXIT -ne 0 ]; then
  warn "Migrate keluar dengan code ${MIGRATE_EXIT}. Cuba migrate --pretend untuk debug..."
  php artisan migrate --pretend || true
  fail "Migration GAGAL. Selesaikan ralat di atas dahulu sebelum teruskan."
fi
ok "Migration selesai."

# ============================================================
# STEP 4 — CACHE CONFIG + ROUTE + VIEW
# ============================================================
log "STEP 4/7  Cache config, route, view ..."
cd "${BACKEND_DIR}"

php artisan config:cache || warn "config:cache ada ralat — .env incomplete?"
php artisan route:cache  || warn "route:cache ada ralat (closure route?)"
php artisan view:cache   2>/dev/null || true
php artisan event:cache  2>/dev/null || true
ok "Cache selesai."

# ============================================================
# STEP 5 — BUILD SPA DENGAN MEMORI 4GB
# ============================================================
log "STEP 5/7  Frontend — npm install + nuxt generate (4GB RAM) ..."
cd "${FRONTEND_DIR}"

export NODE_OPTIONS="--max-old-space-size=4096"
log "NODE_OPTIONS=${NODE_OPTIONS}"

# --- Install dependencies (legacy-peer-deps menyelesaikan Vite conflict) ---
log "npm install --legacy-peer-deps ..."
npm install --legacy-peer-deps --no-audit --no-fund

# --- Pastikan .mjs ada execute permission ---
chmod +x node_modules/nuxt/bin/nuxt.mjs 2>/dev/null || true
chmod -R +x node_modules/.bin/ 2>/dev/null || true

# --- nuxt generate — guna direct binary untuk elak symlink rosak ---
log "Menjana SPA: node node_modules/nuxt/bin/nuxt.mjs generate ..."
node node_modules/nuxt/bin/nuxt.mjs generate
ok "Frontend generate selesai."

# ============================================================
# STEP 6 — SALIN OUTPUT KE DIREKTORI LIVE SPA
# ============================================================
log "STEP 6/7  Salin .output/public/* ke ${SPA_DIR}/ ..."

# --- Pastikan SPA_DIR wujud (git clean -fd boleh padam untracked dir ini) ---
mkdir -p "${SPA_DIR}"

# --- Safety: confirm .output/public wujud sebelum rm -rf ---
if [ ! -d "${FRONTEND_DIR}/.output/public" ]; then
  fail "Tiada folder ${FRONTEND_DIR}/.output/public/. Generate gagal."
fi

# --- Backup ringkas (1 rollback sahaja, elak full backup — space limited) ---
ROLLBACK_DIR="${PROJECT_ROOT}/.spa-rollback-$(date +%Y%m%d-%H%M%S)"
if [ "$(ls -A "${SPA_DIR}" 2>/dev/null)" ]; then
  log "Backup SPA semasa ke ${ROLLBACK_DIR} (simpan 1 rollback shj) ..."
  cp -a "${SPA_DIR}" "${ROLLBACK_DIR}"
  # Buang rollback yang lebih lama dari 1 jam
  find "${PROJECT_ROOT}" -maxdepth 1 -type d -name '.spa-rollback-*' -mmin +60 -exec rm -rf {} + 2>/dev/null || true
fi

# --- Bersihkan dan salin ---
log "Bersihkan ${SPA_DIR}/* ..."
rm -rf "${SPA_DIR:?}"/*

log "Salin fail dari .output/public ..."
cp -a "${FRONTEND_DIR}/.output/public/." "${SPA_DIR}/"

# --- Verify index.html wujud ---
[ -f "${SPA_DIR}/index.html" ] || fail "${SPA_DIR}/index.html tidak dijumpai selepas copy!"
[ -d "${SPA_DIR}/_nuxt" ]      || warn "Tiada ${SPA_DIR}/_nuxt/ folder — build mungkin gagal."
ok "Salin SPA selesai. Rollback disimpan di ${ROLLBACK_DIR}"

# ============================================================
# STEP 7 — RESTART QUEUE / OPTIMASI AKHIR
# ============================================================
log "STEP 7/7  Restart queue + akhir touches ..."
cd "${BACKEND_DIR}"

php artisan queue:restart 2>/dev/null || warn "queue:restart — pastikan queue worker berjalan melalui Process Manager aaPanel."
php artisan up            2>/dev/null || true
ok "Restart isyarat queue dihantar."

# ============================================================
# SUMMARY + CLOUDFLARE PURGE REMINDER
# ============================================================
echo
ok   "========================================"
ok   "DEPLOYMENT BERJAYA — $(date)"
ok   "========================================"
log  "Backend  : ${PROJECT_ROOT}/backend/public"
log  "Frontend : ${SPA_DIR}/index.html"
log  "Rollback : ${ROLLBACK_DIR} (jika ada)"
echo
warn "=== TINDAKAN LANJUTAN DI CLOUDFLARE ==="
warn "1. Buka Cloudflare Dashboard → Caching → Purge Cache"
warn "2. GUNA 👉 Custom Purge  👈 (BUKAN Purge Everything)"
warn "   Salin dan paste URL berikut satu per satu:"
warn "   • https://nfcgo.clbgroups.com/"
warn "   • https://nfcgo.clbgroups.com/profile/*"
warn "   • https://nfcgo.clbgroups.com/dashboard/*"
warn "3. Jika masih nampak versi lama: barulah guna Purge Everything (sebagai last resort)"
echo
log "=== TINDAKAN PENGESAHAN ==="
log "• Buka https://nfcgo.clbgroups.com/ dan check tiada 404 pada _nuxt/*.js"
log "• Login dashboard → pastikan menu berfungsi"
log "• Buka profile card → pastikan gambar dan data load betul"
log "• Jika SPA blank/500: rollback guna 👉 cp -a ${ROLLBACK_DIR}/. ${SPA_DIR}/"
echo
log "Log penuh disimpan: ${LOG_FILE}"
log "DONE."
