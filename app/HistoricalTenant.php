<?php


namespace BuscaAtivaEscolar;


use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use BuscaAtivaEscolar\Traits\Data\Sortable;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistoricalTenant extends \Illuminate\Database\Eloquent\Model
{

    use IndexedByUUID;
    use SoftDeletes;
    use Sortable;

    protected $table = "historical_tenants";

    protected $fillable = [
        'name',
        'name_ascii',
        'city_id',
        'uf',

        'operational_admin_id',
        'political_admin_id',
        'primary_group_id',

        'is_registered',
        'is_active',
        'is_setup',

        'last_active_at',

        'registered_at',
        'activated_at',

        'map_lat',
        'map_lng',

        'educacenso_import_details',

        'goal'
    ];

    protected $casts = [
        'is_registered' => 'boolean',
        'is_active' => 'boolean',

        'educacenso_import_details' => 'object',

        'last_active_at' => 'datetime',
        'registered_at' => 'datetime',
        'activated_at' => 'datetime'
    ];

}