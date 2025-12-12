<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;
class Uroanalisis extends Model implements AuditableContract{
    use SoftDeletes, AuditableTrait;

    // Usamos nombre de tabla fijo para evitar plural raro
    protected $table = 'uroanalisis';

    protected $fillable = [
        'solicitude_id',
        'material_ensayo',
        'metodo',
        'cantidad',
        'color',
        'olor',
        'aspecto',
        'reaccion',
        'densidad',
        'espuma',
        'sedimento',
        'celulas_epiteliales',
        'leucocitos',
        'hematies',
        'bacterias',
        'filamento_mucoide',
        'cilindros',
        'celulas',
        'cristales',
        'morfologia_eritrocitaria',
        'proteinas',
        'glucosa',
        'sangre',
        'cetonas',
        'bilirrubina',
        'urobilinogeno',
        'nitritos',
        'observaciones',
        'valor_morfologia',
        'valor_cilindros',
        'valor_celulas',
        'valor_cristales',
        'otros',
    ];

    public function solicitude()
    {
        return $this->belongsTo(Solicitude::class);
    }
}
