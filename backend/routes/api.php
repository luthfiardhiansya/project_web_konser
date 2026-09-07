<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/profile', [AuthController::class, 'profile']);

    Route::post('/logout', [AuthController::class, 'logout']);

});

/*
|--------------------------------------------------------------------------
| CATEGORY
|--------------------------------------------------------------------------
*/

Route::apiResource('categories', CategoryController::class);

/*
|--------------------------------------------------------------------------
| EVENT
|--------------------------------------------------------------------------
*/

Route::apiResource('events', EventController::class);

/*
|--------------------------------------------------------------------------
| TICKET
|--------------------------------------------------------------------------
*/

Route::apiResource('tickets', TicketController::class);

/*
|--------------------------------------------------------------------------
| ORDER
|--------------------------------------------------------------------------
*/

Route::apiResource('orders', OrderController::class);

/*
|--------------------------------------------------------------------------
| PAYMENT
|--------------------------------------------------------------------------
*/

Route::apiResource('payments', PaymentController::class);