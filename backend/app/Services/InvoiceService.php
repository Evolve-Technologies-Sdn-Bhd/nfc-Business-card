<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class InvoiceService
{
    /**
     * Generate invoice from transaction
     */
    public function generateInvoiceFromTransaction(Transaction $transaction): Invoice
    {
        // Check if invoice already exists for this transaction
        $existingInvoice = Invoice::where('transaction_id', $transaction->id)->first();
        if ($existingInvoice) {
            return $existingInvoice;
        }

        $user = $transaction->user;
        
        // Prepare line items
        $lineItems = $this->prepareLineItems($transaction);
        
        // Calculate amounts
        $subtotal = $transaction->amount - $transaction->fee;
        $taxRate = config('payment.tax_rate', 0); // Can be configured per country
        $taxAmount = ($subtotal * $taxRate) / 100;
        $total = $transaction->amount;
        
        // Create invoice
        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'user_id' => $user->id,
            'transaction_id' => $transaction->id,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'discount_amount' => 0,
            'total_amount' => $total,
            'currency' => $transaction->currency,
            'line_items' => $lineItems,
            'status' => $transaction->status === 'succeeded' ? 'paid' : 'draft',
            'version' => 1,
            'company_details' => $this->getCompanyDetails(),
            'billing_details' => $this->getBillingDetails($user),
            'metadata' => [
                'transaction_id' => $transaction->transaction_id,
                'payment_method' => $transaction->payment_rail,
            ],
            'terms' => config('invoice.default_terms', 'Payment due upon receipt'),
            'issued_at' => $transaction->status === 'succeeded' ? now() : null,
            'paid_at' => $transaction->paid_at,
            'due_date' => now()->addDays(config('invoice.payment_due_days', 30)),
        ]);

        // Generate PDF
        $this->generatePDF($invoice);

        return $invoice->fresh();
    }

    /**
     * Generate PDF for invoice
     */
    public function generatePDF(Invoice $invoice): string
    {
        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
            'company' => $invoice->company_details,
            'customer' => $invoice->billing_details,
            'lineItems' => $invoice->line_items,
        ]);

        // Set PDF options
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

        // Generate filename
        $filename = $this->generatePdfFilename($invoice);
        $path = 'invoices/' . $invoice->invoice_number . '/' . $filename;

        // Save PDF
        Storage::put($path, $pdf->output());

        // Update invoice
        $invoice->update([
            'pdf_path' => $path,
            'pdf_filename' => $filename,
            'pdf_size' => Storage::size($path),
        ]);

        return $path;
    }

    /**
     * Regenerate invoice (creates new version)
     */
    public function regenerateInvoice(Invoice $invoice, $regeneratedBy = null): Invoice
    {
        // Create new version
        $newVersion = $invoice->replicate();
        $newVersion->parent_invoice_id = $invoice->id;
        $newVersion->version = $invoice->version + 1;
        $newVersion->pdf_path = null;
        $newVersion->pdf_filename = null;
        $newVersion->pdf_size = null;
        $newVersion->created_at = now();
        $newVersion->updated_at = now();
        $newVersion->save();

        // Generate PDF for new version
        $this->generatePDF($newVersion);

        return $newVersion;
    }

    /**
     * Send invoice via email
     */
    public function sendInvoiceEmail(Invoice $invoice): void
    {
        $invoice->user->notify(new \App\Notifications\InvoiceGenerated($invoice));
    }

    /**
     * Get signed download URL
     */
    public function getSignedDownloadUrl(Invoice $invoice, int $expiresInMinutes = 60): ?string
    {
        return $invoice->getSignedDownloadUrl($expiresInMinutes);
    }

    /**
     * Download invoice PDF
     */
    public function downloadInvoice(Invoice $invoice)
    {
        if (!$invoice->pdf_path || !Storage::exists($invoice->pdf_path)) {
            throw new Exception('Invoice PDF not found');
        }

        return Storage::download($invoice->pdf_path, $invoice->pdf_filename);
    }

    /**
     * Preview invoice PDF (inline)
     */
    public function previewInvoice(Invoice $invoice)
    {
        if (!$invoice->pdf_path || !Storage::exists($invoice->pdf_path)) {
            throw new Exception('Invoice PDF not found');
        }

        return response()->file(Storage::path($invoice->pdf_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $invoice->pdf_filename . '"',
        ]);
    }

    /**
     * Mark invoice as issued
     */
    public function markAsIssued(Invoice $invoice, $issuedBy = null): void
    {
        $invoice->markAsIssued($issuedBy);
        
        // Send email notification
        $this->sendInvoiceEmail($invoice);
    }

    /**
     * Mark invoice as cancelled
     */
    public function markAsCancelled(Invoice $invoice, $cancelledBy = null, $reason = null): void
    {
        $invoice->markAsCancelled($cancelledBy);
        
        // Add cancellation reason to metadata
        if ($reason) {
            $metadata = $invoice->metadata ?? [];
            $metadata['cancellation_reason'] = $reason;
            $metadata['cancelled_at'] = now()->toIso8601String();
            $invoice->update(['metadata' => $metadata]);
        }
    }

    /**
     * Prepare line items from transaction
     */
    protected function prepareLineItems(Transaction $transaction): array
    {
        $items = [];

        // Main item
        $items[] = [
            'description' => $transaction->description ?: 'Payment for NFC Business Card',
            'quantity' => 1,
            'unit_price' => $transaction->amount - $transaction->fee,
            'amount' => $transaction->amount - $transaction->fee,
        ];

        // Processing fee as separate line item
        if ($transaction->fee > 0) {
            $items[] = [
                'description' => 'Processing Fee (' . ucfirst($transaction->payment_rail) . ')',
                'quantity' => 1,
                'unit_price' => $transaction->fee,
                'amount' => $transaction->fee,
            ];
        }

        return $items;
    }

    /**
     * Get company details
     */
    protected function getCompanyDetails(): array
    {
        return [
            'name' => config('invoice.company.name', config('app.name')),
            'address' => config('invoice.company.address', '123 Business Street'),
            'city' => config('invoice.company.city', 'Kuala Lumpur'),
            'state' => config('invoice.company.state', 'Wilayah Persekutuan'),
            'postal_code' => config('invoice.company.postal_code', '50000'),
            'country' => config('invoice.company.country', 'Malaysia'),
            'phone' => config('invoice.company.phone', '+60 3-1234 5678'),
            'email' => config('invoice.company.email', 'billing@example.com'),
            'tax_id' => config('invoice.company.tax_id', 'SST-123456-78'),
            'logo_url' => config('invoice.company.logo_url'),
        ];
    }

    /**
     * Get billing details from user
     */
    protected function getBillingDetails(User $user): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
            'address' => $user->address ?? '',
            'city' => $user->city ?? '',
            'state' => $user->state ?? '',
            'postal_code' => $user->postal_code ?? '',
            'country' => $user->country ?? 'Malaysia',
        ];
    }

    /**
     * Generate PDF filename
     */
    protected function generatePdfFilename(Invoice $invoice): string
    {
        return sprintf(
            '%s-v%d.pdf',
            $invoice->invoice_number,
            $invoice->version
        );
    }
}
