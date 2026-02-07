<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Routes are loaded by bootstrap/app.php with the 'api' prefix.
| Controller implementations will be added during development.
| Route definitions are kept here as reference for the API structure.
|
*/

// Health Check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'version' => '0.1.0',
        'timestamp' => now()->toISOString(),
    ]);
});

/*
|--------------------------------------------------------------------------
| Auth Routes (Public) - TODO: Implement controllers
|--------------------------------------------------------------------------
*/
// Route::prefix('auth')->group(function () {
//     Route::post('/register', [AuthController::class, 'register']);
//     Route::post('/send-otp', [AuthController::class, 'sendOtp']);
//     Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
//     Route::post('/login', [AuthController::class, 'login']);
// });

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Deposit
    Route::prefix('deposit')->group(function () {
        Route::get('/info', [\App\Http\Controllers\Api\DepositController::class, 'info']);
        Route::post('/sms', [\App\Http\Controllers\Api\DepositController::class, 'createSmsDeposit']);
        Route::get('/history', [\App\Http\Controllers\Api\DepositController::class, 'history']);
        Route::get('/{id}/status', [\App\Http\Controllers\Api\DepositController::class, 'getStatus']);
        Route::post('/{id}/cancel', [\App\Http\Controllers\Api\DepositController::class, 'cancel']);
    });
});
