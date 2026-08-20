<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ServicioSolicitude extends Model implements AuditableContract
{
    use AuditableTrait, SoftDeletes;

    protected $fillable = [
        'solicitude_id',
        'servicio_id',
        'area_id',
        'precio',
        'nombre',
        'realizado',
        'realizado_por',
        'fue_recogido',
        'recogido_por_personal',
        'grado_parentesco',
        'telefono_recogido',
        'recogido_en_dia',
        'ci_recogido',
    ];

    protected $casts = [
        'fue_recogido' => 'boolean',
        'recogido_en_dia' => 'datetime:Y-m-d H:i:s',
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at',
    ];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    public function solicitud()
    {
        return $this->belongsTo(Solicitude::class, 'solicitude_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
