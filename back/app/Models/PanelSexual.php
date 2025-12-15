<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;

class PanelSexual extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

    protected $table = 'panel_sexuales';

    protected $fillable = [
        'solicitude_id',
        'chlamydia_trachomatis',
        'mycoplasma_genitalium',
        'neisseria_gonorrhoeae',
        'trichomonas_vaginalis',
        'ureaplasma_urealyticum',
        'ureaplasma_parvum',
        'mycoplasma_hominis',
        'hsv_1',
        'hsv_2',
        'treponema_pallidum',
        'candida_albicans',
        'gardnerella_vaginalis',
        'observaciones',
    ];

    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
}
