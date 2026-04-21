<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'estado',
        'total',
        'tipo_pago',
        'nro_factura',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'total' => 'decimal:2',
    ];

    public function detalles()
    {
        return $this->hasMany(CompraDetalle::class);
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
