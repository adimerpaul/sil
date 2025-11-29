<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerfilImpresion extends Model
{
    protected $table = 'perfiles_impresion';
    protected $fillable = ['nombre', 'codigo'];

    public function items()
    {
        return $this->hasMany(PerfilImpresionItem::class, 'perfil_id');
    }
}
