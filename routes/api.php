<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\LotteryController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NumberSetController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
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
| Auth Routes (Public)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

/*
|--------------------------------------------------------------------------
| Public Routes (Results)
|--------------------------------------------------------------------------
*/
Route::get('/results', [ResultController::class, 'index']);
Route::get('/results/{date}', [ResultController::class, 'byDate'])->where('date', '\d{4}-\d{2}-\d{2}');
Route::get('/results/type/{typeId}', [ResultController::class, 'byType']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', \App\Http\Middleware\CheckUserStatus::class])->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // User Profile
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'profile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::put('/change-password', [UserController::class, 'changePassword']);
        Route::get('/balance', [FinanceController::class, 'balance']);
        Route::get('/bank-accounts', [UserController::class, 'bankAccounts']);
        Route::post('/bank-accounts', [UserController::class, 'addBankAccount']);
    });

    // Lottery
    Route::prefix('lottery')->group(function () {
        Route::get('/types', [LotteryController::class, 'types']);
        Route::get('/rounds', [LotteryController::class, 'rounds']);
        Route::get('/rounds/{id}', [LotteryController::class, 'roundDetail']);
        Route::get('/rates/{roundId}', [LotteryController::class, 'rates']);
        Route::post('/bet', [LotteryController::class, 'placeBet']);
    });

    // Tickets
    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketController::class, 'index']);
        Route::get('/today', [TicketController::class, 'today']);
        Route::get('/{id}', [TicketController::class, 'show']);
        Route::post('/{id}/cancel', [TicketController::class, 'cancel']);
        Route::post('/reuse/{id}', [TicketController::class, 'reuse']);
    });

    // Number Sets
    Route::apiResource('number-sets', NumberSetController::class)->except(['show']);

    // Deposit (SMS Auto)
    Route::prefix('deposit')->group(function () {
        Route::get('/info', [\App\Http\Controllers\Api\DepositController::class, 'info']);
        Route::post('/sms', [\App\Http\Controllers\Api\DepositController::class, 'createSmsDeposit']);
        Route::get('/history', [\App\Http\Controllers\Api\DepositController::class, 'history']);
        Route::get('/{id}/status', [\App\Http\Controllers\Api\DepositController::class, 'getStatus']);
        Route::post('/{id}/cancel', [\App\Http\Controllers\Api\DepositController::class, 'cancel']);
    });

    // Finance
    Route::post('/withdrawals', [FinanceController::class, 'withdraw']);
    Route::get('/withdrawals', [FinanceController::class, 'withdrawals']);
    Route::get('/transactions', [FinanceController::class, 'transactions']);
    Route::get('/financial-report', [FinanceController::class, 'financialReport']);

    // Affiliate
    Route::prefix('affiliate')->group(function () {
        Route::get('/dashboard', [FinanceController::class, 'affiliateDashboard']);
        Route::get('/members', [FinanceController::class, 'affiliateMembers']);
        Route::get('/commissions', [FinanceController::class, 'affiliateCommissions']);
        Route::post('/withdraw', [FinanceController::class, 'affiliateWithdraw']);
        Route::get('/link', [FinanceController::class, 'affiliateLink']);
    });

    // Messages / Chat
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);
});
