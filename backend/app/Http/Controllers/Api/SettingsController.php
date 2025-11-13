<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    protected $notificationService;
    protected $fileUploadService;

    public function __construct(
        NotificationService $notificationService,
        FileUploadService $fileUploadService
    ) {
        $this->notificationService = $notificationService;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Update user personal information
     */
    public function updatePersonalInfo(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ], [
            'email.unique' => 'This email is already registered in our system. Please use a different email address.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
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
            // Update user table
            $user->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);

            // Also update profile table if profile exists
            if ($user->profile) {
                $user->profile->update([
                    'name' => $request->first_name . ' ' . $request->last_name,
                    'email' => $request->email,
                    'phone' => $request->phone,
                ]);
            }

            DB::commit();

            // Send notification
            $this->notificationService->create($user, 'profile_updated', []);

            // Refresh user data
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Personal information updated successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update user personal info', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update personal information: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 422);
        }

        try {
            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            // Send notification
            $this->notificationService->create($user, 'password_changed', [
                'time' => now()->format('Y-m-d H:i:s'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to change password', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to change password: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user settings
     */
    public function getSettings(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'user' => $user,
            'security_settings' => [
                'two_factor_sms' => false, // Placeholder
                'login_notifications' => true,
            ],
            'privacy_settings' => [
                'public_profile' => true,
                'search_indexing' => true,
                'analytics_enabled' => true,
                'location_tracking' => true,
            ],
            'notification_settings' => [
                'profile_views' => true,
                'nfc_taps' => true,
                'weekly_reports' => true,
                'product_updates' => false,
            ]
        ]);
    }

    /**
     * Upload user account image (Settings page)
     */
    public function uploadUserAccountImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = $request->user();

            // Delete old image if exists
            if ($user->account_image) {
                try {
                    // Extract path from URL
                    $oldPath = str_replace(url('storage/'), '', $user->account_image);
                    $this->fileUploadService->deleteFile($oldPath);
                } catch (\Exception $e) {
                    Log::warning('Failed to delete old user account image', ['error' => $e->getMessage()]);
                }
            }

            // Upload new image
            $uploadResult = $this->fileUploadService->uploadProfileImage(
                $request->file('image'),
                $user->id
            );

            // Update user table (not profile table)
            $user->update([
                'account_image' => $uploadResult['url'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Account image uploaded successfully',
                'account_image' => $uploadResult['url'],
                'user' => $user->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to upload user account image', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user account image (Settings page)
     */
    public function deleteUserAccountImage(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->account_image) {
                return response()->json([
                    'success' => false,
                    'message' => 'No account image to delete'
                ], 400);
            }

            // Delete image file
            try {
                $imagePath = str_replace(url('storage/'), '', $user->account_image);
                $this->fileUploadService->deleteFile($imagePath);
            } catch (\Exception $e) {
                Log::warning('Failed to delete user account image file', ['error' => $e->getMessage()]);
            }

            // Update user table
            $user->update([
                'account_image' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Account image removed successfully',
                'user' => $user->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete user account image', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to remove image: ' . $e->getMessage()
            ], 500);
        }
    }
}
