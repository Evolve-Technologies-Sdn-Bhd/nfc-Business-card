<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'user_id',
        'transaction_id',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'line_items',
        'status',
        'version',
        'parent_invoice_id',
        'pdf_path',
        'pdf_filename',
        'pdf_size',
        'company_details',
        'billing_details',
        'metadata',
        'notes',
        'terms',
        'issued_at',
        'paid_at',
        'cancelled_at',
        'due_date',
        'issued_by',
        'cancelled_by',
    ];

    protected $casts = [
        'line_items' => 'array',
        'company_details' => 'array',
        'billing_details' => 'array',
        'metadata' => 'array',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'issued_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'due_date' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function parentInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'parent_invoice_id');
    }

    public function regeneratedVersions(): HasMany
    {
        return $this->hasMany(Invoice::class, 'parent_invoice_id');
    }

    /**
     * Scopes
     */
    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeLatestVersions($query)
    {
        // Latest versions are those that have NOT been regenerated (i.e., are not parents of another invoice)
        // Wait, if an invoice is regenerated, it becomes a parent.
        // So the "latest" version is the one that is NOT a parent.
        // But the relationship is: regeneratedVersions() -> hasMany(Invoice::class, 'parent_invoice_id')
        // So we want invoices that do NOT have any children in this relation.
        return $query->doesntHave('regeneratedVersions');
    }

    /**
     * Helper Methods
     */
    public function markAsIssued($issuedBy = null): void
    {
        $this->update([
            'status' => 'issued',
            'issued_at' => now(),
            'issued_by' => $issuedBy,
        ]);
    }

    public function markAsPaid($paidAt = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => $paidAt ?? now(),
        ]);
    }

    public function markAsCancelled($cancelledBy = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $cancelledBy,
        ]);
    }

    public function markAsRefunded(): void
    {
        $this->update(['status' => 'refunded']);
    }

    /**
     * Check if invoice is editable
     */
    public function isEditable(): bool
    {
        return in_array($this->status, ['draft']);
    }

    /**
     * Check if invoice can be cancelled
     */
    public function isCancellable(): bool
    {
        return in_array($this->status, ['draft', 'issued']);
    }

    /**
     * Get PDF download URL (signed)
     */
    public function getSignedDownloadUrl($expiresInMinutes = 60): ?string
    {
        if (!$this->pdf_path || !Storage::exists($this->pdf_path)) {
            return null;
        }

        return \URL::temporarySignedRoute(
            'invoices.download',
            now()->addMinutes($expiresInMinutes),
            ['invoice' => $this->id]
        );
    }

    /**
     * Get PDF preview URL
     */
    public function getPdfPreviewUrl(): ?string
    {
        if (!$this->pdf_path) {
            return null;
        }

        return route('invoices.preview', ['invoice' => $this->id]);
    }

    /**
     * Delete PDF file when invoice is deleted
     */
    protected static function booted()
    {
        static::deleting(function ($invoice) {
            if ($invoice->pdf_path && Storage::exists($invoice->pdf_path)) {
                Storage::delete($invoice->pdf_path);
            }
        });
    }

    /**
     * Generate next invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $prefix = 'INV-' . $year . '-';

        $lastInvoice = static::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -5);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }
}
