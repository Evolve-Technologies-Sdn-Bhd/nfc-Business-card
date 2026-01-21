<?php

namespace App\Http\Controllers;

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
        // The routes already have 'auth:sanctum' middleware applied in routes/api.php
    }

    /**
     * Display a listing of the user's invoices.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Invoice::forUser($user->id)
            ->with(['transaction'])
            ->latest('created_at');

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        // Search by invoice number
        if ($request->has('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }

        // Only show latest versions by default
        if (!$request->has('all_versions')) {
            $query->latestVersions();
        }

        $perPage = $request->input('per_page', 15);
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
        $user = Auth::user();

        // Check if user owns this invoice
        if ($invoice->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this invoice.',
            ], 403);
        }

        $invoice->load(['transaction', 'user', 'parentInvoice', 'regeneratedVersions']);

        return response()->json([
            'success' => true,
            'data' => $invoice,
        ]);
    }

    /**
     * Download invoice PDF.
     */
    public function download(Request $request, Invoice $invoice)
    {
        $user = Auth::user();

        // Verify signature if using signed URLs
        if (config('invoice.security.use_signed_urls', true)) {
            if (!$request->hasValidSignature()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired download link.',
                ], 403);
            }
        }

        // Check if user owns this invoice
        if ($invoice->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this invoice.',
            ], 403);
        }

        return $this->invoiceService->downloadInvoice($invoice);
    }

    /**
     * Preview invoice PDF in browser.
     */
    public function preview(Request $request, Invoice $invoice)
    {
        $user = Auth::user();

        // Check if user owns this invoice
        if ($invoice->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this invoice.',
            ], 403);
        }

        return $this->invoiceService->previewInvoice($invoice);
    }

    /**
     * Get invoice statistics for the authenticated user.
     */
    public function statistics()
    {
        $user = Auth::user();

        $stats = [
            'total_invoices' => Invoice::forUser($user->id)->count(),
            'paid_invoices' => Invoice::forUser($user->id)->paid()->count(),
            'pending_invoices' => Invoice::forUser($user->id)->issued()->count(),
            'total_amount' => Invoice::forUser($user->id)->sum('total_amount'),
            'paid_amount' => Invoice::forUser($user->id)->paid()->sum('total_amount'),
            'pending_amount' => Invoice::forUser($user->id)->issued()->sum('total_amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Send invoice via email to the authenticated user.
     */
    public function sendEmail(Invoice $invoice)
    {
        $user = Auth::user();

        // Check if user owns this invoice
        if ($invoice->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this invoice.',
            ], 403);
        }

        try {
            $this->invoiceService->sendInvoiceEmail($invoice);

            return response()->json([
                'success' => true,
                'message' => 'Invoice sent to your email successfully.',
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send invoice email. Please try again later.',
            ], 500);
        }
    }

    /**
     * Generate and download invoice by order ID.
     * Creates invoice if it doesn't exist, then returns PDF for immediate download.
     */
    public function downloadByOrderId(Request $request)
    {
        $user = Auth::user();
        $orderId = $request->input('order_id');
        $sendEmail = $request->boolean('send_email', false);

        if (!$orderId) {
            return response()->json([
                'success' => false,
                'message' => 'Order ID is required.',
            ], 400);
        }

        // Find transaction by provider_transaction_id (order ID)
        $transaction = \App\Models\Transaction::where('provider_transaction_id', $orderId)
            ->where('user_id', $user->id)
            ->first();

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found.',
            ], 404);
        }

        // Check if payment was successful
        if ($transaction->status !== 'succeeded') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice can only be downloaded for successful payments.',
            ], 400);
        }

        try {
            // Check if invoice already exists for this transaction
            $invoice = Invoice::where('transaction_id', $transaction->id)->first();

            // If no invoice exists, generate one now
            if (!$invoice) {
                $invoice = $this->invoiceService->generateInvoiceFromTransaction($transaction);
            }

            // If email requested, send it asynchronously
            if ($sendEmail) {
                try {
                    $this->invoiceService->sendInvoiceEmail($invoice);
                } catch (\Exception $e) {
                    // Log but don't fail the download
                    \Log::warning('Failed to send invoice email', [
                        'invoice_id' => $invoice->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Return PDF for immediate download
            return $this->invoiceService->downloadInvoice($invoice);

        } catch (\Exception $e) {
            \Log::error('Failed to generate/download invoice', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate invoice. Please try again later.',
            ], 500);
        }
    }
}

