<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Area extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'name',
        'descripcion',
        'estado',
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at',
    ];

    public function servicios()
    {
        return $this->hasMany(Servicio::class);
    }
}
