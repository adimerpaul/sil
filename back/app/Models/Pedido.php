<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use SoftDeletes;

    protected $table = 'pedidos';

    protected $fillable = [
        'user_id',
        'fecha_hora',
        'nombre_usuario',
        'estado',
        'total',
        'modificado',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'total' => 'decimal:2',
        'modificado' => 'boolean',
    ];

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeDeUsuario($query)
    {
        return $query->where('user_id', auth()->id());
    }
}
