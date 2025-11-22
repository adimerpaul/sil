<?php

// app/Models/Consentimiento.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Consentimiento extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'paciente_id',
        'fecha_recepcion',
        'hora_recepcion',
        'fecha_solicitud',

        'nombre_completo',
        'fecha_nac',
        'genero',
        'edad',
        'ci',
        'telefono',
        'direccion',

        'discapacidad',
        'discapacidad_cual',

        'embarazo',
        'fum',
        'sem_gest',

        'medicamento',
        'tratamiento',

        'condicion',
        'etapa_gestacion',

        'tipo',
        'declarante_nombre',
        'declarante_condicion',
        'declarante_condicion_otro',
        'fecha_consentimiento',

        'user_id',
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at',
    ];

//    protected $casts = [
//        'fecha_recepcion'      => 'date',
//        'fecha_solicitud'      => 'date',
//        'fecha_nac'            => 'date',
//        'fum'                  => 'date',
//        'fecha_consentimiento' => 'date',
//        'discapacidad'         => 'boolean',
//        'embarazo'             => 'boolean',
//        'medicamento'          => 'boolean',
//    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
