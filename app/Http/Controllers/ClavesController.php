<?php

namespace App\Http\Controllers;

use App\Models\Orientador;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * Contraseñas administradas por Control Escolar (todas las rutas pasan por el guardia sica:control).
 *  - clave de cada rol (director, subdirector, orientador general y la propia de Control Escolar)
 *  - clave propia de cada orientador (si no tiene, entra con la general del rol Orientador)
 * Las claves se guardan siempre con hash (bcrypt); nunca se devuelven ni se guardan en texto plano.
 */
class ClavesController extends Controller
{
    private const ROLES = ['control', 'director', 'subdirector', 'orientador'];

    /** Lista de orientadores y si ya tienen clave propia (solo un sí/no, nunca la clave ni su hash). */
    public function index(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'orientadores' => Orientador::orderBy('nombre')->get()
                ->map(fn ($o) => ['id' => (string) $o->id, 'nombre' => $o->nombre, 'propia' => !empty($o->password)])
                ->values(),
        ]);
    }

    /** Cambia la contraseña de la cuenta de un rol. */
    public function rol(Request $request): JsonResponse
    {
        $d = $request->validate([
            'rol'             => ['required', Rule::in(self::ROLES)],
            'password'        => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
            'password_actual' => ['nullable', 'string', 'max:200'],
        ]);

        $usuario = User::where('rol', $d['rol'])->first();

        if (!$usuario) {
            return response()->json(['ok' => false, 'mensaje' => 'No existe la cuenta de ese rol.'], 404);
        }

        // Para cambiar su propia clave, Control Escolar confirma primero la actual
        if ($d['rol'] === 'control' && !Hash::check($d['password_actual'] ?? '', $usuario->getAuthPassword())) {
            return response()->json(['ok' => false, 'mensaje' => 'La contraseña actual no es correcta.'], 422);
        }

        if ($this->laUsaOtraCuenta($d['password'], $usuario->id)) {
            return response()->json(['ok' => false, 'mensaje' => 'Esa contraseña ya la usa otro rol. Elige una distinta.'], 422);
        }

        $usuario->password = $d['password'];   // el modelo la guarda con hash
        $usuario->save();

        // Cierra las sesiones abiertas de esa cuenta (en Control Escolar, menos la actual)
        DB::table('sessions')
            ->where('user_id', $usuario->id)
            ->when($d['rol'] === 'control', fn ($q) => $q->where('id', '!=', $request->session()->getId()))
            ->delete();

        return response()->json(['ok' => true]);
    }

    /** Pone o cambia la clave propia de un orientador. */
    public function orientador(Request $request): JsonResponse
    {
        $d = $request->validate([
            'orientador_id' => ['required', 'integer', 'exists:orientadores,id'],
            'password'      => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ]);

        if ($this->laUsaOtraCuenta($d['password'], null)) {
            return response()->json(['ok' => false, 'mensaje' => 'Esa contraseña ya la usa un rol. Elige una distinta.'], 422);
        }

        $o = Orientador::findOrFail($d['orientador_id']);
        $o->password = $d['password'];
        $o->save();

        return response()->json(['ok' => true]);
    }

    /** Quita la clave propia: el orientador vuelve a entrar con la contraseña general del rol Orientador. */
    public function quitarOrientador(Orientador $orientador): JsonResponse
    {
        $orientador->password = null;
        $orientador->save();

        return response()->json(['ok' => true]);
    }

    /** ¿La contraseña nueva ya es la de otra cuenta de rol? (se compara contra sus hashes) */
    private function laUsaOtraCuenta(string $password, ?int $exceptoUserId): bool
    {
        return User::when($exceptoUserId, fn ($q) => $q->where('id', '!=', $exceptoUserId))
            ->get()
            ->contains(fn ($u) => Hash::check($password, $u->getAuthPassword()));
    }
}