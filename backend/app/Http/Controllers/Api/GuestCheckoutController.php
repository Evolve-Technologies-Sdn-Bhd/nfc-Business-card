<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\EmailNormalizer;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Guest Checkout Controller
 *
 * Lets a visitor buy a Basic/Premium card without registering first.
 *
 * The data model requires an owner for every transaction and order
 * (transactions.user_id and orders.user_id are NOT NULL), so "no account at all"
 * is not actually possible. Instead a placeholder account is created at the
 * moment the visitor presses Pay:
 *   • no password — it cannot be logged into yet;
 *   • email unverified — ownership of the inbox is proven before the physical
 *     card is dispatched;
 *   • reclaimable while unpaid — a stranger cannot squat someone else's address.
 *
 * Once the payment succeeds the visitor sets a password and continues as a
 * normal account.
 */
class GuestCheckoutController extends Controller
{
    /** Plans a guest is allowed to buy without registering first. */
    private const GUEST_PLANS = ['basic', 'premium'];

    public function __construct(
        protected NotificationService $notificationService,
        protected EmailNormalizer $emailNormalizer,
    ) {
    }

    /**
     * Create (or reuse) the placeholder account and hand back an auth token.
     *
     * The token is what allows the untouched payment endpoints to work for a
     * guest: /payment/initiate still resolves auth()->user() as usual.
     */
    public function start(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'plan' => 'required|string',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $plan = strtolower((string) $request->plan);

        if (!in_array($plan, self::GUEST_PLANS, true)) {
            return response()->json([
                'success' => false,
                'code' => 'PLAN_NOT_ELIGIBLE',
                'message' => 'Guest checkout is only available for the Basic and Premium plans. Please create an account for other plans.',
            ], 422);
        }

        $email = strtolower(trim((string) $request->email));

        if ($this->emailNormalizer->isDisposable($email)) {
            return response()->json([
                'success' => false,
                'code' => 'EMAIL_NOT_ALLOWED',
                'message' => 'Please use a permanent email address so we can send your order updates.',
            ], 422);
        }

        $existing = User::whereRaw('LOWER(email) = ?', [$email])->first();

        // An account that already has a password, an OAuth link or a paid
        // subscription belongs to someone: never take it over from checkout.
        if ($existing && !$existing->isProvisionalGuest()) {
            return response()->json([
                'success' => false,
                'code' => 'ACCOUNT_EXISTS',
                'message' => 'This email already has an account. Please log in to continue.',
            ], 409);
        }

        try {
            $user = $existing ?: new User();

            $localPart = strstr($email, '@', true) ?: 'Guest';
            $user->first_name = $request->filled('first_name')
                ? $request->first_name
                : ucfirst($localPart);
            $user->last_name = $request->filled('last_name')
                ? $request->last_name
                : ($user->last_name ?? '');
            $user->email = $email;
            $user->password = null;
            $user->provider = null;
            $user->is_new_user = true;
            $user->settings = array_merge((array) ($user->settings ?? []), [
                'guest_checkout' => true,
            ]);

            // Only a brand-new placeholder starts unverified; an existing one may
            // already have proven ownership of the inbox.
            if (!$existing) {
                $user->email_verified_at = null;
            }

            $user->save();

            $token = $user->createToken('guest_checkout')->plainTextToken;

            // Verify the inbox — required before the physical card is shipped.
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $e) {
                Log::warning('Failed to send guest checkout verification email', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Welcome / set-password nudge. The action URL falls back to the
            // password reset request page so the account is still reachable if
            // the browser that started the checkout is lost.
            try {
                $this->notificationService->create($user, 'guest_checkout_welcome', [
                    'name' => $user->first_name,
                    'action_url' => config('app.frontend_url') . '/UserAccount/ForgotPass',
                    'action_text' => 'Set your password',
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to send guest checkout welcome notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'token' => $token,
                'token_type' => 'Bearer',
                'needs_password_setup' => true,
                'is_new_user' => true,
                'user' => $user->fresh(),
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Guest checkout start failed', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'We could not start your checkout. Please try again.',
            ], 500);
        }
    }

    /**
     * Set a password on a placeholder account, turning it into a normal one.
     *
     * The caller already holds a valid token for exactly this account (issued by
     * start()), so this is the same trust level as registering — no email
     * round-trip is needed. Email verification is deliberately NOT bypassed:
     * the account stays unverified until the verification link is opened.
     */
    public function setPassword(Request $request)
    {
        $user = $request->user();

        if (!$user || !$user->requiresPasswordSetup()) {
            return response()->json([
                'success' => false,
                'code' => 'NOT_ELIGIBLE',
                'message' => 'A password can only be set on an account created through guest checkout.',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/',
            ],
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*?&).',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        Log::info('Guest checkout password set', ['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'message' => 'Your password has been set.',
            'user' => $user->fresh(),
        ]);
    }
}
