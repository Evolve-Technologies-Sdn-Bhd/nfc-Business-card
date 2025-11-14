<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\NfcCard;
use App\Models\Analytics;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    protected $fileUploadService;
    protected $notificationService;

    public function __construct(FileUploadService $fileUploadService, NotificationService $notificationService)
    {
        $this->fileUploadService = $fileUploadService;
        $this->notificationService = $notificationService;
    }

    public function show($cardId)
    {
        // Find NFC card by card_id
        $nfcCard = NfcCard::where('card_id', $cardId)->firstOrFail();
        
        // Get the landing page for this card
        $landingPage = LandingPage::where('nfc_card_id', $nfcCard->id)
            ->where('is_active', true)
            ->with(['socialLinks' => function ($query) {
                $query->where('is_active', true);
            }])
            ->firstOrFail();

        // Track landing page view
        Analytics::create([
            'trackable_type' => LandingPage::class,
            'trackable_id' => $landingPage->id,
            'action' => 'landing_page_view',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $landingPage
        ]);
    }

    public function getProfile(Request $request)
    {
        $user = $request->user();

        // Get user's first NFC card
        $nfcCard = $user->nfcCards()->first();
        
        if (!$nfcCard) {
            return response()->json([
                'success' => false,
                'message' => 'No NFC card found. Please create one first.'
            ], 404);
        }

        // Get or create landing page for this card
        $landingPage = LandingPage::firstOrCreate(
            ['nfc_card_id' => $nfcCard->id],
            [
                'name' => $user->full_name ?? $user->email,
                'email' => $user->email,
                'is_active' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'landing_page' => $landingPage,
            'nfc_card' => $nfcCard
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        
        // Get user's first NFC card
        $nfcCard = $user->nfcCards()->first();
        
        if (!$nfcCard) {
            return response()->json([
                'success' => false,
                'message' => 'No NFC card found'
            ], 404);
        }
        
        $landingPage = $nfcCard->landingPage;
        
        if (!$landingPage) {
            return response()->json([
                'success' => false,
                'message' => 'No landing page found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:500',
            'email' => 'nullable|email',
            'contactNumber' => 'nullable|string|max:20',
            'website' => 'nullable|url',
            'address' => 'nullable|string|max:500',
            'theme' => 'nullable|in:minimal,modern,creative,professional,dark',
            'backgroundColor' => 'nullable|string|max:7',
            'textColor' => 'nullable|string|max:7',
            'font' => 'nullable|string|max:50',
            'buttonStyle' => 'nullable|string|max:50',
            'showWatermark' => 'nullable|boolean',
            'profileStyle' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Map frontend field names to database field names
        $data = $request->all();
        $mappedData = [
            'name' => $data['name'] ?? $landingPage->name,
            'title' => $data['position'] ?? $landingPage->title,
            'company_name' => $data['company'] ?? $landingPage->company_name,
            'bio' => $data['bio'] ?? $landingPage->bio,
            'email' => $data['email'] ?? $landingPage->email,
            'phone' => $data['contactNumber'] ?? $landingPage->phone,
            'website' => $data['website'] ?? $landingPage->website,
            'location' => $data['address'] ?? $landingPage->location,
            'theme' => $data['theme'] ?? $landingPage->theme,
            'background_color' => $data['backgroundColor'] ?? $landingPage->background_color,
            'text_color' => $data['textColor'] ?? $landingPage->text_color,
            'font' => $data['font'] ?? $landingPage->font,
            'button_style' => $data['buttonStyle'] ?? $landingPage->button_style,
            'show_watermark' => $data['showWatermark'] ?? $landingPage->show_watermark,
            'profile_style' => $data['profileStyle'] ?? $landingPage->profile_style,
        ];

        $landingPage->update($mappedData);

        // Send landing page updated notification
        $this->notificationService->create($request->user(), 'landing_page_updated', [
            'fields' => implode(', ', array_keys(array_filter($mappedData, function($value, $key) use ($landingPage) {
                return $landingPage->wasChanged($key);
            }, ARRAY_FILTER_USE_BOTH))),
        ]);

        return response()->json([
            'success' => true,
            'landing_page' => $landingPage
        ]);
    }

    // Slug functions removed - landing pages use card_id instead

    public function uploadProfileImage(Request $request)
    {
        Log::info('Landing page image upload started', [
            'user_id' => $request->user()->id,
            'has_file' => $request->hasFile('image'),
            'files' => $request->allFiles(),
            'nfc_card_id' => $request->input('nfc_card_id')
        ]);

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'nfc_card_id' => 'nullable|exists:nfc_cards,id',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $user = $request->user();

            // Get NFC card - either specified or user's first card
            if ($request->has('nfc_card_id')) {
                $nfcCard = $user->nfcCards()->find($request->input('nfc_card_id'));
                if (!$nfcCard) {
                    throw new \Exception('NFC card not found or does not belong to you');
                }
            } else {
                $nfcCard = $user->nfcCards()->first();
                if (!$nfcCard) {
                    throw new \Exception('No NFC card found');
                }
            }

            // Get or create landing page
            $landingPage = LandingPage::firstOrCreate(
                ['nfc_card_id' => $nfcCard->id],
                [
                    'name' => $user->full_name ?? $user->email,
                    'email' => $user->email,
                    'is_active' => true,
                ]
            );

            // Check if storage link exists
            if (!file_exists(public_path('storage'))) {
                Log::error('Storage link does not exist. Run: php artisan storage:link');
                throw new \Exception('Storage is not properly configured. Please contact support.');
            }

            // Delete old image if exists
            if ($landingPage->profile_image_path) {
                try {
                    $this->fileUploadService->deleteFile($landingPage->profile_image_path);

                    // Delete thumbnail too
                    $thumbnailPath = str_replace('profile-images/', 'profile-images/thumbnails/', $landingPage->profile_image_path);
                    $this->fileUploadService->deleteFile($thumbnailPath);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete old image', ['error' => $e->getMessage()]);
                }
            }

            // Upload new image
            $uploadResult = $this->fileUploadService->uploadProfileImage(
                $request->file('image'),
                $user->id
            );

            Log::info('Image uploaded successfully', ['result' => $uploadResult]);

            // Update landing page
            $landingPage->update([
                'profile_image' => $uploadResult['url'],
                'profile_image_path' => $uploadResult['path'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profile image uploaded successfully',
                'data' => [
                    'url' => $uploadResult['url'],
                    'thumbnail_url' => $uploadResult['thumbnail_url'],
                    'size' => $uploadResult['size'],
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Profile image upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadCompanyLogo(Request $request)
    {
        Log::info('Company logo upload started', [
            'user_id' => $request->user()->id,
            'has_file' => $request->hasFile('logo'),
            'files' => $request->allFiles(),
            'nfc_card_id' => $request->input('nfc_card_id')
        ]);

        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
            'nfc_card_id' => 'nullable|exists:nfc_cards,id',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $user = $request->user();

            // Get NFC card - either specified or user's first card
            if ($request->has('nfc_card_id')) {
                $nfcCard = $user->nfcCards()->find($request->input('nfc_card_id'));
                if (!$nfcCard) {
                    throw new \Exception('NFC card not found or does not belong to you');
                }
            } else {
                $nfcCard = $user->nfcCards()->first();
                if (!$nfcCard) {
                    throw new \Exception('No NFC card found');
                }
            }

            // Get or create landing page
            $landingPage = LandingPage::firstOrCreate(
                ['nfc_card_id' => $nfcCard->id],
                [
                    'name' => $user->full_name ?? $user->email,
                    'email' => $user->email,
                    'is_active' => true,
                ]
            );

            // Check if storage link exists
            if (!file_exists(public_path('storage'))) {
                Log::error('Storage link does not exist. Run: php artisan storage:link');
                throw new \Exception('Storage is not properly configured. Please contact support.');
            }

            // Delete old logo if exists
            if ($landingPage->company_logo_path) {
                try {
                    $this->fileUploadService->deleteFile($landingPage->company_logo_path);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete old logo', ['error' => $e->getMessage()]);
                }
            }

            // Upload new logo
            $uploadResult = $this->fileUploadService->uploadCompanyLogo(
                $request->file('logo'),
                $user->id
            );

            Log::info('Logo uploaded successfully', ['result' => $uploadResult]);

            // Update landing page
            $landingPage->update([
                'company_logo' => $uploadResult['url'],
                'company_logo_path' => $uploadResult['path'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Company logo uploaded successfully',
                'data' => [
                    'url' => $uploadResult['url'],
                    'size' => $uploadResult['size'],
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Company logo upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload logo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteProfileImage(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = $request->user();
            $nfcCard = $user->nfcCards()->first();
            
            if (!$nfcCard || !$nfcCard->landingPage) {
                throw new \Exception('No landing page found');
            }
            
            $landingPage = $nfcCard->landingPage;

            if ($landingPage->profile_image_path) {
                // Delete main image
                $this->fileUploadService->deleteFile($landingPage->profile_image_path);

                // Delete thumbnail
                $thumbnailPath = str_replace('profile-images/', 'profile-images/thumbnails/', $landingPage->profile_image_path);
                $this->fileUploadService->deleteFile($thumbnailPath);

                // Update landing page
                $landingPage->update([
                    'profile_image' => null,
                    'profile_image_path' => null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profile image deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to delete profile image', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteCompanyLogo(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = $request->user();
            $nfcCard = $user->nfcCards()->first();
            
            if (!$nfcCard || !$nfcCard->landingPage) {
                throw new \Exception('No landing page found');
            }
            
            $landingPage = $nfcCard->landingPage;

            if ($landingPage->company_logo_path) {
                $this->fileUploadService->deleteFile($landingPage->company_logo_path);

                $landingPage->update([
                    'company_logo' => null,
                    'company_logo_path' => null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Company logo deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to delete company logo', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete logo: ' . $e->getMessage()
            ], 500);
        }
    }
}
