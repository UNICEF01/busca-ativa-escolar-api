<?php
/**
 * busca-ativa-escolar-api
 * School.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2017
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 30/01/2017, 17:20
 */

namespace BuscaAtivaEscolar;


use BuscaAtivaEscolar\Search\Interfaces\Searchable;
use BuscaAtivaEscolar\Traits\Data\NonIncrementingIndex;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;


/**
 * @property int $id
 *
 * @property string name
 * @property string uf
 * @property int uf_id
 * @property string region
 */
class School extends Model implements Searchable {

	use NonIncrementingIndex;
	use SoftDeletes;
    use Notifiable;

    protected $table = "schools";
	protected $fillable = [
		'id',
		'name',
		'uf',
		'uf_id',
		'region',
		'city_id',
		'city_name',
		'city_ibge_id',
		'metadata',
        'school_cell_phone',
        'school_phone',
        'school_email',
        'token',
        'periodicidade'
	];

    const PERIODICIDADE_DIARIA = "Diaria";
    const PERIODICIDADE_SEMANAL = "Semanal";
    const PERIODICIDADE_QUINZENAL = "Quinzenal";
    const PERIODICIDADE_MENSAL = "Mensal";

	protected $casts = [
		'metadata' => 'object'
	];

	public function city() {
		return $this->hasOne('BuscaAtivaEscolar\City', 'id', 'city_id');
	}

	// ------------------------------------------------------------------------

	public function getSearchIndex(): string { return config('search.index_prefix') . 'schools'; }
	public function getSearchType(): string { return 'school'; }
	public function getSearchID() { return $this->id; }

	public function buildSearchDocument(): array {
		return [
			'id' => $this->id,
			'uf' => $this->uf,
			'name' => $this->name,
			'region' => $this->region,
			'city_id' => $this->city_id,
			'city_name' => $this->city_name,
			'city_ibge_id' => $this->city_ibge_id,
		];
	}

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function emailJobs(){
	    return $this->hasMany('BuscaAtivaEscolar\EmailJob', 'school_id');
    }

    /**
     * The classes this school has.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function classes()
    {
        return $this->hasMany('BuscaAtivaEscolar\Classe', 'schools_id', 'id');
    }

}