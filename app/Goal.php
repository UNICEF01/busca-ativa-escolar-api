<?php

namespace BuscaAtivaEscolar;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Goal extends Model
{
    use SoftDeletes;
    protected $table = "goals";

}
