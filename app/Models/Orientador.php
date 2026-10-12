<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orientador extends Model
{
    use HasFactory;

    protected $table = 'orientadores';

    protected $fillable = [
        'nombre',
    ];

    // La contraseña nunca sale en JSON ni se llena en masa: solo la cambia ClavesController
    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',   // al asignarla se guarda con bcrypt
        ];
    }

    // Relación: Un orientador puede tener asignados varios grupos
    public function grupos()
    {
        return $this->hasMany(Grupo::class);
    }
}