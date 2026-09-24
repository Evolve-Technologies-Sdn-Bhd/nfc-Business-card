<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LandingPage;
use App\Models\NfcCard;
use App\Models\Analytics;
use App\Models\User;
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

            // Validate storage configuration
            $storageReady = true;
            $storageIssues = [];

            try {
                $publicDisk = Storage::disk('public');
                if (!$publicDisk) {
                    $storageReady = false;
                    $storageIssues[] = 'Public storage disk is not configured';
                }
            } catch (\Exception $e) {
                $storageReady = false;
                $storageIssues[] = 'Storage driver error: ' . $e->getMessage();
            }

            if (!file_exists(public_path('storage'))) {
                Log::warning('Storage symbolic link missing. Images saved but may not be accessible via URL. Run: php artisan storage:link');
                $storageIssues[] = 'Storage link not created';
            } else {
                $targetPath = storage_path('app/public');
                $publicStoragePath = public_path('storage');
                if (is_link($publicStoragePath) && readlink($publicStoragePath) !== realpath($targetPath)) {
                    Log::warning('Storage symlink points to wrong target', [
                        'link_target' => readlink($publicStoragePath),
                        'expected' => realpath($targetPath)
                    ]);
                }
            }

            if (!$storageReady) {
                Log::error('Storage configuration issues', ['issues' => $storageIssues]);
                throw new \Exception('Storage is not properly configured. Please contact support. Issues: ' . implode(', ', $storageIssues));
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

            // Validate storage configuration
            $storageReady = true;
            $storageIssues = [];

            try {
                $publicDisk = Storage::disk('public');
                if (!$publicDisk) {
                    $storageReady = false;
                    $storageIssues[] = 'Public storage disk is not configured';
                }
            } catch (\Exception $e) {
                $storageReady = false;
                $storageIssues[] = 'Storage driver error: ' . $e->getMessage();
            }

            if (!file_exists(public_path('storage'))) {
                Log::warning('Storage symbolic link missing. Images saved but may not be accessible via URL. Run: php artisan storage:link');
                $storageIssues[] = 'Storage link not created';
            } else {
                $targetPath = storage_path('app/public');
                $publicStoragePath = public_path('storage');
                if (is_link($publicStoragePath) && readlink($publicStoragePath) !== realpath($targetPath)) {
                    Log::warning('Storage symlink points to wrong target', [
                        'link_target' => readlink($publicStoragePath),
                        'expected' => realpath($targetPath)
                    ]);
                }
            }

            if (!$storageReady) {
                Log::error('Storage configuration issues', ['issues' => $storageIssues]);
                throw new \Exception('Storage is not properly configured. Please contact support. Issues: ' . implode(', ', $storageIssues));
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

    public function uploadCoverBanner(Request $request)
    {
        Log::info('Cover banner upload started', [
            'user_id' => $request->user()->id,
            'has_file' => $request->hasFile('image'),
            'nfc_card_id' => $request->input('nfc_card_id')
        ]);

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max for banner
            'nfc_card_id' => 'nullable|exists:nfc_cards,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $user = $request->user();

            // Get NFC card
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

            // Delete old banner if exists
            if ($landingPage->cover_banner_path) {
                try {
                    $this->fileUploadService->deleteFile($landingPage->cover_banner_path);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete old cover banner', ['error' => $e->getMessage()]);
                }
            }

            // Upload new banner - use same method as profile image but to different folder
            $file = $request->file('image');
            $fileName = 'banner_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('cover-banners', $fileName, 'public');
            $url = asset('storage/' . $path);

            Log::info('Cover banner uploaded successfully', ['path' => $path, 'url' => $url]);

            // Update landing page
            $landingPage->update([
                'cover_banner' => $url,
                'cover_banner_path' => $path,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cover banner uploaded successfully',
                'data' => [
                    'url' => $url,
                    'size' => $file->getSize(),
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Cover banner upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload cover banner: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteCoverBanner(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = $request->user();
            $nfcCard = $user->nfcCards()->first();
            
            if (!$nfcCard || !$nfcCard->landingPage) {
                throw new \Exception('No landing page found');
            }
            
            $landingPage = $nfcCard->landingPage;

            if ($landingPage->cover_banner_path) {
                $this->fileUploadService->deleteFile($landingPage->cover_banner_path);

                $landingPage->update([
                    'cover_banner' => null,
                    'cover_banner_path' => null,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cover banner deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to delete cover banner', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete cover banner: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get business team members with their landing page URLs
     * Returns employees under the current Business Account
     */
    public function getBusinessTeamMembers(Request $request)
    {
        try {
            $user = $request->user();
            
            Log::info('getBusinessTeamMembers called', [
                'user_id' => $user->id,
                'subscription_plan' => $user->subscription_plan,
                'parent_business_id' => $user->parent_business_id,
                'isBusinessAccount' => $user->isBusinessAccount(),
            ]);
            
            // Only Business Account owners can see their employees
            if (!$user->isBusinessAccount()) {
                Log::info('User is not a Business Account owner');
                return response()->json([
                    'success' => true,
                    'message' => 'Only Business Account owners can view employees',
                    'data' => [],
                    'total' => 0,
                ]);
            }
            
            // Get all employees under current user's business account
            // Same as QuotaController::getEmployees
            $employees = $user->employees()
                ->with(['nfcCards.landingPage'])
                ->get();
            
            Log::info('Employees found', [
                'count' => $employees->count(),
                'employee_ids' => $employees->pluck('id')->toArray(),
            ]);
            
            $teamMembers = [];
            
            foreach ($employees as $employee) {
                // If employee has NFC cards, show each card
                if ($employee->nfcCards->count() > 0) {
                    foreach ($employee->nfcCards as $card) {
                        $landingPage = $card->landingPage;
                        // Use landing page data if available, otherwise use employee data
                        $name = ($landingPage && $landingPage->name) ? $landingPage->name : $employee->full_name;
                        $role = ($landingPage && $landingPage->title) ? $landingPage->title : ($employee->job_title ?? 'Team Member');
                        $profileImage = ($landingPage && $landingPage->profile_image) ? $landingPage->profile_image : null;
                        
                        $teamMembers[] = [
                            'id' => $employee->id,
                            'user_id' => $employee->id,
                            'name' => $name,
                            'role' => $role,
                            'initials' => $this->getInitials($name),
                            'email' => $employee->email,
                            'profile_image' => $profileImage,
                            'nfc_card_id' => $card->nfc_card_id,
                            'landing_page_url' => url('/profile/' . $card->nfc_card_id),
                            'is_admin' => $employee->isBusinessAccount(),
                        ];
                    }
                } else {
                    // Employee without NFC card - still show them
                    $teamMembers[] = [
                        'id' => $employee->id,
                        'user_id' => $employee->id,
                        'name' => $employee->full_name,
                        'role' => $employee->job_title ?? 'Team Member',
                        'initials' => $this->getInitials($employee->full_name),
                        'email' => $employee->email,
                        'profile_image' => null,
                        'nfc_card_id' => null,
                        'landing_page_url' => null,
                        'is_admin' => $employee->isBusinessAccount(),
                    ];
                }
            }
            
            Log::info('Team members result', [
                'count' => count($teamMembers),
                'names' => array_column($teamMembers, 'name'),
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $teamMembers,
                'total' => count($teamMembers),
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get business team members', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get team members: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get initials from name
     */
    private function getInitials($name)
    {
        if (empty($name)) return '??';
        
        $words = explode(' ', trim($name));
        $initials = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }
        
        return substr($initials, 0, 2) ?: '??';
    }
    
    /**
     * Upload portfolio image
     */
    public function uploadPortfolioImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'nfc_card_id' => 'nullable|exists:nfc_cards,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $file = $request->file('image');
            $fileName = 'portfolio_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('portfolio-images', $fileName, 'public');
            $url = asset('storage/' . $path);

            return response()->json([
                'success' => true,
                'message' => 'Portfolio image uploaded successfully',
                'data' => [
                    'url' => $url,
                    'path' => $path,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Portfolio image upload failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Upload service image
     */
    public function uploadServiceImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'nfc_card_id' => 'nullable|exists:nfc_cards,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $file = $request->file('image');
            $fileName = 'service_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('service-images', $fileName, 'public');
            $url = asset('storage/' . $path);

            return response()->json([
                'success' => true,
                'message' => 'Service image uploaded successfully',
                'data' => [
                    'url' => $url,
                    'path' => $path,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Service image upload failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Upload gallery image
     */
    public function uploadGalleryImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'nfc_card_id' => 'nullable|exists:nfc_cards,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $file = $request->file('image');
            $fileName = 'gallery_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('gallery-images', $fileName, 'public');
            $url = asset('storage/' . $path);

            return response()->json([
                'success' => true,
                'message' => 'Gallery image uploaded successfully',
                'data' => [
                    'url' => $url,
                    'path' => $path,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Gallery image upload failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Upload blog image
     */
    public function uploadBlogImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'nfc_card_id' => 'nullable|exists:nfc_cards,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $file = $request->file('image');
            $fileName = 'blog_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('blog-images', $fileName, 'public');
            $url = asset('storage/' . $path);

            return response()->json([
                'success' => true,
                'message' => 'Blog image uploaded successfully',
                'data' => [
                    'url' => $url,
                    'path' => $path,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Blog image upload failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Upload generic file (PDF, documents, etc.)
     */
    public function uploadFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip|max:20480',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();
            $file = $request->file('file');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $fileName = 'file_' . $user->id . '_' . time() . '_' . Str::slug($originalName) . '.' . $extension;
            $path = $file->storeAs('uploads', $fileName, 'public');
            $url = asset('storage/' . $path);

            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'data' => [
                    'url' => $url,
                    'path' => $path,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('File upload failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
