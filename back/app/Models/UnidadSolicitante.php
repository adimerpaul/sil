<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class UnidadSolicitante extends Model implements AuditableContract
{
    use AuditableTrait;

    protected $fillable = [
        'nombre',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function solicitudes()
    {
        return $this->hasMany(Solicitude::class, 'unidad_solicitante_id');
    }
}
