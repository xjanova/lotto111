<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\LotteryManageController;
use App\Http\Controllers\Admin\FinanceManageController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SmsDepositController;
use App\Http\Controllers\Admin\RiskControlController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
| Middleware: auth, admin.only, admin.ip
| Prefix: /admin
*/
Route::middleware(['auth', 'admin.only'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Members
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::get('/members/{user}', [MemberController::class, 'show'])->name('members.show');
    Route::put('/members/{user}/status', [MemberController::class, 'updateStatus'])->name('members.status');
    Route::post('/members/{user}/credit', [MemberController::class, 'adjustCredit'])->name('members.credit');

    // Lottery Management
    Route::get('/lottery', [LotteryManageController::class, 'index'])->name('lottery.index');
    Route::get('/lottery/types', [LotteryManageController::class, 'types'])->name('lottery.types');
    Route::put('/lottery/types/{type}', [LotteryManageController::class, 'updateType'])->name('lottery.types.update');
    Route::get('/lottery/rounds', [LotteryManageController::class, 'rounds'])->name('lottery.rounds');
    Route::post('/lottery/rounds', [LotteryManageController::class, 'createRound'])->name('lottery.rounds.create');
    Route::put('/lottery/rounds/{round}', [LotteryManageController::class, 'updateRound'])->name('lottery.rounds.update');
    Route::post('/lottery/results/{round}', [LotteryManageController::class, 'submitResult'])->name('lottery.results.submit');
    Route::get('/lottery/rates', [LotteryManageController::class, 'rates'])->name('lottery.rates');
    Route::put('/lottery/rates/{rate}', [LotteryManageController::class, 'updateRate'])->name('lottery.rates.update');
    Route::get('/lottery/limits', [LotteryManageController::class, 'limits'])->name('lottery.limits');
    Route::post('/lottery/limits', [LotteryManageController::class, 'createLimit'])->name('lottery.limits.create');

    // Finance Management
    Route::get('/finance/deposits', [FinanceManageController::class, 'deposits'])->name('finance.deposits');
    Route::put('/finance/deposits/{deposit}/approve', [FinanceManageController::class, 'approveDeposit'])->name('finance.deposits.approve');
    Route::put('/finance/deposits/{deposit}/reject', [FinanceManageController::class, 'rejectDeposit'])->name('finance.deposits.reject');
    Route::get('/finance/withdrawals', [FinanceManageController::class, 'withdrawals'])->name('finance.withdrawals');
    Route::put('/finance/withdrawals/{withdrawal}/approve', [FinanceManageController::class, 'approveWithdrawal'])->name('finance.withdrawals.approve');
    Route::put('/finance/withdrawals/{withdrawal}/reject', [FinanceManageController::class, 'rejectWithdrawal'])->name('finance.withdrawals.reject');
    Route::get('/finance/report', [FinanceManageController::class, 'report'])->name('finance.report');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/sms', [SettingsController::class, 'sms'])->name('settings.sms');
    Route::get('/settings/payment', [SettingsController::class, 'payment'])->name('settings.payment');
    Route::get('/settings/announcement', [SettingsController::class, 'announcement'])->name('settings.announcement');
    Route::get('/logs', [SettingsController::class, 'logs'])->name('logs');

    // SMS Auto-Deposit Management
    Route::prefix('sms-deposit')->name('sms-deposit.')->group(function () {
        Route::get('/dashboard', [SmsDepositController::class, 'dashboard'])->name('dashboard');

        // Device Management
        Route::get('/devices', [SmsDepositController::class, 'devices'])->name('devices.index');
        Route::post('/devices', [SmsDepositController::class, 'createDevice'])->name('devices.create');
        Route::put('/devices/{id}', [SmsDepositController::class, 'updateDevice'])->name('devices.update');
        Route::post('/devices/{id}/regenerate-keys', [SmsDepositController::class, 'regenerateKeys'])->name('devices.regenerate');
        Route::get('/devices/{id}/qr', [SmsDepositController::class, 'getDeviceQr'])->name('devices.qr');
        Route::delete('/devices/{id}', [SmsDepositController::class, 'deleteDevice'])->name('devices.delete');

        // SMS Notifications
        Route::get('/notifications', [SmsDepositController::class, 'notifications'])->name('notifications');
        Route::get('/pending', [SmsDepositController::class, 'pendingDeposits'])->name('pending');
        Route::post('/manual-match', [SmsDepositController::class, 'manualMatch'])->name('manual-match');
    });

    // Risk Management & Profit Control
    Route::prefix('risk')->name('risk.')->group(function () {
        Route::get('/dashboard', [RiskControlController::class, 'dashboard'])->name('dashboard');
        Route::get('/live-stats', [RiskControlController::class, 'liveStats'])->name('live-stats');

        // User Risk Profiles
        Route::get('/users', [RiskControlController::class, 'userProfiles'])->name('users');
        Route::get('/users/{user}', [RiskControlController::class, 'userProfile'])->name('users.show');
        Route::put('/users/{user}/win-rate', [RiskControlController::class, 'setWinRate'])->name('users.win-rate');
        Route::put('/users/{user}/rate-adjustment', [RiskControlController::class, 'setRateAdjustment'])->name('users.rate-adjustment');
        Route::put('/users/{user}/blocked-numbers', [RiskControlController::class, 'setBlockedNumbers'])->name('users.blocked-numbers');
        Route::put('/users/{user}/bet-limits', [RiskControlController::class, 'setBetLimits'])->name('users.bet-limits');

        // Global Settings
        Route::get('/settings', [RiskControlController::class, 'settings'])->name('settings');
        Route::put('/settings', [RiskControlController::class, 'updateSettings'])->name('settings.update');

        // AI Auto-Balance
        Route::post('/auto-balance', [RiskControlController::class, 'runAutoBalance'])->name('auto-balance');

        // Reports
        Route::get('/top-winners', [RiskControlController::class, 'topWinners'])->name('top-winners');
        Route::get('/top-losers', [RiskControlController::class, 'topLosers'])->name('top-losers');
        Route::get('/number-exposure', [RiskControlController::class, 'numberExposure'])->name('number-exposure');
        Route::get('/profit-snapshots', [RiskControlController::class, 'profitSnapshots'])->name('profit-snapshots');

        // Alerts
        Route::get('/alerts', [RiskControlController::class, 'alerts'])->name('alerts');
        Route::put('/alerts/{alert}/acknowledge', [RiskControlController::class, 'acknowledgeAlert'])->name('alerts.acknowledge');
        Route::put('/alerts/{alert}/resolve', [RiskControlController::class, 'resolveAlert'])->name('alerts.resolve');

        // Adjustment Logs
        Route::get('/adjustment-logs', [RiskControlController::class, 'adjustmentLogs'])->name('adjustment-logs');
    });
});
