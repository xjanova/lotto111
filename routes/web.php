<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth'])->group(function () {
    // Risk Control
    Route::prefix('risk')->middleware([\App\Http\Middleware\EnsureUserIsAdmin::class])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\RiskControlController::class, 'dashboard']);
        Route::get('/live-stats', [\App\Http\Controllers\Admin\RiskControlController::class, 'liveStats']);
        Route::get('/settings', [\App\Http\Controllers\Admin\RiskControlController::class, 'settings']);
        Route::put('/settings', [\App\Http\Controllers\Admin\RiskControlController::class, 'updateSettings']);
        Route::get('/top-winners', [\App\Http\Controllers\Admin\RiskControlController::class, 'topWinners']);
        Route::get('/top-losers', [\App\Http\Controllers\Admin\RiskControlController::class, 'topLosers']);
        Route::get('/users', [\App\Http\Controllers\Admin\RiskControlController::class, 'userProfiles']);
        Route::get('/users/{user}', [\App\Http\Controllers\Admin\RiskControlController::class, 'userProfile']);
        Route::put('/users/{user}/win-rate', [\App\Http\Controllers\Admin\RiskControlController::class, 'setWinRate']);
        Route::put('/users/{user}/rate-adjustment', [\App\Http\Controllers\Admin\RiskControlController::class, 'setRateAdjustment']);
        Route::put('/users/{user}/blocked-numbers', [\App\Http\Controllers\Admin\RiskControlController::class, 'setBlockedNumbers']);
        Route::put('/users/{user}/bet-limits', [\App\Http\Controllers\Admin\RiskControlController::class, 'setBetLimits']);
        Route::post('/auto-balance', [\App\Http\Controllers\Admin\RiskControlController::class, 'runAutoBalance']);
    });
});
