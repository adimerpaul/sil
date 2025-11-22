<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Solicitude extends Model implements AuditableContract
{
    use  SoftDeletes, AuditableTrait;

    protected $fillable = [
        // relaciones
        'paciente_id',
        'doctor_id',

        // cabecera solicitud
        'codigo_solicitud',
        'tipo_atencion',
        'tipo_otro',
        'fecha_solicitud',
        'hora_solicitud',
        'establecimiento_salud',
        'zona_establecimiento',
        'diagnostico_clinico',
        'estado',                  // CREADO, ATENDIENDO, FINALIZADO

        // copia de datos del paciente
        'paciente_nombre',
        'paciente_ci',
        'paciente_telefono',
        'paciente_direccion',
        'paciente_fecha_nac',
        'paciente_genero',
        'paciente_edad',

        // copia de datos del doctor
        'doctor_nombre',
        'doctor_especialidad',
        'doctor_ci',
        'doctor_telefono',
        'doctor_email',
        'doctor_registro',

        // usuario que crea
        'user_id',
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at',
    ];

//    protected $casts = [
//        'fecha_solicitud'   => 'date',
//        'paciente_fecha_nac'=> 'date',
//    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
