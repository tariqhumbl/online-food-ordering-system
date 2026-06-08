<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\API\RestaurantController;
use App\Http\Controllers\API\MenuCategoryController;
use App\Http\Controllers\API\MenuItemController;
use App\Http\Controllers\API\FoodOrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\NotificationController;
use App\Http\Controllers\API\RiderController;

/*
|--------------------------------------------------------------------------
| API Routes - Online Food Ordering System
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('registerUser', [AuthController::class, 'registerUser']);
    Route::post('registerRestaurant', [AuthController::class, 'registerRestaurant']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-request', [AuthController::class, 'resetPasswordRequest']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::get('{provider}', [SocialiteController::class, 'redirectToProvider']);
    Route::get('{provider}/callback', [SocialiteController::class, 'handleProviderCallback']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [AuthController::class, 'userProfile']);
        Route::post('edit-Profile', [AuthController::class, 'updateProfile']);
        Route::get('logout', [AuthController::class, 'logout']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::get('get-user-role', [AuthController::class, 'getUserRole']);
    });
});

// Public: list restaurants & menu (no auth)
Route::get('restaurants', [RestaurantController::class, 'index']);
Route::get('restaurants/{restaurant}', [RestaurantController::class, 'show']);

// Protected API (auth:sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('admin/restaurants', [RestaurantController::class, 'adminIndex']);
    Route::get('admin/riders', [RiderController::class, 'index']);
    Route::post('admin/riders', [RiderController::class, 'store']);
    Route::post('admin/restaurants', [RestaurantController::class, 'adminStore']);
    Route::post('restaurants', [RestaurantController::class, 'store']);
    Route::put('restaurants/{restaurant}', [RestaurantController::class, 'update']);
    Route::delete('restaurants/{restaurant}', [RestaurantController::class, 'destroy']);
    Route::get('restaurants/{restaurant}/menu', [MenuCategoryController::class, 'index']);
    Route::post('restaurants/{restaurant}/menu-categories', [MenuCategoryController::class, 'store']);
    Route::put('restaurants/{restaurant}/menu-categories/{menuCategory}', [MenuCategoryController::class, 'update']);
    Route::delete('restaurants/{restaurant}/menu-categories/{menuCategory}', [MenuCategoryController::class, 'destroy']);
    Route::post('restaurants/{restaurant}/menu-categories/{menuCategory}/items', [MenuItemController::class, 'store']);
    Route::put('restaurants/{restaurant}/menu-items/{menuItem}', [MenuItemController::class, 'update']);
    Route::delete('restaurants/{restaurant}/menu-items/{menuItem}', [MenuItemController::class, 'destroy']);
    Route::get('orders', [FoodOrderController::class, 'index']);
    Route::post('orders', [FoodOrderController::class, 'store']);
    Route::get('orders/{foodOrder}', [FoodOrderController::class, 'show']);
    Route::patch('orders/{foodOrder}/status', [FoodOrderController::class, 'updateStatus']);
    Route::post('payments', [PaymentController::class, 'store']);
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/read', [NotificationController::class, 'markAsRead']);
});
