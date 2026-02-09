<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin.only' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'check.status' => \App\Http\Middleware\CheckUserStatus::class,
            'phone.verified' => \App\Http\Middleware\EnsurePhoneVerified::class,
            'sms.device' => \App\Http\Middleware\VerifySmsCheckerDevice::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            if ($request->expectsJson()) return null;
            return str_starts_with($request->path(), 'admin') ? route('admin.login') : route('member.login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
