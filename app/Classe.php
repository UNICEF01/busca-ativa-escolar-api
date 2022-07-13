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
     * The school this classe belongs to.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function school()
    {
        return $this->hasOne('BuscaAtivaEscolar\School', 'id', 'schools_id');
    }

    /**
     * The frequencies this classe has.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function frequencies()
    {
        return $this->hasMany('BuscaAtivaEscolar\Frequency', 'classes_id', 'id');
    }

}
