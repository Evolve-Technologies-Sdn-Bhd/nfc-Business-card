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
        $this->middleware('auth:sanctum');
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
}

