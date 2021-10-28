<?php

namespace BuscaAtivaEscolar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * @property int $id
 * @property string $uf
 * @property string $idMap
 * @property int $value
 * @property string $name_city
 * @property int $showLabel
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */

class MapState extends Model
{
    use HasFactory;
    use IndexedByUUID;
    use SoftDeletes;

    protected $table = "map_state";

    protected $fillable = [
        "id",
        "uf",
        "idMap",
        "value",
        "name_city",
        "showLabel",
        "deleted_at"
    ];

    protected $hidden = [
        'created_at', 'update_at'
    ];
}
