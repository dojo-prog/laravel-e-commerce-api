<?php

use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CartItemController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CheckoutController;
use App\Http\Controllers\Api\V1\CouponController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\OrderStatusHistoryController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductImageController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public Routes
    Route::apiResource('categories', CategoryController::class)
        ->only(['index', 'show']);
    Route::apiResource('products', ProductController::class)
        ->only(['index', 'show']);
    Route::get('/products/{product}/images', [ProductImageController::class, 'index'])
        ->name('products.images.index');

    // Authenticated Routes
    Route::middleware('auth:sanctum')->group(function () {
        // Address
        Route::apiResource('addresses', AddressController::class)
            ->only(['index', 'store', 'show', 'update', 'destroy']);
        
        // Cart
        Route::get('/cart', [CartController::class, 'show'])
            ->name('cart.show');

        // Cart Items
        Route::apiResource('cart-items', CartItemController::class)
            ->only(['store', 'show', 'update', 'destroy']);

        // Categories
        Route::apiResource('categories', CategoryController::class)
            ->except(['index', 'show']);

        // Products
        Route::apiResource('products', ProductController::class)
            ->except(['index', 'show']);

        // Product Images
        Route::post('/product-images', [ProductImageController::class, 'store'])
            ->name('product-images.store');
        Route::delete('/product-images/{image}', [ProductImageController::class, 'destroy'])
            ->name('product-images.destroy');

        // Coupons
        Route::apiResource('coupons', CouponController::class);

        // Order Status History
        Route::get('/order-status-histories', [OrderStatusHistoryController::class, 'index'])
            ->name('order-status-histories.index');
        Route::get('/order-status-histories/{history}', [OrderStatusHistoryController::class, 'show'])
            ->name('order-status-histories.show');

        // Checkout
        Route::post('/checkout', [CheckoutController::class, 'store'])
            ->name('checkout.store');

        // Orders
        Route::get('/orders', [OrderController::class, 'index'])
            ->name('orders.index');
        Route::get('/orders/{order}', [OrderController::class, 'show'])
            ->name('orders.show');
        Route::post('/orders/{order}/payments', [PaymentController::class, 'store'])
            ->name('orders.payments.store');

        // GET current user
        Route::get('/user', function (Request $request) {
            return UserResource::make($request->user());
        })->name('user');
    });
});

require __DIR__.'/auth.php';
