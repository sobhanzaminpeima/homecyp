<?php

use App\Http\Controllers\Api\V1\Admin\AdvertisementController as AdminAdvertisementController;
use App\Http\Controllers\Api\V1\Admin\BusinessController as AdminBusinessController;
use App\Http\Controllers\Api\V1\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\V1\Admin\CityController as AdminCityController;
use App\Http\Controllers\Api\V1\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\V1\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Api\V1\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Api\V1\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Api\V1\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Api\V1\Admin\SubscriptionRequestController as AdminSubscriptionRequestController;
use App\Http\Controllers\Api\V1\Admin\UploadController as AdminUploadController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\V1\Admin\GrowthController as AdminGrowthController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AiChatController;
use App\Http\Controllers\Api\V1\BusinessController;
use App\Http\Controllers\Api\V1\BusinessDashboardController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CityController;
use App\Http\Controllers\Api\V1\DashboardMenuController;
use App\Http\Controllers\Api\V1\DashboardProductController;
use App\Http\Controllers\Api\V1\DashboardProjectController;
use App\Http\Controllers\Api\V1\DashboardServiceController;
use App\Http\Controllers\Api\V1\DashboardSubscriptionController;
use App\Http\Controllers\Api\V1\DiscoveryController;
use App\Http\Controllers\Api\V1\EngagementController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\PackageController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\ReviewController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\SetupController;
use App\Http\Controllers\Api\V1\UploadController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Setup wizard fallback — self-locks after first successful run.
    Route::middleware('throttle:5,1')->get('/setup/{token}', [SetupController::class, 'run']);

    // Public
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:password-reset');
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:password-reset');

    Route::get('/home', [HomeController::class, 'index']);
    Route::post('/ai/chat', [AiChatController::class, 'chat'])->middleware('throttle:20,1');
    Route::get('/ai/conversations/{id}', [AiChatController::class, 'history'])->middleware('throttle:30,1');
    Route::get('/cities', [CityController::class, 'index']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/settings', [SettingController::class, 'index']);

    Route::get('/businesses', [BusinessController::class, 'index']);
    Route::get('/businesses/{slug}', [BusinessController::class, 'show']);
    Route::get('/businesses/{slug}/similar', [BusinessController::class, 'similar']);
    Route::get('/businesses/{slug}/reviews', [ReviewController::class, 'index']);

    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);

    Route::get('/packages', [PackageController::class, 'index']);
    Route::get('/deals', [DiscoveryController::class, 'deals']);
    Route::get('/events', [DiscoveryController::class, 'events']);
    Route::post('/track', [EngagementController::class, 'track'])->middleware('throttle:60,1');
    Route::post('/businesses/{business:slug}/report', [EngagementController::class, 'report'])->middleware('throttle:10,1');
    Route::post('/businesses/{business:slug}/inquiries', [EngagementController::class, 'inquiry'])->middleware('throttle:10,1');

    // Authenticated (any account type)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::put('/auth/profile', [AuthController::class, 'updateProfile']);

        Route::post('/businesses/{slug}/reviews', [ReviewController::class, 'store']);
        Route::delete('/businesses/{slug}/reviews/{review}', [ReviewController::class, 'destroy']);
        Route::post('/businesses/{slug}/favorite', [FavoriteController::class, 'toggle']);
        Route::get('/favorites', [FavoriteController::class, 'index']);

        Route::post('/businesses', [BusinessController::class, 'store']);
        Route::put('/businesses/{business}', [BusinessController::class, 'update']);
        Route::post('/uploads', [UploadController::class, 'store']);
        Route::post('/businesses/{business:slug}/claim', [EngagementController::class, 'claim']);
        Route::get('/notifications', [EngagementController::class, 'notifications']);
        Route::put('/notifications/{id}/read', [EngagementController::class, 'read']);

        // Business owner dashboard
        Route::prefix('dashboard')->group(function () {
            Route::get('/business', [BusinessDashboardController::class, 'show']);
            Route::get('/stats', [BusinessDashboardController::class, 'stats']);
            Route::post('/menus', [DashboardMenuController::class, 'store']);
            Route::put('/menus/{menu}', [DashboardMenuController::class, 'update']);
            Route::delete('/menus/{menu}', [DashboardMenuController::class, 'destroy']);
            Route::post('/menus/{menu}/items', [DashboardMenuController::class, 'storeItem']);
            Route::put('/menu-items/{item}', [DashboardMenuController::class, 'updateItem']);
            Route::delete('/menu-items/{item}', [DashboardMenuController::class, 'destroyItem']);

            Route::post('/projects', [DashboardProjectController::class, 'store']);
            Route::put('/projects/{project}', [DashboardProjectController::class, 'update']);
            Route::delete('/projects/{project}', [DashboardProjectController::class, 'destroy']);

            Route::post('/products', [DashboardProductController::class, 'store']);
            Route::put('/products/{product}', [DashboardProductController::class, 'update']);
            Route::delete('/products/{product}', [DashboardProductController::class, 'destroy']);

            Route::post('/services', [DashboardServiceController::class, 'store']);
            Route::put('/services/{service}', [DashboardServiceController::class, 'update']);
            Route::delete('/services/{service}', [DashboardServiceController::class, 'destroy']);

            Route::get('/subscription-requests', [DashboardSubscriptionController::class, 'index']);
            Route::post('/subscription-requests', [DashboardSubscriptionController::class, 'store']);
            Route::get('/inquiries', [EngagementController::class, 'ownerInquiries']);
        });
    });

    // Admin
    Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);

        Route::apiResource('businesses', AdminBusinessController::class);
        Route::apiResource('categories', AdminCategoryController::class);
        Route::apiResource('cities', AdminCityController::class);
        Route::apiResource('advertisements', AdminAdvertisementController::class);
        Route::apiResource('packages', AdminPackageController::class);
        Route::apiResource('users', AdminUserController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::apiResource('reviews', AdminReviewController::class)->only(['index', 'update', 'destroy']);
        Route::apiResource('projects', AdminProjectController::class)->only(['index', 'update', 'destroy']);
        Route::apiResource('subscription-requests', AdminSubscriptionRequestController::class)->only(['index', 'update']);

        Route::get('/settings', [AdminSettingController::class, 'index']);
        Route::put('/settings', [AdminSettingController::class, 'update']);
        Route::post('/uploads', [AdminUploadController::class, 'store']);
        Route::get('/growth', [AdminGrowthController::class, 'index']);
        Route::put('/growth/claims/{id}', [AdminGrowthController::class, 'claim']);
        Route::put('/growth/reports/{id}', [AdminGrowthController::class, 'report']);
        Route::put('/growth/inquiries/{id}', [AdminGrowthController::class, 'inquiry']);
        Route::post('/growth/deals', [AdminGrowthController::class, 'storeDeal']);
        Route::delete('/growth/deals/{deal}', [AdminGrowthController::class, 'destroyDeal']);
        Route::post('/growth/events', [AdminGrowthController::class, 'storeEvent']);
        Route::delete('/growth/events/{event}', [AdminGrowthController::class, 'destroyEvent']);
    });
});
