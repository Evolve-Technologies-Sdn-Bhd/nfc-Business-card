<?php

namespace App\Services;

/**
 * Canonicalises an email address so alias abuse cannot farm extra free trials.
 *
 * Examples (all collapse to the SAME string):
 *   John.Smith+promo@gmail.com  -> johnsmith@gmail.com
 *   johnsmith@googlemail.com    -> johnsmith@gmail.com
 *   jane+1@outlook.com          -> jane@outlook.com
 */
class EmailNormalizer
{
    /** Providers that ignore dots in the local part (gmail/googlemail). */
    private const DOT_INSENSITIVE_DOMAINS = ['gmail.com', 'googlemail.com'];

    /**
     * Return the canonical form of an email address.
     */
    public function normalize(string $email): string
    {
        $email = strtolower(trim($email));

        if (!str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);

        // Strip the "+tag" suffix for EVERY provider — it only ever filters,
        // it is never a distinct mailbox.
        if (($plus = strpos($local, '+')) !== false) {
            $local = substr($local, 0, $plus);
        }

        // Gmail also ignores dots, so john.smith@gmail.com === johnsmith@gmail.com.
        if (in_array($domain, self::DOT_INSENSITIVE_DOMAINS, true)) {
            $local = str_replace('.', '', $local);
            $domain = 'gmail.com';
        }

        return $local . '@' . $domain;
    }

    /**
     * Stable, index-friendly hash of the normalised address.
     */
    public function hash(string $email): string
    {
        return hash('sha256', $this->normalize($email));
    }

    /**
     * True when the address belongs to a known throwaway/temp-mail provider.
     */
    public function isDisposable(string $email): bool
    {
        $at = strrchr($email, '@');
        if ($at === false) {
            return false;
        }

        $domain = strtolower(substr($at, 1));
        if ($domain === '') {
            return false;
        }

        foreach ((array) config('payment.subscriptions.abuse.disposable_domains', []) as $blocked) {
            $blocked = strtolower($blocked);
            if ($domain === $blocked || str_ends_with($domain, '.' . $blocked)) {
                return true;
            }
        }

        return false;
    }
}
