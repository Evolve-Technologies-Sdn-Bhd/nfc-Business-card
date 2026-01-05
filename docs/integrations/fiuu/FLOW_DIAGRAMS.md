# Fiuu Payment Flow - Visual Guide

## 🔄 Complete Payment Flow Diagram

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        FIUU PAYMENT INTEGRATION FLOW                     │
└─────────────────────────────────────────────────────────────────────────┘

STEP 1: PAYMENT INITIATION
━━━━━━━━━━━━━━━━━━━━━━━━━━
┌──────────┐
│ Customer │ Clicks "Pay Now"
└────┬─────┘
     │
     v
┌─────────────────┐
│ Your Frontend   │ POST /api/payment/initiate
└────┬────────────┘
     │
     v
┌─────────────────┐
│ Your Backend    │ 1. Create transaction (status: pending)
│ PaymentService  │ 2. Calculate signature: MD5(amount+merchant_id+orderid+verify_key)
└────┬────────────┘ 3. Generate payment form data
     │
     │ Returns: {payment_url, payment_data, transaction_id}
     v
┌─────────────────┐
│ Your Frontend   │ Auto-submit HTML form to Fiuu
└────┬────────────┘
     │
     │ (Customer's browser redirected to Fiuu)
     v

STEP 2: CUSTOMER PAYMENT AT FIUU
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
┌─────────────────┐
│ Fiuu Payment    │ Customer sees Fiuu payment page
│ Page            │ - Select payment method (FPX/Card/E-wallet)
└────┬────────────┘ - Enter payment details
     │              - Complete authentication (3DS/OTP)
     │
     │ (Customer completes payment)
     v

STEP 3: FIUU PROCESSES PAYMENT
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
┌─────────────────┐
│ Fiuu Server     │ Processes payment
│                 │ - Validates card/bank account
└────┬────────────┘ - Charges customer
     │              - Generates transaction ID
     │              - Determines status (00/11/22)
     v
     
     ├─────────────────────────────┬──────────────────────────┐
     │                             │                          │
     v                             v                          v
     
NOTIFICATION URL              RETURN URL              CALLBACK URL
(Server-to-Server)           (Customer Redirect)     (Delayed Update)
     │                             │                          │
     v                             v                          v

┌─────────────────┐         ┌─────────────────┐     ┌─────────────────┐
│ notification-   │         │ return-url.php  │     │ callback-url.   │
│ url.php         │         │                 │     │ php             │
│                 │         │                 │     │                 │
│ PRIMARY         │         │ UX/Display      │     │ Async Updates   │
│ CONFIRMATION    │         │                 │     │                 │
└────┬────────────┘         └────┬────────────┘     └────┬────────────┘
     │                             │                          │
     │ 1. Receive POST             │ 1. Receive POST          │ 1. Receive POST
     │ 2. Verify signature         │ 2. Verify signature      │ 2. Verify signature
     │ 3. Update database          │ 3. Update database       │ 3. Update database
     │ 4. Trigger actions          │ 4. Display result page   │ 4. Trigger actions
     │ 5. Return "CBTOKEN:MPSTATOK"│ 5. Return "CBTOKEN:..."  │ 5. Return "CBTOKEN:..."
     │                             │                          │
     v                             v                          │
┌─────────────────┐         ┌─────────────────┐             │
│ Your Database   │         │ Customer sees   │             │
│ Status: SUCCEEDED│◄────────┤ Success page!  │             │
└─────────────────┘         └─────────────────┘             │
     │                             │                          │
     │                             │ (Customer clicks         │
     │                             │  "Return to Dashboard")  │
     │                             v                          │
     │                      ┌─────────────────┐              │
     │                      │ Your Frontend   │              │
     │                      │ Dashboard       │              │
     │                      └─────────────────┘              │
     │                                                        │
     │ (For cash/delayed payments only)                      │
     │◄──────────────────────────────────────────────────────┘
     │ Updates status from PENDING → SUCCEEDED
     v
```

## 📊 Three Webhook Endpoints Explained

```
┌────────────────────────────────────────────────────────────────────┐
│                    WEBHOOK ENDPOINT COMPARISON                      │
├─────────────────┬──────────────────┬──────────────────────────────┤
│                 │                  │                              │
│  return-url.php │ notification-    │  callback-url.php            │
│                 │ url.php          │                              │
│                 │                  │                              │
├─────────────────┼──────────────────┼──────────────────────────────┤
│ WHEN CALLED     │ WHEN CALLED      │ WHEN CALLED                  │
│                 │                  │                              │
│ After customer  │ Immediately      │ Hours/days later             │
│ completes       │ after payment    │ when delayed payment         │
│ payment         │ (server-to-      │ is confirmed                 │
│                 │ server)          │                              │
├─────────────────┼──────────────────┼──────────────────────────────┤
│ PURPOSE         │ PURPOSE          │ PURPOSE                      │
│                 │                  │                              │
│ Show result to  │ ⭐ PRIMARY       │ Update pending               │
│ customer        │ payment          │ payments                     │
│                 │ confirmation     │                              │
├─────────────────┼──────────────────┼──────────────────────────────┤
│ CUSTOMER SEES   │ CUSTOMER SEES    │ CUSTOMER SEES                │
│                 │                  │                              │
│ ✅ Yes          │ ❌ No            │ ❌ No                        │
│ (Result page)   │ (Background)     │ (Background)                 │
├─────────────────┼──────────────────┼──────────────────────────────┤
│ TRIGGERED BY    │ TRIGGERED BY     │ TRIGGERED BY                 │
│                 │                  │                              │
│ Browser         │ Fiuu server      │ Fiuu server                  │
│ redirect        │ webhook          │ webhook                      │
├─────────────────┼──────────────────┼──────────────────────────────┤
│ RELIABILITY     │ RELIABILITY      │ RELIABILITY                  │
│                 │                  │                              │
│ Can fail if     │ ⭐ Most         │ Reliable for                 │
│ customer closes │ reliable         │ delayed payments             │
│ browser         │                  │                              │
├─────────────────┼──────────────────┼──────────────────────────────┤
│ USE CASE        │ USE CASE         │ USE CASE                     │
│                 │                  │                              │
│ • Display       │ • Update         │ • Cash payments              │
│   success/fail  │   database       │ • Bank transfers             │
│ • Show receipt  │ • Send emails    │ • Manual verification        │
│ • Redirect      │ • Activate       │ • Pending → Success          │
│   customer      │   services       │                              │
└─────────────────┴──────────────────┴──────────────────────────────┘
```

## 🔐 Signature Verification Process

```
SIGNATURE CALCULATION (All 3 endpoints use this)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Input Parameters:
┌────────────────────────────────────────┐
│ amount       = "100.00"                │
│ merchant_id  = "YOUR_MERCHANT_ID"      │
│ orderid      = "TXN-ABC123DEF456"      │
│ verify_key   = "YOUR_VERIFY_KEY"       │
└────────────────────────────────────────┘
                    │
                    v
         ┌──────────────────────┐
         │ Concatenate String:  │
         │                      │
         │ "100.00" +           │
         │ "YOUR_MERCHANT_ID" + │
         │ "TXN-ABC123DEF456" + │
         │ "YOUR_VERIFY_KEY"    │
         └──────────┬───────────┘
                    │
                    v
         ┌──────────────────────┐
         │ Calculate MD5 Hash   │
         │                      │
         │ md5(concatenated)    │
         └──────────┬───────────┘
                    │
                    v
         ┌──────────────────────┐
         │ Signature (skey):    │
         │ a1b2c3d4e5f6...      │
         └──────────┬───────────┘
                    │
                    v
    ┌───────────────┴────────────────┐
    │                                │
    v                                v
┌──────────────┐             ┌──────────────┐
│ Fiuu sends   │             │ Your webhook │
│ this as      │   Compare   │ calculates   │
│ 'skey'       │◄───────────►│ the same     │
│ parameter    │             │ signature    │
└──────────────┘             └──────────────┘
    │                                │
    └───────────┬────────────────────┘
                │
                v
       ┌────────────────────┐
       │ Match?             │
       └────────┬───────────┘
                │
        ┌───────┴───────┐
        │               │
        v               v
    ✅ VALID        ❌ INVALID
    Process         Reject
    payment         request
```

## 🎯 Payment Status Lifecycle

```
TRANSACTION STATUS FLOW
━━━━━━━━━━━━━━━━━━━━━━━

1. PENDING (Initial)
   │
   │ Transaction created in database
   │ Customer redirected to Fiuu
   │
   v

2. PROCESSING (Optional)
   │
   │ Payment being processed by bank
   │ 3DS authentication in progress
   │
   v

3. REQUIRES_ACTION (For 3DS)
   │
   │ Customer needs to complete 3DS
   │ OTP required
   │
   v

4. FINAL STATUS
   │
   ├──► SUCCEEDED (Status Code: 00)
   │    ✅ Payment successful
   │    💰 Funds received
   │    📧 Send confirmation email
   │    🔓 Activate service
   │
   ├──► FAILED (Status Code: 11)
   │    ❌ Payment declined
   │    💳 Card rejected/insufficient funds
   │    📧 Send failure notification
   │    🔄 Offer retry option
   │
   └──► PENDING (Status Code: 22)
        ⏳ Awaiting payment (cash/bank transfer)
        📱 Send payment instructions
        ⏰ Wait for callback-url update
        │
        v
        SUCCEEDED (via callback-url.php)
        ✅ Payment confirmed
        📧 Send delayed confirmation
```

## 💾 Database Update Flow

```
DATABASE TRANSACTION UPDATES
━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Initial State:
┌─────────────────────────────────────────┐
│ transactions table                      │
├─────────────────────────────────────────┤
│ transaction_id: TXN-ABC123              │
│ status: PENDING                         │
│ provider: fiuu                          │
│ amount: 100.00                          │
│ provider_transaction_id: NULL           │
│ metadata: {}                            │
└─────────────────────────────────────────┘
               │
               v
┌──────────────────────────────────┐
│ notification-url.php receives:   │
│ - tranID: FIU789XYZ              │
│ - status: 00 (success)           │
│ - channel: FPX_MB2U              │
│ - appcode: ABC123                │
└──────────────┬───────────────────┘
               │
               v
After Update:
┌─────────────────────────────────────────┐
│ transactions table                      │
├─────────────────────────────────────────┤
│ transaction_id: TXN-ABC123              │
│ status: SUCCEEDED ✅                    │
│ provider: fiuu                          │
│ amount: 100.00                          │
│ provider_transaction_id: FIU789XYZ      │
│ payment_rail: fpx                       │
│ bank_name: FPX_MB2U                     │
│ metadata: {                             │
│   "fiuu_appcode": "ABC123",             │
│   "fiuu_channel": "FPX_MB2U",           │
│   "fiuu_paydate": "2025-12-01 10:30",   │
│   "fiuu_status_code": "00"              │
│ }                                       │
└─────────────────────────────────────────┘
```

## 🔄 Delayed Payment Flow (Cash/Bank Transfer)

```
DELAYED PAYMENT SCENARIO
━━━━━━━━━━━━━━━━━━━━━━━━━

Day 1 - 10:00 AM
┌─────────────┐
│ Customer    │ Selects "Cash Payment" or "Bank Transfer"
└──────┬──────┘
       │
       v
┌──────────────────┐
│ Your System      │ Creates transaction (status: PENDING)
└──────┬───────────┘
       │
       v
┌──────────────────┐
│ Fiuu Payment     │ Shows payment instructions:
│ Page             │ "Please pay MYR 100.00 at 7-Eleven"
└──────┬───────────┘ "Reference code: ABC12345"
       │
       v
┌──────────────────┐
│ notification-    │ Called with status: 22 (PENDING)
│ url.php          │ Updates: status = PENDING
└──────┬───────────┘
       │
       v
┌──────────────────┐
│ return-url.php   │ Shows: "Payment Pending"
└──────┬───────────┘ "Please complete payment within 72 hours"
       │
       v
┌──────────────────┐
│ Customer         │ Returns to dashboard
│ Dashboard        │ Status: ⏳ Payment Pending
└──────────────────┘

       (Customer goes to 7-Eleven/Bank)

Day 2 - 3:00 PM
┌──────────────────┐
│ Customer         │ Pays cash at 7-Eleven / Makes bank transfer
└──────┬───────────┘
       │
       v
┌──────────────────┐
│ 7-Eleven/Bank    │ Confirms payment to Fiuu
└──────┬───────────┘
       │
       v
┌──────────────────┐
│ Fiuu Server      │ Payment confirmed!
└──────┬───────────┘ Status changed to: 00 (SUCCESS)
       │
       v
┌──────────────────┐
│ callback-url.php │ Called with status: 00 (SUCCESS)
└──────┬───────────┘ Updates: PENDING → SUCCEEDED
       │              Triggers: Email notification
       │              Activates: Service/Subscription
       v
┌──────────────────┐
│ Customer         │ Receives email: "Payment Confirmed!"
└──────────────────┘ Dashboard updated: ✅ Payment Successful
```

## 📱 Mobile Payment Flow

```
MOBILE E-WALLET PAYMENT (TNG/GrabPay/Boost)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

┌─────────────┐
│ Mobile User │ Clicks "Pay with Touch 'n Go"
└──────┬──────┘
       │
       v
┌──────────────────┐
│ Your App         │ Initiates payment
│ (Mobile)         │ channel: 'tng'
└──────┬───────────┘
       │ Redirects to Fiuu
       v
┌──────────────────┐
│ Fiuu Payment     │ Detects mobile browser
│ Page (Mobile)    │ Shows QR code / Deep link
└──────┬───────────┘
       │
       v
┌──────────────────┐
│ Touch 'n Go App  │ Opens automatically (deep link)
│                  │ OR user scans QR code
└──────┬───────────┘
       │
       v
┌──────────────────┐
│ TNG App          │ User authorizes payment
└──────┬───────────┘ Enters PIN / Biometric
       │
       v
┌──────────────────┐
│ TNG Server       │ Processes payment
└──────┬───────────┘
       │
       v
┌──────────────────┐
│ Fiuu Server      │ Receives confirmation
└──────┬───────────┘
       │
       ├─────────────────────────┐
       │                         │
       v                         v
┌──────────────────┐    ┌──────────────────┐
│ notification-    │    │ Mobile user      │
│ url.php          │    │ redirected back  │
└──────┬───────────┘    └──────┬───────────┘
       │                         │
       │ Updates DB              │
       │ Status: SUCCEEDED       │
       v                         v
                         ┌──────────────────┐
                         │ return-url.php   │
                         │ Shows success!   │
                         └──────────────────┘
```

## 🌐 Complete System Architecture

```
┌────────────────────────────────────────────────────────────────┐
│                    YOUR APPLICATION STACK                       │
├────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────┐                    ┌──────────────┐         │
│  │  Frontend    │                    │  Backend     │         │
│  │  (Nuxt/Vue)  │                    │  (Laravel)   │         │
│  │              │                    │              │         │
│  │ - Payment UI │◄──────JSON────────►│ - API Routes │         │
│  │ - Forms      │                    │ - Controllers│         │
│  └──────────────┘                    │ - Services   │         │
│                                      │ - Models     │         │
│                                      └──────┬───────┘         │
│                                             │                  │
│                                             v                  │
│                                      ┌──────────────┐         │
│                                      │  MySQL DB    │         │
│                                      │              │         │
│                                      │ transactions │         │
│                                      │ users        │         │
│                                      └──────────────┘         │
│                                                                 │
│  ┌──────────────────────────────────────────────────────┐    │
│  │ Webhook Endpoints (backend/public/payment/)          │    │
│  │                                                       │    │
│  │ 1. return-url.php       (Customer redirect)          │    │
│  │ 2. notification-url.php (Server-to-server) ⭐        │    │
│  │ 3. callback-url.php     (Delayed updates)            │    │
│  └──────────────────────────────────────────────────────┘    │
│                     ▲                                          │
│                     │                                          │
└─────────────────────┼──────────────────────────────────────────┘
                      │
                      │ HTTPS POST Webhooks
                      │
┌─────────────────────┼──────────────────────────────────────────┐
│                     │            FIUU SERVICES                  │
├─────────────────────┴──────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────────────────────────────────────────────┐     │
│  │              Fiuu Payment Gateway                     │     │
│  │                                                       │     │
│  │  - Payment processing                                │     │
│  │  - 3DS authentication                                │     │
│  │  - Webhook management                                │     │
│  │  - Transaction logging                               │     │
│  └──────────────┬───────────────────────────────────────┘     │
│                 │                                              │
│                 v                                              │
│  ┌──────────────────────────────────────────────────────┐     │
│  │         Payment Channel Partners                      │     │
│  │                                                       │     │
│  │  • Banks (FPX)     • E-wallets (TNG, GrabPay)       │     │
│  │  • Card Networks   • QR Payment (DuitNow)           │     │
│  └──────────────────────────────────────────────────────┘     │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

**Use these diagrams to understand the complete payment flow!**
