<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ClienteMiddleware;
use App\Http\Middleware\Localization; // Importa tu middleware

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Registra los middlewares con alias
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'cliente' => \App\Http\Middleware\ClienteMiddleware::class,
            'cliente.completo' => \App\Http\Middleware\ClienteCompletoMiddleware::class,
        ]);
        
        $middleware->web(append: [
            \App\Http\Middleware\Localization::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();