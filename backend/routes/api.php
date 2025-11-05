<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\LinkController;
use App\Http\Controllers\Api\NfcController;
use App\Http\Controllers\Api\NfcCardController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\PassResetController;
use Illuminate\Support\Facades\Storage;

// Test endpoint
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working!',
        'timestamp' => now(),
        'version' => '1.0.0',
        'features' => [
            'authentication' => true,
            'profiles' => true,
            'social_links' => true,
            'nfc_tags' => true,
            'analytics' => true,
            'file_uploads' => true,
            'onboarding' => true,
        ]
    ]);
});

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public profile viewing
Route::get('/profiles/{slug}', [ProfileController::class, 'show']);
Route::post('/analytics/track', [AnalyticsController::class, 'track']);
Route::post('/nfc/tap/{nfcId}', [NfcController::class, 'tap']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Profile
    Route::get('/user/profile', [ProfileController::class, 'getProfile']);
    Route::put('/user/profile', [ProfileController::class, 'update']);
    Route::post('/user/check-slug', [ProfileController::class, 'checkSlug']);
    Route::put('/user/slug', [ProfileController::class, 'updateSlug']);

    // Links
    Route::get('/links', [LinkController::class, 'index']);
    Route::post('/links', [LinkController::class, 'store']);
    Route::put('/links/{link}', [LinkController::class, 'update']);
    Route::delete('/links/{link}', [LinkController::class, 'destroy']);
    Route::post('/links/reorder', [LinkController::class, 'reorder']);
    Route::post('/links/{link}/track-click', [LinkController::class, 'trackClick']);

    // NFC
    Route::get('/nfc/tags', [NfcController::class, 'index']);
    Route::post('/nfc/activate', [NfcController::class, 'activate']);
    Route::put('/nfc/tags/{tag}', [NfcController::class, 'update']);
    Route::post('/nfc/tags/{tag}/deactivate', [NfcController::class, 'deactivate']);

    // NFC Cards (Physical Cards Management)
    Route::get('/nfc-cards', [NfcCardController::class, 'index']);
    Route::get('/nfc-cards/{nfcCard}', [NfcCardController::class, 'show']);
    Route::post('/nfc-cards', [NfcCardController::class, 'store']);
    Route::put('/nfc-cards/{nfcCard}', [NfcCardController::class, 'update']);
    Route::post('/nfc-cards/{nfcCard}/activate', [NfcCardController::class, 'activate']);
    Route::post('/nfc-cards/{nfcCard}/deactivate', [NfcCardController::class, 'deactivate']);
    Route::get('/nfc-cards/{nfcCard}/analytics', [NfcCardController::class, 'analytics']);
    Route::get('/subscription/status', [NfcCardController::class, 'subscriptionStatus']);

    // Analytics
    Route::get('/analytics/overview', [AnalyticsController::class, 'overview']);
    Route::get('/analytics/profile', [AnalyticsController::class, 'profileAnalytics']);
    Route::get('/analytics/nfc/{tag}', [AnalyticsController::class, 'nfcAnalytics']);

    // File uploads
    Route::post('/upload/profile-image', [ProfileController::class, 'uploadProfileImage']);
    Route::post('/upload/company-logo', [ProfileController::class, 'uploadCompanyLogo']);
    Route::delete('/upload/profile-image', [ProfileController::class, 'deleteProfileImage']);
    Route::delete('/upload/company-logo', [ProfileController::class, 'deleteCompanyLogo']);

    // Onboarding flow
    Route::post('/onboarding/select-plan', [OnboardingController::class, 'selectPlan']);
    Route::post('/onboarding/save-card-info', [OnboardingController::class, 'saveCardInfo']);
    Route::post('/onboarding/upload-card-design', [OnboardingController::class, 'uploadCardDesign']);
    Route::post('/onboarding/process-payment', [OnboardingController::class, 'processPayment']);
    Route::get('/onboarding/order-status', [OnboardingController::class, 'getOrderStatus']);
});

// Admin routes (protected by admin middleware)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/stats', [AdminController::class, 'getSystemStats']);

    // User management
    Route::get('/users', [AdminController::class, 'getUsers']);
    Route::get('/users/{userId}', [AdminController::class, 'getUser']);
    Route::post('/users', [AdminController::class, 'createUser']);
    Route::put('/users/{userId}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{userId}', [AdminController::class, 'deleteUser']);

    // NFC card management
    Route::get('/nfc-cards', [AdminController::class, 'getNfcCards']);
    Route::post('/nfc-cards', [AdminController::class, 'registerNfcCard']);
    Route::put('/nfc-cards/{cardId}', [AdminController::class, 'updateNfcCard']);
    Route::delete('/nfc-cards/{cardId}', [AdminController::class, 'deleteNfcCard']);
});

// Password Reset Routes (no authentication required)
Route::prefix('auth')->group(function () {
    // Request a password reset link
    Route::post('/password-reset-request', [PasswordResetController::class, 'requestReset']);
    
    // Reset the password using token
    Route::post('/password-reset', [PasswordResetController::class, 'resetPassword']);
    
    // Verify if a token is valid
    Route::get('/verify-reset-token/{token}', [PasswordResetController::class, 'verifyToken']);
});