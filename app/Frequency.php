<?php

namespace BuscaAtivaEscolar;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Frequency extends Model
{
    use SoftDeletes;

    protected $fillable = ['id', 'qty_presence', 'qty_enrollment', 'classes_id', 'created_at', 'periodicidade'];
    protected $guarded = ['id', 'update_at'];
    protected $hidden = ['updated_at', 'deleted_at'];

    protected $table = "frequency";

    /**
     * The class this frequency belongs to.
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function classe()
    {
        return $this->hasOne('BuscaAtivaEscolar\Classe', 'id', 'classes_id');
    }

}