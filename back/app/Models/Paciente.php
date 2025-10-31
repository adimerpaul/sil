<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paciente extends Model {
    use SoftDeletes;

    protected $fillable = [
        'fecha_recepcion', 'hora_recepcion',
        'nombre_completo', 'fecha_nac', 'genero', 'edad',
        'ci', 'telefono', 'direccion',
        'discapacidad', 'discapacidad_cual',
        'embarazo', 'fum', 'sem_gest'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];
}
