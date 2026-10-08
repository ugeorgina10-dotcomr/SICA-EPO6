<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            ['rol' => 'control',     'name' => 'Encargado de Control Escolar', 'email' => 'control.escolar@epo6.edu.mx'],
            ['rol' => 'director',    'name' => 'Director',                     'email' => 'director@epo6.edu.mx'],
            ['rol' => 'subdirector', 'name' => 'Subdirector',                  'email' => 'subdirector@epo6.edu.mx'],
            ['rol' => 'orientador',  'name' => 'Orientadores',                 'email' => 'orientador1@epo6.edu.mx'],
        ];

        foreach ($usuarios as $u) {
            // Contraseña aleatoria: no queda escrita en el código ni en git
            $clave = Str::password(12, symbols: false);

            User::updateOrCreate(['email' => $u['email']], $u + ['password' => $clave]);

            $this->command->info("{$u['rol']}  |  {$u['email']}  |  {$clave}");
        }
    }
}