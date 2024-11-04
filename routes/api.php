<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');

Route::middleware('auth:api')->group(function () {
    Route::get('orders', [OrderController::class, 'index']);           // Переглянути всі замовлення
    Route::get('orders/{id}', [OrderController::class, 'show']);       // Переглянути окреме замовлення
    Route::post('orders', [OrderController::class, 'store']);          // Створити нове замовлення
    Route::put('orders/{id}', [OrderController::class, 'update']);     // Оновити замовлення
    Route::delete('orders/{id}', [OrderController::class, 'destroy']); // Видалити замовлення
});