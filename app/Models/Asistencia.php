<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';

    protected $fillable = [
        'alumno_id',
        'estatus',
        'fecha_hora'
    ];

    // Relación: Un registro de asistencia le pertenece a un alumno
    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
}
}