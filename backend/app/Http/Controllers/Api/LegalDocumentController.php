<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

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

    /**
     * Download Terms of Service as PDF (admin only)
     */
    public function downloadTerms()
    {
        $pdfPath = 'public/legal-documents/terms-of-service.pdf';

        if (!Storage::exists($pdfPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Terms of Service PDF not found. Please upload a PDF file first.'
            ], 404);
        }

        return Storage::download($pdfPath, 'Terms-of-Service.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Download Privacy Policy as PDF (admin only)
     */
    public function downloadPrivacy()
    {
        $pdfPath = 'public/legal-documents/privacy-policy.pdf';

        if (!Storage::exists($pdfPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Privacy Policy PDF not found. Please upload a PDF file first.'
            ], 404);
        }

        return Storage::download($pdfPath, 'Privacy-Policy.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Upload Terms of Service PDF (admin only)
     */
    public function uploadTermsPdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pdf' => 'required|file|mimes:pdf|max:10240', // max 10MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('pdf');
            $path = $file->storeAs('public/legal-documents', 'terms-of-service.pdf');

            return response()->json([
                'success' => true,
                'message' => 'Terms of Service PDF uploaded successfully',
                'path' => Storage::url($path)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload Privacy Policy PDF (admin only)
     */
    public function uploadPrivacyPdf(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pdf' => 'required|file|mimes:pdf|max:10240', // max 10MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('pdf');
            $path = $file->storeAs('public/legal-documents', 'privacy-policy.pdf');

            return response()->json([
                'success' => true,
                'message' => 'Privacy Policy PDF uploaded successfully',
                'path' => Storage::url($path)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if PDF exists for a document type (admin only)
     */
    public function checkPdfStatus($type)
    {
        $fileName = $type === 'terms' ? 'terms-of-service.pdf' : 'privacy-policy.pdf';
        $pdfPath = 'public/legal-documents/' . $fileName;

        $exists = Storage::exists($pdfPath);
        
        $fileInfo = null;
        if ($exists) {
            $fileInfo = [
                'size' => Storage::size($pdfPath),
                'last_modified' => Storage::lastModified($pdfPath),
                'url' => Storage::url($pdfPath)
            ];
        }

        return response()->json([
            'success' => true,
            'exists' => $exists,
            'file_info' => $fileInfo
        ]);
    }
}
