<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'company' => 'nullable|string|max:255',
            'job_title' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'company' => $request->company,
            'job_title' => $request->job_title,
        ]);

        // Create profile with unique slug
        $slug = Str::slug($user->full_name);
        $originalSlug = $slug;
        $count = 1;

        while (Profile::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $profile = Profile::create([
            'user_id' => $user->id,
            'slug' => $slug,
            'name' => $user->full_name,
            'title' => $user->job_title,
            'company' => $user->company,
            'email' => $user->email,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $user,
            'profile' => $profile,
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
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

        // Ensure user has a profile (for users created before profile system)
        if (!$user->profile) {
            $slug = Str::slug($user->full_name ?? $user->email);
            $originalSlug = $slug;
            $count = 1;

            while (Profile::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            $user->profile()->create([
                'slug' => $slug,
                'name' => $user->full_name ?? $user->email,
                'title' => $user->job_title,
                'company' => $user->company,
                'email' => $user->email,
            ]);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'user' => $user->load('profile', 'nfcTag'),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        // Ensure user has a profile
        if (!$user->profile) {
            $slug = Str::slug($user->full_name ?? $user->email);
            $originalSlug = $slug;
            $count = 1;

            while (Profile::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            $user->profile()->create([
                'slug' => $slug,
                'name' => $user->full_name ?? $user->email,
                'title' => $user->job_title,
                'company' => $user->company,
                'email' => $user->email,
            ]);
        }

        return response()->json([
            'success' => true,
            'user' => $user->load('profile', 'nfcTag')
        ]);
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
            'user' => $user->load('profile', 'nfcTag')
        ]);
    }
}
