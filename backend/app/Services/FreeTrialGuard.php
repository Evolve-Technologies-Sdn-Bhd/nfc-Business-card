<?php

namespace App\Services;

use App\Models\FreeTrialClaim;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Enforces the "one free trial per person" rule.
 *
 * Policy (see config payment.subscriptions.abuse):
 *   - The ACCOUNT is always allowed to exist — only the free TRIAL is refused,
 *     so the user can still buy an NFC card.
 *   - Email is a hard, permanent rule (normalised hash in free_trial_claims).
 *   - Device fingerprint and IP are additional rules with configurable limits;
 *     they are deliberately tunable because shared NAT (office, campus, mobile)
 *     would otherwise produce false positives.
 */
class FreeTrialGuard
{
    public function __construct(private EmailNormalizer $normalizer)
    {
    }

    /**
     * Decide whether this user/request may start a free trial.
     *
     * @return array{allowed: bool, code?: string, message?: string}
     */
    public function evaluate(Request $request, User $user): array
    {
        $abuse = (array) config('payment.subscriptions.abuse', []);

        if (empty($abuse['enabled'])) {
            return ['allowed' => true];
        }

        // (1) Verified email required — a throwaway address may register, but it
        //     cannot claim the trial until it is confirmed.
        if (!empty($abuse['require_email_verification']) && !$user->hasVerifiedEmail()) {
            return [
                'allowed' => false,
                'code' => 'EMAIL_NOT_VERIFIED',
                'message' => 'Please verify your email address before starting your free trial. Check your inbox for the verification link.',
            ];
        }

        // (2) Disposable / temp-mail domains are never eligible.
        if ($this->normalizer->isDisposable((string) $user->email)) {
            return [
                'allowed' => false,
                'code' => 'DISPOSABLE_EMAIL',
                'message' => 'Disposable email addresses are not eligible for the free trial. Please use a permanent email or purchase an NFC card.',
            ];
        }

        // (3) One trial per EMAIL — permanent, survives account deletion.
        $emailHash = $this->normalizer->hash((string) $user->email);
        $maxEmail = (int) ($abuse['max_claims_per_email'] ?? 1);
        if ($maxEmail > 0 && FreeTrialClaim::where('email_hash', $emailHash)->count() >= $maxEmail) {
            return [
                'allowed' => false,
                'code' => 'TRIAL_EMAIL_USED',
                'message' => 'This email has already used its free trial. Please purchase an NFC card to continue.',
            ];
        }

        // (4) One trial per DEVICE fingerprint.
        $deviceId = $this->deviceId($request);
        $maxDevice = (int) ($abuse['max_claims_per_device'] ?? 1);
        if ($deviceId && $maxDevice > 0 && FreeTrialClaim::where('device_id', $deviceId)->count() >= $maxDevice) {
            return [
                'allowed' => false,
                'code' => 'TRIAL_DEVICE_USED',
                'message' => 'A free trial has already been used on this device. Please purchase an NFC card to continue.',
            ];
        }

        // (5) One trial per IP network (configurable; 0 disables).
        $ip = $request->ip();
        $maxIp = (int) ($abuse['max_claims_per_ip'] ?? 1);
        if ($ip && $maxIp > 0 && FreeTrialClaim::where('ip_address', $ip)->count() >= $maxIp) {
            return [
                'allowed' => false,
                'code' => 'TRIAL_IP_USED',
                'message' => 'A free trial has already been used from this network. Please purchase an NFC card to continue.',
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Record that the trial was claimed. Call ONLY after a successful grant.
     */
    public function claim(Request $request, User $user): FreeTrialClaim
    {
        $emailHash = $this->normalizer->hash((string) $user->email);

        // firstOrCreate so two concurrent requests cannot create two rows for
        // the same email (the email rule is the hard one).
        return FreeTrialClaim::firstOrCreate(
            ['email_hash' => $emailHash],
            [
                'user_id' => $user->id,
                'email' => $user->email,
                'email_normalized' => $this->normalizer->normalize((string) $user->email),
                'ip_address' => $request->ip(),
                'ip_subnet' => $this->subnet($request->ip()),
                'device_id' => $this->deviceId($request),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000) ?: null,
                'claimed_at' => now(),
            ]
        );
    }

    /**
     * Browser-generated fingerprint sent as the X-Device-Id header.
     */
    private function deviceId(Request $request): ?string
    {
        $id = $request->header('X-Device-Id');

        if (!is_string($id) || $id === '') {
            return null;
        }

        $id = preg_replace('/[^A-Za-z0-9\-_]/', '', $id);

        return $id === '' ? null : substr($id, 0, 64);
    }

    /**
     * IPv4 /24 (or the full IPv6 address) describing the claiming network.
     * Stored for audit — refusal uses the exact IP.
     */
    private function subnet(?string $ip): ?string
    {
        if (!$ip) {
            return null;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $prefix = max(1, min(32, (int) config('payment.subscriptions.abuse.ip_subnet_prefix_v4', 24)));
            $octets = array_slice(explode('.', $ip), 0, (int) ceil($prefix / 8));

            return implode('.', $octets) . '/' . $prefix;
        }

        return $ip;
    }
}
