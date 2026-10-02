#Requires -Version 5.1
<#
.SYNOPSIS
  NFCGo - Satu skrip validasi dan start Google OAuth untuk local Windows.
.DESCRIPTION
  1. Auto cari PHP.
  2. Verify .env variables untuk Google OAuth.
  3. Run pending migrations.
  4. Clear config/cache/route.
  5. Test Socialite generate redirect URL ke Google.
  6. Papar summary + langkah manual terakhir.
  7. Dengan -StartServers: start artisan serve + npm run dev.
.NOTES
  Run dari ROOT projek:
    powershell -ExecutionPolicy Bypass -File scripts\setup-oauth-local.ps1
    powershell -ExecutionPolicy Bypass -File scripts\setup-oauth-local.ps1 -StartServers
#>
[CmdletBinding()]
param(
    [switch]$StartServers = $false
)
$ErrorActionPreference = 'Stop'
$PROJECT_ROOT = Split-Path -Parent $PSScriptRoot
$BACKEND_DIR  = Join-Path $PROJECT_ROOT 'backend'
$FRONTEND_DIR = Join-Path $PROJECT_ROOT 'frontend'
$TMP_DIR      = Join-Path $BACKEND_DIR 'bootstrap\cache'
$TEST_CFG_PHP = Join-Path $TMP_DIR '_oauth_test_cfg.php'
$TEST_URL_PHP = Join-Path $TMP_DIR '_oauth_test_url.php'
$script:PASS = New-Object System.Collections.Generic.List[string]
$script:FAIL = New-Object System.Collections.Generic.List[string]
$script:WARN = New-Object System.Collections.Generic.List[string]

function Write-Title($t)  { Write-Host ("`n========== " + $t + " ==========") -ForegroundColor Cyan }
function Write-Step($n,$t){ Write-Host ("`n[" + $n + "] " + $t + "...") -ForegroundColor Yellow }
function Write-OK($m)     { Write-Host ("   OK  " + $m) -ForegroundColor Green;  $script:PASS.Add($m) | Out-Null }
function Write-Bad($m)    { Write-Host ("   FAIL " + $m) -ForegroundColor Red;    $script:FAIL.Add($m) | Out-Null }
function Write-Warn($m)   { Write-Host ("   WARN " + $m) -ForegroundColor Yellow; $script:WARN.Add($m) | Out-Null }

function Read-EnvVal($k, [string]$file) {
    foreach ($line in [System.IO.File]::ReadAllLines($file)) {
        if ($line -match ('^\s*' + [regex]::Escape($k) + '\s*=\s*(.*?)\s*$')) {
            $v = $Matches[1].Trim('"').Trim("'")
            if ($v) { return $v }
        }
    }
    return $null
}

$TEST_APL_PHP = Join-Path $TMP_DIR '_oauth_test_apple.php'
function Ensure-PhpTmpFiles() {
    if (-not (Test-Path $TMP_DIR)) { New-Item -ItemType Directory -Path $TMP_DIR -Force | Out-Null }
    $cfgCode = '<?php' + "`n" + @'
require __DIR__ . "/../../vendor/autoload.php";
$app = require_once __DIR__ . "/../../bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();
$g = config("services.google"); $a = config("services.apple"); $fe = config("services.frontend_url");
echo json_encode([
  "google" => [
    "loaded" => !empty($g["client_id"]),
    "client_id_prefix" => substr($g["client_id"] ?? "", 0, 12),
    "redirect" => $g["redirect"] ?? "MISSING",
  ],
  "apple" => [
    "loaded"        => !empty($a["client_id"]) && !empty($a["team_id"]) && !empty($a["key_id"]),
    "client_id"     => $a["client_id"] ?? "MISSING",
    "team_id"       => $a["team_id"] ?? "MISSING",
    "key_id"        => $a["key_id"] ?? "MISSING",
    "private_key"   => $a["private_key"] ?? "MISSING",
    "redirect"      => $a["redirect"] ?? "MISSING",
    "firebase_jwt"  => class_exists("Firebase\JWT\JWT"),
    "p8_exists"     => !empty($a["private_key"]) ? file_exists(storage_path($a["private_key"])) : null,
  ],
  "frontend_url" => $fe ?? "MISSING",
  "routes" => [
    "g_redirect" => route("oauth.redirect", ["provider"=>"google"], false),
    "g_callback" => route("oauth.callback", ["provider"=>"google"], false),
    "a_redirect" => route("oauth.redirect", ["provider"=>"apple"],  false),
    "a_callback" => route("oauth.callback", ["provider"=>"apple"],  false),
  ],
], JSON_UNESCAPED_SLASHES).PHP_EOL;
'@ + "`n"
    [System.IO.File]::WriteAllText($TEST_CFG_PHP, $cfgCode, [System.Text.UTF8Encoding]::new($true))

    $urlCode = '<?php' + "`n" + @'
require __DIR__ . "/../../vendor/autoload.php";
$app = require_once __DIR__ . "/../../bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();
try {
    $url = Laravel\Socialite\Facades\Socialite::driver("google")
        ->with(["prompt" => "select_account"])
        ->stateless()
        ->redirect()
        ->getTargetUrl();
    $ok = (strpos($url, "accounts.google.com") !== false) && (strpos($url, "client_id=") !== false);
    echo ($ok ? "OK|" : "FAIL|bad_url|").$url.PHP_EOL;
} catch (Throwable $e) {
    echo "FAIL|exception|".$e->getMessage().PHP_EOL;
}
'@ + "`n"
    [System.IO.File]::WriteAllText($TEST_URL_PHP, $urlCode, [System.Text.UTF8Encoding]::new($true))

    $aplCode = '<?php' + "`n" + @'
require __DIR__ . "/../../vendor/autoload.php";
$app = require_once __DIR__ . "/../../bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();
try {
    $svc = new App\Services\AppleClientSecretService();
    $jwt = $svc->generate();
    $parts = explode(".", $jwt);
    $ok = (count($parts) === 3) && (strlen($jwt) > 200);
    echo ($ok ? "OK|jwt_len=".strlen($jwt)."|jwt=".$jwt : "FAIL|bad_jwt|".$jwt).PHP_EOL;
} catch (Throwable $e) {
    echo "FAIL|exception|".$e->getMessage().PHP_EOL;
}
'@ + "`n"
    [System.IO.File]::WriteAllText($TEST_APL_PHP, $aplCode, [System.Text.UTF8Encoding]::new($true))
}

function Remove-PhpTmpFiles() {
    Remove-Item -Path $TEST_CFG_PHP -ErrorAction SilentlyContinue
    Remove-Item -Path $TEST_URL_PHP -ErrorAction SilentlyContinue
    Remove-Item -Path $TEST_APL_PHP -ErrorAction SilentlyContinue
}

# ===========================================================
Write-Title "NFCGo - Google OAuth Local Setup (ALL-IN-1)"
# ===========================================================

# STEP 1 - Cari PHP
Write-Step 1 "Mencari PHP executable"
$PHP = $null
$cands = @("C:\xampp\php\php.exe","C:\php\php.exe")
foreach ($c in $cands) { if (Test-Path $c) { $PHP = $c; break } }
if (-not $PHP -and (Test-Path "C:\laragon\bin\php")) {
    $f = Get-ChildItem "C:\laragon\bin\php" -Filter php.exe -Recurse -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($f) { $PHP = $f.FullName }
}
if (-not $PHP) {
    $p = Get-Command php -ErrorAction SilentlyContinue
    if ($p) { $PHP = $p.Source }
}
if ($PHP) {
    $ver = (& $PHP -v)[0]
    Write-OK ("PHP ditemui: " + $PHP + "  (" + $ver + ")")
} else {
    Write-Bad "PHP tidak dijumpai. Install XAMPP / Laragon / tambah PHP ke PATH."
    exit 1
}

# STEP 2 - Verify env backend (Google + Apple)
Write-Step 2 "Validate .env backend (Google + Apple OAuth)"
$ENV_FILE = Join-Path $BACKEND_DIR '.env'
if (-not (Test-Path $ENV_FILE)) {
    Write-Bad (".env tiada di " + $ENV_FILE + ". Copy dari .env.example dulu.")
    exit 1
}
$APP_URL       = Read-EnvVal 'APP_URL'              $ENV_FILE
$FRONTEND_URL  = Read-EnvVal 'FRONTEND_URL'         $ENV_FILE
$GOOGLE_ID     = Read-EnvVal 'GOOGLE_CLIENT_ID'     $ENV_FILE
$GOOGLE_SECRET = Read-EnvVal 'GOOGLE_CLIENT_SECRET' $ENV_FILE
$CURL_VERIFY   = Read-EnvVal 'CURL_VERIFY_SSL'      $ENV_FILE
$DB_DATABASE   = Read-EnvVal 'DB_DATABASE'          $ENV_FILE
$APPLE_CID     = Read-EnvVal 'APPLE_CLIENT_ID'      $ENV_FILE
$APPLE_TID     = Read-EnvVal 'APPLE_TEAM_ID'        $ENV_FILE
$APPLE_KID     = Read-EnvVal 'APPLE_KEY_ID'         $ENV_FILE
$APPLE_PK      = Read-EnvVal 'APPLE_PRIVATE_KEY'    $ENV_FILE
if ($APP_URL)       { Write-OK ("APP_URL        = " + $APP_URL) }       else { Write-Bad "APP_URL tiada dalam .env" }
if ($FRONTEND_URL)  { Write-OK ("FRONTEND_URL   = " + $FRONTEND_URL) }  else { Write-Bad "FRONTEND_URL tiada dalam .env" }
# Google
if ($GOOGLE_ID) {
    $len = [Math]::Min(14, $GOOGLE_ID.Length)
    Write-OK ("GOOGLE_CLIENT_ID = " + $GOOGLE_ID.Substring(0,$len) + "...")
} else { Write-Bad "GOOGLE_CLIENT_ID tiada dalam .env" }
if ($GOOGLE_SECRET) {
    Write-OK ("GOOGLE_CLIENT_SECRET ada (length=" + $GOOGLE_SECRET.Length + ")")
} else { Write-Bad "GOOGLE_CLIENT_SECRET tiada dalam .env" }
if ($CURL_VERIFY -eq 'false') { Write-Warn "CURL_VERIFY_SSL=false (Windows local sahaja - JANGAN production)" }
if ($DB_DATABASE -and -not (Test-Path $DB_DATABASE)) {
    Write-Warn ("DB_DATABASE='" + $DB_DATABASE + "' tidak wujud - migrate akan auto-create SQLite.")
}
# Apple
Write-Host "   --- Apple OAuth ---" -ForegroundColor DarkCyan
if ($APPLE_CID -and $APPLE_CID -notmatch 'yourcompany|APPLE_CLIENT_ID|PLACEHOLDER|^$') {
    Write-OK ("APPLE_CLIENT_ID = " + $APPLE_CID)
} else {
    if (-not $APPLE_CID) { Write-Warn "APPLE_CLIENT_ID tiada / masih placeholder." } else { Write-OK ("APPLE_CLIENT_ID = " + $APPLE_CID) }
}
if ($APPLE_TID -and $APPLE_TID -notmatch 'YOUR10DIGIT|APPLE_TEAM_ID|^$') {
    Write-OK ("APPLE_TEAM_ID   = " + $APPLE_TID)
} else { Write-Warn "APPLE_TEAM_ID tiada / masih placeholder." }
if ($APPLE_KID -and $APPLE_KID -notmatch 'YOUR10CHAR|APPLE_KEY_ID|^$') {
    Write-OK ("APPLE_KEY_ID    = " + $APPLE_KID)
} else { Write-Warn "APPLE_KEY_ID tiada / masih placeholder." }
if ($APPLE_PK -and $APPLE_PK -notmatch 'XXXXX|APPLE_PRIVATE_KEY|^$') {
    $p8FullPath = Join-Path (Join-Path $BACKEND_DIR 'storage') $APPLE_PK
    if (Test-Path $p8FullPath) { Write-OK ("APPLE_PRIVATE_KEY .p8 file ADA: storage/" + $APPLE_PK) }
    else { Write-Warn ("APPLE_PRIVATE_KEY ditunjuk tapi fail TIDAK wujud: " + $p8FullPath) }
} else { Write-Warn "APPLE_PRIVATE_KEY tiada / masih placeholder." }

# STEP 3 - Migrations
Write-Step 3 "Run pending migrations (social_identities, sessions, cache)"
Push-Location $BACKEND_DIR
try {
    $migStatus = ((& $PHP artisan migrate:status --no-ansi 2>&1) | Out-String)
    $pendingCount = ([regex]::Matches($migStatus, 'Pending')).Count
    Write-OK ("Migration status scan - Pending=" + $pendingCount)
    if ($pendingCount -gt 0) {
        Write-Host "   Running migration(s)" -ForegroundColor DarkGray
        & $PHP artisan migrate --force --no-ansi
        if ($LASTEXITCODE -eq 0) { Write-OK "Semua migration berjaya di-run." } else { Write-Bad "Migration GAGAL." }
    } else {
        Write-OK "Tiada migration pending. Table OAuth = siap."
    }
} catch {
    Write-Bad ("Migration error: " + $_.Exception.Message)
}
Pop-Location

# STEP 4 - Cache clear + verify config services.google + services.apple
Write-Step 4 "Clear caches + Verify services.google + services.apple config"
Ensure-PhpTmpFiles
Push-Location $BACKEND_DIR
& $PHP artisan config:clear --no-ansi | Out-Null
& $PHP artisan cache:clear  --no-ansi | Out-Null
& $PHP artisan route:clear  --no-ansi | Out-Null
Write-OK "config/cache/route cleared."

$cfgRaw = @(& $PHP $TEST_CFG_PHP 2>&1)
$cfgLine = $cfgRaw | Where-Object { $_ -match '^\{' } | Select-Object -First 1
if ($cfgLine) {
    try {
        $o = ($cfgLine | ConvertFrom-Json)
        # ---- Google ----
        if ($o.google.loaded) {
            Write-OK ("GOOGLE: config LOADED - redirect URI = " + $o.google.redirect)
            if ($o.google.redirect -match 'google/callback') {
                Write-OK "GOOGLE: Redirect URI format BETUL."
            } else {
                Write-Warn "GOOGLE: Redirect URI tak nampak betul - check APP_URL."
            }
        } else {
            Write-Bad "GOOGLE: services.google TIDAK loaded - Client ID/Secret kosong?"
        }
        # ---- Apple ----
        $a = $o.apple
        if ($a.firebase_jwt) { Write-OK "APPLE: Firebase\JWT\JWT library = INSTALLED." }
        else { Write-Bad "APPLE: Firebase JWT library TIDAK dijumpai - run composer install." }
        if ($a.loaded) {
            Write-OK ("APPLE: client_id=" + $a.client_id + ", team_id=" + $a.team_id + ", key_id=" + $a.key_id)
        } else {
            Write-Warn "APPLE: vars BELUM set sepenuhnya (client_id / team_id / key_id)."
        }
        if ($a.private_key -and $a.private_key -ne 'MISSING') {
            if ($a.p8_exists) { Write-OK ("APPLE: .p8 private key wujud: storage/" + $a.private_key) }
            else { Write-Warn ("APPLE: .p8 TIDAK wujud di storage/" + $a.private_key + " - upload AuthKey_xxx.p8 terlebih dahulu.") }
        }
        if ($o.routes.a_callback -match 'apple/callback') {
            Write-OK ("APPLE: callback route registered: " + $o.routes.a_callback)
        }
    } catch {
        Write-Warn ("Parse JSON config gagal: " + $_)
        Write-Host ("    Raw: " + $cfgLine) -ForegroundColor DarkGray
    }
} else {
    $joined = ($cfgRaw -join " | ")
    Write-Warn ("Config test PHP tak output JSON. Output: " + $joined)
}
Pop-Location

# STEP 5 - TEST Socialite generate Google redirect URL
Write-Step 5 "TEST Google: Socialite generate redirect URL + Apple: JWT client secret"
Push-Location $BACKEND_DIR
# 5a - Google
$res = @(& $PHP $TEST_URL_PHP 2>&1)
$rline = $res | Where-Object { $_ -match '^(OK|FAIL)\|' } | Select-Object -First 1
if ($rline) {
    $parts = $rline -split '\|', 3
    if ($parts[0] -eq 'OK') {
        $url = $parts[2]
        Write-OK "GOOGLE: Socialite redirect = BERJAYA. Backend Google OAuth = FUNCTIONAL."
        $plen = [Math]::Min(140, $url.Length)
        Write-Host ("      URL: " + $url.Substring(0,$plen) + "...") -ForegroundColor DarkGray
    } else {
        Write-Bad ("GOOGLE: TEST GAGAL: " + $parts[1] + " - " + $parts[2])
    }
} else {
    Write-Warn "GOOGLE: test result tak capture. Run manual: cd backend; php artisan tinker;"
}
# 5b - Apple: generate ES256 client secret (dynamic JWT)
$ares = @(& $PHP $TEST_APL_PHP 2>&1)
Remove-PhpTmpFiles
$aline = $ares | Where-Object { $_ -match '^(OK|FAIL)\|' } | Select-Object -First 1
if ($aline) {
    $ap = $aline -split '\|', 3
    if ($ap[0] -eq 'OK') {
        Write-OK ("APPLE: Client secret ES256 JWT generate = BERJAYA. (" + $ap[1] + ")")
    } else {
        $hint = $ap[2]
        if ($hint -match 'failed to open stream|No such file') {
            Write-Warn ("APPLE: .p8 file not found / cannot read. Upload AuthKey_*.p8 ke storage/" + ($APPLE_PK ?? 'keys/AuthKey_XXXXXX.p8'))
        } else {
            Write-Warn ("APPLE: Test JWT gagal: " + $ap[1] + " - " + $hint)
        }
    }
} else {
    $aJoined = ($ares -join " | ")
    if ($aJoined -match 'No such file|failed to open stream') {
        Write-Warn "APPLE: .p8 private key file not found. Upload AuthKey_XXX.p8 ke storage/app/keys/ dan set APPLE_PRIVATE_KEY=keys/AuthKey_XXX.p8"
    } else {
        Write-Warn ("APPLE: test output tak capture. Check Apple env vars + .p8 file.")
    }
}
Pop-Location

# STEP 6 - Verify OAuth routes
Write-Step 6 "Verify OAuth routes (redirect / callback / check-email)"
Push-Location $BACKEND_DIR
$routes = ((& $PHP artisan route:list --path=auth --no-ansi 2>&1) | Out-String)
$needRoutes = @('auth/check-email','{provider}/redirect','{provider}/callback')
foreach ($r in $needRoutes) {
    if ($routes -match [regex]::Escape($r)) {
        Write-OK ("Route: " + $r)
    } else {
        Write-Bad ("Route MISSING: " + $r)
    }
}
Pop-Location

# STEP 7 - Optional start servers
if ($StartServers) {
    Write-Step 7 "Start backend + frontend servers"
    Push-Location $BACKEND_DIR
    Start-Process -FilePath $PHP -ArgumentList @('artisan','serve','--host=127.0.0.1','--port=8000') -WorkingDirectory $BACKEND_DIR -WindowStyle Normal
    Write-OK "Backend artisan serve - STARTED (http://127.0.0.1:8000)"
    Pop-Location
    $npmCmd = Get-Command npm.cmd -ErrorAction SilentlyContinue
    if ($npmCmd) {
        Start-Process -FilePath $npmCmd.Source -ArgumentList @('run','dev') -WorkingDirectory $FRONTEND_DIR -WindowStyle Normal
        Write-OK "Frontend npm run dev - STARTED (selalunya http://localhost:3000)"
    } else {
        Write-Warn "npm.cmd tidak jumpa. Frontend start manual: cd frontend; npm run dev"
    }
} else {
    Write-Warn "Skip auto-start servers. Guna: -StartServers untuk run kedua-dua."
}

# ===========================================================
Write-Title "SUMMARY"
# ===========================================================
Write-Host (" PASS = " + $script:PASS.Count) -ForegroundColor Green
Write-Host (" FAIL = " + $script:FAIL.Count) -ForegroundColor $(if ($script:FAIL.Count -gt 0) { 'Red' } else { 'Green' })
Write-Host (" WARN = " + $script:WARN.Count) -ForegroundColor Yellow

Write-Host ""
Write-Host "=================================================================" -ForegroundColor Cyan
Write-Host " LANGKAH MANUAL 1/2: Google Cloud Console"                         -ForegroundColor Cyan
Write-Host "=================================================================" -ForegroundColor Cyan
Write-Host " 1. Buka https://console.cloud.google.com/apis/credentials"
$prefix = "{COPY DARI .env}"
if ($GOOGLE_ID) {
    $l2 = [Math]::Min(20, $GOOGLE_ID.Length)
    $prefix = $GOOGLE_ID.Substring(0,$l2) + "..."
}
Write-Host (" 2. Pilih OAuth Client ID dgn prefix: " + $prefix)
Write-Host " 3. Authorized redirect URIs - TAMBAH 2 (EXACT):"
Write-Host "      http://localhost:8000/api/auth/google/callback"    -ForegroundColor Green
Write-Host "      https://nfcgo.clbgroups.com/api/auth/google/callback" -ForegroundColor Green
Write-Host " 4. Authorized JavaScript origins - TAMBAH 2:"
Write-Host "      http://localhost:3000"
Write-Host "      https://nfcgo.clbgroups.com"
Write-Host " 5. OAuth consent screen -> PUBLISH APP"
Write-Host " 6. Test: Buka login page -> Click Google button."
Write-Host ""
Write-Host "=================================================================" -ForegroundColor Cyan
Write-Host " LANGKAH MANUAL 2/2: Apple Developer Portal"                      -ForegroundColor Cyan
Write-Host "=================================================================" -ForegroundColor Cyan
Write-Host " 0. Log masuk: https://developer.apple.com/account (PERLU Apple Developer Membership ~$99/tahun)"
Write-Host " 1. Buat Services ID (Identifiers -> Services IDs -> Register Services ID)"
Write-Host "    - Description: NFCGo Sign In"
Write-Host "    - Identifier (Bundle Services ID): com.clbgroups.nfcgo  <-- ini jadi APPLE_CLIENT_ID"
Write-Host " 2. Edit Services ID tu -> Enable 'Sign In with Apple' -> Configure:"
Write-Host "    - Primary App ID: (pilih App ID utama korang, atau buat App ID baru)"
Write-Host "    - Domains and Subdomains: nfcgo.clbgroups.com"
Write-Host "    - Return URLs (EXACT, TAMBAH 2):"
Write-Host "        http://localhost:8000/api/auth/apple/callback"        -ForegroundColor Green
Write-Host "        https://nfcgo.clbgroups.com/api/auth/apple/callback" -ForegroundColor Green
Write-Host " 3. Buat Sign In with Apple Key (Keys -> Keys -> Register a New Key):"
Write-Host "    - Key Name: NFCGo Sign In Key"
Write-Host "    - TICK: Sign In with Apple -> Configure (pilih Primary App ID tadi)"
Write-Host "    - Click Register -> Download AuthKey_XXXXXX.p8 (JANGAN hilang, sekali sahaja)"
Write-Host "    - Catat: KEY ID (10 char, sebelah key name di portal) -> APPLE_KEY_ID"
Write-Host " 4. TEAM ID dapat dari: Apple Developer Portal atas kanan, nama korang -> Membership -> Team ID (10 char)"
Write-Host " 5. Upload AuthKey_XXXXXX.p8 ke backend:"
Write-Host "    - Folder target: backend/storage/app/keys/  (create folder 'keys' jika tiada)"
Write-Host "    - Path dalam env: APPLE_PRIVATE_KEY=keys/AuthKey_XXXXXX.p8  (replace XXXXXX dengan actual KEY_ID)"
Write-Host " 6. Update backend/.env dengan 4 var ni (contoh):"
Write-Host "    APPLE_CLIENT_ID=com.clbgroups.nfcgo"
Write-Host "    APPLE_TEAM_ID=A1B2C3D4E5"
Write-Host "    APPLE_KEY_ID=1A2B3C4D5E"
Write-Host "    APPLE_PRIVATE_KEY=keys/AuthKey_1A2B3C4D5E.p8"
Write-Host " 7. Run semula script ini untuk verify: powershell -ExecutionPolicy Bypass -File scripts\setup-oauth-local.ps1"
Write-Host " 8. Test Apple: Buka login page dgn Safari / iPhone browser -> Click Apple button."
Write-Host " 9. (Production) Apple perlukan HTTPS untuk callback. Local test biasanya dibenarkan http://localhost"
Write-Host ""

if ($script:FAIL.Count -gt 0) { exit 1 }
Write-Host "Siap! Sisi projek = 100% OK. Google Cloud + Apple Developer Portal (2 manual step) je lagi." -ForegroundColor Green
exit 0
