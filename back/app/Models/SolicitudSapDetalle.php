<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudSapDetalle extends Model
{
    protected $table = 'solicitud_sap_detalles';

    protected $fillable = [
        'solicitud_sap_id',
        'almacen_item_id',
        'imagen',
        'item',
        'part_presup',
        'descripcion',
        'unidad',
        'cantidad',
        'precio_unitario',
        'total',
    ];

    protected $casts = [
        'cantidad'       => 'float',
        'precio_unitario'=> 'float',
        'total'          => 'float',
    ];

    public function solicitudSap()
    {
        return $this->belongsTo(SolicitudSap::class);
    }
}
