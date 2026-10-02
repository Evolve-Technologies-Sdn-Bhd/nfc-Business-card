<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| The frontend lives in a separate Nuxt SPA. This server only serves JSON
| API (under /api). If a user accidentally lands on the API server root,
| redirect them to the SPA instead of showing a useless welcome page.
|
| For local development on `:3001` / `:8000` the SPA runs on port `:3000`.
| On aaPanel production the SPA lives at the root of the public domain.
|
| Tested by going to http://localhost:3001 directly — user now auto-forwards
| to the Nuxt app rather than seeing "Cannot GET / 404" / "Laravel welcome".
*/

Route::get('/', function () {
    $host = request()->getHost();
    $port = (int) request()->getPort();

    // Local development: if user hits an API port (3001/3002/8000/8001)
    // the SPA frontend runs on port 3000 — always redirect there.
    $isLocalHost =
        $host === 'localhost' ||
        $host === '127.0.0.1' ||
        $host === '0.0.0.0' ||
        str_starts_with($host, '192.168.') ||
        str_starts_with($host, '10.') ||
        preg_match('/^172\.(1[6-9]|2[0-9]|3[01])\./', $host);

    $isApiPortLocal = in_array($port, [3001, 3002, 8000, 8001, 8080], true);

    $frontendUrl = Config::get('app.frontend_url');
    if (!$frontendUrl && ($isLocalHost || $isApiPortLocal)) {
        $scheme = request()->isSecure() ? 'https' : 'http';
        // Strip port when building target (frontend never runs on the API server port; keep clean local dev host)
        $frontendUrl = "{$scheme}://{$host}:3000";
    }
    if (!$frontendUrl) {
        // Production / same-host SPA — stay on the same domain (aaPanel).
        $frontendUrl = request()->schemeAndHttpHost();
    }

    return redirect()->away(rtrim($frontendUrl, '/') . '/', 302);
});

/*
|--------------------------------------------------------------------------
| Public Storage Fallback Route
|--------------------------------------------------------------------------
| Serves files from storage/app/public via Laravel when the
| `php artisan storage:link` symlink (public/storage → storage/app/public)
| does not exist (e.g. Windows dev machines without junction/symlink
| permissions, or aaPanel deployments where symlink was not created).
|
| This is a FALLBACK — Apache/Nginx will serve real symlinked files
| directly (much faster) and never hit this route. Only when the file
| path is missing from public/storage will Laravel pick it up here.
|
| Supported subfolders: brand, profile-images, legal-docs, invoices —
| anything under the `public` disk is safe to read here.
*/
Route::get('/storage/{path}', function (string $path) {
    // Guard against path traversal attempts (../ escapes)
    $normalized = str_replace(['\\', '//'], '/', $path);
    if (
        str_contains($normalized, '..') ||
        str_starts_with($normalized, '/') ||
        preg_match('/[\x00-\x1F\x7F]/', $normalized)
    ) {
        abort(400, 'Invalid storage path');
    }

    $disk = \Illuminate\Support\Facades\Storage::disk('public');

    if (!$disk->exists($normalized)) {
        // For image-type paths, return a minimal SVG placeholder instead
        // of the default Laravel 404 HTML page. This way, if the user
        // views a logo that was just-uploaded and APP_URL points to a
        // different host with a stale cached URL, they still see a
        // recognisable "broken image" state instead of an HTML-in-<img>
        // parse error.
        $ext = strtolower(pathinfo($normalized, PATHINFO_EXTENSION));
        $isImage = in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg', 'ico'], true);
        if ($isImage) {
            $svg = '<?xml version="1.0" encoding="UTF-8"?>' .
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64">' .
                '<rect width="64" height="64" rx="8" fill="#eef0f5"/>' .
                '<path d="M20 22h24v20H20z" fill="none" stroke="#b8beca" stroke-width="2" rx="4"/>' .
                '<path d="M20 30l8-8 10 10 6-4v14H20z" fill="#c9d0de"/>' .
                '<circle cx="28" cy="28" r="2" fill="#9aa3b5"/>' .
                '</svg>';
            return response($svg, 404)
                ->header('Content-Type', 'image/svg+xml')
                ->header('X-Content-Type-Options', 'nosniff')
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
        }
        abort(404, 'File not found');
    }

    $absolute = $disk->path($normalized);

    // Basic MIME detection — file() helper auto-sniffs too, but we
    // force a handful of web-critical types for browsers that ignore
    // file() sniffing on Windows.
    $ext = strtolower(pathinfo($normalized, PATHINFO_EXTENSION));
    $mimeMap = [
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
        'ico'  => 'image/x-icon',
        'pdf'  => 'application/pdf',
        'csv'  => 'text/csv; charset=UTF-8',
        'txt'  => 'text/plain; charset=UTF-8',
        'html' => 'text/html; charset=UTF-8',
    ];
    $forcedMime = $mimeMap[$ext] ?? null;

    return response()->file(
        $absolute,
        array_merge(
            $forcedMime ? ['Content-Type' => $forcedMime] : [],
            [
                'X-Content-Type-Options' => 'nosniff',
                // Prevent browsers from caching a stale 404 on a URL that
                // is about to be populated by an in-flight upload.
                'Vary' => 'Accept-Encoding',
            ]
        )
    )
        ->setPublic()
        ->setMaxAge(86400);
})
    ->where('path', '.*')
    ->name('storage.fallback');


/*
|--------------------------------------------------------------------------
| Dev / Admin helper — Clear framework cache
|--------------------------------------------------------------------------
| Occasionally Laravel's cached route/config/view files (under
| bootstrap/cache) carry stale snapshots of the filesystem / settings
| URLs. Calling this endpoint in aaPanel (or after deploy) is a quick
| way to flush those caches so the new /storage fallback route and
| brand settings URLs get routed and parsed correctly.
|
| This intentionally has a fixed random token — NOT open to the public.
| Token lives in .env as ADMIN_CACHE_FLUSH_TOKEN. If the env key is
| missing, route simply 404s (safe default).
*/
Route::any('/__admin/clear-cache/{token}', function (string $token) {
    $expected = env('ADMIN_CACHE_FLUSH_TOKEN');
    if (!$expected || hash_equals((string) $expected, (string) $token) === false) {
        abort(404);
    }

    $done = [];
    try {
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        $done[] = 'cache:clear';
    } catch (\Throwable $e) {
        $done[] = 'cache:clear SKIPPED: ' . $e->getMessage();
    }
    try {
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        $done[] = 'config:clear';
    } catch (\Throwable $e) {
        $done[] = 'config:clear SKIPPED: ' . $e->getMessage();
    }
    try {
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        $done[] = 'route:clear';
    } catch (\Throwable $e) {
        $done[] = 'route:clear SKIPPED: ' . $e->getMessage();
    }
    try {
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        $done[] = 'view:clear';
    } catch (\Throwable $e) {
        $done[] = 'view:clear SKIPPED: ' . $e->getMessage();
    }

    if (ob_get_level() > 0) {
        @ob_end_clean();
    }

    return response()->json([
        'success' => true,
        'cleared' => $done,
    ], 200)->header('Content-Type', 'application/json; charset=utf-8');
})->name('admin.cache.flush');

/*
 Optional health-check endpoint for aaPanel / UptimeRobot monitoring.
   GET /health  → { "status": "ok", ... }
*/
Route::get('/health', function () {
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $dbOk = true;
    } catch (\Throwable) {
        $dbOk = false;
    }
    return response()->json([
        'status'   => $dbOk ? 'ok' : 'degraded',
        'service'  => 'nfcgo-backend',
        'timestamp'=> now()->toIso8601String(),
        'version'  => '1.0.0',
        'database' => $dbOk ? 'connected' : 'unreachable',
    ], $dbOk ? 200 : 503);
});
