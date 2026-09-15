<?php

use App\Http\Middleware\EnsureBlogEnabled;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'locale' => SetLocale::class,
            'blog' => EnsureBlogEnabled::class,
        ]);

        // The dashboard is the only authenticated area, and its login route is
        // named admin.login rather than the framework default of "login".
        $middleware->redirectGuestsTo(fn (): string => route('admin.login'));
        $middleware->redirectUsersTo(fn (): string => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
