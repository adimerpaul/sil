<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable as AuditableTrait;
class PanelRespiratorio extends Model implements AuditableContract
{
    use SoftDeletes, AuditableTrait;

    protected $fillable = [
        'solicitude_id',
        'vrs_ab',
        'influenza_b',
        'influenza_a',
        'sars_cov_2',
        'streptococcus_pyogenes',
        'adenovirus',
        'rhinovirus',
        'coronavirus_229e_oc43',
        'parainfluenza_1_2',
        'coronavirus_nl63_hku1',
        'parainfluenza_3_4',
        'haemophilus_influenzae',
        'bordetella_pertussis',
        'streptococcus_pneumoniae',
        'bocavirus',
        'mycoplasma_pneumoniae',
        'metapneumovirus',
        'enterovirus',
        'legionella_pneumophila',
        'observaciones',
    ];

    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];
}
