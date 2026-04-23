<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompraDetalle extends Model
{
    use SoftDeletes;

    protected $appends = [
        'existencia',
    ];

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

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function producto()
    {
        return $this->belongsTo(AlmacenItem::class, 'producto_id');
    }

    public function getExistenciaAttribute(): int
    {
        $cantidad = (int) ($this->cantidad ?? 0);
        $cantidadVenta = (int) ($this->cantidad_venta ?? 0);

        return max($cantidad - $cantidadVenta, 0);
    }
}
