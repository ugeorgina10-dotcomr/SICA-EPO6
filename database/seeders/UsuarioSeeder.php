<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsuarioSeeder extends Seeder
{
    /** Contraseña inicial de todas las cuentas. Se guarda SIEMPRE con hash (bcrypt), nunca en texto plano. */
    public const CLAVE_INICIAL = 'prepa06';

    /** Cuentas del sistema (también las usa el comando sica:crear-usuarios en cada despliegue). */
    public const CUENTAS = [
        ['rol' => 'control',     'name' => 'Encargado de Control Escolar', 'email' => 'control.escolar@epo6.edu.mx'],
        ['rol' => 'director',    'name' => 'Director',                     'email' => 'director@epo6.edu.mx'],
        ['rol' => 'subdirector', 'name' => 'Subdirector',                  'email' => 'subdirector@epo6.edu.mx'],
        ['rol' => 'orientador',  'name' => 'Orientadores',                 'email' => 'orientador1@epo6.edu.mx'],
    ];

    public function run(): void
    {
        $usuarios = self::CUENTAS;

        foreach ($usuarios as $u) {
            // El cast 'hashed' del modelo User convierte la contraseña en hash al guardar
            User::updateOrCreate(['email' => $u['email']], $u + ['password' => self::CLAVE_INICIAL]);

            $this->command->info("{$u['rol']}  |  {$u['email']}  |  contraseña guardada con hash");
        }

        $this->command->warn('Comprueba que quedaron bien con:  php artisan sica:verificar-claves');
    }
}
