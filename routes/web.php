<?php

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
