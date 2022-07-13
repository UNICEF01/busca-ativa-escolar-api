<?php


namespace BuscaAtivaEscolar;

use Illuminate\Database\Eloquent\Model;

class DailyMetricsConsolidated extends Model
{
    protected $table = "daily_metrics_consolidated";

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'date',
        'region',
        'state',
        'city',
        'in_observation',
        'out_of_school',
        'cancelled',
        'in_school',
        'interrupted',
        'transferred',
        'justified_cancelled',
        'selo',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
        'date' => 'datetime:Y-m-d',
    ];

}