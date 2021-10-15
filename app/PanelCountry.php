<?php

namespace BuscaAtivaEscolar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @property int $id
 * @property int $num_tenants
 * @property int $num_ufs
 * @property int $num_signups
 * @property int $num_pending_setup
 * @property int $num_alerts
 * @property int $num_pending_alerts
 * @property int $num_rejected_alerts
 * @property int $num_total_alerts
 * @property int $num_cases_in_progress
 * @property int $num_children_reinserted
 * @property int $num_pending_signups
 * @property int $num_pending_state_signups
 * @property int $num_children_in_school
 * @property int $num_children_in_observation
 * @property int $num_children_out_of_school
 * @property int $num_children_cancelled
 * @property int $num_children_transferred
 * @property int $num_children_interrupted
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */

class PanelCountry extends Model
{
    use HasFactory;
    use IndexedByUUID;
    use SoftDeletes;

    protected $table = "panel_country";

    protected $fillable = [
        "id",
        "num_tenants",
        "num_ufs",
        "num_signups",
        "num_pending_setup",
        "num_alerts",
        "num_pending_alerts",
        "num_rejected_alerts",
        "num_total_alerts",
        "num_cases_in_progress",
        "num_children_reinserted",
        "num_pending_signups",
        "num_pending_state_signups",
        "num_children_in_school",
        "num_children_in_observation",
        "num_children_out_of_school",
        "num_children_cancelled",
        "num_children_transferred",
        "num_children_interrupted",
        "deleted_at"
    ];

    protected $hidden = [
        'created_at', 'update_at'
    ];
}
