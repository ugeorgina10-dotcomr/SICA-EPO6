<?php

namespace Tests\Feature;

use App\Models\Orientador;
use App\Models\User;
use Database\Seeders\UsuarioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SicaClavesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();   // reinicia los límites de peticiones entre pruebas
        $this->seed(UsuarioSeeder::class);
    }

    private function como(string $rol): static
    {
        return $this->actingAs(User::where('rol', $rol)->firstOrFail());
    }

    public function test_control_cambia_la_clave_del_director_y_se_guarda_con_hash(): void
    {
        $this->como('control')->postJson('/api/sica/claves/rol', [
            'rol' => 'director', 'password' => 'NuevaClave2026', 'password_confirmation' => 'NuevaClave2026',
        ])->assertOk();

        $hash = User::where('rol', 'director')->value('password');
        $this->assertStringStartsWith('$2y$', $hash);
        $this->assertTrue(Hash::check('NuevaClave2026', $hash));

        $this->postJson('/auth/login', ['email' => 'director@epo6.edu.mx', 'password' => 'prepa06'])->assertStatus(422);
        $this->postJson('/auth/login', ['email' => 'director@epo6.edu.mx', 'password' => 'NuevaClave2026'])->assertOk();
    }

    public function test_los_demas_roles_no_pueden_cambiar_claves(): void
    {
        foreach (['director', 'subdirector', 'orientador'] as $rol) {
            $this->como($rol)->postJson('/api/sica/claves/rol', [
                'rol' => 'control', 'password' => 'Hackeada2026', 'password_confirmation' => 'Hackeada2026',
            ])->assertStatus(403);
            $this->como($rol)->getJson('/api/sica/claves')->assertStatus(403);
        }
        $this->assertTrue(Hash::check('prepa06', User::where('rol', 'control')->value('password')));
    }

    public function test_sin_sesion_responde_401(): void
    {
        $this->postJson('/api/sica/claves/rol', ['rol' => 'director', 'password' => 'NuevaClave2026', 'password_confirmation' => 'NuevaClave2026'])
            ->assertStatus(401);
    }

    public function test_valida_longitud_confirmacion_y_rol(): void
    {
        $c = $this->como('control');
        $c->postJson('/api/sica/claves/rol', ['rol' => 'director', 'password' => 'corta', 'password_confirmation' => 'corta'])->assertStatus(422);
        $c->postJson('/api/sica/claves/rol', ['rol' => 'director', 'password' => 'NuevaClave2026', 'password_confirmation' => 'otra'])->assertStatus(422);
        $c->postJson('/api/sica/claves/rol', ['rol' => 'admin', 'password' => 'NuevaClave2026', 'password_confirmation' => 'NuevaClave2026'])->assertStatus(422);
    }

    public function test_control_necesita_su_clave_actual_para_cambiar_la_suya(): void
    {
        $c = $this->como('control');
        $c->postJson('/api/sica/claves/rol', ['rol' => 'control', 'password' => 'NuevaClave2026', 'password_confirmation' => 'NuevaClave2026', 'password_actual' => 'mala'])
            ->assertStatus(422);
        $c->postJson('/api/sica/claves/rol', ['rol' => 'control', 'password' => 'NuevaClave2026', 'password_confirmation' => 'NuevaClave2026', 'password_actual' => 'prepa06'])
            ->assertOk();
    }

    public function test_no_acepta_una_clave_que_ya_usa_otro_rol(): void
    {
        $c = $this->como('control');
        $c->postJson('/api/sica/claves/rol', ['rol' => 'director', 'password' => 'ClaveDirector1', 'password_confirmation' => 'ClaveDirector1'])->assertOk();
        $c->postJson('/api/sica/claves/rol', ['rol' => 'subdirector', 'password' => 'ClaveDirector1', 'password_confirmation' => 'ClaveDirector1'])->assertStatus(422);
        $o = Orientador::create(['nombre' => 'Ana']);
        $c->postJson('/api/sica/claves/orientador', ['orientador_id' => $o->id, 'password' => 'ClaveDirector1', 'password_confirmation' => 'ClaveDirector1'])->assertStatus(422);
    }

    public function test_cada_orientador_entra_con_su_clave_propia(): void
    {
        $ana = Orientador::create(['nombre' => 'Ana']);
        $luis = Orientador::create(['nombre' => 'Luis']);

        $this->como('control')->postJson('/api/sica/claves/orientador', [
            'orientador_id' => $ana->id, 'password' => 'ClaveDeAna2026', 'password_confirmation' => 'ClaveDeAna2026',
        ])->assertOk();
        $this->assertStringStartsWith('$2y$', Orientador::find($ana->id)->password);

        $correo = 'orientador1@epo6.edu.mx';
        // Ana: solo vale la suya
        $this->postJson('/auth/login', ['email' => $correo, 'password' => 'ClaveDeAna2026', 'orientador' => 'Ana'])->assertOk()->assertJsonPath('rol', 'orientador');
        $this->postJson('/auth/login', ['email' => $correo, 'password' => 'prepa06', 'orientador' => 'Ana'])->assertStatus(422);
        // Luis no tiene propia: vale la general del rol
        $this->postJson('/auth/login', ['email' => $correo, 'password' => 'prepa06', 'orientador' => 'Luis'])->assertOk();
        // La clave de Ana no sirve para entrar como Luis
        $this->postJson('/auth/login', ['email' => $correo, 'password' => 'ClaveDeAna2026', 'orientador' => 'Luis'])->assertStatus(422);
    }

    public function test_el_rol_orientador_exige_elegir_un_nombre_valido(): void
    {
        $this->postJson('/auth/login', ['email' => 'orientador1@epo6.edu.mx', 'password' => 'prepa06'])->assertStatus(422);
        $this->postJson('/auth/login', ['email' => 'orientador1@epo6.edu.mx', 'password' => 'prepa06', 'orientador' => 'No existe'])->assertStatus(422);
    }

    public function test_quitar_la_clave_propia_regresa_a_la_general(): void
    {
        $ana = Orientador::create(['nombre' => 'Ana']);
        $c = $this->como('control');
        $c->postJson('/api/sica/claves/orientador', ['orientador_id' => $ana->id, 'password' => 'ClaveDeAna2026', 'password_confirmation' => 'ClaveDeAna2026'])->assertOk();
        $c->deleteJson('/api/sica/claves/orientador/' . $ana->id)->assertOk();
        $this->assertNull(Orientador::find($ana->id)->password);
        $this->postJson('/auth/login', ['email' => 'orientador1@epo6.edu.mx', 'password' => 'prepa06', 'orientador' => 'Ana'])->assertOk();
    }

    public function test_la_clave_nunca_sale_en_las_respuestas(): void
    {
        $ana = Orientador::create(['nombre' => 'Ana']);
        $c = $this->como('control');
        $c->postJson('/api/sica/claves/orientador', ['orientador_id' => $ana->id, 'password' => 'ClaveDeAna2026', 'password_confirmation' => 'ClaveDeAna2026'])->assertOk();

        $lista = $c->getJson('/api/sica/claves')->assertOk()->assertJsonPath('orientadores.0.propia', true)->getContent();
        $this->assertStringNotContainsString('$2y$', $lista);
        $this->assertStringNotContainsString('ClaveDeAna2026', $lista);
        $this->assertStringNotContainsString('$2y$', $c->getJson('/api/sica/estado')->getContent());
        $this->assertArrayNotHasKey('password', Orientador::find($ana->id)->toArray());
    }

    public function test_rechaza_claves_que_no_cumplen_las_indicaciones(): void
    {
        $c = $this->como('control');
        foreach (['todominuscula1' => 'mayúscula', 'TODOMAYUSCULA1' => 'mayúscula', 'SinNumeroAlguno' => 'número',
                  'Con Espacio 12' => 'espacios', 'Segura#Clave12' => 'especiales', 'Contraseña2026X' => 'especiales', 'Clave_Segura12' => 'especiales', 'MiPrepa06Nueva' => 'escuela', 'Epo6Segura123' => 'escuela'] as $clave => $pista) {
            $r = $c->postJson('/api/sica/claves/rol', ['rol' => 'director', 'password' => $clave, 'password_confirmation' => $clave]);
            $r->assertStatus(422);
            $this->assertStringContainsStringIgnoringCase($pista, $r->json('errors.password.0'), $clave);
        }
        $this->assertTrue(Hash::check('prepa06', User::where('rol', 'director')->value('password')));
    }

    public function test_borrar_todas_requiere_confirmacion_y_clave_actual(): void
    {
        $c = $this->como('control');
        $c->deleteJson('/api/sica/claves/todas', ['confirmacion' => 'borrar', 'password_actual' => 'prepa06'])->assertStatus(422);
        $c->deleteJson('/api/sica/claves/todas', ['confirmacion' => 'BORRAR TODO', 'password_actual' => 'mala'])->assertStatus(422);
        $this->assertSame(0, User::where('password', '')->count());
    }

    public function test_solo_control_puede_borrar_todas(): void
    {
        foreach (['director', 'subdirector', 'orientador'] as $rol) {
            $this->como($rol)->deleteJson('/api/sica/claves/todas', ['confirmacion' => 'BORRAR TODO', 'password_actual' => 'prepa06'])->assertStatus(403);
        }
        $this->assertSame(0, User::where('password', '')->count());
    }

    public function test_borrar_todas_no_deja_nada_guardado_y_nadie_entra(): void
    {
        $ana = Orientador::create(['nombre' => 'Ana']);
        $c = $this->como('control');
        $c->postJson('/api/sica/claves/orientador', ['orientador_id' => $ana->id, 'password' => 'ClaveDeAna2026', 'password_confirmation' => 'ClaveDeAna2026'])->assertOk();
        DB::table('users')->where('rol', 'director')->update(['remember_token' => 'abc']);
        DB::table('password_reset_tokens')->insert(['email' => 'director@epo6.edu.mx', 'token' => 'x', 'created_at' => now()]);

        $c->deleteJson('/api/sica/claves/todas', ['confirmacion' => 'BORRAR TODO', 'password_actual' => 'prepa06'])->assertOk();

        $this->assertSame(4, User::count());                                        // las cuentas existen, sin contraseña
        $this->assertSame(0, DB::table('users')->where('password', '!=', '')->count());
        $this->assertSame(0, DB::table('users')->whereNotNull('remember_token')->count());
        $this->assertSame(0, DB::table('orientadores')->whereNotNull('password')->count());
        $this->assertSame(0, DB::table('password_reset_tokens')->count());
        $this->assertSame(0, DB::table('sessions')->count());

        foreach (['control.escolar', 'director', 'subdirector'] as $u) {
            $this->postJson('/auth/login', ['email' => "$u@epo6.edu.mx", 'password' => 'prepa06'])->assertStatus(422);
            $this->postJson('/auth/login', ['email' => "$u@epo6.edu.mx", 'password' => ''])->assertStatus(422);
        }
        $this->postJson('/auth/login', ['email' => 'orientador1@epo6.edu.mx', 'password' => 'prepa06', 'orientador' => 'Ana'])->assertStatus(422);
        $this->postJson('/auth/login', ['email' => 'orientador1@epo6.edu.mx', 'password' => 'ClaveDeAna2026', 'orientador' => 'Ana'])->assertStatus(422);
    }

    public function test_el_despliegue_no_restaura_prepa06_en_cuentas_borradas(): void
    {
        $this->como('control')->deleteJson('/api/sica/claves/todas', ['confirmacion' => 'BORRAR TODO', 'password_actual' => 'prepa06'])->assertOk();

        $this->artisan('sica:crear-usuarios')->assertSuccessful();   // se ejecuta en cada despliegue de Railway

        $this->assertSame(0, DB::table('users')->where('password', '!=', '')->count());
    }

    public function test_restablecer_claves_vuelve_a_dar_acceso_con_hash(): void
    {
        $this->como('control')->deleteJson('/api/sica/claves/todas', ['confirmacion' => 'BORRAR TODO', 'password_actual' => 'prepa06'])->assertOk();

        $this->artisan('sica:restablecer-claves', ['--clave' => 'Restablecida2026'])->assertSuccessful();

        foreach (User::all() as $u) {
            $this->assertStringStartsWith('$2y$', $u->getAuthPassword());
        }
        $this->postJson('/auth/login', ['email' => 'director@epo6.edu.mx', 'password' => 'Restablecida2026'])->assertOk();

        $this->artisan('sica:restablecer-claves', ['--clave' => 'debil'])->assertFailed();
    }

    public function test_borrar_todas_no_toca_ningun_dato_de_los_roles(): void
    {
        $g = \App\Models\Grupo::create(['grado' => 1, 'nombre' => '1° 1', 'turno' => 'Matutino']);
        \App\Models\Alumno::create(['matricula' => 'AAAA000101HMCXXX01', 'nombre' => 'Alumno Uno', 'grupo_id' => $g->id]);
        Orientador::create(['nombre' => 'Ana']);
        \App\Models\Permiso::updateOrCreate(['rol' => 'director'], ['tipo' => 'editar']);
        $antes = [User::count(), DB::table('grupos')->count(), DB::table('alumnos')->count(), DB::table('orientadores')->count(), DB::table('permisos')->count()];
        $correos = User::orderBy('id')->pluck('email', 'rol')->all();

        $this->como('control')->deleteJson('/api/sica/claves/todas', ['confirmacion' => 'BORRAR TODO', 'password_actual' => 'prepa06'])->assertOk();

        $despues = [User::count(), DB::table('grupos')->count(), DB::table('alumnos')->count(), DB::table('orientadores')->count(), DB::table('permisos')->count()];
        $this->assertSame($antes, $despues);
        $this->assertSame($correos, User::orderBy('id')->pluck('email', 'rol')->all());   // mismos roles y correos
        $this->assertSame('editar', DB::table('permisos')->where('rol', 'director')->value('tipo'));
    }
}
