# E-Wallet Payment Testing Checklist

## Pre-Testing Setup

### Backend Configuration
- [ ] Billplz API credentials configured in `.env`
- [ ] `BILLPLZ_ENABLED=true`
- [ ] `BILLPLZ_SANDBOX=true` (for testing)
- [ ] Backend server running: `php artisan serve`
- [ ] Database migrations run: `php artisan migrate`

### Frontend Configuration  
- [ ] Frontend running: `npm run dev`
- [ ] API URL configured correctly in `nuxt.config.ts`

### Billplz Dashboard Setup
- [ ] Account created at billplz.com
- [ ] Collection created
- [ ] Webhook URL configured: `http://yoururl/api/webhooks/billplz`
- [ ] X-Signature key generated and copied to `.env`

## Test Cases

### Test 1: Select E-Wallet
- [ ] Navigate to payment page
- [ ] Click "E-Wallet" payment method
- [ ] All 4 wallets displayed (TnG, GrabPay, Boost, ShopeePay)
- [ ] Can select a wallet (border turns blue)
- [ ] Amount + fee displayed correctly

### Test 2: Initiate Payment
- [ ] Click "Continue to [Wallet Name]"
- [ ] Loading state shows
- [ ] API call successful (check Network tab)
- [ ] Transaction created in database
- [ ] Deep link URL generated
- [ ] QR code displayed (or app opens on mobile)

### Test 3: Mobile Deep Link
**On mobile device:**
- [ ] Deep link automatically opens wallet app
- [ ] Falls back to QR if app not installed
- [ ] Payment details shown in app
- [ ] Can approve/reject payment

### Test 4: Desktop QR Code
**On desktop:**
- [ ] QR code displayed
- [ ] Can scan with phone camera
- [ ] Opens wallet app after scan
- [ ] Countdown timer shows (10 minutes)

### Test 5: Payment Approval (Sandbox)
- [ ] Click "Pay" in sandbox page
- [ ] Billplz processes payment
- [ ] Webhook fires to backend
- [ ] Transaction status updates to 'succeeded'
- [ ] Frontend polls and detects change
- [ ] Success message displayed

### Test 6: Payment Status Page
- [ ] After payment, redirected to `/payment/status`
- [ ] Transaction ID in URL
- [ ] Correct status displayed (Success/Pending/Failed)
- [ ] Transaction details shown
- [ ] "Return to Dashboard" button works

### Test 7: Webhook Handling
**Check backend logs:**
- [ ] Webhook received: `storage/logs/laravel.log`
- [ ] Signature verified correctly
- [ ] `webhook_logs` table has entry
- [ ] `transactions` table updated
- [ ] Status changed from 'pending' to 'succeeded'

### Test 8: Failed Payment
- [ ] Start payment flow
- [ ] Cancel in sandbox (or use fail amount)
- [ ] Webhook fires with failed status
- [ ] Transaction marked as 'failed'
- [ ] Frontend shows error message
- [ ] Can retry payment

### Test 9: Timeout Handling
- [ ] Initiate payment
- [ ] Wait 10 minutes without paying
- [ ] Countdown reaches 0
- [ ] QR code expires
- [ ] Error message shown
- [ ] Transaction status remains 'pending'

### Test 10: Each Wallet Type
- [ ] Test Touch 'n Go payment
- [ ] Test GrabPay payment
- [ ] Test Boost payment
- [ ] Test ShopeePay payment
- [ ] Each generates correct deep link
- [ ] Each processes webhook correctly

## API Testing

### Test API Endpoints

#### 1. Get Payment Rails
```bash
curl http://localhost:8000/api/payment/rails
```
**Expected**: E-wallet rail listed with 4 wallets

#### 2. Initiate E-Wallet Payment
```bash
curl -X POST http://localhost:8000/api/payment/initiate \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 50.00,
    "payment_rail": "ewallet",
    "wallet_type": "tng",
    "description": "Test payment"
  }'
```
**Expected**: Transaction created with deep_link_url in metadata

#### 3. Check Transaction Status
```bash
curl http://localhost:8000/api/payment/transactions/{transaction_id} \
  -H "Authorization: Bearer YOUR_TOKEN"
```
**Expected**: Transaction details with current status

#### 4. Test Webhook (Manual)
```bash
curl -X POST http://localhost:8000/api/webhooks/billplz \
  -H "Content-Type: application/json" \
  -H "X-Signature: YOUR_SIGNATURE" \
  -d '{
    "id": "bill_test123",
    "collection_id": "col_xyz",
    "paid": "true",
    "state": "paid",
    "amount": "5000",
    "paid_at": "2025-11-14 15:30:00"
  }'
```
**Expected**: 200 OK, webhook logged, transaction updated

## Database Verification

### Check Transaction Record
```sql
SELECT * FROM transactions 
WHERE payment_rail = 'ewallet' 
ORDER BY created_at DESC 
LIMIT 1;
```

**Verify**:
- [ ] `ewallet_type` is set (tng, grabpay, etc.)
- [ ] `provider` is 'billplz'
- [ ] `provider_transaction_id` is bill ID
- [ ] `metadata` contains deep_link_url, qr_code_url
- [ ] `status` updates from 'pending' to 'succeeded'

### Check Webhook Log
```sql
SELECT * FROM webhook_logs 
WHERE provider = 'billplz' 
ORDER BY created_at DESC 
LIMIT 1;
```

**Verify**:
- [ ] `event_type` is 'bill.paid'
- [ ] `signature_verified` is true
- [ ] `status` is 'processed'
- [ ] `payload` contains bill data

## Common Issues & Solutions

### Issue: Deep link doesn't open app
**Solution**: 
- Check if wallet app installed
- Try QR code fallback
- Verify deep link format in logs

### Issue: Webhook not received
**Solution**:
- Check webhook URL is publicly accessible
- Verify Billplz dashboard webhook config
- Use ngrok for local testing
- Check firewall/security settings

### Issue: Signature verification fails
**Solution**:
- Verify X-Signature key matches Billplz
- Check webhook payload structure
- Review signature generation in code

### Issue: Status not updating in frontend
**Solution**:
- Check polling interval (should be 3 seconds)
- Verify transaction ID matches
- Check browser console for errors
- Review API response structure

## Production Checklist

Before going live:
- [ ] Change `BILLPLZ_SANDBOX=false`
- [ ] Use production API keys
- [ ] Configure production webhook URL (HTTPS required)
- [ ] Test with small real transaction
- [ ] Monitor webhook logs for 24 hours
- [ ] Set up error alerts
- [ ] Document customer support procedures

## Support Resources

- Billplz API Docs: https://www.billplz.com/api
- Laravel HTTP Client: https://laravel.com/docs/http-client
- Nuxt Composables: https://nuxt.com/docs/guide/directory-structure/composables

---

**Last Updated**: November 14, 2025
**Version**: 1.0
