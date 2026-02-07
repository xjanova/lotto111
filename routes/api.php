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
| Authenticated Routes - TODO: Implement controllers
|--------------------------------------------------------------------------
*/
// See docs/PRD.md for full API specification
