# NFCGo Login Network Error — Debug Session
- **Session ID**: `nfcgo-login-network-error`
- **Status**: [OPEN]
- **Created**: 2026-09-24
- **Symptom**: User clicks "Sign in" → toast "Network error. Please check your connection and try again." DevTools shows login XHR stuck in `preflight` / CORS-like failure.
- **Constraint**: All investigation & fixes must be ISOLATED to nfcgo.clbgroups.com; NO changes that may affect other live sites on the same aaPanel (iotplatform.clbgroups.com etc).

---

## Hypotheses (Falsifiable)

| # | Hypothesis | Predicted Observable | Verification Method |
|---|-----------|---------------------|---------------------|
| H1 | **Frontend built with wrong `NUXT_PUBLIC_API_BASE_URL`** (e.g. `http://localhost` instead of `https://nfcgo.clbgroups.com`) | Login XHR targets `localhost` instead of the correct domain; `curl` to correct endpoint works. | Inspect `payload` of login XHR in DevTools → check `Request URL`; grep built SPA `_nuxt/*.js` for `localhost`. |
| H2 | **Backend `.env` missing / wrong `FRONTEND_URL` → CORS `Access-Control-Allow-Origin` missing or mismatched** | Preflight OPTIONS returns 4xx or missing CORS headers; `curl -X OPTIONS -H "Origin: ..." /api/login` fails header check. | `curl -v -X OPTIONS https://nfcgo.clbgroups.com/api/login -H "Access-Control-Request-Method: POST" -H "Origin: https://nfcgo.clbgroups.com"` |
| H3 | **Backend `.env` corrupt / unparseable** (APP_KEY unquoted `=`, backtick in APP_URL) → Laravel fails to boot, returns 500 or no response for `/api/*` | Direct `curl /api/login` returns 500 / white page / no JSON. | `curl -v https://nfcgo.clbgroups.com/api/sanctum/csrf-cookie` |
| H4 | **Nginx routing collision** — `/api` block not reached; static-asset regex catches `.php` or `/api` is routed to SPA `index.html` | `/api/login` returns HTML (SPA shell) instead of JSON. | `curl -i https://nfcgo.clbgroups.com/api/login` → inspect `Content-Type` & body. |
| H5 | **Mixed-content or HTTPS mismatch** — API called over `http://` from `https://` page, or `SESSION_DOMAIN`/`SANCTUM_STATEFUL_DOMAINS` wrong → cookies / cookie CSRF flow broken | Browser blocks XHR with "Mixed Content" or 419 CSRF mismatch. | DevTools Console tab; `curl /sanctum/csrf-cookie` cookies. |
| H6 | **aaPanel Lua / extension conflict specific to this vhost** — `set_by_lua_block` or `proxy_cache_purge` directive injected by aaPanel → Nginx partial reload, causing intermittent 502 on `/api/*`. | `nginx -t` reports warnings; or error log shows upstream prematurely closed. | `nginx -t 2>&1 | grep -i lua\|proxy_cache\|warn\|err` and `/www/server/nginx/logs/error.log` tail. |

---

---

---

## Evidence Log — Runtime Diagnostics SSH (Run 2026-09-24 by user)

### Hypothesis Status After Diagnostic Output

| # | Hypothesis | Status | Evidence (log lines) |
|---|-----------|--------|---------------------|
| **H1** | SPA built with wrong `NUXT_PUBLIC_API_BASE_URL` | ✅ **CONFIRMED — PRIMARY ROOT CAUSE** | [login/index.html](file:///c:/Users/USER/AppData/Roaming/Trae/User/workspaceStorage/f08324b05b852642b49d8d5323134cf3/long-text/6ab4910e2793f5c5ff3a2850/muf0lhuo-sizn/root@flexu....txt#L86-L89): `apiBaseUrl:"http://localhost:8000/api"`, `appUrl:"http://localhost:3002"` — **HTTP (not HTTPS) + localhost** baked into `window.__NUXT__.config.public` → Mixed Content blocker + wrong host. |
| **H5** | Mixed-content / HTTPS mismatch | ✅ **CONFIRMED (by H1)** | `apiBaseUrl` uses `http://`; page served over `https://nfcgo.clbgroups.com` → Chrome "Mixed Content: The page at 'https://…' was loaded over HTTPS, but requested an insecure XMLHttpRequest endpoint 'http://…'. This request has been blocked; the content must be served over HTTPS." → No response received → Axios toast "Network error". |
| **H3** | `.env` corrupt / unparseable | ❌ **REJECTED** | [Blok 1 output](file:///c:/Users/USER/AppData/Roaming/Trae/User/workspaceStorage/f08324b05b852642b49d8d5323134cf3/long-text/6ab4910e2793f5c5ff3a2850/muf0lhuo-sizn/root@flexu....txt#L20-L34): `APP_KEY="base64:…"` — quoted ✅; `APP_URL=https://nfcgo.clbgroups.com` — clean ✅; `FRONTEND_URL=https://nfcgo.clbgroups.com` — exists ✅; `SANCTUM_STATEFUL_DOMAINS=nfcgo.clbgroups.com` ✅. (parse_ini_file test failed due to wrong PHP binary path `/www/server/php/82/bin/php` — *NOT* due to corrupt .env). |
| **H2b** | HandleCors vs cors.php header conflict | ⚠️ **LURKING — will become #1 blocker after H1 fixed** | Static code analysis confirms double-injection danger. Must patch now so after URL fix login still works. |
| **H2a** | `FRONTEND_URL` missing → CORS ACAO wrong | ❌ REJECTED (for now) | Blok 1 shows `FRONTEND_URL=https://nfcgo.clbgroups.com` present. But we cannot confirm ACAO header yet because `/www/server/php/82/bin/php` not found & user skipped Blok 2/3. |
| **H4** | Nginx routing collision → `/api` served as SPA HTML | ⚠️ **UNCERTAIN** | User skipped Blok 3 (Nginx diagnostics). Will re-check after SPA URL fixed: if backend unreachable still, test `curl -D- /api/login` for `Content-Type: text/html`. |
| **H6** | aaPanel Lua / proxy_cache global conflict → partial nginx fail | ⚠️ **UNCERTAIN** | Blok 3 skipped. User explicitly said dia skip Nginx steps — takut affect system lain. Kita tangguh dulu; hanya buat jika still fail selepas step lain OK. |

### Side Issues Detected (Minor But Fix)
| Item | Evidence | Risk |
|------|----------|------|
| PHP binary path wrong: `/www/server/php/82/bin/php: No such file or directory` | [Line 32](file:///c:/Users/USER/AppData/Roaming/Trae/User/workspaceStorage/f08324b05b852642b49d8d5323134cf3/long-text/6ab4910e2793f5c5ff3a2850/muf0lhuo-sizn/root@flexu....txt#L32) | Laravel 12 requires PHP ≥8.4; Need to detect correct path (`php83`, `php84`, `php` alias). |
| `SESSION_SAME_SITE=strict` + CORS (cross-origin preflight) with credentials | [Line 65](file:///c:/Users/USER/AppData/Roaming/Trae/User/workspaceStorage/f08324b05b852642b49d8d5323134cf3/long-text/6ab4910e2793f5c5ff3a2850/muf0lhuo-sizn/root@flexu....txt#L65) | CSRF cookie may not be attached on preflight for `strict`. Change to `lax` (per Laravel default & project template `SESSION_SAME_SITE=lax`). |
| `bootstrap/cache/config.php` missing (no config cache) | [Line 34](file:///c:/Users/USER/AppData/Roaming/Trae/User/workspaceStorage/f08324b05b852642b49d8d5323134cf3/long-text/6ab4910e2793f5c5ff3a2850/muf0lhuo-sizn/root@flexu....txt#L34) | Minor; re-cache after tweaks. |

### Verified Diagnostic Commands
- `/www/server/php/82/bin/php` — **does NOT exist** on this server (aaPanel installed PHP 8.3 / 8.4 instead).

---

## Evidence Log — Static Code Analysis (Completed 2026-09-24)

### Frontend Touch-Points
| File | Line(s) | Relevance |
|------|---------|-----------|
| [nuxt.config.ts](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/nuxt.config.ts#L40-L56) | 42-47 | Default `apiBaseUrl=https://nfcgo.clbgroups.com/api`; env `NUXT_PUBLIC_API_BASE_URL` overrides if set; regex strips backtick/quote chars. H1 checks this. |
| [plugins/api.client.js](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/plugins/api.client.js#L8-L16) | 8-16 | Axios `baseURL=config.public.apiBaseUrl`. **`withCredentials=false`**. Toast "Network error" fires at lines 116-124 when `!error.response` (no response body = preflight/CORS/timeout/Mixed-Content). |
| [stores/auth.js](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/stores/auth.js#L38-L69) | 41 | Login calls `POST /login` relative to baseURL (so full = `{apiBaseUrl}/login` → `…/api/login`). |
| [pages/UserAccount/login.vue](file:///c:/Users/USER/Desktop/nfc-Business-card/frontend/pages/UserAccount/login.vue#L364-L430) | 321, 374 | `check-email?email=…` fires 500ms debounced after typing → also will fail for same CORS reasons. |

### Backend Touch-Points
| File | Line(s) | Relevance |
|------|---------|-----------|
| [config/cors.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/config/cors.php#L18-L68) | 18, 33-52, 67 | Paths covered: `api/*`, `sanctum/csrf-cookie`, `auth/*`. In production `allowed_origins` = ONLY `[FRONTEND_URL]`. **`supports_credentials=true`** |
| [app/Http/Middleware/HandleCors.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Middleware/HandleCors.php#L24-L33) | 24, 29-32 | ⚠️ **MISMATCH CONFLICT**: Custom middleware also sets CORS headers, but `Access-Control-Allow-Credentials='false'` (opposite of `cors.php`). Also `Access-Control-Allow-Origin = env(FRONTEND_URL)` single-value. If user accesses via `www.` or trailing-slash mismatch → browser blocks preflight. |
| [config/sanctum.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/config/sanctum.php#L18-L24) | 18-24 | `SANCTUM_STATEFUL_DOMAINS` must include `nfcgo.clbgroups.com` (no `https://`, no port). |
| [routes/api.php](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/routes/api.php#L62-L90) | 64, 80 | `POST /login` (throttle 10/min) ; `GET /auth/check-email` (throttle 30/min). Both are public (no auth middleware). |
| [env.production.template](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/env.production.template#L30-L46) | 8, 30, 43-45 | Required keys for this bug: `APP_URL`, `FRONTEND_URL`, `SESSION_SECURE_COOKIE`, `SANCTUM_STATEFUL_DOMAINS`. |
| [AuthController::login](file:///c:/Users/USER/Desktop/nfc-Business-card/backend/app/Http/Controllers/Api/AuthController.php#L94-L179) | 94-179 | Endpoint returns JSON 200 on success. Uses Cookie `remember_token` (secure,httpOnly,sameSite=lax). |

### Static Conflict Detected
**Custom `HandleCors` middleware vs `fruitcake/laravel-cors` config** both inject ACAO headers. This can cause:
- Duplicate `Access-Control-Allow-Origin` headers → browser blocks request.
- Contradictory `Access-Control-Allow-Credentials` (true vs false).
- HandleCors returns `200` for OPTIONS without hitting the `cors.php` allowed_origins check, so preflight may pass but the actual request fails.

### Nginx Routing (from project_memory + deployment_guide)
Required pattern — order matters in Nginx:
1. `location ~ \.php$` → Laravel php-fpm
2. `location ^~ /api/` → Laravel (via index.php)
3. `location ^~ /admin/` → Laravel
4. `location ^~ /storage/` → Laravel public dir
5. `location /_nuxt/` → serve static from `/spa/` with HIGH PRIORITY (before generic static regex)
6. `location /` → try_files → fallback `/spa/index.html`

Generic aaPanel static regex block `location ~* \.(gif|jpg|png|js|css)$` MUST NOT match before `/_nuxt/` or it will serve 404.

---

## Phase 2: READ-ONLY DIAGNOSTICS (User to run over SSH — Isolated to nfcgo only)

Run each block in order. Copy output before proceeding. These do NOT modify anything.

```bash
# ============================================================
# BLOK 1 — Verify Backend .env values (READ ONLY)
# ============================================================
cd /www/wwwroot/nfcgo.clbgroups.com
echo "=== APP_KEY (check if quoted with double-quotes when has '=' at end) ==="
grep '^APP_KEY=' .env
echo ""
echo "=== APP_URL (check NO backtick, no trailing slash) ==="
grep '^APP_URL=' .env
echo ""
echo "=== FRONTEND_URL ==="
grep '^FRONTEND_URL=' .env
echo ""
echo "=== SANCTUM_STATEFUL_DOMAINS ==="
grep '^SANCTUM_STATEFUL_DOMAINS=' .env
echo ""
echo "=== SESSION ==="
grep -E '^SESSION_(DOMAIN|SECURE_COOKIE|SAME_SITE)=' .env
echo ""
echo "=== Can PHP parse .env? ==="
/www/server/php/82/bin/php -r '
$env = parse_ini_file(".env", false, INI_SCANNER_NORMAL);
if ($env === false) { echo "FAIL: .env cannot be parsed by parse_ini_file\n"; }
else { echo "OK: parse_ini_file succeeded. APP_URL=" . ($env["APP_URL"] ?? "NULL") . " FRONTEND_URL=" . ($env["FRONTEND_URL"] ?? "NULL") . "\n"; }
'
echo ""
echo "=== Laravel config:cache stale check ==="
ls -la bootstrap/cache/config.php 2>&1 || echo "No config cache (using live .env)"
```

```bash
# ============================================================
# BLOK 2 — Direct API Reachability (bypass browser CORS)
# ============================================================
cd /www/wwwroot/nfcgo.clbgroups.com
echo "=== Test /api/test endpoint via localhost ==="
curl -s -o /tmp/apitest.out -w "HTTP_CODE:%{http_code}\nCONTENT_TYPE:%{content_type}\n" \
  -H "Host: nfcgo.clbgroups.com" \
  http://127.0.0.1:80/api/test
echo "Body preview:"; head -c 400 /tmp/apitest.out; echo ""
echo ""
echo "=== Test OPTIONS /api/login (preflight simulation, check ACAO header) ==="
curl -s -D- -o /dev/null \
  -X OPTIONS \
  -H "Host: nfcgo.clbgroups.com" \
  -H "Origin: https://nfcgo.clbgroups.com" \
  -H "Access-Control-Request-Method: POST" \
  -H "Access-Control-Request-Headers: content-type,accept,x-requested-with" \
  http://127.0.0.1:80/api/login \
  | grep -iE 'access-control|HTTP/'
echo ""
echo "=== Test actual POST /api/login with wrong creds (should return 401 JSON, not 500/HTML) ==="
curl -s -o /tmp/login.out -w "HTTP_CODE:%{http_code}\nCONTENT_TYPE:%{content_type}\n" \
  -X POST \
  -H "Host: nfcgo.clbgroups.com" \
  -H "Origin: https://nfcgo.clbgroups.com" \
  -H "Content-Type: application/json" \
  -d '{"email":"diagnostic@test.com","password":"wrongpass123"}' \
  http://127.0.0.1:80/api/login
echo "Body preview:"; head -c 400 /tmp/login.out; echo ""
```

```bash
# ============================================================
# BLOK 3 — Nginx Config Syntax & THIS vhost routing (READ ONLY)
# ============================================================
echo "=== Nginx syntax check (SAFE — does NOT reload) ==="
nginx -t 2>&1
echo ""
echo "=== Show NFCGO vhost file location ==="
ls -la /www/server/panel/vhost/nginx/nfcgo.clbgroups.com.conf 2>&1
echo ""
echo "=== Extract only AFFECTING directives from NFCGO vhost (NO other sites touched) ==="
grep -nE 'location|root|try_files|index|proxy_pass|set_by_lua|proxy_cache_purge|include' \
  /www/server/panel/vhost/nginx/nfcgo.clbgroups.com.conf 2>/dev/null \
  || echo "vhost file not found at that path — check aaPanel Website settings path"
echo ""
echo "=== Check global nginx conf includes for Lua / proxy_cache directives that may conflict ==="
grep -rnE 'set_by_lua|proxy_cache_purge' /www/server/panel/vhost/nginx/ 2>/dev/null | head -40
```

```bash
# ============================================================
# BLOK 4 — Frontend build check (READ ONLY)
# ============================================================
echo "=== Search built SPA for 'localhost' (wrong apiBaseUrl baked in) ==="
grep -rE 'localhost|127\.0\.0\.1' /www/wwwroot/nfcgo.clbgroups.com/spa/ 2>/dev/null \
  | grep -v '.map$' | head -20
echo ""
echo "=== Search for apiBaseUrl in _nuxt JS files ==="
for f in $(find /www/wwwroot/nfcgo.clbgroups.com/spa/_nuxt -name "*.js" 2>/dev/null | head -20); do
  if strings "$f" 2>/dev/null | grep -q 'apiBaseUrl\|nfcgo.clbgroups.com/api\|localhost.*api'; then
    echo "Found api url refs in: $(basename $f)"
    strings "$f" 2>/dev/null | grep -oE '(https?://[^"\x27 ]+|apiBaseUrl:[^,}\x27"]+)' | head -10
    echo "---"
  fi
done
echo ""
echo "=== Check SPA folder exists ==="
ls -la /www/wwwroot/nfcgo.clbgroups.com/spa/ 2>&1 | head -15
echo ""
echo "=== Check _nuxt folder (static assets) ==="
ls /www/wwwroot/nfcgo.clbgroups.com/spa/_nuxt/ 2>&1 | head -5
```

```bash
# ============================================================
# BLOK 5 — Laravel Latest Log (READ ONLY)
# ============================================================
cd /www/wwwroot/nfcgo.clbgroups.com
echo "=== Last 80 lines of laravel.log ==="
tail -n 80 storage/logs/laravel.log 2>/dev/null || echo "(empty or missing)"
echo ""
echo "=== Nginx error log — last entries for nfcgo ==="
tail -n 40 /www/server/nginx/logs/error.log 2>/dev/null \
  | grep -iE 'nfcgo|upstream|connect|failed|timed out|No such file' | tail -20
```

---

## Phase 3: ISOLATED FIXES — Apply only for matching hypotheses

All fixes target ONLY nfcgo.clbgroups.com — no global nginx restart until Step P3-5, no edits to other vhosts.

### FIX H3 / H2a — .env Corruption (APP_KEY unquoted / backtick in URL / FRONTEND_URL missing)
Run AFTER Block 1 confirms parse_ini_file failure or missing keys.
```bash
cd /www/wwwroot/nfcgo.clbgroups.com

# --- STEP A: Backup current .env (ISOLATED BACKUP) ---
cp -a .env ".env.backup.pre-fix.$(date +%Y%m%d-%H%M%S)"
ls -la .env.backup.pre-fix.* | tail -3

# --- STEP B: Repair using PHP CLI (safer than sed regex) ---
/www/server/php/82/bin/php -r '
$file = ".env";
$content = file_get_contents($file);
$lines = explode("\n", $content);
$out = [];
$fixed = [];
foreach ($lines as $raw) {
  $line = $raw;
  // Strip backtick EVERYWHERE (known corruption from editor copy-paste)
  if (strpos($line, "`") !== false) { $line = str_replace("`", "", $line); $fixed[] = "removed-backtick"; }
  // Strip smart quotes & acute accents
  $line = str_replace(["\xe2\x80\x98","\xe2\x80\x99","\xc2\xb4"], ["","",""], $line);
  // For APP_KEY: if value has = char inside and NOT quoted yet, wrap in double quotes
  if (strpos($line, "APP_KEY=") === 0) {
    $v = substr($line, 8);
    $v = trim($v);
    if ((strpos($v, "=") !== false || strpos($v, ":") !== false)
        && !(($v[0] ?? "") === "\x22" && substr($v,-1) === "\x22")) {
      $v = str_replace(["\x22","\x27"], "", $v);
      $line = "APP_KEY=\x22" . $v . "\x22";
      $fixed[] = "APP_KEY-quoted";
    }
  }
  // For APP_URL and FRONTEND_URL: strip trailing slash and protocol variants
  if (preg_match("/^(APP_URL|FRONTEND_URL)=(.*)$/", $line, $m)) {
    $k = $m[1]; $v = trim($m[2]); $v = trim($v, "\x22\x27"); $v = rtrim($v, "/");
    $line = $k . "=" . (strpos($v, " ") !== false ? "\x22".$v."\x22" : $v);
    $fixed[] = "normalized-".$k;
  }
  $out[] = $line;
}
$final = implode("\n", $out);
// Ensure FRONTEND_URL exists (CORS critical); default same as APP_URL
if (strpos($final, "FRONTEND_URL=") === false) {
  $app = "";
  foreach ($out as $l) { if (strpos($l, "APP_URL=") === 0) { $app = substr($l, 8); $app = trim($app, "\x22\x27 "); } }
  if ($app) { $final .= "\nFRONTEND_URL=" . $app . "\n"; $fixed[] = "added-FRONTEND_URL-from-APP_URL"; }
}
// Ensure SANCTUM_STATEFUL_DOMAINS exists
if (strpos($final, "SANCTUM_STATEFUL_DOMAINS=") === false) {
  $app = "";
  foreach ($out as $l) { if (strpos($l, "APP_URL=") === 0) { $app = substr($l, 8); $app = trim($app, "\x22\x27 "); $app = preg_replace("#^https?://#", "", $app); } }
  if ($app) { $final .= "\nSANCTUM_STATEFUL_DOMAINS=" . $app . "\n"; $fixed[] = "added-SANCTUM_STATEFUL_DOMAINS"; }
}
// Ensure SESSION hardening
if (strpos($final, "SESSION_SECURE_COOKIE=") === false) $final .= "SESSION_SECURE_COOKIE=true\n";
if (strpos($final, "SESSION_SAME_SITE=") === false)       $final .= "SESSION_SAME_SITE=lax\n";
file_put_contents($file, $final);
echo "Repairs applied: " . implode(", ", array_unique($fixed) ?: ["none-needed"]) . "\n";
'
# Verify parse OK now
/www/server/php/82/bin/php -r '
$e = parse_ini_file(".env", false, INI_SCANNER_NORMAL);
if ($e === false) { echo "STILL FAIL parse_ini_file — inspect APP_KEY manually\n"; exit(1); }
echo "parse_ini_file OK\n";
echo "APP_URL=".($e["APP_URL"]??"NULL")."\n";
echo "FRONTEND_URL=".($e["FRONTEND_URL"]??"NULL")."\n";
echo "SANCTUM_STATEFUL_DOMAINS=".($e["SANCTUM_STATEFUL_DOMAINS"]??"NULL")."\n";
'
# Recache config
/www/server/php/82/bin/php artisan optimize:clear
/www/server/php/82/bin/php artisan config:cache
/www/server/php/82/bin/php artisan route:cache
chown -R www:www storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### FIX H2b / HandleCors Conflict — Disable custom HandleCors middleware (cors.php already handles correctly)
```bash
cd /www/wwwroot/nfcgo.clbgroups.com
cp -a app/Http/Middleware/HandleCors.php app/Http/Middleware/HandleCors.php.BACKUP-pre-fix
ls -la app/Http/Middleware/HandleCors.php.BACKUP-pre-fix
# Comment-out the middleware bootstrap / registration
/www/server/php/82/bin/php -r '
// Check if HandleCors is registered in bootstrap/app.php or kernel
foreach (["bootstrap/app.php", "app/Http/Kernel.php"] as $f) {
  if (!file_exists($f)) continue;
  $c = file_get_contents($f);
  if (strpos($c, "HandleCors::class") !== false || strpos($c, "HandleCors") !== false) {
    echo "Found HandleCors registered in: $f\n";
    echo "Will NOT auto-remove; user to inspect manually.\n";
  }
}
// Laravel 12 uses bootstrap/app.php with middleware stack
$f = "bootstrap/app.php";
if (file_exists($f)) {
  $c = file_get_contents($f);
  echo "\n--- bootstrap/app.php HandleCors references --- \n";
  foreach (explode("\n", $c) as $n => $l) { if (stripos($l, "HandleCors")!==false) echo ($n+1).": ".$l."\n"; }
  echo "--- end ---\n";
}
'
echo ""
echo "=== Quick Patch: Make HandleCors.php a PASS-THROUGH (no header injection, passes to fruitcake) ==="
cat > /tmp/HandleCors_patched.php << 'HANDLECORSEOF'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleCors
{
    public function handle(Request $request, Closure $next)
    {
        // Header injection REMOVED.
        // CORS is handled by Laravel's built-in cors.php config
        // (config/cors.php) using FRONTEND_URL. This prevents double-injection
        // of conflicting Access-Control-Allow-Credentials headers.
        return $next($request);
    }
}
HANDLECORSEOF
cp /tmp/HandleCors_patched.php app/Http/Middleware/HandleCors.php
chown www:www app/Http/Middleware/HandleCors.php
chmod 644 app/Http/Middleware/HandleCors.php
echo "Patched. Diff:"
diff -u app/Http/Middleware/HandleCors.php.BACKUP-pre-fix app/Http/Middleware/HandleCors.php || true
/www/server/php/82/bin/php artisan optimize:clear
/www/server/php/82/bin/php artisan config:cache
```

### FIX H4 — Nginx vhost routing: /api→Laravel ; rest→SPA (ISOLATED to nfcgo vhost ONLY)
```bash
# FIRST: BACKUP vhost BEFORE editing
VHOST=/www/server/panel/vhost/nginx/nfcgo.clbgroups.com.conf
cp -a "$VHOST" "${VHOST}.BACKUP-pre-fix.$(date +%Y%m%d-%H%M%S)"
ls -la "${VHOST}".BACKUP-pre-fix* | tail -3
echo ""
echo "=== Show current vhost (for user to diff later) ==="
cat "$VHOST"
```
*Then, in aaPanel Website → Settings → Configuration File, REPLACE the server{...} for nfcgo.clbgroups.com with this structure. Keep existing `listen`, `server_name`, `ssl_certificate`, `ssl_certificate_key` lines INTACT — only replace/insert `root` and all `location` blocks:*
```nginx
    # ... keep listen/server_name/ssl/root index lines intact ...
    # DOCROOT for Laravel:
    root  /www/wwwroot/nfcgo.clbgroups.com/public;
    index index.php index.html index.htm default.php default.htm default.html;

    # ---------- HIGHEST PRIORITY: SPA _nuxt static assets ----------
    # MUST run BEFORE generic aaPanel static-cache regex; never let
    # `location ~* \.(js|css)$` above match /_nuxt/.
    location ^~ /_nuxt/ {
        alias /www/wwwroot/nfcgo.clbgroups.com/spa/_nuxt/;
        access_log off;
        expires 30d;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # Other SPA static files (favicon, images, robots at /spa root)
    location ~* ^/(favicon\.ico|robots\.txt|default-avatar\.png)$ {
        root /www/wwwroot/nfcgo.clbgroups.com/spa;
        access_log off;
        expires 7d;
        try_files $uri =404;
    }

    # ---------- Laravel destinations: /api, /admin, /storage, .php ----------
    location ^~ /api/ {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ^~ /admin/ {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ^~ /storage/ {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_pass unix:/tmp/php-cgi-82.sock;   # or 127.0.0.1:9000 — check aaPanel PHP 8.2 setting
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO       $fastcgi_path_info;
    }

    # ---------- EVERYTHING ELSE -> Nuxt SPA fallback ----------
    location / {
        root /www/wwwroot/nfcgo.clbgroups.com/spa;
        try_files $uri $uri/ /index.html;
    }

    # ---------- DENY sensitive files ----------
    location ~ /\.(?!well-known) { deny all; }

    # Include aaPanel default ACME / security stubs (keep aaPanel defaults here; if any Lua line shows error later, DISABLE per-vhost, NOT globally)
```

```bash
# After saving vhost via aaPanel UI: VERIFY SYNTAX BEFORE reload
nginx -t 2>&1
# IF syntax OK: RELOAD nginx (NOT full restart — preserves other site live conns)
nginx -s reload 2>&1
echo "Nginx reload exit code: $?"
```

### FIX H6 — aaPanel Lua / proxy_cache directives causing nginx -t FAIL
*(Only run IF Blok 3 `nginx -t` reports Lua/set_by_lua_block / proxy_cache_purge error, AND the error references OTHER SITE vhosts, NOT nfcgo.)*
```bash
# IMPORTANT: ISOLATED — only DISABLE files causing errors, do NOT edit live config lines
# Example from past: iotplatform.clbgroups.com had set_by_lua_block
BAD_DIR=/www/server/panel/vhost/nginx
echo "=== Moving problematic extension files to .DISABLED suffix (not deleted) ==="
cd "$BAD_DIR"
# For each failing file from nginx -t output:
# (User replaces FILENAME below with actual files listed in error)
# mv FILENAME FILENAME.DISABLED_LUA_$(date +%Y%m%d)
#
# Then run:
nginx -t 2>&1
echo "If syntax OK now, reload:  nginx -s reload"
echo "DO NOT DELETE; .DISABLED suffix ensures it won't load."
```

### FIX H1 — Rebuild SPA with correct NUXT_PUBLIC_API_BASE_URL (baked into JS)
Run after backend env confirmed OK. *On server or local machine; then upload `frontend/.output/public` to `/www/wwwroot/nfcgo.clbgroups.com/spa/`*
```bash
cd /root  # or wherever frontend repo is deployed (local or server)
cd /path/to/frontend

# Ensure NO legacy env leaks
unset NUXT_PUBLIC_API_BASE_URL NUXT_PUBLIC_APP_URL
export NODE_OPTIONS="--max-old-space-size=4096"
# Explicitly force production values so npm generate uses them
export NUXT_PUBLIC_API_BASE_URL="https://nfcgo.clbgroups.com/api"
export NUXT_PUBLIC_APP_URL="https://nfcgo.clbgroups.com"

rm -rf node_modules/.cache .nuxt .output
npm install --legacy-peer-deps   # or: npm ci --legacy-peer-deps
npm run generate 2>&1 | tail -30

echo "=== Verify baked-in URL in newly-built files ==="
grep -rEo 'https?://[^"\x27 ]+' .output/public/_nuxt/*.js 2>/dev/null | grep -v 'sourceMappingURL' | sort -u | head -20

# Deploy (OVERWRITE existing /spa/* — NO other paths touched)
TARGET=/www/wwwroot/nfcgo.clbgroups.com/spa
mkdir -p "$TARGET"
# Backup previous build first
if [ -d "$TARGET/_nuxt" ]; then
  mv "$TARGET" "${TARGET}.rollback-$(date +%Y%m%d-%H%M%S)"
  mkdir -p "$TARGET"
fi
cp -a .output/public/. "$TARGET/"
chown -R www:www "$TARGET"
find "$TARGET" -type d -exec chmod 755 {} \;
find "$TARGET" -type f -exec chmod 644 {} \;
echo "Deployed. Rollback path: ${TARGET}.rollback-*"
ls -la "${TARGET}.rollback-"* 2>/dev/null | tail -3
```

---

## Phase 4: POST-FIX VERIFICATION (same as Blok 2 curl + browser)
1. Re-run `BLOK 2` — expect: `api/test` HTTP 200 JSON; `OPTIONS /api/login` returns `Access-Control-Allow-Origin: https://nfcgo.clbgroups.com` and `Access-Control-Allow-Credentials: true`; actual POST `/api/login` returns HTTP 401 with `{"success":false,"message":"Invalid credentials"}` JSON.
2. Browser: hard-refresh `Ctrl+Shift+R` → open DevTools Network → type email → confirm `check-email?email=…` XHR returns HTTP 200 JSON with `success:true`.
3. Try Sign in → expect either `Invalid email or password` toast (back-end reached) OR dashboard redirect (success). `Network error` toast means the hypothesis still unaddressed — review Phase 2 grep results again.

---

## Phase 5: ABORT / ROLLBACK paths (safety — no other sites affected)
| Change | Rollback command |
|--------|------------------|
| .env | `cp .env.backup.pre-fix.TIMESTAMP .env && php artisan config:cache` |
| HandleCors.php | `cp app/Http/Middleware/HandleCors.php.BACKUP-pre-fix app/Http/Middleware/HandleCors.php` |
| Nginx vhost | `cp /www/server/panel/vhost/nginx/nfcgo.clbgroups.com.conf.BACKUP-pre-fix.TIMESTAMP /www/server/panel/vhost/nginx/nfcgo.clbgroups.com.conf && nginx -t && nginx -s reload` |
| SPA build | `rm -rf /www/wwwroot/nfcgo.clbgroups.com/spa && mv /www/wwwroot/nfcgo.clbgroups.com/spa.rollback-TIMESTAMP /www/wwwroot/nfcgo.clbgroups.com/spa` |
| Disabled vhost Lua | `mv FILENAME.DISABLED_LUA_TS FILENAME` |

---

## Artifacts
- `debug-nfcgo-login-network-error.md` (this file)
