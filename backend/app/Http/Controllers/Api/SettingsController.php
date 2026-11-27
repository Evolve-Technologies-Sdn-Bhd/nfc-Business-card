<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserNotificationPreference;
use App\Services\NotificationService;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    protected $notificationService;
    protected $fileUploadService;

    public function __construct(
        NotificationService $notificationService,
        FileUploadService $fileUploadService
    ) {
        $this->notificationService = $notificationService;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Update user personal information
     */
    public function updatePersonalInfo(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ], [
            'email.unique' => 'This email is already registered in our system. Please use a different email address.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $emailChanged = strcasecmp((string) $user->email, (string) $request->email) !== 0;

            // Account details only. Public profiles keep their own contact
            // details (edited in the Profile Builder); this used to overwrite
            // the first card's profile name/email/phone as a side effect.
            $user->fill([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);

            // A new address must be verified before it is trusted: otherwise an
            // account could carry an e-mail its owner never proved they control.
            if ($emailChanged) {
                $user->email_verified_at = null;
            }

            $user->save();

            if ($emailChanged) {
                try {
                    $user->sendEmailVerificationNotification();
                } catch (\Throwable $e) {
                    Log::warning('Verification e-mail for changed address failed', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            try {
                $this->notificationService->create($user, 'profile_updated', []);
            } catch (\Throwable) { /* non-critical */ }

            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => $emailChanged
                    ? 'Saved. We sent a verification link to your new e-mail address.'
                    : 'Personal information updated successfully',
                'email_verification_required' => $emailChanged,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update user personal info', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update personal information. Please try again.'
            ], 500);
        }
    }

    /**
     * Get user settings
     */
    public function getSettings(Request $request)
    {
        $user = $request->user();

        $preference = UserNotificationPreference::where('user_id', $user->id)->first();

        // The placeholder `security_settings` / `privacy_settings` blocks were
        // removed: nothing persisted or enforced them, and no client reads them.
        return response()->json([
            'success' => true,
            'user' => $user,
            'notification_settings' => $preference
                ? [
                    'profile_views' => (bool) $preference->profile_views,
                    'nfc_taps' => (bool) $preference->nfc_taps,
                    'weekly_reports' => (bool) $preference->weekly_reports,
                    'product_updates' => (bool) $preference->product_updates,
                ]
                : UserNotificationPreference::DEFAULTS,
            // Admin-configured contact (System Settings → General), used by the
            // "Close account" request on the Settings page.
            'support_email' => $this->supportEmail(),
        ]);
    }

    /**
     * Download a copy of the signed-in user's own data.
     *
     * POST /api/settings/export-data
     * Read-only: account details, NFC cards with their profile pages and links,
     * invoices, subscriptions and notification preferences. Credentials and
     * 2FA material are never included.
     */
    public function exportData(Request $request)
    {
        $user = $request->user();

        try {
            $account = $user->makeHidden(['nfcCards', 'nfc_cards', 'latestSubscription'])->toArray();
            unset($account['nfc_cards'], $account['latest_subscription']);
            unset($account['two_factor_secret'], $account['remember_token'], $account['password']);
            if (is_array($account['settings'] ?? null)) {
                $account['settings'] = collect($account['settings'])
                    ->reject(fn ($v, $k) => Str::contains(strtolower((string) $k), ['two_factor', 'recovery', 'secret', 'token']))
                    ->all();
            }

            $cards = $user->nfcCards()
                ->with(['landingPage.socialLinks'])
                ->get()
                ->map(fn ($card) => $card->toArray())
                ->all();

            $invoices = \App\Models\Invoice::where('user_id', $user->id)
                ->orderByDesc('issued_at')
                ->get(['invoice_number', 'status', 'subtotal', 'tax_amount', 'discount_amount', 'total_amount', 'currency', 'line_items', 'issued_at', 'paid_at', 'due_date'])
                ->toArray();

            $preference = UserNotificationPreference::where('user_id', $user->id)->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'exported_at' => now()->toIso8601String(),
                    'account' => $account,
                    'nfc_cards' => $cards,
                    'invoices' => $invoices,
                    'subscriptions' => $user->subscriptions()->get()->toArray(),
                    'notification_preferences' => $preference
                        ? $preference->only(['profile_views', 'nfc_taps', 'weekly_reports', 'product_updates'])
                        : UserNotificationPreference::DEFAULTS,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('User data export failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'We could not prepare your export. Please try again.',
            ], 500);
        }
    }

    private function supportEmail(): string
    {
        $saved = \Illuminate\Support\Facades\Cache::get('admin_system_settings:general', []);
        $email = is_array($saved) ? ($saved['support_email'] ?? null) : null;

        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : 'support@nfcgo.com';
    }

    /**
     * Update a single (or several) email notification preference(s).
     *
     * PUT /api/settings/notifications
     * Body: { profile_views?: bool, nfc_taps?: bool, weekly_reports?: bool, product_updates?: bool }
     */
    public function updateNotificationSettings(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'profile_views' => 'sometimes|boolean',
            'nfc_taps' => 'sometimes|boolean',
            'weekly_reports' => 'sometimes|boolean',
            'product_updates' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $preference = UserNotificationPreference::firstOrCreate(
                ['user_id' => $user->id],
                UserNotificationPreference::DEFAULTS
            );

            // Only persist the keys that were actually submitted.
            $updates = array_intersect_key(
                $validator->validated(),
                array_flip(['profile_views', 'nfc_taps', 'weekly_reports', 'product_updates'])
            );

            $preference->fill($updates)->save();

            return response()->json([
                'success' => true,
                'message' => 'Notification settings updated',
                'notification_settings' => [
                    'profile_views' => (bool) $preference->profile_views,
                    'nfc_taps' => (bool) $preference->nfc_taps,
                    'weekly_reports' => (bool) $preference->weekly_reports,
                    'product_updates' => (bool) $preference->product_updates,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update notification settings', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update notification settings',
            ], 500);
        }
    }

    /**
     * Upload user account image (Settings page)
     */
    public function uploadUserAccountImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();

            // Delete old image if exists
            if ($user->account_image) {
                try {
                    // Extract path from URL
                    $oldPath = str_replace(url('storage/'), '', $user->account_image);
                    $this->fileUploadService->deleteFile($oldPath);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete old user account image', ['error' => $e->getMessage()]);
                }
            }

            // Upload new image
            $uploadResult = $this->fileUploadService->uploadProfileImage(
                $request->file('image'),
                $user->id
            );

            // Update user table (not profile table)
            $user->update([
                'account_image' => $uploadResult['url'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Account image uploaded successfully',
                'account_image' => $uploadResult['url'],
                'user' => $user->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to upload user account image', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete user account image (Settings page)
     */
    public function deleteUserAccountImage(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->account_image) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account image to delete'
                ], 400);
            }

            // Delete image file
            try {
                $imagePath = str_replace(url('storage/'), '', $user->account_image);
                $this->fileUploadService->deleteFile($imagePath);
            } catch (\Exception $e) {
                Log::warning('Failed to delete user account image file', ['error' => $e->getMessage()]);
            }

            // Update user table
            $user->update([
                'account_image' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Account image removed successfully',
                'user' => $user->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete user account image', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove image. Please try again.'
            ], 500);
        }
    }
}
