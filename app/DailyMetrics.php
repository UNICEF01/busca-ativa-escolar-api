<?php


namespace BuscaAtivaEscolar;

use Illuminate\Database\Eloquent\Model;

class DailyMetrics extends Model
{
    protected $table = "daily_metrics";

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'child_id',
        'child_status',
        'alert_status',
        'deadline_status',
        'date',
        'case_status',
        'step_slug',
        'city_id',
        'uf',
        'cancel_reason',
        'reinsertion_grade'
    ];

    protected $casts = [
        'date' => 'datetime:Y-m-d',
    ];
}