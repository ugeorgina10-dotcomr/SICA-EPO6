<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupos';

    protected $fillable = [
        'nombre',
        'turno',
        'grado',
        'orientador_id',
        'hora_entrada',
        'hora_salida',
        'plantilla_frente',
        'plantilla_reverso',
    ];

    // Relación: Un grupo tiene muchos alumnos
    public function alumnos()
    {
        return $this->hasMany(Alumno::class);
    }

    // Relación: Un grupo tiene un orientador(a) asignado
    public function orientador()
    {
        return $this->belongsTo(Orientador::class);
    }
}