<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Establecimiento extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'nombre',
        'tipo',
        'nivel',
        'direccion',
        'telefono_contacto',
        'responsable_laboratorio',
        'telefono_responsable',
        'estado',
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at',
    ];

    public function servicios()
    {
        return $this->belongsToMany(
            Servicio::class,
            'establecimiento_servicios',
            'establecimiento_id',
            'servicio_id'
        )->withTimestamps();
    }
}
