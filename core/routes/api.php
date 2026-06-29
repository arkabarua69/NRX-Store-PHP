<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auto-topup/webhook', [App\Http\Controllers\Api\TopupWebhookController::class, 'handle'])
    ->middleware('throttle:30,1')
    ->name('auto.topup.webhook');

Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('reseller')->name('api.reseller.')->group(function () {
    Route::get('/products', [App\Http\Controllers\Api\ResellerApiController::class, 'products'])->name('products');
    Route::post('/order/place', [App\Http\Controllers\Api\ResellerApiController::class, 'placeOrder'])->name('order.place');
    Route::get('/order/{trackId}/status', [App\Http\Controllers\Api\ResellerApiController::class, 'orderStatus'])->name('order.status');
    Route::get('/orders', [App\Http\Controllers\Api\ResellerApiController::class, 'myOrders'])->name('orders');
    Route::get('/balance', [App\Http\Controllers\Api\ResellerApiController::class, 'balance'])->name('balance');
});
