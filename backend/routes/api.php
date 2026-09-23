<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\IssuedTicketController;
use App\Http\Controllers\Api\MidtransController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ScannerController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/my-tickets', [IssuedTicketController::class, 'myTickets']);

    // Admin — riwayat scan QR (semua tiket berstatus used)
    Route::get('/issued-tickets', [IssuedTicketController::class, 'scannedTickets']);

    Route::post('/scanner/scan', [ScannerController::class, 'scan']);

    // Upload gambar poster event (admin)
    Route::post('/upload-image', [UploadController::class, 'uploadImage']);

    // ─── Laporan & Statistik ─────────────────────────────────────────────
    Route::prefix('reports')->group(function () {
        Route::get('/statistik', [ReportController::class, 'statistik']);
        Route::get('/penjualan', [ReportController::class, 'penjualan']);
        Route::get('/event', [ReportController::class, 'laporanEvent']);
        Route::get('/tiket', [ReportController::class, 'laporanTiket']);
        Route::get('/scan', [ReportController::class, 'laporanScan']);
        Route::get('/pengguna', [ReportController::class, 'laporanPengguna']);
        Route::get('/export/pdf/{type}', [ReportController::class, 'exportPdf']);
        Route::get('/export/excel/{type}', [ReportController::class, 'exportExcel']);
        Route::post('/export/excel-page', [ReportController::class, 'exportExcelPage']);
    });
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
// Midtrans routes harus didaftarkan SEBELUM apiResource agar tidak tertangkap oleh {payment} wildcard
Route::post('/payments/snap-token', [MidtransController::class, 'createSnapToken']);
Route::post('/payments/finish', [MidtransController::class, 'finishPayment']);
Route::post('/payments/notification', [MidtransController::class, 'handleNotification']);

Route::apiResource('payments', PaymentController::class);
Route::apiResource('pembayaran', PaymentController::class);
