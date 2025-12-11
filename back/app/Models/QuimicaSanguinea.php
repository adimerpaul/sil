<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;
class QuimicaSanguinea extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

    protected $table = 'quimica_sanguineas';

    protected $fillable = [
        'solicitude_id',

        'acido_urico',
        'albumina',
        'proteinas_totales',

        'bilirrubina_total',
        'bilirrubina_directa',
        'bilirrubina_indirecta',

        'got',
        'gpt',
        'fosfatasa_alcalina',
        'ggt',
        'amilasa',

        'glucosa',
        'urea',
        'nus',
        'creatinina',

        'trigliceridos',
        'colesterol_total',
        'hdl_colesterol',
        'ldl_colesterol',
        'vldl_colesterol',

        'ck_total',
        'ck_mb',

        'ferritina',
        'hierro_serico',
        'got_cinetico',
        'gpt_cinetico',
        'hb_glicosilada',
        'hb_a1c',

        'sodio',
        'potasio',
        'cloro',
        'calcio',
        'fosforo',
        'magnesio',
        'ldh',

        'creatinuria_24h',
        'proteinuria_24h',
        'volumen_24h',

        'aso',
        'fr',
        'pcr',

        'prueba_rapida_vih',
        'rpr',
        'reaccion_widal',
        'dce',

        'observaciones',
        'metodo',
        'equipo',
    ];

    protected $hidden = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public function solicitude()
    {
        return $this->belongsTo(Solicitude::class, 'solicitude_id');
    }
}
