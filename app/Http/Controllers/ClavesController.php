<?php

namespace App\Http\Controllers;

use App\Models\Orientador;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public const MENSAJES = [
        'password.required'  => 'Escribe la contraseña nueva.',
        'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
        'password.max'       => 'La contraseña no puede tener más de 72 caracteres.',
        'password.confirmed' => 'Las dos contraseñas no coinciden.',
    ];

    /**
     * Reglas de una contraseña nueva (las mismas indicaciones que muestra la pantalla):
     * 8 a 72 caracteres, mayúscula, minúscula, número, solo letras y números (sin caracteres especiales), sin espacios y sin usar el nombre de la escuela ni la clave inicial.
     */
    public static function reglasClave(): array
    {
        return [
            'required', 'string', 'min:8', 'max:72', 'confirmed',
            function (string $atributo, mixed $valor, \Closure $fallar) {
                $valor = (string) $valor;
                if (preg_match('/\s/u', $valor)) {
                    $fallar('La contraseña no puede llevar espacios.');
                } elseif (!preg_match('/^[A-Za-z0-9]+$/', $valor)) {
                    $fallar('La contraseña solo puede llevar letras (A-Z) y números; no uses caracteres especiales ni acentos.');
                } elseif (!preg_match('/\p{Lu}/u', $valor) || !preg_match('/\p{Ll}/u', $valor)) {
                    $fallar('La contraseña debe llevar al menos una letra mayúscula y una minúscula.');
                } elseif (!preg_match('/\d/', $valor)) {
                    $fallar('La contraseña debe llevar al menos un número.');
                } elseif (preg_match('/prepa0?6|epo0?6/i', $valor)) {
                    $fallar('No uses el nombre de la escuela ni la contraseña inicial (prepa06, epo6).');
                }
            },
        ];
    }

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
            'password'        => self::reglasClave(),
            'password_actual' => ['nullable', 'string', 'max:200'],
        ], self::MENSAJES);

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
            'password'      => self::reglasClave(),
        ], self::MENSAJES);

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

    /**
     * Borra TODAS las contraseñas: las de los roles, las propias de cada orientador, los tokens de "recordarme",
     * los tokens de restablecimiento y las sesiones abiertas. No queda ni siquiera un hash guardado.
     * Después nadie puede iniciar sesión hasta ejecutar en el servidor:  php artisan sica:restablecer-claves
     */
    public function borrarTodas(Request $request): JsonResponse
    {
        $d = $request->validate([
            'confirmacion'    => ['required', 'in:BORRAR TODO'],
            'password_actual' => ['required', 'string', 'max:200'],
        ], ['confirmacion.in' => 'Escribe exactamente BORRAR TODO para confirmar.']);

        if (!Hash::check($d['password_actual'], $request->user()->getAuthPassword())) {
            return response()->json(['ok' => false, 'mensaje' => 'Tu contraseña actual no es correcta.'], 422);
        }

        DB::transaction(function () {
            DB::table('users')->update(['password' => '', 'remember_token' => null]);
            DB::table('orientadores')->update(['password' => null]);
            DB::table('password_reset_tokens')->delete();
            DB::table('sessions')->delete();
        });

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['ok' => true, 'csrf' => csrf_token()]);
    }

    /** ¿La contraseña nueva ya es la de otra cuenta de rol? (se compara contra sus hashes) */
    private function laUsaOtraCuenta(string $password, ?int $exceptoUserId): bool
    {
        return User::when($exceptoUserId, fn ($q) => $q->where('id', '!=', $exceptoUserId))
            ->get()
            ->contains(fn ($u) => Hash::check($password, $u->getAuthPassword()));
    }
}
