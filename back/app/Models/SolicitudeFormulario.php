<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;
class SolicitudeFormulario extends Model implements AuditableContract{

    use SoftDeletes, AuditableTrait;
    protected $fillable = [
        'solicitude_id',
        'formulario_id',
        'nombre',
        'html',
        'area_id',
    ];
    protected $hidden= [
        'created_at', 'updated_at', 'deleted_at',
    ];
    function solicitude(){
        return $this->belongsTo(Solicitude::class);
    }
}
