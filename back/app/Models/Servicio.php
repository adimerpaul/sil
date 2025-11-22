<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Servicio extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'area_id',
        'codigo',
        'nombre',
        'metodo',
        'precio',
        'estado',
        'subarea',
        'descripcion'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
    public function establecimientos()
    {
        return $this->belongsToMany(
            Establecimiento::class,
            'establecimiento_servicios',
            'servicio_id',
            'establecimiento_id'
        )->withTimestamps();
    }
    public function solicitudes()
    {
        return $this->belongsToMany(
            \App\Models\Solicitude::class,
            'servicio_solicitudes',
            'servicio_id',
            'solicitude_id'
        )->withPivot('precio')->withTimestamps();
    }
}
