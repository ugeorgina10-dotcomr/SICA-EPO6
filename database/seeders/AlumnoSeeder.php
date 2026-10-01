<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Grupo;
use App\Models\Alumno;

class AlumnoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear grupos de ejemplo de la EPO 6
        $grupo1 = Grupo::create(['nombre' => '1° 1', 'turno' => 'Matutino']);
        $grupo2 = Grupo::create(['nombre' => '3° 2', 'turno' => 'Matutino']);

        // 2. Crear alumnos de ejemplo asociados a esos grupos
        Alumno::create([
            'matricula' => 'EPO6-101',
            'nombre' => 'Sofía Hernández Pérez',
            'grupo_id' => $grupo1->id
        ]);

        Alumno::create([
            'matricula' => 'EPO6-102',
            'nombre' => 'Carlos Martínez Gómez',
            'grupo_id' => $grupo2->id
        ]);
    }
}