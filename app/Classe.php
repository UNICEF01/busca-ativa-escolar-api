<?php

namespace BuscaAtivaEscolar;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Classe extends Model
{
    use SoftDeletes;

    protected $fillable = ['id', 'name', 'shift', 'qty_enrollment', 'schools_id'];
    protected $guarded = ['id', 'created_at', 'update_at'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $table = "classes";

    /**
     * The tenant this user belongs to.
     * Will be null when users are global users (SUPERUSER and GESTOR_NACIONAL)
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function school()
    {
        return $this->hasOne('BuscaAtivaEscolar\School', 'id', 'schools_id');
    }

}
