<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\User;
use Database\Seeders\UsuarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SicaListasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->seed(UsuarioSeeder::class);
    }

    private function control(): static
    {
        return $this->actingAs(User::where('rol', 'control')->firstOrFail());
    }

    private function lista(array $alumnos)
    {
        return $this->postJson('/api/sica/sync', ['accion' => 'guardar_todo', 'datos' => ['alumnos' => $alumnos, 'horarios' => [], 'orientadores' => []]]);
    }

    public function test_lista_sin_curp_se_guarda_y_no_se_duplica(): void
    {
        $al = [['id' => 'id_abc', 'curp' => '', 'nombre' => 'PEREZ LOPEZ ANA', 'grado' => 1, 'grupo' => 2, 'turno' => 'Matutino', 'reg' => []]];

        $this->control()->lista($al)->assertOk();
        $this->control()->lista($al)->assertOk();

        $this->assertSame(1, Alumno::count());
        $this->assertNull(Alumno::first()->matricula);

        $estado = $this->getJson('/api/sica/estado')->assertOk()->json('alumnos');
        $this->assertSame('id_abc', $estado[0]['id']);
        $this->assertSame('', $estado[0]['curp']);
    }

    public function test_al_asignar_la_curp_se_actualiza_el_mismo_alumno(): void
    {
        $al = ['id' => 'id_abc', 'curp' => '', 'nombre' => 'PEREZ LOPEZ ANA', 'grado' => 1, 'grupo' => 2, 'turno' => 'Matutino', 'reg' => []];
        $this->control()->lista([$al])->assertOk();

        $al['curp'] = 'PELA050101MMCRPN09';
        $this->control()->lista([$al])->assertOk();

        $this->assertSame(1, Alumno::count());
        $this->assertSame('PELA050101MMCRPN09', Alumno::first()->matricula);
    }

    public function test_varios_alumnos_sin_curp_en_la_misma_lista(): void
    {
        $al = [];
        foreach (range(1, 40) as $i) {
            $al[] = ['id' => "id_$i", 'curp' => '', 'nombre' => "ALUMNO $i", 'grado' => 3, 'grupo' => 1, 'turno' => 'Vespertino', 'reg' => []];
        }
        $this->control()->lista($al)->assertOk();
        $this->assertSame(40, Alumno::count());
    }

    public function test_eliminar_alumno_sin_curp(): void
    {
        $this->control()->lista([['id' => 'id_abc', 'curp' => '', 'nombre' => 'X', 'grado' => 1, 'grupo' => 1, 'turno' => 'Matutino', 'reg' => []]])->assertOk();
        $this->postJson('/api/sica/sync', ['accion' => 'eliminar_alumno', 'datos' => ['curp' => '', 'id' => 'id_abc']])->assertOk();
        $this->assertSame(0, Alumno::count());
    }

    public function test_crear_usuarios_corrige_roles_de_cuentas_viejas(): void
    {
        DB::table('users')->update(['rol' => 'orientador']);

        Artisan::call('sica:crear-usuarios');

        $this->assertSame('control', User::where('email', 'control.escolar@epo6.edu.mx')->value('rol'));
        $this->assertSame('director', User::where('email', 'director@epo6.edu.mx')->value('rol'));
        $this->assertSame('subdirector', User::where('email', 'subdirector@epo6.edu.mx')->value('rol'));
    }
}
