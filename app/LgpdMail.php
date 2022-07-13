<?php

namespace BuscaAtivaEscolar;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $plataform_id
 * @property string $mail
 * @property \Carbon\Carbon $send_date
 * @property \Carbon\Carbon $delivery_date
 * @property \Carbon\Carbon $open_date
 * @property \Carbon\Carbon $click_date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */

class LgpdMail extends Model
{
    use HasFactory;
    use IndexedByUUID;
    use SoftDeletes;

    protected $table = "lgpd_mail";

    protected $fillable = [
        'id',
        'plataform_id',
        'mail',
        'send_date',
        'delivery_date',
        'open_date',
        'click_date',
        'deleted_at'
    ];

    protected $hidden = [
        'created_at', 'update_at'
    ];
}
