<?php

namespace BuscaAtivaEscolar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;

/**
 * @property int $id
 * @property int $attempt
 * @property boolean $blocked
 * @property string $email
 * @property \Carbon\Carbon $attempted_at
 */

class Attempt extends Model
{
    use HasFactory;
    use IndexedByUUID;
    protected $fillable = [
        'id',
        'email',
        'attempt',
        'attempt_history',
        'blocked',
        'attempted_at',
        'attempted_at_history',
    ];
}
