<?php

namespace BuscaAtivaEscolar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @property int $id
 * @property string $place_uf
 * @property int $value
 * @property string $idMap
 * @property string $displayValue
 * @property int $showLabel
 * @property string $simple_name
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */

class MapCountry extends Model
{
    use HasFactory;
    use IndexedByUUID;
    use SoftDeletes;

    protected $table = "map_country";

    protected $fillable = [
        "id",
        "place_uf",
        "value",
        "idMap",
        "displayValue",
        "showLabel",
        "simple_name",
        "deleted_at"
    ];

    protected $hidden = [
        'created_at', 'update_at'
    ];
}
