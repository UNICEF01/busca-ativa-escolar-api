<?php


namespace BuscaAtivaEscolar;


use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use BuscaAtivaEscolar\Traits\Data\Sortable;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistoricalTenantSignup extends \Illuminate\Database\Eloquent\Model
{

    use IndexedByUUID;
    use SoftDeletes;
    use Sortable;

    protected $table = "historical_signups";
    protected $fillable = [
        'city_id',
        'tenant_id',
        'is_approved',
        'is_provisioned',
        'ip_addr',
        'user_agent',
        'data',

        // Sort-only fields
        'cities.name',
        'cities.uf',
        'created_at',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_provisioned' => 'boolean',
        'data' => 'array',
        'is_approved_by_mayor' => 'boolean'
    ];
}