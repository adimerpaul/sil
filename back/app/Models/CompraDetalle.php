<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompraDetalle extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'compra_id',
        'user_id',
        'proveedor_id',
        'producto_id',
        'nombre',
        'precio',
        'cantidad',
        'cantidad_venta',
        'total',
        'factor',
        'precio13',
        'total13',
        'precio_venta',
        'estado',
        'lote',
        'fecha_vencimiento',
        'nro_factura',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'total' => 'decimal:2',
        'factor' => 'decimal:4',
        'precio13' => 'decimal:2',
        'total13' => 'decimal:2',
        'precio_venta' => 'decimal:2',
        'fecha_vencimiento' => 'date',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function producto()
    {
        return $this->belongsTo(AlmacenItem::class, 'producto_id');
    }
}
