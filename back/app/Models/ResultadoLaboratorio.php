<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class ResultadoLaboratorio extends Model implements Auditable
{
    use AuditableTrait, SoftDeletes;

    protected $fillable = [
        'solicitude_id',
        'area_id',
        'area_rango_id',
        'valor_final',
        'visible',
        'unidad',
        // 'name',
        'metodo_final',
        'valor_automatizado',
        'valor_manual',
        'preferido',
        'observacion',
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitude::class, 'solicitude_id');
    }

    public function rango()
    {
        return $this->belongsTo(AreaRango::class, 'area_rango_id');
    }
}
