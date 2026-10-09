<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\Grupo;
use App\Models\Orientador;
use App\Models\Permiso;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ControlEscolarController extends Controller
{
    /**
     * Único punto de entrada que ya usa el frontend (sincronizarConServidor() en el JS).
     * Recibe { accion, datos } y decide qué hacer según la acción.
     */
    public function sync(Request $request): JsonResponse
    {
        $validado = $request->validate([
            'accion' => 'required|string',
            'datos'  => 'required|array',
        ]);

        $esControl = $request->user()?->rol === 'control';

        return match ($validado['accion']) {
            'guardar_permisos'     => $this->guardarPermisos($validado['datos']),
            // Solo Control Escolar puede crear/cambiar alumnos, horarios, orientadores y plantillas.
            // Los demás roles con permiso "editar" únicamente pueden guardar asistencias.
            'guardar_todo'         => $this->guardarTodo($validado['datos'], !$esControl),
            'registrar_asistencia' => $this->registrarAsistencia($validado['datos']),
            'eliminar_alumno'      => $this->eliminarAlumno($validado['datos']),
            'eliminar_orientador'  => $this->eliminarOrientador($validado['datos']),
            default                => response()->json(['ok' => false, 'mensaje' => 'Acción no reconocida'], 400),
        };
    }

    /**
     * Reemplaza la plantilla oficial (frente o reverso) de un turno en public/img.
     * El JS envía el archivo convertido a PNG en "archivo" y el turno en "turno".
     * Guarda: plantillamatutinofrente.png, plantillamatutinoreverso.png,
     *         plantillavespertinofrente.png, plantillavespertinoreverso.png
     */
    public function plantillaOficial(Request $request, string $lado): JsonResponse
    {
        abort_unless(in_array($lado, ['frente', 'reverso'], true), 404);

        $request->validate([
            'archivo' => 'required|file|mimes:png|max:5120',
            'turno'   => 'required|in:Matutino,Vespertino',
        ]);

        $carpeta = public_path('img');
        $nombre  = 'plantilla' . strtolower($request->input('turno')) . $lado . '.png';
        $destino = $carpeta . DIRECTORY_SEPARATOR . $nombre;

        // Respaldo del diseño vigente, para poder volver atrás
        if (file_exists($destino)) {
            copy($destino, $carpeta . DIRECTORY_SEPARATOR . 'respaldo_' . date('Ymd_His') . '_' . $nombre);
        }

        $request->file('archivo')->move($carpeta, $nombre);

        return response()->json(['ok' => true]);
    }

    /**
     * Lo que manda el teléfono en cada escaneo.
     * La fecha y la hora las pone el SERVIDOR (zona America/Mexico_City): así nadie puede falsear
     * una entrada cambiando la hora de su teléfono o editando la petición desde F12.
     */
    private function registrarAsistencia(array $d): JsonResponse
    {
        $v = validator($d, [
            'curp' => 'required|string|max:30',
            'modo' => 'nullable|in:Entrada,Salida',
        ]);

        if ($v->fails()) {
            return response()->json(['ok' => false, 'mensaje' => 'Datos de escaneo inválidos'], 422);
        }

        $alumno = Alumno::where('matricula', $d['curp'])->first();
        if (!$alumno) {
            return response()->json(['ok' => false, 'mensaje' => 'Alumno no encontrado'], 404);
        }

        $ahora     = now();
        $hora      = $ahora->format('H:i');
        $fechaHora = $ahora->format('Y-m-d H:i:00');

        if (($d['modo'] ?? 'Entrada') === 'Salida') {
            $estatus = 'Salida';
        } else {
            $grupo   = Grupo::find($alumno->grupo_id);
            $estatus = ($grupo && $grupo->hora_entrada && $hora > substr($grupo->hora_entrada, 0, 5))
                ? 'Retardo' : 'Asistencia';
        }

        Asistencia::updateOrCreate(
            ['alumno_id' => $alumno->id, 'fecha_hora' => $fechaHora],
            ['estatus' => $estatus]
        );

        return response()->json(['ok' => true, 'fecha' => $ahora->format('Y-m-d'), 'hora' => $hora]);
    }

    /** Estado completo para que computadoras y teléfonos lo descarguen */
    public function estado(Request $request): JsonResponse
    {
        $grupos = Grupo::select('id', 'nombre', 'grado', 'turno', 'hora_entrada', 'hora_salida', 'orientador_id')->get()->keyBy('id');
        $asist  = Asistencia::select('alumno_id', 'estatus', 'fecha_hora')->get()->groupBy('alumno_id');

        // Clave de horario: "Turno-grado-grupo" (ej. "Vespertino-1-2")
        $horarios = [];
        foreach ($grupos as $g) {
            if (!$g->hora_entrada && !$g->hora_salida && !$g->orientador_id) {
                continue;
            }
            $partes = explode(' ', $g->nombre);
            $turno  = $g->turno ?: 'Matutino';
            $horarios[$turno . '-' . $g->grado . '-' . end($partes)] = [
                'entrada'      => $g->hora_entrada ? substr($g->hora_entrada, 0, 5) : '',
                'salida'       => $g->hora_salida ? substr($g->hora_salida, 0, 5) : '',
                'orientadorId' => $g->orientador_id ? (string) $g->orientador_id : '',
            ];
        }

        $alumnos = [];
        foreach (Alumno::select('id', 'matricula', 'nombre', 'grupo_id')->get() as $a) {
            $g = $grupos[$a->grupo_id] ?? null;
            if (!$g) {
                continue;
            }

            $reg = [];
            foreach ($asist->get($a->id, collect()) as $x) {
                $f = substr((string) $x->fecha_hora, 0, 10);
                $h = substr((string) $x->fecha_hora, 11, 5);
                if ($x->estatus === 'Salida') {
                    if (empty($reg[$f]['salida']) || $h > $reg[$f]['salida']) {
                        $reg[$f]['salida'] = $h;
                    }
                } else {
                    if (empty($reg[$f]['entrada']) || $h < $reg[$f]['entrada']) {
                        $reg[$f]['entrada'] = $h;
                    }
                }
            }

            $partes = explode(' ', $g->nombre);
            $alumnos[] = [
                'id'     => (string) $a->id,
                'curp'   => $a->matricula,
                'nombre' => $a->nombre,
                'grado'  => (int) $g->grado,
                'grupo'  => (int) end($partes),
                'turno'  => $g->turno ?: 'Matutino',
                'reg'    => (object) $reg,
            ];
        }

        $permisos = ['orientador' => 'editar', 'director' => 'ver', 'subdirector' => 'ver'];
        foreach (Permiso::all() as $p) {
            $permisos[$p->rol] = $p->tipo;
        }

        $datos = [
            'alumnos'      => $alumnos,
            'horarios'     => (object) $horarios,
            'orientadores' => Orientador::all()->map(fn ($o) => ['id' => (string) $o->id, 'nombre' => $o->nombre])->values(),
            'permisos'     => $permisos,
        ];

        // ETag: si nada cambió desde la última descarga, responde 304 (sin cuerpo) y la pantalla se actualiza más rápido
        $respuesta = response()->json($datos);
        $respuesta->setEtag(md5($respuesta->getContent()));
        $respuesta->isNotModified($request);

        return $respuesta;
    }

    /** Elimina un alumno (por CURP) junto con todos sus registros de asistencia */
    private function eliminarAlumno(array $d): JsonResponse
    {
        $alumno = Alumno::where('matricula', $d['curp'] ?? '')->first();

        if ($alumno) {
            DB::transaction(function () use ($alumno) {
                Asistencia::where('alumno_id', $alumno->id)->delete();
                $alumno->delete();
            });
        }

        // Si no existía en el servidor, también es correcto: el navegador lo quita de su lista
        return response()->json(['ok' => true]);
    }

    /** Elimina un orientador (por nombre) y deja sin orientador los grupos que tenía asignados */
    private function eliminarOrientador(array $d): JsonResponse
    {
        $orientador = Orientador::where('nombre', $d['nombre'] ?? '')->first();

        if ($orientador) {
            DB::transaction(function () use ($orientador) {
                Grupo::where('orientador_id', $orientador->id)->update(['orientador_id' => null]);
                $orientador->delete();
            });
        }

        return response()->json(['ok' => true]);
    }

    private function guardarPermisos(array $permisos): JsonResponse
    {
        foreach ($permisos as $rol => $tipo) {
            // Solo estos roles y estos tipos son válidos; Control Escolar siempre puede todo
            if (!in_array($rol, ['orientador', 'director', 'subdirector'], true) || !in_array($tipo, ['ver', 'editar'], true)) {
                return response()->json(['ok' => false, 'mensaje' => 'Permiso inválido'], 422);
            }
        }

        foreach ($permisos as $rol => $tipo) {
            Permiso::updateOrCreate(['rol' => $rol], ['tipo' => $tipo]);
        }

        return response()->json(['ok' => true]);
    }

    private function guardarTodo(array $db, bool $soloAsistencias = false): JsonResponse
    {
        DB::transaction(function () use ($db, $soloAsistencias) {
            if ($soloAsistencias) {
                $this->guardarSoloAsistencias($db['alumnos'] ?? []);
                return;
            }

            $mapaOrientadores = $this->guardarOrientadores($db['orientadores'] ?? []);
            $this->guardarHorarios($db['horarios'] ?? [], $mapaOrientadores);
            $this->guardarPlantillas($db['plantillas'] ?? []);
            $this->guardarAlumnos($db['alumnos'] ?? []);
        });

        return response()->json(['ok' => true]);
    }

    /**
     * Clave de grupo -> [grado, grupo, "1° 2", turno]
     * Acepta "Vespertino-1-2" (nueva) y "1-2" (formato anterior, se toma como Matutino).
     */
    private function datosDeClave(string $clave): array
    {
        $p = explode('-', $clave);

        if (count($p) === 2) {
            $turno = 'Matutino';
            [$grado, $grupo] = $p;
        } else {
            [$turno, $grado, $grupo] = $p;
        }

        $turno = ($turno === 'Vespertino') ? 'Vespertino' : 'Matutino';

        return [(int) $grado, (int) $grupo, $grado . '° ' . $grupo, $turno];
    }

    /** Guarda la lista de orientadores y devuelve un mapa id-del-JS -> id-real-de-la-BD */
    private function guardarOrientadores(array $orientadores): array
    {
        $mapa = [];

        foreach ($orientadores as $o) {
            if (empty($o['nombre'])) {
                continue;
            }

            $registro = Orientador::updateOrCreate(['nombre' => $o['nombre']], []);
            $mapa[$o['id']] = $registro->id;
        }

        return $mapa;
    }

    /** Horario límite de entrada/salida y orientador asignado por grupo Y turno */
    private function guardarHorarios(array $horarios, array $mapaOrientadores): void
    {
        foreach ($horarios as $clave => $h) {
            [$grado, , $nombre, $turno] = $this->datosDeClave($clave);

            $grupo = Grupo::firstOrNew(['grado' => $grado, 'nombre' => $nombre, 'turno' => $turno]);
            $grupo->hora_entrada = $h['entrada'] ?? null;
            $grupo->hora_salida = $h['salida'] ?? null;
            $grupo->orientador_id = (!empty($h['orientadorId']) && isset($mapaOrientadores[$h['orientadorId']]))
                ? $mapaOrientadores[$h['orientadorId']]
                : null;
            $grupo->save();
        }
    }

    /** Plantillas (frente/reverso) propias de cada grupo (opcional, ya casi no se usa) */
    private function guardarPlantillas(array $plantillas): void
    {
        foreach ($plantillas as $clave => $p) {
            [$grado, , $nombre, $turno] = $this->datosDeClave($clave);

            $grupo = Grupo::firstOrNew(['grado' => $grado, 'nombre' => $nombre, 'turno' => $turno]);

            if (!empty($p['frente']['img'])) {
                $grupo->plantilla_frente = $p['frente']['img'];
            }
            if (!empty($p['reverso']['img'])) {
                $grupo->plantilla_reverso = $p['reverso']['img'];
            }

            $grupo->save();
        }
    }

    /** Alumnos y sus registros de entrada/salida por día */
    private function guardarAlumnos(array $alumnos): void
    {
        foreach ($alumnos as $a) {
            if (!is_array($a)) {
                continue;
            }

            $nombreGrupo = ((int) ($a['grado'] ?? 0)) . '° ' . ((int) ($a['grupo'] ?? 0));
            $turno       = (($a['turno'] ?? '') === 'Vespertino') ? 'Vespertino' : 'Matutino';

            // El grupo se identifica por grado + nombre + TURNO, así 1° 1 matutino y 1° 1 vespertino son distintos
            $grupo = Grupo::firstOrCreate(
                ['grado' => (int) ($a['grado'] ?? 0), 'nombre' => $nombreGrupo, 'turno' => $turno]
            );

            // Tu tabla exige matrícula única y obligatoria: sin CURP el alumno
            // todavía no se guarda en la BD (sigue viendo bien en el navegador).
            if (empty($a['curp']) || !is_string($a['curp'])) {
                continue;
            }

            $alumno = Alumno::updateOrCreate(
                ['matricula' => $a['curp']],
                ['nombre' => mb_substr((string) ($a['nombre'] ?? ''), 0, 150), 'grupo_id' => $grupo->id]
            );

            $this->guardarRegistros($alumno, $grupo, $a['reg'] ?? []);
        }
    }

    /**
     * Modo para roles que NO son Control Escolar: solo se guardan entradas/salidas de alumnos que ya existen.
     * No se crean alumnos, grupos, horarios ni orientadores, aunque la petición los traiga.
     */
    private function guardarSoloAsistencias(array $alumnos): void
    {
        foreach ($alumnos as $a) {
            if (!is_array($a) || empty($a['curp']) || !is_string($a['curp'])) {
                continue;
            }

            $alumno = Alumno::where('matricula', $a['curp'])->first();
            if (!$alumno) {
                continue;
            }

            $this->guardarRegistros($alumno, Grupo::find($alumno->grupo_id), $a['reg'] ?? []);
        }
    }

    /** Guarda entradas y salidas por fecha, ignorando fechas u horas con formato inválido */
    private function guardarRegistros(Alumno $alumno, ?Grupo $grupo, mixed $reg): void
    {
        if (!is_array($reg)) {
            return;
        }

        $esFecha = fn ($x) => is_string($x) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $x) === 1;
        $esHora  = fn ($x) => is_string($x) && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $x) === 1;

        foreach ($reg as $fecha => $r) {
            if (!$esFecha($fecha) || !is_array($r)) {
                continue;
            }

            if (!empty($r['entrada']) && $esHora($r['entrada'])) {
                $estatus = ($grupo && $grupo->hora_entrada && $r['entrada'] > substr($grupo->hora_entrada, 0, 5))
                    ? 'Retardo'
                    : 'Asistencia';

                Asistencia::updateOrCreate(
                    ['alumno_id' => $alumno->id, 'fecha_hora' => $fecha . ' ' . $r['entrada'] . ':00'],
                    ['estatus' => $estatus]
                );
            }

            if (!empty($r['salida']) && $esHora($r['salida'])) {
                Asistencia::updateOrCreate(
                    ['alumno_id' => $alumno->id, 'fecha_hora' => $fecha . ' ' . $r['salida'] . ':00'],
                    ['estatus' => 'Salida']
                );
            }
        }
    }
}
