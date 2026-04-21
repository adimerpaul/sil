<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlmacenItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'subpartida_id',
        'nombre',
        'unidad_medida',
        'precio_unitario',
        'imagen',
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:4',
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at',
    ];

    public function subpartida()
    {
        return $this->belongsTo(Subpartida::class);
    }
}
