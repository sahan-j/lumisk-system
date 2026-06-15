<?php

use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\ClientAuth;
use App\Http\Middleware\RedirectIfAdmin;
use App\Http\Middleware\RedirectIfClient;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Illuminate\Support\Facades\Route::middleware('web')
                ->prefix('admin')
                ->as('admin.')
                ->group(base_path('routes/admin.php'));

            Illuminate\Support\Facades\Route::middleware('web')
                ->prefix('portal')
                ->as('portal.')
                ->group(base_path('routes/portal.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin.auth' => AdminAuth::class,
            'client.auth' => ClientAuth::class,
            'admin.guest' => RedirectIfAdmin::class,
            'client.guest' => RedirectIfClient::class,
            'permission' => CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
