<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;
class Parasitologia extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'solicitude_id',
        'tipo',

        'olor',
        'color',
        'consistencia',
        'bacterias',
        'otros',

        'descripcion_muestra',
        'descripcion_muestra_1',
        'descripcion_muestra_2',
        'descripcion_muestra_3',

        'sangre_oculta',
        'prueba_rapida_rotavirus',
        'moco_fecal',
        'test_benedict',
        'reaccion',
        'otros_examenes',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];
}
