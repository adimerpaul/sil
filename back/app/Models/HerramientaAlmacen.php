<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HerramientaAlmacen extends Model
{
    protected $table = 'herramientas_almacen';

    protected $fillable = ['nombre', 'valor'];

    public static function obtener(string $nombre): ?string
    {
        return static::where('nombre', $nombre)->value('valor');
    }

    public static function pedidosHabilitados(): bool
    {
        $inicio = static::obtener('fecha_inicio_pedido_almacen');
        $fin    = static::obtener('fecha_fin_pedido_almacen');

        if (!$inicio || !$fin) {
            return false;
        }

        $hoy = today()->format('Y-m-d');
        return $hoy >= $inicio && $hoy <= $fin;
    }
}
