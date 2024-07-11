<?php

use App\Http\Controllers\v1\AuthController;
use App\Http\Controllers\v1\CartController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    // Cart routes
    Route::apiResource('cart', CartController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::post('cart/merge', [CartController::class, 'mergeCart']);
    Route::delete('cart/clear', [CartController::class, 'clearCart']);
});
