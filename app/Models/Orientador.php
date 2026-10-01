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

    // Relación: Un orientador puede tener asignados varios grupos
    public function grupos()
    {
        return $this->hasMany(Grupo::class);
    }
}