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
use App\Http\Controllers\Api\Business\QuotaController;
use App\Http\Controllers\Api\Business\EmployeeController;
use App\Http\Controllers\Api\Business\ActivityLogController;
use App\Http\Controllers\Api\Admin\BusinessUserController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\RefundController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\Admin\ManualBankTransferController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\Api\Admin\PlanPriceController;
use App\Http\Controllers\Api\ProfileDesignController;
use App\Http\Controllers\Api\Admin\ProfileBuilderFieldController;
use App\Http\Controllers\Api\Admin\ProfileBuilderSectionController;
use App\Http\Controllers\Api\CardTemplateController;
use App\Http\Controllers\Api\AdminExportController;

// ============== API ROOT ROUTES (Diagnosis / Onboarding) ==============

/**
 * API "is it alive?" route. The prior 404 `Cannot GET /` from the screenshot
 * happened because the API server has no GET /api handler defined. This
 * replaces that empty 404 with a friendly status JSON.
 *
 *   Localhost example: http://localhost:3001/api     (shows OK status)
 *   Production:       https://nfcgo.clbgroups.com/api
 */
Route::get('/', function () {
    return response()->json([
        'success' => true,
        'service' => 'nfcgo-api',
        'name'    => 'NFC Business Card Backend',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
        'env'     => app()->environment(),
        'available_routes' => [
            'health_check'        => '/health',
            'api_test'            => '/api/test',
            'auth'                => ['POST /api/register', 'POST /api/login'],
            'public_profile'      => 'GET /api/nfc-cards/{card}/landing-page',
            'nfc_hardware_tap'    => 'POST /api/nfc/tap/{nfcId}',
            'docs_notice'         => 'Append /api to all endpoints above.',
        ],
    ]);
});

/**
 * Fallback for unknown API routes — return consistent JSON 404 envelope
 * instead of the generic "{message:Not Found,statusCode:404}" that users
 * mistook for a "profile bug". Keeps response shape the same across the app.
 */
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found',
        'service' => 'nfcgo-api',
        'available_routes_hint' => 'Try GET /api or GET /api/test',
        'timestamp' => now()->toIso8601String(),
    ], 404);
});

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
Route::post('/magick/replace-text', [\App\Http\Controllers\MagickController::class, 'replace']);

// Public routes (with rate limiting to prevent abuse)
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1'); // 10 per minute
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1'); // 10 per minute

// Email verification (public - signed URLs from email + resend)
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');
Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail'])
    ->middleware('throttle:3,1'); // 3 per minute

// Public profile viewing (using landing pages)
Route::get('/nfc-cards/{nfcCard}/landing-page', [NfcCardController::class, 'getLandingPage']);
Route::post('/nfc-cards/{nfcCard}/track-tap', [NfcCardController::class, 'trackTap']);
Route::post('/analytics/track', [AnalyticsController::class, 'track']);
Route::post('/nfc/tap/{nfcId}', [NfcController::class, 'tap']);

// Legacy password reset routes (no prefix) for backward compatibility with older clients
Route::post('password-reset-request', [PasswordResetController::class, 'requestReset']);
Route::post('password-reset', [PasswordResetController::class, 'resetPassword']);
Route::get('verify-reset-token/{token}', [PasswordResetController::class, 'verifyToken']);

// ✅ OAuth Routes (Public - No Auth Required)
Route::prefix('auth')->group(function () {
    // Email check for OAuth conflict detection (public, rate-limited)
    Route::get('check-email', [AuthController::class, 'checkEmail'])->middleware('throttle:30,1');

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
    Route::post('/nfc-cards/{nfcCard}/upload-payment-proof', [NfcCardController::class, 'uploadPaymentProof']);
    Route::post('/nfc-cards/{nfcCard}/confirm-received', [NfcCardController::class, 'confirmReceived']);

    // Landing Page update (protected - only card owner can update)
    Route::put('/nfc-cards/{nfcCard}/landing-page', [NfcCardController::class, 'updateLandingPage']);

    Route::get('/subscription/status', [NfcCardController::class, 'subscriptionStatus']);

    // Business Team Members (for profile builder)
    Route::get('/business/team-members', [ProfileController::class, 'getBusinessTeamMembers']);

    // Analytics
    Route::get('/analytics/overview', [AnalyticsController::class, 'overview']);
    Route::get('/analytics/profile', [AnalyticsController::class, 'profileAnalytics']);
    Route::get('/analytics/nfc/{tag}', [AnalyticsController::class, 'nfcAnalytics']);

    // User Analytics (for Premium/Basic Plan users - aggregate all cards)
    Route::get('/analytics/user/overview', [AnalyticsController::class, 'userOverview']);
    Route::get('/analytics/user/overview/export', [AnalyticsController::class, 'userOverviewExport']);

    // Business Analytics (for Business Plan users)
    Route::get('/analytics/business/overview', [AnalyticsController::class, 'businessOverview']);
    Route::get('/analytics/business/overview/export', [AnalyticsController::class, 'businessOverviewExport']);
    Route::get('/analytics/nfc-card/{cardId}', [AnalyticsController::class, 'nfcCardAnalytics']);
    Route::get('/analytics/nfc-card/{cardId}/export', [AnalyticsController::class, 'nfcCardExport']);

    // File uploads
    Route::post('/upload/profile-image', [ProfileController::class, 'uploadProfileImage']);
    Route::post('/upload/company-logo', [ProfileController::class, 'uploadCompanyLogo']);
    Route::post('/upload/cover-banner', [ProfileController::class, 'uploadCoverBanner']);
    Route::post('/upload/portfolio-image', [ProfileController::class, 'uploadPortfolioImage']);
    Route::post('/upload/service-image', [ProfileController::class, 'uploadServiceImage']);
    Route::post('/upload/gallery-image', [ProfileController::class, 'uploadGalleryImage']);
    Route::post('/upload/blog-image', [ProfileController::class, 'uploadBlogImage']);
    Route::post('/upload/file', [ProfileController::class, 'uploadFile']);
    Route::delete('/upload/profile-image', [ProfileController::class, 'deleteProfileImage']);
    Route::delete('/upload/company-logo', [ProfileController::class, 'deleteCompanyLogo']);
    Route::delete('/upload/cover-banner', [ProfileController::class, 'deleteCoverBanner']);

    // Settings
    Route::get('/settings', [SettingsController::class, 'getSettings']);
    Route::put('/settings/personal-info', [SettingsController::class, 'updatePersonalInfo']);
    Route::post('/settings/upload-account-image', [SettingsController::class, 'uploadUserAccountImage']);
    Route::delete('/settings/delete-account-image', [SettingsController::class, 'deleteUserAccountImage']);

    // Linked OAuth Accounts
    Route::get('/user/linked-accounts', [AuthController::class, 'getLinkedAccounts']);
    Route::post('/user/verify-password', [AuthController::class, 'verifyPassword']);
    Route::post('/user/initiate-link-account', [AuthController::class, 'initiateLinkAccount']);
    Route::delete('/user/linked-accounts/{provider}', [AuthController::class, 'unlinkAccount']);

    // Security: password + sessions
    Route::post('/user/change-password', [AuthController::class, 'changePassword']);
    Route::get('/user/sessions', [AuthController::class, 'getActiveSessions']);
    Route::delete('/user/sessions/{id}', [AuthController::class, 'revokeSession']);

    // Security: Two-Factor Authentication (TOTP)
    Route::prefix('/user/2fa')->group(function () {
        Route::post('/setup', [AuthController::class, 'setupTwoFactor']);
        Route::post('/confirm', [AuthController::class, 'confirmTwoFactor']);
        Route::post('/disable', [AuthController::class, 'disableTwoFactor']);
        Route::post('/recovery-codes/regenerate', [AuthController::class, 'regenerate2FARecoveryCodes']);
    });

    // Logout from all devices (revoke all tokens)
    Route::post('/user/logout-everywhere', [AuthController::class, 'logoutEverywhere']);

    // Notifications (User)
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount']);
        Route::post('/', [NotificationController::class, 'store']);
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

    // Payment Routes
    Route::prefix('payment')->group(function () {
        Route::get('/rails', [PaymentController::class, 'getAvailableRails']);
        Route::post('/calculate-fees', [PaymentController::class, 'calculateFees']);
        Route::post('/initiate', [PaymentController::class, 'initiatePayment']);
        Route::post('/confirm/{transactionId}', [PaymentController::class, 'confirmPayment']);
        Route::get('/transactions', [PaymentController::class, 'getTransactions']);
        Route::get('/transactions/{transactionId}', [PaymentController::class, 'getTransactionDetails']);
        Route::post('/transactions/{transactionId}/upload-proof', [PaymentController::class, 'uploadPaymentProof']);
    });

    // Business Plan Routes (for Business accounts and employees)
    Route::prefix('business')->group(function () {
        // Quota Information
        Route::get('/card-quota', [QuotaController::class, 'getCardQuota']);
        Route::get('/can-order-card', [QuotaController::class, 'canOrderCard']);

        // Employee Management (Business account owners only)
        Route::get('/employees', [QuotaController::class, 'getEmployees']);
        // Note: Employee creation is now handled by Super Admin via /admin/business-employees
        // Route::post('/employees', [QuotaController::class, 'createEmployee']); // DEPRECATED
        Route::delete('/employees/{id}', [QuotaController::class, 'deleteEmployee']);

        // Bulk Employee Data Upload & Card Orders (does NOT create employee accounts)
        Route::post('/employees/upload', [EmployeeController::class, 'uploadEmployees']);
        Route::post('/employees/{id}/reset-password', [EmployeeController::class, 'resetPassword']);
        Route::post('/employees/{id}/toggle-status', [EmployeeController::class, 'toggleStatus']);
        Route::get('/employees/{id}/details', [EmployeeController::class, 'getEmployeeDetails']);

        // Activity Logs (Business Admin can view employee activities, read-only)
        Route::get('/activity-logs', [ActivityLogController::class, 'index']);
        Route::get('/activity-logs/statistics', [ActivityLogController::class, 'statistics']);
        Route::get('/activity-logs/action-types', [ActivityLogController::class, 'actionTypes']);
        Route::get('/activity-logs/export', [ActivityLogController::class, 'export']); // Must be before {id}
        Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show']);
        // Note: No DELETE endpoint - Business Admin can only view, not delete
    });

    // ✅ Invoice Routes (User)
    Route::prefix('invoices')->group(function () {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::get('/statistics', [InvoiceController::class, 'statistics']);
        Route::post('/download-by-order', [InvoiceController::class, 'downloadByOrderId']);
        Route::get('/{invoice}', [InvoiceController::class, 'show']);
        Route::get('/{invoice}/preview', [InvoiceController::class, 'preview']);
        Route::post('/{invoice}/send-email', [InvoiceController::class, 'sendEmail']);
    });

    // Invoice download with signed URL
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])
        ->name('invoices.download')
        ->middleware('signed');

    // Profile Design Options (User - get available options)
    Route::get('/profile-design-options', [ProfileDesignController::class, 'index']);

    // Profile Builder (User - get sections and fields by plan)
    Route::get('/profile-builder-sections', [ProfileBuilderFieldController::class, 'getSections']);
    Route::get('/profile-builder-fields', [ProfileBuilderFieldController::class, 'index']);

    // ─── PERFORMANCE: Batch init endpoint for Profile Builder page load ──────
    // Merges sections + fields + design options into a single HTTP roundtrip.
    // Saves 2x full Laravel request bootstrap + TLS/HTTP handshake overhead
    // versus calling the 3 individual endpoints above separately.
    Route::get('/profile-builder-init', [\App\Http\Controllers\Api\ProfileBuilderInitController::class, 'index']);
});

// Public legal documents routes
Route::get('/legal/terms', [LegalDocumentController::class, 'getTerms']);
Route::get('/legal/privacy', [LegalDocumentController::class, 'getPrivacy']);
Route::get('/legal/documents', [LegalDocumentController::class, 'index']);
Route::get('/legal/documents/{type}', [LegalDocumentController::class, 'show']);
// Public PDF viewing routes (no auth required)
Route::get('/legal/pdf/terms/view', [LegalDocumentController::class, 'viewTermsPdf']);
Route::get('/legal/pdf/privacy/view', [LegalDocumentController::class, 'viewPrivacyPdf']);
// Public PDF metadata routes (no auth required - for footer modals)
Route::get('/legal/pdf/{type}/info', [LegalDocumentController::class, 'getPdfInfo']);

// Public chatbot routes
Route::get('/chatbot/questions', [ChatbotController::class, 'getPublicQuestions']);
Route::post('/chatbot/ask', [ChatbotController::class, 'askQuestion']);
Route::post('/chatbot/feedback', [ChatbotController::class, 'submitFeedback']);

// Public admin info endpoint (for getting super admin to send notifications)
Route::middleware('auth:sanctum')->get('/admin/super-admin', [AdminController::class, 'getSuperAdmin']);

// Admin routes (protected by admin middleware + ActivityLog audit)
Route::middleware(['auth:sanctum', 'admin', \App\Http\Middleware\LogActivity::class])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/stats', [AdminController::class, 'getSystemStats']);

    // User management
    Route::get('/users', [AdminController::class, 'getUsers']);
    Route::get('/users/{userId}', [AdminController::class, 'getUser']);
    Route::post('/users', [AdminController::class, 'createUser']);
    Route::put('/users/{userId}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{userId}', [AdminController::class, 'deleteUser']);

    // Business Employee Management (Super Admin creates employees under Business accounts)
    Route::post('/business-employees', [AdminController::class, 'createBusinessEmployee']);

    // Business User Management
    Route::prefix('business-users')->group(function () {
        Route::get('/', [BusinessUserController::class, 'index']);
        Route::get('/statistics', [BusinessUserController::class, 'statistics']);
        Route::get('/{id}', [BusinessUserController::class, 'show']);
        Route::post('/create-employee', [BusinessUserController::class, 'createEmployee']);
        Route::post('/create-employees-bulk', [BusinessUserController::class, 'createEmployeesBulk']);
        Route::post('/{id}/update-quota', [BusinessUserController::class, 'updateQuota']);
        Route::delete('/employees/{id}', [BusinessUserController::class, 'deleteEmployee']);
    });

    // NFC card management
    Route::get('/nfc-cards', [AdminController::class, 'getNfcCards']);
    Route::post('/nfc-cards', [AdminController::class, 'registerNfcCard']);
    Route::put('/nfc-cards/{cardId}', [AdminController::class, 'updateNfcCard']);
    Route::delete('/nfc-cards/{cardId}', [AdminController::class, 'deleteNfcCard']);

    // Admin NFC Card Order Flow actions
    Route::post('/nfc-cards/{cardId}/verify-payment', [AdminController::class, 'verifyNfcCardPayment']);
    Route::post('/nfc-cards/{cardId}/reject-payment', [AdminController::class, 'rejectNfcCardPayment']);
    Route::post('/nfc-cards/{cardId}/mark-processing', [AdminController::class, 'markNfcCardProcessing']);
    Route::post('/nfc-cards/{cardId}/mark-shipped', [AdminController::class, 'markNfcCardShipped']);
    Route::post('/nfc-cards/{cardId}/mark-delivered', [AdminController::class, 'markNfcCardDelivered']);
    Route::post('/nfc-cards/{cardId}/cancel', [AdminController::class, 'cancelNfcCard']);

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
        Route::get('/unread-count', [AdminNotificationController::class, 'getUnreadCount']);
        Route::post('/announcement', [AdminNotificationController::class, 'sendAnnouncement']);
        Route::post('/send-to-users', [AdminNotificationController::class, 'sendToUsers']);
        Route::post('/system-message', [AdminNotificationController::class, 'sendSystemMessage']);
        Route::post('/approve', [AdminNotificationController::class, 'approveOrder']);
        Route::post('/mark-all-read', [AdminNotificationController::class, 'markAllAsRead']);
        Route::post('/{id}/approve', [AdminNotificationController::class, 'approveOrder']);
        Route::post('/{id}/reject', [AdminNotificationController::class, 'rejectOrder']);
        Route::delete('/{id}', [AdminNotificationController::class, 'destroy']);
        Route::post('/{id}/mark-read', [AdminNotificationController::class, 'markAsRead']);
        Route::post('/cleanup', [AdminNotificationController::class, 'cleanupOldNotifications']);
    });

    // ✅ Chatbot Feedback Management
    Route::prefix('chatbot')->group(function () {
        Route::get('/feedback', [AdminChatbotController::class, 'getFeedback']);
        Route::get('/feedback/statistics', [AdminChatbotController::class, 'getStatistics']);
        Route::get('/feedback/unread-count', [AdminChatbotController::class, 'getUnreadCount']);
        Route::put('/feedback/{id}/status', [AdminChatbotController::class, 'updateFeedbackStatus']);
        Route::put('/feedback/{id}/read', [AdminChatbotController::class, 'markFeedbackAsRead']);
        Route::delete('/feedback/{id}', [AdminChatbotController::class, 'deleteFeedback']);
    });

    // ✅ Admin Payment Management
    Route::prefix('payments')->group(function () {
        // Refund management
        Route::get('/refunds/pending', [RefundController::class, 'getPendingRefunds']);
        Route::post('/refunds/{refund}/process', [RefundController::class, 'processRefund']);

        // Manual bank transfer verification
        Route::get('/manual-transfers/pending', [ManualBankTransferController::class, 'getPendingTransfers']);
        Route::post('/manual-transfers/{transaction}/verify', [ManualBankTransferController::class, 'verifyTransfer']);
        Route::get('/manual-transfers', [ManualBankTransferController::class, 'getAllTransfers']);
    });

    // ✅ Admin Invoice Management
    Route::prefix('invoices')->group(function () {
        Route::get('/', [AdminInvoiceController::class, 'index']);
        Route::get('/statistics', [AdminInvoiceController::class, 'statistics']);
        Route::get('/{invoice}', [AdminInvoiceController::class, 'show']);
        Route::post('/{invoice}/regenerate', [AdminInvoiceController::class, 'regenerate']);
        Route::post('/{invoice}/mark-issued', [AdminInvoiceController::class, 'markAsIssued']);
        Route::post('/{invoice}/mark-paid', [AdminInvoiceController::class, 'markAsPaid']);
        Route::post('/{invoice}/cancel', [AdminInvoiceController::class, 'cancel']);
        Route::post('/{invoice}/resend', [AdminInvoiceController::class, 'resend']);
        Route::get('/{invoice}/download', [AdminInvoiceController::class, 'download']);
        Route::get('/{invoice}/preview', [AdminInvoiceController::class, 'preview']);
    });

    // ✅ Plan Price Management
    Route::prefix('plan-prices')->group(function () {
        Route::get('/', [PlanPriceController::class, 'index']);
        Route::get('/{id}', [PlanPriceController::class, 'show']);
        Route::put('/{id}', [PlanPriceController::class, 'update']);
        Route::post('/bulk-update', [PlanPriceController::class, 'bulkUpdate']);
    });

    // ✅ Profile Design Options Management
    Route::prefix('profile-design-options')->group(function () {
        Route::get('/', [ProfileDesignController::class, 'adminIndex']);
        Route::get('/types', [ProfileDesignController::class, 'getTypes']);
        Route::post('/', [ProfileDesignController::class, 'store']);
        Route::put('/{id}', [ProfileDesignController::class, 'update']);
        Route::delete('/{id}', [ProfileDesignController::class, 'destroy']);
        Route::post('/{id}/toggle-active', [ProfileDesignController::class, 'toggleActive']);
        Route::post('/{id}/set-default', [ProfileDesignController::class, 'setDefault']);
        Route::post('/reorder', [ProfileDesignController::class, 'reorder']);
    });

    // ✅ Profile Builder Management
    Route::prefix('profile-builder')->group(function () {
        // Sections CRUD
        Route::get('/sections', [ProfileBuilderSectionController::class, 'index']);
        Route::get('/sections/{id}', [ProfileBuilderSectionController::class, 'show']);
        Route::post('/sections', [ProfileBuilderSectionController::class, 'store']);
        Route::put('/sections/{id}', [ProfileBuilderSectionController::class, 'update']);
        Route::delete('/sections/{id}', [ProfileBuilderSectionController::class, 'destroy']);
        Route::post('/sections/reorder', [ProfileBuilderSectionController::class, 'reorder']);

        // Sections with fields (legacy compatibility)
        Route::get('/sections-with-fields', [ProfileBuilderFieldController::class, 'getSections']);

        // Fields CRUD
        Route::get('/fields', [ProfileBuilderFieldController::class, 'index']);
        Route::post('/fields', [ProfileBuilderFieldController::class, 'store']);
        Route::put('/fields/{id}', [ProfileBuilderFieldController::class, 'update']);
        Route::delete('/fields/{id}', [ProfileBuilderFieldController::class, 'destroy']);
    });

    // ✅ Card Template Management
    Route::prefix('card-templates')->group(function () {
        Route::get('/', [CardTemplateController::class, 'adminIndex']);
        Route::post('/', [CardTemplateController::class, 'store']);
        Route::put('/{id}', [CardTemplateController::class, 'update']);
        Route::delete('/{id}', [CardTemplateController::class, 'destroy']);
        Route::post('/{id}/retry', [CardTemplateController::class, 'retryProcessing']);
        Route::post('/{id}/toggle-visibility', [CardTemplateController::class, 'toggleVisibility']);
        Route::post('/{id}/mark-completed', [CardTemplateController::class, 'markCompleted']);
    });

    // ✅ Business User Custom Assignments (separate prefix to not conflict with BusinessUserController)
    Route::prefix('business-user-assignments')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Admin\BusinessUserAssignmentController::class, 'index']);
        Route::get('/{userId}', [\App\Http\Controllers\Api\Admin\BusinessUserAssignmentController::class, 'show']);
        Route::put('/{userId}', [\App\Http\Controllers\Api\Admin\BusinessUserAssignmentController::class, 'update']);
        Route::post('/{userId}/reset', [\App\Http\Controllers\Api\Admin\BusinessUserAssignmentController::class, 'resetToDefault']);
        Route::post('/{userId}/copy-from', [\App\Http\Controllers\Api\Admin\BusinessUserAssignmentController::class, 'copyFrom']);
    });

    // ✅ Data Export (CSV)
    Route::prefix('export')->group(function () {
        Route::get('/nfc-cards', [AdminExportController::class, 'exportNfcCards']);
        Route::get('/users', [AdminExportController::class, 'exportUsers']);
        Route::get('/analytics', [AdminExportController::class, 'exportAnalytics']);
    });

    // ✅ System Audit Log (Admin) — full filterable list + CSV export
    Route::prefix('audit-logs')->group(function () {
        Route::get('/', [AdminController::class, 'auditLogsIndex']);
        Route::get('/export-csv', [AdminController::class, 'auditLogsExportCsv']);
    });

    // ✅ Admin System Settings
    Route::prefix('system-settings')->group(function () {
        Route::get('/general', [\App\Http\Controllers\Api\Admin\AdminSystemSettingsController::class, 'getGeneralSettings']);
        Route::post('/general', [\App\Http\Controllers\Api\Admin\AdminSystemSettingsController::class, 'saveGeneralSettings']);
        Route::get('/payment', [\App\Http\Controllers\Api\Admin\AdminSystemSettingsController::class, 'getPaymentSettings']);
        Route::post('/payment', [\App\Http\Controllers\Api\Admin\AdminSystemSettingsController::class, 'savePaymentSettings']);
        Route::get('/email', [\App\Http\Controllers\Api\Admin\AdminSystemSettingsController::class, 'getEmailSettings']);
        Route::post('/email', [\App\Http\Controllers\Api\Admin\AdminSystemSettingsController::class, 'saveEmailSettings']);
        Route::post('/email/test', [\App\Http\Controllers\Api\Admin\AdminSystemSettingsController::class, 'sendTestEmail']);
        // Brand assets (Logo / Favicon)
        Route::prefix('brand')->group(function () {
            Route::post('/upload-logo', [\App\Http\Controllers\Api\Admin\AdminSystemSettingsController::class, 'uploadLogo']);
            Route::post('/remove-logo', [\App\Http\Controllers\Api\Admin\AdminSystemSettingsController::class, 'removeLogo']);
            Route::post('/upload-favicon', [\App\Http\Controllers\Api\Admin\AdminSystemSettingsController::class, 'uploadFavicon']);
            Route::post('/remove-favicon', [\App\Http\Controllers\Api\Admin\AdminSystemSettingsController::class, 'removeFavicon']);
        });
    });
});

// Public brand info (app name, logo, favicon) for login/home pages
Route::get('/system-settings/brand/public', [\App\Http\Controllers\Api\Admin\AdminSystemSettingsController::class, 'publicBrandInfo']);

// Public endpoint for viewing current plan prices
Route::get('/plan-prices/public', [PlanPriceController::class, 'publicPrices']);

// ✅ Public Payment Routes (for payment method selection on checkout)
Route::prefix('payment')->group(function () {
    Route::get('/rails', [PaymentController::class, 'getPaymentRails']);
    Route::post('/calculate-fees', [PaymentController::class, 'calculateFees']);
});

// ✅ n8n Webhook (Public - receives processed images)
Route::post('/webhooks/n8n/template-processed', [CardTemplateController::class, 'n8nWebhook']);

// ✅ Public Card Templates (for users)
Route::get('/card-templates', [CardTemplateController::class, 'index']);
Route::get('/card-templates/{id}', [CardTemplateController::class, 'show']);

// ✅ Fiuu Payment Gateway Webhooks (Public - receives payment notifications)
Route::prefix('webhooks/fiuu')->group(function () {
    Route::any('/return', [\App\Http\Controllers\Api\FiuuWebhookController::class, 'handleReturn']);
    Route::post('/notification', [\App\Http\Controllers\Api\FiuuWebhookController::class, 'handleNotification']);
    Route::any('/callback', [\App\Http\Controllers\Api\FiuuWebhookController::class, 'handleCallback']);
});
