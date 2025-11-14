# E-Wallet Payment - Quick Start

## What Was Implemented

✅ **Complete e-wallet payment flow** for Touch 'n Go eWallet, GrabPay, Boost, and ShopeePay

### Key Features

1. **App Redirection**
   - Deep links open the wallet app directly (tngd://, grab://, boostapp://, shopeemy://)
   - Auto-detects mobile devices and redirects automatically
   - QR code fallback for desktop users

2. **Webhook Integration**
   - Billplz sends webhook when user approves payment in their app
   - Transaction status updated to 'succeeded' automatically
   - Signature verification for security

3. **Real-time Status Updates**
   - Frontend polls transaction status every 3 seconds
   - Shows success/pending/failed states
   - 10-minute payment timeout with countdown

## Files Modified/Created

### Backend
- ✅ `app/Services/PaymentService.php` - E-wallet processing + deep link generation + webhook handler
- ✅ `app/Http/Controllers/Api/WebhookController.php` - Already supports Billplz webhooks
- ✅ `env.production.template` - Added Billplz config
- ✅ `env.staging.template` - Added Billplz sandbox config
- ✅ `.env` - Already has Billplz config (set BILLPLZ_ENABLED=true to activate)

### Frontend
- ✅ `components/EWalletSelector.vue` - Updated to handle deep links and auto-redirect
- ✅ `pages/payment/status.vue` - NEW payment status page for redirects

### Documentation
- ✅ `EWALLET_PAYMENT_GUIDE.md` - Complete integration guide

## How It Works

```
User selects TnG → Backend creates Billplz bill → Deep link opens TnG app 
→ User approves with Secure Sign → TnG confirms to Billplz 
→ Billplz webhook → Backend marks paid → Frontend polls status → Success!
```

## Testing

### Enable Billplz Sandbox

```bash
# backend/.env
BILLPLZ_ENABLED=true
BILLPLZ_API_KEY=your_sandbox_api_key
BILLPLZ_COLLECTION_ID=your_collection_id
BILLPLZ_X_SIGNATURE_KEY=your_signature_key
BILLPLZ_SANDBOX=true
```

### Test Flow

1. Select Touch 'n Go eWallet
2. Click "Continue to Payment"
3. System generates payment link with deep link
4. On mobile: App opens automatically
5. On desktop: QR code shown
6. In sandbox: Simulated payment page appears
7. Approve payment
8. Webhook fires → Status updates to 'succeeded'
9. Success page shows

## Production Setup

1. **Sign up** at [Billplz.com](https://www.billplz.com)
2. **Create Collection** in dashboard
3. **Get API credentials** from Settings
4. **Configure webhook** URL: `https://yourdomain.com/api/webhooks/billplz`
5. **Update .env**:
   ```env
   BILLPLZ_ENABLED=true
   BILLPLZ_API_KEY=your_live_api_key
   BILLPLZ_COLLECTION_ID=your_live_collection_id
   BILLPLZ_X_SIGNATURE_KEY=your_live_signature_key
   BILLPLZ_SANDBOX=false
   ```

## What's Next

- [ ] Test with real Billplz sandbox account
- [ ] Configure webhook URL in Billplz dashboard
- [ ] Test each wallet type (TnG, GrabPay, Boost, ShopeePay)
- [ ] Verify webhook signature in production
- [ ] Add notification when payment succeeds

## Need Help?

See `EWALLET_PAYMENT_GUIDE.md` for detailed documentation, troubleshooting, and Billplz API reference.
