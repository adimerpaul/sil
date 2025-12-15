<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class CultivoAntibiograma extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'solicitude_id',
        'numero_identificacion',
        'codigo_microbiologia',
        'institucion',
        'cultivo_solicitado',
        'localizacion',
        'servicio',
        'sala',
        'cama',
        'fecha_ingreso',
        'fecha_salida',
        'tincion_gram',
        'conteo_colonia',
        'microorganismo',
        'mecanismo_resistencia',
        'observaciones',
        'antibiograma',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'fecha_salida'  => 'date',
        'antibiograma'  => 'array',
    ];

    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
}
