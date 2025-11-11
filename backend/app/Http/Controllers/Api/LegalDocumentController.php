<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LegalDocumentController extends Controller
{
    /**
     * Get all legal documents (public)
     */
    public function index()
    {
        $documents = LegalDocument::with('updater:id,first_name,last_name')->get();

        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }

    /**
     * Get a specific document by type (public)
     */
    public function show($type)
    {
        $document = LegalDocument::where('type', $type)->first();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $document
        ]);
    }

    /**
     * Update or create a legal document (admin only)
     */
    public function update(Request $request, $type)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',
            'version' => 'nullable|string',
            'effective_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Validate type
        if (!in_array($type, ['terms_of_service', 'privacy_policy'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid document type'
            ], 400);
        }

        $document = LegalDocument::updateOrCreate(
            ['type' => $type],
            [
                'content' => $request->content,
                'version' => $request->version ?? '1.0',
                'effective_date' => $request->effective_date ?? now(),
                'updated_by' => $request->user()->id,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Document updated successfully',
            'data' => $document->load('updater:id,first_name,last_name')
        ]);
    }

    /**
     * Get Terms of Service (public)
     */
    public function getTerms()
    {
        $document = LegalDocument::getTermsOfService();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Terms of Service not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $document
        ]);
    }

    /**
     * Get Privacy Policy (public)
     */
    public function getPrivacy()
    {
        $document = LegalDocument::getPrivacyPolicy();

        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Privacy Policy not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $document
        ]);
    }
}
