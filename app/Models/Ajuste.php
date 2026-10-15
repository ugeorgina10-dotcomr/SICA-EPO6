<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ajuste extends Model
{
    protected $table = 'ajustes';
    protected $primaryKey = 'clave';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['clave', 'valor'];
}
