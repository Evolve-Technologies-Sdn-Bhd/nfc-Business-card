<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
        // Note: Middleware should be applied at route level in Laravel 11+
        // The routes already have 'auth:sanctum' and 'admin' middleware applied in routes/api.php
    }

    /**
     * Display a listing of all invoices.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['user', 'transaction'])
            ->latest('created_at');

        // Apply filters
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        // Search by invoice number or user email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('email', 'like', '%' . $search . '%')
                            ->orWhere('name', 'like', '%' . $search . '%');
                    });
            });
        }

        // Filter by version type
        if ($request->input('latest_only', true)) {
            $query->latestVersions();
        }

        $perPage = $request->input('per_page', 20);
        $invoices = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'user',
            'transaction',
            'parentInvoice',
            'regeneratedVersions',
            'issuedBy',
            'cancelledBy'
        ]);

        return response()->json([
            'success' => true,
            'data' => $invoice,
        ]);
    }

    /**
     * Regenerate an existing invoice.
     */
    public function regenerate(Request $request, Invoice $invoice)
    {
        $request->validate([
            'reason' => 'required_if:' . config('invoice.versioning.require_regeneration_reason', false) . ',true|string|max:500',
        ]);

        $user = Auth::user();

        try {
            $newInvoice = $this->invoiceService->regenerateInvoice($invoice, $user->id);

            // Add reason to metadata if provided
            if ($request->has('reason')) {
                $metadata = $newInvoice->metadata ?? [];
                $metadata['regeneration_reason'] = $request->reason;
                $newInvoice->metadata = $metadata;
                $newInvoice->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Invoice regenerated successfully.',
                'data' => $newInvoice->load(['user', 'transaction', 'parentInvoice']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to regenerate invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark invoice as issued.
     */
    public function markAsIssued(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Only draft invoices can be marked as issued.',
            ], 422);
        }

        $user = Auth::user();

        try {
            $this->invoiceService->markAsIssued($invoice, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Invoice marked as issued and email sent.',
                'data' => $invoice->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark invoice as issued: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(Request $request, Invoice $invoice)
    {
        $request->validate([
            'paid_at' => 'nullable|date',
            'payment_method' => 'nullable|string|max:50',
            'transaction_id' => 'nullable|string|max:100',
        ]);

        if (!in_array($invoice->status, ['issued', 'draft'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only issued or draft invoices can be marked as paid.',
            ], 422);
        }

        try {
            $invoice->markAsPaid($request->input('paid_at'));

            // Add payment metadata
            if ($request->has('payment_method') || $request->has('transaction_id')) {
                $metadata = $invoice->metadata ?? [];
                if ($request->has('payment_method')) {
                    $metadata['payment_method'] = $request->payment_method;
                }
                if ($request->has('transaction_id')) {
                    $metadata['transaction_id'] = $request->transaction_id;
                }
                $invoice->metadata = $metadata;
                $invoice->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Invoice marked as paid.',
                'data' => $invoice->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark invoice as paid: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel an invoice.
     */
    public function cancel(Request $request, Invoice $invoice)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if (!$invoice->isCancellable()) {
            return response()->json([
                'success' => false,
                'message' => 'This invoice cannot be cancelled. Only draft or issued invoices can be cancelled.',
            ], 422);
        }

        $user = Auth::user();

        try {
            $this->invoiceService->markAsCancelled($invoice, $user->id, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'Invoice cancelled successfully.',
                'data' => $invoice->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel invoice: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resend invoice email.
     */
    public function resend(Invoice $invoice)
    {
        if ($invoice->status === 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot send email for draft invoices. Mark as issued first.',
            ], 422);
        }

        try {
            $this->invoiceService->sendInvoiceEmail($invoice);

            return response()->json([
                'success' => true,
                'message' => 'Invoice email sent successfully.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send invoice email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get invoice statistics.
     */
    public function statistics(Request $request)
    {
        $query = Invoice::query();

        // Filter by date range if provided
        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        $stats = [
            'total_invoices' => (clone $query)->count(),
            'draft_invoices' => (clone $query)->where('status', 'draft')->count(),
            'issued_invoices' => (clone $query)->issued()->count(),
            'paid_invoices' => (clone $query)->paid()->count(),
            'cancelled_invoices' => (clone $query)->cancelled()->count(),
            'total_amount' => (clone $query)->sum('total_amount'),
            'paid_amount' => (clone $query)->paid()->sum('total_amount'),
            'pending_amount' => (clone $query)->issued()->sum('total_amount'),
            'average_invoice_amount' => (clone $query)->avg('total_amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Download invoice PDF (admin access).
     */
    public function download(Invoice $invoice)
    {
        return $this->invoiceService->downloadInvoice($invoice);
    }

    /**
     * Preview invoice PDF in browser (admin access).
     */
    public function preview(Invoice $invoice)
    {
        return $this->invoiceService->previewInvoice($invoice);
    }
}

