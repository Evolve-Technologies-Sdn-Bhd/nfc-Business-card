<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Public endpoint for submitting a Business-plan enquiry.
 *
 * The submission is persisted as a BusinessInquiry so admins can triage it from
 * the admin panel. Works both for anonymous visitors (homepage form) and for
 * logged-in users (plan selection) — if a valid token is present the enquiry is
 * linked to that account, otherwise it stays anonymous.
 */
class BusinessInquiryController extends Controller
{
    /**
     * Store a new Business enquiry.
     *
     * POST /api/business-inquiries
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company_address' => 'nullable|string|max:2000',
            'quota' => 'nullable|integer|min:1|max:100000',
            'notes' => 'nullable|string|max:5000',
            'source' => 'nullable|string|max:50',
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
            $user = $request->user('sanctum');

            $inquiry = BusinessInquiry::create([
                'user_id' => $user?->id,
                'company_name' => $data['company_name'],
                'contact_person' => $data['contact_person'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'company_address' => $data['company_address'] ?? null,
                'quota' => $data['quota'] ?? null,
                'notes' => $data['notes'] ?? null,
                'source' => $data['source'] ?? 'homepage',
                'status' => BusinessInquiry::STATUS_NEW,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Your enquiry has been received. Our team will contact you shortly.',
                'data' => [
                    'id' => $inquiry->id,
                    'status' => $inquiry->status,
                ],
            ], 201);
        } catch (\Throwable $e) {
            Log::error('BusinessInquiryController::store error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unable to submit your enquiry right now. Please try again later.',
            ], 500);
        }
    }
}
