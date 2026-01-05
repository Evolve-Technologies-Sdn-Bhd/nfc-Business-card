# Payment System API Documentation

## Overview

This document provides comprehensive documentation for the payment system API endpoints. The system supports multiple payment rails including credit/debit cards, FPX online banking, DuitNow, e-wallets (Touch 'n Go, GrabPay, Boost, ShopeePay), and manual bank transfers.

## Table of Contents

1. [Authentication](#authentication)
2. [Payment Rails](#payment-rails)
3. [Transactions](#transactions)
4. [Payment Methods](#payment-methods)
5. [Refunds](#refunds)
6. [Subscriptions](#subscriptions)
7. [Webhooks](#webhooks)
8. [Admin Endpoints](#admin-endpoints)

---

## Authentication

All endpoints except webhooks require authentication using Laravel Sanctum tokens.

**Header:**
```
Authorization: Bearer {your-token}
```

---

## Payment Rails

### Get Available Payment Rails

Retrieve all available payment methods with fees and supported providers.

**Endpoint:** `GET /api/payment/rails`

**Authentication:** Required

**Response:**
```json
{
  "success": true,
  "rails": {
    "card": {
      "name": "Credit/Debit Card",
      "types": ["visa", "mastercard", "amex"],
      "providers": ["stripe"],
      "features": ["3ds", "tokenization", "recurring"],
      "fees": {
        "percentage": 2.9,
        "fixed": 0.3,
        "currency": "MYR"
      }
    },
    "fpx": {
      "name": "FPX Online Banking",
      "banks": [
        {"code": "MB2U", "name": "Maybank"},
        {"code": "CIMBCLICKS", "name": "CIMB Bank"},
        // ... more banks
      ],
      "providers": ["billplz"],
      "fees": {
        "percentage": 1.5,
        "fixed": 0,
        "currency": "MYR"
      }
    },
    // ... other rails
  }
}
```

---

## Transactions

### Calculate Fees

Calculate payment processing fees before initiating payment.

**Endpoint:** `POST /api/payment/calculate-fees`

**Authentication:** Required

**Request Body:**
```json
{
  "amount": 100.00,
  "currency": "MYR",
  "payment_rail": "card"
}
```

**Response:**
```json
{
  "success": true,
  "calculation": {
    "amount": 100.00,
    "fee": 3.20,
    "total": 103.20,
    "currency": "MYR",
    "payment_rail": "card"
  }
}
```

---

### Initiate Payment

Start a new payment transaction.

**Endpoint:** `POST /api/payment/initiate`

**Authentication:** Required

**Request Body (Card Payment):**
```json
{
  "amount": 100.00,
  "currency": "MYR",
  "payment_rail": "card",
  "payment_method_id": 1,
  "description": "Premium subscription",
  "save_payment_method": true,
  "metadata": {
    "order_id": "ORD-12345"
  }
}
```

**Request Body (FPX Payment):**
```json
{
  "amount": 100.00,
  "currency": "MYR",
  "payment_rail": "fpx",
  "bank_code": "MB2U",
  "description": "Premium subscription",
  "callback_url": "https://yourapp.com/payment/callback"
}
```

**Request Body (E-Wallet Payment):**
```json
{
  "amount": 100.00,
  "currency": "MYR",
  "payment_rail": "ewallet",
  "ewallet_type": "tng",
  "description": "Premium subscription",
  "callback_url": "https://yourapp.com/payment/callback"
}
```

**Request Body (Manual Bank Transfer):**
```json
{
  "amount": 100.00,
  "currency": "MYR",
  "payment_rail": "manual_bank",
  "description": "Premium subscription"
}
```

**Response (Card - 3DS Required):**
```json
{
  "success": true,
  "transaction": {
    "id": 1,
    "transaction_id": "TXN-ABC123",
    "amount": 100.00,
    "currency": "MYR",
    "status": "requires_action",
    "payment_rail": "card"
  },
  "requires_action": true,
  "client_secret": "pi_xxx_secret_xxx"
}
```

**Response (Manual Bank Transfer):**
```json
{
  "success": true,
  "transaction": {
    "id": 1,
    "transaction_id": "TXN-ABC123",
    "amount": 100.00,
    "currency": "MYR",
    "status": "pending",
    "payment_rail": "manual_bank",
    "bank_reference_code": "REF-XYZ789"
  },
  "bank_details": {
    "account_name": "Your Company Sdn Bhd",
    "banks": [
      {
        "name": "Maybank",
        "account_number": "1234567890"
      }
    ],
    "reference_code": "REF-XYZ789",
    "instructions": "Please include the reference code in your transfer"
  }
}
```

---

### Confirm Payment (3DS)

Confirm payment after 3D Secure authentication.

**Endpoint:** `POST /api/payment/transactions/{transaction_id}/confirm`

**Authentication:** Required

**Request Body:**
```json
{
  "payment_intent_id": "pi_xxx"
}
```

**Response:**
```json
{
  "success": true,
  "transaction": {
    "id": 1,
    "transaction_id": "TXN-ABC123",
    "amount": 100.00,
    "status": "succeeded"
  }
}
```

---

### Get Transaction

Retrieve details of a specific transaction.

**Endpoint:** `GET /api/payment/transactions/{transaction_id}`

**Authentication:** Required

**Response:**
```json
{
  "success": true,
  "transaction": {
    "id": 1,
    "transaction_id": "TXN-ABC123",
    "amount": 100.00,
    "currency": "MYR",
    "status": "succeeded",
    "payment_rail": "card",
    "provider": "stripe",
    "created_at": "2025-01-14T10:30:00Z",
    "refunds": [],
    "metadata": {
      "order_id": "ORD-12345"
    }
  }
}
```

---

### Get Transaction History

Retrieve paginated transaction history with filters.

**Endpoint:** `GET /api/payment/transactions`

**Authentication:** Required

**Query Parameters:**
- `status` (optional): Filter by status (pending, succeeded, failed, etc.)
- `payment_rail` (optional): Filter by payment rail
- `type` (optional): one_time or recurring
- `from_date` (optional): Start date filter
- `to_date` (optional): End date filter
- `per_page` (optional): Results per page (default: 20)

**Example:** `GET /api/payment/transactions?status=succeeded&per_page=10`

**Response:**
```json
{
  "success": true,
  "transactions": {
    "data": [
      {
        "id": 1,
        "transaction_id": "TXN-ABC123",
        "amount": 100.00,
        "currency": "MYR",
        "status": "succeeded",
        "payment_rail": "card",
        "created_at": "2025-01-14T10:30:00Z"
      }
    ],
    "current_page": 1,
    "per_page": 10,
    "total": 50
  }
}
```

---

### Upload Payment Proof

Upload proof of payment for manual bank transfers.

**Endpoint:** `POST /api/payment/transactions/{transaction_id}/proof`

**Authentication:** Required

**Content-Type:** `multipart/form-data`

**Request Body:**
```
proof_file: [File] (image/jpeg, image/png, application/pdf, max 5MB)
```

**Response:**
```json
{
  "success": true,
  "message": "Payment proof uploaded successfully",
  "transaction": {
    "id": 1,
    "transaction_id": "TXN-ABC123",
    "status": "pending",
    "payment_proof_url": "https://storage.com/proofs/abc123.jpg",
    "payment_proof_uploaded_at": "2025-01-14T11:00:00Z"
  }
}
```

---

## Payment Methods

### List Payment Methods

Get all saved payment methods for the authenticated user.

**Endpoint:** `GET /api/payment-methods`

**Authentication:** Required

**Query Parameters:**
- `type` (optional): Filter by type (card, bank, ewallet)
- `verified_only` (optional): true/false

**Response:**
```json
{
  "success": true,
  "payment_methods": [
    {
      "id": 1,
      "type": "card",
      "provider": "stripe",
      "is_default": true,
      "is_verified": true,
      "card_brand": "visa",
      "card_last_four": "4242",
      "card_exp_month": 12,
      "card_exp_year": 2025,
      "display_name": "Visa •••• 4242",
      "created_at": "2025-01-14T10:00:00Z"
    }
  ]
}
```

---

### Get Default Payment Method

Get the user's default payment method.

**Endpoint:** `GET /api/payment-methods/default`

**Authentication:** Required

**Response:**
```json
{
  "success": true,
  "payment_method": {
    "id": 1,
    "type": "card",
    "is_default": true,
    "display_name": "Visa •••• 4242"
  }
}
```

---

### Save Payment Method

Save a new payment method for future use.

**Endpoint:** `POST /api/payment-methods`

**Authentication:** Required

**Request Body (Card):**
```json
{
  "type": "card",
  "provider": "stripe",
  "stripe_payment_method_id": "pm_xxx",
  "card_brand": "visa",
  "card_last_four": "4242",
  "card_exp_month": 12,
  "card_exp_year": 2025,
  "billing_email": "user@example.com",
  "set_as_default": true
}
```

**Response:**
```json
{
  "success": true,
  "message": "Payment method saved successfully",
  "payment_method": {
    "id": 1,
    "type": "card",
    "is_default": true,
    "display_name": "Visa •••• 4242"
  }
}
```

---

### Update Payment Method

Update an existing payment method (mainly for setting default).

**Endpoint:** `PUT /api/payment-methods/{id}`

**Authentication:** Required

**Request Body:**
```json
{
  "set_as_default": true
}
```

**Response:**
```json
{
  "success": true,
  "message": "Payment method updated successfully",
  "payment_method": {
    "id": 1,
    "is_default": true
  }
}
```

---

### Delete Payment Method

Delete a saved payment method.

**Endpoint:** `DELETE /api/payment-methods/{id}`

**Authentication:** Required

**Response:**
```json
{
  "success": true,
  "message": "Payment method deleted successfully"
}
```

---

## Refunds

### Request Refund

Request a refund for a transaction.

**Endpoint:** `POST /api/refunds/transactions/{transaction_id}/refund`

**Authentication:** Required

**Request Body:**
```json
{
  "amount": 50.00,
  "reason": "Product not as described"
}
```

**Response (Auto-Approved):**
```json
{
  "success": true,
  "message": "Refund processed successfully",
  "refund": {
    "id": 1,
    "refund_id": "REF-ABC123",
    "amount": 50.00,
    "status": "succeeded",
    "reason": "Product not as described"
  }
}
```

**Response (Pending Review):**
```json
{
  "success": true,
  "message": "Refund request submitted for review",
  "refund": {
    "id": 1,
    "refund_id": "REF-ABC123",
    "amount": 50.00,
    "status": "pending",
    "reason": "Product not as described"
  }
}
```

---

### Get Refund Details

Get details of a specific refund.

**Endpoint:** `GET /api/refunds/{refund_id}`

**Authentication:** Required

**Response:**
```json
{
  "success": true,
  "refund": {
    "id": 1,
    "refund_id": "REF-ABC123",
    "transaction_id": "TXN-ABC123",
    "amount": 50.00,
    "status": "succeeded",
    "reason": "Product not as described",
    "created_at": "2025-01-14T12:00:00Z",
    "processed_at": "2025-01-14T12:05:00Z"
  }
}
```

---

### List Refunds

Get refund history for the authenticated user.

**Endpoint:** `GET /api/refunds`

**Authentication:** Required

**Query Parameters:**
- `status` (optional): Filter by status
- `per_page` (optional): Results per page

**Response:**
```json
{
  "success": true,
  "refunds": {
    "data": [
      {
        "id": 1,
        "refund_id": "REF-ABC123",
        "amount": 50.00,
        "status": "succeeded",
        "created_at": "2025-01-14T12:00:00Z"
      }
    ],
    "current_page": 1,
    "total": 10
  }
}
```

---

## Subscriptions

### Get Plans

Get all available subscription plans.

**Endpoint:** `GET /api/subscriptions/plans`

**Authentication:** Required

**Response:**
```json
{
  "success": true,
  "plans": [
    {
      "name": "Basic",
      "monthly_price": 29.90,
      "yearly_price": 299.00,
      "currency": "MYR",
      "features": [
        "1 NFC card",
        "Basic analytics",
        "Email support"
      ]
    },
    {
      "name": "Business",
      "monthly_price": 79.90,
      "yearly_price": 799.00,
      "currency": "MYR",
      "features": [
        "5 NFC cards",
        "Advanced analytics",
        "Priority support",
        "Custom branding"
      ]
    }
  ]
}
```

---

### Get Active Subscription

Get the user's current active subscription.

**Endpoint:** `GET /api/subscriptions/active`

**Authentication:** Required

**Response:**
```json
{
  "success": true,
  "subscription": {
    "id": 1,
    "subscription_id": "SUB-ABC123",
    "plan_type": "business",
    "amount": 79.90,
    "interval": "monthly",
    "status": "active",
    "current_period_start": "2025-01-01",
    "current_period_end": "2025-02-01",
    "next_billing_date": "2025-02-01",
    "trial_ends_at": null,
    "is_in_trial": false,
    "days_until_billing": 18,
    "payment_method": {
      "display_name": "Visa •••• 4242"
    }
  }
}
```

---

### Subscribe

Create a new subscription.

**Endpoint:** `POST /api/subscriptions/subscribe`

**Authentication:** Required

**Request Body:**
```json
{
  "plan_type": "business",
  "interval": "monthly",
  "payment_method_id": 1,
  "trial_period_days": 14
}
```

**Response:**
```json
{
  "success": true,
  "message": "Subscription created successfully",
  "subscription": {
    "id": 1,
    "subscription_id": "SUB-ABC123",
    "plan_type": "business",
    "status": "active",
    "trial_ends_at": "2025-01-28",
    "next_billing_date": "2025-01-28"
  }
}
```

---

### Cancel Subscription

Cancel an active subscription.

**Endpoint:** `POST /api/subscriptions/{subscription_id}/cancel`

**Authentication:** Required

**Request Body:**
```json
{
  "cancel_immediately": false,
  "reason": "Switching to another provider"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Subscription will be cancelled at the end of the billing period",
  "subscription": {
    "id": 1,
    "subscription_id": "SUB-ABC123",
    "status": "active",
    "cancelled_at": "2025-01-14T12:00:00Z",
    "ends_at": "2025-02-01"
  }
}
```

---

### Reactivate Subscription

Reactivate a cancelled subscription.

**Endpoint:** `POST /api/subscriptions/{subscription_id}/reactivate`

**Authentication:** Required

**Response:**
```json
{
  "success": true,
  "message": "Subscription reactivated successfully",
  "subscription": {
    "id": 1,
    "subscription_id": "SUB-ABC123",
    "status": "active"
  }
}
```

---

### Update Payment Method

Change the payment method for a subscription.

**Endpoint:** `PUT /api/subscriptions/{subscription_id}/payment-method`

**Authentication:** Required

**Request Body:**
```json
{
  "payment_method_id": 2
}
```

**Response:**
```json
{
  "success": true,
  "message": "Payment method updated successfully",
  "subscription": {
    "id": 1,
    "subscription_id": "SUB-ABC123",
    "payment_method": {
      "display_name": "Mastercard •••• 5678"
    }
  }
}
```

---

## Webhooks

Webhook endpoints for payment provider callbacks. These endpoints do NOT require authentication but verify signatures.

### Stripe Webhook

**Endpoint:** `POST /api/webhooks/stripe`

**Headers:**
```
Stripe-Signature: {signature}
```

---

### Billplz Webhook

**Endpoint:** `POST /api/webhooks/billplz`

**Headers:**
```
X-Signature: {signature}
```

---

### Senangpay Callback

**Endpoint:** `POST /api/webhooks/senangpay`

**Query Parameters:**
- `status_id`
- `order_id`
- `transaction_id`
- `msg`
- `hash`

---

### Xendit Webhook

**Endpoint:** `POST /api/webhooks/xendit`

**Headers:**
```
X-Callback-Token: {token}
```

---

## Admin Endpoints

Admin endpoints require `is_admin` flag on the user.

### Get Pending Refunds

**Endpoint:** `GET /api/admin/payments/refunds/pending`

**Authentication:** Required (Admin)

**Response:**
```json
{
  "success": true,
  "refunds": [
    {
      "id": 1,
      "refund_id": "REF-ABC123",
      "user": {
        "name": "John Doe",
        "email": "john@example.com"
      },
      "amount": 500.00,
      "reason": "Product defective",
      "created_at": "2025-01-14T10:00:00Z"
    }
  ]
}
```

---

### Process Refund

**Endpoint:** `POST /api/admin/payments/refunds/{refund_id}/process`

**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "action": "approve",
  "notes": "Approved as per company policy"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Refund approved successfully",
  "refund": {
    "id": 1,
    "refund_id": "REF-ABC123",
    "status": "succeeded"
  }
}
```

---

### Get Pending Manual Transfers

**Endpoint:** `GET /api/admin/payments/manual-transfers/pending`

**Authentication:** Required (Admin)

**Response:**
```json
{
  "success": true,
  "transfers": [
    {
      "id": 1,
      "transaction_id": "TXN-ABC123",
      "user": {
        "name": "John Doe",
        "email": "john@example.com"
      },
      "amount": 100.00,
      "bank_reference_code": "REF-XYZ789",
      "payment_proof_url": "https://storage.com/proofs/abc123.jpg",
      "payment_proof_uploaded_at": "2025-01-14T10:00:00Z"
    }
  ]
}
```

---

### Verify Manual Transfer

**Endpoint:** `POST /api/admin/payments/manual-transfers/{transaction_id}/verify`

**Authentication:** Required (Admin)

**Request Body:**
```json
{
  "action": "approve",
  "notes": "Payment verified in bank statement"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Payment verified successfully",
  "transaction": {
    "id": 1,
    "transaction_id": "TXN-ABC123",
    "status": "succeeded",
    "verified_at": "2025-01-14T12:00:00Z"
  }
}
```

---

## Error Responses

All endpoints return consistent error responses:

**Validation Error (422):**
```json
{
  "success": false,
  "errors": {
    "amount": ["The amount field is required."],
    "payment_rail": ["The selected payment rail is invalid."]
  }
}
```

**Unauthorized (401):**
```json
{
  "success": false,
  "message": "Unauthenticated."
}
```

**Forbidden (403):**
```json
{
  "success": false,
  "message": "Unauthorized. Admin access required."
}
```

**Not Found (404):**
```json
{
  "success": false,
  "message": "Transaction not found"
}
```

**Server Error (500):**
```json
{
  "success": false,
  "message": "Failed to process payment"
}
```

---

## Status Codes

- `pending` - Payment initiated, awaiting processing
- `processing` - Payment is being processed
- `requires_action` - Requires user action (3DS)
- `succeeded` - Payment completed successfully
- `failed` - Payment failed
- `cancelled` - Payment cancelled by user
- `refunded` - Payment has been refunded

---

## Testing

### Test Cards (Stripe)

- **Success:** 4242 4242 4242 4242
- **3DS Required:** 4000 0027 6000 3184
- **Declined:** 4000 0000 0000 0002

### Test FPX

Use the Billplz sandbox environment with test bank codes.

### Test E-Wallets

Use provider sandbox credentials for testing.

---

## Rate Limits

- Standard endpoints: 60 requests per minute
- Webhook endpoints: No rate limit (use signature verification)

---

## Support

For API support, contact: support@yourcompany.com
