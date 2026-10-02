@echo off
REM ============================================================
REM  NFCGo — Deploy Step 1/2: BUILD & UPLOAD (Local Windows)
REM  Usage: Run this from local machine with SSH + Node installed
REM ============================================================
setlocal enabledelayedexpansion
chcp 65001 >nul

REM ============ CONFIG (UBAH INI DULU!) ============
set SERVER_IP=124.156.xxx.xxx
set SERVER_USER=root
set PROJECT_LOCAL=c:\Users\USER\Desktop\nfc-Business-card
set PROJECT_SERVER=/www/wwwroot/nfcgo.clbgroups.com
REM ==================================================

cd /d "%PROJECT_LOCAL%"

echo.
echo ==============================================
echo   [1/6] Upload Backend Update Files ...
echo ==============================================
scp -o StrictHostKeyChecking=no "backend\database\migrations\2025_12_01_000000_add_design_detail_columns_to_landing_pages.php" %SERVER_USER%@%SERVER_IP%:%PROJECT_SERVER%/backend/database/migrations/
if errorlevel 1 ( echo FAIL upload migration & goto :err )
scp -o StrictHostKeyChecking=no "backend\app\Models\LandingPage.php" %SERVER_USER%@%SERVER_IP%:%PROJECT_SERVER%/backend/app/Models/
if errorlevel 1 ( echo FAIL upload LandingPage & goto :err )
scp -o StrictHostKeyChecking=no "backend\app\Http\Controllers\Api\NfcCardController.php" %SERVER_USER%@%SERVER_IP%:%PROJECT_SERVER%/backend/app/Http/Controllers/Api/
if errorlevel 1 ( echo FAIL upload NfcCardController & goto :err )
echo OK.

echo.
echo ==============================================
echo   [2/6] Build Frontend (Nuxt Generate) ...
echo ==============================================
cd frontend
if exist .output rmdir /s /q .output
if exist dist rmdir /s /q dist
call npm install
if errorlevel 1 ( echo FAIL npm install & goto :err )
call npm run generate
if errorlevel 1 ( echo FAIL npm run generate & goto :err )
if not exist ".output\public\index.html" ( echo FAIL .output/public tidak dijana & goto :err )
cd ..
echo OK.

echo.
echo ==============================================
echo   [3/6] Clean old compressed cache server ...
echo ==============================================
ssh -o StrictHostKeyChecking=no %SERVER_USER%@%SERVER_IP% "rm -f %PROJECT_SERVER%/spa/index.html.gz %PROJECT_SERVER%/spa/index.html.br"
if errorlevel 1 ( echo FAIL clear cache gz/br & goto :err )
echo OK.

echo.
echo ==============================================
echo   [4/6] Sandaran spa folder server ...
echo ==============================================
ssh -o StrictHostKeyChecking=no %SERVER_USER%@%SERVER_IP% "cp -R %PROJECT_SERVER%/spa %PROJECT_SERVER%/spa.bak-$(date +%%Y%%m%%d-%%H%%M)"
echo OK.

echo.
echo ==============================================
echo   [5/6] Upload build output (.output/public/*) ke spa/ ...
echo ==============================================
scp -o StrictHostKeyChecking=no -r frontend\.output\public\* %SERVER_USER%@%SERVER_IP%:%PROJECT_SERVER%/spa/
if errorlevel 1 ( echo FAIL upload build & goto :err )
echo OK.

echo.
echo ==============================================
echo   [6/6] Set permission & Run backend migrate (Step2 script) ...
echo ==============================================
echo.
echo ===== LANJUTKAN Step2 di Server =====
echo Jalankan command ini di server SSH selepas ini:
echo.
echo   bash /tmp/nfcgo-deploy-step2.sh
echo.
echo Kita upload step2 script dulu...
(
  echo #!/bin/bash
  echo set -euo pipefail
  echo PROJECT=%PROJECT_SERVER%
  echo cd $PROJECT/backend
  echo TS=$(date +%%Y%%m%%d-%%H%%M)
  echo echo "[1/5] Backup DB..."
  echo cp database/database.sqlite database/database.sqlite.bak-$TS
  echo echo "[2/5] Clear all caches..."
  echo php artisan config:clear ^|^| true
  echo php artisan route:clear ^|^| true
  echo php artisan view:clear ^|^| true
  echo php artisan cache:clear ^|^| true
  echo echo "[3/5] Run migration..."
  echo php artisan migrate --force
  echo echo "[4/5] Chown permission..."
  echo cd $PROJECT
  echo chown -R www:www spa backend
  echo chmod -R 755 spa
  echo chmod -R 775 backend/storage backend/bootstrap/cache
  echo echo "[5/5] Re-clean gzip/br cache..."
  echo rm -f spa/index.html.gz spa/index.html.br
  echo echo ""
  echo echo "=================================================="
  echo echo "  DONE! Sekarang Re-Save Profile Hanish sekali"
  echo echo "  kat builder, kemudian clear cache phone."
  echo echo "=================================================="
) > %TEMP%\nfcgo-deploy-step2.sh
scp -o StrictHostKeyChecking=no %TEMP%\nfcgo-deploy-step2.sh %SERVER_USER%@%SERVER_IP%:/tmp/nfcgo-deploy-step2.sh
ssh -o StrictHostKeyChecking=no %SERVER_USER%@%SERVER_IP% "chmod +x /tmp/nfcgo-deploy-step2.sh"

echo.
echo ==============================================
echo   Deploy Step 1 — SIAP.
echo   Sekarang login SSH dan jalankan:
echo.
echo   ssh %SERVER_USER%@%SERVER_IP%
echo   bash /tmp/nfcgo-deploy-step2.sh
echo ==============================================
echo.
pause
exit /b 0

:err
echo.
echo !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
echo   FAIL di step atas. Fix dulu kemudian retry.
echo !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
pause
exit /b 1
