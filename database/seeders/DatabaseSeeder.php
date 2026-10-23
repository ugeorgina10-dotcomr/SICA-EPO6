<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Solo las cuentas reales del sistema (ya no se crea "test@example.com")
        $this->call(UsuarioSeeder::class);
    }
}
