<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profile;
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

    public function show($slug)
    {
        $profile = Profile::where('slug', $slug)
            ->where('is_active', true)
            ->with(['socialLinks' => function ($query) {
                $query->where('is_active', true);
            }])
            ->firstOrFail();

        // Track profile view
        Analytics::create([
            'trackable_type' => Profile::class,
            'trackable_id' => $profile->id,
            'action' => 'profile_view',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'data' => $profile
        ]);
    }

    public function getProfile(Request $request)
    {
        $user = $request->user();

        // Ensure profile exists
        if (!$user->profile) {
            $user->profile()->create([
                'name' => $user->name ?? $user->email,
                'email' => $user->email,
                'slug' => Str::slug(($user->name ?? $user->email) . '-' . $user->id),
            ]);
        }

        $profile = $user->profile;

        return response()->json([
            'success' => true,
            'profile' => $profile
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

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
            'name' => $data['name'] ?? $profile->name,
            'title' => $data['position'] ?? $profile->title,
            'company' => $data['company'] ?? $profile->company,
            'bio' => $data['bio'] ?? $profile->bio,
            'email' => $data['email'] ?? $profile->email,
            'phone' => $data['contactNumber'] ?? $profile->phone,
            'website' => $data['website'] ?? $profile->website,
            'location' => $data['address'] ?? $profile->location,
            'theme' => $data['theme'] ?? $profile->theme,
            'background_color' => $data['backgroundColor'] ?? $profile->background_color,
            'text_color' => $data['textColor'] ?? $profile->text_color,
            'font' => $data['font'] ?? $profile->font,
            'button_style' => $data['buttonStyle'] ?? $profile->button_style,
            'show_watermark' => $data['showWatermark'] ?? $profile->show_watermark,
            'profile_style' => $data['profileStyle'] ?? $profile->profile_style,
        ];

        $profile->update($mappedData);

        // Send profile updated notification
        $this->notificationService->create($request->user(), 'profile_updated', [
            'fields' => implode(', ', array_keys(array_filter($mappedData, function($value, $key) use ($profile) {
                return $profile->wasChanged($key);
            }, ARRAY_FILTER_USE_BOTH))),
        ]);

        return response()->json([
            'success' => true,
            'profile' => $profile
        ]);
    }

    public function updateSlug(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile;

        $validator = Validator::make($request->all(), [
            'slug' => 'required|string|max:255|unique:profiles,slug,' . $profile->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $profile->update(['slug' => Str::slug($request->slug)]);

        return response()->json([
            'success' => true,
            'profile' => $profile
        ]);
    }

    public function checkSlug(Request $request)
    {
        $slug = Str::slug($request->slug);
        $exists = Profile::where('slug', $slug)
            ->where('id', '!=', $request->user()->profile->id ?? 0)
            ->exists();

        return response()->json([
            'success' => true,
            'available' => !$exists,
            'slug' => $slug
        ]);
    }

    public function uploadProfileImage(Request $request)
    {
        Log::info('Profile image upload started', [
            'user_id' => $request->user()->id,
            'has_file' => $request->hasFile('image'),
            'files' => $request->allFiles()
        ]);

        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
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

            // Ensure profile exists
            if (!$user->profile) {
                $user->profile()->create([
                    'name' => $user->name ?? $user->email,
                    'email' => $user->email,
                    'slug' => Str::slug(($user->name ?? $user->email) . '-' . $user->id),
                ]);
            }

            $profile = $user->profile;

            // Check if storage link exists
            if (!file_exists(public_path('storage'))) {
                Log::error('Storage link does not exist. Run: php artisan storage:link');
                throw new \Exception('Storage is not properly configured. Please contact support.');
            }

            // Delete old image if exists
            if ($profile->profile_image_path) {
                try {
                    $this->fileUploadService->deleteFile($profile->profile_image_path);

                    // Delete thumbnail too
                    $thumbnailPath = str_replace('profile-images/', 'profile-images/thumbnails/', $profile->profile_image_path);
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

            // Update profile
            $profile->update([
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
            'files' => $request->allFiles()
        ]);

        $validator = Validator::make($request->all(), [
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
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

            // Ensure profile exists
            if (!$user->profile) {
                $user->profile()->create([
                    'name' => $user->name ?? $user->email,
                    'email' => $user->email,
                    'slug' => Str::slug(($user->name ?? $user->email) . '-' . $user->id),
                ]);
            }

            $profile = $user->profile;

            // Check if storage link exists
            if (!file_exists(public_path('storage'))) {
                Log::error('Storage link does not exist. Run: php artisan storage:link');
                throw new \Exception('Storage is not properly configured. Please contact support.');
            }

            // Delete old logo if exists
            if ($profile->company_logo_path) {
                try {
                    $this->fileUploadService->deleteFile($profile->company_logo_path);
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

            // Update profile
            $profile->update([
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
            $profile = $request->user()->profile;

            if ($profile->profile_image_path) {
                // Delete main image
                $this->fileUploadService->deleteFile($profile->profile_image_path);

                // Delete thumbnail
                $thumbnailPath = str_replace('profile-images/', 'profile-images/thumbnails/', $profile->profile_image_path);
                $this->fileUploadService->deleteFile($thumbnailPath);

                // Update profile
                $profile->update([
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
            $profile = $request->user()->profile;

            if ($profile->company_logo_path) {
                $this->fileUploadService->deleteFile($profile->company_logo_path);

                $profile->update([
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
