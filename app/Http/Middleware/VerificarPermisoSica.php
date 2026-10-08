<?php

namespace App\Http\Middleware;

use App\Models\Permiso;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarPermisoSica
{
    /** Acciones de /api/sica/sync que solo puede hacer Control Escolar. */
    private const SOLO_CONTROL = ['guardar_permisos', 'eliminar_orientador'];

    /** Permiso por defecto de cada rol si Control Escolar aún no lo cambió. */
    private const PERMISO_DEFAULT = [
        'orientador'  => 'editar',
        'director'    => 'ver',
        'subdirector' => 'ver',
    ];

    /**
     * Uso en rutas:
     *   ->middleware('sica:control')  solo Control Escolar
     *   ->middleware('sica:sync')     según la acción y el permiso del rol
     */
    public function handle(Request $request, Closure $next, string $modo = 'control'): Response
    {
        $usuario = $request->user();

        if (!$usuario) {
            return $this->denegar('Inicia sesión para continuar.', 401);
        }

        // Control Escolar puede todo
        if ($usuario->rol === 'control') {
            return $next($request);
        }

        if ($modo === 'control') {
            return $this->denegar('Solo Control Escolar puede hacer esto.');
        }

        if (in_array($request->input('accion'), self::SOLO_CONTROL, true)) {
            return $this->denegar('Solo Control Escolar puede hacer esto.');
        }

        $tipo = Permiso::where('rol', $usuario->rol)->value('tipo')
            ?? (self::PERMISO_DEFAULT[$usuario->rol] ?? 'ver');

        if ($tipo !== 'editar') {
            return $this->denegar('Tu rol solo tiene permiso de visualización.');
        }

        return $next($request);
    }

    private function denegar(string $mensaje, int $codigo = 403): Response
    {
        return response()->json(['ok' => false, 'mensaje' => $mensaje], $codigo);
    }
}