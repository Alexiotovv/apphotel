<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ClienteMiddleware;

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
        
        // También puedes agregar middlewares globales si necesitas
        // $middleware->append([
        //     \App\Http\Middleware\CustomMiddleware::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();