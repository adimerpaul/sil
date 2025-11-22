<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;
class ServicioSolicitude extends Model implements AuditableContract
{
    use AuditableTrait;

    protected $fillable = [
        'solicitud_id',
        'servicio_id',
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at',
    ];
    function servicio(){
        return $this->belongsTo(Servicio::class);
    }
    function solicitud(){
        return $this->belongsTo(Solicitude::class);
    }
}
