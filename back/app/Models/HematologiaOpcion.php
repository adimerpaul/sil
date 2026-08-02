<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HematologiaOpcion extends Model
{
    public const SECCIONES = ['HEMOGRAMA', 'COAGULOGRAMA'];

    public const TIPOS = ['METODO', 'EQUIPO'];

    protected $table = 'hematologia_opciones';

    protected $fillable = [
        'seccion',
        'tipo',
        'nombre',
        'orden',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];
}
