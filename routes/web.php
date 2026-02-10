<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\MemberController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $lotteryTypes = \App\Models\LotteryType::where('is_active', true)
        ->orderBy('sort_order')
        ->get();

    $openRounds = \App\Models\LotteryRound::with('lotteryType')
        ->where('status', 'open')
        ->orderBy('close_at')
        ->limit(6)
        ->get();

    $latestResults = \App\Models\LotteryRound::with('lotteryType')
        ->where('status', 'resulted')
        ->orderByDesc('result_at')
        ->limit(6)
        ->get();

    $siteName = \App\Models\Setting::getValue('site_name', 'Lotto111');
    $marquee = \App\Models\Setting::getValue('marquee_text', '');

    return view('welcome', compact('lotteryTypes', 'openRounds', 'latestResults', 'siteName', 'marquee'));
});

// OTP Login/Register
Route::get('/login', function () {
    return view('auth.otp-login');
})->name('login');

/*
|--------------------------------------------------------------------------
| Member Auth (web session - Firebase OTP)
|--------------------------------------------------------------------------
*/
Route::get('/register', [AuthController::class, 'showRegister'])->middleware('guest')->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('guest');
Route::get('/login', [AuthController::class, 'showLogin'])->middleware('guest')->name('member.login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('member.logout');

/*
|--------------------------------------------------------------------------
| Member Dashboard (authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('member')->name('member.')->group(function () {
    Route::get('/', [MemberController::class, 'dashboard'])->name('dashboard');
    Route::get('/lottery', [MemberController::class, 'lottery'])->name('lottery');
    Route::get('/tickets', [MemberController::class, 'tickets'])->name('tickets');
    Route::get('/deposit', [MemberController::class, 'deposit'])->name('deposit');
    Route::get('/withdrawal', [MemberController::class, 'withdrawal'])->name('withdrawal');
    Route::get('/results', [MemberController::class, 'results'])->name('results');
    Route::get('/transactions', [MemberController::class, 'transactions'])->name('transactions');
    Route::get('/notifications', [MemberController::class, 'notifications'])->name('notifications');
    Route::get('/referral', [MemberController::class, 'referral'])->name('referral');
    Route::post('/referral/withdraw', [MemberController::class, 'withdrawCommission'])->name('referral.withdraw');
    Route::get('/profile', [MemberController::class, 'profile'])->name('profile');
    Route::put('/profile', [MemberController::class, 'updateProfile'])->name('profile.update');
});

/*
|--------------------------------------------------------------------------
| Admin Auth (web session)
|--------------------------------------------------------------------------
*/
Route::get('/admin/login', function () {
    if (! User::whereIn('role', [UserRole::Admin, UserRole::SuperAdmin])->exists()) {
        return redirect()->route('admin.setup');
    }

    return auth()->check() && auth()->user()->isAdmin()
        ? redirect()->route('admin.dashboard')
        : view('admin.login');
})->middleware('guest')->name('admin.login');

Route::post('/admin/login', function (Request $request) {
    $credentials = $request->validate([
        'phone' => 'required|string',
        'password' => 'required|string',
    ]);

    if (! auth()->attempt($credentials, $request->boolean('remember'))) {
        return back()->withErrors(['phone' => 'Phone or password is incorrect.'])->onlyInput('phone');
    }

    $user = auth()->user();

    if (! $user->isAdmin()) {
        auth()->logout();
        return back()->withErrors(['phone' => 'Access denied. Admin only.'])->onlyInput('phone');
    }

    if (! $user->isActive()) {
        auth()->logout();
        return back()->withErrors(['phone' => 'Account is suspended.'])->onlyInput('phone');
    }

    $request->session()->regenerate();

    return redirect()->intended(route('admin.dashboard'));
})->middleware('guest')->name('login.attempt');

Route::post('/admin/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/admin/login');
})->middleware('auth')->name('admin.logout');

/*
|--------------------------------------------------------------------------
| First-Time Admin Setup
|--------------------------------------------------------------------------
| Only accessible when no admin user exists in the database.
*/
Route::get('/admin/setup', function () {
    if (User::whereIn('role', [UserRole::Admin, UserRole::SuperAdmin])->exists()) {
        return redirect()->route('admin.login');
    }

    return view('admin.setup');
})->middleware('guest')->name('admin.setup');

Route::post('/admin/setup', function (Request $request) {
    if (User::whereIn('role', [UserRole::Admin, UserRole::SuperAdmin])->exists()) {
        return redirect()->route('admin.login');
    }

    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'phone' => 'required|string|max:20|unique:users,phone',
        'email' => 'nullable|email|max:255|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = User::create([
        'name' => $validated['name'],
        'phone' => $validated['phone'],
        'email' => $validated['email'] ?? null,
        'password' => $validated['password'],
        'role' => UserRole::SuperAdmin,
        'status' => UserStatus::Active,
    ]);

    auth()->login($user);
    $request->session()->regenerate();

    return redirect()->route('admin.dashboard');
})->middleware('guest')->name('admin.setup.store');
