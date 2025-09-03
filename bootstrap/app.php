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
        $middleware->alias([
            'auth.session' => \App\Http\Middleware\EnsureAuthenticated::class,
            'role' => \App\Http\Middleware\EnsureRole::class,
            'supervisor.self' => \App\Http\Middleware\EnsureSupervisorSelf::class,
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
            'admin.self' => \App\Http\Middleware\EnsureAdminSelf::class,
            'student.self' => \App\Http\Middleware\EnsureStudentSelf::class,
            'developer.self' => \App\Http\Middleware\EnsureDeveloperSelf::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
