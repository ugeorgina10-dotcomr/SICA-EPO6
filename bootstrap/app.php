<?php

use App\Http\Middleware\CabecerasSeguridad;
use App\Http\Middleware\VerificarPermisoSica;
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
        // Detrás de un proxy (Render, Railway, Nginx...) Laravel debe confiar en él para saber que es HTTPS
        // y para ver la IP real de cada persona (límite de intentos de login).
        $middleware->trustProxies(at: '*');

        // Cabeceras de seguridad en todas las respuestas
        $middleware->append(CabecerasSeguridad::class);

        // Si alguien sin sesión pide una página, se le manda al inicio (ahí está el login)
        $middleware->redirectGuestsTo('/');

        // Guardia de permisos por rol: sica:control  /  sica:sync
        $middleware->alias([
            'sica' => VerificarPermisoSica::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // /api/* y /auth/* siempre responden JSON (401, 403, 419, 422, 429...) y nunca redirigen
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, Throwable $e) => $request->is('api/*', 'auth/*') || $request->expectsJson()
        );
    })->create();
