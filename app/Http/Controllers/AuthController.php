<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /** Inicia sesión con correo y contraseña. Máximo 5 intentos fallidos por minuto (por correo + IP). */
    public function login(Request $request): JsonResponse
    {
        $credenciales = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $clave = Str::lower($credenciales['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($clave, 5)) {
            $segundos = RateLimiter::availableIn($clave);

            return response()->json([
                'ok'      => false,
                'mensaje' => "Demasiados intentos. Intenta de nuevo en {$segundos} segundos.",
            ], 429);
        }

        if (!Auth::attempt($credenciales)) {
            RateLimiter::hit($clave, 60);

            return response()->json([
                'ok'      => false,
                'mensaje' => 'Correo o contraseña incorrectos.',
            ], 422);
        }

        RateLimiter::clear($clave);
        $request->session()->regenerate();

        return response()->json($this->datosUsuario($request));
    }

    /** Cierra la sesión y descarta la sesión actual. */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['ok' => true, 'csrf' => csrf_token()]);
    }

    /** Devuelve quién tiene la sesión activa. Sin sesión responde 401 pero entrega un token CSRF nuevo. */
    public function me(Request $request): JsonResponse
    {
        if (!$request->user()) {
            return response()->json(['ok' => false, 'mensaje' => 'Sin sesión.', 'csrf' => csrf_token()], 401);
        }

        return response()->json($this->datosUsuario($request));
    }

    private function datosUsuario(Request $request): array
    {
        $u = $request->user();

        return [
            'ok'     => true,
            'nombre' => $u->name,
            'email'  => $u->email,
            'rol'    => $u->rol,
            'csrf'   => csrf_token(),
        ];
    }
}