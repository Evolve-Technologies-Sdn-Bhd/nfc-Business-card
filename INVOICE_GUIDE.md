# Invoice System - Complete Guide

## 📋 Table of Contents
1. [Quick Start](#quick-start)
2. [System Overview](#system-overview)
3. [Installation & Configuration](#installation--configuration)
4. [Payment to Invoice Flow](#payment-to-invoice-flow)
5. [API Reference](#api-reference)
6. [Customization](#customization)
7. [Troubleshooting](#troubleshooting)
8. [Implementation Details](#implementation-details)

---

## 🚀 Quick Start

### Installation Complete!

The invoice system is fully integrated into your NFC Business Card application. Follow these steps to get started:

### 1. Update Environment Variables

Add these to your `backend/.env` file:

```env
# Company Information (Required)
COMPANY_NAME="Your Company Name"
COMPANY_LOGO_URL="https://yourdomain.com/logo.png"
COMPANY_ADDRESS="123 Business Street"
COMPANY_CITY="Kuala Lumpur"
COMPANY_STATE="Federal Territory"
COMPANY_POSTAL_CODE="50000"
COMPANY_COUNTRY="Malaysia"
COMPANY_PHONE="+60 12-345-6789"
COMPANY_EMAIL="billing@yourcompany.com"
COMPANY_TAX_ID="TAX123456789"
COMPANY_WEBSITE="https://yourcompany.com"

# Invoice Settings
INVOICE_PAYMENT_DUE_DAYS=30
INVOICE_TAX_RATE=0  # Set to 6 for 6% SST, 0 for no tax
INVOICE_CURRENCY="MYR"
INVOICE_NUMBER_PREFIX="INV"

# Email Settings
INVOICE_EMAIL_ENABLED=true
INVOICE_EMAIL_FROM_ADDRESS="billing@yourcompany.com"
INVOICE_EMAIL_FROM_NAME="Billing Department"

# Security & Storage
INVOICE_SIGNED_URL_EXPIRATION=60  # Minutes
INVOICE_STORAGE_DISK="local"  # Change to "s3" for production
INVOICE_STORAGE_PATH="invoices"

# Queue Settings
INVOICE_QUEUE_ENABLED=true
INVOICE_QUEUE_NAME="invoices"
INVOICE_QUEUE_CONNECTION="database"
```

### 2. Run Queue Worker

Start the queue worker to process invoice generation jobs:

```bash
cd backend
php artisan queue:work --queue=invoices
```

For production, set up Supervisor:

```ini
[program:invoice-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/backend/artisan queue:work --queue=invoices --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
stdout_logfile=/path/to/backend/storage/logs/worker.log
```

### 3. Test Invoice Generation

```bash
php artisan tinker
```

```php
// Get a completed transaction
$transaction = \App\Models\Transaction::where('status', 'succeeded')->first();

// Generate invoice
$invoiceService = app(\App\Services\InvoiceService::class);
$invoice = $invoiceService->generateInvoiceFromTransaction($transaction);

// View invoice
echo $invoice->invoice_number; // INV-2025-00001
echo $invoice->total_amount;
echo $invoice->pdf_path;
```

---

## 🎯 System Overview

### Automatic Invoice Generation

When a payment is completed:

1. **Payment succeeds** → Webhook received (Stripe, Billplz, Manual Bank Transfer)
2. **Transaction updated** → Status changed to "succeeded"
3. **Job dispatched** → `GenerateInvoiceJob` added to queue
4. **Invoice created** → PDF generated and saved to storage
5. **Email sent** → Customer receives invoice with PDF attachment
6. **Status updated** → Invoice marked as "issued"

### Components

- **Database**: `invoices` table with 30+ fields
- **Model**: `Invoice.php` with relationships and scopes
- **Service**: `InvoiceService.php` for business logic
- **Job**: `GenerateInvoiceJob.php` for async processing
- **PDF Template**: `pdf.blade.php` for invoice layout
- **Notification**: `InvoiceGenerated.php` for email delivery
- **Controllers**: User and Admin endpoints

---

## 📦 Installation & Configuration

### Database Schema

The `invoices` table includes:

- **Versioning**: `parent_invoice_id`, `version`
- **Status Workflow**: draft → issued → paid/cancelled/refunded
- **Line Items**: JSON field for flexible item structure
- **Company Details**: JSON field for sender information
- **Billing Details**: JSON field for customer information
- **Metadata**: JSON field for additional data
- **Audit Trail**: issued_by, cancelled_by, timestamps
- **Indexes**: Optimized for performance

### Configuration File

All settings in `config/invoice.php`:

```php
return [
    'company' => [
        'name' => env('COMPANY_NAME'),
        'logo_url' => env('COMPANY_LOGO_URL'),
        'address' => env('COMPANY_ADDRESS'),
        // ... more company details
    ],
    
    'defaults' => [
        'payment_due_days' => env('INVOICE_PAYMENT_DUE_DAYS', 30),
        'tax_rate' => env('INVOICE_TAX_RATE', 0),
        'currency' => env('INVOICE_CURRENCY', 'MYR'),
        // ... more defaults
    ],
    
    'pdf' => [
        'paper_size' => 'a4',
        'orientation' => 'portrait',
        // ... PDF settings
    ],
    
    // ... more configuration
];
```

---

## 🔄 Payment to Invoice Flow

### ✅ Critical Fixes Applied

**Issue #1: Status Mismatch**
- **Problem**: Job checked for 'completed' but service sets 'succeeded'
- **Fix**: Changed to check for 'succeeded' status
- **Impact**: Invoices now generate correctly

**Issue #2: Missing paid_at Timestamp**
- **Problem**: Stripe webhook didn't set payment date
- **Fix**: Added `'paid_at' => now()` to webhook handler
- **Impact**: Invoices show correct payment date

**Issue #3: Manual Bank Transfer Gap**
- **Problem**: Manual verification didn't trigger invoice
- **Fix**: Added job dispatch to verification method
- **Impact**: Manual payments now receive invoices

### 1. Card Payment (Stripe)

```
Customer pays
  ↓
PaymentService.processCardPayment()
  ↓
Transaction created (status='pending')
  ↓
Stripe webhook: payment_intent.succeeded
  ↓
handlePaymentIntentSucceeded() updates:
  - status = 'succeeded'
  - paid_at = now()
  ↓
GenerateInvoiceJob dispatched
  ↓
Invoice created with PDF
  ↓
Email sent with PDF attachment + download link
```

### 2. E-Wallet Payment (Billplz)

```
Customer pays via TnG/GrabPay/Boost/ShopeePay
  ↓
PaymentService.processEWalletPayment()
  ↓
Transaction created (status='pending')
  ↓
Billplz webhook: bill.paid
  ↓
handleBillplzWebhook() updates:
  - status = 'succeeded'
  - paid_at = now()
  ↓
GenerateInvoiceJob dispatched
  ↓
[Same as Card Payment]
```

### 3. Manual Bank Transfer

```
Customer uploads proof
  ↓
Transaction created (status='pending')
  ↓
Admin reviews in dashboard
  ↓
Admin approves
  ↓
verifyManualBankTransfer() updates:
  - status = 'succeeded'
  - paid_at = now()
  - verified_by = admin_id
  ↓
GenerateInvoiceJob dispatched
  ↓
[Same as Card Payment]
```

---

## 📡 API Reference

### User Endpoints

```
GET    /api/invoices                      List user's invoices
GET    /api/invoices/statistics           User invoice statistics
GET    /api/invoices/{invoice}            View invoice details
GET    /api/invoices/{invoice}/preview    Preview PDF in browser
GET    /api/invoices/{invoice}/download   Download PDF (signed URL)
```

**Example Response:**

```json
{
  "success": true,
  "data": {
    "invoices": [
      {
        "id": 1,
        "invoice_number": "INV-2025-00001",
        "total_amount": 50.00,
        "status": "paid",
        "issued_at": "2025-11-14T10:30:00Z",
        "pdf_path": "invoices/INV-2025-00001.pdf",
        "download_url": "https://..."
      }
    ],
    "pagination": {
      "current_page": 1,
      "total": 10
    }
  }
}
```

### Admin Endpoints

```
GET    /api/admin/invoices                           List all invoices
GET    /api/admin/invoices/statistics                System statistics
GET    /api/admin/invoices/{invoice}                 View invoice
POST   /api/admin/invoices/{invoice}/regenerate      Create new version
POST   /api/admin/invoices/{invoice}/mark-issued     Mark as issued
POST   /api/admin/invoices/{invoice}/mark-paid       Mark as paid
POST   /api/admin/invoices/{invoice}/cancel          Cancel invoice
POST   /api/admin/invoices/{invoice}/resend          Resend email
GET    /api/admin/invoices/{invoice}/download        Download PDF
GET    /api/admin/invoices/{invoice}/preview         Preview PDF
```

**Admin Statistics Response:**

```json
{
  "total_invoices": 1250,
  "total_revenue": 125000.00,
  "pending_amount": 5000.00,
  "issued_count": 1200,
  "paid_count": 1150,
  "cancelled_count": 50
}
```

---

## 🎨 Customization

### PDF Template

Edit `backend/resources/views/invoices/pdf.blade.php` to customize:

- Company branding
- Layout and styling
- Colors and fonts
- Additional fields
- Terms and conditions

### Invoice Numbering

Default format: `INV-2025-00001`

Customize in `config/invoice.php`:

```php
'number_format' => [
    'prefix' => env('INVOICE_NUMBER_PREFIX', 'INV'),
    'separator' => '-',
    'date_format' => 'Y',  // Year
    'padding' => 5,        // Number padding (00001)
],
```

### Email Template

Customize notification in `app/Notifications/InvoiceGenerated.php`:

```php
public function toMail($notifiable)
{
    return (new MailMessage)
        ->subject('Invoice ' . $this->invoice->invoice_number)
        ->greeting('Dear ' . $notifiable->name)
        ->line('Your invoice is ready.')
        ->attach($this->invoice->pdf_path)
        ->action('Download Invoice', $this->invoice->getSignedDownloadUrl());
}
```

---

## 🔧 Troubleshooting

### Invoice Not Generating

**Check queue worker:**
```bash
php artisan queue:work --queue=invoices
```

**Check failed jobs:**
```bash
php artisan queue:failed
```

**Check transaction status:**
```sql
SELECT id, status, paid_at FROM transactions WHERE id = ?;
```

**Common issues:**
- Queue worker not running
- Transaction status not 'succeeded'
- Missing paid_at timestamp
- Invalid transaction data

### PDF Generation Fails

**Check DomPDF installation:**
```bash
composer show barryvdh/laravel-dompdf
```

**Check storage permissions:**
```bash
chmod -R 775 storage/app/invoices
```

**Check template syntax:**
- Verify Blade syntax is correct
- Check for missing variables
- Test with simple template first

### Email Not Sending

**Check mail configuration:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
```

**Check queue:**
```bash
php artisan queue:work
```

**Check logs:**
```bash
tail -f storage/logs/laravel.log
```

---

## 🏗️ Implementation Details

### Database Layer

**Migration**: `2025_11_14_064417_create_invoices_table.php`
- ✅ 30+ fields for complete invoice data
- ✅ Versioning system (parent_invoice_id, version)
- ✅ Status workflow tracking
- ✅ JSON fields for flexibility
- ✅ Full audit trail
- ✅ Performance indexes

### Model Layer

**File**: `app/Models/Invoice.php` (200+ lines)

**Relationships:**
- belongsTo: user, transaction, issuedBy, cancelledBy, parentInvoice
- hasMany: regeneratedVersions (child invoices)

**Scopes:**
- issued(), paid(), cancelled(), forUser(), latestVersions()

**Methods:**
- markAsIssued(), markAsPaid(), markAsCancelled()
- generateInvoiceNumber()
- getSignedDownloadUrl()
- isEditable(), isCancellable()

### Service Layer

**File**: `app/Services/InvoiceService.php` (280+ lines)

**Key Methods:**
- `generateInvoiceFromTransaction()` - Main entry point
- `generatePDF()` - Creates PDF using DomPDF
- `sendInvoiceEmail()` - Triggers email notification
- `markAsIssued()` - Updates status and sends email
- `regenerateInvoice()` - Creates new version
- `downloadInvoice()` - Returns PDF download
- `previewInvoice()` - Returns PDF for browser

### Queue Job

**File**: `app/Jobs/GenerateInvoiceJob.php`

**Features:**
- Queued processing for async operation
- Eligibility checks before generation
- 3 retry attempts with exponential backoff
- Comprehensive error logging
- Failed job notifications

### PDF Generation

**Package**: barryvdh/laravel-dompdf v3.1.4

**Template**: `resources/views/invoices/pdf.blade.php`
- Professional A4 layout
- Company branding with logo
- Customer billing details
- Line items table with calculations
- Subtotal, tax, discount, total
- Payment status and terms
- Version indicator
- Responsive design

### Notification System

**File**: `app/Notifications/InvoiceGenerated.php`

**Features:**
- Queued for async sending
- Professional email template
- PDF attachment (optional)
- Signed download link (60 min expiration)
- Database notification for in-app display

---

## 📊 Invoice Email Contents

**Subject:** Invoice {invoice_number} from {company_name}

**Body Includes:**
- Greeting with customer name
- Invoice number
- Amount (formatted with currency)
- Payment status
- Due date (if applicable)
- Download button with signed URL
- PDF attachment (if enabled)

**Security:**
- Signed URLs expire after 60 minutes
- One-time use download links
- Email verification required

---

## ✅ Success Checklist

- [ ] Environment variables configured
- [ ] Queue worker running
- [ ] Mail settings configured
- [ ] Test invoice generated successfully
- [ ] Email received with PDF
- [ ] Download link works
- [ ] Admin can view all invoices
- [ ] User can view their invoices
- [ ] PDF template customized
- [ ] Company branding applied

---

## 📚 Related Documentation

- Laravel Queues: https://laravel.com/docs/queues
- DomPDF: https://github.com/barryvdh/laravel-dompdf
- Blade Templates: https://laravel.com/docs/blade
- Laravel Notifications: https://laravel.com/docs/notifications

---

**Last Updated**: November 17, 2025  
**Status**: ✅ Fully Implemented and Tested
