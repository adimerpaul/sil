<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Solicitude extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

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
        'codigo',
        'nro_registro',

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

        // fechas de flujo
        'fecha_pre_analitica',
        'fecha_creacion',
        'fecha_envio_analitica',
        'fecha_finalizacion',

        // usuario que crea / atiende
        'user_id',
        'user_preanalitica_id',
        'user_analitica_id',
        'sala',
        'cama',

        // ---- NUEVOS CAMPOS: calidad de muestra + equipo ----
        'muestra_sangre_entera',
        'muestra_coagulo',
        'muestra_volumen',
        'muestra_identificacion',
        'muestra_equipo',
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at',
    ];

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

    public function userPreanalitica()
    {
        return $this->belongsTo(User::class, 'user_preanalitica_id');
    }

    public function servicios()
    {
        return $this->belongsToMany(
            \App\Models\Servicio::class,
            'servicio_solicitudes',
            'solicitude_id',
            'servicio_id'
        )->withPivot('precio')->withTimestamps();
    }

    public function preAnaliticaMuestras()
    {
        return $this->hasMany(SolitudePreAnalitica::class, 'solicitude_id');
    }

    public function userAnalitica()
    {
        return $this->belongsTo(User::class, 'user_analitica_id');
    }

    public function resultados()
    {
        return $this->hasMany(ResultadoLaboratorio::class);
    }
}
