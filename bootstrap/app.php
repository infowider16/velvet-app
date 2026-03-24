<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\AdminAuth;
use App\Http\Middleware\RedirectIfAdminAuthenticated;
use App\Http\Middleware\SetLanguageFromHeader;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->alias([
            'admin.auth' => AdminAuth::class,
            'admin.guest' => RedirectIfAdminAuthenticated::class,
            'setlang' => SetLanguageFromHeader::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Invalid or missing token.',
                    'error_code' => 'UNAUTHENTICATED',
                    'data' => null
                ], 401);
            }
        });
    })->create();
