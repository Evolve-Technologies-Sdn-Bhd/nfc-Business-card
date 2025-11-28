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
        $pdfPath = 'legal-documents/terms-of-service.pdf';

        if (!Storage::disk('public')->exists($pdfPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Terms of Service PDF not found. Please upload a PDF file first.'
            ], 404);
        }

        return Storage::disk('public')->download($pdfPath, 'Terms-of-Service.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Download Privacy Policy as PDF (admin only)
     */
    public function downloadPrivacy()
    {
        $pdfPath = 'legal-documents/privacy-policy.pdf';

        if (!Storage::disk('public')->exists($pdfPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Privacy Policy PDF not found. Please upload a PDF file first.'
            ], 404);
        }

        return Storage::disk('public')->download($pdfPath, 'Privacy-Policy.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Upload Terms of Service PDF (admin only)
     */
    public function uploadTermsPdf(Request $request)
    {
        \Log::info('Upload Terms PDF called', [
            'has_file' => $request->hasFile('pdf'),
            'all_files' => array_keys($request->allFiles()),
            'all_data' => array_keys($request->all())
        ]);

        $validator = Validator::make($request->all(), [
            'pdf' => 'required|file|mimes:pdf|max:10240', // max 10MB
        ]);

        if ($validator->fails()) {
            \Log::error('Upload validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('pdf');
            \Log::info('File received', [
                'original_name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType()
            ]);
            
            $originalName = $file->getClientOriginalName();
            $path = $file->storeAs('legal-documents', 'terms-of-service.pdf', 'public');
            
            // Store original filename in metadata file
            $metadataPath = 'legal-documents/terms-of-service-metadata.json';
            $metadata = [
                'original_filename' => $originalName,
                'uploaded_at' => now()->toIso8601String(),
                'size' => $file->getSize()
            ];
            Storage::disk('public')->put($metadataPath, json_encode($metadata));
            
            \Log::info('File stored', [
                'path' => $path,
                'original_name' => $originalName,
                'full_path' => storage_path('app/public/' . $path),
                'exists' => Storage::disk('public')->exists($path)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Terms of Service PDF uploaded successfully',
                'path' => Storage::disk('public')->url($path),
                'original_filename' => $originalName,
                'size' => $file->getSize()
            ]);
        } catch (\Exception $e) {
            \Log::error('Upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
            $originalName = $file->getClientOriginalName();
            $path = $file->storeAs('legal-documents', 'privacy-policy.pdf', 'public');
            
            // Store original filename in metadata file
            $metadataPath = 'legal-documents/privacy-policy-metadata.json';
            $metadata = [
                'original_filename' => $originalName,
                'uploaded_at' => now()->toIso8601String(),
                'size' => $file->getSize()
            ];
            Storage::disk('public')->put($metadataPath, json_encode($metadata));

            return response()->json([
                'success' => true,
                'message' => 'Privacy Policy PDF uploaded successfully',
                'path' => Storage::disk('public')->url($path),
                'original_filename' => $originalName,
                'size' => $file->getSize()
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
        $metadataFileName = $type === 'terms' ? 'terms-of-service-metadata.json' : 'privacy-policy-metadata.json';
        $pdfPath = 'legal-documents/' . $fileName;
        $metadataPath = 'legal-documents/' . $metadataFileName;

        $exists = Storage::disk('public')->exists($pdfPath);
        
        $fileInfo = null;
        if ($exists) {
            $fileInfo = [
                'size' => Storage::disk('public')->size($pdfPath),
                'last_modified' => Storage::disk('public')->lastModified($pdfPath),
                'url' => Storage::disk('public')->url($pdfPath),
                'original_filename' => $fileName // Default to stored filename
            ];
            
            // Try to load metadata if it exists
            if (Storage::disk('public')->exists($metadataPath)) {
                try {
                    $metadata = json_decode(Storage::disk('public')->get($metadataPath), true);
                    if (isset($metadata['original_filename'])) {
                        $fileInfo['original_filename'] = $metadata['original_filename'];
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to read metadata file', ['error' => $e->getMessage()]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'exists' => $exists,
            'file_info' => $fileInfo
        ]);
    }

    /**
     * View Terms of Service PDF (public - no auth required)
     */
    public function viewTermsPdf()
    {
        $pdfPath = 'legal-documents/terms-of-service.pdf';

        if (!Storage::disk('public')->exists($pdfPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Terms of Service PDF not found.'
            ], 404);
        }

        return response()->file(
            storage_path('app/public/' . $pdfPath),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="Terms-of-Service.pdf"'
            ]
        );
    }

    /**
     * View Privacy Policy PDF (public - no auth required)
     */
    public function viewPrivacyPdf()
    {
        $pdfPath = 'legal-documents/privacy-policy.pdf';

        if (!Storage::disk('public')->exists($pdfPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Privacy Policy PDF not found.'
            ], 404);
        }

        return response()->file(
            storage_path('app/public/' . $pdfPath),
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="Privacy-Policy.pdf"'
            ]
        );
    }
}
