# Fiuu Payment Integration - Complete System

**Status:** ✅ **COMPLETE** - All files created and ready for testing

## 📁 Directory Structure

```
backend/public/
├── config/
│   └── fiuu-config.php          # Central configuration (credentials, URLs, settings)
├── includes/
│   ├── database.php             # PDO database connection & CRUD functions
│   └── payment-security.php     # Security functions (vcode/skey, validation)
├── payment/
│   ├── payment-form.php         # Customer entry point (creates order, redirects to Fiuu)
│   ├── return-url.php           # Customer-facing result page (NO DB updates)
│   ├── notification-url.php     # PRIMARY webhook (server-to-server, ALL DB updates)
│   ├── callback-url.php         # Delayed payment notification (cash channels)
│   └── cancel.php               # Payment cancellation page
├── admin/
│   └── check-status.php         # Admin tool to query Fiuu API
├── logs/
│   └── .htaccess                # Block public access to logs
├── pricing.php                  # Pricing page
└── database.sql                 # Database schema

```

---

## 🔧 Setup Instructions

### 1. Database Setup

Run the SQL schema to create required tables:

```bash
mysql -u root -p nfc_business_card < backend/public/database.sql
```

**Tables Created:**
- `orders` - Customer orders and payment status
- `payment_transactions` - Log of all Fiuu webhooks
- `payment_logs` - Activity log for debugging

### 2. Configuration

**File:** `backend/public/config/fiuu-config.php`

✅ Already configured with your credentials:
- **Merchant ID:** `SB_evolvetechnology`
- **Verify Key:** `3d94ceb644a497522601465da218ec6b`
- **Secret Key:** `6f0c6cd63f23fad128b594b36bb9252c`
- **Environment:** Sandbox (for testing)

**Update for Production:**
1. Change `FIUU_ENVIRONMENT` to `'production'`
2. Replace sandbox credentials with live credentials
3. Update `BASE_URL` to your production domain

### 3. URL Configuration

**Current URLs (localhost):**
- Base: `http://localhost:8000`
- Return URL: `http://localhost:8000/payment/return-url.php`
- Notification URL: `http://localhost:8000/payment/notification-url.php`
- Callback URL: `http://localhost:8000/payment/callback-url.php`

**For Production:**
Update `BASE_URL` in `config/fiuu-config.php` to your domain.

### 4. Fiuu Merchant Portal Setup

1. Log in to [Fiuu Merchant Portal](https://merchant.fiuu.com)
2. Navigate to **Settings** → **Merchant Profile**
3. Configure these URLs:
   - **Return URL:** `https://yourdomain.com/payment/return-url.php`
   - **Notification URL:** `https://yourdomain.com/payment/notification-url.php`
   - **Callback URL:** `https://yourdomain.com/payment/callback-url.php`

---

## 🚀 Payment Flow

### Customer Journey

1. **Pricing Page** (`pricing.php`)
   - Customer selects a plan
   - Submits form to `payment-form.php`

2. **Payment Form** (`payment/payment-form.php`)
   - Creates order in database
   - Generates `vcode` signature
   - Auto-redirects customer to Fiuu payment gateway

3. **Fiuu Payment Gateway**
   - Customer selects payment method (FPX, card, e-wallet, etc.)
   - Completes payment

4. **Return URL** (`payment/return-url.php`)
   - Customer redirected here after payment
   - **Displays result ONLY** (no database updates)
   - Shows success/pending/failed status

5. **Notification URL** (`payment/notification-url.php`) ⭐ **MOST IMPORTANT**
   - Fiuu sends server-to-server webhook
   - **Performs ALL database updates**
   - Verifies signature (`skey`)
   - Updates order status
   - Logs transaction
   - Returns `CBTOKEN:MPSTATOK` to acknowledge

6. **Callback URL** (`payment/callback-url.php`)
   - For delayed payments (cash channels)
   - Same logic as notification URL

---

## 🔐 Security Features

### Signature Verification

**Outgoing (to Fiuu):**
```php
vcode = MD5(amount + merchant_id + order_id + verify_key)
```

**Incoming (from Fiuu):**
```php
skey = MD5(tran_id + order_id + status + domain + amount + currency + verify_key)
```

### Security Measures

✅ Signature verification on all webhooks
✅ Anti-duplicate webhook protection
✅ IP whitelisting (optional, configurable)
✅ Input sanitization
✅ SQL injection prevention (PDO prepared statements)
✅ Log file access blocking (.htaccess)
✅ Comprehensive logging for audit trail

---

## 📊 Database Schema

### `orders` Table
Stores customer orders and payment status.

**Key Fields:**
- `order_id` - Unique order identifier
- `user_id` - Foreign key to users (optional)
- `status` - pending, processing, completed, failed, cancelled
- `amount` - Payment amount
- `fiuu_tran_id` - Fiuu transaction ID
- `fiuu_channel` - Payment method used

### `payment_transactions` Table
Logs every webhook received from Fiuu.

**Purpose:** Audit trail, duplicate detection, debugging

### `payment_logs` Table
Activity log for payment events.

**Levels:** info, warning, error

---

## 🧪 Testing

### Local Testing

1. **Start PHP server:**
   ```bash
   cd backend/public
   php -S localhost:8000
   ```

2. **Access pricing page:**
   ```
   http://localhost:8000/pricing.php
   ```

3. **Test payment flow:**
   - Select a plan
   - Fill in customer details
   - Redirects to Fiuu sandbox

### Sandbox Test Cards

**Fiuu provides test credentials for sandbox:**
- **FPX:** Use test bank credentials from Fiuu docs
- **Credit Card:** Test card numbers available in Fiuu merchant portal

### Testing Webhooks Locally

⚠️ **Problem:** Fiuu cannot reach `localhost`

**Solutions:**
1. **ngrok** (recommended for testing):
   ```bash
   ngrok http 8000
   ```
   Use ngrok URL in Fiuu merchant portal

2. **Deploy to test server:** Use a public staging environment

---

## 🛠️ Admin Tools

### Check Payment Status

**File:** `admin/check-status.php`

**Features:**
- Query Fiuu API for order status
- Compare with local database
- View full transaction details

**Access:**
```
http://localhost:8000/admin/check-status.php
```

**⚠️ Security Note:**
Currently set to allow all access for testing.
**Remove this line in production:**
```php
$isAdmin = true; // REMOVE THIS IN PRODUCTION
```

Implement proper admin authentication!

---

## 📝 Logging

### Log Files Location

```
backend/public/logs/
├── payment-YYYY-MM-DD.log      # Payment events
├── error-YYYY-MM-DD.log        # Errors only
└── .htaccess                   # Blocks public access
```

### Log Levels

- **INFO:** Normal operations (order created, payment successful)
- **WARNING:** Non-critical issues (duplicate webhook, signature warning)
- **ERROR:** Critical issues (DB errors, signature failure)

### Viewing Logs

```bash
# Today's payment log
tail -f backend/public/logs/payment-2025-01-06.log

# Today's errors
tail -f backend/public/logs/error-2025-01-06.log
```

---

## 🎯 File Responsibilities

### Separation of Concerns

| Directory | Purpose | Updates DB? |
|-----------|---------|-------------|
| `config/` | Configuration only | ❌ |
| `includes/` | Reusable functions | ❌ |
| `payment/` | Customer flow & webhooks | ✅ (webhooks only) |
| `admin/` | Admin tools | ❌ (read-only) |
| `logs/` | Log files | N/A |

### Critical Files

1. **notification-url.php** ⭐
   - PRIMARY webhook
   - ALL database updates happen here
   - Must return `CBTOKEN:MPSTATOK`

2. **payment-security.php**
   - All signature verification
   - Input validation
   - Logging functions

3. **database.php**
   - All database operations
   - PDO connection
   - CRUD functions

---

## 🚨 Common Issues & Solutions

### Issue 1: Fiuu Not Sending Webhooks

**Solution:**
- Check Fiuu merchant portal → notification URL is correct
- Ensure URL is publicly accessible (not localhost)
- Check Fiuu merchant logs for webhook delivery status

### Issue 2: Signature Verification Fails

**Solution:**
- Verify credentials in `fiuu-config.php` match merchant portal
- Check signature calculation (case-sensitive)
- Log both expected and received signatures for debugging

### Issue 3: Orders Not Updating

**Solution:**
- Check `notification-url.php` is receiving webhooks (check logs)
- Verify database connection in `includes/database.php`
- Check MySQL credentials in `fiuu-config.php`

### Issue 4: Duplicate Webhooks

**Expected behavior:** Fiuu sends webhooks 2-3 times for redundancy

**Solution:**
- `checkDuplicateWebhook()` function handles this
- Still returns `CBTOKEN:MPSTATOK` to acknowledge

---

## 📚 API Reference

### Fiuu Status Codes

| Code | Meaning | System Status |
|------|---------|---------------|
| `00` | Success | `completed` |
| `11` | Failed | `failed` |
| `22` | Pending | `pending` |
| `33` | Processing | `processing` |

### IPN Acknowledgment Tokens

| Token | Meaning |
|-------|---------|
| `CBTOKEN:MPSTATOK` | Success - webhook processed |
| `CBTOKEN:MPSUNK` | Unknown - processing failed |

---

## 🔄 Migration from Laravel

Your previous Laravel-based webhooks have been replaced with standalone PHP.

**Key Changes:**
- ❌ No Laravel dependencies
- ✅ Pure PHP with PDO
- ✅ Modular architecture
- ✅ Separation of concerns

**Old files replaced:**
- `backend/public/payment/return-url.php`
- `backend/public/payment/notification-url.php`
- `backend/public/payment/callback-url.php`

---

## 📞 Support

**Fiuu Support:**
- Email: support@fiuu.com
- Merchant Portal: https://merchant.fiuu.com
- Documentation: https://fiuu.com/developer

**System Logs:**
- Check `backend/public/logs/` for debugging
- All webhook data logged with full context

---

## ✅ Pre-Launch Checklist

Before going live:

- [ ] Update `FIUU_ENVIRONMENT` to `'production'`
- [ ] Replace sandbox credentials with live credentials
- [ ] Update `BASE_URL` to production domain
- [ ] Configure webhook URLs in Fiuu merchant portal
- [ ] Test complete payment flow on staging
- [ ] Implement proper admin authentication
- [ ] Enable IP whitelisting (set `ENABLE_IP_WHITELIST` to `true`)
- [ ] Set up log rotation/cleanup
- [ ] Configure email notifications for completed payments
- [ ] Test error scenarios (failed payments, network issues)
- [ ] Review database indexes for performance
- [ ] Set up monitoring/alerts for critical errors

---

## 🎉 System Complete!

All 13 files have been created following your exact specifications:

✅ Modular architecture
✅ Separation of concerns
✅ Comprehensive security
✅ Detailed logging
✅ Complete documentation

**Next Steps:**
1. Run `database.sql` to create tables
2. Start PHP server and test locally
3. Configure Fiuu merchant portal with your URLs
4. Test with sandbox credentials
5. Go live! 🚀
