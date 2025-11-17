<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Company Information
    |--------------------------------------------------------------------------
    |
    | This information will appear on all invoices. Update these values
    | with your company's actual details.
    |
    */

    'company' => [
        'name' => env('COMPANY_NAME', config('app.name', 'Your Company Name')),
        'logo_url' => env('COMPANY_LOGO_URL', ''),
        'address' => env('COMPANY_ADDRESS', '123 Business Street'),
        'city' => env('COMPANY_CITY', 'Business City'),
        'state' => env('COMPANY_STATE', 'State'),
        'postal_code' => env('COMPANY_POSTAL_CODE', '12345'),
        'country' => env('COMPANY_COUNTRY', 'Country'),
        'phone' => env('COMPANY_PHONE', '+1 (555) 123-4567'),
        'email' => env('COMPANY_EMAIL', 'billing@company.com'),
        'tax_id' => env('COMPANY_TAX_ID', ''),
        'website' => env('COMPANY_WEBSITE', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Settings
    |--------------------------------------------------------------------------
    |
    | Configure default invoice behavior and settings.
    |
    */

    'defaults' => [
        // Number of days until payment is due
        'payment_due_days' => env('INVOICE_PAYMENT_DUE_DAYS', 30),

        // Default tax rate (percentage)
        'tax_rate' => env('INVOICE_TAX_RATE', 0),

        // Default currency code (ISO 4217)
        'currency' => env('INVOICE_CURRENCY', 'USD'),

        // Default terms and conditions
        'terms' => 'Payment is due within 30 days. Late payments may incur additional charges. All sales are final unless otherwise stated.',

        // Default notes
        'notes' => 'Thank you for your business. If you have any questions about this invoice, please contact our billing department.',

        // Invoice number prefix
        'number_prefix' => env('INVOICE_NUMBER_PREFIX', 'INV'),

        // Invoice number format: {prefix}-{year}-{number}
        'number_format' => '{prefix}-{year}-{number}',

        // Number padding (e.g., 5 = 00001)
        'number_padding' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | PDF Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for PDF generation using DomPDF.
    |
    */

    'pdf' => [
        // Paper size (A4, Letter, Legal, etc.)
        'paper' => env('INVOICE_PDF_PAPER', 'A4'),

        // Orientation (portrait or landscape)
        'orientation' => env('INVOICE_PDF_ORIENTATION', 'portrait'),

        // Enable/disable image embedding
        'enable_remote' => true,

        // Enable/disable HTML5 parser
        'enable_html5_parser' => true,

        // Default font
        'default_font' => 'DejaVu Sans',

        // DPI setting
        'dpi' => 96,

        // Enable/disable font subsetting
        'font_subsetting' => false,

        // HTTPS verification for remote files
        'enable_php' => false,

        // Chroot for security
        'chroot' => public_path(),
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    |
    | Configure where and how invoices are stored.
    |
    */

    'storage' => [
        // Storage disk for invoices
        'disk' => env('INVOICE_STORAGE_DISK', 'local'),

        // Base path for invoice storage
        'path' => env('INVOICE_STORAGE_PATH', 'invoices'),

        // File naming pattern: {invoice_number}-v{version}.pdf
        'filename_format' => '{invoice_number}-v{version}.pdf',

        // Maximum file size in bytes (10MB default)
        'max_size' => env('INVOICE_MAX_FILE_SIZE', 10485760),

        // Delete invoice files when invoice is deleted
        'auto_delete_files' => true,

        // Keep old versions when regenerating
        'keep_versions' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Email Settings
    |--------------------------------------------------------------------------
    |
    | Configure invoice email notifications.
    |
    */

    'email' => [
        // Enable/disable email notifications
        'enabled' => env('INVOICE_EMAIL_ENABLED', true),

        // From email address
        'from' => [
            'address' => env('INVOICE_EMAIL_FROM_ADDRESS', env('MAIL_FROM_ADDRESS', 'billing@company.com')),
            'name' => env('INVOICE_EMAIL_FROM_NAME', env('MAIL_FROM_NAME', 'Billing Department')),
        ],

        // Email subject line
        'subject' => 'Invoice {invoice_number} from {company_name}',

        // Attach PDF to email
        'attach_pdf' => true,

        // Include download link in email
        'include_download_link' => true,

        // CC addresses for invoice emails
        'cc' => env('INVOICE_EMAIL_CC', ''),

        // BCC addresses for invoice emails
        'bcc' => env('INVOICE_EMAIL_BCC', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | URL Settings
    |--------------------------------------------------------------------------
    |
    | Configure invoice download and preview URLs.
    |
    */

    'urls' => [
        // Signed URL expiration time (in minutes)
        'signed_url_expiration' => env('INVOICE_SIGNED_URL_EXPIRATION', 60),

        // Route names
        'download_route' => 'invoices.download',
        'preview_route' => 'invoices.preview',

        // Frontend invoice view URL
        'frontend_view_url' => env('FRONTEND_URL', 'http://localhost:3000') . '/invoices/{invoice_number}',
    ],

    /*
    |--------------------------------------------------------------------------
    | Versioning Settings
    |--------------------------------------------------------------------------
    |
    | Configure invoice versioning behavior.
    |
    */

    'versioning' => [
        // Enable invoice versioning
        'enabled' => true,

        // Maximum versions per invoice (0 = unlimited)
        'max_versions' => env('INVOICE_MAX_VERSIONS', 0),

        // Track who regenerated the invoice
        'track_regenerated_by' => true,

        // Store reason for regeneration in metadata
        'require_regeneration_reason' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Settings
    |--------------------------------------------------------------------------
    |
    | Configure audit trail and logging.
    |
    */

    'audit' => [
        // Track who issued the invoice
        'track_issued_by' => true,

        // Track who cancelled the invoice
        'track_cancelled_by' => true,

        // Require cancellation reason
        'require_cancellation_reason' => true,

        // Log invoice events
        'log_events' => env('INVOICE_LOG_EVENTS', true),

        // Log level for invoice events
        'log_level' => env('INVOICE_LOG_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Configure security features for invoices.
    |
    */

    'security' => [
        // Require authentication for invoice access
        'require_auth' => true,

        // Users can only view their own invoices
        'restrict_to_owner' => true,

        // Admin roles that can view all invoices
        'admin_roles' => ['admin', 'super_admin', 'billing_admin'],

        // Use signed URLs for downloads
        'use_signed_urls' => true,

        // IP whitelist for invoice access (empty = no restriction)
        'ip_whitelist' => [],

        // Rate limiting for invoice downloads
        'rate_limit' => [
            'enabled' => true,
            'max_attempts' => 10,
            'decay_minutes' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    |
    | Configure queue behavior for invoice generation.
    |
    */

    'queue' => [
        // Enable queueing for invoice generation
        'enabled' => env('INVOICE_QUEUE_ENABLED', true),

        // Queue name for invoice jobs
        'name' => env('INVOICE_QUEUE_NAME', 'invoices'),

        // Queue connection
        'connection' => env('INVOICE_QUEUE_CONNECTION', env('QUEUE_CONNECTION', 'database')),

        // Number of job retry attempts
        'retry_attempts' => 3,

        // Delay between retries (in seconds)
        'retry_delay' => 60,
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules
    |--------------------------------------------------------------------------
    |
    | Validation rules for invoice data.
    |
    */

    'validation' => [
        // Minimum invoice amount
        'min_amount' => 0,

        // Maximum invoice amount (0 = no limit)
        'max_amount' => 0,

        // Required line item fields
        'required_line_item_fields' => ['description', 'quantity', 'unit_price', 'amount'],

        // Allowed invoice statuses
        'allowed_statuses' => ['draft', 'issued', 'paid', 'cancelled', 'refunded'],

        // Allowed currencies (empty = all currencies)
        'allowed_currencies' => ['USD', 'EUR', 'GBP', 'MYR'],
    ],

];
