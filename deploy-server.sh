#!/bin/bash
# ============================================================
#  NFCGo — Deploy Step 2/2: SERVER SIDE (SSH Terminal)
#  Muat naik terus ke server /tmp, atau auto-upload oleh batch
# ============================================================
set -euo pipefail

PROJECT="/www/wwwroot/nfcgo.clbgroups.com"
TS="$(date +%Y%m%d-%H%M)"

echo ""
echo "=============================================="
echo "  NFCGo Server Deploy v2"
echo "  Project : $PROJECT"
echo "  Stamps  : $TS"
echo "=============================================="
echo ""

# --- 1) Backup DB --------------------------------------
echo "[1/6] Backup DB SQLite..."
cd "$PROJECT/backend"
cp database/database.sqlite database/database.sqlite.bak-$TS
echo "  -> database/database.sqlite.bak-$TS"

# --- 2) Backup spa --------------------------------------
echo "[2/6] Backup spa folder..."
cd "$PROJECT"
cp -R spa spa.bak-$TS 2>/dev/null || echo "  (skip, spa tiada)"
echo "  -> spa.bak-$TS"

# --- 3) Clear caches ------------------------------------
echo "[3/6] Clear Laravel + Nginx gzip/br cache..."
cd "$PROJECT/backend"
php artisan config:clear 2>/dev/null || true
php artisan route:clear  2>/dev/null || true
php artisan view:clear   2>/dev/null || true
php artisan cache:clear  2>/dev/null || true
rm -f "$PROJECT/spa/index.html.gz" "$PROJECT/spa/index.html.br"
echo "  OK."

# --- 4) Migrate -----------------------------------------
echo "[4/6] Run php artisan migrate --force..."
php artisan migrate --force
echo "  OK."

# --- 5) Permissions -------------------------------------
echo "[5/6] Chown & chmod..."
cd "$PROJECT"
chown -R www:www spa backend
chmod -R 755 spa
chmod -R 775 backend/storage backend/bootstrap/cache
# Ensure SQLite writable
chown www:www backend/database/database.sqlite backend/database/*.sqlite* 2>/dev/null || true
chmod 664 backend/database/database.sqlite backend/database/*.sqlite* 2>/dev/null || true
echo "  OK."

# --- 6) Final clean cache -------------------------------
echo "[6/6] Clean gzip/br cache lagi sekali (redundant safe)..."
rm -f spa/index.html.gz spa/index.html.br
echo "  OK."

echo ""
echo "=============================================="
echo "  ✅ Server side deploy — SIAP"
echo ""
echo "  Next step (manual sekali sahaja):"
echo "  1) Buka NFCGo Admin di browser"
echo "  2) Premium Profile Builder → Hanish Syaura"
echo "  3) Klik [Save Profile] (populate column baru)"
echo "  4) iPhone: Settings → Safari → Clear History & Data"
echo "  5) Android: Chrome → 3 dot → ⭯ Force refresh"
echo "=============================================="
echo ""
