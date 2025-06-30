<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');

Route::middleware('auth:api')->group(function () {
    Route::get('orders', [OrderController::class, 'index']);           // Переглянути всі замовлення
    Route::get('orders/{id}', [OrderController::class, 'show']);       // Переглянути окреме замовлення
    Route::post('orders', [OrderController::class, 'store']);          // Створити нове замовлення
    Route::put('orders/{id}', [OrderController::class, 'update']);     // Оновити замовлення
    Route::delete('orders/{id}', [OrderController::class, 'destroy']); // Видалити замовлення
    Route::get('/orders/export-excel', [OrderController::class, 'exportExcel']);
    Route::get('/orders/export-csv', [OrderController::class, 'exportCsv']);
    Route::get('/orders/export-pdf', [OrderController::class, 'exportPdf']);
    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart/add', [CartController::class, 'add']);
    Route::delete('cart/{item}', [CartController::class, 'remove']);
    Route::post('cart/checkout', [CartController::class, 'checkout']);
    Route::get('products', [ProductController::class, 'index']);
    Route::post('products', [ProductController::class, 'store']);
    Route::get('products/{product}', [ProductController::class, 'show']);
    Route::put('products/{product}', [ProductController::class, 'update']);
    Route::delete('products/{product}', [ProductController::class, 'destroy']);

    // Analytics
    Route::get('analytics', [\App\Http\Controllers\AnalyticsController::class, 'summary']);
});
