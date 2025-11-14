<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\LinkController;
use App\Http\Controllers\Api\NfcController;
use App\Http\Controllers\Api\NfcCardController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\LegalDocumentController;
use App\Http\Controllers\Api\ChatbotController;
use App\Http\Controllers\Api\AdminChatbotController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AdminNotificationController;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Auth\SocialAuthController;

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
            'oauth' => true,
        ]
    ]);
});

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public profile viewing
Route::get('/profiles/{slug}', [ProfileController::class, 'show']);
Route::get('/nfc-cards/{nfcCard}/landing-page', [NfcCardController::class, 'getLandingPage']);
Route::post('/analytics/track', [AnalyticsController::class, 'track']);
Route::post('/nfc/tap/{nfcId}', [NfcController::class, 'tap']);

// ✅ OAuth Routes (Public - No Auth Required)
Route::prefix('auth')->group(function () {
    // Social OAuth routes (Google & Apple)
    Route::get('{provider}/redirect', [SocialAuthController::class, 'redirect']);
    Route::get('{provider}/callback', [SocialAuthController::class, 'callback']);

    // Password Reset Routes
    Route::post('password-reset-request', [PasswordResetController::class, 'requestReset']);
    Route::post('password-reset', [PasswordResetController::class, 'resetPassword']);
    Route::get('verify-reset-token/{token}', [PasswordResetController::class, 'verifyToken']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/complete-onboarding', [AuthController::class, 'completeOnboarding']);

    // Profile
    Route::get('/user/profile', [ProfileController::class, 'getProfile']);
    Route::put('/user/profile', [ProfileController::class, 'update']);
    Route::put('/user/account', [ProfileController::class, 'updateUserInfo']); // Settings page account update
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
    
    // Landing Page update (protected - only card owner can update)
    Route::put('/nfc-cards/{nfcCard}/landing-page', [NfcCardController::class, 'updateLandingPage']);
    
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

    // Settings
    Route::get('/settings', [SettingsController::class, 'getSettings']);
    Route::put('/settings/personal-info', [SettingsController::class, 'updatePersonalInfo']);
    Route::post('/settings/upload-account-image', [SettingsController::class, 'uploadUserAccountImage']);
    Route::delete('/settings/delete-account-image', [SettingsController::class, 'deleteUserAccountImage']);

   // Notifications (User)
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount']);
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
        Route::delete('/read/all', [NotificationController::class, 'deleteAllRead']);
    });

    // Onboarding flow
    Route::post('/onboarding/select-plan', [OnboardingController::class, 'selectPlan']);
    Route::post('/onboarding/save-card-info', [OnboardingController::class, 'saveCardInfo']);
    Route::post('/onboarding/upload-card-design', [OnboardingController::class, 'uploadCardDesign']);
    Route::post('/onboarding/process-payment', [OnboardingController::class, 'processPayment']);
    Route::get('/onboarding/order-status', [OnboardingController::class, 'getOrderStatus']);
});

// Public legal documents routes
Route::get('/legal/terms', [LegalDocumentController::class, 'getTerms']);
Route::get('/legal/privacy', [LegalDocumentController::class, 'getPrivacy']);
Route::get('/legal/documents', [LegalDocumentController::class, 'index']);
Route::get('/legal/documents/{type}', [LegalDocumentController::class, 'show']);

// Public chatbot routes
Route::post('/chatbot/ask', [ChatbotController::class, 'ask']);
Route::post('/chatbot/feedback', [ChatbotController::class, 'submitFeedback']);
Route::get('/chatbot/questions', [ChatbotController::class, 'getQuestions']);

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

    // Legal documents management
    Route::put('/legal/documents/{type}', [LegalDocumentController::class, 'update']);
    Route::get('/legal/pdf/{type}/status', [LegalDocumentController::class, 'checkPdfStatus']);
    Route::get('/legal/pdf/terms/download', [LegalDocumentController::class, 'downloadTerms']);
    Route::get('/legal/pdf/privacy/download', [LegalDocumentController::class, 'downloadPrivacy']);
    Route::post('/legal/pdf/terms/upload', [LegalDocumentController::class, 'uploadTermsPdf']);
    Route::post('/legal/pdf/privacy/upload', [LegalDocumentController::class, 'uploadPrivacyPdf']);

    // Admin Notification Management
    Route::prefix('notifications')->group(function () {
        Route::get('/', [AdminNotificationController::class, 'index']);
        Route::get('/statistics', [AdminNotificationController::class, 'getStatistics']);
        Route::post('/announcement', [AdminNotificationController::class, 'sendAnnouncement']);
        Route::post('/send-to-users', [AdminNotificationController::class, 'sendToUsers']);
        Route::post('/system-message', [AdminNotificationController::class, 'sendSystemMessage']);
        Route::delete('/{id}', [AdminNotificationController::class, 'destroy']);
        Route::post('/cleanup', [AdminNotificationController::class, 'cleanupOldNotifications']);
    });
    // Chatbot management
    Route::get('/chatbot/questions', [AdminChatbotController::class, 'index']);
    Route::post('/chatbot/questions', [AdminChatbotController::class, 'store']);
    Route::put('/chatbot/questions/{id}', [AdminChatbotController::class, 'update']);
    Route::delete('/chatbot/questions/{id}', [AdminChatbotController::class, 'destroy']);
    Route::get('/chatbot/feedback', [AdminChatbotController::class, 'getFeedback']);
    Route::put('/chatbot/feedback/{id}/read', [AdminChatbotController::class, 'markFeedbackAsRead']);
    Route::delete('/chatbot/feedback/{id}', [AdminChatbotController::class, 'deleteFeedback']);
    Route::get('/chatbot/analytics', [AdminChatbotController::class, 'getAnalytics']);
});

