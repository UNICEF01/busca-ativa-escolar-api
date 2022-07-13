<?php

namespace BuscaAtivaEscolar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $plataform_id
 * @property string $name
 * @property string $ip_addr
 * @property int $term_version
 * @property \Carbon\Carbon $assigned_date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */

class Lgpd extends Model
{
    use HasFactory;
    use IndexedByUUID;
    use SoftDeletes;

    protected $table = "lgpd";

    protected $fillable = [
        'id',
        'plataform_id',
        'name',
        'ip_addr',
        'term_version',
        'assigned_date',
        'deleted_at'
    ];

    protected $hidden = [
        'created_at', 'update_at'
    ];

    public function getPlataformId()
    {
        return $this->plataform_id;
    }

    public function getName()
    {
        return ($this->name) ? $this->name : '';
    }

    public function getIp()
    {
        return ($this->ip_addr) ? $this->ip_addr : '';
    }

    public function getAssignedDate()
    {
        return $this->assigned_date;
    }
}
