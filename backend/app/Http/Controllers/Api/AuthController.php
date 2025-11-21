<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use App\Models\ActivityLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
        ], [
            'email.unique' => 'This email is already registered. Please login or use a different email.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'company' => $request->company,
                'job_title' => $request->job_title,
                'is_new_user' => true, // Mark as new user for onboarding
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            // Send registration success notification
            try {
                $this->notificationService->create($user, 'registration_success', [
                    'name' => $user->first_name,
                    'profile_url' => config('app.frontend_url') . '/UserDashboard/PlanSelection',
                ]);
            } catch (\Exception $e) {
                // Log notification error but don't fail registration
                \Log::error('Failed to send registration notification: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'user' => $user->fresh(),
                'token' => $token,
                'token_type' => 'Bearer',
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Registration error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        // Log login activity
        ActivityLog::logActivity(
            $user,
            'login',
            'User logged in successfully',
            [
                'metadata' => [
                    'ip_address' => $request->ip(),
                    'device' => $this->getDeviceInfo($request->userAgent()),
                    'user_agent' => $request->userAgent(),
                ]
            ]
        );

        // Check if this is a new device/location (simplified check)
        $lastLoginDevice = $user->last_login_device ?? '';
        $currentDevice = $request->userAgent();
        
        if ($lastLoginDevice && $lastLoginDevice !== $currentDevice) {
            // Send new device login notification
            $this->notificationService->create($user, 'login_new_device', [
                'device' => $this->getDeviceInfo($currentDevice),
                'ip' => $request->ip(),
                'time' => now()->format('Y-m-d H:i:s'),
            ]);
        }
        
        // Update last login device
        $user->update(['last_login_device' => $currentDevice]);

        return response()->json([
            'success' => true,
            'user' => $user->load('nfcCards'),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        
        // Log logout activity before deleting token
        ActivityLog::logActivity(
            $user,
            'logout',
            'User logged out',
            [
                'metadata' => [
                    'ip_address' => $request->ip(),
                    'device' => $this->getDeviceInfo($request->userAgent()),
                    'user_agent' => $request->userAgent(),
                ]
            ]
        );
        
        $user->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => $user->load('nfcCards')
        ]);
    }

    /**
     * Get simplified device info from user agent
     */
    private function getDeviceInfo($userAgent)
    {
        if (preg_match('/Windows/', $userAgent)) {
            return 'Windows PC';
        } elseif (preg_match('/Macintosh/', $userAgent)) {
            return 'Mac';
        } elseif (preg_match('/iPhone/', $userAgent)) {
            return 'iPhone';
        } elseif (preg_match('/iPad/', $userAgent)) {
            return 'iPad';
        } elseif (preg_match('/Android/', $userAgent)) {
            return 'Android Device';
        } else {
            return 'Unknown Device';
        }
    }

    /**
     * Mark user as no longer new (after completing onboarding/plan selection)
     */
    public function completeOnboarding(Request $request)
    {
        $user = $request->user();
        
        $user->update(['is_new_user' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Onboarding completed',
            'user' => $user->load('nfcCards')
        ]);
    }
}
