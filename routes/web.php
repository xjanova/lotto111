<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\LotteryController;
use App\Http\Controllers\App\ProfileController;

/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('/login', fn () => view('pages.auth.login'))->name('login');
    Route::get('/register', fn () => view('pages.auth.register'))->name('register');
    Route::get('/register/{referral?}', fn () => view('pages.auth.register'))->name('register.referral');
    Route::get('/verify-otp', fn () => view('pages.auth.verify-otp'))->name('verify-otp');
});

/*
|--------------------------------------------------------------------------
| Authenticated Member Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified.phone', 'check.status'])->prefix('app')->name('app.')->group(function () {
    // Dashboard
    Route::get('/main', fn () => view('pages.app.dashboard'))->name('dashboard');

    // Lottery
    Route::get('/lottery', fn () => view('pages.app.lottery'))->name('lottery');
    Route::get('/lottery-game/{round}', fn () => view('pages.app.betting'))->name('betting');

    // Yeekee
    Route::get('/special-lottery-game/YEEKEE', fn () => view('pages.app.yeekee'))->name('yeekee');

    // Set Lottery
    Route::get('/set-lottery-game/SET', fn () => view('pages.app.set-lottery'))->name('set-lottery');

    // Results
    Route::get('/result-reward', fn () => view('pages.app.results'))->name('results');

    // Tickets
    Route::get('/ticket', fn () => view('pages.app.tickets'))->name('tickets');

    // Number Sets
    Route::get('/number-set', fn () => view('pages.app.number-sets'))->name('number-sets');

    // Finance
    Route::get('/deposit', fn () => view('pages.app.deposit'))->name('deposit');
    Route::get('/withdraw', fn () => view('pages.app.withdraw'))->name('withdraw');
    Route::get('/transaction', fn () => view('pages.app.transactions'))->name('transactions');
    Route::get('/financial-report', fn () => view('pages.app.financial-report'))->name('financial-report');
    Route::get('/topup-sms', fn () => view('pages.app.topup'))->name('topup');

    // Affiliate
    Route::get('/affiliate', fn () => view('pages.app.affiliate'))->name('affiliate');

    // Chat / Inbox
    Route::get('/inbox', fn () => view('pages.app.inbox'))->name('inbox');

    // Games
    Route::get('/games', fn () => view('pages.app.games'))->name('games');

    // Profile
    Route::get('/profile', fn () => view('pages.app.profile'))->name('profile');
});
