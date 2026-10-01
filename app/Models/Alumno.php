<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    use HasFactory;

    protected $table = 'alumnos';

    protected $fillable = [
        'matricula',
        'nombre',
        'grupo_id'
    ];

    // Relación: Un alumno pertenece a un grupo
    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    // Relación: Un alumno tiene muchos registros de asistencia
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class);
    }
}