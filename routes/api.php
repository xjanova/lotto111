<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LotteryController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\NumberSetController;
use App\Http\Controllers\Api\AffiliateController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\DepositController;
use App\Http\Controllers\Api\V1\SmsPaymentController;

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

// Public results
Route::get('/results', [ResultController::class, 'index']);
Route::get('/results/{date}', [ResultController::class, 'byDate']);

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // User Profile
    Route::get('/user/profile', [AuthController::class, 'profile']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
    Route::put('/user/change-password', [AuthController::class, 'changePassword']);
    Route::get('/user/balance', [AuthController::class, 'balance']);

    // Lottery
    Route::get('/lottery/types', [LotteryController::class, 'types']);
    Route::get('/lottery/rounds', [LotteryController::class, 'activeRounds']);
    Route::get('/lottery/rounds/{round}', [LotteryController::class, 'showRound']);
    Route::get('/lottery/rates/{lotteryType}', [LotteryController::class, 'rates']);
    Route::post('/lottery/bet', [LotteryController::class, 'placeBet']);

    // Yeekee
    Route::get('/yeekee/rounds', [LotteryController::class, 'yeekeeRounds']);
    Route::post('/yeekee/bet', [LotteryController::class, 'placeYeekeeBet']);

    // Results
    Route::get('/results/type/{type}', [ResultController::class, 'byType']);

    // Tickets
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::get('/tickets/today', [TicketController::class, 'today']);
    Route::get('/tickets/{ticket}', [TicketController::class, 'show']);
    Route::post('/tickets/reuse/{ticket}', [TicketController::class, 'reuse']);

    // Number Sets
    Route::apiResource('number-sets', NumberSetController::class);

    // Finance
    Route::post('/deposits', [FinanceController::class, 'deposit']);
    Route::get('/deposits', [FinanceController::class, 'depositHistory']);
    Route::post('/withdrawals', [FinanceController::class, 'withdraw']);
    Route::get('/withdrawals', [FinanceController::class, 'withdrawHistory']);
    Route::get('/transactions', [FinanceController::class, 'transactions']);
    Route::get('/financial-report', [FinanceController::class, 'report']);

    // Affiliate
    Route::get('/affiliate/dashboard', [AffiliateController::class, 'dashboard']);
    Route::get('/affiliate/members', [AffiliateController::class, 'members']);
    Route::get('/affiliate/commissions', [AffiliateController::class, 'commissions']);
    Route::post('/affiliate/withdraw', [AffiliateController::class, 'withdrawCommission']);
    Route::get('/affiliate/link', [AffiliateController::class, 'link']);

    // Messages
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages', [MessageController::class, 'store']);

    // SMS Auto-Deposit (ฝากเงินอัตโนมัติ)
    Route::prefix('deposit')->group(function () {
        Route::get('/info', [DepositController::class, 'info']);
        Route::post('/sms', [DepositController::class, 'createSmsDeposit']);
        Route::get('/{id}/status', [DepositController::class, 'getStatus']);
        Route::post('/{id}/cancel', [DepositController::class, 'cancel']);
        Route::get('/history', [DepositController::class, 'history']);
    });
});

/*
|--------------------------------------------------------------------------
| SMS Checker Device Routes (smschecker Android App)
|--------------------------------------------------------------------------
| Middleware: verify.sms.device
| ใช้ X-Api-Key header แทน auth:sanctum
*/
Route::prefix('v1/sms-payment')
    ->middleware('verify.sms.device')
    ->group(function () {
        Route::post('/notify', [SmsPaymentController::class, 'notify']);
        Route::get('/status', [SmsPaymentController::class, 'status']);
        Route::post('/register-device', [SmsPaymentController::class, 'registerDevice']);
    });
