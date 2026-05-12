<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\DespachoDetalle;
use App\Models\DespachoDetalleReal;

class Compra extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'proveedor_id',
        'fecha_hora',
        'tipo_registro',
        'motivo_registro',
        'carnet',
        'nombre',
        'comentario',
        'estado',
        'total',
        'tipo_pago',
        'nro_factura',
        'categoria_programatica',
        'orden_de_compra',
        'codigo_interno',
    ];

    public function detalles()
    {
        return $this->hasMany(CompraDetalle::class);
    }

    public function despachoDetalles()
    {
        return $this->hasManyThrough(DespachoDetalle::class, CompraDetalle::class, 'compra_id', 'compra_detalle_id');
    }

    public function despachoDetalleReales()
    {
        return $this->hasManyThrough(DespachoDetalleReal::class, CompraDetalle::class, 'compra_id', 'compra_detalle_id');
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
