<?php

namespace App\Http\Controllers;

use App\Models\Orientador;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /** Inicia sesión con correo y contraseña. Máximo 5 intentos fallidos por minuto (por correo + IP). */
    public function login(Request $request): JsonResponse
    {
        $credenciales = $request->validate([
            'email'    => 'required|email|max:150',
            'password' => 'required|string|max:200',
            'orientador' => 'nullable|string|max:150',   // nombre elegido en la lista (solo rol Orientador)
        ]);

        $nombreOrientador = $credenciales['orientador'] ?? null;
        unset($credenciales['orientador']);

        $credenciales['email'] = Str::lower(trim($credenciales['email']));
        $clave = $credenciales['email'] . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($clave, 5)) {
            $segundos = RateLimiter::availableIn($clave);

            return response()->json([
                'ok'      => false,
                'mensaje' => "Demasiados intentos. Intenta de nuevo en {$segundos} segundos.",
            ], 429);
        }

        [$usuario, $borrada] = $this->usuarioValido($credenciales['email'], $credenciales['password'], $nombreOrientador);

        if ($borrada) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Tu contraseña fue borrada. Pide a Control Escolar que te genere una nueva.',
            ], 422);
        }

        if (!$usuario) {
            RateLimiter::hit($clave, 60);

            return response()->json([
                'ok'      => false,
                'mensaje' => 'Correo o contraseña incorrectos.',
            ], 422);
        }

        RateLimiter::clear($clave);
        Auth::login($usuario, false);   // false = sin "recordarme": la sesión no queda abierta para siempre
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

    /**
     * Devuelve [cuenta o null, contraseña borrada?].
     * Rol Orientador: se pide el nombre y se compara con la clave PROPIA de ese orientador;
     * si Control Escolar no le puso una, vale la contraseña general del rol.
     * "Borrada" = Control Escolar la eliminó y todavía no genera una nueva (columna vacía).
     */
    private function usuarioValido(string $email, string $password, ?string $nombreOrientador): array
    {
        $usuario = User::where('email', $email)->first();

        if (!$usuario) {
            Hash::make($password);   // gasta el mismo tiempo para no revelar qué correos existen

            return [null, false];
        }

        $hash = (string) $usuario->getAuthPassword();

        if ($usuario->rol === 'orientador') {
            $orientador = $nombreOrientador ? Orientador::where('nombre', $nombreOrientador)->first() : null;

            if (!$orientador) {
                return [null, false];
            }

            if ($orientador->password !== null) {
                $hash = (string) $orientador->password;   // propia (o '' si se la borraron)
            }
        }

        if ($hash === '') {
            return [null, true];
        }

        return [Hash::check($password, $hash) ? $usuario : null, false];
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