<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'check.tenant' => \App\Http\Middleware\CheckTenantStatus::class,
            'detect.tenant' => \App\Http\Middleware\DetectTenant::class,
            'switch.db' => \App\Http\Middleware\SwitchTenantDatabase::class,
        ]);

        // Switch tenant context before session/auth middleware run.
        // Keep status checks appended so session flashing still works.
        $middleware->web(prepend: [
            \App\Http\Middleware\DetectTenant::class,
            \App\Http\Middleware\SwitchTenantDatabase::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\CheckTenantStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
