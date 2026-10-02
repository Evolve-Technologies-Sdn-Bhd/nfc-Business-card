<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminSystemSettingsController extends Controller
{
    protected string $cachePrefix = 'admin_system_settings:';
    protected FileUploadService $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Get general system settings
     */
    public function getGeneralSettings()
    {
        try {
            $defaults = [
                'app_name' => 'NFCGo',
                'app_url' => 'https://nfcgo.my',
                'support_email' => 'support@nfcgo.my',
                'support_phone' => '+60 12-345 6789',
                'company_name' => 'NFCGo Sdn Bhd',
                'company_address' => '',
                'company_reg_no' => '',
                'company_tax_id' => '',
                'default_currency' => 'MYR',
                'default_language' => 'en',
                'default_timezone' => 'Asia/Kuala_Lumpur',
                'system_logo_url' => '',
                'system_favicon_url' => '',
            ];

            $saved = Cache::get($this->cachePrefix . 'general', []);
            $brand = Cache::get($this->cachePrefix . 'brand', []);
            $data = array_merge($defaults, $saved, $brand);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get general settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load settings',
            ], 500);
        }
    }

    /**
     * Save general system settings
     */
    public function saveGeneralSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'app_name' => 'required|string|max:255',
            'app_url' => 'nullable|url|max:255',
            'support_email' => 'nullable|email|max:255',
            'support_phone' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:1000',
            'company_reg_no' => 'nullable|string|max:100',
            'company_tax_id' => 'nullable|string|max:100',
            'default_currency' => 'required|string|in:MYR,SGD,USD',
            'default_language' => 'required|string|in:en,ms,zh',
            'default_timezone' => 'required|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();
            Cache::forever($this->cachePrefix . 'general', $data);

            return response()->json([
                'success' => true,
                'message' => 'General settings saved successfully',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save general settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment system settings
     */
    public function getPaymentSettings()
    {
        try {
            $defaults = [
                'fiuu_merchant_id' => '',
                'fiuu_verify_key' => '',
                'fiuu_secret_key' => '',
                'fiuu_enabled' => true,
                'fiuu_environment' => 'sandbox',
                'bank_name' => '',
                'bank_account_name' => '',
                'bank_account_number' => '',
                'bank_transfer_enabled' => true,
                'ewallet_tng' => true,
                'ewallet_grabpay' => true,
                'ewallet_shopeepay' => true,
                'ewallet_duitnow' => true,
            ];

            $saved = Cache::get($this->cachePrefix . 'payment', []);
            $data = array_merge($defaults, $saved);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get payment settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load settings',
            ], 500);
        }
    }

    /**
     * Save payment system settings
     */
    public function savePaymentSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fiuu_merchant_id' => 'nullable|string|max:100',
            'fiuu_verify_key' => 'nullable|string|max:255',
            'fiuu_secret_key' => 'nullable|string|max:255',
            'fiuu_enabled' => 'nullable|boolean',
            'fiuu_environment' => 'nullable|string|in:sandbox,production',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:100',
            'bank_transfer_enabled' => 'nullable|boolean',
            'ewallet_tng' => 'nullable|boolean',
            'ewallet_grabpay' => 'nullable|boolean',
            'ewallet_shopeepay' => 'nullable|boolean',
            'ewallet_duitnow' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();
            $data['fiuu_enabled'] = $request->boolean('fiuu_enabled', true);
            $data['bank_transfer_enabled'] = $request->boolean('bank_transfer_enabled', true);
            $data['ewallet_tng'] = $request->boolean('ewallet_tng', true);
            $data['ewallet_grabpay'] = $request->boolean('ewallet_grabpay', true);
            $data['ewallet_shopeepay'] = $request->boolean('ewallet_shopeepay', true);
            $data['ewallet_duitnow'] = $request->boolean('ewallet_duitnow', true);

            Cache::forever($this->cachePrefix . 'payment', $data);

            return response()->json([
                'success' => true,
                'message' => 'Payment settings saved successfully',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save payment settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get email system settings
     */
    public function getEmailSettings()
    {
        try {
            $defaults = [
                'smtp_host' => env('MAIL_HOST', ''),
                'smtp_port' => env('MAIL_PORT', 587),
                'smtp_username' => env('MAIL_USERNAME', ''),
                'smtp_password' => env('MAIL_PASSWORD', ''),
                'smtp_encryption' => env('MAIL_ENCRYPTION', 'tls'),
                'mailer' => env('MAIL_MAILER', 'smtp'),
                'from_name' => env('MAIL_FROM_NAME', 'NFCGo'),
                'from_email' => env('MAIL_FROM_ADDRESS', 'no-reply@nfcgo.my'),
            ];

            $saved = Cache::get($this->cachePrefix . 'email', []);
            $data = array_merge($defaults, $saved);

            // Don't return actual password in full, just a placeholder if it exists
            if (!empty($data['smtp_password'])) {
                $data['smtp_password'] = '********';
            }

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get email settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load settings',
            ], 500);
        }
    }

    /**
     * Save email system settings
     */
    public function saveEmailSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|string|in:tls,ssl,',
            'mailer' => 'required|string|in:smtp,sendmail,log',
            'from_name' => 'required|string|max:255',
            'from_email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $existing = Cache::get($this->cachePrefix . 'email', []);
            $data = $validator->validated();

            // Handle password masking - if it's ********, use existing stored password
            if ($data['smtp_password'] === '********') {
                if (isset($existing['smtp_password_raw'])) {
                    $data['smtp_password_raw'] = $existing['smtp_password_raw'];
                } elseif (!empty(env('MAIL_PASSWORD'))) {
                    $data['smtp_password_raw'] = env('MAIL_PASSWORD');
                } else {
                    $data['smtp_password_raw'] = '';
                }
            } else {
                $data['smtp_password_raw'] = $data['smtp_password'];
            }

            Cache::forever($this->cachePrefix . 'email', $data);

            return response()->json([
                'success' => true,
                'message' => 'Email settings saved successfully',
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save email settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send test email
     */
    public function sendTestEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'to' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $to = $validator->validated()['to'];
            $emailSettings = Cache::get($this->cachePrefix . 'email', []);
            $fromName = $emailSettings['from_name'] ?? env('MAIL_FROM_NAME', 'NFCGo');
            $fromEmail = $emailSettings['from_email'] ?? env('MAIL_FROM_ADDRESS', 'no-reply@nfcgo.my');

            // Send a simple test email
            Mail::raw("This is a test email from NFCGo System Configuration.\n\nIf you received this, your email settings are working correctly!\n\nTime: " . now()->toDateTimeString(), function ($message) use ($to, $fromName, $fromEmail) {
                $message->to($to)
                    ->from($fromEmail, $fromName)
                    ->subject('NFCGo - Test Email');
            });

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully to ' . $to,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send test email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload system logo
     */
    public function uploadLogo(Request $request)
    {
        // Clean any stray PHP startup notices / warnings before emitting JSON
        if (ob_get_level() > 0) {
            @ob_end_clean();
        }
        @ini_set('display_errors', '0');
        @ini_set('display_startup_errors', '0');

        $validator = Validator::make($request->all(), [
            'logo' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'system_logo' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'brand_logo' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'image' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'file' => 'nullable|file|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ], [
            'logo.max' => 'Logo file size must not exceed 2MB',
            'logo.mimes' => 'Logo must be PNG, JPG, SVG, or WebP format',
            'system_logo.max' => 'Logo file size must not exceed 2MB',
            'system_logo.mimes' => 'Logo must be PNG, JPG, SVG, or WebP format',
            'brand_logo.max' => 'Logo file size must not exceed 2MB',
            'brand_logo.mimes' => 'Logo must be PNG, JPG, SVG, or WebP format',
            'image.max' => 'Logo file size must not exceed 2MB',
            'image.mimes' => 'Logo must be PNG, JPG, SVG, or WebP format',
            'file.max' => 'Logo file size must not exceed 2MB',
            'file.mimes' => 'Logo must be PNG, JPG, SVG, or WebP format',
        ]);

        if ($validator->fails()) {
            if (ob_get_level() > 0) {
                @ob_end_clean();
            }
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)->header('Content-Type', 'application/json; charset=utf-8');
        }

        try {
            // Accept multiple field names (backward compat). Prefer "logo"
            // but also accept "system_logo", "brand_logo", "image", "file"
            // because some older upload components & Axios FormData
            // serialise with different keys.
            $file = null;
            foreach (['logo', 'system_logo', 'brand_logo', 'image', 'file'] as $field) {
                if ($request->hasFile($field) && $request->file($field)->isValid()) {
                    $file = $request->file($field);
                    break;
                }
            }

            if (!$file) {
                if (ob_get_level() > 0) {
                    @ob_end_clean();
                }
                return response()->json([
                    'success' => false,
                    'message' => 'No valid logo file uploaded',
                ], 422)->header('Content-Type', 'application/json; charset=utf-8');
            }
            $filename = 'system_logo_' . time() . '.' . $file->getClientOriginalExtension();
            $path = 'brand/' . $filename;

            Storage::disk('public')->makeDirectory('brand');

            // Write file to disk with MULTIPLE fallbacks because different
            // Laravel 12 / Flysystem 3.x environments support different
            // UploadedFile::storeAs signatures.
            //
            // STRATEGY (safe order):
            //   1) $file->storeAs('brand', $filename, 'public') — signature
            //      (dir, name, disk-string) — 100% compatible across L9-12.
            //   2) If that fails, use Storage::disk('public')->put() with
            //      fopen() stream (respects binary safety, avoids PHP
            //      file_get_contents stream wrapper issues on Windows).
            //   3) If that also fails, return a specific error instead of
            //      generic 500.
            $storedPath = false;
            $storageFailReason = null;
            try {
                /** @var \Illuminate\Http\UploadedFile $file */
                $storedPath = $file->storeAs('brand', $filename, 'public');
            } catch (\Throwable $e) {
                $storageFailReason = 'storeAs(string-disk): ' . $e->getMessage();
                Log::warning('uploadLogo storeAs(string-disk) fallback: ' . $storageFailReason);
                $storedPath = false;
            }

            if ($storedPath === false || $storedPath === null) {
                try {
                    // Explicit visibility set AFTER write, so even when
                    // a symlink-less fallback route reads the file, the
                    // underlying filesystem permissions are world-readable
                    // for Nginx/Apache on Linux aaPanel.
                    $handle = $file->getRealPath() ? fopen($file->getRealPath(), 'rb') : false;
                    if ($handle !== false) {
                        $written = Storage::disk('public')->put($path, $handle);
                        if (is_resource($handle)) {
                            fclose($handle);
                        }
                        if ($written) {
                            try {
                                Storage::disk('public')->setVisibility($path, 'public');
                            } catch (\Throwable) {
                                // ignore — some local filesystems don't
                                // support visibility calls; read still works.
                            }
                            $storedPath = $path;
                        } else {
                            $storageFailReason = ($storageFailReason ? $storageFailReason . ' | ' : '') . 'put(stream): returned false';
                        }
                    } else {
                        $storageFailReason = ($storageFailReason ? $storageFailReason . ' | ' : '') . 'put(stream): fopen failed';
                    }
                } catch (\Throwable $e2) {
                    $storageFailReason = ($storageFailReason ? $storageFailReason . ' | ' : '') . 'put(stream): ' . $e2->getMessage();
                    Log::warning('uploadLogo put(stream) also failed: ' . $storageFailReason);
                    $storedPath = false;
                }
            }

            // Final existence check — regardless of method, confirm bytes
            // actually landed on the public disk before returning success.
            $fileExistsNow = false;
            try {
                $fileExistsNow = (bool) Storage::disk('public')->exists($path);
            } catch (\Throwable) {
                $fileExistsNow = false;
            }
            if (!$fileExistsNow) {
                if (ob_get_level() > 0) {
                    @ob_end_clean();
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to write logo file to storage disk',
                    'debug' => [
                        'attempted_path' => $path,
                        'filename' => $filename,
                        'size_bytes' => $file->getSize(),
                        'original_name' => $file->getClientOriginalName(),
                        'storage_fail_reason' => $storageFailReason,
                        'disk_root' => Storage::disk('public')->path(''),
                    ],
                ], 500)->header('Content-Type', 'application/json; charset=utf-8');
            }

            // Build the final URL. Prefer Storage::disk('public')->url() so
            // filesystems.php config is the single source of truth
            // (including custom URLs, S3 driver swaps, etc.). For local
            // dev we also return an alternative `public_url` that points
            // at the fallback /storage/{path} route, so the SPA on a
            // different port (3000 vs 8000) can always hit the image
            // regardless of whether storage:link symlink exists on disk.
            $url = Storage::disk('public')->url($path);

            // Determine a "public reachable URL" that definitely works
            // from the SPA origin. filesystems.public.url defaults to
            // APP_URL + '/storage'. If APP_URL is an API-only port (e.g.
            // localhost:8000 on a Windows dev box where SPA runs on 3000),
            // the SPA <img> can still fetch it because same host, but we
            // also guarantee it via a path-absolute URL.
            $absoluteRoot = rtrim(Config::get('app.url'), '/');
            $publicPath = '/storage/' . ltrim($path, '/');
            $publicFallbackUrl = $absoluteRoot . $publicPath;

            // Remove old logo if exists
            $brand = Cache::get($this->cachePrefix . 'brand', []);
            if (!empty($brand['system_logo_path']) && $brand['system_logo_path'] !== $path) {
                try {
                    $this->fileUploadService->deleteFile($brand['system_logo_path']);
                } catch (\Throwable $e) {
                    Log::warning('Failed to delete old system logo: ' . $e->getMessage());
                }
            }

            $brand['system_logo_url'] = $url;
            $brand['system_logo_public_url'] = $publicFallbackUrl;
            $brand['system_logo_path'] = $path;
            Cache::forever($this->cachePrefix . 'brand', $brand);

            // Immediately evict framework-level caches that may contain
            // stale config / settings snapshots (route, config, view cache
            // all live under bootstrap/cache on Laravel 12 — harmless if
            // missing on dev, critical when page cache was warmed pre-upload).
            try {
                Cache::forget($this->cachePrefix . 'general');
            } catch (\Throwable $e) {
                // ignore — cache driver unavailable
            }

            if (ob_get_level() > 0) {
                @ob_end_clean();
            }
            return response()->json([
                'success' => true,
                'message' => 'System logo uploaded successfully',
                'url' => $url,
                'public_url' => $publicFallbackUrl,
                'path' => $path,
            ], 200)->header('Content-Type', 'application/json; charset=utf-8');
        } catch (\Exception $e) {
            Log::error('Failed to upload system logo: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            if (ob_get_level() > 0) {
                @ob_end_clean();
            }
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload logo: ' . $e->getMessage(),
                'debug' => [
                    'class'   => get_class($e),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                    'trace'   => collect($e->getTrace())
                        ->take(8)
                        ->map(fn ($t) => ($t['file'] ?? '?') . ':' . ($t['line'] ?? '?') . ' :: ' . ($t['function'] ?? '?'))
                        ->values()
                        ->all(),
                ],
            ], 500)->header('Content-Type', 'application/json; charset=utf-8');
        }
    }

    /**
     * Remove system logo
     */
    public function removeLogo()
    {
        // Clean any stray PHP startup notices / warnings before emitting JSON
        if (ob_get_level() > 0) {
            @ob_end_clean();
        }
        @ini_set('display_errors', '0');
        @ini_set('display_startup_errors', '0');

        try {
            $brand = Cache::get($this->cachePrefix . 'brand', []);

            if (!empty($brand['system_logo_path'])) {
                try {
                    $this->fileUploadService->deleteFile($brand['system_logo_path']);
                } catch (\Throwable $e) {
                    Log::warning('Failed to delete old system logo: ' . $e->getMessage());
                }
            }

            unset($brand['system_logo_url']);
            unset($brand['system_logo_public_url']);
            unset($brand['system_logo_path']);
            Cache::forever($this->cachePrefix . 'brand', $brand);

            try {
                Cache::forget($this->cachePrefix . 'general');
            } catch (\Throwable $e) {
                // ignore — cache driver unavailable
            }

            if (ob_get_level() > 0) {
                @ob_end_clean();
            }
            return response()->json([
                'success' => true,
                'message' => 'System logo removed successfully',
            ], 200)->header('Content-Type', 'application/json; charset=utf-8');
        } catch (\Exception $e) {
            Log::error('Failed to remove system logo: ' . $e->getMessage());
            if (ob_get_level() > 0) {
                @ob_end_clean();
            }
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove logo: ' . $e->getMessage(),
            ], 500)->header('Content-Type', 'application/json; charset=utf-8');
        }
    }

    /**
     * Upload system favicon
     */
    public function uploadFavicon(Request $request)
    {
        // Clean any stray PHP startup notices / warnings before emitting JSON
        if (ob_get_level() > 0) {
            @ob_end_clean();
        }
        @ini_set('display_errors', '0');
        @ini_set('display_startup_errors', '0');

        $validator = Validator::make($request->all(), [
            'favicon' => 'nullable|file|mimes:png,ico|max:500',
            'icon'    => 'nullable|file|mimes:png,ico|max:500',
            'image'   => 'nullable|file|mimes:png,ico|max:500',
            'file'    => 'nullable|file|mimes:png,ico|max:500',
            'system_favicon' => 'nullable|file|mimes:png,ico|max:500',
        ], [
            'favicon.max' => 'Favicon file size must not exceed 500KB',
            'favicon.mimes' => 'Favicon must be PNG or ICO format',
            'icon.max' => 'Favicon file size must not exceed 500KB',
            'icon.mimes' => 'Favicon must be PNG or ICO format',
            'image.max' => 'Favicon file size must not exceed 500KB',
            'image.mimes' => 'Favicon must be PNG or ICO format',
            'file.max' => 'Favicon file size must not exceed 500KB',
            'file.mimes' => 'Favicon must be PNG or ICO format',
            'system_favicon.max' => 'Favicon file size must not exceed 500KB',
            'system_favicon.mimes' => 'Favicon must be PNG or ICO format',
        ]);

        if ($validator->fails()) {
            if (ob_get_level() > 0) {
                @ob_end_clean();
            }
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)->header('Content-Type', 'application/json; charset=utf-8');
        }

        try {
            $file = null;
            foreach (['favicon', 'system_favicon', 'icon', 'image', 'file'] as $field) {
                if ($request->hasFile($field) && $request->file($field)->isValid()) {
                    $file = $request->file($field);
                    break;
                }
            }
            if (!$file) {
                if (ob_get_level() > 0) {
                    @ob_end_clean();
                }
                return response()->json([
                    'success' => false,
                    'message' => 'No valid favicon file uploaded',
                ], 422)->header('Content-Type', 'application/json; charset=utf-8');
            }
            $filename = 'system_favicon_' . time() . '.' . $file->getClientOriginalExtension();
            $path = 'brand/' . $filename;

            Storage::disk('public')->makeDirectory('brand');

            $storedPath = false;
            $storageFailReason = null;
            try {
                /** @var \Illuminate\Http\UploadedFile $file */
                $storedPath = $file->storeAs('brand', $filename, 'public');
            } catch (\Throwable $e) {
                $storageFailReason = 'storeAs(string-disk): ' . $e->getMessage();
                Log::warning('uploadFavicon storeAs fallback: ' . $storageFailReason);
                $storedPath = false;
            }
            if ($storedPath === false || $storedPath === null) {
                try {
                    $handle = $file->getRealPath() ? fopen($file->getRealPath(), 'rb') : false;
                    if ($handle !== false) {
                        $written = Storage::disk('public')->put($path, $handle);
                        if (is_resource($handle)) {
                            fclose($handle);
                        }
                        if ($written) {
                            try {
                                Storage::disk('public')->setVisibility($path, 'public');
                            } catch (\Throwable) {
                            }
                            $storedPath = $path;
                        } else {
                            $storageFailReason = ($storageFailReason ? $storageFailReason . ' | ' : '') . 'put(stream): returned false';
                        }
                    } else {
                        $storageFailReason = ($storageFailReason ? $storageFailReason . ' | ' : '') . 'put(stream): fopen failed';
                    }
                } catch (\Throwable $e2) {
                    $storageFailReason = ($storageFailReason ? $storageFailReason . ' | ' : '') . 'put(stream): ' . $e2->getMessage();
                    Log::warning('uploadFavicon put(stream) also failed: ' . $storageFailReason);
                    $storedPath = false;
                }
            }
            $fileExistsNow = false;
            try {
                $fileExistsNow = (bool) Storage::disk('public')->exists($path);
            } catch (\Throwable) {
                $fileExistsNow = false;
            }
            if (!$fileExistsNow) {
                if (ob_get_level() > 0) {
                    @ob_end_clean();
                }
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to write favicon file to storage disk',
                    'debug' => [
                        'attempted_path' => $path,
                        'filename' => $filename,
                        'size_bytes' => $file->getSize(),
                        'original_name' => $file->getClientOriginalName(),
                        'storage_fail_reason' => $storageFailReason,
                        'disk_root' => Storage::disk('public')->path(''),
                    ],
                ], 500)->header('Content-Type', 'application/json; charset=utf-8');
            }

            $url = Storage::disk('public')->url($path);
            $absoluteRoot = rtrim(Config::get('app.url'), '/');
            $publicPath = '/storage/' . ltrim($path, '/');
            $publicFallbackUrl = $absoluteRoot . $publicPath;

            // Remove old favicon if exists
            $brand = Cache::get($this->cachePrefix . 'brand', []);
            if (!empty($brand['system_favicon_path']) && $brand['system_favicon_path'] !== $path) {
                try {
                    $this->fileUploadService->deleteFile($brand['system_favicon_path']);
                } catch (\Throwable) {
                }
            }

            $brand['system_favicon_url'] = $url;
            $brand['system_favicon_public_url'] = $publicFallbackUrl;
            $brand['system_favicon_path'] = $path;
            Cache::forever($this->cachePrefix . 'brand', $brand);

            try {
                Cache::forget($this->cachePrefix . 'general');
            } catch (\Throwable) {
            }

            if (ob_get_level() > 0) {
                @ob_end_clean();
            }
            return response()->json([
                'success' => true,
                'message' => 'System favicon uploaded successfully',
                'url' => $url,
                'public_url' => $publicFallbackUrl,
                'path' => $path,
            ], 200)->header('Content-Type', 'application/json; charset=utf-8');
        } catch (\Exception $e) {
            Log::error('Failed to upload system favicon: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            if (ob_get_level() > 0) {
                @ob_end_clean();
            }
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload favicon: ' . $e->getMessage(),
                'debug' => [
                    'class'   => get_class($e),
                    'file'    => $e->getFile(),
                    'line'    => $e->getLine(),
                    'trace'   => collect($e->getTrace())
                        ->take(8)
                        ->map(fn ($t) => ($t['file'] ?? '?') . ':' . ($t['line'] ?? '?') . ' :: ' . ($t['function'] ?? '?'))
                        ->values()
                        ->all(),
                ],
            ], 500)->header('Content-Type', 'application/json; charset=utf-8');
        }
    }

    /**
     * Remove system favicon
     */
    public function removeFavicon()
    {
        // Clean any stray PHP startup notices / warnings before emitting JSON
        if (ob_get_level() > 0) {
            @ob_end_clean();
        }
        @ini_set('display_errors', '0');
        @ini_set('display_startup_errors', '0');

        try {
            $brand = Cache::get($this->cachePrefix . 'brand', []);

            if (!empty($brand['system_favicon_path'])) {
                $this->fileUploadService->deleteFile($brand['system_favicon_path']);
            }

            unset($brand['system_favicon_url']);
            unset($brand['system_favicon_path']);
            Cache::forever($this->cachePrefix . 'brand', $brand);

            if (ob_get_level() > 0) {
                @ob_end_clean();
            }
            return response()->json([
                'success' => true,
                'message' => 'System favicon removed successfully',
            ], 200)->header('Content-Type', 'application/json; charset=utf-8');
        } catch (\Exception $e) {
            Log::error('Failed to remove system favicon: ' . $e->getMessage());
            if (ob_get_level() > 0) {
                @ob_end_clean();
            }
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove favicon: ' . $e->getMessage(),
            ], 500)->header('Content-Type', 'application/json; charset=utf-8');
        }
    }

    /**
     * Public brand info endpoint (for non-admin pages like login/home)
     */
    public function publicBrandInfo()
    {
        try {
            $defaults = [
                'app_name' => 'NFCGo',
                'system_logo_url' => '',
                'system_favicon_url' => '',
            ];
            $general = Cache::get($this->cachePrefix . 'general', []);
            $brand = Cache::get($this->cachePrefix . 'brand', []);
            $data = array_merge($defaults, array_intersect_key($general, ['app_name' => true]), $brand);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get public brand info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => [
                    'app_name' => 'NFCGo',
                    'system_logo_url' => '',
                    'system_favicon_url' => '',
                ],
            ], 500);
        }
    }
}
