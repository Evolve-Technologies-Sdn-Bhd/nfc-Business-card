<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\BusinessInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Admin endpoints for reviewing and triaging Business-plan enquiries.
 */
class BusinessInquiryController extends Controller
{
    /**
     * Paginated, filterable list of enquiries.
     *
     * GET /api/admin/business-inquiries
     */
    public function index(Request $request)
    {
        try {
            $query = BusinessInquiry::query()->with([
                'user:id,first_name,last_name,email',
                'handledBy:id,first_name,last_name',
            ]);

            if ($status = $request->query('status')) {
                $query->where('status', $status);
            }

            if ($search = $request->query('search')) {
                $query->where(function ($q) use ($search) {
                    $q->where('company_name', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $perPage = (int) $request->query('per_page', 20);
            $perPage = max(1, min(100, $perPage));

            $inquiries = $query->orderByDesc('created_at')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $inquiries,
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin BusinessInquiryController::index error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch business enquiries.',
            ], 500);
        }
    }

    /**
     * Counts per status for the dashboard badge / overview.
     *
     * GET /api/admin/business-inquiries/statistics
     */
    public function statistics()
    {
        try {
            $counts = BusinessInquiry::query()
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $byStatus = [];
            foreach (BusinessInquiry::statuses() as $status) {
                $byStatus[$status] = (int) ($counts[$status] ?? 0);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => (int) $counts->sum(),
                    'by_status' => $byStatus,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin BusinessInquiryController::statistics error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch business enquiry statistics.',
            ], 500);
        }
    }

    /**
     * A single enquiry with its relations.
     *
     * GET /api/admin/business-inquiries/{inquiry}
     */
    public function show(BusinessInquiry $inquiry)
    {
        $inquiry->load([
            'user:id,first_name,last_name,email',
            'handledBy:id,first_name,last_name',
        ]);

        return response()->json([
            'success' => true,
            'data' => $inquiry,
        ]);
    }

    /**
     * Update an enquiry's triage state.
     *
     * PUT /api/admin/business-inquiries/{inquiry}
     * Body: { status?: string, admin_notes?: string }
     *
     * Moving an enquiry off "new" stamps the handling admin and timestamp so the
     * workflow is auditable.
     */
    public function update(Request $request, BusinessInquiry $inquiry)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|string|in:' . implode(',', BusinessInquiry::statuses()),
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();

            if (array_key_exists('admin_notes', $data)) {
                $inquiry->admin_notes = $data['admin_notes'];
            }

            if (array_key_exists('status', $data) && $data['status'] !== $inquiry->status) {
                $inquiry->status = $data['status'];

                if ($inquiry->handled_at === null && $inquiry->status !== BusinessInquiry::STATUS_NEW) {
                    $inquiry->handled_by = $request->user()->id;
                    $inquiry->handled_at = now();
                }
            }

            $inquiry->save();

            $inquiry->load([
                'user:id,first_name,last_name,email',
                'handledBy:id,first_name,last_name',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Enquiry updated.',
                'data' => $inquiry,
            ]);
        } catch (\Throwable $e) {
            Log::error('Admin BusinessInquiryController::update error', [
                'error' => $e->getMessage(),
                'inquiry_id' => $inquiry->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update the business enquiry.',
            ], 500);
        }
    }
}
