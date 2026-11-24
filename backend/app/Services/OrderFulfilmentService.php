<?php

namespace App\Services;

use App\Models\LandingPage;
use App\Models\NfcCard;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Order Fulfilment Service
 *
 * Single source of truth for what happens once a payment is confirmed successful.
 * Invoked from the trusted Fiuu success paths (notification / callback / return).
 *
 * Idempotency: fulfilment is guarded by orders.fulfilled_at. Even if the gateway
 * delivers the same success more than once (and the webhook duplicate detection
 * is bypassed for any reason), the order can only be fulfilled once — so no
 * duplicate NFC cards or landing pages are ever created.
 */
class OrderFulfilmentService
{
    /**
     * Fulfil the order attached to a successfully-paid transaction.
     */
    public function fulfilForTransaction(Transaction $transaction): void
    {
        $order = $transaction->order;

        if (!$order) {
            // Nothing to fulfil (e.g. a transaction created outside the order flow).
            return;
        }

        // Fast idempotency guard (no lock needed for the common duplicate case).
        if ($order->fulfilled_at !== null) {
            return;
        }

        DB::transaction(function () use ($order, $transaction) {
            // Re-read under the transaction and re-check the guard to survive races.
            $order->refresh();
            if ($order->fulfilled_at !== null) {
                return;
            }

            $user = $order->user;

            // 1) Mark the order as paid.
            $order->update([
                'status' => Order::STATUS_PAID,
                'paid_at' => $order->paid_at ?? now(),
            ]);

            if (!$user) {
                // Without an owner we cannot create a card; still stamp fulfilment
                // so we never loop on a broken order.
                $order->update(['fulfilled_at' => now()]);
                return;
            }

            // 2) Card fulfilment — link an existing card if the order already had
            //    one, otherwise create one card per ordered quantity.
            $preShipmentStatus = 'payment_verified';
            $cardAddress = $this->formatShippingAddress($order);

            $card = null;
            $existingCard = $transaction->nfc_card_id
                ? NfcCard::find($transaction->nfc_card_id)
                : null;

            if ($existingCard) {
                $card = $existingCard;
                if (!in_array($card->status, ['payment_verified', 'processing', 'shipped', 'delivered', 'active'], true)) {
                    $card->update([
                        'status' => $preShipmentStatus,
                        'order_confirmed_at' => $card->order_confirmed_at ?? now(),
                    ]);
                }
            } else {
                $quantity = $this->resolveCardQuantity($order);
                $perCardAmount = round($order->total / $quantity, 2);

                for ($i = 0; $i < $quantity; $i++) {
                    $newCard = NfcCard::create([
                        'user_id' => $user->id,
                        'nfc_card_id' => 'NFC-' . strtoupper(Str::random(12)),
                        'card_owner' => $user->full_name ?: ($user->name ?? 'Customer'),
                        'billing_address' => $cardAddress ?: ($user->company ?? 'Not provided'),
                        'shipping_address' => $cardAddress,
                        'contact_number' => $order->shipping_phone ?: ($user->phone ?? 'Not provided'),
                        'purchase_date' => now(),
                        'subscription_plan' => $order->metadata['plan'] ?? $user->subscription_plan ?? 'premium',
                        'purchase_amount' => $perCardAmount,
                        'status' => $preShipmentStatus,
                        'order_confirmed_at' => now(),
                    ]);

                    LandingPage::create([
                        'nfc_card_id' => $newCard->id,
                        'name' => $user->full_name ?: '',
                        'email' => $user->email,
                    ]);

                    // Link the first card back onto the transaction.
                    if ($card === null) {
                        $card = $newCard;
                        $transaction->update(['nfc_card_id' => $card->id]);
                    }
                }
            }

            // 3) Ensure the primary card has a landing page.
            if (!$card->landingPage()->exists()) {
                LandingPage::create([
                    'nfc_card_id' => $card->id,
                    'name' => $user->full_name ?: '',
                    'email' => $user->email,
                ]);
            }

            // 4) Stamp fulfilment — the permanent idempotency marker.
            $order->update(['fulfilled_at' => now()]);

            Log::info('Order fulfilled after successful payment', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'nfc_card_id' => $card->id,
                'transaction_id' => $transaction->id,
            ]);
        });
    }

    /**
     * How many physical cards were ordered. Prefers the card line-item
     * quantity, falls back to the checkout metadata, clamped to 1..50.
     */
    protected function resolveCardQuantity(Order $order): int
    {
        $quantity = 0;

        foreach ($order->items as $item) {
            $meta = $item->metadata ?? [];
            if (is_array($meta) && array_key_exists('card_type', $meta)) {
                $quantity += max(1, (int) $item->quantity);
            }
        }

        if ($quantity < 1) {
            $quantity = (int) ($order->metadata['card_quantity'] ?? 1);
        }

        return max(1, min(50, $quantity));
    }

    /**
     * Single-line rendering of the order's shipping address for the card record.
     */
    protected function formatShippingAddress(Order $order): ?string
    {
        $parts = array_filter([
            $order->shipping_address_line1,
            $order->shipping_city,
            $order->shipping_state,
            $order->shipping_postcode,
            $order->shipping_country,
        ], fn ($value) => !empty($value));

        return $parts ? implode(', ', $parts) : null;
    }
}
