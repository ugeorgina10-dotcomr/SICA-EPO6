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

        return match ($validado['accion']) {
            'guardar_permisos' => $this->guardarPermisos($validado['datos']),
            'guardar_todo'     => $this->guardarTodo($validado['datos']),
            default            => response()->json(['ok' => false, 'mensaje' => 'Acción no reconocida'], 400),
        };
    }

    /**
     * Reemplaza la plantilla oficial (frente o reverso) en public/img.
     * El JS ya envía el archivo convertido a PNG en el campo "archivo".
     */
    public function plantillaOficial(Request $request, string $lado): JsonResponse
    {
        abort_unless(in_array($lado, ['frente', 'reverso'], true), 404);

        $request->validate([
            'archivo' => 'required|file|mimes:png|max:5120',
        ]);

        $carpeta = public_path('img');
        $nombre  = 'plantilla' . $lado . '.png';   // plantillafrente.png / plantillareverso.png
        $destino = $carpeta . DIRECTORY_SEPARATOR . $nombre;

        // Respaldo del diseño vigente, para poder volver atrás
        if (file_exists($destino)) {
            copy($destino, $carpeta . DIRECTORY_SEPARATOR . 'respaldo_' . date('Ymd_His') . '_' . $nombre);
        }

        $request->file('archivo')->move($carpeta, $nombre);

        return response()->json(['ok' => true]);
    }

    private function guardarPermisos(array $permisos): JsonResponse
    {
        foreach ($permisos as $rol => $tipo) {
            Permiso::updateOrCreate(['rol' => $rol], ['tipo' => $tipo]);
        }

        return response()->json(['ok' => true]);
    }

    private function guardarTodo(array $db): JsonResponse
    {
        DB::transaction(function () use ($db) {
            $mapaOrientadores = $this->guardarOrientadores($db['orientadores'] ?? []);
            $this->guardarHorarios($db['horarios'] ?? [], $mapaOrientadores);
            $this->guardarPlantillas($db['plantillas'] ?? []);
            $this->guardarAlumnos($db['alumnos'] ?? []);
        });

        return response()->json(['ok' => true]);
    }

    /** claveGrupo "grado-grupo" (ej. "1-2") -> [grado, grupo, "1° 2"] */
    private function datosDeClave(string $clave): array
    {
        [$grado, $grupo] = explode('-', $clave);

        return [(int) $grado, (int) $grupo, $grado . '° ' . $grupo];
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

    /** Horario límite de entrada/salida y orientador asignado por grupo */
    private function guardarHorarios(array $horarios, array $mapaOrientadores): void
    {
        foreach ($horarios as $clave => $h) {
            [$grado, , $nombre] = $this->datosDeClave($clave);

            $grupo = Grupo::firstOrNew(['grado' => $grado, 'nombre' => $nombre]);
            $grupo->turno = $grupo->turno ?: 'Matutino';
            $grupo->hora_entrada = $h['entrada'] ?? null;
            $grupo->hora_salida = $h['salida'] ?? null;
            $grupo->orientador_id = (!empty($h['orientadorId']) && isset($mapaOrientadores[$h['orientadorId']]))
                ? $mapaOrientadores[$h['orientadorId']]
                : null;
            $grupo->save();
        }
    }

    /** Plantillas (frente/reverso) propias de cada grupo */
    private function guardarPlantillas(array $plantillas): void
    {
        foreach ($plantillas as $clave => $p) {
            [$grado, , $nombre] = $this->datosDeClave($clave);

            $grupo = Grupo::firstOrNew(['grado' => $grado, 'nombre' => $nombre]);
            $grupo->turno = $grupo->turno ?: 'Matutino';

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
            $nombreGrupo = $a['grado'] . '° ' . $a['grupo'];

            $grupo = Grupo::firstOrCreate(
                ['grado' => $a['grado'], 'nombre' => $nombreGrupo],
                ['turno' => $a['turno'] ?? 'Matutino']
            );

            if (!empty($a['turno']) && $grupo->turno !== $a['turno']) {
                $grupo->turno = $a['turno'];
                $grupo->save();
            }

            // Tu tabla exige matrícula única y obligatoria: sin CURP el alumno
            // todavía no se guarda en la BD (sigue viendo bien en el navegador).
            if (empty($a['curp'])) {
                continue;
            }

            $alumno = Alumno::updateOrCreate(
                ['matricula' => $a['curp']],
                ['nombre' => $a['nombre'], 'grupo_id' => $grupo->id]
            );

            foreach (($a['reg'] ?? []) as $fecha => $r) {
                if (!empty($r['entrada'])) {
                    $estatus = ($grupo->hora_entrada && $r['entrada'] > substr($grupo->hora_entrada, 0, 5))
                        ? 'Retardo'
                        : 'Asistencia';

                    Asistencia::updateOrCreate(
                        [
                            'alumno_id'  => $alumno->id,
                            'fecha_hora' => $fecha . ' ' . $r['entrada'] . ':00',
                        ],
                        ['estatus' => $estatus]
                    );
                }

                if (!empty($r['salida'])) {
                    Asistencia::updateOrCreate(
                        [
                            'alumno_id'  => $alumno->id,
                            'fecha_hora' => $fecha . ' ' . $r['salida'] . ':00',
                        ],
                        ['estatus' => 'Salida']
                    );
                }
            }
        }
    }
}