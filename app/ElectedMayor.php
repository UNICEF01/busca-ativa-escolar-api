<?php


namespace BuscaAtivaEscolar;


use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 *
 * @property string $nome
 * @property string $email
 * @property string $cpf
 * @property string $nm_titulo
 * @property string $uf
 *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 */
class ElectedMayor extends Model
{

    protected $table = "elected_mayour";

    protected $fillable = [
        'nome',
        'email',
        'cpf',
        'nm_titulo',
        'uf',
        'municipio'
    ];

}