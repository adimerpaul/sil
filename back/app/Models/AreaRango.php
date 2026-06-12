<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;
class AreaRango extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;


    protected $fillable = [
        'area_id',
        'analito',
        'metodo',
        'resultado',
        'rango_nombre',
        'rango_minimo',
        'rango_maximo',
        'unidad',
        'interpretacion',
        'muestra',
        'marca',
        'perfil',
        'lista',
        'textarea',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
    public function resultados()
    {
        return $this->hasMany(ResultadoLaboratorio::class);
    }

    public function servicios()
    {
        return $this->belongsToMany(
            Servicio::class,
            'servicio_rangos',
            'area_rango_id',
            'servicio_id'
        )->withPivot('nombre_variable')->withTimestamps();
    }
}
