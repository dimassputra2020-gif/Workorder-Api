<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->group('api', [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,

        ]);

        // PENTING: Pastikan CORS middleware terdaftar
        $middleware->validateCsrfTokens(except: [
                    'api/*',
        ]);

        $middleware->alias([

            'auth' => \App\Http\Middleware\Authenticate::class,
            'check.external.token' => \App\Http\Middleware\CheckExternalToken::class,
            // 'permission' => \App\Http\Middleware\CheckPermission::class,
            'providers' => [
    App\Providers\AppServiceProvider::class,
],


            'token-validated' => \Gemboot\Middleware\TokenValidated::class,
            'token-validated-client' => \App\Http\Middleware\TokenValidatedClient::class,
            'role' => \Gemboot\Middleware\HasRole::class,
            'permission' => \Gemboot\Middleware\HasPermissionTo::class,
        ]);
    })

    // 🧩 Tambahkan ini untuk definisi rate limiter "api"
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->booting(function () {
        RateLimiter::for('api', function ($request) {
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });
    })
    ->create();

RateLimiter::for('api', function ($request) {
    return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
});
