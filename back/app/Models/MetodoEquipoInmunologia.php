<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetodoEquipoInmunologia extends Model
{
    use SoftDeletes;

    public const TIPO_METODO = 'METODO';

    public const TIPO_EQUIPO = 'EQUIPO';

    protected $table = 'metodo_equipo_inmunologias';

    protected $fillable = ['tipo', 'nombre'];
}
