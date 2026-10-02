#!/usr/bin/env bash
# =======================================================================
#  NFCGo — Satu skrip deploy Google OAuth ke Production (Ubuntu 22.04 LTS)
#  Huawei Cloud + aaPanel
#
#  CARA GUNA:
#    1. Upload ke server:  /www/wwwroot/nfcgo.clbgroups.com/scripts/
#    2. chmod +x deploy-oauth-production.sh
#    3. cd /www/wwwroot/nfcgo.clbgroups.com  ;  bash scripts/deploy-oauth-production.sh
#
#  FEATURES:
#    * Backup .env + database SEBELUM modify (auto-rollback env jika migrate gagal)
#    * Verify GOOGLE_CLIENT_ID + GOOGLE_CLIENT_SECRET wujud
#    * artisan config/cache/route/view clear
#    * migrate --force
#    * Smoke test: curl API health + auth/google/redirect HTTP 302
#    * Papar summary checklist
# =======================================================================

set -uo pipefail

# ========= CONFIG (Edit jika server lain =============
APP_ROOT="/www/wwwroot/nfcgo.clbgroups.com"
# ===========================================================

BACKEND_DIR="${APP_ROOT}/backend"
STAMP="$(date +%Y%m%d-%H%M%S)"
LOG_FILE="${APP_ROOT}/deploy-oauth-${STAMP}.log"
BACKUP_DIR="${APP_ROOT}/backups"
ENV_BACKUP=""
DB_BACKUP=""

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'; NC='\033[0m'
pass=0; fail=0; warn=0

log()  { printf "${CYAN}[INFO]${NC}  %s\n" "$*"; }
ok()   { printf "${GREEN}[ OK ]${NC}  %s\n" "$*"; pass=$((pass+1)); }
warn() { printf "${YELLOW}[WARN]${NC}  %s\n" "$*"; warn=$((warn+1)); }
bad()  { printf "${RED}[FAIL]${NC}  %s\n" "$*"; fail=$((fail+1)); }
step() { printf "\n${YELLOW}▶ %s${NC}\n" "$*"; }
title(){ printf "\n${CYAN}========== %s ==========${NC}\n" "$*"; }

rollback() {
    echo
    bad "*** ROLLBACK DIPICU: $1 ***"
    if [ -n "$ENV_BACKUP" ] && [ -f "$ENV_BACKUP" ]; then
        warn "Restore .env dari: $ENV_BACKUP"
        cp -f "$ENV_BACKUP" "${BACKEND_DIR}/.env"
    fi
    exit 1
}

[ -d "$APP_ROOT" ] || { echo "ERROR: APP_ROOT=$APP_ROOT tidak wujud. Edit fail ini, tetapkan APP_ROOT."; exit 1; }
cd "$APP_ROOT"
mkdir -p "$BACKUP_DIR"
exec > >(tee -a "$LOG_FILE") 2>&1

title 'NFCGo — Google OAuth Production Deployment'
log "Tarikh : $(date)"
log "User   : $(whoami)"
log "PWD    : $(pwd)"
log "Log    : $LOG_FILE"

# ── 1. Backup ──────────────────────────────────────────
step '1/7 Backup aset penting (env + DB)'
cd "$BACKEND_DIR"

ENV_BACKUP="${BACKUP_DIR}/.env.oauth.${STAMP}.bak"
if cp -f "${BACKEND_DIR}/.env" "$ENV_BACKUP"; then
    ok "Backup .env: $ENV_BACKUP"
else
    bad "Gagal backup .env — berhenti."
    exit 1
fi

DB_PATH="$(grep -E '^DB_DATABASE=' "${BACKEND_DIR}/.env" | cut -d= -f2- | tr -d '"' | tr -d "'")"
if [ -n "$DB_PATH" ] && [ -f "$DB_PATH" ]; then
    DB_BACKUP="${BACKUP_DIR}/database.oauth.${STAMP}.sqlite"
    if cp -f "$DB_PATH" "$DB_BACKUP"; then
        ok "Backup SQLite DB: $DB_BACKUP"
    else
        warn "Gagal backup SQLite DB, teruskan tanpa DB backup."
        DB_BACKUP=""
    fi
else
    warn "DB_DATABASE tidak jumpa file: ${DB_PATH:-not set in env} — skip DB backup."
fi

# ── 2. Verify env ────────────────────────────────────────────
step '2/7 Verify Google + Apple OAuth env variables'
need_key() {
    local k="$1"; local v
    v="$(grep -E "^${k}=" "${BACKEND_DIR}/.env" | cut -d= -f2- | tr -d '"' | tr -d "'")"
    if [ -z "$v" ]; then bad "Tiada $k dalam .env backend"; return 1; fi
    local preview="$(printf '%s' "$v" | head -c 14)"
    ok "${k} = ${preview}..."
    return 0
}
need_key 'APP_URL'              || true
need_key 'FRONTEND_URL'         || true
need_key 'GOOGLE_CLIENT_ID'     || true
need_key 'GOOGLE_CLIENT_SECRET' || true
# Apple checks
need_key 'APPLE_CLIENT_ID'      || true
need_key 'APPLE_TEAM_ID'        || true
need_key 'APPLE_KEY_ID'         || true
APPLE_PK_ENV="$(grep -E '^APPLE_PRIVATE_KEY=' "${BACKEND_DIR}/.env" | cut -d= -f2- | tr -d '"' | tr -d "'")"
if [ -n "$APPLE_PK_ENV" ] && ! echo "$APPLE_PK_ENV" | grep -qiE 'xxxxx|placeholder|not_set|^$'; then
    PK_FULL_PATH="${BACKEND_DIR}/storage/${APPLE_PK_ENV}"
    if [ -f "$PK_FULL_PATH" ]; then
        ok "Apple .p8 file wujud: storage/${APPLE_PK_ENV}"
    else
        warn "Apple .p8 file TIDAK wujud: ${PK_FULL_PATH} - upload AuthKey_xxx.p8 dan set APPLE_PRIVATE_KEY=keys/AuthKey_xxx.p8"
    fi
else
    warn "APPLE_PRIVATE_KEY = masih placeholder / kosong - belum upload .p8 file."
fi

# Firebase JWT library check
if php -r "require '${BACKEND_DIR}/vendor/autoload.php'; exit(class_exists('Firebase\JWT\JWT') ? 0 : 1);"; then
    ok "Firebase\JWT\JWT library INSTALLED (required for Apple ES256 JWT)"
else
    bad "Firebase JWT library TIDAK dijumpai. Jalankan: cd ${BACKEND_DIR} && composer install"
fi

# ── 3. Cache clear ────────────────────────────────────────────────
step '3/7 Laravel: clear config + cache + route + view'
php artisan config:clear >/dev/null && ok 'config:clear' || warn 'config:clear returned non-zero'
php artisan cache:clear  >/dev/null && ok 'cache:clear'  || warn 'cache:clear returned non-zero'
php artisan route:clear  >/dev/null && ok 'route:clear'  || warn 'route:clear returned non-zero'
php artisan view:clear   >/dev/null && ok 'view:clear'   || warn 'view:clear returned non-zero'

# ── 4. Migrate ───────────────────────────────────────────────
step '4/7 Run pending migrations (--force untuk production)'
mig_out="$(php artisan migrate --force --no-ansi 2>&1)" || { echo "$mig_out"; rollback "php artisan migrate GAGAL"; }
mig_count="$(printf '%s' "$mig_out" | grep -c 'Migrated' || true)"
latest_count="$(printf '%s' "$mig_out" | grep -c 'Nothing to migrate' || true)"
if [ "$mig_count" -gt 0 ]; then
    ok "Migrate OK — $mig_count migration dijalankan."
else
    ok "Semua migrations up-to-date (Nothing to migrate: count=$latest_count)."
fi

# ── 5. Verify Google redirect config ───────────────────────
step '5/7 Verify services.google redirect URI'
cfg_json="$(php artisan tinker --execute="echo json_encode(['redirect'=>config('services.google.redirect','MISSING'),'has_id'=>!empty(config('services.google.client_id'))]);" 2>/dev/null || true)"
if printf '%s' "$cfg_json" | grep -q 'google/callback'; then
    redir="$(printf '%s' "$cfg_json" | grep -oE '"redirect":"[^"]+"' | cut -d'"' -f4)"
    ok "services.google.redirect OK = $redir"
else
    warn "Config services.google nampak tak betul. Tinker output: $cfg_json"
fi

# ── 6. Smoke test HTTPS ─────────────────────────────────────
step '6/7 Smoke test HTTPS endpoint (curl)'
APP_FQDN="$(grep -E '^APP_URL=' "${BACKEND_DIR}/.env" | cut -d= -f2- | tr -d '"' | tr -d "'" | sed 's#/$##')"
if [ -z "$APP_FQDN" ]; then
    warn "APP_URL kosong — skip curl smoke test."
else
    log "Smoke test target FQDN: $APP_FQDN"
    http_code="$(curl -sSL -o /dev/null -w '%{http_code}' --max-time 15 "${APP_FQDN}/api" 2>/dev/null || echo '000')"
    if [ "$http_code" = "200" ]; then
        ok "API health = HTTP 200 OK (${APP_FQDN}/api)"
    else
        warn "API health curl: HTTP $http_code — boleh jadi HTTPS redirect. Test manual kat browser."
    fi

    head_out="$(curl -sSL -D - --max-time 20 "${APP_FQDN}/api/auth/google/redirect" -o /dev/null 2>&1 || true)"
    if printf '%s' "$head_out" | grep -qiE 'location:[[:space:]]+https?://accounts\.google\.com'; then
        ok "Google OAuth endpoint OK — HTTP 302 redirect ke accounts.google.com"
    else
        warn "Google redirect smoke test result (20 lines pertama):"
        printf '%s\n' "$head_out" | head -n 20
    fi
fi

# ── 7. Summary + langkah manual terakhir ────────────────────
step '7/7 Summary'
{
echo
echo "=============================================="
echo " PASS  = $pass"
echo " FAIL  = $fail"
echo " WARN  = $warn"
echo "=============================================="
echo
printf "  ╔══════════════════════════════════════════════════════════════════╗\n"
printf "  ║     LANGKAH MANUAL TERAKHIR (Google Cloud Console)              ║\n"
printf "  ╠══════════════════════════════════════════════════════════════════╣\n"
printf "  ║ 1. Credentials → Authorized redirect URI (HTTPS):               ║\n"
printf "  ║    %-64s║\n" "${APP_FQDN:-https://nfcgo.clbgroups.com}/api/auth/google/callback"
printf "  ║                                                                  ║\n"
printf "  ║ 2. Credentials → Authorized JS origins:                         ║\n"
printf "  ║    %-64s║\n" "${APP_FQDN:-https://nfcgo.clbgroups.com}"
printf "  ║                                                                  ║\n"
printf "  ║ 3. OAuth consent screen → PUBLISH APP                           ║\n"
printf "  ║                                                                  ║\n"
printf "  ║ 4. Smoke test login:                                            ║\n"
printf "  ║    %-64s║\n" "${APP_FQDN:-https://nfcgo.clbgroups.com}/UserAccount/login → Click Google"
printf "  ╚══════════════════════════════════════════════════════════════════╝\n"
echo
echo "Log penuh        : $LOG_FILE"
echo "Backup env       : $ENV_BACKUP"
[ -n "$DB_BACKUP" ] && echo "Backup sqlite db : $DB_BACKUP"
}

if [ "$fail" -gt 0 ]; then
    bad "Terdapat $fail kegagalan. Rujuk log atas / $LOG_FILE"
    exit 1
fi
ok "Deploy OAuth production SELESAI — Google console 2 langkah je lagi!"
exit 0
