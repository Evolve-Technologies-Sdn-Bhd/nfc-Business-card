<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A Business-plan enquiry submitted from the public website or plan selection.
 *
 * This replaces the previous "open a prefilled Gmail compose window" workaround
 * with a persisted, triageable record.
 */
class BusinessInquiry extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_QUOTED = 'quoted';
    public const STATUS_CONVERTED = 'converted';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'user_id',
        'company_name',
        'contact_person',
        'email',
        'phone',
        'company_address',
        'quota',
        'notes',
        'source',
        'status',
        'handled_by',
        'handled_at',
        'admin_notes',
        'metadata',
    ];

    protected $casts = [
        'quota' => 'integer',
        'handled_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * All statuses an enquiry may move through, in workflow order.
     *
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_CONTACTED,
            self::STATUS_QUOTED,
            self::STATUS_CONVERTED,
            self::STATUS_CLOSED,
        ];
    }

    /**
     * The registered user who submitted the enquiry, when authenticated.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The admin who last handled the enquiry.
     */
    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
