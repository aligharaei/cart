<?php

use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\CartController;
use App\Http\Controllers\v1\ProductController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

// Product routes without authentication
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{product}', [ProductController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {

    // Product routes with admin middleware for authenticated users
    Route::prefix('products')->middleware('isAdmin')->group(function () {
        Route::post('', [ProductController::class, 'store']);
        Route::put('/{product}', [ProductController::class, 'update']);
        Route::delete('/{product}', [ProductController::class, 'destroy']);
    });

    // Cart routes
    Route::delete('cart/clear', [CartController::class, 'clearCart']);
    Route::post('cart/merge', [CartController::class, 'mergeCart']);
    Route::apiResource('cart', CartController::class)->only(['index', 'store', 'update', 'destroy']);
});
