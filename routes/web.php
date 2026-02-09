<?php

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\Request;
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
})->middleware('guest')->name('login');

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
        return redirect()->route('login');
    }

    return view('admin.setup');
})->middleware('guest')->name('admin.setup');

Route::post('/admin/setup', function (Request $request) {
    if (User::whereIn('role', [UserRole::Admin, UserRole::SuperAdmin])->exists()) {
        return redirect()->route('login');
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
