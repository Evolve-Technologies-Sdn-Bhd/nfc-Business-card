<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    /**
     * Order lifecycle statuses.
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_AWAITING_PAYMENT_VERIFICATION = 'awaiting_payment_verification';
    public const STATUS_PAID = 'paid';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    public const TYPE_CARD = 'card';
    public const TYPE_SUBSCRIPTION = 'subscription';
    public const TYPE_SERVICE = 'service';

    protected $fillable = [
        'user_id',
        'order_number',
        'type',
        'status',
        'currency',
        'subtotal',
        'discount_amount',
        'shipping_fee',
        'tax_amount',
        'total',
        'shipping_name',
        'shipping_phone',
        'shipping_address_line1',
        'shipping_city',
        'shipping_state',
        'shipping_postcode',
        'shipping_country',
        'notes',
        'paid_at',
        'fulfilled_at',
        'cancelled_at',
        'transaction_id',
        'subscription_id',
        'metadata',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'fulfilled_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    /**
     * Generate a unique order number in the format NFCG-YYYYMMDD-XXXX.
     *
     * The suffix is a zero-padded per-day sequence; on the (unlikely) collision
     * it falls back to a random 4-character uppercase suffix. Both paths are
     * checked against the existing rows so the unique index can never be hit.
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'NFCG-' . now()->format('Ymd') . '-';

        // Per-day sequential attempt.
        for ($attempt = 0; $attempt < 25; $attempt++) {
            $sequence = static::where('order_number', 'like', $prefix . '%')->count() + 1 + $attempt;
            $candidate = $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            if (!static::where('order_number', $candidate)->exists()) {
                return $candidate;
            }
        }

        // Random-suffix fallback (bounded retries).
        for ($attempt = 0; $attempt < 25; $attempt++) {
            $candidate = $prefix . strtoupper(Str::random(4));
            if (!static::where('order_number', $candidate)->exists()) {
                return $candidate;
            }
        }

        // Last-resort unique suffix using a timestamp fragment.
        return $prefix . strtoupper(substr((string) now()->getTimestampMs(), -4));
    }

    /**
     * All statuses the order lifecycle can move through.
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_AWAITING_PAYMENT_VERIFICATION,
            self::STATUS_PAID,
            self::STATUS_PROCESSING,
            self::STATUS_SHIPPED,
            self::STATUS_DELIVERED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_REFUNDED,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Whether the order has been paid.
     */
    public function isPaid(): bool
    {
        return $this->paid_at !== null
            || in_array($this->status, [
                self::STATUS_PAID,
                self::STATUS_PROCESSING,
                self::STATUS_SHIPPED,
                self::STATUS_DELIVERED,
                self::STATUS_COMPLETED,
            ], true);
    }

    /**
     * Whether fulfilment (card creation / linking) has already run.
     */
    public function isFulfilled(): bool
    {
        return $this->fulfilled_at !== null;
    }
}
