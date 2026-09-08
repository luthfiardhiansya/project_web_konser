<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\MidtransController;

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
| CATEGORY / KATEGORI
|--------------------------------------------------------------------------
*/
Route::apiResource('categories', CategoryController::class);
Route::apiResource('kategori', CategoryController::class);

/*
|--------------------------------------------------------------------------
| EVENT
|--------------------------------------------------------------------------
*/
Route::apiResource('events', EventController::class);

/*
|--------------------------------------------------------------------------
| TICKET / TIKET
|--------------------------------------------------------------------------
*/
Route::apiResource('tickets', TicketController::class);
Route::apiResource('tiket', TicketController::class);

/*
|--------------------------------------------------------------------------
| ORDER / PESANAN
|--------------------------------------------------------------------------
*/
Route::apiResource('orders', OrderController::class);
Route::apiResource('pesanan', OrderController::class);

/*
|--------------------------------------------------------------------------
| USER / PENGGUNA
|--------------------------------------------------------------------------
*/
Route::apiResource('users', UserController::class);
Route::apiResource('pengguna', UserController::class);

/*
|--------------------------------------------------------------------------
| PAYMENT / PEMBAYARAN
|--------------------------------------------------------------------------
*/
Route::apiResource('payments', PaymentController::class);
Route::apiResource('pembayaran', PaymentController::class);
Route::post('/payments/snap-token', [MidtransController::class, 'createSnapToken']);
Route::post('/payments/finish', [MidtransController::class, 'finishPayment']);