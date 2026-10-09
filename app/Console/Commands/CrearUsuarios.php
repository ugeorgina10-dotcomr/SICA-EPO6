<?php

namespace App\Console\Commands;

use App\Models\User;
use Database\Seeders\UsuarioSeeder;
use Illuminate\Console\Command;

class CrearUsuarios extends Command
{
    protected $signature = 'sica:crear-usuarios';

    protected $description = 'Crea las cuentas que falten (contraseña inicial con hash) sin tocar las que ya existen y funcionan. Seguro para ejecutar en cada despliegue.';

    public function handle(): int
    {
        foreach (UsuarioSeeder::CUENTAS as $cuenta) {
            $usuario = User::where('email', $cuenta['email'])->first();

            if (!$usuario) {
                User::create($cuenta + ['password' => UsuarioSeeder::CLAVE_INICIAL]);
                $this->info("Creada:     {$cuenta['rol']} | {$cuenta['email']}");
                continue;
            }

            // Cuenta vieja con la contraseña sin hash: se corrige. Si ya es hash, NO se toca (puede haberla cambiado su dueño).
            if (!str_starts_with((string) $usuario->getAuthPassword(), '$2y$')) {
                $usuario->password = UsuarioSeeder::CLAVE_INICIAL;
                $usuario->rol = $cuenta['rol'];
                $usuario->save();
                $this->warn("Corregida:  {$cuenta['rol']} | {$cuenta['email']} (la contraseña no estaba con hash)");
                continue;
            }

            $this->line("Sin cambios: {$cuenta['rol']} | {$cuenta['email']}");
        }

        return self::SUCCESS;
    }
}
