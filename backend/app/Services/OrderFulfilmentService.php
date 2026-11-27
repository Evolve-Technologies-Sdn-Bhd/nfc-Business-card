<?php

namespace App\Services;

use App\Models\CardTemplate;
use App\Models\LandingPage;
use App\Models\NfcCard;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
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

            $designFields = $this->cardDesignFields($order, $user);

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
                if (empty($card->card_design)) {
                    $card->update($designFields);
                }
            } else {
                $quantity = $this->resolveCardQuantity($order);
                $perCardAmount = round($order->total / $quantity, 2);

                for ($i = 0; $i < $quantity; $i++) {
                    $newCard = NfcCard::create($designFields + [
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

                    LandingPage::create(array_merge(
                        ['nfc_card_id' => $newCard->id],
                        $this->landingPageDefaults($order, $user)
                    ));

                    // Link the first card back onto the transaction.
                    if ($card === null) {
                        $card = $newCard;
                        $transaction->update(['nfc_card_id' => $card->id]);
                    }
                }
            }

            // 3) Ensure the primary card has a landing page.
            if (!$card->landingPage()->exists()) {
                LandingPage::create(array_merge(
                    ['nfc_card_id' => $card->id],
                    $this->landingPageDefaults($order, $user)
                ));
            }

            // 4) Maintain the subscription record so renewal / expiry / downgrade
            //    logic has something to act on (previously no row was ever created
            //    for card-purchase payments).
            $this->syncSubscription($order, $user);

            // 5) Stamp fulfilment — the permanent idempotency marker.
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
     * The design the customer ordered, as NfcCard columns. The snapshot is
     * built here from the template id (never trusted from the client) and the
     * details typed at checkout, so the printed card matches the preview even
     * if the template is edited or removed later.
     */
    protected function cardDesignFields(Order $order, User $user): array
    {
        $metadata = is_array($order->metadata) ? $order->metadata : [];
        $info = is_array($metadata['card_info'] ?? null) ? $metadata['card_info'] : [];
        $method = ($metadata['design_method'] ?? 'template') === 'custom' ? 'custom' : 'template';

        $logo = $info['companyLogo'] ?? null;
        $data = [
            'name' => $info['name'] ?? ($user->full_name ?: null),
            'position' => $info['position'] ?? null,
            'company' => $info['companyName'] ?? ($user->company ?? null),
            'phone' => $info['contactNumber'] ?? null,
            'email' => $info['email'] ?? $user->email,
            'website' => $info['website'] ?? null,
            'address' => $info['address'] ?? null,
            // Only keep a hosted URL; a data: URL never reached the server.
            'logo' => is_string($logo) && preg_match('#^https?://#', $logo) ? $logo : null,
        ];

        if ($method === 'custom') {
            $upload = $user->settings['card_design'] ?? [];
            $front = $upload['front_design'] ?? null;
            $back = $upload['back_design'] ?? null;

            return [
                'is_custom_design' => true,
                'custom_design_front_url' => $front,
                'custom_design_back_url' => $back,
                'card_design' => [
                    'method' => 'custom',
                    'front_image_url' => $front,
                    'back_image_url' => $back,
                    'data' => $data,
                    'captured_at' => now()->toISOString(),
                ],
            ];
        }

        $template = !empty($metadata['card_template_id'])
            ? CardTemplate::find((int) $metadata['card_template_id'])
            : null;

        return [
            'card_template_id' => $template?->id,
            'is_custom_design' => false,
            'card_design' => [
                'method' => 'template',
                'template_id' => $template?->id,
                'template_name' => $template?->name,
                'card_size' => $template?->card_size ?: '90x54',
                'front_image_url' => $template?->front_image_url,
                'back_image_url' => $template?->back_image_url,
                'front_layout' => $template?->front_layout,
                'back_layout' => $template?->back_layout,
                'data' => $data,
                'captured_at' => now()->toISOString(),
            ],
        ];
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
     * Create or refresh the Subscription record for a paid self-serve plan.
     *
     * Only basic/premium are handled here: free needs no subscription, and
     * business is an enquiry-driven, admin-approved path. Idempotent — a repeat
     * fulfilment call refreshes the existing active subscription instead of
     * creating a duplicate.
     */
    protected function syncSubscription(Order $order, User $user): void
    {
        $metadata = is_array($order->metadata) ? $order->metadata : [];
        $plan = $metadata['plan'] ?? $user->subscription_plan;

        if (!in_array($plan, ['basic', 'premium'], true)) {
            return;
        }

        $start = now();
        $end = now()->addYear();
        $amount = (float) $order->total;

        $subscription = Subscription::where('user_id', $user->id)
            ->whereIn('status', ['active', 'past_due'])
            ->latest()
            ->first();

        if ($subscription) {
            $subscription->update([
                'plan_name' => ucfirst($plan) . ' Plan',
                'plan_type' => $plan,
                'amount' => $amount,
                'status' => 'active',
                'current_period_end' => $end,
                'next_billing_date' => $end,
            ]);
        } else {
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'provider' => 'fiuu',
                'plan_name' => ucfirst($plan) . ' Plan',
                'plan_type' => $plan,
                'amount' => $amount,
                'currency' => $order->currency ?: config('payment.currency', 'MYR'),
                'interval' => 'yearly',
                'status' => 'active',
                'current_period_start' => $start,
                'current_period_end' => $end,
                'next_billing_date' => $end,
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
            ]);
        }

        // Link the order to the subscription and align the user's plan fields.
        $order->update(['subscription_id' => $subscription->id]);

        $user->update([
            'subscription_plan' => $plan,
            'subscription_active' => true,
            'subscription_start_date' => $user->subscription_start_date ?? $start,
            'subscription_end_date' => $end,
        ]);
    }

    /**
     * Seed values for a newly-created landing page, sourced from what the user
     * actually entered during checkout (order metadata) with the account's own
     * name/email as fallbacks. Only non-empty optional values are included so we
     * never write empty placeholders over a real profile.
     */
    protected function landingPageDefaults(Order $order, User $user): array
    {
        $metadata = is_array($order->metadata) ? $order->metadata : [];

        $cardInfo = $metadata['card_info'] ?? [];
        if (!is_array($cardInfo)) {
            $cardInfo = [];
        }

        $attributes = [
            'name' => $cardInfo['name'] ?? ($user->full_name ?: ($user->name ?? '')),
            'email' => $cardInfo['email'] ?? $user->email,
        ];

        $optional = [
            'title' => $cardInfo['position'] ?? null,
            'phone' => $cardInfo['contactNumber'] ?? ($order->shipping_phone ?: ($user->phone ?? null)),
            'website' => $cardInfo['website'] ?? null,
            'address' => $cardInfo['address'] ?? $this->formatShippingAddress($order),
            'bio' => $cardInfo['companyBackground'] ?? null,
            'company_logo' => $cardInfo['companyLogo'] ?? null,
        ];

        foreach ($optional as $field => $value) {
            if (!empty($value)) {
                $attributes[$field] = $value;
            }
        }

        return $attributes;
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
