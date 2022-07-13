<?php
/**
 * busca-ativa-escolar-api
 * City.php
 *
 * Copyright (c) LQDI Digital
 * www.lqdi.net - 2016
 *
 * @author Aryel Tupinambá <aryel.tupinamba@lqdi.net>
 *
 * Created at: 22/12/2016, 20:25
 */

namespace BuscaAtivaEscolar;

use BuscaAtivaEscolar\Search\Interfaces\Searchable;
use BuscaAtivaEscolar\Traits\Data\IndexedByUUID;
use Cache;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property int $id
 *
 * @property string $uf
 * @property string $region
 * @property string $name
 * @property string $name_ascii
 * @property string $ibge_city_id
 * @property string $ibge_uf_id
 * @property string $ibge_region_id
 * @property string $webdoc_url
 *
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon $deleted_at
 *
 * @property Tenant $tenant
 */
class City extends Model implements Searchable {

	use SoftDeletes;
	use IndexedByUUID;

	protected $table = "cities";
	protected $fillable = [
		'uf',
		'region',

		'name',
		'name_ascii',

		'ibge_city_id',
		'ibge_uf_id',
		'ibge_region_id',

		'webdoc_url',
	];

	// ------------------------------------------------------------------------

	/**
	 * The tenant associated with this city.
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function tenant() {
		return $this->hasOne('BuscaAtivaEscolar\Tenant', 'city_id', 'id');
	}

	/**
	 * Internal, determines primary key for API routing.
	 * @return string
	 */
	public function getRouteKeyName() {
		return 'id';
	}

    /**
     * The goal this city is associated with
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function goal() {
        return $this->hasOne('BuscaAtivaEscolar\Goal', 'id', 'ibge_city_id');
    }

	// ------------------------------------------------------------------------

	/**
	 * Gets the UF that contains this city
	 * @return IBGE\UF
	 */
	public function getUF() {
		return IBGE\UF::getByCode($this->uf);
	}

	/**
	 * Gets the Region that contains this city's UF
	 * @return IBGE\Region
	 */
	public function getRegion() {
		return $this->getUF()->getRegion();
	}

	// ------------------------------------------------------------------------

	/**
	 * Builds a query for searching cities
	 * @param array $filters The associative array of filter keys and values
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	public static function search(array $filters) {
		$query = self::query();

		if(isset($filters['uf'])) $query->where('uf', $filters['uf']);
		if(isset($filters['region'])) $query->where('region', $filters['region']);
		if(isset($filters['name'])) {
			// TODO: extract to elasticsearch/sphinx
			$query->where("name", "REGEXP", $filters['name']);
		}

		return $query;
	}

	/**
	 * Finds a city by its internal ID or IBGE ID
	 * @param string $id An internal city UUID or an IBGE city ID
	 * @return Model|null|static
	 */
	public static function findByID($id) {
		return self::query()
			->where('id', $id)
			->orWhere('ibge_city_id', $id)
			->first();
	}

	/**
	 * Gets a list of all city IDs within a specific state
	 * @param string $uf
	 * @return array
	 */
	public static function getIDsWithinUF($uf) {
		return Cache::remember('uf_cities_' . $uf, config('cache.timeouts.uf_cities'), function () use ($uf) {
			return DB::table('cities')
				->where('uf', $uf)
				->get(['id'])
				->pluck('id')
				->toArray();
		});
	}

	public function getSearchIndex(): string { return config('search.index_prefix') . 'cities'; }
	public function getSearchType(): string { return 'city'; }
	public function getSearchID() { return $this->id; }

	public function buildSearchDocument(): array {
		return [
			'id' => $this->id,
			'uf' => $this->uf,
			'name' => $this->name,
			'name_suggest' => $this->name,
			'name_ascii' => Str::ascii($this->name),
			'region' => $this->region,
			'tenant' => ($this->tenant ?? null),
			'ibge_city_id' => $this->ibge_city_id,
			'ibge_uf_id' => $this->ibge_uf_id,
			'ibge_region_id' => $this->ibge_region_id,
		];
	}
}