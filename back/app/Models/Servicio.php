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
}
