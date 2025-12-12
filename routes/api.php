<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AssetController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\AuthController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    // Route::get('/profile', [AssetController::class, 'profile']);
    // Route::post('/assets', [AssetController::class, 'store']);
    // Route::get('/orders', [OrderController::class, 'index']);
    // Route::post('/orders', [OrderController::class, 'store']);
    // Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    // Route::post('/transactions', [TransactionController::class, 'store']);
});


