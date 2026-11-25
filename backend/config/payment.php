<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Configuration
    |--------------------------------------------------------------------------
    |
    | Configure payment providers and rails for the application.
    | Supports Cards, Bank Payments (FPX/DuitNow), and E-wallets.
    |
    */

    'default_provider' => env('PAYMENT_DEFAULT_PROVIDER', 'fiuu'),

    'currency' => env('PAYMENT_CURRENCY', 'MYR'),

    'providers' => [

        /*
        |--------------------------------------------------------------------------
        | Stripe Configuration (International Cards + 3DS)
        |--------------------------------------------------------------------------
        */
        'stripe' => [
            'enabled' => env('STRIPE_ENABLED', true),
            'secret_key' => env('STRIPE_SECRET_KEY'),
            'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
            'api_version' => '2024-12-18.acacia',

            'supports' => [
                'cards' => true,
                'fpx' => false,
                'duitnow' => false,
                'ewallet' => false,
            ],

            'card_brands' => ['visa', 'mastercard', 'amex'],
            'require_3ds' => true,
            'save_cards' => true,
        ],

        /*
        |--------------------------------------------------------------------------
        | Billplz Configuration (Malaysian Payment Gateway)
        |--------------------------------------------------------------------------
        | Supports: FPX, DuitNow Online Banking, E-wallets
        */
        'billplz' => [
            'enabled' => env('BILLPLZ_ENABLED', false),
            'api_key' => env('BILLPLZ_API_KEY'),
            'collection_id' => env('BILLPLZ_COLLECTION_ID'),
            'x_signature_key' => env('BILLPLZ_X_SIGNATURE_KEY'),
            'sandbox' => env('BILLPLZ_SANDBOX', true),

            'supports' => [
                'cards' => false,
                'fpx' => true,
                'duitnow' => true,
                'ewallet' => true,
            ],

            'ewallets' => ['tng', 'grabpay', 'boost', 'shopeepay'],
        ],

        /*
        |--------------------------------------------------------------------------
        | Senangpay Configuration (Alternative Malaysian Gateway)
        |--------------------------------------------------------------------------
        */
        'senangpay' => [
            'enabled' => env('SENANGPAY_ENABLED', false),
            'merchant_id' => env('SENANGPAY_MERCHANT_ID'),
            'secret_key' => env('SENANGPAY_SECRET_KEY'),
            'sandbox' => env('SENANGPAY_SANDBOX', true),

            'supports' => [
                'cards' => true,
                'fpx' => true,
                'duitnow' => false,
                'ewallet' => true,
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Xendit Configuration (SEA Regional Gateway)
        |--------------------------------------------------------------------------
        */
        'xendit' => [
            'enabled' => env('XENDIT_ENABLED', false),
            'secret_key' => env('XENDIT_SECRET_KEY'),
            'public_key' => env('XENDIT_PUBLIC_KEY'),
            'webhook_token' => env('XENDIT_WEBHOOK_TOKEN'),

            'supports' => [
                'cards' => true,
                'fpx' => false,
                'duitnow' => false,
                'ewallet' => true,
            ],

            'ewallets' => ['grabpay', 'shopeepay'],
        ],

        /*
        |--------------------------------------------------------------------------
        | Fiuu (MOLPay) Configuration (Malaysian Payment Gateway)
        |--------------------------------------------------------------------------
        | Supports: FPX, Cards, E-wallets, DuitNow
        | Webhook URLs: return-url, notification-url, callback-url
        */
        'fiuu' => [
            'enabled' => env('FIUU_ENABLED', false),
            'merchant_id' => env('FIUU_MERCHANT_ID'),
            'verify_key' => env('FIUU_VERIFY_KEY'),
            'secret_key' => env('FIUU_SECRET_KEY'),
            'sandbox' => env('FIUU_SANDBOX', true),
            'api_url' => env('FIUU_SANDBOX', true)
                ? 'https://sandbox.merchant.razer.com/RMS/API/Direct/1.0.0/'
                : 'https://payment.ipay88.com.my/epayment/entry.asp',

            'supports' => [
                'cards' => true,
                'fpx' => true,
                'duitnow' => true,
                'ewallet' => true,
            ],

            'payment_channels' => [
                'credit' => 'Credit Card (MasterCard/Visa)',
                'fpx' => 'FPX Online Banking',
                'fpx_b2b' => 'FPX B2B',
                'tng' => 'Touch n Go eWallet',
                'grabpay' => 'GrabPay',
                'boost' => 'Boost',
                'shopeepay' => 'ShopeePay',
                'maybank_qr' => 'Maybank QRPay',
            ],

            // Webhook URLs (configured for your environment)
            'return_url' => env('FIUU_RETURN_URL', 'http://localhost:8000/payment/return-url.php'),
            'notification_url' => env('FIUU_NOTIFICATION_URL', 'http://localhost:8000/payment/notification-url.php'),
            'callback_url' => env('FIUU_CALLBACK_URL', 'http://localhost:8000/payment/callback-url.php'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Rails Configuration
    |--------------------------------------------------------------------------
    */
    'rails' => [

        'card' => [
            'enabled' => true,
            'providers' => ['fiuu', 'stripe', 'senangpay', 'xendit'],
            'min_amount' => 1.00,
            'max_amount' => 50000.00,
            'fee_percentage' => 2.9,
            'fee_fixed' => 0.30,
        ],

        'fpx' => [
            'enabled' => true,
            'providers' => ['fiuu', 'billplz', 'senangpay'],
            'min_amount' => 1.00,
            'max_amount' => 30000.00,
            'fee_percentage' => 1.5,
            'fee_fixed' => 0.00,
            'banks' => [
                'maybank2u' => 'Maybank2u',
                'cimb' => 'CIMB Clicks',
                'rhb' => 'RHB Now',
                'pbb' => 'Public Bank',
                'ambank' => 'AmBank',
                'hlb' => 'Hong Leong Bank',
                'affinbank' => 'Affin Bank',
                'alliance' => 'Alliance Bank',
                'bankislam' => 'Bank Islam',
                'bankrakyat' => 'Bank Rakyat',
                'bsn' => 'BSN',
                'hsbc' => 'HSBC',
                'kfh' => 'KFH',
                'ocbc' => 'OCBC',
                'sc' => 'Standard Chartered',
                'uob' => 'UOB',
            ],
        ],

        'duitnow' => [
            'enabled' => true,
            'providers' => ['fiuu', 'billplz'],
            'min_amount' => 1.00,
            'max_amount' => 50000.00,
            'fee_percentage' => 0.5,
            'fee_fixed' => 0.00,
        ],

        'ewallet' => [
            'enabled' => true,
            'providers' => ['fiuu', 'billplz', 'senangpay', 'xendit'],
            'min_amount' => 1.00,
            'max_amount' => 10000.00,
            'fee_percentage' => 2.0,
            'fee_fixed' => 0.00,
            'wallets' => [
                'tng' => [
                    'name' => 'Touch \'n Go',
                    'logo' => '/images/wallets/tng.png',
                    'providers' => ['billplz', 'senangpay'],
                ],
                'grabpay' => [
                    'name' => 'GrabPay',
                    'logo' => '/images/wallets/grabpay.png',
                    'providers' => ['billplz', 'xendit'],
                ],
                'boost' => [
                    'name' => 'Boost',
                    'logo' => '/images/wallets/boost.png',
                    'providers' => ['billplz', 'senangpay'],
                ],
                'shopeepay' => [
                    'name' => 'ShopeePay',
                    'logo' => '/images/wallets/shopeepay.png',
                    'providers' => ['billplz', 'xendit'],
                ],
            ],
        ],

        'manual_bank' => [
            'enabled' => true,
            'accounts' => [
                [
                    'bank_name' => 'Maybank',
                    'account_name' => env('BANK_ACCOUNT_NAME', 'NFC Business Card Sdn Bhd'),
                    'account_number' => env('MAYBANK_ACCOUNT_NUMBER'),
                    'swift' => 'MBBEMYKL',
                ],
                [
                    'bank_name' => 'CIMB Bank',
                    'account_name' => env('BANK_ACCOUNT_NAME', 'NFC Business Card Sdn Bhd'),
                    'account_number' => env('CIMB_ACCOUNT_NUMBER'),
                    'swift' => 'CIBBMYKL',
                ],
            ],
            'verification_required' => true,
            'auto_expire_hours' => 72, // Expire pending after 3 days
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Checkout / Physical NFC Card Pricing
    |--------------------------------------------------------------------------
    | Single source of truth for the physical card unit price and the flat
    | shipping fee applied at checkout. The checkout page reads these via the
    | public GET /payment/config endpoint instead of hard-coding the amounts in
    | the Vue component, so pricing changes no longer require a frontend deploy.
    */
    'checkout' => [
        'card_unit_price' => (float) env('CHECKOUT_CARD_UNIT_PRICE', 15.00),
        'shipping_fee' => (float) env('CHECKOUT_SHIPPING_FEE', 5.00),
        'max_card_quantity' => (int) env('CHECKOUT_MAX_CARD_QUANTITY', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscription & Recurring Payments
    |--------------------------------------------------------------------------
    */
    'subscriptions' => [
        'enabled' => true,
        'provider' => 'fiuu', // Primary provider for subscriptions (Fiuu / MOLPay)

        'plans' => [
            'basic' => [
                'monthly' => 29.90,
                'yearly' => 299.00,
            ],
            'business' => [
                'monthly' => 79.90,
                'yearly' => 799.00,
            ],
            'premium' => [
                'monthly' => 149.90,
                'yearly' => 1499.00,
            ],
        ],

        'trial_days' => 14,

        /*
        |----------------------------------------------------------------------
        | Free-trial abuse prevention (one-time trial)
        |----------------------------------------------------------------------
        | A free account is granted ONCE per person. The check is enforced at
        | OnboardingController::selectPlan('free'). It is NOT a hard account
        | block: the user may still register and buy a card, they just cannot
        | claim a second free trial.
        |
        | Signals, from strongest to weakest:
        |   email    — permanent ledger keyed by a NORMALISED email hash
        |              (gmail dots + "+tag" aliases are merged, disposable
        |              domains rejected). Survives account deletion.
        |   device   — browser fingerprint sent as X-Device-Id.
        |   ip       — flat shared NAT (office/campus/mobile CGNAT) is the usual
        |              false positive, so this threshold is configurable. Set to 0
        |              to disable IP-based refusal entirely.
        */
        'abuse' => [
            'enabled' => true,

            // Require a VERIFIED email before the trial is granted. This is
            // stricter than the app-wide grace period: a throwaway address can
            // register, but it cannot claim a free trial until it is confirmed.
            'require_email_verification' => true,

            'max_claims_per_email' => 1,
            'max_claims_per_device' => 1,
            'max_claims_per_ip' => 1,

            // IPv4 /24 prefix used when recording the network of a claim
            // (stored for audit; not used to refuse by default).
            'ip_subnet_prefix_v4' => 24,

            // Throwaway / temp-mail domains that are never eligible.
            'disposable_domains' => [
                'mailinator.com', 'guerrillamail.com', 'guerrillamail.net',
                'sharklasers.com', '10minutemail.com', '10minutemail.net',
                'tempmail.com', 'tempmail.net', 'temp-mail.org', 'tempmailo.com',
                'throwawaymail.com', 'yopmail.com', 'yopmail.fr', 'getnada.com',
                'nada.email', 'trashmail.com', 'trashmail.de', 'dispostable.com',
                'maildrop.cc', 'mailnesia.com', 'moakt.com', 'fakeinbox.com',
                'mintemail.com', 'spamgourmet.com', 'mytrashmail.com',
                'mailcatch.com', 'emailondeck.com', 'getairmail.com',
                'discard.email', 'throwam.com', 'tempr.email', 'tempinbox.com',
            ],
        ],

        // Dunning management
        'dunning' => [
            'enabled' => true,
            'max_attempts' => 4,
            'retry_schedule' => [
                1 => 3,  // Retry after 3 days
                2 => 5,  // Retry after 5 days
                3 => 7,  // Retry after 7 days
                4 => 14, // Final retry after 14 days
            ],
            'suspend_after_attempts' => 2,
            'cancel_after_attempts' => 4,
            'notify_user' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Refunds Configuration
    |--------------------------------------------------------------------------
    */
    'refunds' => [
        'enabled' => true,
        'auto_approval_limit' => 100.00, // Auto-approve refunds below this amount
        'manual_review_required' => 500.00, // Require manual review above this
        'reasons' => [
            'duplicate' => 'Duplicate Payment',
            'fraudulent' => 'Fraudulent Transaction',
            'requested' => 'Requested by Customer',
            'error' => 'Payment Error',
            'other' => 'Other',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    */
    'webhooks' => [
        'verify_signatures' => true,
        'log_all_events' => true,
        'retry_failed' => true,
        'max_retries' => 3,

        'events' => [
            'stripe' => [
                'payment_intent.succeeded',
                'payment_intent.payment_failed',
                'payment_intent.requires_action',
                'charge.refunded',
                'customer.subscription.created',
                'customer.subscription.updated',
                'customer.subscription.deleted',
                'invoice.payment_succeeded',
                'invoice.payment_failed',
            ],
            'billplz' => [
                'bill.paid',
                'bill.deleted',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security & Audit
    |--------------------------------------------------------------------------
    */
    'security' => [
        'log_all_transactions' => true,
        'require_authentication' => true,
        'require_https' => env('APP_ENV') === 'production',
        'audit_retention_days' => 365,
        'encrypt_sensitive_data' => true,
    ],

];
